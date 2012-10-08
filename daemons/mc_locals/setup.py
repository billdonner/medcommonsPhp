#!/usr/bin/env python

from distutils.core import setup

DESC="MedCommons REST/SOAP server for allocating MCIDs and tracking numbers"

LONG="""
The mc_locals server listens on port 1080 of the localhost (127.0.0.1)
interface, responding to requests for individual MedCommons IDs (MCIDs)
and tracking numbers.  These numbers are allocated in batch by
mc_globals, which run on central, globally-accessible servers run by
MedCommons.
"""

USER = 'mc_locals'

from sys import argv
from os import system

def add_user():
    try:
        from pwd import getpwnam
        getpwnam(USER)
    except ImportError:
        pass
    except KeyError:
        print 'Adding user', USER
        cmd = ['/usr/sbin/useradd',       # standard user add command
               '-c "MC local allocator"', # comment
               '-M',                      # do not make home dir
               '-s /sbin/nologin',        # shell
               '-d /usr/etc',             # home directory
               USER]

        system(' '.join(cmd))

def services():
    print 'Adding service mc_locals'
    system('/sbin/chkconfig --add mc_locals')

setup(name='mc_locals',
      version='1.4',
      description=DESC,
      long_description=LONG,

      author='Terence Way',
      author_email='tway@medcommons.net',
      url='http://www.medcommons.net',

      scripts=['mc_locals.py'],

      data_files=[('etc', ['mc_locals.rc']),
                  ('/etc/init.d', ['init.d/mc_locals'])])

print argv

if __name__ == '__main__' and 'install' in argv:
    add_user()
    services()
