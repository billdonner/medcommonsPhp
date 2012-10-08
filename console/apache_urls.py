from django.conf.urls.defaults import *

urlpatterns = patterns('',
                       (r'^console/', include('urls')))
