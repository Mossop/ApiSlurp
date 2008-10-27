<?php
require_once('setup.php');

if (!isset($_GET['interface']) || !isset($_GET['platform1']) || !isset($_GET['platform2'])) {
  error('Invalid Request', 'This URL is malformed');
}

$interface1 = InterfaceVersion::getByNameAndPlatform($_GET['interface'], $_GET['platform1']);
if ($interface1 === null) {
  error('Unknown Interface', 'The interface ' . $_GET['interface'] . ' does not exist in platform ' . $_GET['platform1'] . ' the database.');
}
$interface2 = InterfaceVersion::getByNameAndPlatform($_GET['interface'], $_GET['platform2']);
if ($interface2 === null) {
  error('Unknown Interface', 'The interface ' . $_GET['interface'] . ' does not exist in platform ' . $_GET['platform2'] . ' the database.');
}

if ($versioncomparator->compareVersions($interface1->platform->version, $interface2->platform->version) > 0) {
  redirect('/compare/interface/' . $interface1->name . '/' . $interface2->platform->version . '/' . $interface1->platform->version);
}

$smarty->prepare('compareInterface.tpl', $interface1->id . '.' . $interface2->id);
$smarty->assign('diff', new InterfaceDiff($interface1, $interface2));
$smarty->display();
?>