from django.shortcuts import render_to_response
from django.http import HttpResponse, Http404
from xpcomref.models import *

def index(request):
  return render_to_response('index.html');

def interfaces(request):
  modules = dict()
  mods = Interface.objects.values_list('module', flat=True).distinct()
  for module in mods:
    modules[module] = Interface.objects.filter(module=module).values_list('name', flat=True).order_by('name').distinct()
  return render_to_response('interfaces.html', {
      'modules': modules
    })

def interface(request, name):
  interfaces = Interface.objects.filter(name=name)
  if interfaces.count() > 0:
    return render_to_response('interface.html', {
        'name': name,
        'interfaces': interfaces
      })
  else:
    raise Http404

def components(request):
  return render_to_response('components.html', {
      'components': Component.objects.values_list('contract', flat=True).distinct()
    })
