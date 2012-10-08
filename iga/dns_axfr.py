#!/usr/bin/env python
# inventory/dumpdns.py
# Copyright(c) 2008, Medcommons Inc.

"""Given a list of domains, output name->addr and addr->name csvs
"""

import csv
import re

from os import popen
from sys import argv, stderr, exit

from socket import getfqdn

ADDR_RE = re.compile(r'([a-zA-Z0-9\.\_\-]+)\s[0-9]+\sIN\s([A-Z]+)\s([0-9\.A-Za-z\_\-]+)')
NAMES = {}
CNAMES = {}
ADDRS = {}

def main(args):
    if not args:
        print >>stderr, "Usage: python dumpdns.py {domain}..."
        exit(1)

    while args:
        a = args.pop(0)
        axfr(a, names=NAMES, cnames=CNAMES, addrs=ADDRS)

    cnames = CNAMES.copy()
    for cname, names in cnames.items():
        for name in names:
            if name in ADDRS:
                for addr in ADDRS[name]:
                    NAMES.setdefault(addr, []).append(cname)
                    ADDRS.setdefault(cname, []).append(addr)
                CNAMES.pop(cname)

    addrs = NAMES.keys()
    addrs.sort()

    o = csv.writer(file('names.csv', 'w'))
    o.writerow(['addr', 'name'])

    for addr in addrs:
        for name in NAMES[addr]:
            o.writerow([addr, name])

    names = ADDRS.keys() + CNAMES.keys()
    names.sort()

    o = csv.writer(file('addrs.csv', 'w'))
    o.writerow(['name', 'addr'])

    for name in names:
        if name in CNAMES:
            for cname in CNAMES[name]:
                o.writerow([name, cname])
        else:
            for addr in ADDRS[name]:
                o.writerow([name, addr])

def axfr(domain=None, names=None, cnames=None, addrs=None):
    domain = domain or '.'.join(getfqdn().split('.')[1:])
    names = names or {}
    cnames = cnames or {}
    addrs = addrs or {}

    for line in popen('dig @ns1.medcommons.net %s AXFR' % domain):
        for name, type, addr in ADDR_RE.findall(line):
            if name.endswith('.'):
                name = name[:-1]

            if type == 'CNAME':
                cnames.setdefault(name, []).append(addr)

            if type != 'A':
                continue

            names.setdefault(addr, []).append(name)
            addrs.setdefault(name, []).append(addr)

    return names, cnames, addrs

if __name__ == '__main__':
    main(argv[1:])
