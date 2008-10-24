<?php
require_once('smarty/Smarty.class.php');
$smarty = new Smarty();

$smarty->template_dir         = 'templates/';
$smarty->compile_dir          = 'compiled/';
$smarty->cache_dir            = 'cache/';
$smarty->caching              = 1;
$smarty->cache_modified_check = 1;

$smarty->assign('ROOT', '/experiments/apidocs');

$db = new SQLiteDatabase('api.sqlite');

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
                            'platform_interfaces JOIN platforms ON platform_interfaces.platform=id '.
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
                            'platform_interfaces JOIN interfaces ON platform_interfaces.interface=id '.
                            'WHERE platform=' . $platform . ' ORDER BY interfaces.interface');
  }
}

function get_platforms_for_interface($platform) {
}

?>