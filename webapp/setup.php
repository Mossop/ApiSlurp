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
  $smarty->prepare('error.tpl');
  $smarty->display();
  exit;
}

function redirect($path) {
  global $webroot;

  header('Location: http://' . $_SERVER['HTTP_HOST'] . '/' . $webroot . $path);
  exit;
}

$smarty = new APISmarty();
$smarty->assign('ROOT', $webroot);

if (!is_file($dbpath)) {
  error('No database', 'The API database was not found.');
}

try {
  $db = new PDODB('sqlite:' . realpath($dbpath));
}
catch (Exception $e) {
  error('Corrupt database', 'The database could not be opened: ' . $e->getMessage());
}

$smarty->caching = false;

?>