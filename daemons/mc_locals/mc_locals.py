#!/usr/bin/env python
# vim: ts=8 shiftwidth=8
#
# mc_locals.py
# Copyright(c) 2006, Medcommons, Inc.
#
"""Runs a SOAP service that allocates MCIDs (Medcommons Identification
numbers).

Publishes four methods:

1. next_mcid(), returns a numeric (long) value from 0 to 10**16 (a 16
   digit number).

2. next_mcid_str(), returns a 16-character zero-filled string.

3. next_tracking_number(), returns a numeric (long) value from
   0 to 10**12 (a 12 digit number).

4. next_tracking_number_str(), returns a 12-character zero-filled string.
"""

__author__ = "Terence Way"
__email = "tway@medcommons.net"
__version__ = "1.6: Sep 26, 2007"

import SOAPpy # http://pywebsvcs.sourceforge.net/

import socket

from datetime import datetime
from sys import argv, exit, stderr, exc_info
import pickle
import os, os.path

NS1 = 'http://www.medcommons.net/mcid'
NS2 = 'http://www.medcommons.net/locals'

# Linux daemon process files
#
PID_FILE = '/var/run/mc_locals.pid'
LOG_FILE = '/var/log/mc_locals.log'

DEFAULT_USERNAME = 'mc_locals'
DEFAULT_HOST = 'mcid.internal'
DEFAULT_PORT = 1080
DEFAULT_URL = 'http://globals.medcommons.net/globals/'

mcid_cache_path = 'mcids.txt'
tns_cache_path = 'tns.txt'

mcid_cache = None
tn_cache = None

class Status(object):
    pass

def infinite_sequence(f, appliance):

    while True:
        try:
            print "New mcid 2"
            next, g = f(appliance)
            
            print "Calculating for " + str(g)

            # ssadedin: scan over each id in the batch
            for i in xrange(g.n):
                # note range is 1 in practice
                # so this loop is redundant
                # if it wasn't 1 then this would produce a linear 
                # sequence 1,2,3,4,5 ... offset from base + leap
                for j in xrange(g.leap):
                    result = g.base + next * g.leap + j
                    yield result

                next = (g.A * next + g.B) & g.mask
        except:
            yield exc_info()

def wrap(next):
    status.requests += 1
    x = next()
    if isinstance(x, tuple):
        raise x[1]
    else:
        return x

def next_mcid():
    status.mcid_requests += 1
    return wrap(mcids.next)

def next_mcid_str():
    """Returns the next MCID as a zero-filled, 16 character string."""
    return '%016d' % next_mcid()

def next_tracking_number():
    status.tracking_number_requests += 1
    return wrap(tracking_numbers.next)

def next_tracking_number_str():
    return '%012d' % next_tracking_number()

HTTP_404 = """<h1>File not found</h1>
<p>
This is a SOAP/REST server for allocating MedCommons MCIDs and tracking
numbers.  You may retrieve the WSDL from <a href='/wsdl'>/wsdl</a>.
You may also get the next MCID from <a href='/mcid'>/mcid</a>, and the
next tracking number from <a href='/tracking_number'>/tracking_number</a>.
</p>
"""

WSDL = """<?xml version="1.0"?>

<!--

        locals.wsdl
        Copyright(c) 2006, Medcommons, Inc.

        Four web services:

        1) next_mcid() returns a new integer MCID.
        2) next_mcid_str() returns a new 16-character string

        3) next_tracking_number() returns a new integer tracking number.
        4) next_tracking_number_str() returns a new 12-character string

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
      <wsdlsoap:address location="http://%(host)s:%(port)d/" />
    </wsdl:port>
  </wsdl:service>

</wsdl:definitions>
"""

STATUS_FORMAT = "status=OK" + \
                "&version=%(version)s" + \
                "&url=%(url)s" + \
                "&requests=%(requests)d" + \
                "&mcid_requests=%(mcid_requests)d" + \
                "&tracking_number_requests=%(tracking_number_requests)d" + \
                "&started=%(started)sZ"

class RESTRequestHandler(SOAPpy.SOAPRequestHandler):
    def do_GET(self):
        response = 200

        try:
            if self.path == '/mcid':
                content_type = 'text/plain'
                content = next_mcid_str()

            elif self.path == '/tracking_number':
                content_type = 'text/plain'
                content = next_tracking_number_str()

            elif self.path == '/wsdl':
                content_type = 'text/xml'
                content = WSDL % status.__dict__

            elif self.path == '/status':
                content_type = 'application/x-www-form-urlencoded'
                now = datetime.utcnow()
                delta = now - status.started
                content = STATUS_FORMAT % status.__dict__ + \
                          "&seconds=%d.%06d&uptime=%s" % (delta.seconds,
                                                          delta.microseconds,
                                                          delta)
            else:
                response = 404
                content_type = 'text/html'
                content = HTTP_404
        except:
            response = 500
            content_type = 'text/plain'
            content = str(exc_info()[1])

        self.send_response(response)
        self.send_header('Content-Type', content_type)
        self.end_headers()
        self.wfile.write(content)


def next_mcid_cache(appliance):
    """Returns a new set of parameters for generating mcids from cache"""
    
    global mcid_cache_path
    
    if len(mcid_cache) == 0:
        raise Exception("mcid cache exhausted.  Please refresh mcid cache mcid_cache_path with a new file.")
    
    print "Returning next mcid set from cache"
    result = mcid_cache.pop()
    f = open(mcid_cache_path,'w')
    pickle.dump(mcid_cache,f)
    f.close();
    return result

def next_tn_cache(appliance):
    """Returns a new set of parameters for generating tracking numbers from cache"""
    
    global tns_cache_path
    
    if len(tn_cache) == 0:
        raise Exception("tracking number cache exhausted.  Please refresh tracking number cache 'tns.txt' with a new file.")
    
    print "Returning next tn set from cache"
    result = tn_cache.pop()
    f = open(tns_cache_path,'w')
    pickle.dump(tn_cache,f)
    f.close()
    return result

def main(argv):
    global status, mcids, tracking_numbers, proxy, mcid_cache, tn_cache, tns_cache_path, mcid_cache_path
    
    args = argv[1:]

    debug = False
    username = DEFAULT_USERNAME
    port = DEFAULT_PORT

    try:
        socket.gethostbyname(DEFAULT_HOST)
        host = DEFAULT_HOST
    except socket.error:
        host = '127.0.0.1'

    pid_file = PID_FILE
    log_file = LOG_FILE

    url = DEFAULT_URL
    appliance = None
    use_cache = False
    
    while args:
        if args[0] == '-D':
            debug = True
            args = args[1:]

        elif args[0] == '-C' and len(args) > 1:
            args = file(args[1]).read().split() + args[2:]

        elif args[0] == '-p' and len(args) > 1:
            port = int(args[1])
            args = args[2:]

        elif args[0] == '-h' and len(args) > 1:
            host = args[1]
            args = args[2:]
        
        elif args[0] == '-u' and len(args) > 1:
            username = args[1]
            args = args[2:]

        elif args[0] == '-pid' and len(args) > 1:
            pid_file = args[1]
            args = args[2:]

        elif args[0] == '-log' and len(args) > 1:
            log_file = args[1]
            args = args[2:]

        elif args[0] == '-url' and len(args) > 1:
            url = args[1]
            args = args[2:]

        elif args[0] == '-usecache' and len(args) > 1:
            use_cache = True
            mcid_cache_path =  os.path.join(args[1], mcid_cache_path)
            tns_cache_path =  os.path.join(args[1], tns_cache_path)
            args = args[2:]

        elif not args[0].startswith('-') and not appliance:
            appliance = args[0]
            args = args[1:]

        else:
            usage()

    if not appliance:
        appliance = socket.getfqdn()
        
   
    if use_cache:
        print "Using cache for mcids and tracking numbers:  loading archived sets"
        f = open(mcid_cache_path,'r')
        mcid_cache = pickle.load(f)
        print "Loaded " + str(len(mcid_cache)) + " mcid sets"
        mcids = infinite_sequence(next_mcid_cache, appliance)
        f.close()
        
        f = open(tns_cache_path,'r')
        tn_cache = pickle.load(f)
        f.close()
        print "Loaded " + str(len(tn_cache)) + " tracking number sets"
        
        tracking_numbers = infinite_sequence(next_tn_cache, appliance)
        
    else:
        proxy = SOAPpy.SOAPProxy(url,
                                 namespace = 'http://www.medcommons.net/globals')
        mcids = infinite_sequence(proxy.next_mcid_set, appliance)
        tracking_numbers = infinite_sequence(proxy.next_tracking_number_set,
                                             appliance)

    status = Status()
    status.port = port
    status.host = host
    status.uid = status.gid = 0
    status.url = url
    status.requests = status.mcid_requests = 0
    status.tracking_number_requests = 0
    status.version = __version__

    config = SOAPpy.SOAPConfig()
    #config.dumpSOAPIn = True
    server = SOAPpy.SOAPServer((host, port),
                               namespace = NS2, config = config,
                               RequestHandler = RESTRequestHandler)

    server.registerFunction(next_mcid_str, namespace=NS2)
    server.registerFunction(next_mcid, namespace=NS2)
    server.registerFunction(next_tracking_number_str, namespace=NS2)
    server.registerFunction(next_tracking_number, namespace=NS2)

    # backwards compatibility
    server.registerFunction(next_mcid_str, path='mcid', namespace=NS1)
    server.registerFunction(next_mcid, path='mcid', namespace=NS1)

    # On unix root, try to setuid to the 'mc_locals' account for
    # least privilege
    #
    if not debug:
        try:
            from os import _exit, setuid, setgid, fork, dup2, \
                 O_RDONLY, O_WRONLY, O_APPEND, O_CREAT
            from pwd import getpwnam
        except ImportError:
            pass

        else:
            pw = getpwnam(username)
            status.user = username

            pid = fork()

            if pid:
                print >>file(pid_file, 'w'), pid
                _exit(0)

            fd = os.open('/dev/null', O_RDONLY)
            dup2(fd, 0)

            fd = os.open(log_file, O_WRONLY | O_APPEND | O_CREAT)
            dup2(fd, 1)
            dup2(fd, 2)
            os.close(fd)

            setgid(pw.pw_gid)
            setuid(pw.pw_uid)

            status.gid = pw.pw_gid
            status.uid = pw.pw_uid

    status.started = datetime.utcnow()

    server.serve_forever()

USAGE = """Usage: python mc_locals.py {appliance-name}

    -url {SOAP url}       -- defaults to %(DEFAULT_URL)s
    -D                    -- debug mode (no fork)
    -C {filename}         -- extra config options
    -h {hostname}         -- server bind address, defaults to %(DEFAULT_HOST)s
    -p {TCP port}         -- defaults to %(DEFAULT_PORT)s
    -u {username}         -- Setuid username, defaults to '%(DEFAULT_USERNAME)s'
    -pid {filename}       -- PID output file, defaults to '%(PID_FILE)s'
    -usecache             -- use cached file of mcids instead of querying server
    -log {filename}       -- log file, defaults to '%(LOG_FILE)s'

On Unix this program forks as a server/daemon, unless -D is specified.
The PID of the daemon is written to '%(PID_FILE)s',
and errors are written to '%(LOG_FILE)s'.
"""

def usage():
    print >>stderr, USAGE % globals()
    exit(1)

def _test():
    import doctest, mc_locals
    return doctest.testmod(mc_locals)

if __name__ == '__main__':
    main(argv)
