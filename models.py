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
# The Original Code is XPCOM API Slurp.
#
# The Initial Developer of the Original Code is
# Dave Townsend <dtownsend@oxymoronical.com>.
# Portions created by the Initial Developer are Copyright (C) 2008
# the Initial Developer. All Rights Reserved.
#
# Contributor(s):
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

from django.db import models

class Application(models.Model):
  appid = models.CharField(max_length=36, unique=True)
  name = models.CharField(max_length=50, unique=True)

  def __unicode__(self):
    return self.name

class Version(models.Model):
  application = models.ForeignKey(Application)
  version = models.CharField(max_length=20, db_index=True)
  gecko = models.CharField(max_length=20)

  class Meta:
    unique_together = ('application', 'version')

  def __unicode__(self):
    return self.version

class Platform(models.Model):
  codename = models.CharField(max_length=10, unique=True, db_index=True)
  name = models.CharField(max_length=10, blank=True)

  def __unicode__(self):
    if self.name:
      return self.name
    return self.codename

class Interface(models.Model):
  versions = models.ManyToManyField(Version, through='InterfaceVersion')
  scriptable = models.BooleanField()
  noscript = models.BooleanField()
  function = models.BooleanField()
  name = models.CharField(max_length=50, db_index=True)
  lcname = models.CharField(max_length=50, db_index=True)
  parent = models.ForeignKey('self', null=True)
  iid = models.CharField(max_length=36)
  comment = models.TextField()
  module = models.CharField(max_length=20, db_index=True)
  path = models.CharField(max_length=100)
  hash = models.CharField(max_length=32)

  class Meta:
    unique_together = ('name', 'hash')

  def __unicode__(self):
    return self.name

class Component(models.Model):
  versions = models.ManyToManyField(Version, through='ComponentVersion')
  contract = models.CharField(max_length=100, db_index=True)
  cid = models.CharField(max_length=36)
  hash = models.CharField(max_length=32)

  class Meta:
    unique_together = ('contract', 'hash')

  def __unicode__(self):
    return self.contract

class ComponentVersion(models.Model):
  component = models.ForeignKey(Component)
  version = models.ForeignKey(Version)
  interfaces = models.ManyToManyField(Interface, through='ComponentVersionInterface')

  class Meta:
    unique_together = ('component', 'version')

class ComponentVersionInterface(models.Model):
  componentversion = models.ForeignKey(ComponentVersion)
  interface = models.ForeignKey(Interface)
  platforms = models.ManyToManyField(Platform)

  class Meta:
    unique_together = ('componentversion', 'interface')

class InterfaceVersion(models.Model):
  interface = models.ForeignKey(Interface)
  version = models.ForeignKey(Version)
  platforms = models.ManyToManyField(Platform)
  url = models.CharField(max_length=200)

  class Meta:
    unique_together = ('interface', 'version')

class Member(models.Model):
  interface = models.ForeignKey(Interface)
  name = models.CharField(max_length=60, db_index=True)
  lcname = models.CharField(max_length=60, db_index=True)
  comment = models.TextField()
  type = models.TextField()
  line = models.IntegerField()
  hash = models.CharField(max_length=32)

  class Meta:
    unique_together = ('interface', 'name')

  def __unicode__(self):
    return self.name

class Constant(Member):
  value = models.CharField(max_length=50)

class Attribute(Member):
  noscript = models.BooleanField()
  notxpcom = models.BooleanField()
  readonly = models.BooleanField()
  binaryname = models.CharField(max_length=50, blank=True)

class Method(Member):
  noscript = models.BooleanField()
  notxpcom = models.BooleanField()
  binaryname = models.CharField(max_length=50, blank=True)

class Parameter(models.Model):
  method = models.ForeignKey(Method)
  direction = models.CharField(max_length=5)
  const = models.BooleanField()
  array = models.BooleanField()
  retval = models.BooleanField()
  shared = models.BooleanField()
  optional = models.BooleanField()
  type = models.CharField(max_length=50)
  name = models.CharField(max_length=30, db_index=True)
  sizeis = models.CharField(max_length=30, blank=True)
  iidis = models.CharField(max_length=30, blank=True)

  class Meta:
    order_with_respect_to = 'method'

  def __unicode__(self):
    return self.name
