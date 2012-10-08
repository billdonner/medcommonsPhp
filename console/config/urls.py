from django.conf.urls.defaults import *

urlpatterns = patterns('config.views',
		       (r'^$',          'index_req'),
		       (r'^paths$',     'paths_req'),
		       (r'^wizard$',    'wiz_name_req'),
		       (r'^wiz_name$',  'wiz_name_req'),
		       (r'^set_name$',  'set_name_req'),
		       (r'^wiz_location$', 'wiz_location_req'),
		       (r'^set_location$', 'set_location_req'),
		       (r'^wiz_web$',   'wiz_web_req'),
		       (r'^set_web$',   'set_web_req'),
		       (r'^wiz_email$', 'wiz_email_req'),
		       (r'^set_email$', 'set_email_req'),
		       (r'^wiz_style$', 'wiz_style_req'),
		       (r'^set_style$', 'set_style_req'),
		       (r'^wiz_settings$', 'wiz_settings_req'),
		       (r'^set_settings$', 'set_settings_req'),
               (r'^set_backup$', 'set_backup_req'),
		       (r'^wiz_publish$', 'wiz_publish_req'),

		       (r'^preview$',   'preview_req'),

		       (r'^backup$',    'backup_req'),
		       (r'^restore$',   'restore_req'),
		       (r'^delete$',    'delete_req'),
                       )
