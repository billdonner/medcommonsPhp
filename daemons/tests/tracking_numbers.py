#!/usr/bin/env python

"""
Tracking numbers are numbers of the form:
    XXXX-XXXX-XXXX

Removing the formatting, they are 12 decimal digits.  Numbers prefixed
by '0' are reserved for testing.  Numbers prefixed by the digits '1'
through '9' are production.

This means we need to allocate numbers in the range 100,000,000,000
through 999,999,999,999.

>>> log(10 ** 12 - 10 ** 11, 2)
39.711134045203302

This gives us around 39.71 bits of data.

The Linear Congruential Method (LCM) of generating psuedo-random
numbers will generate a sequence of numbers, with a huge cycle::

    V(n + 1) = (A * V(n) + B) % M

'Numerical Recipes in C' advocates an LCM generator of the form:

    A = 1664525
    B = 1013904223
    M = 2 ** 32

This will generate *every* number in the range 0..2**32 before cycling.

So, this gives us 32 out of 39.71 bits.

The central Tracking Number allocator can give out 32 bits of psuedo-
random data.  That leaves:

>>> log(10 ** 12 - 10 ** 11, 2) - 32
7.7111340452033019

Around 7.71 bits of data to be generated locally

>>> round(2 ** 7.71, 2)
209.38

So, local Tracking Number allocators can generate 209 tracking numbers
before going back to the central allocator for the next batch of data.

The algorithm for generating tracking numbers can now be defined::

    For ever:
        rand = [lcm A=1664525 B=1013904223 M=2^32]
        base = rand * 209 + 100000000000

        loop i from 0 to 209:
            tracking number = base + i

With this system, we can allocate one thousand (1000) tracking numbers
per second for 28 years before cycling.

>>> FULL_RANGE = 10 ** 12
>>> TESTING_RANGE = 10 ** 11
>>> PRODUCTION_RANGE = FULL_RANGE - TESTING_RANGE
>>> SECONDS_PER_YEAR = 60 * 60 * 24 * 366
>>> PRODUCTION_RANGE / (SECONDS_PER_YEAR * 1000)
28L

"""

from math import log

def _test():
    import doctest, tracking_numbers
    return doctest.testmod(tracking_numbers)

if __name__ == '__main__':
    _test()
