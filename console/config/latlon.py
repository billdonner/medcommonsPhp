#!/usr/bin/env python

import re

__all__ = ['parse_latitude', 'parse_longitude']

GROUP = re.compile('(N|E|S|W|\"|\')')

def parse_latitude(s):
	"""Parses a latitude.

	Ignores whitespace::
		>>> parse_latitude("  10 n  45.33 '  ")
		10.7555

		>>> parse_latitude("10n45.33'")
		10.7555

	Ignores case::
		>>> parse_latitude("10N 45.33'")
		10.7555
		>>> parse_latitude("10n45.33'")
		10.7555

	Accepts plain old floating point numbers::
		>>> parse_latitude("  10.7555 ")
		10.7555

	Otherwise, the latitude must be followed by N or S::
		>>> parse_latitude("  10N")
		10.0
		>>> parse_latitude("  10S")
		-10.0

	Use ' (single-quote) to indicate minutes, and " (double-quote)
	to indicate seconds::
		>>> parse_latitude("  10.7555N")
		10.7555
		>>> parse_latitude("  10 N 37.5'")
		10.625
		>>> parse_latitude("  10 N 37' 30\\"")
		10.625

	Latitudes are in the range -90.0 (90S) to 90.0 (90N).
	Values out of this range result in an exception::
		>>> parse_latitude("91N")
		Traceback (most recent call last):
		...
		ValueError: latitude must be in range 90S to 90N
	"""
	x = _parse(s, 'S', 'N')

	if -90.0 <= x <= 90.0:
		return x
	else:
		raise ValueError("latitude must be in range 90S to 90N")

def parse_longitude(s):
	"""Parse a longitude.

	Ignores whitespace::
		>>> parse_longitude("  10 e  45.33 '  ")
		10.7555

		>>> parse_longitude("10e45.33'")
		10.7555

	Ignores case::
		>>> parse_longitude("10E 45.33'")
		10.7555
		>>> parse_longitude("10e45.33'")
		10.7555

	Accepts plain old floating point numbers::
		>>> parse_longitude("  10.7555 ")
		10.7555

	Otherwise, the longitude must be followed by E or W::
		>>> parse_longitude("  10E")
		10.0
		>>> parse_longitude("  10W")
		-10.0

	Use ' (single-quote) to indicate minutes, and " (double-quote)
	to indicate seconds::
		>>> parse_longitude("  10.7555E")
		10.7555
		>>> parse_longitude("  10 E 37.5'")
		10.625
		>>> parse_longitude("  10 E 37' 30\\"")
		10.625

	Longitudes are in the range -180.0 (180W) to 180.0 (180E).
	Values out of this range result in an exception::
		>>> parse_longitude("180W 23'")
		Traceback (most recent call last):
		...
		ValueError: longitude must be in range 180W to 180E
	"""
	x = _parse(s, 'W', 'E')

	if -180 <= x <= 180.0:
		return x

	else:
		raise ValueError("longitude must be in range 180W to 180E")

def _parse(s, neg, pos):
	"""Liberal parser of latitudes and longitudes"""

	# remove whitespace, normalize to uppercase
	s = ''.join(s.split()).upper()

	try:
		return float(s)
	except ValueError:
		pass

	sign = 1.0
	degrees = minutes = seconds = 0.0
	value = 0.0

	for x in GROUP.split(s):
		if x == '':
			pass
		elif x == neg:
			sign = -1.0
			degrees = value
		elif x == pos:
			sign = 1.0
			degrees = value
		elif x == "'":
			minutes = value
		elif x == '"':
			seconds = value
		else:
			value = float(x)

	return sign * (degrees + minutes / 60.0 + seconds / 3600.0)

def _test():
	import doctest, latlon
	return doctest.testmod(latlon)

if __name__ == '__main__':
	_test()
