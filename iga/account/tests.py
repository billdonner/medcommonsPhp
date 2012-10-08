#!/usr/bin/env python

import doctest

import session
from test import ConsoleTest, Crawler

PASSWORD_PROTECTED = ['/',
		      '/admins/',
		      '/appliances/']

OPEN = ['/account/login',
	'/account/forgot']

class AccountTest(ConsoleTest):
    fixtures = ['test']

    def test_password_protected(self):
	for url in PASSWORD_PROTECTED:
	    r = self.get(url)
	    self.assertRedirects(r, '/account/login?next=%s' % url)

    def test_open(self):
	for url in OPEN:
	    r = self.get(url)
	    self.assertEquals(r.status_code, 200)
	    self.assertValidXHTML(r.content, name=url)

    def test_bad_login(self):
	"""Tests that unknown user and bad password both result
	in identical pages (well, except that the username is filled out)"""
	r = self.client.post('/account/login',
			     dict(username='root', password='badpassword'))
	self.assertContains(r, 'Unknown username/password')
	self.assertValidXHTML(r.content, name='/account/login')

	content = r.content

	r = self.client.post('/account/login',
			     dict(username='Xyzzy', password='welcome'))
	self.assertEquals(r.content.replace("Xyzzy", "root"), content)
	self.assertEquals(r.status_code, 200)

    def test_good_login(self):
	r = self.get('/')
	self.assertRedirects(r, '/account/login?next=/')

	r = self.client.post('/account/login',
			     dict(username='root', password='medcommons',
				  next='/'))
	self.assertRedirects(r, '/')

	r = self.get('/')
	self.assertContains(r, 'Configure and manage')

	for url in PASSWORD_PROTECTED:
	    r = self.get(url)
	    self.assertEquals(r.status_code, 200)
	    self.assertValidXHTML(r.content, name=url)

    def test_crawl_logged_out(self):
	crawler = Crawler(self)

	r = self.get('/')
	self.assertRedirects(r, '/account/login?next=/')

	crawler.visit('/account/login')
	crawler.crawl()

    def test_crawl_logged_in(self):
	crawler = Crawler(self)

	self.login(username='root', password='medcommons')

	crawler.visit('/')
	crawler.crawl()
 
class DocTest(ConsoleTest):
    def test(self):
	doctest.testmod(session)

