from django.conf.urls.defaults import *
from django.conf import settings

urlpatterns = patterns('',
                       (r'^$', 'views.index_req'),
		       (r'^console-led\.png$', 'views.console_led_req'),
		       (r'^db-led\.png$', 'views.db_led_req'),
		       (r'^gateway-led\.png$', 'views.gateway_led_req'),
		       (r'^mclocals-led\.png$', 'views.mclocals_led_req'),

                       (r'^admins/', include('admins.urls')),
                       (r'^users/', include('users.urls')),
                       (r'^groups/', include('groups.urls')),
                       (r'^idps/', include('idps.urls')),
                       (r'^logs/', include('logs.urls')),

                       (r'^account/', include('account.urls')),
		       (r'^config/',  include('config.urls')),
                       (r'^backups/', include('backups.urls')),
		       (r'^security/', include('security.urls')),
		       (r'^appliances/', include('appliances.urls')),
		       (r'^applications/', include('applications.urls')),
		       (r'^demos/', include('demos.urls')),

                       (r'^media/(?P<path>.*)$', 'django.views.static.serve',
                        {'document_root': settings.INSTALL_DIR + 'media/'}),


                       # Uncomment this for admin:
                       (r'^admin/', include('django.contrib.admin.urls')),
)
