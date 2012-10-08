#!/usr/bin/env python
#
# mc-backup-to-s3
# Copyright(c) 2008, Medcommons Inc.
#
"""
"""

__author__ = "Terence Way"
__email__ = "tway@medcommons.net"
__version__ = '1.0: April 23, 2008'

from sys import argv, exit, stderr, stdin
from os.path import expanduser, join

import s3

DEFAULT_SIZE = 50000000 # 50M

SIZES = [('K', 1000),
         ('M', 1000000),
         ('G', 1000000000)]

USAGE = """Usage: python s3backup.py {bucket} {prefix}

Copies standard input to an S3 bucket.  Segments the input into multiple
files, each file suffixed with .000, .001, etc.

Options:
  -a {key_id}   Amazon Key ID: defaults to contents of ~/.s3/key_id
  -s {secret}   Amazon Secret: defaults to contents of ~/.s3/key
  -l {length}   Maximum length of each object: defaults to %(DEFAULT_SIZE)s
                Use common suffixes like 'G': '5G' is 5000000000

  -C {filename} Read command line options from file

  -mcproperties Pull S3 bucket, key_id, and secret from mcx.mcproperties table
"""

def main(argv):
    args = argv[1:]

    # Defaults
    key_id = get_s3_info('key_id')
    secret = get_s3_info('key')
    size = DEFAULT_SIZE

    bucket = None
    prefix = None

    while args:
	a = args.pop(0)

        if a == '-C' and args:
            filename = args.pop(0)
            args = file(filename).read().split() + args

        elif a == '-a' and args:
            key_id = args.pop(0)

        elif a == '-s' and args:
            secret = args.pop(0)

        elif a == '-l' and args:
            size = parse_size(args.pop(0))

        elif a == '-mcproperties':
            import s3options
            bucket = s3options.get_bucket()
            key_id = s3options.get_key_id()
            secret = s3options.get_secret()

        elif a.startswith('-'):
            usage()

        elif bucket is None:
            bucket = a

        elif prefix is None:
            prefix = a

        else:
            usage()

    if not prefix or not bucket:
        usage()

    if not key_id:
        print >>stderr, "Must either specify -a {key_id} or put key_id into ~/.s3/key_id"
        exit(1)

    if not secret:
        print >>stderr, "Must either specify -s {secret} or put secret into ~/.s3/key"
        exit(1)

    s3.init0(key_id = key_id, secret = secret)

    segment = 0
    while True:
        block = read_fully(stdin, size)
        if not block:
            break

        object = prefix + '.%03d' % segment

        s3.put_data(bucket, object, block)
        print object, len(block)

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

def read_fully(inp, size):
    result = []

    while size:
        b = inp.read(size)
        if not b: break

        result.append(b)
        size -= len(b)

    return ''.join(result)

def _test():
    import doctest, s3backup
    return doctest.testmod(s3backup)

if __name__ == '__main__':
    _test()
    main(argv)
