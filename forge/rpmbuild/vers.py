#!/usr/bin/env python

import re, sys

def repl(mo):
    global vers
    vers = mo.group(2) + str(int(mo.group(3)) + 1)
    return mo.group(1) + vers

fd = open(sys.argv[1], 'rb')
s = fd.read()
fd.close()

s, n = re.subn('(?m)^(Version:[ \t]*)(\d+\.\d+\.)(\d+)[ \t]*$', repl, s, 1)
if n:
    fd = open(sys.argv[1], 'wb')
    fd.write(s)
    fd.close()
    print vers
