from django.conf.urls.defaults import *

urlpatterns = patterns('users.views',
                       (r'^$',         'index_req'),
                       (r'^search$',   'search_req'),
                       (r'^user$',     'user_req'),
                       (r'^password$', 'password_req'),
                       (r'^create$',   'create_req'),
                       (r'^disable$',  'disable_req'),
                       (r'^claim$',    'claim_req'),
                       (r'^edit$',     'edit_req'),
                       (r'^groups$',   'groups_req'),
                       (r'^addgroup$', 'addgroup_req'),
                       (r'^add_to_group$', 'add_to_group_req'),
                       (r'^remove_from_group$', 'remove_from_group_req'),
                       (r'^unlink_idp$', 'unlink_idp_req'),
                       (r'^login_as$', 'login_as_req'),
                       )
