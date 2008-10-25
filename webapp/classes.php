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

  function __construct() {
    parent::__construct();

    $this->template_dir         = 'templates/';
    $this->compile_dir          = 'compiled/';
    $this->cache_dir            = 'cache/';
    $this->cache_modified_check = true;

    $this->starttime = microtime(true);
  }

  function display($template, $cacheid = null, $compileid = null) {
    $time = round(100 * (microtime(true) - $this->starttime)) / 100;
    $this->assign('parsetime', $time);

    parent::display($template, $cacheid, $compileid);
  }
}

class Database {
  function escape($str) {
    return addslashes($str);
  }

  function arrayQuery($query) {
  }

  function singleQuery($query) {
    $rows = $this->arrayQuery($query);
    if ($rows === false) {
      return $rows;
    }
    return $rows[0][0];
  }

  function columnQuery($query) {
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

  function rowQuery($query) {
    $rows = $this->arrayQuery($query);
    if ($rows === false) {
      return $rows;
    }
    return $rows[0];
  }
}

class SQLiteDB extends Database {
  private $dbres;

  function __construct($filename) {
    $this->dbres = sqlite_popen($filename);
  }

  function escape($str) {
    return sqlite_escape_string($str);
  }

  function arrayQuery($query) {
    return sqlite_array_query($this->dbres, $query);
  }

  function singleQuery($query) {
    return sqlite_single_query($this->dbres, $query, true);
  }

  function columnQuery($query) {
    return sqlite_single_query($this->dbres, $query, false);
  }
}
?>