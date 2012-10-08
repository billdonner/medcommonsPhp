#!/usr/bin/env python
# session.py
# Copyright(c) 2006, MedCommons, Inc.
#
# Pre-requisites:
# * Python 2.3.5 or greater
# * pycrypto, version 2.0 or greater (http://www.amk.ca/python/code/crypto)
#
"""HTTP Session and URL redirect protection.

Encrypts, timestamps, and signs URL query strings.
"""
__author__ = "Terence Way"
__email__ = "tway@medcommons.net"
__version__ = "1.1: June 6, 2007"
MODULE = 'session'

import hmac, sha

from cgi import parse_qs
from time import time
from urlparse import urlparse

try:
    from os import urandom
except ImportError:
    urandom_file = file('/dev/urandom')
    def urandom(n): return urandom_file.read(n)

try:
    from base64 import urlsafe_b64encode, urlsafe_b64decode
except ImportError:
    from base64 import encodestring, decodestring
    def urlsafe_b64encode(str):
	str = encodestring(str)
	return ''.join(str.replace('+', '-').replace('/', '_').split('\n'))

    def urlsafe_b64decode(str):
	return decodestring(str.replace('_', '/').replace('-', '+'))

__all__ = ['timestamp_url', 'is_query_string_current',
	   'add_encrypted_query_string', 'get_encrypted_query_string',
	   'encrypt', 'decrypt',
	   'sign_query_string', 'is_signed_query_string_valid',
	   'urlsafe_b64encode', 'urlsafe_b64decode', 'urandom']

def timestamp_url(url, now = None):
    """Append a timestamp to a URL.

    If _now_ is None (the default) then the current time is used.

    Example:
    >>> timestamp_url('http://gateway001.medcommons.net/test', now=1145491135)
    'http://gateway001.medcommons.net/test?ts=1145491135'
    """
    if now is None: now = int(time())
    return add_to_url(url, 'ts', str(now))

def is_query_string_current(query_string, seconds = 30, now = None):
    """Tests if a particular URL is timestamped, and if the timestamp is
    within a range of seconds.

    Examples::
    >>> is_query_string_current('')
    False

    >>> is_query_string_current('foo=42&ts=1145491135&bar=43',
    ...                         now=1145491135)
    True

    >>> is_query_string_current('foo=42&ts=1145491135&bar=43',
    ...                         now=1145491166)
    False

    >>> is_query_string_current('foo=42&ts=1145491135&bar=43',
    ...                         now=1145491104)
    False
    """
    if now is None: now = int(time())
    d = parse_qs(query_string)
    try:
	return abs(now - int(d['ts'][-1])) <= seconds
    except KeyError:
	return False

def add_encrypted_query_string(url, query, key, iv = None):
    """Encrypt critical portions of a URL.

    If _iv_ is None (the default) then 16 random bytes are read from
    /dev/urandom.

    Examples:
    >>> iv = '87b69c288d34185d150372f6b399cec9'.decode('hex')
    >>> add_encrypted_query_string("http://gateway001.medcommons.net/test",
    ...                            "name=Jane+Hernandez&ss=293-16-5629",
    ...                            "secret", iv=iv)
    'http://gateway001.medcommons.net/test?enc=h7acKI00GF0VA3L2s5nOyWKYnQtjYImRmfWDuBQexvTJnNKSCjTKfV3QdmRezaac_Tuo_mibFR2eFe8d8epVsw=='
    """
    return add_to_url(url, 'enc', urlsafe_b64encode(encrypt(query, key, iv)))

def encrypt(data, key, iv=None):
    """Encrypt _data_ using AES (Rijndael-128) in CBC mode.

    If _iv_ isn't specified, or is None, uses system hard random number
    generator.

    Returns the iv appended with the encrypted data as a binary string.
    """
    from Crypto.Cipher import AES

    if iv is None: iv = urandom(16)

    p = AES.new(sha.new(key).digest()[:16], AES.MODE_CBC, iv)
    l = 16 - (len(data) % 16)

    # pad data a la RFC1423 (PKCS #5)
    data += chr(l) * l
    assert len(data) % 16 == 0

    return iv + p.encrypt(data)

def get_encrypted_query_string(query_string, key):
    """Retrieve a query string encrypted by add_encrypted_query_string, above.

    Example:
    >>> query_string = 'foo=42&enc=h7acKI00GF0VA3L2s5nOyWKYnQtjYIm' + \\
    ...                'RmfWDuBQexvTJnNKSCjTKfV3QdmRezaac_Tuo_mibF' + \\
    ...                'R2eFe8d8epVsw==&bar=43'
    >>> get_encrypted_query_string(query_string, "secret")
    'name=Jane+Hernandez&ss=293-16-5629'
    """
    d = parse_qs(query_string)
    try:
	return decrypt(urlsafe_b64decode(d['enc'][-1]), key)
    except KeyError:
	return None

def decrypt(data, key):
    """Decrypt data encrypted using encrypt() above.

    >>> decrypt(encrypt('this is a test', 'secret'), 'secret')
    'this is a test'
    """
    from Crypto.Cipher import AES
    p = AES.new(sha.new(key).digest()[:16], AES.MODE_CBC, data[:16])
    data = p.decrypt(data[16:])
    l = ord(data[-1])
    if 0 < l <= 16:
	return data[:-l]
    else:
	return ''

def sign_query_string(secret, url):
    """HMAC sign the query string portion of a URL with a secret.
    Tacks the hex digest to the end of the URL.

    Examples:
    >>> sign_query_string('secret', 'http://gateway001.medcommons.net/test')
    'http://gateway001.medcommons.net/test?hmac=25af6174a0fcecc4d346680a72b7ce644b9a88e8'

    >>> sign_query_string('secret',
    ...                   'http://gateway001.medcommons.net/test?foo=bar')
    'http://gateway001.medcommons.net/test?foo=bar&hmac=e82a4d9109fd6249b6f686e8aa93d668e96c9409'
    """
    query_string = urlparse(url)[4]
    p = hmac.new(secret, query_string, sha)
    return add_to_url(url, 'hmac', p.hexdigest())

def is_signed_query_string_valid(secret, query_string):
    """Tests whether a signed URL is valid or not.

    Examples::
    >>> is_signed_query_string_valid('secret', 'foo')
    False

    >>> query_string = 'hmac=25af6174a0fcecc4d346680a72b7ce644b9a88e8'
    >>> is_signed_query_string_valid('secret', query_string)
    True

    >>> query_string = 'foo=bar&hmac=e82a4d9109fd6249b6f686e8aa93d668e96c9409'
    >>> is_signed_query_string_valid('secret', query_string)
    True

    The hex is case-insensitive.
    >>> query_string = 'foo=bar&hmac=E82A4D9109FD6249B6F686E8AA93D668E96C9409'
    >>> is_signed_query_string_valid('secret', query_string)
    True

    Do not mix up the parameters: the _hmac_ field needs to be at the end.
    >>> query_string = 'hmac=E82A4D9109FD6249B6F686E8AA93D668E96C9409&foo=bar'
    >>> is_signed_query_string_valid('secret', query_string)
    False
    """
    i = query_string.rfind('hmac=')

    if i > 0:
	qs = query_string[:i-1]
    elif i == 0:
	qs = ''
    else:
	return False

    sig = query_string[i + 5:]

    try:
	sig = sig.decode('hex')
	p = hmac.new(secret, qs, sha)
	return p.digest() == sig
    except TypeError:
	return False

def add_to_url(url, name, value):
    """Adds a query parameter to a URL.

    Examples::
    >>> add_to_url('http://www.medcommons.net/test', 'foo', 'bar')
    'http://www.medcommons.net/test?foo=bar'

    >>> add_to_url('http://www.medcommons.net/test?foo=bar', 'x', 42)
    'http://www.medcommons.net/test?foo=bar&x=42'
    """
    if url.find('?') > 0:
	sep = '&'
    else:
	sep = '?'
    return '%s%c%s=%s' % (url, sep, name, value)

def _test():
    import doctest, session
    return doctest.testmod(session)

def _perf():
    url = 'http://gateway001.medcommons.net/directory/script?' + \
	'a=5&b=6&c=7&d=8&e=test+string+padded+for+length'

    query_string = 'name=Jane+Hernandez&ssn=529-62-7284'

    for i in xrange(1000):
	key = 'secret_%d' % i
	url = add_encrypted_query_string(url, query_string, key)
	url = timestamp_url(url)
	url = sign_query_string(key, url)
	assert is_query_string_current(url)
	assert is_signed_query_string_valid(key, url.split('?', 1)[1])

if __name__ == '__main__':
    failed, ntests = _test()
    if failed == 0:
	print 'All tests passed.'
    else:
	print '%d out of %d tests failed.' % (failed, ntests)
    _perf()
