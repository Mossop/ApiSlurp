from django.shortcuts import render_to_response
from django.http import HttpResponse, Http404
from django.template import RequestContext
from xpcomref.models import *

def index(request):
  applications = Version.objects.all()
  return render_to_response('index.html', {
    'applications': applications
    }, context_instance=RequestContext(request))

def interfaces(request):
  modules = []
  mods = Interface.objects.values_list('module', flat=True).order_by('module').distinct()
  for module in mods:
    modules.append({
      'name': module,
      'interfaces': Interface.objects.filter(module=module).values_list('name', flat=True).order_by('lcname').distinct()
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
  raise Http404

def components(request):
  return render_to_response('components.html', {
    'components': Component.objects.values_list('contract', flat=True).order_by('contract').distinct()
    }, context_instance=RequestContext(request))

def component(request, name):
  components = Component.objects.filter(contract=name)
  if components.count() > 0:
    return render_to_response('component.html', {
      'name': name,
      'components': components
      }, context_instance=RequestContext(request))
  raise Http404

def appinterfaces(request, name, version):
  version = Version.objects.get(version=version, application__name=name)
  modules = []
  mods = Interface.objects.filter(versions=version).values_list('module', flat=True).order_by('module').distinct()
  for module in mods:
    modules.append({
      'name': module,
      'interfaces': Interface.objects.filter(module=module, versions=version).values_list('name', flat=True).order_by('lcname')
      })
  return render_to_response('appinterfaces.html', {
    'version': version,
    'modules': modules
    }, context_instance=RequestContext(request))
  raise Http404

def appinterface(request, name, version, interface):
  version = Version.objects.get(version=version, application__name=name)
  interface = Interface.objects.get(versions=version, name=interface)
  return render_to_response('appinterface.html', {
    'version': version,
    'interface': interface,
    'constants': Constant.objects.filter(interface=interface).order_by('line'),
    'attributes': Attribute.objects.filter(interface=interface).order_by('lcname'),
    'methods': Method.objects.filter(interface=interface).order_by('lcname')
    }, context_instance=RequestContext(request))
  raise Http404

def appcomponents(request, name, version):
  version = Version.objects.get(version=version, application__name=name)
  return render_to_response('appcomponents.html', {
    'version': version,
    'components': Component.objects.filter(versions=version).values_list('contract', flat=True).order_by('contract')
    }, context_instance=RequestContext(request))
  raise Http404

def appcomponent(request, name, version, component):
  version = Version.objects.get(version=version, application__name=name)
  component = Component.objects.get(versions=version, contract=component)
  cv = ComponentVersion.objects.get(version=version, component=component)
  return render_to_response('appcomponent.html', {
    'version': version,
    'component': component,
    'componentversion': cv
    }, context_instance=RequestContext(request))
  raise Http404
