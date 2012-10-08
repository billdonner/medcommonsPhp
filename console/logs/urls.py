from django.conf.urls.defaults import *

urlpatterns = patterns('logs.views',
                       (r'^$',        'index_req'),
                       (r'^list$',    'index_req'),
                       (r'^router$',  'router_req'),
                       )
