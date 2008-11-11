<?php
require_once('setup.php');

if (!isset($_GET['platform1']) || !isset($_GET['platform2'])) {
  error('Invalid Request', 'This URL is malformed');
}

$platform1 = Platform::getByVersion($_GET['platform1']);
if ($platform1 == null) {
  error('Unknown Platform', 'The platform ' . $_GET['platform1'] . ' does not exist in the database.');
}

$platform2 = Platform::getByVersion($_GET['platform2']);
if ($platform2 == null) {
  error('Unknown Platform', 'The platform ' . $_GET['platform2'] . ' does not exist in the database.');
}

$smarty->prepare('comparePlatform.tpl', $platform1->id.'.'.$platform2->id);
$smarty->assign('platforms', Platform::getAllPlatforms());
$smarty->assign("diff", new PlatformDiff($platform1, $platform2));
$smarty->display();
?>