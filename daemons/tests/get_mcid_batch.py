#!/usr/bin/env python

from sys import argv
import SOAPpy

URL = 'http://localhost:1081'
NS = 'http://www.medcommons.net/globals'

def main():
    global generator

    generator = SOAPpy.SOAPProxy(URL, namespace = NS)

    for mcid in get_batch(generator.next_mcid_set):
        print pretty_mcid(mcid)

def get_batch(g):
    next, params = g(argv[1])
    
    result = []

    for x in xrange(params.n):
        for y in xrange(params.leap):
            result.append(params.base + next * params.leap + y)

        next = (params.A * next + params.B) & params.mask

    return result

def pretty_mcid(mcid):
    mcid = '%016d' % mcid
    return '%s-%s-%s-%s' % (mcid[0:4], mcid[4:8], mcid[8:12], mcid[12:16])

if __name__ == '__main__':
    main()
    
