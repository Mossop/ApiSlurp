#!/usr/bin/env python
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

import os
import sys

MAGIC = "XPCOM\nTypeLib\r\n\032"
IDE_SIZE = 16 + 4 + 4 + 4

try:
  bytes
except:
  bytes = str

# basic data type extractors
def uint8(data, pos):
  return ord(data[pos])

def uint16(data, pos):
  n  = ord(data[pos]) << 8
  n += ord(data[pos+1])
  return n

def uint32(data, pos):
  n  = ord(data[pos])   << 24
  n += ord(data[pos+1]) << 16
  n += ord(data[pos+2]) << 8
  n += ord(data[pos+3])
  return n

def uint64(data, pos):
  n  = ord(data[pos])   << 56
  n += ord(data[pos+1]) << 48
  n += ord(data[pos+2]) << 40
  n += ord(data[pos+3]) << 32
  n  = ord(data[pos+4]) << 24
  n += ord(data[pos+5]) << 16
  n += ord(data[pos+6]) << 8
  n += ord(data[pos+7])
  return n

def sint8(data, pos):
  n = uint8(data, pos)
  if n >= 0x80: n -= 0x100
  return n

def sint16(data, pos):
  n = uint16(data, pos)
  if n >= 0x8000: n -= 0x10000
  return n

def sint32(data, pos):
  n = uint32(data, pos)
  if n >= 0x80000000: n -= 0x100000000
  return n

def sint64(data, pos):
  n = uint64(data, pos)
  if n >= 0x8000000000000000: n -= 0x10000000000000000
  return n

def pointer(data, pos, pool):
  raw = sint32(data, pos)
  if raw == 0: n = 0
  else: n = (raw-1) + pool
  return n

def string(data, pos):
  slen = uint16(data, pos)
  sdat = data[pos+2 : pos+2+slen]
  return (sdat, slen)

def identifier(data, pos):
  end = pos
  while data[end] != '\0': end += 1
  return data[pos:end]

def iid(data, pos):
  value = data[pos:pos+16]
  if value == "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0":
    return "unknown"
  else:
    return ("%02x%02x%02x%02x-%02x%02x-%02x%02x-"
            "%02x%02x-%02x%02x%02x%02x%02x%02x" %
            tuple(ord(x) for x in value))

def read_binary_file(fname):
  fd = -1
  data = bytes()
  try:
    fd = os.open(fname, os.O_RDONLY)
    while True:
      block = os.read(fd, 8192)
      if block == "": break
      data += block
    return data
  finally:
    if fd != -1: os.close(fd)

class Interface(object):
  name = None
  iid = None

  def __init__(self, data, pool, pos):
    self.iid = iid(data, pos)
    self.name = identifier(data, pointer(data, pos+16, pool))

class XPT(object):
  magic = None
  major_version = None
  minor_version = None
  interfaces = None
  
  def __init__(self, fname):
    self.interfaces = []
    data = read_binary_file(fname)
    self.magic = data[:16]
    if self.magic != MAGIC:
      raise IOError, "Invalid XPT header"

    self.major_version       = uint8(data,  16)
    self.minor_version       = uint8(data,  17)

    num_interfaces      = uint16(data, 18)
    interface_directory = uint32(data, 24)
    data_pool           = uint32(data, 28)
    interface_directory -= 1
    interface_directory_end = interface_directory + num_interfaces * IDE_SIZE - 1

    for idx in xrange(num_interfaces):
        pos = interface_directory + idx * IDE_SIZE
        self.interfaces.append(Interface(data, data_pool, pos))

if __name__ == '__main__':
  for fname in sys.argv[1:]:
    xpt = XPT(fname)
    for interface in xpt.interfaces:
      print "%s %s" % (interface.iid, interface.name)
