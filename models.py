from django.db import models

class Application(models.Model):
  appid = models.CharField(max_length=38, unique=True)
  name = models.CharField(max_length=50, unique=True)

  def __unicode__(self):
    return self.name

class Version(models.Model):
  application = models.ForeignKey(Application)
  version = models.CharField(max_length=20)
  gecko = models.CharField(max_length=20)

  def __unicode__(self):
    return self.version

class Platform(models.Model):
  codename = models.CharField(max_length=10, unique=True)
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
  name = models.CharField(max_length=50)
  parent = models.ForeignKey('self', null=True)
  iid = models.CharField(max_length=38)
  comment = models.TextField()
  module = models.CharField(max_length=20)
  path = models.CharField(max_length=100)
  url = models.CharField(max_length=200)
  hash = models.CharField(max_length=32)

  def __unicode__(self):
    return self.name

class Component(models.Model):
  versions = models.ManyToManyField(Version, through='ComponentVersion')
  contract = models.CharField(max_length=100)
  cid = models.CharField(max_length=38)
  interfaces = models.ManyToManyField(Interface)
  hash = models.CharField(max_length=32)

  def __unicode__(self):
    return self.contractID

class ComponentVersion(models.Model):
  component = models.ForeignKey(Component)
  version = models.ForeignKey(Version)
  platforms = models.ManyToManyField(Platform)

class InterfaceVersion(models.Model):
  interface = models.ForeignKey(Interface)
  version = models.ForeignKey(Version)
  platforms = models.ManyToManyField(Platform)

class Member(models.Model):
  interface = models.ForeignKey(Interface)
  name = models.CharField(max_length=30)
  comment = models.TextField()
  url = models.CharField(max_length=200)
  hash = models.CharField(max_length=32)

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
  position = models.IntegerField()
  direction = models.CharField(max_length=5)
  const = models.BooleanField()
  array = models.BooleanField()
  retval = models.BooleanField()
  shared = models.BooleanField()
  optional = models.BooleanField()
  type = models.CharField(max_length=50)
  name = models.CharField(max_length=30)
  sizeis = models.CharField(max_length=30, blank=True)
  iidis = models.CharField(max_length=30, blank=True)

  def __unicode__(self):
    return self.name
