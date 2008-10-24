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

$smarty->caching = false;

function sqlesc($str) {
  return sqlite_escape_string($str);
}

function sql_array($query) {
  global $dbres;

  $result = sqlite_array_query($dbres, $query);
  if ($result === FALSE) {
    error('Invalid SQL', 'This page attempted to run some invalid SQL: ' . $query . '<br />' . sqlite_error_string(sqlite_last_error($dbres)));
  }

  return $result;
}

function sql_single($query, $firstOnly = false) {
  global $dbres;

  $result = sqlite_single_query($dbres, $query, $firstOnly);
  if ($result === FALSE) {
    error('Invalid SQL', 'This page attempted to run some invalid SQL: ' . $query . '<br />' . sqlite_error_string(sqlite_last_error($dbres)));
  }

  return $result;
}

function get_newest_platform($names) {
  return $names[count($names) - 1];
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

?>