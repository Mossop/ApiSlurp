from django.conf.urls.defaults import *

urlpatterns = patterns('xpcomref.views',
  (r'^$', 'index'),
  (r'^interfaces/(?P<name>.+)$', 'interface'),
  (r'^interfaces/$', 'interfaces'),
  (r'^components/(?P<contract>.+)$', 'component'),
  (r'^components/$', 'components'),
  (r'^applications/(?P<name>.+)/(?P<version>.+)/interfaces/(?P<interface>.+)$', 'appinterface'),
  (r'^applications/(?P<name>.+)/(?P<version>.+)/interfaces/$', 'appinterfaces'),
  (r'^applications/(?P<name>.+)/(?P<version>.+)/components/(?P<contract>.+)$', 'appcomponent'),
  (r'^applications/(?P<name>.+)/(?P<version>.+)/components/$', 'appcomponents')
)
