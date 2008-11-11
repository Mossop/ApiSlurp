<?php
require_once('setup.php');

if (!isset($_GET['string'])) {
  error('Invalid Request', 'This URL is malformed');
}
$query = $_GET['string'];

$interfaces = XPCOMInterface::searchForName($query);
if (count($interfaces) == 1) {
  redirect('/interface/' . $interfaces[0]->name);
}

$smarty->prepare('searchInterface.tpl', $query);
$smarty->assign('platforms', Platform::getAllPlatforms());
$smarty->assign('interfaces', $interfaces);
$smarty->assign('query', $query);
$smarty->display();
?>