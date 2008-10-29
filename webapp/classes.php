<?php

define('FLAG_NOSCRIPT',  1);
define('FLAG_NOTXPCOM', 2);
define('FLAG_READONLY', 4);
define('FLAG_SCRIPTABLE', 8);
define('FLAG_FUNCTION', 16);

define('FLAG_CONST', 1);
define('FLAG_ARRAY', 2);
define('FLAG_RETVAL', 4);
define('FLAG_SHARED', 8);
define('FLAG_OPTIONAL', 16);

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

function member_name_compare($a, $b) {
  if ($a->name == $b->name) {
    return 0;
  }
  return ($a->name < $b->name) ? -1 : 1;
}

function member_value_compare($a, $b) {
  if ($a->value == $b->value) {
    return 0;
  }
  return ($a->value < $b->value) ? -1 : 1;
}

function member_line_compare($a, $b) {
  if ($a->line == $b->line) {
    return 0;
  }
  return ($a->line < $b->line) ? -1 : 1;
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
  private $row;
  private $prefix;

  private function __construct($row, $prefix = 'platforms.') {
    $this->row = $row;
    $this->prefix = $prefix;

    Cache::set('Platform', $row[$prefix . 'id'], $this);
  }

  public function __toString() {
    return $this->name;
  }

  public function __isset($name) {
    return isset($this->row[$this->prefix . $name]);
  }

  public function __get($name) {
    return $this->row[$this->prefix . $name];
  }

  public function getSourceURL($path, $line) {
    return str_replace(array('%p', '%l'), array($path, $line), $this->sourceurl);
  }

  public function getInterfaces() {
    global $db;

    $interfaces = array();
    $rows = $db->arrayQuery('SELECT plat_ifaces.*,interfaces.* FROM plat_ifaces JOIN '.
                            'interfaces ON interfaces.id=plat_ifaces.interface WHERE platform='.
                            $this->id . ' ORDER BY interfaces.name');
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
    return new Platform($row, $prefix);
  }

  public static function getByVersion($name) {
    global $db;

    $row = $db->rowQuery('SELECT platforms.* FROM platforms WHERE version="' . $db->escape($name) . '"');
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
  private $row;
  private $prefix;
  private $versions;

  private function __construct($row, $prefix = 'interfaces.') {
    $this->row = $row;
    $this->prefix = $prefix;

    Cache::set('XPCOMInterface', $row[$prefix . 'id'], $this);
  }

  public function __toString() {
    return $this->name;
  }

  public function __isset($name) {
    switch ($name) {
      case 'versions':
        return true;
      case 'oldest':
      case 'newest':
        $version = $this->getVersions();
        return count($versions) > 0;
        break;
    }
    return isset($this->row[$this->prefix . $name]);
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
    return $this->row[$this->prefix . $name];
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
    return new XPCOMInterface($row, $prefix);
  }

  public static function getByName($name) {
    global $db;

    $versions = array();
    $rows = $db->arrayQuery('SELECT interfaces.*, plat_ifaces.*, platforms.* '.
                            'FROM plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                            'JOIN platforms ON plat_ifaces.platform=platforms.id WHERE interfaces.name="' . $db->escape($name) . '"');
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

  public static function searchForName($string) {
    global $db;

    $versions = array();
    $rows = $db->arrayQuery('SELECT interfaces.*, plat_ifaces.*, platforms.* '.
                            'FROM plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                            'JOIN platforms ON plat_ifaces.platform=platforms.id '.
                            'WHERE interfaces.name LIKE "%' . $db->escape($string) . '%"');

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

  public static function getAllInterfaces() {
    global $db;

    $versions = array();
    $rows = $db->arrayQuery('SELECT interfaces.*, plat_ifaces.*, platforms.* '.
                            'FROM plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                            'JOIN platforms ON plat_ifaces.platform=platforms.id ORDER BY interfaces.name');

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
  private $row;
  private $prefix;
  private $versions;
  public $platform;
  public $name;

  private $members;

  private function __construct($row, $versions, $platform, $name, $prefix = 'plat_ifaces.') {
    $this->row = $row;
    $this->prefix = $prefix;
    $this->versions = $versions;
    $this->platform = $platform;
    $this->name = $name;

    Cache::set('InterfaceVersion', $row[$prefix . 'id'], $this);
  }

  public function __toString() {
    return $this->name;
  }

  public function __isset($name) {
    switch ($name) {
      case 'constants':
      case 'attributes':
      case 'methods':
      case 'versions':
      case 'sourceurl':
      case 'noscript':
      case 'scriptable':
      case 'function':
        return true;
        break;
    }
    return isset($this->row[$this->prefix . $name]);
  }

  public function __get($name) {
    switch ($name) {
      case 'constants':
      case 'attributes':
      case 'methods':
        return $this->getMembers($name);
        break;
      case 'versions':
        return $this->versions->getVersions();
        break;
      case 'sourceurl':
        return $this->platform->getSourceURL($this->path, $this->line);
        break;
      case 'noscript':
        return ($this->flags & FLAG_NOSCRIPT) != 0;
        break;
      case 'scriptable':
        return ($this->flags & FLAG_SCRIPTABLE) != 0;
        break;
      case 'function':
        return ($this->flags & FLAG_FUNCTION) != 0;
        break;
    }
    return $this->row[$this->prefix . $name];
  }

  private function getMembers($name) {
    global $db;

    if (!isset($this->members)) {
      $this->members = array('constants' => array(), 'attributes' => array(), 'methods' => array());
      $rows = $db->arrayQuery('SELECT members.*,interfaces.id FROM '.
                              'members LEFT JOIN interfaces ON members.type=interfaces.name '.
                              'WHERE pint=' . $this->id);
      foreach ($rows as $row) {
        $member = Method::getOrCreate($row, $this);
        if ($member instanceof Constant) {
          array_push($this->members['constants'], $member);
        }
        else if ($member instanceof Attribute) {
          array_push($this->members['attributes'], $member);
        }
        else if ($member instanceof Method) {
          array_push($this->members['methods'], $member);
        }
      }
      usort($this->members['constants'], 'member_line_compare');
      usort($this->members['attributes'], 'member_name_compare');
      usort($this->members['methods'], 'member_name_compare');
    }

    return $this->members[$name];
  }

  public static function getOrCreate($row, $versions, $platform, $name, $prefix = 'plat_ifaces.') {
    $result = Cache::get('InterfaceVersion', $row[$prefix . 'id']);
    if ($result != null) {
      return $result;
    }
    return new InterfaceVersion($row, $versions, $platform, $name, $prefix);
  }

  public static function getByNameAndPlatform($name, $platform) {
    global $db;

    if ($platform instanceof Platform) {
      $row = $db->rowQuery('SELECT plat_ifaces.*,interfaces.* FROM plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                           'WHERE plat_ifaces.platform=' . $platform->id . ' AND interfaces.name="' . $db->escape($name) . '"');
      if ($row === false) {
        return null;
      }
    }
    else {
      $row = $db->rowQuery('SELECT plat_ifaces.*, interfaces.*, platforms.* '.
                           'FROM plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                           'JOIN platforms ON plat_ifaces.platform=platforms.id WHERE '.
                           'platforms.version="' . $db->escape($platform) . '" AND interfaces.name="' . $db->escape($name) . '"');
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
  private $row;
  private $prefix;
  public $interface;

  public function __construct($row, $interface, $prefix = 'members.') {
    $this->row = $row;
    $this->prefix = $prefix;
    $this->interface = $interface;

    Cache::set('Member', $row[$prefix . 'id'], $this);
  }

  public function __isset($name) {
    switch ($name) {
      case 'sourceurl':
      case 'typeisif':
        return true;
        break;
    }
    return isset($this->row[$this->prefix . $name]);
  }

  public function __get($name) {
    switch ($name) {
      case 'sourceurl':
        return $this->interface->platform->getSourceURL($this->interface->path, $this->line);
        break;
      case 'typeisif':
        return (isset($this->row['interfaces.id']) && $this->row['interfaces.id'] != false);
        break;
    }
    return $this->row[$this->prefix . $name];
  }

  public static function getOrCreate($row, $interface, $prefix = 'members.') {
    $result = Cache::get('Member', $row[$prefix . 'id']);
    if ($result != null) {
      return $result;
    }
    switch ($row[$prefix . 'kind']) {
      case 'const':
        return new Constant($row, $interface, $prefix);
        break;
      case 'attribute':
        return new Attribute($row, $interface, $prefix);
        break;
      case 'method':
        return new Method($row, $interface, $prefix);
        break;
    }
  }
}

class Attribute extends Member {
  public function __isset($name) {
    switch ($name) {
      case 'attributes':
      case 'readonly':
      case 'noscript':
      case 'notxpcom':
        return true;
        break;
      case 'binaryname':
        return $this->text != '';
        break;
    }
    return parent::__get($name);
  }

  public function __get($name) {
    switch ($name) {
      case 'attributes':
        $type = '';
        if ($this->noscript) {
          $type .= 'noscript, ';
        }
        if ($this->notxpcom) {
          $type .= 'notxpcom, ';
        }
        if (isset($this->binaryname)) {
          $type .= 'binaryname(' . $this->binaryname . '), ';
        }
        if ($type != '') {
          $type = substr($type, 0, -2);
        }
        return $type;
        break;
      case 'readonly':
        return ($this->flags & FLAG_READONLY) != 0;
        break;
      case 'noscript':
        return ($this->flags & FLAG_NOSCRIPT) != 0;
        break;
      case 'notxpcom':
        return ($this->flags & FLAG_NOTXPCOM) != 0;
        break;
      case 'binaryname':
        return $this->text;
        break;
    }
    return parent::__get($name);
  }
}

class Constant extends Member {
  public function __get($name) {
    if ($name == 'value') {
      return $this->text;
    }
    return parent::__get($name);
  }
}

class Method extends Member {
  private $params;

  public function __isset($name) {
    switch ($name) {
      case 'attributes':
      case 'params':
      case 'noscript':
      case 'notxpcom':
        return true;
        break;
      case 'binaryname':
        return $this->text != '';
        break;
    }
    return parent::__get($name);
  }

  public function __get($name) {
    switch ($name) {
      case 'attributes':
        $type = '';
        if ($this->noscript) {
          $type .= 'noscript, ';
        }
        if ($this->notxpcom) {
          $type .= 'notxpcom, ';
        }
        if (isset($this->binaryname)) {
          $type .= 'binaryname(' . $this->binaryname . '), ';
        }
        if ($type != '') {
          $type = substr($type, 0, -2);
        }
        return $type;
        break;
      case 'params':
        return $this->getParameters();
        break;
      case 'noscript':
        return ($this->flags & FLAG_NOSCRIPT) != 0;
        break;
      case 'notxpcom':
        return ($this->flags & FLAG_NOTXPCOM) != 0;
        break;
      case 'binaryname':
        return $this->text;
        break;
    }
    return parent::__get($name);
  }

  public function getParameters() {
    global $db;

    if (!isset($this->params)) {
      $this->params = array();
      $rows = $db->arrayQuery('SELECT parameters.*,interfaces.id FROM '.
                              'parameters LEFT JOIN interfaces ON parameters.type=interfaces.name '.
                              'WHERE member=' . $this->id . ' ORDER BY parameters.pos');
      foreach ($rows as $row) {
        array_push($this->params, new Parameter($row, $this));
      }
    }
    return $this->params;
  }
}

class Parameter {
  private $prefix;
  private $row;
  public $method;

  public function __construct($row, $method, $prefix = 'parameters.') {
    $this->row = $row;
    $this->prefix = $prefix;
    $this->method = $method;
  }

  public function __isset($name) {
    switch ($name) {
      case 'attributes':
      case 'typeisif':
      case 'const':
      case 'array':
      case 'retval':
      case 'shared':
      case 'optional':
        return true;
        break;
      case 'iid_is':
        return $this->iidis != '';
        break;
      case 'size_is':
        return $this->sizeis != '';
        break;
    }
    return isset($this->row[$this->prefix . $name]);
  }

  public function __get($name) {
    switch ($name) {
      case 'attributes':
        $type = '';
        if ($this->const) {
          $type .= 'const, ';
        }
        if ($this->array) {
          $type .= 'array, ';
        }
        if ($this->retval) {
          $type .= 'retval, ';
        }
        if ($this->shared) {
          $type .= 'shared, ';
        }
        if ($this->optional) {
          $type .= 'optional, ';
        }
        if (isset($this->iid_is)) {
          $type .= 'iid_is(' . $this->iid_is . '), ';
        }
        if (isset($this->size_is)) {
          $type .= 'size_is(' . $this->size_is . '), ';
        }
        if ($type != '') {
          $type = substr($type, 0, -2);
        }
        return $type;
        break;
      case 'typeisif':
        return (isset($this->row['interfaces.id']) && $this->row['interfaces.id'] != false);
        break;
      case 'const':
        return ($this->flags & FLAG_CONST) != 0;
        break;
      case 'array':
        return ($this->flags & FLAG_ARRAY) != 0;
        break;
      case 'retval':
        return ($this->flags & FLAG_RETVAL) != 0;
        break;
      case 'shared':
        return ($this->flags & FLAG_SHARED) != 0;
        break;
      case 'optional':
        return ($this->flags & FLAG_OPTIONAL) != 0;
        break;
      case 'iid_is':
        return $this->iidis;
        break;
      case 'size_is':
        return $this->sizeis;
        break;
    }
    return $this->row[$this->prefix . $name];
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

    $rows = $db->arrayQuery('SELECT interfaces.* FROM '.
                            '(SELECT * FROM plat_ifaces WHERE platform=' . $right->id .') AS pi1 '.
                            'LEFT JOIN (SELECT * FROM plat_ifaces WHERE platform=' . $left->id . ') AS pi2 '.
                            'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=interfaces.id '.
                            'WHERE pi2.platform IS NULL ORDER BY interfaces.name');
    foreach ($rows as $row) {
      array_push($this->added, XPCOMInterface::getOrCreate($row));
    }

    $rows = $db->arrayQuery('SELECT interfaces.* FROM '.
                            '(SELECT * FROM plat_ifaces WHERE platform=' . $left->id .') AS pi1 '.
                            'LEFT JOIN (SELECT * FROM plat_ifaces WHERE platform=' . $right->id . ') AS pi2 '.
                            'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=interfaces.id '.
                            'WHERE pi2.platform IS NULL ORDER BY interfaces.name');
    foreach ($rows as $row) {
      array_push($this->removed, XPCOMInterface::getOrCreate($row));
    }

    $rows = $db->arrayQuery('SELECT interfaces.* FROM '.
                            '(SELECT * FROM plat_ifaces WHERE platform=' . $left->id .') AS pi1 '.
                            'JOIN (SELECT * FROM plat_ifaces WHERE platform=' . $right->id . ') AS pi2 '.
                            'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=interfaces.id WHERE pi1.hash=pi2.hash ORDER BY interfaces.name');
    foreach ($rows as $row) {
      array_push($this->unchanged, XPCOMInterface::getOrCreate($row));
    }

    $rows = $db->arrayQuery('SELECT interfaces.* FROM '.
                            '(SELECT * FROM plat_ifaces WHERE platform=' . $left->id .') AS pi1 '.
                            'JOIN (SELECT * FROM plat_ifaces WHERE platform=' . $right->id . ') AS pi2 '.
                            'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=interfaces.id WHERE pi1.hash<>pi2.hash ORDER BY interfaces.name');
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

    $this->constants = $this->getLineMemberPairs($left->constants, $right->constants);
    $this->attributes = $this->getNameMemberPairs($left->attributes, $right->attributes);
    $this->methods = $this->getNameMemberPairs($left->methods, $right->methods);
  }

  private function getLineMemberPairs($left, $right) {
    $pairs = array();

    $names = array();
    $pos = 0;
    while ($pos < count($left)) {
      $names[$left[$pos]->name] = $pos;
      $pos++;
    }

    $r = reset($right);
    while ($r !== false) {
      if (isset($names[$r->name])) {
        array_push($pairs, new MemberPair($left[$names[$r->name]], $r));
        unset($left[$names[$r->name]]);
      }
      else {
        array_push($pairs, new MemberPair(null, $r));
      }
      $r = next($right);
    }

    $l = reset($left);
    while ($l !== false) {
      $pos = 0;
      while (($pos < count($pairs)) && (($pairs[$pos]->left == null) ||
                                        ($pairs[$pos]->left->line < $l->line))) {
        
        $pos++;
      }
      array_splice($pairs, $pos, 0, array(new MemberPair($l, null)));
      $l = next($left);
    }

    return $pairs;
  }

  private function getNameMemberPairs($left, $right) {
    $pairs = array();
    $l = reset($left);
    $r = reset($right);

    while ($l !== false || $r !== false) {
      if ($r === false || $l->name < $r->name) {
        array_push($pairs, new MemberPair($l, null));
        $l = next($left);
      }
      else if ($l === false || $r->name < $l->name) {
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