#!/usr/bin/env python

from sys import argv
import SOAPpy

URL = 'http://localhost:1081'
NS = 'http://www.medcommons.net/globals'

def main():
    global generator
    
    generator = SOAPpy.SOAPProxy(URL, namespace = NS)

    for tracking_number in get_batch(generator.next_tracking_number_set):
        print pretty_tracking_number(tracking_number)

def get_batch(g):
    next, params = g(argv[1])

    result = []

    for x in xrange(params.n):
        for y in xrange(params.leap):
            result.append(params.base + next * params.leap + y)

        next = (params.A * next + params.B) & params.mask

    return result

def pretty_tracking_number(n):
    n = '%012d' % n
    return '%s-%s-%s' % (n[0:4], n[4:8], n[8:12])

if __name__ == '__main__':
    main()
    
