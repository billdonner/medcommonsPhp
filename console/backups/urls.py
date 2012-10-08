from django.conf.urls.defaults import *

urlpatterns = patterns('backups.views',
                       (r'^$',              'index_req'),
                       (r'^keys$',          'keys_req'),
                       (r'^buckets$',       'buckets_req'),
                       (r'^createbucket$',  'createbucket_req'),
                       (r'^encryption$',    'encryption_req'),
                       (r'^publish$',       'publish_req'),
                       (r'^master$',        'master_req'),
                       (r'^status-led\.png$','status_led_req'),
                       )
