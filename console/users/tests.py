#!/usr/bin/env python

import session
from test import ConsoleTest, Crawler

class UserTest(ConsoleTest):
    fixtures = ['test']

    def test_create_user(self):
	crawler = Crawler(self)

	self.login(username='root', password='medcommons')

	crawler.visit('/users/')
	crawler.crawl()

	self.assertEquals(crawler.types['/users/create'],
			  'text/html')

	r = self.client.get('/users/create')
	self.assertEquals(r.status_code, 200)
	self.assertValidXHTML(r.content)
	self.assertContains(r, 'MCID')

	mcid = r.context[0]['mcid']
	test_user = dict(mcid = mcid,
			 first_name = 'Bob',
			 last_name = 'Marley')

	r = self.client.post('/users/create', test_user)
	self.assertRedirects(r, 'user?mcid=' + mcid)

	r = self.client.get('/users/')
	self.assertEquals(r.status_code, 200)
	self.assertValidXHTML(r.content)
	self.assertContains(r, 'Marley')

	crawler = Crawler(self)
	crawler.visit('/')
	crawler.crawl()
