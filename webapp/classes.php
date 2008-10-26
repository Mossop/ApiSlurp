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
    if ($rows === false || count($rows) == 0) {
      return false;
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

$versioncomparator = new VersionComparator();

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

function interfaceversion_compare($a, $b) {
  global $versioncomparator;

  return $versioncomparator->compareVersions($a->platform->version, $b->platform->version);
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

  public static function set($type, $key, $value) {
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
    $rows = $db->arrayQuery('SELECT plat_ifaces.*,interfaces.* FROM plat_ifaces JOIN '.
                            'interfaces ON interfaces.id=plat_ifaces.interface WHERE platform='.
                            $this->id . ' ORDER BY interfaces.interface');
    foreach ($rows as $row) {
      $interface = XPCOMInterface::getOrCreate($row);
      array_push($interfaces, InterfaceVersion::getOrCreate($row,
                                                            $interface,
                                                            $this,
                                                            $interface->name));
    }
    return $interfaces;
  }

  public static function getOrCreate($row, $prefix = 'platforms.') {
    $result = Cache::get('Platform', $row[$prefix . 'id']);
    if ($result != null) {
      return $result;
    }
    return new Platform($row[$prefix . 'id'],
                        $row[$prefix . 'platform'],
                        $row[$prefix . 'platform'],
                        $row[$prefix . 'url']);
  }

  public static function getByName($name) {
    global $db;

    $row = $db->rowQuery('SELECT platforms.* FROM platforms WHERE platform="' . $db->escape($name) . '"');
    if ($row === false) {
      return null;
    }
    return Platform::getOrCreate($row, '');
  }

  public static function getAllPlatforms() {
    global $db;

    $platforms = array();
    $rows = $db->arrayQuery('SELECT platforms.* FROM platforms');
    foreach ($rows as $row) {
      array_push($platforms, Platform::getOrCreate($row, ''));
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

  public function __get($name) {
    switch ($name) {
      case 'versions':
        return $this->getVersions();
        break;
      case 'oldest':
        return $this->getOldestVersion();
        break;
      case 'newest':
        return $this->getNewestVersion();
        break;
    }
    return null;
  }

  public function getVersions() {
    global $db;

    if (isset($this->versions)) {
      return $this->versions;
    }

    $this->versions = array();
    $rows = $db->arrayQuery('SELECT plat_ifaces.*, platforms.* FROM '.
                            'plat_ifaces JOIN platforms ON plat_ifaces.platform=platforms.id '.
                            'WHERE plat_ifaces.interface=' . $this->id);
    foreach ($rows as $row) {
      $platform = Platform::getOrCreate($row);
      array_push($this->versions, InterfaceVersion::getOrCreate($row,
                                                                $this,
                                                                $platform,
                                                                $this->name));
    }
    usort($this->versions, 'interfaceversion_compare');
    return $this->versions;
  }

  public function getNewestVersion() {
    $versions = $this->getVersions();
    return $versions[count($versions) - 1];
  }

  public function getOldestVersion() {
    $versions = $this->getVersions();
    return $versions[0];
  }

  public static function getOrCreate($row, $prefix = 'interfaces.') {
    $result = Cache::get('XPCOMInterface', $row[$prefix . 'id']);
    if ($result != null) {
      return $result;
    }
    return new XPCOMInterface($row[$prefix . 'id'], $row[$prefix . 'interface']);
  }

  public static function getByName($name) {
    global $db;

    $versions = array();
    $rows = $db->arrayQuery('SELECT interfaces.*, plat_ifaces.*, platforms.* '.
                            'FROM plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                            'JOIN platforms ON plat_ifaces.platform=platforms.id WHERE interfaces.interface="' . $db->escape($name) . '"');
    if ($rows === false || count($rows) == 0) {
      return null;
    }
    if (Cache::has('XPCOMInterface', $rows[0]['interfaces.id'])) {
      return Cache::get('XPCOMInterface', $rows[0]['interfaces.id']);
    }

    $interface = self::getOrCreate($rows[0]);

    $interface->versions = array();
    foreach ($rows as $row) {
      $platform = Platform::getOrCreate($row);
      array_push($interface->versions, InterfaceVersion::getOrCreate($row,
                                                                     $interface,
                                                                     $platform,
                                                                     $interface->name));
    }
    usort($interface->versions, 'interfaceversion_compare');
    return $interface;
  }

  public static function getAllInterfaces() {
    global $db;

    $versions = array();
    $rows = $db->arrayQuery('SELECT interfaces.*, plat_ifaces.*, platforms.* '.
                            'FROM plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                            'JOIN platforms ON plat_ifaces.platform=platforms.id ORDER BY interfaces.interface');

    $interface = self::getOrCreate($rows[0]);

    $interfaces = array();
    foreach ($rows as $row) {
      $interface = Cache::get('XPCOMInterface', $row['interfaces.id']);
      if ($interface == null) {
        $interface = self::getOrCreate($row);
        array_push($interfaces, $interface);
      }
      if (!isset($interface->versions)) {
        $interface->versions = array();
      }
      $platform = Platform::getOrCreate($row);
      array_push($interface->versions, InterfaceVersion::getOrCreate($row,
                                                                     $interface,
                                                                     $platform,
                                                                     $interface->name));
      usort($interface->versions, 'interfaceversion_compare');
    }
    return $interfaces;
  }
}

class InterfaceVersion {
  public $id;
  private $versions;
  public $platform;
  public $name;
  public $path;
  public $line;
  public $comment;
  public $iid;
  public $hash;

  private $members;

  private function __construct($id, $versions, $platform, $name, $path, $line, $comment, $iid, $hash) {
    $this->id = $id;
    $this->versions = $versions;
    $this->platform = $platform;
    $this->name = $name;
    $this->path = $path;
    $this->line = $line;
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
    if ($name == 'versions') {
      return $this->versions->getVersions();
    }
    if ($name == 'sourceurl') {
      return $this->platform->sourceurl . $this->path . '#' . $this->line;
    }
    return null;
  }

  private function getMembers($name) {
    global $db;

    if (!isset($this->members)) {
      $this->members = array('constants' => array(), 'attributes' => array(), 'methods' => array());
      $rows = $db->arrayQuery('SELECT * FROM members WHERE pint=' . $this->id);
      foreach ($rows as $row) {
        switch ($row['kind']) {
          case 'const':
            array_push($this->members['constants'], new Constant($row, $this, ''));
            break;
          case 'attribute':
            array_push($this->members['attributes'], new Attribute($row, $this, ''));
            break;
          case 'method':
            array_push($this->members['methods'], new Method($row, $this, ''));
            break;
        }
      }
      usort($this->members['constants'], 'constant_compare');
      usort($this->members['attributes'], 'member_compare');
      usort($this->members['methods'], 'member_compare');
    }

    return $this->members[$name];
  }

  public static function getOrCreate($row, $versions, $platform, $name, $prefix = 'plat_ifaces.') {
    $result = Cache::get('InterfaceVersion', $row[$prefix . 'id']);
    if ($result != null) {
      return $result;
    }
    return new InterfaceVersion($row[$prefix . 'id'],
                                $versions,
                                $platform,
                                $name,
                                $row[$prefix . 'path'],
                                $row[$prefix . 'line'],
                                $row[$prefix . 'comment'],
                                $row[$prefix . 'iid'],
                                $row[$prefix . 'hash']);
  }

  public static function getByNameAndPlatform($name, $platform) {
    global $db;

    if ($platform instanceof Platform) {
      $row = $db->rowQuery('SELECT plat_ifaces.*,interfaces.* FROM plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                           'WHERE plat_ifaces.platform=' . $platform->id . ' AND interfaces.interface="' . $db->escape($name) . '"');
      if ($row === false) {
        return null;
      }
    }
    else {
      $row = $db->rowQuery('SELECT plat_ifaces.*, interfaces.*, platforms.* '.
                           'FROM plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                           'JOIN platforms ON plat_ifaces.platform=platforms.id WHERE '.
                           'platforms.platform="' . $db->escape($platform) . '" AND interfaces.interface="' . $db->escape($name) . '"');
      if ($row === false) {
        return null;
      }
      $platform = Platform::getOrCreate($row);
    }
    $interface = XPCOMInterface::getOrCreate($row);
    return self::getOrCreate($row,
                             $interface,
                             $platform,
                             $interface->name);
  }
}

class Member {
  public $id;
  public $interface;
  public $line;
  public $comment;
  public $type;
  public $name;
  public $hash;

  public function __construct($row, $interface, $prefix = 'members.') {
    $this->id = $row[$prefix . 'id'];
    $this->interface = $interface;
    $this->line = $row[$prefix . 'line'];
    $this->comment = $row[$prefix . 'comment'];
    $this->type = $row[$prefix . 'type'];
    $this->name = $row[$prefix . 'name'];
    $this->hash = $row[$prefix . 'hash'];
  }

  public function __get($name) {
    if ($name == 'sourceurl') {
      return $this->interface->platform->sourceurl . $this->interface->path . '#' . $this->line;
    }
    return null;
  }
}

class Attribute extends Member {
  public $readonly;

  public function __construct($row, $interface, $prefix = 'members.') {
    parent::__construct($row, $interface, $prefix);
    $this->readonly = $row[$prefix . 'text'];
  }
}

class Constant extends Member {
  public $value;

  public function __construct($row, $interface, $prefix = 'members.') {
    parent::__construct($row, $interface, $prefix);
    $this->value = $row[$prefix . 'text'];
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


class PlatformDiff {
  public $left;
  public $right;

  public $added = array();
  public $removed = array();
  public $modified = array();
  public $unchanged = array();

  public function __construct($left, $right) {
    global $db;

    $this->left = $left;
    $this->right = $right;

    $rows = $db->arrayQuery('SELECT interfaces.id, interfaces.interface FROM '.
                            '(SELECT * FROM plat_ifaces WHERE platform=' . $right->id .') AS pi1 '.
                            'LEFT JOIN (SELECT * FROM plat_ifaces WHERE platform=' . $left->id . ') AS pi2 '.
                            'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=interfaces.id '.
                            'WHERE pi2.platform IS NULL ORDER BY interfaces.interface');
    foreach ($rows as $row) {
      array_push($this->added, XPCOMInterface::getOrCreate($row));
    }

    $rows = $db->arrayQuery('SELECT interfaces.id, interfaces.interface FROM '.
                            '(SELECT * FROM plat_ifaces WHERE platform=' . $left->id .') AS pi1 '.
                            'LEFT JOIN (SELECT * FROM plat_ifaces WHERE platform=' . $right->id . ') AS pi2 '.
                            'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=interfaces.id '.
                            'WHERE pi2.platform IS NULL ORDER BY interfaces.interface');
    foreach ($rows as $row) {
      array_push($this->removed, XPCOMInterface::getOrCreate($row));
    }

    $rows = $db->arrayQuery('SELECT interfaces.id, interfaces.interface FROM '.
                            '(SELECT * FROM plat_ifaces WHERE platform=' . $left->id .') AS pi1 '.
                            'JOIN (SELECT * FROM plat_ifaces WHERE platform=' . $right->id . ') AS pi2 '.
                            'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=interfaces.id WHERE pi1.hash=pi2.hash ORDER BY interfaces.interface');
    foreach ($rows as $row) {
      array_push($this->unchanged, XPCOMInterface::getOrCreate($row));
    }

    $rows = $db->arrayQuery('SELECT interfaces.id, interfaces.interface FROM '.
                            '(SELECT * FROM plat_ifaces WHERE platform=' . $left->id .') AS pi1 '.
                            'JOIN (SELECT * FROM plat_ifaces WHERE platform=' . $right->id . ') AS pi2 '.
                            'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=interfaces.id WHERE pi1.hash<>pi2.hash ORDER BY interfaces.interface');
    foreach ($rows as $row) {
      array_push($this->modified, XPCOMInterface::getOrCreate($row));
    }
  }
}

class InterfaceDiff {
  public $left;
  public $right;

  public $constants = array();
  public $attributes = array();
  public $methods = array();

  public function __construct($left, $right) {
    global $db;

    $this->left = $left;
    $this->right = $right;

    $this->constants = $this->getMemberPairs($left->constants, $right->constants, 'constant_compare');
    $this->attributes = $this->getMemberPairs($left->attributes, $right->attributes, 'member_compare');
    $this->methods = $this->getMemberPairs($left->methods, $right->methods, 'member_compare');
  }

  private function getMemberPairs($left, $right, $compare) {
    $pairs = array();
    $l = reset($left);
    $r = reset($right);

    while ($l !== false || $r !== false) {
      if ($l !== false && $r !== false) {
        $dif = $compare($l, $r);
      }
      if ($dif < 0 || $r === false) {
        array_push($pairs, new MemberPair($l, null));
        $l = next($left);
      }
      else if ($dif > 0 || $l === false) {
        array_push($pairs, new MemberPair(null, $r));
        $r = next($right);
      }
      else {
        array_push($pairs, new MemberPair($l, $r));
        $l = next($left);
        $r = next($right);
      }
    }

    return $pairs;
  }
}

class MemberPair {
  public $left;
  public $right;
  public $state;

  public function __construct($left, $right) {
    $this->left = $left;
    $this->right = $right;

    if ($left == null) {
      $this->state = 'added';
    }
    else if ($right == null) {
      $this->state = 'removed';
    }
    else if ($left->hash != $right->hash) {
      $this->state = 'modified';
    }
    else {
      $this->state = 'unchanged';
    }
  }
}

?>