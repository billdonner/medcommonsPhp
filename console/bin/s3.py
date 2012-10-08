#!/usr/bin/env python

"""
"""

__author__ = "Terence Way"
__email__ = "tway@medcommons.net"
__version__ = "1.3: Feb 8, 2008"

import SOAPpy

import base64
import hmac
import md5
import sha
import sys

from datetime import datetime
from os.path import expanduser, basename, join

from httplib import HTTPConnection

from xml.dom.minidom import Document, parse

KEY_ID = SECRET = None

# Create a .s3 directory under your home directory.  Put your access
# key ID in a file ~/.s3/key_id and your access key in ~/.s3/key.
# Do a chmod 600 on these files, of course.
#
INIT = """Cannot get Amazon AWS authorization keys.
1.  Create the directory %(key_dir)s
2.  Put your Amazon Key ID into %(key_dir)s/key_id
3.  Put your Amazon Secret Key into %(key_dir)s/key
"""

def init():
    """Initialize based on user-specific files ~/.s3/key_id and ~/.s3/key

    Prints out a usage message and exits on error
    """
    try:
	init1()
    except:
        print >>sys.stderr, INIT % {'key_dir': join(expanduser('~'), '.s3')}
        sys.exit(1)

def init1():
    """Initialize based on user-specific files ~/.s3/key_id and ~/.s3/key

    Raises IO exceptions if files aren't found.
    """
    home_dir = expanduser('~')
    init0(key_id = file(join(home_dir, '.s3', 'key_id')).read().strip(),
	  secret = file(join(home_dir, '.s3', 'key')).read().strip())

def init0(key_id, secret):
    """Initializes based on parameters key_id and secret.
    """
    global KEY_ID, SECRET

    KEY_ID = key_id
    SECRET = secret

#################
# SOAP methods...
#
#WSDL = 'http://s3.amazonaws.com/doc/2006-03-1/AmazonS3.wsdl'
#
#client = SOAPpy.WSDL.Proxy(WSDL)

SOAP_URL = 'https://s3.amazonaws.com/soap'
SOAP_NS = 'http://s3.amazonaws.com/doc/2006-03-01/'

client = SOAPpy.SOAPProxy(SOAP_URL, namespace=SOAP_NS)

def create_bucket(name, key_id = None, secret = None):
    return client.CreateBucket(Bucket=name,
                               **auth_keywords('CreateBucket', key_id, secret))

def delete_bucket(name, key_id = None, secret = None):
    return client.DeleteBucket(Bucket=name,
                               **auth_keywords('DeleteBucket', key_id, secret))

def list_all_my_buckets(key_id = None, secret = None):
    """Gets a bucket list.

    Examples::

	>>> r = list_all_my_buckets()
	>>> r.Owner.DisplayName
	'terenceway'

    r.Buckets.Bucket[0].Name
    r.Buckets.Bucket[1].Name
    """
    return client.ListAllMyBuckets(**auth_keywords('ListAllMyBuckets',
                                                   key_id, secret))

def list_bucket(bucket, key_id = None, secret = None):
    return client.ListBucket(Bucket=bucket,
                             **auth_keywords('ListBucket', key_id, secret))

def get_bucket_access_control_policy(bucket, key_id = None, secret = None):
    """Gets a bucket's ACP (Access Control Policy).

    Examples::

	>>> r = get_bucket_access_control_policy('media.wayforward.net')
	>>> r.Owner.DisplayName
	'terenceway'
    """
    return client.GetBucketAccessControlPolicy(Bucket = bucket,
					       **auth_keywords('GetBucketAccessControlPolicy',
                                                               key_id, secret))

def get_object_access_control_policy(bucket, object, key_id, secret):
    return client.GetObjectAccessControlPolicy(Bucket = bucket,
                                               Key = object,
                                               **auth_keywords('GetObjectAccessControlPolicy',
                                                               key_id, secret))

def delete_object(bucket, name, key_id = None, secret = None):
    return client.DeleteObject(Bucket=bucket, Key=name,
			       **auth_keywords('DeleteObject', key_id, secret))

def auth_keywords(operation, key_id = None, secret = None):
    ts = timestamp()
    return dict(AWSAccessKeyId = key_id or KEY_ID,
                Timestamp = ts,
		Signature = soap_signature(operation, ts, secret))

def timestamp(now = None):
    """Returns an xml-schema dateTime in UTC, millisecond precision
    http://www.w3.org/TR/xmlschema-2/#datetime

    Examples:
    >>> timestamp(datetime(2006, 10, 7, 3, 34, 21, 386241))
    '2006-10-07T03:34:21.386Z'

    >>> timestamp(datetime(2006, 10, 7, 3, 05, 06, 0))
    '2006-10-07T03:05:06.000Z'
    """
    if not now:
	now = datetime.utcnow()
    return '%s.%03dZ' % (now.strftime('%Y-%m-%dT%H:%M:%S'),
			 now.microsecond / 1000)

def soap_signature(operation, timestamp, secret = None):
    """Returns a signature for the Signature soap parameter"""
    h = hmac.new(secret or SECRET, 'AmazonS3', sha)
    h.update(operation)
    h.update(timestamp)
    return base64.encodestring(h.digest()).strip()

# ...SOAP methods
#################

#################
# REST methods...
#
REST_HOST = 's3.amazonaws.com'
REST_NS = SOAP_NS

READ = 'READ'
WRITE = 'WRITE'
READ_ACP = 'READ_ACP'
WRITE_ACP = 'WRITE_ACP'
FULL_CONTROL = 'FULL_CONTROL'

_ALLOWED_PERMISSIONS = {READ: READ, 'R': READ, WRITE: WRITE, 'W': WRITE,
			READ_ACP: READ_ACP, WRITE_ACP: WRITE_ACP,
			FULL_CONTROL: FULL_CONTROL}

ALL_USERS = "http://acs.amazonaws.com/groups/global/AllUsers"
AUTH_USERS = "http://acs.amazonaws.com/groups/global/AuthenticatedUsers"

class AccessControlList(object):
    def __init__(self, id, display_name):
	dom = self.dom = Document()
	acp = dom.createElement('AccessControlPolicy')
	acp.setAttribute('xmlns', REST_NS)
	acp.setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance')
	dom.appendChild(acp)

	own = dom.createElement('Owner')
	self.__make_id(own, id, display_name)

	acp.appendChild(own)
	self.acl = self.dom.createElement('AccessControlList')
	acp.appendChild(self.acl)

    def grant_to_group(self, group, what):
	who = self.dom.createElement('Grantee')
	who.setAttribute('xsi:type', 'Group')
	uri = self.dom.createElement('URI')
	uri.appendChild(self.dom.createTextNode(group))
	who.appendChild(uri)
	self.__grant(who, what)

    def grant_to_email(self, email, what):
	who = self.dom.createElement('Grantee')
	who.setAttribute('xsi:type', 'AmazonCustomerByEmail')
	e = self.dom.createElement('EmailAddress')
	e.appendChild(self.dom.createTextNode(email))
	who.appendChild(e)
	self.__grant(who, what)

    def grant_to_user(self, id, display_name, what):
	who = self.dom.createElement('Grantee')
	who.setAttribute('xsi:type', 'CanonicalUser')
	self.__make_id(who, id, display_name)
	self.__grant(who, what)

    def __make_id(self, node, id, display_name):
	id_xml = self.dom.createElement('ID')
	id_xml.appendChild(self.dom.createTextNode(id))
	node.appendChild(id_xml)

	dn_xml = self.dom.createElement('DisplayName')
	dn_xml.appendChild(self.dom.createTextNode(display_name))
	node.appendChild(dn_xml)

    def __grant(self, who, what):
	try:
	    self.__grant0(who, _ALLOWED_PERMISSIONS[what.upper()])
	except KeyError:
	    for x in what:
		self.__grant0(who.cloneNode(True),
			      _ALLOWED_PERMISSIONS[x.upper()])

    def __grant0(self, who, permission):
	assert permission in _ALLOWED_PERMISSIONS

	g = self.dom.createElement('Grant')
	g.appendChild(who)

	p = self.dom.createElement('Permission')
	p.appendChild(self.dom.createTextNode(permission))
	g.appendChild(p)
	self.acl.appendChild(g)

    def to_xml(self):
	return self.dom.toxml()

    def unlink(self):
	self.dom.unlink()
	del self.dom
	self.acl.unlink()
	del self.acl

def get(bucket, object):
    return data_to_url('GET', '/%s/%s' % (bucket, object), None, None)

def get_bucket_acl(bucket):
    return parse(data_to_url('GET', '/%s?acl' % bucket, None, None))

def set_bucket_acl(bucket, acl):
    xml = acl.to_xml()
    data_to_url('PUT', '/%s?acl' % bucket, xml,
		content_type='application/xml')

def get_object_acl(bucket, object):
    return parse(data_to_url('GET', '/%s/%s?acl' % (bucket, object),
			     None, None))

def set_object_acl(bucket, object, acl):
    xml = acl.to_xml()
    return data_to_url('PUT', '/%s/%s?acl' % (bucket, object), xml,
		       content_type='application/xml')

def put_data(bucket, name, data, content_type='application/octet-stream'):
    data_to_url('PUT', '/%s/%s' % (bucket, name), data, content_type)

def file_to_url(method, url, file, content_type='application/octet-stream'):
    hash = md5.new()
    length = 0

    while True:
	block = file.read(4096)
	if not block:
	    break
	length += len(block)
	hash.update(block)

    headers = rest_headers(method, url, hash, content_type)

    file.seek(0)

    #print 'Content-Length:', str(length)
    headers['Content-Length'] = str(length)

    c = HTTPConnection(REST_HOST)
    #c.set_debuglevel(9)
    c.connect()
    c.putrequest(method, url)
    for key, value in headers.items():
	c.putheader(key, value)
    c.endheaders()

    while length > 4096:
	block = file.read(4096)
	if not block:
	    raise "Unexpected EOF"

	c.send(block)
	sys.stdout.write('.')
	sys.stdout.flush()
	length -= len(block)

    while length > 0:
	block = file.read(length)
	if not block:
	    raise "Unexpected EOF"
	c.send(block)
	length -= len(block)

    return c.getresponse()

def data_to_url(method, url, data, content_type=None):
    if content_type:
	hash = md5.new(data)
    else:
	hash = None

    headers = rest_headers(method, url, hash, content_type)

    c = HTTPConnection(REST_HOST)
    #c.set_debuglevel(9)
    c.request(method, url, data, headers)

    r = c.getresponse()

    return r

def rest_headers(method, url, hash, content_type,
                 key_id = None, secret = None):
    headers = {}

    if content_type:
	md5_hash = base64.encodestring(hash.digest()).strip()
	headers['Content-Md5'] = md5_hash
	headers['Content-Type'] = content_type
    else:
	md5_hash = data = content_type = ''

    date = datetime.utcnow().strftime('%a, %d %b %Y %H:%M:%S GMT')
    headers['Date'] = date

    str = '\n'.join([method, md5_hash, content_type, date, url])

    h = hmac.new(secret or SECRET, str, sha)
    auth = 'AWS %s:%s' % (key_id or KEY_ID,
                          base64.encodestring(h.digest()).strip())

    headers['Authorization'] = auth

    return headers

# ...REST methods
#################

USAGE = """Usage: python s3.py {command}...
Commands:

new bucket             creates new S3 bucket

dir bucket             lists contents of bucket

del bucket             deletes S3 bucket 
    bucket/object      deletes S3 object

put bucket fn          puts file into S3 bucket with filename as object name
    bucket/object      puts standard input into bucket/object
    bucket/object fn   puts file into bucket/object

get bucket fn          copies bucket/fn to fn
    bucket/object      prints S3 object to standard output
    bucket/object fn   copies S3 object

acl bucket             prints ACL as XML
    bucket/object

share bucket acl...    modifies ACL on bucket.  all:r, terry@medcommons.net:RW
      bucket/object acl...

ACL ->          '@' bucket
              | ( 'all' | 'owner' | email ) ':' permission
permission ->   'R' | 'RW' | 'FULL_CONTROL' | 'READ' | 'WRITE'
"""

def main(args):
    init()
    
    cmds = {'new': cmd_new, 'dir': cmd_dir, 'del': cmd_del, 'put': cmd_put,
	    'get': cmd_get, 'acl': cmd_acl, 'share': cmd_share}

    if len(args) == 0:
	usage()

    else:
	try:
	    cmds[args[0]](args[1:])
	except KeyError:
	    usage()

def usage():
    print >>sys.stderr, USAGE
    sys.exit(1)

def cmd_new(args):
    if len(args) != 1:
	usage()

    create_bucket(args[0])

def cmd_dir(args):
    if len(args) == 0:
	r = list_all_my_buckets()
	print "Buckets owned by '%s'" % r.Owner.DisplayName
	print "------------------%s-" % ('-' * len(r.Owner.DisplayName))

        l = r.Buckets.Bucket
        if not isinstance(l, list):
            l = [l]
            
	for b in l:
	    print b.Name

    elif len(args) == 1:
	r = list_bucket(args[0])

	if isinstance(r.Contents, list):
	    for o in r.Contents:
		print o.Key
	else:
	    print r.Contents.Key
    else:
	usage()

def cmd_del(args):
    if len(args) != 1:
	usage()

    f = args[0].split('/', 1)
    if len(f) == 1:
	delete_bucket(f[0])
    else:
	delete_object(f[0], f[1])

def cmd_put(args):
    if len(args) == 1:
	f = args[0].split('/', 1)
	if len(f) != 2:
	    usage()
	cmd_put_0(f[0], f[1], sys.stdin)

    elif len(args) == 2:
	f = args[0].split('/', 1)
	if len(f) == 1:
	    cmd_put_0(f[0], basename(args[1]), file(args[1], 'rb'))
	else:
	    cmd_put_0(f[0], f[1], file(args[1], 'rb'))

    else:
	usage()

def cmd_put_0(bucket, object, file):
    r = file_to_url('PUT', '/%s/%s' % (bucket, object), file)
    print r.status, r.reason

def cmd_get(args):
    if len(args) == 0 or len(args) > 2:
	usage()

    f = args[0].split('/', 1)
    if len(f) == 1:
	if len(args) != 2:
	    usage()

	f = [args[0], args[1]]

    r = get(f[0], f[1])

    if len(args) == 2:
	out = file(args[1], 'w')
    else:
	out = sys.stdout

    while True:
	b = r.read(1024)
	if not b:
	    break

	out.write(b)

def cmd_acl(args):
    if len(args) != 1:
	usage()

    f = args[0].split('/', 1)

    if len(f) == 1:
	r = get_bucket_acl(f[0])
    else:
	r = get_object_acl(f[0], f[1])

    print r.toprettyxml()

def cmd_share(args):
    if len(args) <= 1:
	usage()

    f = args[0].split('/', 1)

    if len(f) == 1:
        acp = get_bucket_access_control_policy(f[0])
    else:
        acp = get_object_access_control_policy(f[0], f[1])
        
    print acp
    acl = AccessControlList(acp.Owner.ID, acp.Owner.DisplayName)

    for a in args[1:]:
        try:
            who, what = a.split(':', 1)
        except:
            usage()
            return

        if who == 'owner':
            acl.grant_to_user(acp.Owner.ID, acp.Owner.DisplayName, what)
        elif who == 'all':
            acl.grant_to_group(ALL_USERS, what)
        else:
            acl.grant_to_email(who, what)

    if len(f) == 1:
	set_bucket_acl(f[0], acl)
    else:
	set_object_acl(f[0], f[1], acl)

def _test():
    import doctest, s3
    return doctest.testmod(s3)

if __name__ == '__main__':
    #_test()

    main(sys.argv[1:])
    sys.exit(0)

