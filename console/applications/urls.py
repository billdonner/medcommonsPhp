from django.conf.urls.defaults import *

urlpatterns = patterns('applications.views',
                       (r'^$',                    'index_req'),
                       (r'^register$',            'register'),
                       (r'^register_application$','register_application_req'),
                       (r'^delete$',              'delete_application_req'),
                       (r'^edit$',                'edit_application_req'),
                       )
