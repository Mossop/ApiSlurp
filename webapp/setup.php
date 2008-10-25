<?php
if (!is_dir('compiled') || !is_dir('cache') || !is_file('smarty/Smarty.class.php')) {
  print 'Web-app not properly set up. Create compile and cache directories and install smarty.';
  exit;
}

require_once('smarty/Smarty.class.php');
require_once('classes.php');
require_once('config.php');

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
  $db = new SQLiteDB($dbpath);
}
catch (Exception $e) {
  error('Corrupt database', 'The database could not be opened: ' . $e->getMessage());
}

$smarty->caching = false;

function sqlesc($str) {
  global $db;

  return $db->escape($str);
}

function sql_array($query) {
  global $db;

  $result = $db->arrayQuery($query);
  if ($result === FALSE) {
    //error('Database Error', 'Something this page did caused a database error. Please email <a href="mailto:dtownsend@mozilla.com">Dave</a> with the page url.');
    error('Invalid SQL', 'This page attempted to run some invalid SQL: ' . $query . '<br />' . sqlite_error_string(sqlite_last_error($dbres)));
  }

  return $result;
}

function sql_row($query) {
  global $db;

  $result = $db->rowQuery($query);
  if ($result === FALSE) {
    //error('Database Error', 'Something this page did caused a database error. Please email <a href="mailto:dtownsend@mozilla.com">Dave</a> with the page url.');
    error('Invalid SQL', 'This page attempted to run some invalid SQL: ' . $query . '<br />' . sqlite_error_string(sqlite_last_error($dbres)));
  }

  return $result;
}

function sql_single($query) {
  global $db;

  $result = $db->singleQuery($query);
  if ($result === FALSE) {
    //error('Database Error', 'Something this page did caused a database error. Please email <a href="mailto:dtownsend@mozilla.com">Dave</a> with the page url.');
    error('Invalid SQL', 'This page attempted to run some invalid SQL: ' . $query . '<br />' . sqlite_error_string(sqlite_last_error($dbres)));
  }

  return $result;
}

function sql_column($query) {
  global $db;

  $result = $db->columnQuery($query);
  if ($result === FALSE) {
    //error('Database Error', 'Something this page did caused a database error. Please email <a href="mailto:dtownsend@mozilla.com">Dave</a> with the page url.');
    error('Invalid SQL', 'This page attempted to run some invalid SQL: ' . $query . '<br />' . sqlite_error_string(sqlite_last_error($dbres)));
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
  return sql_row('SELECT id, platform AS name, url FROM platforms WHERE platform="' . sqlesc($name) . '" LIMIT 1');
}

function get_platform_names($interface = null) {
  if ($interface == null) {
    return sql_column('SELECT platform FROM platforms');
  }
  else {
    return sql_column('SELECT platforms.platform FROM '.
                      'plat_ifaces JOIN platforms ON plat_ifaces.platform=platforms.id '.
                      'WHERE interface=' . $interface . ' ORDER BY platforms.id');
  }
}

function get_interface($name) {
  return sql_single('SELECT id FROM interfaces WHERE interface="' . sqlesc($name) .'"');
}

function get_interface_names($platform = null) {
  if ($platform === null) {
    return sql_column('SELECT interface FROM interfaces ORDER BY interface');
  }
  else {
    return sql_column('SELECT interfaces.interface FROM '.
                      'plat_ifaces JOIN interfaces ON plat_ifaces.interface=interfaces.id '.
                      'WHERE platform=' . $platform . ' ORDER BY interfaces.interface');
  }
}

function get_plat_iface($iid, $platform) {
  return sql_row('SELECT plat_ifaces.id AS id, plat_ifaces.iid AS iid, plat_ifaces.comment AS comment, plat_ifaces.path AS path, plat_ifaces.hash AS hash FROM plat_ifaces JOIN platforms ON plat_ifaces.platform=platforms.id '.
                 'WHERE platforms.platform="' . sqlesc($platform) . '" AND interface=' . $iid . ' LIMIT 1');
}

?>