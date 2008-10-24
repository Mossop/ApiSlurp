<?php
if (!is_dir('compiled') || !is_dir('cache') || !is_file('smarty/Smarty.class.php')) {
  print 'Web-app not properly set up. Create compile and cache directories and install smarty.';
  exit;
}

require_once('smarty/Smarty.class.php');
require_once('config.php');

$smarty = new Smarty();

$smarty->template_dir         = 'templates/';
$smarty->compile_dir          = 'compiled/';
$smarty->cache_dir            = 'cache/';
$smarty->cache_modified_check = true;

$smarty->assign('ROOT', $webroot);

if (!is_file($dbpath)) {
  $smarty->assign('error', 'No database');
  $smarty->assign('message', 'The API database was not found.');
  $smarty->display('error.tpl');
  exit;
}

try {
  $db = new SQLiteDatabase($dbpath);
}
catch (Exception $e) {
  $smarty->assign('error', 'Corrupt database');
  $smarty->assign('message', 'The database could not be opened: ' . $e->getMessage());
  $smarty->display('error.tpl');
  exit;
}

$smarty->caching = true;

function sqlesc($str) {
  return sqlite_escape_string($str);
}

function get_newest_platform($names) {
  return $names[count($names) - 1];
}

function get_platform($name) {
  global $db;

  return $db->singleQuery('SELECT id FROM platforms WHERE platform="' . sqlesc($name) . '"', true);
}

function get_platform_names($interface = null) {
  global $db;

  if ($interface == null) {
    return $db->singleQuery('SELECT platform FROM platforms');
  }
  else {
    return $db->singleQuery('SELECT platforms.platform FROM '.
                            'plat_ifaces JOIN platforms ON plat_ifaces.platform=id '.
                            'WHERE interface=' . $interface . ' ORDER BY id');
  }
}

function get_interface($name) {
  global $db;

  return $db->singleQuery('SELECT id FROM interfaces WHERE interface="' . sqlesc($name) .'"', true);
}

function get_interface_names($platform = null) {
  global $db;

  if ($platform === null) {
    return $db->singleQuery('SELECT interface FROM interfaces ORDER BY interface');
  }
  else {
    return $db->singleQuery('SELECT interfaces.interface FROM '.
                            'plat_ifaces JOIN interfaces ON plat_ifaces.interface=id '.
                            'WHERE platform=' . $platform . ' ORDER BY interfaces.interface');
  }
}

function get_platforms_for_interface($platform) {
}

?>