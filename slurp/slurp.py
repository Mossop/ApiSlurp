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

import xpidl, sys, os, sqlite3, md5

class Slurp(object):
  platform = None
  idldirs = None
  parser = None
  dbc = None
  
  def __init__(self, platform, cachedir, db, idldirs):
    self.idldirs = idldirs
    self.parser = xpidl.IDLParser(cachedir)
    if os.path.isfile(db):
      self.__addPlatform(db, platform)
    else:
      self.__createDatabase(db, platform)

  def __createDatabase(self, db, platform):
    self.dbc = sqlite3.connect(db)
    c = self.dbc.cursor()
    c.execute('CREATE TABLE platforms (id INTEGER PRIMARY KEY AUTOINCREMENT, platform TEXT)')
    c.execute('CREATE TABLE interfaces (id INTEGER PRIMARY KEY AUTOINCREMENT, interface TEXT)')
    c.execute('CREATE TABLE plat_ifaces (id INTEGER PRIMARY KEY AUTOINCREMENT, platform INTEGER, interface INTEGER, iid TEXT, comment TEXT, hash TEXT)')
    c.execute('CREATE TABLE members (id INTEGER PRIMARY KEY AUTOINCREMENT, pint INTEGER, name TEXT, kind TEXT, type TEXT, comment TEXT, hash TEXT, text TEXT)')
    c.execute('CREATE TABLE parameters (member INTEGER, pos INTEGER, type TEXT, name TEXT)')
    c.execute('INSERT INTO platforms (platform) VALUES (?)', (platform,))
    self.platform = c.lastrowid
    self.dbc.commit()
    c.close()


  def __addPlatform(self, db, platform):
    self.dbc = sqlite3.connect(db)
    c = self.dbc.cursor()
    c.execute('SELECT id FROM platforms WHERE platform=?', (platform,))
    pl = c.fetchone()
    if pl is None:
      c.execute('INSERT INTO platforms (platform) VALUES (?)', (platform,))
      self.platform = c.lastrowid
    else:
      self.platform = pl[0]
      c.execute('DELETE FROM plat_ifaces WHERE platform=?', (self.platform,))
      # TODO delete from interfaces as necessary
    self.dbc.commit()
    c.close()

  def __addInterface(self, interface):
    c = self.dbc.cursor()
    c.execute('SELECT id from interfaces WHERE interface=?', (interface.name,))
    id = c.fetchone()
    if id is None:
      c.execute('INSERT INTO interfaces (interface) VALUES (?)', (interface.name,))
      id = c.lastrowid
    else:
      id = id[0]
    c.execute('INSERT INTO plat_ifaces (platform,interface,iid,comment) VALUES (?,?,?,?)',
              (self.platform, id, interface.attributes.uuid, "\n".join(interface.doccomments)))
    id = c.lastrowid
    self.dbc.commit()
    c.close()
    return id

  def slurpFile(self, filename):
    if not os.path.isfile(filename):
      raise IOError, "Unknown file " + filename
    text = open(filename, 'r').read()
    idl = self.parser.parse(text, filename=filename)
    idl.resolve(self.idldirs, self.parser)
    for interface in idl.getNames():
      if interface.location._file == filename and interface.kind == 'interface':
        print "Slurping %s" % interface.name
        iid = self.__addInterface(interface)
        interfacehash = md5.new(interface.name + "," + interface.attributes.uuid)
        c = self.dbc.cursor()
        for member in interface.namemap:
          text = str(member).strip()
          if member.kind == "method":
            hash = "%s,%s,%s,%s" % (member.kind, member.name, member.type, ",".join([p.type for p in member.params]))
            text = ""
          elif member.kind == "attribute":
            hash = "%s,%s,%s" % (member.kind, member.name, member.type)
            text = member.readonly and 'readonly' or ''
          elif member.kind == "const":
            hash = "%s,%s,%s,%s" % (member.kind, member.name, member.type, member.getValue())
            text = member.getValue()
          memberhash = md5.new(hash)
          interfacehash.update(hash)
          c.execute('INSERT INTO members (pint, kind, type, name, comment, hash, text) VALUES (?,?,?,?,?,?,?)',
                    (iid, member.kind, member.type, member.name, "\n".join(member.doccomments), memberhash.hexdigest(), text))
          if member.kind == "method":
            mid = c.lastrowid
            pos = 0
            for param in member.params:
              c.execute('INSERT INTO parameters (member, pos, type, name) VALUES (?,?,?,?)',
                        (mid, pos, param.type, param.name))
              pos += 1
        c.execute('UPDATE plat_ifaces SET hash=? WHERE platform=? AND interface=?',
                  (interfacehash.hexdigest(), self.platform, iid))
        self.dbc.commit()
        c.close()

  def slurpFiles(self, dir):
    for root, dirs, files in os.walk(dir):
      for name in files:
        self.slurpFile(os.path.join(root, name))

def displayUsage():
  print "Usage: slurp.py <platform> <cache dir> <database> <idl path>"

if __name__ == '__main__':
  if len(sys.argv) < 5:
    displayUsage()
    sys.exit()
  if not os.path.isdir(sys.argv[2]):
    displayUsage()
    sys.exit()
  if not os.path.isdir(sys.argv[4]):
    displayUsage()
    sys.exit()
  s = Slurp(sys.argv[1], sys.argv[2], sys.argv[3], sys.argv[4:])
  s.slurpFiles(sys.argv[4])
