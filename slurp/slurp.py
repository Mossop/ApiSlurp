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

import xpidl, sys, os, sqlite3, md5, re

NOSCRIPT = 1
NOTXPCOM = 2
READONLY = 4
SCRIPTABLE = 8
FUNCTION = 16

CONST = 1
ARRAY = 2
RETVAL = 4
SHARED = 8
OPTIONAL = 16

class Slurp(object):
  platform = None
  idldirs = None
  parser = None
  dbc = None
  
  def __init__(self, platform, url, cachedir, db, idldir):
    self.idldirs = self.__buildPath(idldir)
    self.parser = xpidl.IDLParser(cachedir)
    if os.path.isfile(db):
      self.__addPlatform(db, platform, url)
    else:
      self.__createDatabase(db, platform, url)

  def __buildPath(self, dir):
    path = []
    for root, dirs, files in os.walk(dir):
      if len(files) > 0:
        path.append(root)
    return path

  def __createDatabase(self, db, platform, url):
    self.dbc = sqlite3.connect(db)
    c = self.dbc.cursor()
    c.execute('CREATE TABLE platforms (id INTEGER PRIMARY KEY AUTOINCREMENT, platform TEXT UNIQUE, url TEXT)')
    c.execute('CREATE TABLE interfaces (id INTEGER PRIMARY KEY AUTOINCREMENT, interface TEXT UNIQUE)')
    c.execute('CREATE TABLE plat_ifaces (id INTEGER PRIMARY KEY AUTOINCREMENT, platform INTEGER, interface INTEGER, base TEXT, flags INTEGER, iid TEXT, comment TEXT, path TEXT, line INTEGER, hash TEXT)')
    c.execute('CREATE INDEX pi_plat ON plat_ifaces (platform)');
    c.execute('CREATE UNIQUE INDEX pi_id ON plat_ifaces (platform, interface)');
    c.execute('CREATE TABLE members (id INTEGER PRIMARY KEY AUTOINCREMENT, pint INTEGER, name TEXT, kind TEXT, type TEXT, flags INTEGER, comment TEXT, line INTEGER, hash TEXT, text TEXT)')
    c.execute('CREATE UNIQUE INDEX mem_id ON members (pint, name)');
    c.execute('CREATE TABLE parameters (member INTEGER, pos INTEGER, type TEXT, name TEXT, flags INTEGER, sizeis TEXT, iidis TEXT)')
    c.execute('CREATE UNIQUE INDEX param_idx ON parameters (member, pos)');
    c.execute('INSERT INTO platforms (platform, url) VALUES (?,?)', (platform,url))
    self.platform = c.lastrowid
    self.dbc.commit()
    c.close()


  def __addPlatform(self, db, platform, url):
    self.dbc = sqlite3.connect(db)
    c = self.dbc.cursor()
    c.execute('SELECT id FROM platforms WHERE platform=?', (platform,))
    pl = c.fetchone()
    if pl is None:
      c.execute('INSERT INTO platforms (platform, url) VALUES (?,?)', (platform,url))
      self.platform = c.lastrowid
    else:
      self.platform = pl[0]
      c.execute('DELETE FROM plat_ifaces WHERE platform=?', (self.platform,))
      # TODO delete from interfaces as necessary
      # TODO delete from members as necessary
      # TODO delete from parameters as necessary
    self.dbc.commit()
    c.close()

  def __mungeComment(self, comments):
    lines = "\n".join(comments).splitlines()
    return "\n".join([re.sub("^\s*/?\*+/?", "", s) for s in lines])

  def __addInterface(self, interface, path):
    c = self.dbc.cursor()
    c.execute('SELECT id from interfaces WHERE interface=?', (interface.name,))
    id = c.fetchone()
    if id is None:
      c.execute('INSERT INTO interfaces (interface) VALUES (?)', (interface.name,))
      id = c.lastrowid
    else:
      id = id[0]
    flags = 0
    flags += SCRIPTABLE if interface.attributes.scriptable else 0
    flags += NOSCRIPT if interface.attributes.noscript else 0
    flags += FUNCTION if interface.attributes.function else 0
    c.execute('INSERT INTO plat_ifaces (platform,interface,base,iid,flags,comment,path,line) VALUES (?,?,?,?,?,?,?,?)',
              (self.platform, id, interface.base, interface.attributes.uuid,
               flags, self.__mungeComment(interface.doccomments), path, interface.location._lineno))
    id = c.lastrowid
    self.dbc.commit()
    c.close()
    return id

  def slurpFile(self, filename, path):
    if not os.path.isfile(filename):
      raise IOError, "Unknown file " + filename
    text = open(filename, 'r').read()
    idl = self.parser.parse(text, filename=filename)
    idl.resolve(self.idldirs, self.parser)
    for interface in idl.getNames():
      if interface.location._file == filename and interface.kind == 'interface':
        print "Slurping %s" % interface.name
        iid = self.__addInterface(interface, path)
        interfacehash = md5.new(interface.name + "," + interface.attributes.uuid)
        c = self.dbc.cursor()
        for member in interface.namemap:
          text = str(member).strip()
          flags = 0
          if member.kind == "method":
            text = member.binaryname
            flags += NOSCRIPT if member.noscript else 0
            flags += NOTXPCOM if member.notxpcom else 0
            hash = "%s,%s,%s,%s,%s" % (member.kind, member.name, member.type, member.binaryname, flags)
          elif member.kind == "attribute":
            flags += NOSCRIPT if member.noscript else 0
            flags += NOTXPCOM if member.notxpcom else 0
            flags += READONLY if member.readonly else 0
            text = member.binaryname
            hash = "%s,%s,%s,%s,%s" % (member.kind, member.name, member.type, member.binaryname, flags)
          elif member.kind == "const":
            text = member.getValue()
            hash = "%s,%s,%s,%s" % (member.kind, member.name, member.type, member.getValue())
          memberhash = md5.new(hash)
          interfacehash.update(hash)
          c.execute('INSERT INTO members (pint, kind, type, name, flags, comment, text, line) VALUES (?,?,?,?,?,?,?,?)',
                    (iid, member.kind, member.type, member.name, flags, self.__mungeComment(member.doccomments), text, member.location._lineno))
          mid = c.lastrowid
          if member.kind == "method":
            pos = 0
            for param in member.params:
              flags = 0
              flags += CONST if param.const else 0
              flags += ARRAY if param.array else 0
              flags += RETVAL if param.retval else 0
              flags += SHARED if param.shared else 0
              flags += OPTIONAL if param.optional else 0
              memberhash.update(",%s %s %s %s" % (flags, param.size_is, param.iid_is, param.type))
              c.execute('INSERT INTO parameters (member, pos, type, name, flags, sizeis, iidis) VALUES (?,?,?,?,?,?,?)',
                        (mid, pos, param.type, param.name, flags, param.size_is, param.iid_is))
              pos += 1
          c.execute('UPDATE members SET hash=? WHERE id=?', (memberhash.hexdigest(), mid))
        c.execute('UPDATE plat_ifaces SET hash=? WHERE id=?',
                  (interfacehash.hexdigest(), iid))
        self.dbc.commit()
        c.close()

  def slurpFiles(self, dir):
    dir = os.path.abspath(dir)
    for root, dirs, files in os.walk(dir):
      for name in files:
        fullpath = os.path.join(root, name)
        self.slurpFile(fullpath, fullpath[len(dir) + 1:])

def displayUsage():
  print "Usage: slurp.py <platform> <source url> <cache dir> <database> <idl path>"

if __name__ == '__main__':
  if len(sys.argv) < 6:
    displayUsage()
    sys.exit()
  if not os.path.isdir(sys.argv[3]):
    displayUsage()
    sys.exit()
  if not os.path.isdir(sys.argv[5]):
    displayUsage()
    sys.exit()
  s = Slurp(sys.argv[1], sys.argv[2], sys.argv[3], sys.argv[4], sys.argv[5])
  if len(sys.argv) > 6:
    fullpath = sys.argv[6]
    s.slurpFile(fullpath, fullpath[len(sys.argv[5]) + 1:])
  else:
    s.slurpFiles(sys.argv[5])
