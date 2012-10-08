#!/usr/bin/env python
# vim: tabstop=4 expandtab shiftwidth=4 softtabstop=4
#

USAGE = """
Usage: python deploy.py {options}...

Options:
    safe         does not delete files from target that don't exist in source
                 default, use 'clean' to disable

    clean        deletes files from target that don't exist in source

    verbose      logs all activity

    pretend      just logs activity, doesn't actually modify any files

    base {dir}   changes output, defaults to %(BASE)s
"""

from filecmp import dircmp
from os import remove, makedirs
from os.path import exists, join, isdir
from fnmatch import fnmatch
from shutil import rmtree, copy, copytree
from sys import argv, stderr, exit

VERBOSE = False
PERFORM = True
SAFE = True
BASE = '/var/www'

EXCLUDE = ['*~']

def main(args):
    global SAFE, VERBOSE, PERFORM, BASE

    while args:
        arg = args.pop(0)
        if arg == 'safe':
            SAFE = True

        elif arg == 'clean':
            SAFE = False

        elif arg == 'pretend':
            VERBOSE = True
            PERFORM = False

        elif arg == 'verbose':
            VERBOSE = True

        elif arg == 'base' and args:
            BASE = args.pop(0)

        else:
            print >>stderr, USAGE % globals()
            exit(1)

    html = join(BASE, 'html')
    include = join(BASE, 'php')

    for f in ['appsrvstatus.php', 'centralstatus.php', 'info.php', 'uinfo.php']:
        deploy_file('.', f, html)

    for d in ['acct', 'ca', 'groups', 'ops', 'secure', 'api', 'openid', 'modpay','yui', 'mod']:
        dst = join(html, d)
        deploy(d, dst, exclude=EXCLUDE)

    deploy('site/images', join(html, 'images'), exclude=EXCLUDE)
    deploy('site/css', join(html, 'css'), exclude=EXCLUDE)
    deploy('site', html, exclude=EXCLUDE)
    deploy('site', join(html, 'site'), exclude=EXCLUDE)
    deploy('include', include, exclude=EXCLUDE + ['*.local.inc.php',
                                                  'local_settings.php'])

def deploy(from_dir, to_dir, exclude=[]):
    d = dircmp(from_dir, to_dir)

    if PERFORM and not exists(to_dir):
        makedirs(to_dir)

    for fn in d.right_only:
        if not excluded(fn, exclude):
            delete(to_dir, fn)

    for fn in d.left_only + d.diff_files:
        if not excluded(fn, exclude):
            deploy_file(from_dir, fn, to_dir)

    for fn in d.common_dirs:
        if fn != '.svn' and not excluded(fn, exclude):
            deploy(join(from_dir, fn), join(to_dir, fn))

def excluded(fn, exclude_list):
    for ex in exclude_list:
        if fnmatch(fn, ex):
            return True
    return False

def delete(dir, fn):
    path = join(dir, fn)

    if SAFE:
        return

    if VERBOSE:
        print 'rm -rf', path

    if PERFORM:
        if isdir(path):
            rmtree(path)
        else:
            remove(path)

def deploy_file(from_dir, fn, to_dir):
    if fn == '.svn':
        return

    from_path = join(from_dir, fn)
    to_path = join(to_dir, fn)

    if VERBOSE:
        print 'cp', from_path, to_dir

    if PERFORM:
        if isdir(from_path):
            copytree(from_path, to_path)
        else:
            if not exists(to_dir):
                makedirs(to_dir)
            copy(from_path, to_dir)

if __name__ == '__main__':
    main(argv[1:])
