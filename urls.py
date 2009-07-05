from django.conf.urls.defaults import *

urlpatterns = patterns('xpcomref.views',
  (r'^$', 'index'),
  (r'^interfaces/(?P<name>.+)$', 'interface'),
  (r'^interfaces/$', 'interfaces'),
  (r'^components/(?P<contract>.+)$', 'component'),
  (r'^components/$', 'components'),
  (r'^search/interfaces/(?P<string>.+)$', 'searchinterfaces'),
  (r'^search/components/(?P<string>.+)$', 'searchcomponents'),
  (r'^applications/(?P<leftname>.+)/(?P<leftversion>.+)/interfaces/compare/(?P<rightname>.+)/(?P<rightversion>.+)$', 'compareappinterfaces'),
  (r'^applications/(?P<leftname>.+)/(?P<leftversion>.+)/interfaces/(?P<interface>.+)/compare/(?P<rightname>.+)/(?P<rightversion>.+)$', 'compareappinterface'),
  (r'^applications/(?P<name>.+)/(?P<version>.+)/interfaces/(?P<interface>.+)$', 'appinterface'),
  (r'^applications/(?P<name>.+)/(?P<version>.+)/interfaces/$', 'appinterfaces'),
  (r'^applications/(?P<name>.+)/(?P<version>.+)/components/(?P<contract>.+)$', 'appcomponent'),
  (r'^applications/(?P<name>.+)/(?P<version>.+)/components/$', 'appcomponents'),
)
