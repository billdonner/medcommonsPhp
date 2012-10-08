#!/usr/bin/env python
#
# s3cat
# Copyright(c) 2008, Medcommons Inc.
#
"""
Concatenate to standard output a series of segments stored on S3.
"""

__author__ = "Terence Way"
__email__ = "tway@medcommons.net"
__version__ = '1.0: April 24, 2008'

from datetime import date, timedelta
from os import close, dup, open, write, O_WRONLY, O_TRUNC, O_CREAT
from os.path import expanduser, join
from socket import getfqdn
from sys import argv, exit, stderr

import s3

USAGE = """Usage: python s3cat.py {bucket} {object}

If {object} contains strftime formatting instructions ('%%Y-%%m-%%d')
then try a range of dates consistent with the backup retention policy.

Use %%%% as a single %% for {object} names that contain %%.

Options:
  -a {key_id}   Amazon Key ID: defaults to contents of ~/.s3/key_id
  -s {secret}   Amazon Secret: defaults to contents of ~/.s3/key

  -C {filename} Read command line options from file

  -mcproperties Pull S3 bucket, key_id, and secret from mcx.mcproperties table

  -o {output}   filename to write to, instead of stdout
"""

WEEK = timedelta(7)

def main(argv):
    args = argv[1:]

    # Defaults
    key_id = get_s3_info('key_id')
    secret = get_s3_info('key')

    bucket = None
    object = None
    output = dup(1)

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

        elif a == '-o':
            close(output)
            output = open(args.pop(0), O_WRONLY | O_CREAT | O_TRUNC, 0600)

        elif a.startswith('-'):
            usage()

        elif bucket is None:
            bucket = a

        elif object is None:
            object = a

        else:
            usage()

    if not bucket or not object:
        usage()

    if not key_id:
        print >>stderr, "Must either specify -a {key_id} or put key_id into ~/.s3/key_id"
        exit(1)

    if not secret:
        print >>stderr, "Must either specify -s {secret} or put secret into ~/.s3/key"
        exit(1)

    s3.init0(key_id = key_id, secret = secret)

    if object.count('%%') == object.count('%') * 2:
        # No strftime formatting instructions
        download(bucket, object.replace('%%', '%'), output)

    else:
        for target_date in date_range(date.today()):
            if download(bucket, target_date.strftime(object), output):
                break

    close(output)

def download(bucket, prefix, output):
    segment = 0
    while True:
        object = '%s.%03d' % (prefix, segment)

        # See if object exists
        r = s3.get(bucket, object)

        print >>stderr, 'trying', bucket, object

        if r.status != 200:
            r.close()
            return segment != 0

        while True:
            buf = r.read(4096)
            if not buf:
                break

            assert write(output, buf) == len(buf)

        r.close()
        segment += 1

def date_range(today):
    """Return a list of dates to try to download backups of:

    1. Tomorrow (for timezone issues)
    2. Today, and the last 7 days
    2. Last 4 Sundays
    3. 1st of the Month for the last 7 years

    Examples:

    >>> date_range(date(2008, 4, 24))[:18]
    [datetime.date(2008, 4, 25), datetime.date(2008, 4, 24),\
 datetime.date(2008, 4, 23), datetime.date(2008, 4, 22),\
 datetime.date(2008, 4, 21), datetime.date(2008, 4, 20),\
 datetime.date(2008, 4, 19), datetime.date(2008, 4, 18),\
 datetime.date(2008, 4, 13), datetime.date(2008, 4, 6),\
 datetime.date(2008, 4, 1), datetime.date(2008, 3, 30),\
 datetime.date(2008, 3, 23), datetime.date(2008, 3, 1),\
 datetime.date(2008, 2, 1), datetime.date(2008, 1, 1),\
 datetime.date(2007, 12, 1), datetime.date(2007, 11, 1)]
    """
    
    last_sunday = today - timedelta(today.weekday() + 1)
    assert last_sunday.weekday() == 6

    result = {today + timedelta(1): today + timedelta(1)}
    for dt in [today - timedelta(x) for x in range(7)] + \
              [last_sunday - x * WEEK for x in range(1, 5)] + \
              [months_ago(today, m) for m in range(7 * 12)]:
        result[dt] = dt

    result = result.keys()

    result.sort(reverse=True)

    return result

def months_ago(dt, m):
    """Returns the first day _m_ months ago.

    Examples::
        >>> months_ago(date(2008, 4, 24), 12)
        datetime.date(2007, 4, 1)

        >>> months_ago(date(2008, 4, 24), 4)
        datetime.date(2007, 12, 1)
    """
    months_ago = m % 12
    years_ago = m / 12

    if months_ago >= dt.month:
        months_ago -= 12
        years_ago += 1

    return date(dt.year - years_ago, dt.month - months_ago, 1)

def usage():
    print >>stderr, USAGE % globals()
    exit(1)

def get_s3_info(fn):
    try:
        return file(join(expanduser('~'), '.s3', fn)).read().strip()
    except IOError:
        return None

def _test():
    import doctest, s3cat
    return doctest.testmod(s3cat)

if __name__ == '__main__':
    # doctest inserts ansi-escape sequences into stdout...
    if len(argv) == 1:
        _test()

    main(argv)
