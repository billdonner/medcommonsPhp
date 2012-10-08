#!/usr/bin/env python

import session
from test import ConsoleTest, Crawler

class AccountTest(ConsoleTest):
    fixtures = ['test']

    def test_create_admin(self):
	crawler = Crawler(self)

	self.login(username='root', password='medcommons')

	crawler.visit('/admins/')
	crawler.crawl()

	self.assertEquals(crawler.types['/admins/create'],
			  'text/html')

	test_admin = dict(username = 'bob',
			  first_name = 'Bob',
			  last_name = 'Marley',
			  email = 'test',
			  pw1 = 'welcome',
			  pw2 = 'welcome')

	r = self.client.post('/admins/create', test_admin)
	self.assertEquals(r.status_code, 200)
	self.assertValidXHTML(r.content)
	self.assertContains(r, 'Enter a valid e-mail')

	test_admin['email'] = 'test@medcommons.net'
	test_admin['pw2'] = 'mismatch'

	r = self.client.post('/admins/create', test_admin)
	self.assertEquals(r.status_code, 200)
	self.assertValidXHTML(r.content)
	self.assertContains(r, 'Must match password above')

	test_admin['pw2'] = test_admin['pw1']
	r = self.client.post('/admins/create', test_admin)
	self.assertRedirects(r, '.')

	r = self.client.get('/admins/')
	self.assertEquals(r.status_code, 200)
	self.assertValidXHTML(r.content)
	self.assertContains(r, 'Marley')

	r = self.client.get('/account/logout')
	r = self.client.post('/account/login',
			     dict(username='bob', password='welcome'))
	self.assertRedirects(r, '/')
