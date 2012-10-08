#!/usr/bin/env python

import SOAPpy

URL = 'http://mcid.internal:1080/mcid'
NS  = 'http://www.medcommons.net/mcid'

mcid_generator = SOAPpy.SOAPProxy(URL, namespace=NS)

if __name__ == '__main__':
    print mcid_generator.next_mcid()
