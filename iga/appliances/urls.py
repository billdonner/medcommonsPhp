from django.conf.urls.defaults import *

urlpatterns = patterns('appliances.views',
                       (r'^$',         'index_req'),
		       (r'^edit$',     'edit_req'),
		       (r'^merge$',    'merge_req'),
		       (r'^delete$',   'delete_req'),
                       )
