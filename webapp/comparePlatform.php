<?php
require_once('setup.php');

if (!isset($_GET['platform1']) || !isset($_GET['platform2'])) {
  error('Invalid Request', 'This URL is malformed');
}

$platform1 = Platform::getByName($_GET['platform1']);
if ($platform1 == null) {
  error('Unknown Platform', 'The platform ' . $_GET['platform1'] . ' does not exist in the database.');
}

$platform2 = Platform::getByName($_GET['platform2']);
if ($platform2 == null) {
  error('Unknown Platform', 'The platform ' . $_GET['platform2'] . ' does not exist in the database.');
}

if ($versioncomparator->compareVersions($platform1->version, $platform2->version) > 0) {
  $temp = $platform2;
  $platform1 = $platform2;
  $platform2 = $temp;
}

$smarty->prepare('comparePlatform.tpl', $platform1->id.'.'.$platform2->id);
$smarty->assign("diff", new PlatformDiff($platform1, $platform2));
$smarty->display();
?>