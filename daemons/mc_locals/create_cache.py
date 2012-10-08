#!/usr/bin/env python
# vim: ts=4 shiftwidth=4
#
# mc_locals.py
# Copyright(c) 2006, Medcommons, Inc.
#
"""Creates a cache of mcid sets so that a machine can run off line
"""

__author__ = "Simon Sadedin"
__email = "ssadedin@medcommons.net"
__version__ = "1.0: Feb 24, 2009"

import SOAPpy # http://pywebsvcs.sourceforge.net/

import socket
import pickle

from datetime import datetime
from sys import argv, exit, stderr, exc_info

NS1 = 'http://www.medcommons.net/mcid'
NS2 = 'http://www.medcommons.net/locals'

DEFAULT_USERNAME = 'mc_locals'
DEFAULT_HOST = 'mcid.internal'
DEFAULT_PORT = 1080
DEFAULT_URL = 'http://globals.medcommons.net/globals/'
DEFAULT_URL = 'http://ci.myhealthespace.com/globals/'

CACHE_SIZE=2

def main(argv):
    global status, mcids, tracking_numbers, proxy, CACHE_SIZE

    args = argv[1:]

    debug = False
    username = DEFAULT_USERNAME
    port = DEFAULT_PORT

    try:
        socket.gethostbyname(DEFAULT_HOST)
        host = DEFAULT_HOST
    except socket.error:
        host = '127.0.0.1'

    url = DEFAULT_URL
    appliance = None
    
    while args:
        if args[0] == '-D':
            debug = True
            args = args[1:]

        elif args[0] == '-p' and len(args) > 1:
            port = int(args[1])
            args = args[2:]

        elif args[0] == '-h' and len(args) > 1:
            host = args[1]
            args = args[2:]


        elif args[0] == '-url' and len(args) > 1:
            url = args[1]
            args = args[2:]

        elif args[0] == '-size' and len(args) > 1:
            CACHE_SIZE = int(args[1])
            args = args[2:]
            
        elif not args[0].startswith('-') and not appliance:
            appliance = args[0]
            args = args[1:]

        else:
            usage()

    if not appliance:
        appliance = socket.getfqdn()

    proxy = SOAPpy.SOAPProxy(url,
                             namespace = 'http://www.medcommons.net/globals')

    mcids=[]
    for i in xrange(CACHE_SIZE):
        mcidset = proxy.next_mcid_set(appliance)
        print "Got mcid range {next=%d,A=%d,B=%d,mask=0x%x,n=%d}\n" % (mcidset[0],mcidset[1].A, mcidset[1].B, mcidset[1].mask, mcidset[1].n)
        mcids.append([ mcidset[0], mcidset[1] ])
        
    f = open('mcids.txt','w')
    pickle.dump(mcids,f) 
    f.close()
    
    tns=[]
    for i in xrange(CACHE_SIZE):
		tnset = proxy.next_tracking_number_set(appliance)
		print "Got track# range {A=%d,B=%d,mask=0x%x,n=%d}\n" % (tnset[1].A, tnset[1].B, tnset[1].mask, tnset[1].n)
		tns.append([ tnset[0], tnset[1] ])
				    
    f = open('tns.txt','w')
    pickle.dump(tns,f) 
    f.close()

USAGE = """Usage: python create_cache.py {appliance-name}

    -url {SOAP url}  -- defaults to %(DEFAULT_URL)s
    -h {hostname}    -- server bind address, defaults to %(DEFAULT_HOST)s
    -p {TCP port}    -- defaults to %(DEFAULT_PORT)s
    
"""

def usage():
    print >>stderr, USAGE % globals()
    exit(1)

def _test():
    import doctest, mc_locals
    return doctest.testmod(mc_locals)

if __name__ == '__main__':
    main(argv)
