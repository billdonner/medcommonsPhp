#!/usr/bin/env python

import SOAPpy

WSDL = 'http://mcid.internal:1080/wsdl'

proxy = SOAPpy.WSDL.Proxy(WSDL)

if __name__ == '__main__':
    print proxy.next_mcid()
