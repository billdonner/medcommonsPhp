#!/usr/bin/env python

import fnmatch, hashlib, os, os.path, sys, time

vers = sys.argv[1]
arch = 'i386'
va = {'vers':vers, 'arch':arch}

indexhead = """\
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>%(title)s</title>
</head>
<body>
  <table width="100%%">
    <tr><th>Name</th><th>Date</th><th>Size</th><th>SHA-1</th><th>Links</th></tr>
"""

indexbodyparts = """\
    <tr><td>%(name)s</td><td>%(date)s</td><td align="right">%(size)s</td><td align="center">%(sha1)s</td><td><a href="%(href)s">download</a> | <a href="%(href)s?torrent">torrent</a> | <a href="%(partshref)s">parts</a></td></tr>
"""

indexbodynoparts = """\
    <tr><td>%(name)s</td><td>%(date)s</td><td align="right">%(size)s</td><td align="center">%(sha1)s</td><td><a href="%(href)s">download</a> | <a href="%(href)s?torrent">torrent</a></td></tr>
"""

indextail = """\
  </table>
</body>
</html>
"""

partshead = """\
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>%(title)s</title>
</head>
<body>
  <table width="100%%">
    <tr><th>Name</th><th>Date</th><th>Size</th><th>SHA-1</th><th>Link</th></tr>
"""

partsbody = """\
<tr><td>%(name)s</td><td>%(date)s</td><td align="right">%(size)s</td><td align="center">%(sha1)s</td><td><a href="%(href)s">download</a></td></tr>
"""

partstail = """\
  </table>
</body>
</html>
"""

indexname = 'index.html' % va
indexhref = 'http://appliance.medcommons.net/%(vers)s/%(name)s' % {'vers':vers,'name':indexname}

vmwpartsname = 'VMWare.html' % va
vmwpartshref = 'http://appliance.medcommons.net/%(vers)s/%(name)s' % {'vers':vers,'name':vmwpartsname}

isopartsname = 'ISO.html' % va
isopartshref = 'http://appliance.medcommons.net/%(vers)s/%(name)s' % {'vers':vers,'name':isopartsname}

def formatdate(d):
    return time.strftime('%Y-%m-%d %H:%M%Z', time.localtime(d))

def formatsize(s):
    M = 1024 * 1024
    if s > M * 10:
        return '%dM' % (s / M)
    else:
        return '%dK' % (s / 1024)

def makehash(name):
    m = hashlib.sha1()
    fd = open(name, 'rb')
    while True:
        buf = fd.read(0x10000)
        if not buf:
            break
        m.update(buf)
    fd.close()
    return m.hexdigest()

def makeparams(name, params):
    date = os.stat(name).st_mtime
    size = os.stat(name).st_size
    params['name'] = name
    params['href'] = 'http://appliance.medcommons.net/%(vers)s/%(name)s' % {'vers':vers,'name':name}
    params['date'] = formatdate(date)
    params['size'] = formatsize(size)
    params['sha1'] = makehash(name)
    return params

fd = open(indexname, 'wb')
fd.write(indexhead % {'title':'MedCommons Appliance %(vers)s %(arch)s' % va})

fn = 'MedCommons-%(vers)s-%(arch)s-vmwarevm.7z' % va
if os.path.exists(fn):
    fd.write(indexbodyparts % makeparams(fn, {'partshref':vmwpartshref}))
fn = 'MedCommons-%(vers)s-%(arch)s-DVD.iso' % va
if os.path.exists(fn):
    fd.write(indexbodyparts % makeparams(fn, {'partshref':isopartshref}))
fn = 'MedCommons-%(vers)s-%(arch)s-rescuecd.iso' % va
if os.path.exists(fn):
    fd.write(indexbodynoparts % makeparams(fn, {}))

fd.write(indextail)
fd.close()

fd = open(vmwpartsname, 'wb')
fd.write(partshead % {'title':'MedCommons Appliance %(vers)s %(arch)s VMWare (Parts)' % va})
v = fnmatch.filter(os.listdir('.'), 'MedCommons-%(vers)s-%(arch)s-vmwarevm.7z.??' % va)
v.sort()
for i in v:
    fd.write(partsbody % makeparams(i, {}))
fd.write(partstail)
fd.close()

fd = open(isopartsname, 'wb')
fd.write(partshead % {'title':'MedCommons Appliance %(vers)s %(arch)s ISO (Parts)' % va})
v = fnmatch.filter(os.listdir('.'), 'MedCommons-%(vers)s-%(arch)s-DVD.iso.??' % va)
v.sort()
for i in v:
    fd.write(partsbody % makeparams(i, {}))
fd.write(partstail)
fd.close()
