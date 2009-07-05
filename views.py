from django.core.urlresolvers import reverse
from django.shortcuts import render_to_response
from django.http import HttpResponse, HttpResponseRedirect, Http404
from django.template import RequestContext
from xpcomref.models import *

def redirect(view, **kwargs):
  return HttpResponseRedirect(reverse(view, kwargs=kwargs))

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
  ivs = InterfaceVersion.objects.filter(interface__name=name).order_by('version')
  if ivs.count() > 0:
    iv = ivs[ivs.count() - 1]
    return redirect('xpcomref.views.appinterface', name=iv.version.application, version=iv.version, interface=name)
  raise Http404

def components(request):
  return render_to_response('components.html', {
    'components': Component.objects.values_list('contract', flat=True).order_by('contract').distinct()
    }, context_instance=RequestContext(request))

def component(request, contract):
  cvs = ComponentVersion.objects.filter(component__contract=contract).order_by('version')
  if cvs.count() > 0:
    cv = cvs[cvs.count() - 1]
    return redirect('xpcomref.views.appcomponent', name=cv.version.application, version=cv.version, contract=contract)
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
    'versions': Version.objects.filter(application=version.application),
    'modules': modules
    }, context_instance=RequestContext(request))
  raise Http404

def appinterface(request, name, version, interface):
  version = Version.objects.get(version=version, application__name=name)
  interface = Interface.objects.get(versions=version, name=interface)
  return render_to_response('appinterface.html', {
    'version': version,
    'interfaceversions': InterfaceVersion.objects.filter(interface__name=interface.name).order_by('version'),
    'interface': interface,
    'components': interface.componentversioninterface_set.filter(componentversion__version=version),
    'constants': Constant.objects.filter(interface=interface).order_by('line'),
    'attributes': Attribute.objects.filter(interface=interface).order_by('lcname'),
    'methods': Method.objects.filter(interface=interface).order_by('lcname')
    }, context_instance=RequestContext(request))
  raise Http404

def appcomponents(request, name, version):
  version = Version.objects.get(version=version, application__name=name)
  return render_to_response('appcomponents.html', {
    'version': version,
    'versions': Version.objects.filter(application=version.application),
    'components': Component.objects.filter(versions=version).values_list('contract', flat=True).order_by('contract')
    }, context_instance=RequestContext(request))
  raise Http404

def appcomponent(request, name, version, contract):
  version = Version.objects.get(version=version, application__name=name)
  component = Component.objects.get(versions=version, contract=contract)
  cv = ComponentVersion.objects.get(version=version, component=component)
  return render_to_response('appcomponent.html', {
    'version': version,
    'componentversions': ComponentVersion.objects.filter(component__contract=contract).order_by('version'),
    'component': component,
    'componentversion': cv
    }, context_instance=RequestContext(request))
  raise Http404
