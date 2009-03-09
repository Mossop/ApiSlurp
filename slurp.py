#!/usr/bin/env python
# slurp.py - A tool to grab API information from IDL files into an sqlite database
#
# ***** BEGIN LICENSE BLOCK *****
# Version: MPL 1.1/GPL 2.0/LGPL 2.1
#
# The contents of this file are subject to the Mozilla Public License Version
# 1.1 (the "License"); you may not use this file except in compliance with
# the License. You may obtain a copy of the License at
# http://www.mozilla.org/MPL/
#
# Software distributed under the License is distributed on an "AS IS" basis,
# WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
# for the specific language governing rights and limitations under the
# License.
#
# The Original Code is mozilla.org code.
#
# The Initial Developer of the Original Code is
#   Mozilla Foundation.
# Portions created by the Initial Developer are Copyright (C) 2008
# the Initial Developer. All Rights Reserved.
#
# Contributor(s):
#   Dave Townsend <dtownsend@oxymoronical.com>
#
# Alternatively, the contents of this file may be used under the terms of
# either of the GNU General Public License Version 2 or later (the "GPL"),
# or the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
# in which case the provisions of the GPL or the LGPL are applicable instead
# of those above. If you wish to allow use of your version of this file only
# under the terms of either the GPL or the LGPL, and not to allow others to
# use your version of this file under the terms of the MPL, indicate your
# decision by deleting the provisions above and replace them with the notice
# and other provisions required by the GPL or the LGPL. If you do not delete
# the provisions above, a recipient may use your version of this file under
# the terms of any one of the MPL, the GPL or the LGPL.
#
# ***** END LICENSE BLOCK *****

import xpidl, sys, os, md5, re
from apislurp.xpcomref.models import *
from xpt import XPT
from django.db import connection, transaction

ComponentBlackList = [
  re.compile('^@netscape.com/fullsoft/qfa;1$'),
  re.compile('^@mozilla.org/commandlinehandler/general-startup;1?type=inspector$'),
  re.compile('^@mozilla.org/inspector/')
]

def hashit(str):
  return md5.new(str).hexdigest()

class Output:
  def __init__(self):
    self.str = ""
  def write(self, string):
    self.str += string

class Slurp(object):
  parser = None
  sources = None

  def __init__(self, sources):
    self.parser = xpidl.IDLParser('cache')
    self.sources = sources

  def __mungeComment(self, comments):
    lines = "\n".join(comments).splitlines()
    return "\n".join([re.sub("^\s*/?\*+/?", "", s) for s in lines])

  def getHashStringForMember(self, member):
    str = "%s,%s,%s" % (member.kind, member.name, member.type)
    if member.kind == "method":
      str += ",%s,%s,%s" % (member.binaryname, member.noscript, member.notxpcom)
      for param in member.params:
        str += ",%s,%s,%s,%s,%s" % (param.const, param.array, param.retval, param.shared, param.optional)
        str += ",%s,%s,%s,%s" % (param.size_is, param.iid_is, param.paramtype, param.type)
    elif member.kind == "attribute":
      str += ",%s,%s,%s,%s" % (member.binaryname, member.noscript, member.notxpcom, member.readonly)
    elif member.kind == "const":
      str += ",%s" % member.getValue()
    return str

  def getHashStringForInterface(self, interface):
    str = "%s,%s,%s" % (interface.name, interface.attributes.uuid, interface.base)
    str += ",%s,%s,%s" % (interface.attributes.scriptable, interface.attributes.noscript, interface.attributes.function)
    for member in interface.namemap:
      str += ",%s" % self.getHashStringForMember(member)
    return str

  def getHashStringForComponent(self, cid, implements):
    str = cid
    for interface in implements:
      str += ",%s" % interface.hash
    return str

  def addMember(self, interface, member):
    if member.kind == "method":
      m = Method()
      m.binaryname = member.binaryname if member.binaryname else ''
      m.noscript = member.noscript
      m.notxpcom = member.notxpcom
    elif member.kind == "attribute":
      m = Attribute()
      m.binaryname = member.binaryname if member.binaryname else ''
      m.noscript = member.noscript
      m.readonly = member.readonly
    elif member.kind == "const":
      m = Constant()
      m.value = member.getValue()
    m.interface = interface
    m.name = member.name
    m.comment = self.__mungeComment(member.doccomments)
    m.hash = hashit(self.getHashStringForMember(member))
    m.url = member.url
    m.save()
    if member.kind == "method":
      pos = 0
      for param in member.params:
        p = Parameter(method=m, position=pos, name=param.name)
        p.const = param.const
        p.array = param.array
        p.retval = param.retval
        p.shared = param.shared
        p.optional = param.optional
        p.direction = param.paramtype
        p.type = param.type
        p.sizeis = param.size_is if param.size_is else ''
        p.iidis = param.iid_is if param.iid_is else ''
        p.save()

  def addInterface(self, seen, used, version, platform, interface):
    if seen.has_key(interface.name):
      return seen[interface.name]
    hash = hashit(self.getHashStringForInterface(interface))
    try:
      iface = Interface.objects.get(name=interface.name, hash=hash)
      try:
        iv = InterfaceVersion.objects.get(interface=iface, version=version)
      except InterfaceVersion.DoesNotExist:
        iv = InterfaceVersion(version=version, interface=iface)
        iv.save()
    except Interface.DoesNotExist:
      iface = Interface(name=interface.name, iid=interface.attributes.uuid, hash=hash)
      iface.scriptable = interface.attributes.scriptable
      iface.noscript = interface.attributes.noscript
      iface.function = interface.attributes.function
      if interface.base:
        iface.parent = self.addInterface(seen, used, version, platform, used[interface.base])
      iface.comment = self.__mungeComment(interface.doccomments)
      iface.path = interface.path
      iface.url = interface.url
      iface.module = interface.module
      iface.save()
      for member in interface.namemap:
        self.addMember(iface, member)
      iv = InterfaceVersion(version=version, interface=iface)
      iv.save()
    try:
      iv.platforms.get(id=platform.id)
    except Platform.DoesNotExist:
      iv.platforms.add(platform)
    seen[iface.name] = iface
    return iface

  @transaction.commit_on_success
  def scanPlatform(self, dir, sources, sourceinterfaces):
    f = open(os.path.join(dir, 'application'), 'r')
    lines = f.readlines()
    appid = lines[0].rstrip()[1:-1]
    appname = lines[1].rstrip()
    vername = lines[2].rstrip()
    gecko = lines[5].rstrip()
    platname = lines[7].rstrip()
    f.close()
    print "Scanning application data %s %s %s." % (appname, vername, platname)
    try:
      app = Application.objects.get(appid=appid)
    except Application.DoesNotExist:
      app = Application(appid=appid, name=appname)
      app.save()
    try:
      version = Version.objects.get(application=app, version=vername)
    except Version.DoesNotExist:
      version = Version(application=app, version=vername, gecko=gecko)
      version.save()
    try:
      platform = Platform.objects.get(codename=platname)
    except Platform.DoesNotExist:
      platform = Platform(codename=platname)
      platform.save()

    used = dict()
    for name in os.listdir(dir):
      if os.path.isfile(os.path.join(dir, name)) and name.endswith('.xpt'):
        xptfile = XPT(os.path.join(dir, name))
        for interface in xptfile.interfaces:
          if interface.iid != "unknown":
            if used.has_key(interface.name):
              print "warning: Found duplicate entry in source xpt for %s." % interface.name
            if sourceinterfaces.has_key(interface.name) and sourceinterfaces[interface.name].has_key(interface.iid):
              used[interface.name] = sourceinterfaces[interface.name][interface.iid]
            else:
              print "warning: Interface %s ({%s}) not found in sources." % (interface.name, interface.iid)

    seen = dict()
    for interface in used.values():
      self.addInterface(seen, used, version, platform, interface)

    f = open(os.path.join(dir, 'components'), 'r')
    contract = None
    cid = None
    implements = []
    for line in f:
      line = line.rstrip()
      if line.startswith('C '):
        if contract is not None:
          hash = hashit(self.getHashStringForComponent(cid, implements))
          try:
            component = Component.objects.get(contract=contract, hash=hash)
          except Component.DoesNotExist:
            component = Component(contract=contract, cid=cid, hash=hash)
            component.save()
            for interface in implements:
              component.interfaces.add(interface)
          try:
            cv = ComponentVersion.objects.get(component=component, version=version)
          except ComponentVersion.DoesNotExist:
            cv = ComponentVersion(version=version, component=component)
            cv.save()
          try:
            cv.platforms.get(id=platform.id)
          except Platform.DoesNotExist:
            cv.platforms.add(platform)
        line = line[2:]
        [contract, cid] = line.rsplit(',', 1)
        cid = cid[1:-1]
        for bad in ComponentBlackList:
          if bad.search(contract):
            contract = None
            break
        implements = []
      elif contract is not None:
        if seen.has_key(line):
          implements.append(seen[line])
        else:
          print "warning: Component %s implements unknown interface %s." % (contract, line)
    f.close()

  def scanVersion(self, name, dir):
    idldirs = []
    sources = []
    for name in os.listdir(dir):
      if os.path.isfile(os.path.join(dir, name)) and os.path.isdir(os.path.join(self.sources, name)):
        sversion = open(os.path.join(dir, name), 'r').read().rstrip()
        source = os.path.join(self.sources, name, sversion)
        if not os.path.isdir(source):
          raise Exception, "Application %s references unknown source %s %s" % (dir, name, sversion)
        sources.append(source)
        for root, dirs, files in os.walk(source):
          if len(files) > 0:
            idldirs.append(root)

    errorlog = Output()
    sourceinterfaces = dict()
    for source in sources:
      print "Scanning IDL source %s." % source
      url = open(os.path.join(source, 'source'), 'r').read().rstrip()
      for root, dirs, files in os.walk(source):
        for name in files:
          if name.endswith('.idl'):
            idlfile = os.path.join(root, name)
            text = open(idlfile, 'r').read()
            try:
              sys.stderr = errorlog
              idl = self.parser.parse(text, filename=idlfile)
              idl.resolve(idldirs, self.parser)
              sys.stderr = sys.__stderr__
              for interface in idl.getNames():
                if interface.location._file == idlfile and interface.kind == 'interface':
                  if not sourceinterfaces.has_key(interface.name):
                    sourceinterfaces[interface.name] = dict()
                  if sourceinterfaces[interface.name].has_key(interface.attributes.uuid):
                    print "warning: Duplicate interface in source %s ({%s})." % (interface.name, interface.attributes.uuid)
                  sourceinterfaces[interface.name][interface.attributes.uuid] = interface
                  interface.path = idlfile[len(source) + 1:]
                  interface.module = interface.path.split("/")[0]
                  interface.url = url.replace('%PATH%', interface.path).replace('%LINE%', str(interface.location._lineno))
                  for member in interface.namemap:
                    member.url = url.replace('%PATH%', interface.path).replace('%LINE%', str(member.location._lineno))
            except xpidl.IDLError:
              continue
              print "warning: Unable to parse idl file %s." % os.path.join(idldir, name)
        
    for name in os.listdir(dir):
      if os.path.isdir(os.path.join(dir, name)):
        self.scanPlatform(os.path.join(dir, name), sources, sourceinterfaces)

  def scanApplication(self, name, dir):
    for name in os.listdir(dir):
      if os.path.isdir(os.path.join(dir, name)):
        self.scanVersion(name, os.path.join(dir, name))

  def scanApplications(self, dir):
    for name in os.listdir(dir):
      if os.path.isdir(os.path.join(dir, name)):
        self.scanApplication(name, os.path.join(dir, name))

def displayUsage():
  print "Usage: slurp.py <applications> <sources>"

if __name__ == '__main__':
  if len(sys.argv) != 3:
    displayUsage()
    sys.exit()
  if not os.path.isdir(sys.argv[1]):
    displayUsage()
    sys.exit()
  if not os.path.isdir(sys.argv[2]):
    displayUsage()
    sys.exit()
  s = Slurp(sys.argv[2])
  try:
    s.scanApplications(sys.argv[1])
  except:
    if len(connection.queries) > 0:
      print "Last SQL: %s " % connection.queries[-1]['sql']
    raise
