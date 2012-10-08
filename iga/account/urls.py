from django.conf.urls.defaults import *

urlpatterns = patterns('account.views',
                       (r'^$',             'index_req'),

                      # If you want people to be able to register themselves
		      #(r'^register$',     'register_req'),

		       (r'^login$',        'login_req'),
		       (r'^logout$',       'logout_req'),
		       (r'^password$',     'password_req'),
		       (r'^forgot$',       'forgot_req'),
		       (r'^sent$',         'sent_req'),
		       (r'^recover$',      'recover_req'),
)
