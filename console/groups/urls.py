from django.conf.urls.defaults import *

urlpatterns = patterns('groups.views',
                       (r'^$',            'index_req'),
                       (r'^search$',      'search_req'),
                       (r'^group$',       'group_req'),
                       (r'^remove_from_group$', 'remove_from_group_req'),
		       (r'^wiz_create$',  'wiz_create_req'),
		       (r'^wiz_name$',    'wiz_name_req'),
		       (r'^wiz_users$',   'wiz_users_req'),
		       (r'^add_user$',    'add_user_req'),
		       (r'^delete$',      'delete_req'),
		       (r'^delete_wl_entry$', 'delete_wl_entry_req'),
		       (r'^edit$',        'edit_req'),
                       )
