#!/usr/bin/env python
#
# s3purge
# Copyright(c) 2008, Medcommons Inc.
#
"""
Purge old backups, according to a simple backup retention policy:

1.  Retain the first backup of the month.  YYYY-XX-01
2.  Retain the last 4 Sundays' backups.
3.  Retain the last 6 days' backups.

This script is intended to be run every day.
"""

# Those rules above break down to:
# If today is Sunday:
#     delete the backup 5 weeks ago, unless that's the first of the month
# Else:
#     delete the backup 1 week ago, unless that's the first of the month
#
# We will delete AT MOST one set of backup files, where one set is:
#     - the .sql.bz2.enc.XXX files  AND
#     - the .tar.bz2.enc.XXX files

__author__ = "Terence Way"
__email__ = "tway@medcommons.net"
__version__ = '1.0: April 24, 2008'

from datetime import date, timedelta
from os.path import expanduser, join
from socket import getfqdn
from sys import argv, exit, stderr, stdin

import s3

USAGE = """Usage: python s3purge.py {bucket} [host]


Options:
  -a {key_id}   Amazon Key ID: defaults to contents of ~/.s3/key_id
  -s {secret}   Amazon Secret: defaults to contents of ~/.s3/key

  -C {filename} Read command line options from file

  -mcproperties Pull S3 bucket, key_id, and secret from mcx.mcproperties table
"""

MONDAY, TUESDAY, WEDNESDAY, THURSDAY, FRIDAY, SATURDAY, SUNDAY = range(7)
WEEK = timedelta(7)

def main(argv):
    args = argv[1:]

    # Defaults
    key_id = get_s3_info('key_id')
    secret = get_s3_info('key')

    bucket = None
    host = None

    while args:
	a = args.pop(0)

        if a == '-C' and args:
            filename = args.pop(0)
            args = file(filename).read().split() + args

        elif a == '-a' and args:
            key_id = args.pop(0)

        elif a == '-s' and args:
            secret = args.pop(0)

        elif a == '-mcproperties':
            import s3options
            bucket = s3options.get_bucket()
            key_id = s3options.get_key_id()
            secret = s3options.get_secret()

        elif a.startswith('-'):
            usage()

        elif bucket is None:
            bucket = a

        elif host is None:
            host = a

        else:
            usage()

    if not host:
        host = getfqdn()

    if not bucket:
        usage()

    if not key_id:
        print >>stderr, "Must either specify -a {key_id} or put key_id into ~/.s3/key_id"
        exit(1)

    if not secret:
        print >>stderr, "Must either specify -s {secret} or put secret into ~/.s3/key"
        exit(1)

    s3.init0(key_id = key_id, secret = secret)

    today = date.today()

    if today.weekday() == SUNDAY:
        target = today - 5 * WEEK
    else:
        target = today - WEEK

    if target.day != 1:
        # NOT the first of the month, safe to delete
        prefix = '%s-%s' % (host, target.strftime('%Y-%m-%d'))

        delete_all_segments(bucket, prefix + '.sql.bz2.enc')
        delete_all_segments(bucket, prefix + '.tar.bz2.enc')

def delete_all_segments(bucket, prefix):
    segment = 0
    while True:
        object = '%s.%03d' % (prefix, segment)

        # See if object exists
        r = s3.data_to_url('HEAD', '/%s/%s' % (bucket, object),
                           None, None)

        if r.status != 200:
            break

        print 'Deleting', bucket, object
        r = s3.delete_object(bucket, object)

        segment += 1

def usage():
    print >>stderr, USAGE % globals()
    exit(1)

def parse_size(s):
    """Parses file sizes.

    Examples::
	>>> parse_size('42')
        42L

        >>> parse_size('42G')
        42000000000L
    """
    for suffix, multiple in SIZES:
        if s.endswith(suffix):
            return long(s[:-len(suffix)]) * multiple
    return long(s)

def get_s3_info(fn):
    try:
        return file(join(expanduser('~'), '.s3', fn)).read().strip()
    except IOError:
        return None

def _test():
    import doctest, s3purge
    return doctest.testmod(s3purge)

if __name__ == '__main__':
    _test()
    main(argv)
