/*
 * ***** BEGIN LICENSE BLOCK *****
 * Version: MPL 1.1/GPL 2.0/LGPL 2.1
 *
 * The contents of this file are subject to the Mozilla Public License Version
 * 1.1 (the "License"); you may not use this file except in compliance with
 * the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 *
 * The Original Code is XPCOM API Slurp.
 *
 * The Initial Developer of the Original Code is
 * Dave Townsend <dtownsend@oxymoronical.com>.
 * Portions created by the Initial Developer are Copyright (C) 2008
 * the Initial Developer. All Rights Reserved.
 *
 * Contributor(s):
 *
 * Alternatively, the contents of this file may be used under the terms of
 * either of the GNU General Public License Version 2 or later (the "GPL"),
 * or the GNU Lesser General Public License Version 2.1 or later (the "LGPL"),
 * in which case the provisions of the GPL or the LGPL are applicable instead
 * of those above. If you wish to allow use of your version of this file only
 * under the terms of either the GPL or the LGPL, and not to allow others to
 * use your version of this file under the terms of the MPL, indicate your
 * decision by deleting the provisions above and replace them with the notice
 * and other provisions required by the GPL or the LGPL. If you do not delete
 * the provisions above, a recipient may use your version of this file under
 * the terms of any one of the MPL, the GPL or the LGPL.
 *
 * ***** END LICENSE BLOCK *****
 */

const Ci = Components.interfaces;
const Cc = Components.classes;

function LOG(str) {
  dump(str + "\n");
}

var ComponentBlackList = [
  "@oxymoronical.com/componentdumper;1",
  "@mozilla.org/generic-factory;1",
  "QueryInterface"
];

var InterfaceBlackList = [
  "IDispatch"
];

var ComponentDumper = {
  timer: null,
  stack: null,
  interfaces: null,

  processComponent: function() {
    if (this.stack.length == 0) {
      var prompt = Cc["@mozilla.org/embedcomp/prompt-service;1"].
                   getService(Ci.nsIPromptService);
      prompt.alert(null, "APISlurp", "Copying API data complete!");
      return;
    }

    var c = this.stack.shift();
    this.timer.init(this, 100, Ci.nsITimer.TYPE_ONE_SHOT);
    this.writeData("C " + c + "," + Cc[c].number + "\n");
    var str = "";
    try {
      var object = Cc[c].getService();
      for (var i = 0; i < this.interfaces.length; i++) {
        try {
          object.QueryInterface(Ci[this.interfaces[i]]);
          str += this.interfaces[i] + "\n";
        }
        catch (e) {
        }
      }
    }
    catch (e) {
    }
    this.writeData(str);
  },

  writeData: function(str) {
    dump(str);

    var foStream = Cc["@mozilla.org/network/file-output-stream;1"].
                   createInstance(Ci.nsIFileOutputStream);

    foStream.init(this.datafile, 0x02 | 0x08 | 0x10, 0666, 0); 
    foStream.write(str, str.length);
    foStream.close();
  },

  startDump: function() {
    var ds = Cc["@mozilla.org/file/directory_service;1"].
             getService(Ci.nsIProperties);
    this.filestore = ds.get("Desk", Ci.nsIFile);
    this.filestore.append("apislurp");
    if (this.filestore.exists())
      this.filestore.remove(true);
    this.filestore.create(Ci.nsIFile.DIRECTORY_TYPE, 0755);

    var file = ds.get("ComRegF", Ci.nsIFile);
    file.copyTo(this.filestore, "compreg.dat");
    var file = ds.get("XptiRegF", Ci.nsIFile);
    file.copyTo(this.filestore, "xpti.dat");
    var file = ds.get("ComsD", Ci.nsIFile);
    var en = file.directoryEntries;
    while (en.hasMoreElements()) {
      file = en.getNext().QueryInterface(Ci.nsIFile);
      if (file.isFile() && file.leafName.substring(file.leafName.length - 4) == ".xpt")
        file.copyTo(this.filestore, file.leafName);
    }

    this.timer = Cc["@mozilla.org/timer;1"].
                 createInstance(Ci.nsITimer);
    this.timer.init(this, 100, Ci.nsITimer.TYPE_ONE_SHOT);

    this.stack = [];
    for (var c in Cc) {
      if (ComponentBlackList.indexOf(c) < 0)
        this.stack.push(c);
    }

    this.datafile = this.filestore.clone();
    this.datafile.append("application");
    var app = Cc["@mozilla.org/xre/app-info;1"].
              getService(Ci.nsIXULAppInfo).
              QueryInterface(Ci.nsIXULRuntime);
    this.writeData(app.ID + "\n" +
                   app.name + "\n" +
                   app.version + "\n" +
                   app.vendor + "\n" +
                   app.appBuildID + "\n" +
                   app.platformVersion + "\n" +
                   app.platformBuildID + "\n" +
                   app.OS + "\n" +
                   app.XPCOMABI);

    this.datafile = this.filestore.clone();
    this.datafile.append("interfaces");
    this.interfaces = [];
    var str = "";
    for (var iid in Components.interfacesByID) {
      if (typeof(Components.interfacesByID[iid]) != "object")
        continue;
      var i = "" + Components.interfacesByID[iid];
      if (InterfaceBlackList.indexOf(i) >= 0)
        continue;
      this.interfaces.push(i);
      str += i + "," + iid + "\n";
    }
    for (var i in Ci) {
      if (typeof(Ci[i]) != "object")
        continue;
      if (InterfaceBlackList.indexOf(i) >= 0)
        continue;
      if (this.interfaces.indexOf(i) >= 0)
        continue;
      this.interfaces.push(i);
      str += i + ",\n";
    }
    this.writeData(str);

    this.datafile = this.filestore.clone();
    this.datafile.append("components");
  },

  observe: function (subject, topic, data) {
    switch(topic) {
    case "app-startup":
      var os = Cc["@mozilla.org/observer-service;1"].
               getService(Ci.nsIObserverService);
      os.addObserver(this, "profile-after-change", false);
      break;
    case "profile-after-change":
      this.startDump();
      break;
    case "timer-callback":
      this.processComponent();
      break;
    }
  },

  QueryInterface: function(iid) {
    if (iid.equals(Ci.nsIObserver)
     || iid.equals(Ci.nsISupports))
      return this;

    throw Components.results.NS_ERROR_NO_INTERFACE;
  }
};

var initModule =
{
  ServiceCID: Components.ID("{39b91464-b48f-4a8b-a633-af419bd6c962}"),
  ServiceContractID: "@oxymoronical.com/componentdumper;1",
  ServiceName: "Component Dumper",

  registerSelf: function (compMgr, fileSpec, location, type) {
    compMgr = compMgr.QueryInterface(Ci.nsIComponentRegistrar);
    compMgr.registerFactoryLocation(this.ServiceCID, this.ServiceName,
                                    this.ServiceContractID, fileSpec, location,
                                    type);
    var catMan = Cc["@mozilla.org/categorymanager;1"].
                 getService(Ci.nsICategoryManager);
    catMan.addCategoryEntry("app-startup", this.ServiceName,
                            "service," + this.ServiceContractID,
                            true, true);

  },

  unregisterSelf: function (compMgr, fileSpec, location) {
    compMgr = compMgr.QueryInterface(Ci.nsIComponentRegistrar);
    compMgr.unregisterFactoryLocation(this.ServiceCID,fileSpec);
  },

  getClassObject: function (compMgr, cid, iid) {
    if (!cid.equals(this.ServiceCID))
      throw Components.results.NS_ERROR_NO_INTERFACE
    if (!iid.equals(Ci.nsIFactory))
      throw Components.results.NS_ERROR_NOT_IMPLEMENTED;
    return this.instanceFactory;
  },

  canUnload: function(compMgr) {
    return true;
  },

  instanceFactory: {
    createInstance: function (outer, iid)
    {
      if (outer != null)
        throw Components.results.NS_ERROR_NO_AGGREGATION;
      return ComponentDumper.QueryInterface(iid);
    }
  }
};

function NSGetModule(compMgr, fileSpec) {
  return initModule;
}
