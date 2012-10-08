#!/usr/bin/env python
#
# backup_to_s3.py
# Copyright(c) 2007, MedCommons, Inc

"""
Backups patient data to S3.

1. Connects to the Gateway/Router's MySQL database;
2. Pulls out documents from the backup_queue table;
3. Retrieves these documents using CXP;
4. Archives, compresses, and encrypts the data before...
5. Sending up to S3
"""

__author__ = 'Terence Way'
__email__ = 'tway@medcommons.net'
__version__ = '1.1: Feb	8, 2008'

import MySQLdb as sql
from sys import argv, exit, exc_info, stderr
from time import sleep
from os.path import expanduser

from cxp_to_s3 import cxp_to_s3, DEFAULT_HOST, DEFAULT_BUCKET, DEFAULT_KEYFILE,\
     CXP_OK, CXP_TEMP_ERR, CXP_PERM_ERR

import s3

DB_NAME = 'mcgateway'
DB_USER = 'backup'

# Linux daemon process files
PID_FILE = '/var/run/mc_backups.pid'
LOG_FILE = '/var/log/mc_backups.log'

DEFAULT_USERNAME = 'mc_backups'

USAGE = """Usage: python backup_to_s3.py

Options:
    -D                  - debug mode (no fork)
    -C {filename}       - extra config options
    -pid {filename}     - PID output file, defaults to '%(PID_FILE)s'
    -log {filename}     - log file, defaults to '%(LOG_FILE)s'
    -u {username}       - Setuid username, defaults to '%(DEFAULT_USERNAME)s'
    -db {database}      - optional database name, defaults to '%(DB_NAME)s'
    -user {db user}     - database user, defaults to '%(DB_USER)s'
    -host {CXP host}    - CXP host, defaults to '%(DEFAULT_HOST)s'
    -bucket {s3 bucket} - S3 bucket name, defaults to '%(DEFAULT_BUCKET)s'
    -a {s3 key id}      - S3 Key ID, defaults to contents of '~/.s3/key_id'
    -s {s3 secret}      - S3 Secret, defaults to contents of '~/.s3/key'
    -kfile {enc file}   - AES key file, defaults to '%(DEFAULT_KEYFILE)s'
"""

db_options = dict(db = DB_NAME,
                  user = DB_USER)

cxp_options = dict()

debug = False

def main(argv):
    global debug

    args = argv[1:]

    username = DEFAULT_USERNAME
    pid_file = PID_FILE
    log_file = LOG_FILE

    try:
	s3.init0()
    except:
	pass
    
    while args:
        if args[0] == '-D':
            debug = True
            args = args[1:]

        elif args[0] == '-C' and len(args) > 1:
            args = file(args[1]).read().split() + args[2:]

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
            cxp_options['host'] = args[1]
            args = args[2:]
        elif args[0] == '-bucket' and len(args) > 1:
            cxp_options['bucket'] = args[1]
            args = args[2:]
	elif args[0] == '-a' and len(args) > 1:
	    s3.KEY_ID = args[1]
	    args = args[2:]
	elif args[0] == '-s' and len(args) > 1:
	    s3.SECRET = args[1]
	    args = args[2:]
	elif args[0] == '-kfile' and len(args) > 1:
	    cxp_options['kfile'] = expanduser(args[1])
	    args = args[2:]
        else:
            usage()

    if not s3.KEY_ID or not s3.SECRET:
	usage()

    if not debug:
        try:
            from os import _exit, setuid, setgid, fork, dup2, open, close, \
                 mkdir, chdir, O_RDONLY, O_WRONLY, O_APPEND, O_CREAT
            from pwd import getpwnam
        except ImportError:
            pass

        else:
            pw = getpwnam(username)

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

    while True:
        db = sql.connect(**db_options)

        c = db.cursor()

        c.execute("SELECT id, account_id, guid, amazon_user_token, amazon_product_token" + \
                  " FROM backup_queue, mcx.users" + \
                  " WHERE endtime IS NULL AND account_id = mcx.users.mcid" + \
                  " ORDER BY id" + \
                  " LIMIT 1")

        t = c.fetchone()

        if t:
            c.execute("UPDATE backup_queue" + \
                      " SET starttime=NOW(), status='UPLOADING'" + \
                      " WHERE id=%s;" % t[0])

            db.commit()

        c.close()
        db.close()

        if t:
            put(t[0], t[1], t[2], t[3], t[4])
        else:
            sleep(1)

def put(id, mcid, guid, amazon_user_token, amazon_product_token):
    x = cxp_to_s3(guid = guid, storageId = '%016d' % int(mcid),
                  amazon_user_token=amazon_user_token,
                  amazon_product_token=amazon_product_token, **cxp_options)

    update = "UPDATE backup_queue "

    if x == CXP_OK:
        update += " SET endtime=NOW(), status='COMPLETE' "
    elif x == CXP_TEMP_ERR:
        update += " SET status='TEMPERR' "
    else:
        update += " SET endtime=NOW(), status='PERMERR' "

    update += " WHERE id=%s" % id

    db = sql.connect(**db_options)

    c = db.cursor()

    if debug:
	print mcid, guid

    c.execute(update)
    db.commit()

def usage():
    print >>stderr, USAGE % globals()
    exit(1)

if __name__ == '__main__':
    main(argv)
