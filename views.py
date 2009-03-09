from django.shortcuts import render_to_response
from django.http import HttpResponse, Http404
from django.template import RequestContext
from xpcomref.models import *

def index(request):
  return render_to_response('index.html', {}, context_instance=RequestContext(request));

def interfaces(request):
  modules = []
  mods = Interface.objects.values_list('module', flat=True).order_by('module').distinct()
  for module in mods:
    modules.append({
      'name': module,
      'interfaces': Interface.objects.filter(module=module).values_list('name', flat=True).order_by('name').distinct()
      })
  return render_to_response('interfaces.html', {
    'modules': modules
    }, context_instance=RequestContext(request))

def interface(request, name):
  interfaces = Interface.objects.filter(name=name)
  if interfaces.count() > 0:
    return render_to_response('interface.html', {
      'name': name,
      'interfaces': interfaces
      }, context_instance=RequestContext(request))
  else:
    raise Http404

def components(request):
  return render_to_response('components.html', {
    'components': Component.objects.values_list('contract', flat=True).order_by('contract').distinct()
    }, context_instance=RequestContext(request))
