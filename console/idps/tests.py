#!/usr/bin/env python

import session
from test import ConsoleTest, Crawler

class IdpTest(ConsoleTest):
    fixtures = ['test']

    def test_create_idp(self):
	crawler = Crawler(self)

	self.login(username='root', password='medcommons')

	crawler.visit('/idps/')
	crawler.crawl()

	self.assertEquals(crawler.types['/idps/add'],
			  'text/html')

	test_idp = dict(source_id = 'aol',
			name = 'AOL',
			domain = 'www.aol.com',
			format = 'test')

	r = self.client.post('/idps/add', test_idp)

	self.assertEquals(r.status_code, 200)
	self.assertValidXHTML(r.content)
	self.assertContains(r, 'Must have one percent')

	test_idp['format'] = 'http://openid.aol.com/%'

	r = self.client.post('/idps/add', test_idp)
	self.assertRedirects(r, '.')

	r = self.client.get('/idps/')
	self.assertEquals(r.status_code, 200)
	self.assertValidXHTML(r.content)
	self.assertContains(r, 'http://openid.aol')

	crawler = Crawler(self)
	crawler.visit('/idps/')
	crawler.crawl()
