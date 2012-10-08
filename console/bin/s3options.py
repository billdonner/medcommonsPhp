#!/usr/bin/env python

from sys import argv, path
from os import getenv, putenv, environ
from os.path import exists, dirname, join

# Set up so Django db settings can be used
if exists(join(dirname(argv[0]), '..', 'settings.py')):
	path.append(join(dirname(argv[0]), '..'))

DJANGO_SETTINGS_MODULE = 'DJANGO_SETTINGS_MODULE'
if not getenv(DJANGO_SETTINGS_MODULE):
	environ[DJANGO_SETTINGS_MODULE] = 'settings'
	putenv(DJANGO_SETTINGS_MODULE, environ[DJANGO_SETTINGS_MODULE])

from config.models import MCProperty

def main():
    print '-a', get_key_id(),
    print '-s', get_secret(),
    print get_bucket()

def get_secret():
    return get('acS3Secret')

def get_key_id():
    return get('acS3Key_ID')

def get_bucket():
    return get('acS3Bucket')

def get(n):
    try:
        p = MCProperty.objects.get(property=n)
        return p.value
    except MCProperty.DoesNotExist:
        return None

if __name__ == '__main__':
    main()
