#!/usr/bin/env python

from sys import argv, exit, stdout, stderr, exc_info
from time import sleep

import SOAPpy

URL = 'http://globals.medcommons.net/globals/'
NS = 'http://www.medcommons.net/globals'

def main(args):
    global generator

    all = {}

    if len(args) == 3:
	url = args[2]
    elif len(args) != 2:
	print >>stderr, "Usage: python test_mcid_set.py {appliance} [url]"
	exit(1) 
    else:
	url = URL

    generator = SOAPpy.SOAPProxy(url, namespace = NS)

    print "This allocates a HUGE number of MCIDs!"
    raw_input("If this is okay, press RETURN: ")

    i = 72
    count = 0

    while True:
        try:
            while True:
                batch = get_batch(generator.next_mcid_set)

                count += len(batch)
                for n in batch:
                    assert n not in all
                    all[n] = True

                stdout.write('.')
                stdout.flush()
                if i == 0:
                    print
                    i = 72
                else:
                    i -= 1

        except AssertionError:
            print
            print 'Collision! %s' % n
            break
        except KeyboardInterrupt:
            print
            print '%d numbers allocated with no collisions' % count
            break
        except:
            print >>stderr, exc_info()[0]

            sleep(1)

def get_batch(g):
    next, params = g(argv[1])

    result = []

    for x in xrange(params.n):
        for y in xrange(params.leap):
            result.append(params.base + next * params.leap + y)

        next = (params.A * next + params.B) & params.mask

    return result

if __name__ == '__main__':
    main(argv)
