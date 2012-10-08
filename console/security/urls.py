from django.conf.urls.defaults import *

urlpatterns = patterns('security.views',
                       (r'^$',         'index_req'),
                       (r'^req$',      'req_req'),
		       (r'^csr$',      'csr_req'),
		       (r'^download_csr$', 'download_csr_req'),
		       (r'^cert$',     'cert_req'),
                       )
