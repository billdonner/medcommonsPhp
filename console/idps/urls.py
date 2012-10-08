from django.conf.urls.defaults import *

urlpatterns = patterns('idps.views',
                       (r'^$',            'index_req'),
                       (r'^details$',     'details_req'),
                       (r'^add$',         'add_req'),
                       (r'^edit$',        'edit_req'),
                       (r'^delete$',      'delete_req'),
                       (r'^unlink_user$', 'unlink_user_req'),
                       (r'^logo$',        'logo_req'),
                       (r'^upload$',      'upload_req'),
                       (r'^download$',    'download_req'),
                       )
