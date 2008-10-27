<?php
require_once('setup.php');

if (!isset($_GET['string'])) {
  error('Invalid Request', 'This URL is malformed');
}

$interfaces = XPCOMInterface::searchForName($_GET['string']);
if (count($interfaces) == 1) {
  redirect('/interface/' . $interfaces[0]->name);
}

$smarty->prepare('searchInterface.tpl', $interface->id);
$smarty->assign('interfaces', $interfaces);
$smarty->assign('query', $_GET['string']);
$smarty->display();
?>