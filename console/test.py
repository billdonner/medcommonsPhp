#!/usr/bin/env python

import doctest
import re
import xml.sax
from cgi import parse_qs
from urlparse import urljoin
from sys import stderr

import session

from django.conf import settings
from django.contrib.auth.models import User
from django.test import TestCase
from django.test.client import Client

from cStringIO import StringIO

XHTML_NS = u'http://www.w3.org/1999/xhtml'

IGNORE = [r'https://testserver/?',
          r'/account/logout',
          r'/ops/mpng.php\?.*',
          r'mailto:.*',
          r'http://www\.google\.com/apis/maps/signup\.html',
          r'http://aws\.amazon\.com']

IGNORE_RE = re.compile('^(' + ')|('.join(IGNORE) + ')$')

def make_sax_parser():
    """Makes a SAX parser with our particular feature set.

    We need namespaces, to be sure some pages are valid XHTML

    We don't want to download any external entities or dts, though.
    """
    p = xml.sax.make_parser()
    p.setFeature(xml.sax.handler.feature_namespaces, True)
    p.setFeature(xml.sax.handler.feature_external_ges, False)
    p.setFeature(xml.sax.handler.feature_external_pes, False)
    return p

class ConsoleTest(TestCase):
    def setUp(self):
        self.client = Client()

    def get(self, url):
        """Extends the TestCase.client, so urls with parameters will be
        correctly handled.
        """
        a = url.split('?', 1)
        args = {}

        if len(a) == 2:
            for k, v in parse_qs(a[1]).items():
                args[k] = v[0]

        return self.client.get(a[0], args)

    def login(self, username, password):
        r = self.client.post('/account/login',
                             dict(username = username, password = password))
        self.assertRedirects(r, '/')

    def assertRedirects(self, response, expected_redirect):
        self.assertEquals(response.status_code, 302)

        location = response.headers['Location']

        self.assertEquals(location, expected_redirect)

    def assertContains(self, response, text, count = 1, status_code = 200):
        self.assertEquals(response.status_code, status_code)
        self.assertEquals(count_substrings(text, response.content), count)

    def assertValidXHTML(self, content, name='unknown'):
        p = make_sax_parser()
        i = xml.sax.InputSource(name)
        i.setByteStream(StringIO(content))
        p.parse(i)

def match_type(t, e):
    """Match two content types

    Examples::
        >>> match_type('text/html', 'text/html')
        True

        >>> match_type('text/html', 'text')
        True

        >>> match_type('text/html', 'text/css')
        False

        >>>
    """
    t = {'application/x-javascript': 'text/javascript'}.get(t, t)

    t = t.split('/')
    e = e.split('/')

    for i in range(len(e)):
        if e[i] == '*':
           e[i] = t[i]

    return t[:len(e)] == e

class Crawler(xml.sax.ContentHandler):
    def __init__(self, test):
        self.queue = []
        self.types = {}
        self.test = test
        self.base = self.url = None

    def visit(self, url, type='text/html'):
        self.base = self.url = url
        r = self.test.get(url)

        if r.status_code == 302:
            self.add_url(r['Location'], '*')
            return

        if r.status_code != 200:
            print r.status_code, url
        self.test.assertEquals(r.status_code, 200)

        # get base content-type: text/html; charset=utf-8 => text/html
        content_type = r.headers['Content-Type'].split(';', 1)[0]

        if not match_type(content_type, type):
            print >>stderr, "Mismatched type", content_type, type

        self.test.assertTrue(match_type(content_type, type))

        if content_type == 'text/html':
            p = make_sax_parser()
            p.setContentHandler(self)

            i = xml.sax.InputSource(url)
            i.setByteStream(StringIO(r.content))

            p.parse(i)

    def crawl(self):
        while self.queue:
            url, referer = self.queue.pop(0)

            if IGNORE_RE.match(url):
                continue

            self.test.assertFalse(url.startswith('http://'))
            self.test.assertFalse(url.startswith('https://'))

            #print url, referer
            self.visit(url, self.types[url])

    def startElementNS(self, name, qname, attrs):
        # we're doing NS processing, must be valid XHTML
        ns, qname = name

        if ns == XHTML_NS:
            if qname == u'a':
                self.start_a(attrs)
            elif qname == u'img':
                self.start_img(attrs)
            elif qname == u'input':
                self.start_input(attrs)
            elif qname == u'link':
                self.start_link(attrs)
            elif qname == u'script':
                self.start_script(attrs)

    def start_a(self, attrs):
        qnames = attrs.getQNames()

        if u'href' in qnames:
            if u'type' in qnames:
                type = attrs.getValueByQName('type')
            else:
                type = 'text/html'

            self.add_url(attrs.getValueByQName('href'), type)
        else:
            assert u'name' in qnames

    def start_img(self, attrs):
        self.add_url(attrs.getValueByQName('src'), 'image')

    def start_input(self, attrs):
        if attrs.getValueByQName('type') == u'image':
            self.add_url(attrs.getValueByQName('src'), 'image')

    def start_link(self, attrs):
        self.add_url(attrs.getValueByQName('href'),
                     attrs.getValueByQName('type'))

    def start_script(self, attrs):
        self.add_url(attrs.getValueByQName('src'),
                     attrs.getValueByQName('type'))

    def add_url(self, url, type):
        url = urljoin(self.base, url)

        if url in self.types:
            self.test.assertTrue(match_type(self.types[url], type))
        else:
            self.queue.append((url, self.url))
            self.types[url] = type

def count_substrings(needle, haystack):
    count = start = 0
    while True:
        i = haystack.find(needle, start)
        if i == -1:
            return count
        start = i + len(needle)
        count += 1
