<?php
$CONFIG = array(
  'compiledir' => 'compiled',
  'cachedir' => 'cache',
  'smartydir' => 'smarty',
  'dbpath' => 'api.sqlite',
  'webroot' => '',
  'caching' => false
);
if (!is_file('config.php')) {
  print 'Web-app not properly configured. Copy config.example.php to config.php and edit as necessary.';
  exit;
}

if (!is_dir($CONFIG['compiledir']) ||
    !is_dir($CONFIG['cachedir']) ||
    !is_file($CONFIG['smartydir'] . '/Smarty.class.php')) {
  print 'Web-app not properly set up. Create compile and cache directories and install smarty.';
  exit;
}

require_once($CONFIG['smartydir'] . '/Smarty.class.php');
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

  header('Location: http://' . $_SERVER['HTTP_HOST'] . $webroot . $path);
  exit;
}

$smarty = new APISmarty();

if (!is_file($CONFIG['dbpath'])) {
  error('No database', 'The API database was not found. Check the app configuration.');
}

try {
  $db = new PDO('sqlite:' . realpath($CONFIG['dbpath']), '', '',
                array(PDO::ATTR_PERSISTENT => true));
}
catch (Exception $e) {
  error('Corrupt database', 'The database could not be opened: ' . $e->getMessage());
}

if ($CONFIG['caching'] == true) {
  $smarty->caching = true;
}

?>