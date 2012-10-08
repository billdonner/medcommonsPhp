from django.conf.urls.defaults import *

urlpatterns = patterns('demos.views',
                       (r'^$',         'index_req'),
		       (r'^create$',   'create_req'),
		       (r'^delete$',   'delete_req'),
                      )
