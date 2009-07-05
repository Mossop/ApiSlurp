from django.template import Library
from django.template.loader import render_to_string
from apislurp.xpcomref.models import *

register = Library()

@register.simple_tag
def idlattributes(member):
  str = ""
  if isinstance(member, Method) or isinstance(member, Attribute):
    if member.noscript:
      str += render_to_string('includes/attributes/noscript.html') + ", "
    if member.notxpcom:
      str += render_to_string('includes/attributes/notxpcom.html') + ", "
    if member.binaryname:
      str += render_to_string('includes/attributes/binaryname.html', 
                              { 'name' : member.binaryname}) + ", "
  if isinstance(member, Parameter):
    if member.optional:
      str += render_to_string('includes/attributes/optional.html') + ", "
    if member.const:
      str += render_to_string('includes/attributes/const.html') + ", "
    if member.shared:
      str += render_to_string('includes/attributes/shared.html') + ", "
    if member.array:
      str += render_to_string('includes/attributes/array.html') + ", "
    if member.sizeis:
      str += render_to_string('includes/attributes/size_is.html', 
                              { 'name' : member.sizeis}) + ", "
    if member.iidis:
      str += render_to_string('includes/attributes/iid_is.html', 
                              { 'name' : member.iidis}) + ", "
    if member.retval:
      str += render_to_string('includes/attributes/retval.html') + ", "
  if len(str) > 0:
    return "[" + str[0:-2] + "] "
  return ""
