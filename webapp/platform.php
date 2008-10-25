<?php
require_once('setup.php');

if (!isset($_GET['platform'])) {
  error('Invalid Request', 'This URL is malformed');
}

$platform = Platform::getByName($_GET['platform']);

if ($platform == null) {
  error('Unknown Platform', 'The platform ' . $_GET['platform'] . ' does not exist in the database.');
}

$smarty->prepare('platform.tpl', $platform->id);
$smarty->assign('platform', $platform);
$smarty->assign('interfaces', $platform->getInterfaces());
$smarty->assign('platforms', Platform::getAllPlatforms());
$smarty->display();
?>