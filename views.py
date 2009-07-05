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

def searchinterfaces(request, string):
  interfaces = Interface.objects.filter(name__icontains=string).values_list('name', flat=True).order_by('lcname').distinct()
  if interfaces.count() == 1:
    return interface(request, interfaces[0])
  return render_to_response('searchinterfaces.html', {
    'string': string,
    'interfaces': interfaces
    }, context_instance=RequestContext(request))

def searchcomponents(request, string):
  components = Component.objects.filter(contract__icontains=string).values_list('contract', flat=True).order_by('contract').distinct()
  if components.count() == 1:
    return component(request, components[0])
  return render_to_response('searchcomponents.html', {
    'string': string,
    'components': components
    }, context_instance=RequestContext(request))

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
  iv = InterfaceVersion.objects.get(version=version, interface__name=interface)
  return render_to_response('appinterface.html', {
    'version': version,
    'interfaceversions': InterfaceVersion.objects.filter(interface__name=interface).order_by('version'),
    'interface': iv.interface,
    'interfaceversion': iv,
    'components': iv.interface.componentversioninterface_set.filter(componentversion__version=version),
    'constants': Constant.objects.filter(interface=iv.interface).order_by('line'),
    'attributes': Attribute.objects.filter(interface=iv.interface).order_by('lcname'),
    'methods': Method.objects.filter(interface=iv.interface).order_by('lcname')
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
  cv = ComponentVersion.objects.get(version=version, component__contract=contract)
  return render_to_response('appcomponent.html', {
    'version': version,
    'componentversions': ComponentVersion.objects.filter(component__contract=contract).order_by('version'),
    'component': cv.component,
    'componentversion': cv
    }, context_instance=RequestContext(request))
  raise Http404

def compareappinterfaces(request, leftname, leftversion, rightname, rightversion):
  leftv = Version.objects.get(version=leftversion, application__name=leftname)
  rightv = Version.objects.get(version=rightversion, application__name=rightname)
  if leftv.version > rightv.version:
    [leftv, rightv] = [rightv, leftv]

  added = []
  removed = []
  modified = []
  unchanged = []
  names = Interface.objects.values_list('name', flat=True).order_by('lcname').distinct()
  for name in names:
    lefti = InterfaceVersion.objects.filter(version=leftv, interface__name=name)
    righti = InterfaceVersion.objects.filter(version=rightv, interface__name=name)
    if lefti.count() > 0 and righti.count() > 0:
      if lefti[0].interface == righti[0].interface:
        unchanged.append(name)
      else:
        modified.append(name)
    elif lefti.count() > 0:
      removed.append(name)
    elif righti.count() > 0:
      added.append(name)

  return render_to_response('compareappinterfaces.html', {
    'leftversion': leftv,
    'rightversion': rightv,
    'versions': Version.objects.filter(application=leftv.application),
    'added': added,
    'removed': removed,
    'modified': modified,
    'unchanged': unchanged
    }, context_instance=RequestContext(request))

def compareappinterface(request, leftname, leftversion, rightname, rightversion, interface):
  leftv = Version.objects.get(version=leftversion, application__name=leftname)
  rightv = Version.objects.get(version=rightversion, application__name=rightname)
  if leftv.version > rightv.version:
    [leftv, rightv] = [rightv, leftv]
  leftinterface = Interface.objects.get(versions=leftv, name=interface)
  rightinterface = Interface.objects.get(versions=rightv, name=interface)

  constants = []
  left = list(Constant.objects.filter(interface=leftinterface).order_by('line'))
  right = list(Constant.objects.filter(interface=rightinterface).order_by('line'))
  while len(left) > 0 and len(right) > 0:
    if left[0].name == right[0].name:
      constants.append((left[0], right[0]))
      left = left[1:]
      right = right[1:]
    elif left[0].line < right[0].line:
      constants.append((left[0], None))
      left = left[1:]
    else:
      constants.append((None, right[0]))
      right = right[1:]
  while len(left) > 0:
    constants.append((left[0], None))
    left = left[1:]
  while len(right) > 0:
    constants.append((None, right[0]))
    right = right[1:]

  attributes = []
  left = list(Attribute.objects.filter(interface=leftinterface).order_by('lcname'))
  right = list(Attribute.objects.filter(interface=rightinterface).order_by('lcname'))
  while len(left) > 0 and len(right) > 0:
    if left[0].name == right[0].name:
      attributes.append((left[0], right[0]))
      left = left[1:]
      right = right[1:]
    elif left[0].name < right[0].name:
      attributes.append((left[0], None))
      left = left[1:]
    else:
      attributes.append((None, right[0]))
      right = right[1:]
  while len(left) > 0:
    attributes.append((left[0], None))
    left = left[1:]
  while len(right) > 0:
    attributes.append((None, right[0]))
    right = right[1:]

  methods = []
  left = list(Method.objects.filter(interface=leftinterface).order_by('lcname'))
  right = list(Method.objects.filter(interface=rightinterface).order_by('lcname'))
  while len(left) > 0 and len(right) > 0:
    if left[0].name == right[0].name:
      methods.append((left[0], right[0]))
      left = left[1:]
      right = right[1:]
    elif left[0].name < right[0].name:
      methods.append((left[0], None))
      left = left[1:]
    else:
      methods.append((None, right[0]))
      right = right[1:]
  while len(left) > 0:
    methods.append((left[0], None))
    left = left[1:]
  while len(right) > 0:
    methods.append((None, right[0]))
    right = right[1:]

  return render_to_response('compareappinterface.html', {
    'leftversion': leftv,
    'rightversion': rightv,
    'interfaceversions': InterfaceVersion.objects.filter(interface__name=interface).order_by('version'),
    'leftinterface': leftinterface,
    'rightinterface': rightinterface,
    'constants': constants,
    'attributes': attributes,
    'methods': methods
    }, context_instance=RequestContext(request))
  raise Http404
