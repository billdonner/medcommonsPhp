from django.conf.urls.defaults import *
from django.conf import settings

urlpatterns = patterns('',
                       (r'^$', 'views.index_req'),
                       (r'^iga-led\.png$', 'views.iga_led_req'),
                       (r'^db-led\.png$', 'views.db_led_req'),
                       (r'^mcglobals-led\.png$', 'views.mcglobals_led_req'),

                       (r'^status', 'views.status_req'),
                       (r'^account/', include('account.urls')),
                       (r'^admins/', include('admins.urls')),
                       (r'^appliances/', include('appliances.urls')),

                       (r'^media/(?P<path>.*)$', 'django.views.static.serve',
                        {'document_root': settings.INSTALL_DIR + 'media/'}),


                       # Uncomment this for admin:
                       (r'^admin/', include('django.contrib.admin.urls')),
)
