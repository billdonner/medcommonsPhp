#!/usr/bin/env python
#
# mcid_server.py
# Copyright(c) 2006, Medcommons, Inc.
#
"""Runs a SOAP service that allocates MCIDs (Medcommons Identification
numbers).

Publishes two methods:

1. next_mcid(), returns a numeric (long) value from 0 to 10**16 (a 16
   digit number).

2. next_mcid_str(), returns a 16-character zero-filled string.

MCIDs have a single type digit, and roughly 15 digits of random data.
The random data comes from a Linear Congruential Pseudo-Random Number
Generator, pretty much guaranteed not to repeat in our lifetimes.

The single type digit is used to differentiate between development,
test, and production.  There's an 'employee' type, too.

These types are stored in an SQLite database, along with the last seed
used by the allocator.  The allocator can be stopped and restarted
with no fear of MCID collisions.

To keep database interactions to a minimum, the generator allocates
2000 MCIDs before writing an entry to the database.  When the
generator starts up, it allocates and throws away 2000 entries.  Now
if there's a hardware failure, it will skip any entries already
allocated.

To create the database, execute this script::

    $ sqlite3 mcids
    sqlite> CREATE TABLE mcids (name VARCHAR(32), type INTEGER,
       ...> seed DECIMAL(16));

To populate it with decent data, execute this script::

    $ sqlite3 mcids
    sqlite> INSERT INTO mcids (name, type, seed)
       ...> VALUES('development', 1, 0);
    sqlite> INSERT INTO mcids (name, type, seed)
       ...> VALUES('test', 2, 0);
    sqlite> INSERT INTO mcids (name, type, seed)
       ...> VALUES('employee', 3, 0);
    sqlite> INSERT INTO mcids (name, type, seed)
       ...> VALUES('production', 4, 0);

"""

import SOAPpy # http://pywebsvcs.sourceforge.net/

from sys import argv, exit, stderr

# SQLite version 3
from pysqlite2 import dbapi2 as sql # http://initd.org/tracker/pysqlite

NS = 'http://www.medcommons.net/mcid'

# Linux daemon process files
#
PID_FILE = '/var/run/mcid_server.pid'
LOG_FILE = '/var/log/mcid_server.log'

class Generator(object):
    """The state for an MCID generator, including the random number
    seed, and everything necessary to load and save back to the
    persistent seed database.
    """
    A = 44485709377909L;
    B = 11863279L;

    __slots__ = ['type', 'seed', 'mask', 'iterations', 'count']

    def __init__(self, iterations = 2000):
	self.count = self.iterations = iterations

    def next_mcid_str(self):
	"""Returns the next MCID as a zero-filled, 16 character string."""
	return '%016d' % self.next_mcid()

    def next_mcid(self):
	"""Constructs an MCID, occasionally saving to the database.
	"""
	result = self.next()

	if self.count == 0:
	    self.save()
	    self.count = self.iterations
	else:
	    self.count -= 1

	return result

    def load(self, typename=None):
	"""Given a MCID type name (a key into the 'mcids' table) set
	this Generator with the correct mask and seed."""

        if not typename:
            typename = 'development'

	c = db.execute("SELECT type, seed FROM mcids WHERE name='%s'" % typename)
	self.type, self.seed = c.next()
	self.mask = self.type * 1000000000000000L

    def save(self):
	"""Saves the seed state in the database.
	"""
	db.execute("UPDATE mcids SET seed=%d WHERE type=%d" % (self.seed,
							       self.type))
	db.commit()

    def next(self):
	"""Constructs an MCID using the random number and a mask.
	"""
	self.seed = self.lcm_prn(self.seed)

	return self.seed + self.mask

    # @staticmethod
    def lcm_prn(last):
	"""Linear Congruential Method for generating Pseudo-Random Numbers.

	Examples::

	>>> Generator.lcm_prn(0)
	11863279L

	>>> Generator.lcm_prn(11863279L)
	222303975802154L
	"""
	return (Generator.A * last + Generator.B) & 0xFFFFFFFFFFFFL

    lcm_prn = staticmethod(lcm_prn)

HTTP_404 = """<h1>File not found</h1>
<p>
This is a SOAP/REST server for allocating MedCommons MCIDs.
You may retrieve the WSDL from <a href='/wsdl'>/wsdl</a>.
You may also get the next MCID from <a href='/mcid'>/mcid</a>.
</p>
"""

WSDL = """<?xml version="1.0"?>

<!--

	mcids.wsdl
	Copyright(c) 2006, Medcommons, Inc.

	Two web services:

	1) next_mcid() returns a new integer MCID.
	2) next_mcid_str() returns a new 16-character string

-->

<wsdl:definitions name='mcid' targetNamespace="http://www.medcommons.net/mcid"
		  xmlns:impl='http://www.medcommons.net/mcid'
		  xmlns:wsdlsoap="http://schemas.xmlsoap.org/wsdl/soap/"
		  xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/"
		  xmlns:xsd="http://www.w3.org/2001/XMLSchema">

  <!-- next_mcid()   request -->
  <wsdl:message name="next_mcidRequest">
  </wsdl:message>

  <!-- next_mcid()   response -->
  <wsdl:message name="next_mcid_strResponse">
    <wsdl:part name="next_mcid_strReturn" type="xsd:string" />
  </wsdl:message>

  <!-- next_mcid_str()   request -->
  <wsdl:message name="next_mcid_strRequest">
  </wsdl:message>

  <!-- next_mcid_str()   response -->
  <wsdl:message name="next_mcidResponse">
    <wsdl:part name="next_mcidReturn" type="xsd:long" />
  </wsdl:message>

  <wsdl:portType name="mcid">

    <wsdl:operation name="next_mcid">
      <wsdl:input message="impl:next_mcidRequest"
		  name="next_mcidRequest" />
      <wsdl:output message="impl:next_mcidResponse"
		   name="next_mcidResponse" />
    </wsdl:operation>

    <wsdl:operation name="next_mcid_str">
      <wsdl:input message="impl:next_mcid_strRequest"
		  name="next_mcid_strRequest" />
      <wsdl:output message="impl:next_mcid_strResponse"
		   name="next_mcid_strResponse" />
    </wsdl:operation>

  </wsdl:portType>

  <wsdl:binding name="mcidSoapBinding" type="impl:mcid">
    <wsdlsoap:binding style="rpc"
		      transport="http://schemas.xmlsoap.org/soap/http" />

    <wsdl:operation name="next_mcid_str">
      <wsdlsoap:operation soapAction="" />

      <wsdl:input name="next_mcid_strRequest">
	<wsdlsoap:body
	  encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
	  namespace="http://www.medcommons.net/mcid" use="encoded" />
      </wsdl:input>

      <wsdl:output name="next_mcid_strResponse">
        <wsdlsoap:body
	  encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
	  namespace="http://www.medcommons.net/mcid" use="encoded" />
      </wsdl:output>
    </wsdl:operation>

    <wsdl:operation name="next_mcid">
      <wsdlsoap:operation soapAction="" />
      <wsdl:input name="next_mcidRequest">
	<wsdlsoap:body
	  encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
	  namespace="http://www.medcommons.net/mcid" use="encoded" />
      </wsdl:input>

      <wsdl:output name="next_mcidResponse">
	<wsdlsoap:body
	  encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
	  namespace="http://www.medcommons.net/mcid" use="encoded" />
      </wsdl:output>
    </wsdl:operation>
  </wsdl:binding>

  <wsdl:service name="mcidService">
    <wsdl:port binding="impl:mcidSoapBinding" name="mcid">
      <wsdlsoap:address location="http://mcid.internal:1080/" />
    </wsdl:port>
  </wsdl:service>

</wsdl:definitions>
"""

class RESTRequestHandler(SOAPpy.SOAPRequestHandler):
    def do_GET(self):
	response = 200

	if self.path == '/mcid':
	    content_type = 'text/plain'
	    content = generator.next_mcid_str()

	elif self.path == '/wsdl':
	    content_type = 'text/xml'
	    content = WSDL
	else:
	    response = 404
	    content_type = 'text/html'
	    content = HTTP_404

	self.send_response(response)
	self.send_header('Content-Type', content_type)
	self.end_headers()
	self.wfile.write(content)

generator = Generator()

def main(argv):
    global db

    dbname = 'db/mcids'
    type = None

    args = argv[1:]
    debug = False
    port = 1080

    while args:
        if args[0] == '-db' and len(args) > 1:
            dbname = args[1]
            args = args[2:]

        elif args[0] == '-D':
            debug = True
            args = args[1:]

        elif args[0] == '-p' and len(args) > 1:
            port = int(args[1])
            args = args[2:]

        elif type is None and not args[0].startswith('-'):
            type = args[0]
            args = args[1:]

        else:
            usage(dbname)

    db = sql.connect(dbname)
    generator.load(type)

    # This is set up so the very next allocation from the SOAP service
    # will result in a database write.  That way, you can start and
    # stop the SOAP service, and as long as there weren't any SOAP calls,
    # we're not allocating blocks of MCIDs.
    #
    for i in xrange(generator.iterations):
	generator.next_mcid()

    config = SOAPpy.SOAPConfig()
    #config.dumpSOAPIn = True
    server = SOAPpy.SOAPServer(("mcid.internal", port),
			       namespace = NS, config = config,
			       RequestHandler = RESTRequestHandler)
    server.registerFunction(generator.next_mcid_str, path='mcid', namespace=NS)
    server.registerFunction(generator.next_mcid, path='mcid', namespace=NS)

    # On unix root, try to setuid to the 'mcid_server' account for
    # least privilege
    #
    if not debug:
        try:
            from os import _exit, setuid, setgid, fork, dup2, open, close, \
                 O_RDONLY, O_WRONLY, O_APPEND, O_CREAT
            from pwd import getpwnam
        except ImportError:
            pass

        else:
            pw = getpwnam('mcid_server')

            pid = fork()

            if pid:
                print >>file(PID_FILE, 'w'), pid
                _exit(0)

            fd = open('/dev/null', O_RDONLY)
            dup2(fd, 0)

            fd = open(LOG_FILE, O_WRONLY | O_APPEND | O_CREAT)
            dup2(fd, 1)
            dup2(fd, 2)
            close(fd)

            setgid(pw.pw_gid)
            setuid(pw.pw_uid)

    server.serve_forever()

def usage(dbname):
    try:
        db = sql.connect(dbname)

	types = [r[0] for r in db.execute("SELECT name FROM mcids")]
    except:
        types = ['development']

    print >>stderr, "Usage: python mcid_server [%s]" % '|'.join(types)
    print >>stderr, "    -db {sqlite database file}   -- change database"
    print >>stderr, "    -D                           -- debug mode (no fork)"
    print >>stderr, "    -p {TCP port)                -- defaults to 1080"
    exit(1)

def _test():
    import doctest, mcid_server
    return doctest.testmod(mcid_server)

if __name__ == '__main__':
    _test()
    main(argv)
