from django.conf.urls.defaults import *

urlpatterns = patterns('',
  (r'^interface/(?P<name>.+)$', 'xpcomref.views.interface'),
  (r'^interfaces$', 'xpcomref.views.interfaces'),
  (r'^components$', 'xpcomref.views.components')
)
