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

class Database {
  public function escape($str) {
    return addslashes($str);
  }

  public function arrayQuery($query) {
  }

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

class Platform {
  public $id;
  public $name;
  public $version;
  public $sourceurl;

  public function __construct($id, $name, $version, $sourceurl) {
    $this->id = $id;
    $this->name = $name;
    $this->version = $version;
    $this->sourceurl = $sourceurl;
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
      array_push($interfaces, new XPCOMInterface($row['id'], $this, $row['name'], $row['path'],
                                                 $row['comment'], $row['iid'], $row['hash']));
    }
    return $interfaces;
  }

  public static function getByName($name) {
    global $db;

    $row = $db->rowQuery('SELECT * FROM platforms WHERE platform="' . $db->escape($name) . '"');
    if ($row === false) {
      return null;
    }
    return new Platform($row['id'], $row['platform'], $row['platform'], $row['url']);
  }

  public static function getAllPlatforms() {
    global $db;

    $platforms = array();
    $rows = $db->arrayQuery('SELECT * FROM platforms');
    foreach ($rows as $row) {
      array_push($platforms, new Platform($row['id'], $row['platform'], $row['platform'], $row['url']));
    }
    return $platforms;
  }
}

class XPCOMInterface {
  public $id;
  public $platform;
  public $name;
  public $path;
  public $comment;
  public $iid;
  public $hash;

  public function __construct($id, $platform, $name, $path, $comment, $iid, $hash) {
    $this->id = $id;
    $this->platform = $platform;
    $this->name = $name;
    $this->path = $path;
    $this->comment = $comment;
    $this->iid = $iid;
    $this->hash = $hash;
  }

  public function __toString() {
    return $this->name;
  }

  public function getMembers() {
  }

  public function getConstants() {
  }

  public function getAttributes() {
  }

  public function getMethods() {
  }
}

class Member {
  public $id;
  public $interface;
  public $type;
  public $name;
  public $hash;
}

class Attribute extends Member {
}

class Constant extends Member {
  public $value;
}

class Method extends Member {
  public function getParameters() {
  }
}

class Parameter {
  public $method;
  public $type;
  public $name;
}

?>