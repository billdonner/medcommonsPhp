from django.conf.urls.defaults import *

urlpatterns = patterns('admins.views',
                       (r'^$',        'index_req'),
                       (r'^create',   'create_req'),
                       (r'^edit',     'edit_req'),
                       (r'^search',   'search_req'),
                       )
