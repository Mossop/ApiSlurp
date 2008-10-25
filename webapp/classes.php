<?php

class VersionComparator {
  /**
   * Parse a version part. 
   * @return array $r parsed version part.
   */
  function parseVersionPart($p) {
    if ($p == '*') {
      return array('numA'   => 2147483647,
                   'strB'   => '',
                   'numC'   => 0,
                   'extraD' => '');
    }

    preg_match('/^([-\d]*)([^-\d]*)([-\d]*)(.*)$/', $p, $m);

    $r = array('numA'   => intval($m[1]),
               'strB'   => $m[2],
               'numC'   => intval($m[3]),
               'extraD' => $m[4]);

    if ($r['strB'] == '+') {
      ++$r['numA'];
      $r['strB'] = 'pre';
    }

    return $r;
  }

  /**
   * Compare parsed version parts.
   * @param string $an
   * @param string $bp
   * @return int $r
   */
  function cmp($an, $bn) {
    if ($an < $bn)
      return -1;

    if ($an > $bn)
      return 1;

    return 0;
  }

  /**
   * Recursive string comparison.
   * @param string $as
   * @param string $bs
   * @return int $r
   */
  function strcmp($as, $bs) {
    if ($as == $bs)
      return 0;

    if ($as == '')
      return 1;

    if ($bs == '')
      return -1;

    return strcmp($as, $bs);
  }

  /**
   * Compare parsed version numbers.
   * @param string $ap
   * @param string $bp
   * @return int $r -1|0|1
   */
  function compareVersionParts($ap, $bp) {
    $avp = $this->parseVersionPart($ap);
    $bvp = $this->parseVersionPart($bp);

    $r = $this->cmp($avp['numA'], $bvp['numA']);
    if ($r)
      return $r;

    $r = $this->strcmp($avp['strB'], $bvp['strB']);
    if ($r)
      return $r;

    $r = $this->cmp($avp['numC'], $bvp['numC']);
    if ($r)
      return $r;

    return $this->strcmp($avp['extraD'], $bvp['extraD']);
  }

  /**
   * Master comparison function.
   * @param string $a complete version string.
   * @param string $b complete version string.
   * @return int $r -1|0|1
   */
  function compareVersions($a, $b) {
    $al = explode('.', $a);
    $bl = explode('.', $b);

    while (count($al) || count($bl)) {
      $ap = array_shift($al);
      $bp = array_shift($bl);

      $r = $this->compareVersionParts($ap, $bp);
      if ($r != 0)
        return $r;
    }

    return 0;
  }
}

class APISmarty extends Smarty {
  private $starttime;
  private $template;
  private $cacheid;
  private $compileid;

  public function __construct() {
    parent::__construct();

    $this->template_dir         = 'templates/';
    $this->compile_dir          = 'compiled/';
    $this->cache_dir            = 'cache/';
    $this->cache_modified_check = true;

    $this->starttime = microtime(true);
  }

  public function prepare($template, $cacheid = null, $compileid = null) {
    if ($this->is_cached($template, $cacheid, $compileid)) {
      parent::display($template, $cacheid, $compileid);
      exit;
    }

    $this->template = $template;
    $this->cacheid = $cacheid;
    $this->compileid = $compileid;
  }

  public function display() {
    $time = round(100 * (microtime(true) - $this->starttime)) / 100;
    $this->assign('parsetime', $time);

    parent::display($this->template, $this->cacheid, $this->compileid);
  }
}

abstract class Database {
  public function escape($str) {
    return addslashes($str);
  }

  abstract public function arrayQuery($query);

  public function singleQuery($query) {
    $rows = $this->arrayQuery($query);
    if ($rows === false) {
      return $rows;
    }
    return $rows[0][0];
  }

  public function columnQuery($query) {
    $column = array();
    $rows = $this->arrayQuery($query);
    if ($rows === false) {
      return $rows;
    }
    foreach ($rows as $row) {
      array_push($column, $row[0]);
    }
    return $column;
  }

  public function rowQuery($query) {
    $rows = $this->arrayQuery($query);
    if ($rows === false) {
      return $rows;
    }
    return $rows[0];
  }
}

class SQLiteDB extends Database {
  private $dbres;

  public function __construct($filename) {
    $this->dbres = sqlite_popen($filename);
  }

  public function escape($str) {
    return sqlite_escape_string($str);
  }

  public function arrayQuery($query) {
    return sqlite_array_query($this->dbres, $query);
  }

  public function singleQuery($query) {
    return sqlite_single_query($this->dbres, $query, true);
  }

  public function columnQuery($query) {
    return sqlite_single_query($this->dbres, $query, false);
  }
}

function member_compare($a, $b) {
  if ($a->name == $b->name) {
    return 0;
  }
  return ($a->name < $b->name) ? -1 : 1;
}

function constant_compare($a, $b) {
  if ($a->value == $b->value) {
    return 0;
  }
  return ($a->value < $b->value) ? -1 : 1;
}

class Cache {
  private static $cache = array();

  public static function has($type, $key) {
    if (isset(self::$cache[$type]) && isset(self::$cache[$type][$key])) {
      return true;
    }
    return false;
  }

  public static function get($type, $key) {
    if (isset(self::$cache[$type]) && isset(self::$cache[$type][$key])) {
      return self::$cache[$type][$key];
    }
    return null;
  }

  public static function set($type, $value) {
    if (!isset(self::$cache[$type])) {
      self::$cache[$type] = array();
    }
    self::$cache[$type][$key] = $value;
  }
}

class Platform {
  public $id;
  public $name;
  public $version;
  public $sourceurl;

  private function __construct($id, $name, $version, $sourceurl) {
    $this->id = $id;
    $this->name = $name;
    $this->version = $version;
    $this->sourceurl = $sourceurl;

    Cache::set('Platform', $id, $this);
  }

  public function __toString() {
    return $this->name;
  }

  public function getInterfaces() {
    global $db;

    $interfaces = array();
    $rows = $db->arrayQuery('SELECT plat_ifaces.*,interfaces.interface AS name FROM plat_ifaces JOIN '.
                            'interfaces ON interfaces.id=plat_ifaces.interface WHERE platform='.
                            $this->id . ' ORDER BY interfaces.interface');
    foreach ($rows as $row) {
      array_push($interfaces, InterfaceVersion::getOrCreate($row['id'], $this, $row['name'], $row['path'],
                                                            $row['comment'], $row['iid'], $row['hash']));
    }
    return $interfaces;
  }

  public static function getOrCreate($id, $name, $version, $sourceurl) {
    $result = Cache::get('Platform', $id);
    if ($result != null) {
      return $result;
    }
    return new Platform($id, $name, $version, $sourceurl);
  }

  public static function getByName($name) {
    global $db;

    $row = $db->rowQuery('SELECT * FROM platforms WHERE platform="' . $db->escape($name) . '"');
    if ($row === false) {
      return null;
    }
    $platform = Cache::get('Platform', $row['id']);
    if ($platform != null) {
      return $platform;
    }
    return Platform::getOrCreate($row['id'], $row['platform'], $row['platform'], $row['url']);
  }

  public static function getAllPlatforms() {
    global $db;

    $platforms = array();
    $rows = $db->arrayQuery('SELECT * FROM platforms');
    foreach ($rows as $row) {
      array_push($platforms, Platform::getOrCreate($row['id'], $row['platform'], $row['platform'], $row['url']));
    }
    return $platforms;
  }
}

class XPCOMInterface {
  public $id;
  public $name;
  private $versions;

  private function __construct($id, $name) {
    $this->id = $id;
    $this->name = $name;

    Cache::set('XPCOMInterface', $id, $this);
  }

  public function __toString() {
    return $this->name;
  }

  public function getVersions() {
    global $db;

    if (isset($this->versions)) {
      return $this->versions;
    }

    $this->versions = array();
    $rows = $db->arrayQuery('SELECT plat_ifaces.*, platforms.* FROM '.
                            'plat_ifaces JOIN platforms ON plat_ifaces.platform=platforms.id WHERE plat_ifaces.interface=' . $this->id);
    foreach ($rows as $row) {
      $platform = Platform::getOrCreate($row['platforms.id'],
                                        $row['platforms.platform'],
                                        $row['platforms.platform'],
                                        $row['platforms.url']);
      array_push($this->versions, InterfaceVersion::getOrCreate($row['plat_ifaces.id'],
                                                                $platform,
                                                                $this->name,
                                                                $row['plat_ifaces.path'],
                                                                $row['plat_ifaces.comment'],
                                                                $row['plat_ifaces.iid'],
                                                                $row['plat_ifaces.hash']));
    }
    return $this->versions;
  }

  public function getNewestVersion() {
    $vc = new VersionComparator();
    $version = $this->getVersions();

    $version = $version[0];
    for ($i = 1; $i < count($versions); $i++) {
      if ($vc->compareVersions($version->platform->version, $versions[$i]->platform->version) < 0) {
        $version = $versions[$i];
      }
    }
    return $version;
  }

  public function getOldestVersion() {
    $vc = new VersionComparator();
    $version = $this->getVersions();

    $version = $version[0];
    for ($i = 1; $i < count($versions); $i++) {
      if ($vc->compareVersions($version->platform->version, $versions[$i]->platform->version) > 0) {
        $version = $versions[$i];
      }
    }
    return $version;
  }

  public static function getOrCreate($id, $name) {
    $result = Cache::get('XPCOMInterface', $id);
    if ($result != null) {
      return $result;
    }
    return new XPCOMInterface($id, $name);
  }

  public static function getByName($name) {
    global $db;

    $versions = array();
    $rows = $db->arrayQuery('SELECT interfaces.*, plat_ifaces.*, platforms.* '.
                            'FROM plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                            'JOIN platforms ON plat_ifaces.platform=platforms.id WHERE interfaces.interface="' . $db->escape($name) . '"');
    if ($rows === false) {
      return null;
    }
    if (Cache::has('XPCOMInterface', $rows[0]['interfaces.id'])) {
      return Cache::get('XPCOMInterface', $rows[0]['interfaces.id']);
    }

    $interface = self::getOrCreate($rows[0]['interfaces.id'], $rows[0]['interfaces.interface']);

    $interface->versions = array();
    foreach ($rows as $row) {
      $platform = Platform::getOrCreate($row['platforms.id'],
                                        $row['platforms.platform'],
                                        $row['platforms.platform'],
                                        $row['platforms.url']);
      array_push($interface->versions, InterfaceVersion::getOrCreate($row['plat_ifaces.id'],
                                                                     $platform,
                                                                     $interface->name,
                                                                     $row['plat_ifaces.path'],
                                                                     $row['plat_ifaces.comment'],
                                                                     $row['plat_ifaces.iid'],
                                                                     $row['plat_ifaces.hash']));
    }
    return $interface;
  }

  public static function getAllInterfaces() {
    global $db;

    $interfaces = array();
    $rows = $db->arrayQuery('SELECT id,interface FROM interfaces');
    foreach ($rows as $row) {
      array_push($interfaces, new XPCOMInterface($row['id'], $row['interface']));
    }
    return $interfaces;
  }
}

class InterfaceVersion {
  public $id;
  public $platform;
  public $name;
  public $path;
  public $comment;
  public $iid;
  public $hash;

  private $members;

  private function __construct($id, $platform, $name, $path, $comment, $iid, $hash) {
    $this->id = $id;
    $this->platform = $platform;
    $this->name = $name;
    $this->path = $path;
    $this->comment = $comment;
    $this->iid = $iid;
    $this->hash = $hash;

    Cache::set('InterfaceVersion', $id, $this);
  }

  public function __toString() {
    return $this->name;
  }

  public function __get($name) {
    if ($name == 'constants' || $name == 'attributes' || $name == 'methods') {
      return $this->getMembers($name);
    }
    return null;
  }

  private function getMembers($name) {
    global $db;

    if (!isset($this->members)) {
      $this->members = array('constants' => array(), 'attributes' => array(), 'methods' => array());
      $rows = $db->arrayQuery('SELECT id, kind, comment, type, name, text, hash FROM members WHERE pint=' . $this->id);
      foreach ($rows as $row) {
        switch ($row['kind']) {
          case 'const':
            array_push($this->members['constants'], new Constant($row['id'],
                                                                 $this, $row['comment'],
                                                                 $row['type'],
                                                                 $row['name'],
                                                                 $row['hash'],
                                                                 $row['text']));
            break;
          case 'attribute':
            array_push($this->members['attributes'], new Attribute($row['id'],
                                                                   $this,
                                                                   $row['comment'],
                                                                   $row['type'],
                                                                   $row['name'],
                                                                   $row['hash'],
                                                                   $row['text']));
            break;
          case 'method':
            array_push($this->members['methods'], new Method($row['id'],
                                                             $this,
                                                             $row['comment'],
                                                             $row['type'],
                                                             $row['name'],
                                                             $row['hash']));
            break;
        }
      }
      usort($this->members['constants'], 'constant_compare');
      usort($this->members['attributes'], 'member_compare');
      usort($this->members['methods'], 'member_compare');
    }

    return $this->members[$name];
  }

  public static function getOrCreate($id, $platform, $name, $path, $comment, $iid, $hash) {
    $result = Cache::get('InterfaceVersion', $id);
    if ($result != null) {
      return $result;
    }
    return new InterfaceVersion($id, $platform, $name, $path, $comment, $iid, $hash);
  }

  public static function getByNameAndPlatform($name, $platform) {
    global $db;

    if ($platform instanceof Platform) {
      $row = $db->rowQuery('SELECT plat_ifaces.* FROM plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                           'WHERE plat_ifaces.platform=' . $platform->id . ' AND interfaces.interface="' . $db->escape($name) . '"');
    }
    else {
      $row = $db->rowQuery('SELECT plat_ifaces.*, platforms.id AS plit, platforms.platform AS plname, platforms.url as plurl '.
                           'FROM plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                           'JOIN platforms ON plat_ifaces.platform=platforms.id WHERE '.
                           'platforms.platform="' . $db->escape($platform) . '" AND interfaces.interface="' . $db->escape($name) . '"');
      $platform = Platform::getOrCreate($row['plid'], $row['plname'], $row['plname'], $row['plurl']);
    }
    return self::getOrCreate($row['plat_ifaces.id'], $platform, $name, $row['plat_ifaces.path'], $row['plat_ifaces.comment'], $row['plat_ifaces.iid'], $row['plat_ifaces.hash']);
  }
}

class Member {
  public $id;
  public $interface;
  public $comment;
  public $type;
  public $name;
  public $hash;

  public function __construct($id, $interface, $comment, $type, $name, $hash) {
    $this->id = $id;
    $this->interface = $interface;
    $this->comment = $comment;
    $this->type = $type;
    $this->name = $name;
    $this->hash = $hash;
  }

  public function __get($name) {
    return null;
  }
}

class Attribute extends Member {
  public $readonly;

  public function __construct($id, $interface, $comment, $type, $name, $hash, $value) {
    parent::__construct($id, $interface, $comment, $type, $name, $hash);
    $this->readonly = $value;
  }
}

class Constant extends Member {
  public $value;

  public function __construct($id, $interface, $comment, $type, $name, $hash, $value) {
    parent::__construct($id, $interface, $comment, $type, $name, $hash);
    $this->value = $value;
  }
}

class Method extends Member {
  private $params;

  public function __get($name) {
    if ($name == 'params') {
      return $this->getParameters();
    }
    return parent::__get($name);
  }

  public function getParameters() {
    global $db;

    if (!isset($this->params)) {
      $this->params = array();
      $rows = $db->arrayQuery('SELECT type, name FROM parameters WHERE member=' . $this->id . ' ORDER BY pos');
      foreach ($rows as $row) {
        array_push($this->params, new Parameter($this, $row['type'], $row['name']));
      }
    }
    return $this->params;
  }
}

class Parameter {
  public $method;
  public $type;
  public $name;

  public function __construct($method, $type, $name) {
    $this->method = $method;
    $this->type = $type;
    $this->name = $name;
  }
}

?>