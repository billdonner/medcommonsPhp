#!/usr/bin/env python
#
# mc_globals.py
# Copyright(c) 2007, MedCommons, Inc.
#

__author__ = "Terence Way"
__email__ = "tway@medcommons.net"
__version__ = "1.1.1: April 30, 2007"

from cgi import parse_qs
from datetime import datetime
from sys import argv, exit, exc_info, stderr

import SOAPpy # http://pywebsvcs.sourceforge.net

# SQLite version 3
#from pysqlite2 import dbapi2 as sql # http://initd.org/tracker/pysqlite

# MySQL
import MySQLdb as sql

NS = 'http://www.medcommons.net/globals'

# Linux daemon process files
PID_FILE = '/var/run/mc_globals.pid'
LOG_FILE = '/var/log/mc_globals.log'

DEFAULT_PORT = 1081

DEFAULT_USERNAME = 'mc_globals'

DEFAULT_DBHOST = 'mysql.internal'
DEFAULT_DBNAME = 'mcglobals'
DEFAULT_DBUSER = 'mc_globals'

db_options = dict(db = DEFAULT_DBNAME,
                  user = DEFAULT_DBUSER,
                  host = DEFAULT_DBHOST)

class Status(object):
    pass

UPDATE_FORMAT = "UPDATE alloc_numbers SET seed=%d WHERE id=%d"

INSERT_FORMAT = """
INSERT INTO alloc_log (numbers_id, seed, datetime, appliance_id, ipaddr)
VALUES ('%s', '%s', NOW(), '%s', %s)
"""

class Allocator(object):
    """The base class for application-specific number generators,
    like MCIDs, or tracking numbers.

    Uses a Linear Congruential pseudo-random number generator
    to generate the base data.

    Loads and stores seeds in the persistent 'mcx/alloc_numbers' database.
    """

    __slots__ = ['name', 'id',          # db record key
                 'A', 'B', 'mask',      # PRNG values
                 'seed',                # next value
                 'base', 'leap',        # range and multiplier for final number
                 'iterations',          # per allocation
                 'format', 'params']

    def __init__(self, name, A, B, mask):
        self.name = name
        self.A = A
        self.B = B
        self.mask = mask
        self.params = self.format = None

    def next_str(self, ipaddr, appliance):
        next, params = self.next_set(ipaddr, appliance)
        return self.format % next

    def soap_set(self, appliance):
        c = SOAPpy.GetSOAPContext()
        ipaddr = c.httpheaders.get('X-Forwarded-For',
                                   c.connection.getpeername()[0])
        return self.next_set(ipaddr, appliance)

    def next_set(self, ipaddr, appliance):
        status.requests += 1
        attr = '%s_requests' % self.name
        setattr(status, attr, getattr(status, attr, 0) + 1)

        db = sql.connect(**db_options)

        cursor = db.cursor()

        if self.format is None:
            c = cursor.execute("SELECT id, seed, base, leap, iterations" +
                               " FROM alloc_numbers"+
                               " WHERE name='%s'" % self.name)
            id, seed, base, leap, iterations = cursor.fetchone()

            self.id = int(id)
            self.seed = long(seed)
            self.base = long(base)
            self.leap = int(leap)
            self.iterations = int(iterations)

            self.params = dict(base = self.base,
                               leap = self.leap,
                               n = self.iterations,
                               A = self.A,
                               B = self.B,
                               mask = self.mask)

            self.format = ("next=%%d&base=%(base)d&leap=%(leap)d&n=%(n)d" + \
                           "&A=%(A)d&B=%(B)d&mask=%(mask)d") % self.params

        cursor.execute("SELECT id FROM appliances WHERE name='%s'" % \
                       db.escape_string(appliance))

        row = cursor.fetchone()

        if not row:
            cursor.execute("INSERT INTO appliances (name) VALUES ('%s')" % \
                           db.escape_string(appliance))
            cursor.execute("SELECT id FROM appliances WHERE name='%s'" % \
                           db.escape_string(appliance))
            row = cursor.fetchone()

        appliance_id = row[0]

        results = [(self.id, self.lcm_prn(), appliance_id, ipaddr) \
                   for i in xrange(self.iterations)]
        next = results[0][1]

        cursor.executemany(INSERT_FORMAT, results)
        cursor.execute(UPDATE_FORMAT % (self.seed, self.id))

        db.commit()

        return next, self.params

    def lcm_prn(self):
        result = self.seed
        self.seed = (self.A * self.seed + self.B) & self.mask
        return result

WSDL = '/wsdl'
NEXT_MCID_SET = '/next_mcid_set'
NEXT_TRACKING_NUMBER_SET = '/next_tracking_number_set'
STATUS = '/status'

HTTP_404 = """<h1>File not found</h1>
<p>
This is a SOAP/REST server for allocating MedCommons MCIDs and
Tracking Numbers.
</p>
<p>
You may retrieve the WSDL from <a href='%(WSDL)s'>%(WSDL)s</a>.
</p>
<p>
You may get the next MCID from <a href='%(NEXT_MCID_SET)s'>%(NEXT_MCID_SET)s</a>.
</p>
<p>
You may get the next Tracking Number from <a href='%(NEXT_TRACKING_NUMBER_SET)s'>
%(NEXT_TRACKING_NUMBER_SET)s</a>.
</p>
""" % globals()

STATUS_FORMAT = "status=OK" + \
                "&version=%(version)s" + \
                "&requests=%(requests)d" + \
                "&mcid_requests=%(mcid_requests)d" + \
                "&tracking_number_requests=%(tracking_number_requests)d" + \
                "&started=%(started)sZ"

class RESTRequestHandler(SOAPpy.SOAPRequestHandler):
    def do_GET(self):
        response = 200

        try:
            d = self.path.split('?', 1)
            if len(d) == 2:
                path, qs = d
                qs = parse_qs(qs)
            else:
                path = d[0]
                qs = {}

            ipaddr = self.headers.get('X-Forwarded-For', self.client_address[0])

            if path == NEXT_MCID_SET:
                content_type = 'text/plain'
                if 'appliance' in qs:
                    appliance = qs['appliance'][0]
                else:
                    appliance = None

                content = mcid_allocator.next_str(ipaddr, appliance)

            elif path == NEXT_TRACKING_NUMBER_SET:
                content_type = 'text/plain'
                if 'appliance' in qs:
                    appliance = qs['appliance'][0]
                else:
                    appliance = None

                content = tracking_number_allocator.next_str(ipaddr, appliance)

            elif path == STATUS:
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

mcid_allocator = Allocator('mcid',
                           A = 44485709377909,
                           B = 11863279,
                           mask = 0xFFFFFFFFFFFF)

tracking_number_allocator = Allocator('tracking_number',
                                      A = 1664525,
                                      B = 1013904223,
                                      mask = 0xFFFFFFFF)

generators = [mcid_allocator, tracking_number_allocator]

def main(argv):
    from os import getpid

    global status

    args = argv[1:]

    debug = False
    port = DEFAULT_PORT
    username = DEFAULT_USERNAME
    pid_file = PID_FILE
    log_file = LOG_FILE

    while args:
        if args[0] == '-D':
            debug = True
            args = args[1:]

        elif args[0] == '-C' and len(args) > 1:
            args = file(args[1]).read().split() + args[2:]

        elif args[0] == '-p' and len(args) > 1:
            port = int(args[1])
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

        elif args[0] == '-db' and len(args) > 1:
            db_options['db'] = args[1]
            args = args[2:]

        elif args[0] == '-user' and len(args) > 1:
            db_options['user'] = args[1]
            args = args[2:]

        elif args[0] == '-host' and len(args) > 1:
            db_options['host'] = args[1]
            args = args[2:]
        else:
            usage()

    config = SOAPpy.SOAPConfig()
    #config.dumpSOAPIn = True
    server = SOAPpy.SOAPServer(('0.0.0.0', port),
                               namespace = NS,
                               config = config,
                               RequestHandler = RESTRequestHandler)

    status = Status()

    for g in generators:
        # Create function names of the form
        #    next_{}_set
        #
        # for example:
        #    next_mcid_set()
        #    next_tracking_number_set()
        #
        server.registerFunction(g.soap_set, funcName = 'next_%s_set' % g.name,
                                namespace = NS)

        setattr(status, '%s_requests' % g.name, 0)

    status.uid = status.gid = 0
    status.log_file = None
    status.version = __version__
    
    if not debug:
        try:
            from os import _exit, setuid, setgid, fork, dup2, open, close, \
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

            fd = open('/dev/null', O_RDONLY)
            dup2(fd, 0)

            fd = open(log_file, O_WRONLY | O_APPEND | O_CREAT)
            dup2(fd, 1)
            dup2(fd, 2)
            close(fd)

            setgid(pw.pw_gid)
            setuid(pw.pw_uid)

            status.uid = pw.pw_uid
            status.gid = pw.pw_gid
            status.log_file = log_file

    status.port = port
    status.db_host = db_options['host']
    status.db_user = db_options['user']
    status.db = db_options['db']
    status.pid = getpid()
    status.started = datetime.utcnow()

    status.requests = 0

    server.serve_forever()

USAGE = """Usage: python mc_globals.py

    -D               -- debug mode (no fork)
    -C {filename}    -- extra config options
    -p {TCP port}    -- defaults to %(DEFAULT_PORT)d
    -u {username}    -- Setuid username, defaults to '%(DEFAULT_USERNAME)s'
    -pid {filename}  -- PID output file, defaults to '%(PID_FILE)s'
    -log {filename}  -- log file, defaults to '%(LOG_FILE)s'

Database options:
    -db {filename}   -- name of database, defaults to '%(DEFAULT_DBNAME)s'
    -host {host}     -- hostname, defaults to '%(DEFAULT_DBHOST)s'
    -user {name}     -- database user, defaults to '%(DEFAULT_DBUSER)s'
    -pass {password} -- database password

On Unix this program forks as a server/daemon, unless -D is specified.
The PID of the daemon process is written to '%(PID_FILE)s',
and errors are written to '%(LOG_FILE)s'
"""

def usage():
    print >>stderr, USAGE % globals()
    exit(1)
    
def _test():
    import doctest, mc_globals
    return doctest.testmod(mc_globals)

if __name__ == '__main__':
    _test()
    main(argv)
