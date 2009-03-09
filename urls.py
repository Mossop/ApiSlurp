from django.conf.urls.defaults import *

urlpatterns = patterns('xpcomref.views',
  (r'^$', 'index'),
  (r'^interface/(?P<name>.+)$', 'interface'),
  (r'^interfaces$', 'interfaces'),
  (r'^component/(?P<name>.+)$', 'component'),
  (r'^components$', 'components')
)
