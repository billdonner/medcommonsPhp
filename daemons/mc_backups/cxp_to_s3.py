#!/usr/bin/env python
#
# cxp_to_s3.py
# Copyright(c) 2007, MedCommons, Inc.

"""
Uses CXP GET to get CCRs from MedCommons gateways,
archives, compresses, and encrypts the data, then
puts the data onto Amazon S3.
"""

__author__ = "Terence Way"
__email__ = "tway@medcommons.net"
__version__ = "1.1: Feb 8, 2008"

from os import walk, rmdir, remove
from os.path import expanduser, isdir, join
from s3 import put_data
from sys import argv, exit, stderr

from pipes import popen

import socket # for error declaration

DEFAULT_BUCKET = 'backups.medcommons.net'
DEFAULT_HOST = 'localhost'
DEFAULT_KEYFILE = join(expanduser('~'), '.aes-key')

OBJECT_FORMAT = '%(storageId)s.%(documentGuid)s.tar.bz2.enc.%(i)03d'

REPOSITORY = '/opt/gateway/data/Repository'

ARCHIVE_CMD = ['tar',
               '--create',
               '--file', '-']   # create, standard output

COMPRESS_CMD = ['bzip2',
                '--stdout',     # output to standard out
                '--compress',   # force compression
                '--quiet']      # suppress noncritical error messages

ENCRYPT_CMD = ['openssl',
               'enc',
               '-e',
               '-aes-256-cbc',
               '-kfile']

SEGMENT_SIZE = 16 * 1024 * 1024

import cxp, mtom

status = None

def main(argv):
    bucket = DEFAULT_BUCKET
    host = DEFAULT_HOST
    kfile = DEFAULT_KEYFILE
    guid = None
    storageId = None

    args = argv[1:]

    while args:
        if args[0] == '-bucket' and len(args) > 1:
            bucket = args[1]
            args = args[2:]

        elif args[0] == '-host' and len(args) > 1:
            host = args[1]
            args = args[2:]

        elif args[0] == '-C' and len(args) > 1:
            args = file(args[1]).read().split() + args[2:]

        elif args[0] == '-kfile' and len(args) > 1:
            kfile = expanduser(args[1])
            args = args[2:]

        elif guid is None:
            guid = args[0]
            args = args[1:]

        elif storageId is None:
            storageId = args[0]
            args = args[1:]

        else:
            usage()

    if storageId is None:
        usage()

    cxp_to_s3(guid, storageId, bucket = bucket, host = host, kfile = kfile)

CXP_OK, CXP_TEMP_ERR, CXP_PERM_ERR = range(3)

def cxp_to_s3(guid, storageId, host=DEFAULT_HOST, bucket=DEFAULT_BUCKET,
              kfile=DEFAULT_KEYFILE, amazon_user_token=None,
              amazon_product_token=None):
    global status

    if 0:
        params = cxp.params.copy()

        params['host'] = host

        params['documentGuid'] = guid
        params['storageId'] = storageId

        if not status:
            status = mtom.status('CXP get')

        try:
            cxp.get(params, status).cxp(dict(elements = [dict(guid = guid)]))
        except socket.error:
            return CXP_TEMP_ERR
        except:
            from traceback import print_exc
            print_exc(file = stderr)
            return CXP_PERM_ERR
    else:
        params = {'host': host, 'documentGuid': guid, 'storageId': storageId}

    try:
        inp = popen([ ARCHIVE_CMD + ['--directory', '%s/%s' % (REPOSITORY, storageId),
                                     '%s' % guid,
                                     '%s.properties' % guid],
                      COMPRESS_CMD,
                      ENCRYPT_CMD + [kfile] ])

        i = 0

        while True:
            b = read_fully(inp, SEGMENT_SIZE)

            if not b:
                break

            params['i'] = i

            put_data(bucket, OBJECT_FORMAT % params, b,
                     user_token=amazon_user_token,
                     product_token=amazon_product_token)

            i += 1
    except:
        from traceback import print_exc
        print_exc(file = stderr)
        return CXP_PERM_ERR

    #clean(guid)

    return CXP_OK

def clean(d):
    """An 'rm -rf' for a file or directory.

    Recursively cleans out a directory, removing all files.
    If _d_ specifies a file, simply removes the file.
    """
    assert d[0] not in ['/', '\\']

    if isdir(d):
        for root, dirs, files in walk(d, topdown = False):
            for name in files:
                remove(join(root, name))
            for name in dirs:
                rmdir(join(root, name))
        rmdir(d)
    else:
        remove(d)

USAGE = """Usage: python s3put.py {guid} {mcid}

Options:
    -C {filename}          - read options from file
    -bucket {bucket-name}  - S3 bucket, defaults to '%(DEFAULT_BUCKET)s'
    -host {hostname}       - CXP gateway, defaults to '%(DEFAULT_HOST)s'
    -kfile {enc key file}  - AES key file, defaults to '%(DEFAULT_KEYFILE)s'
"""

def usage():
    print >> stderr, USAGE % globals()
    exit(1)
    
def read_fully(inp, size):
    result = ''

    while size:
        b = inp.read(size)
        if not b: break

        result += b
        size -= len(b)

    return result

if __name__ == '__main__':
    main(argv)
