<?php
if (!is_dir('compiled') || !is_dir('cache') || !is_file('smarty/Smarty.class.php')) {
  print 'Web-app not properly set up. Create compile and cache directories and install smarty.';
  exit;
}

require_once('smarty/Smarty.class.php');
require_once('config.php');

class APISmarty extends Smarty {
  private $starttime;

  function APISmarty() {
    $this->Smarty();

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

function error($title, $text) {
  global $smarty;

  $smarty->caching = false;

  $smarty->assign('error', $title);
  $smarty->assign('message', $text);
  $smarty->display('error.tpl');
  exit;
}

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

$smarty = new APISmarty();

$smarty->assign('ROOT', $webroot);

if (!is_file($dbpath)) {
  error('No database', 'The API database was not found.');
}

try {
  $dbres = sqlite_popen($dbpath);
}
catch (Exception $e) {
  error('Corrupt database', 'The database could not be opened: ' . $e->getMessage());
}

$smarty->caching = true;

function sqlesc($str) {
  return sqlite_escape_string($str);
}

function sql_array($query) {
  global $dbres;

  $result = sqlite_array_query($dbres, $query);
  if ($result === FALSE) {
    error('Database Error', 'Something this page did caused a database error. Please email <a href="mailto:dtownsend@mozilla.com">Dave</a> with the page url.');
    //error('Invalid SQL', 'This page attempted to run some invalid SQL: ' . $query . '<br />' . sqlite_error_string(sqlite_last_error($dbres)));
  }

  return $result;
}

function sql_single($query, $firstOnly = false) {
  global $dbres;

  $result = sqlite_single_query($dbres, $query, $firstOnly);
  if ($result === FALSE) {
    error('Database Error', 'Something this page did caused a database error. Please email <a href="mailto:dtownsend@mozilla.com">Dave</a> with the page url.');
    //error('Invalid SQL', 'This page attempted to run some invalid SQL: ' . $query . '<br />' . sqlite_error_string(sqlite_last_error($dbres)));
  }

  return $result;
}

function get_newest_platform($names) {
  $max = $names[0];
  $vc = new VersionComparator();
  for ($i = 1; $i < count($names); $i++) {
    if ($vc->compareVersions($max, $names[$i]) < 0) {
      $max = $names[$i];
    }
  }
  return $max;
}

function get_platform($name) {
  return sql_single('SELECT id FROM platforms WHERE platform="' . sqlesc($name) . '"', true);
}

function get_platform_names($interface = null) {
  if ($interface == null) {
    return sql_single('SELECT platform FROM platforms');
  }
  else {
    return sql_single('SELECT platforms.platform FROM '.
                      'plat_ifaces JOIN platforms ON plat_ifaces.platform=platforms.id '.
                      'WHERE interface=' . $interface . ' ORDER BY platforms.id');
  }
}

function get_interface($name) {
  return sql_single('SELECT id FROM interfaces WHERE interface="' . sqlesc($name) .'"', true);
}

function get_interface_names($platform = null) {
  if ($platform === null) {
    return sql_single('SELECT interface FROM interfaces ORDER BY interface');
  }
  else {
    return sql_single('SELECT interfaces.interface FROM '.
                      'plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                      'WHERE platform=' . $platform . ' ORDER BY interfaces.interface');
  }
}

function get_plat_iface_id($iid, $platform) {
  return sql_single('SELECT plat_ifaces.id FROM plat_ifaces JOIN platforms ON plat_ifaces.platform=platforms.id '.
                    'WHERE platforms.platform="' . sqlesc($platform) . '" AND interface=' . $iid, true);
}

function get_plat_iface($iid, $platform) {
  $rows = sql_array('SELECT plat_ifaces.id AS id, plat_ifaces.iid AS iid, plat_ifaces.comment AS comment, plat_ifaces.hash AS hash FROM plat_ifaces JOIN platforms ON plat_ifaces.platform=platforms.id '.
                    'WHERE platforms.platform="' . sqlesc($platform) . '" AND interface=' . $iid);
  return $rows[0];
}

?>