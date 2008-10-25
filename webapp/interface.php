<?php
require_once('setup.php');

if (!isset($_GET['interface'])) {
  error('Invalid Request', 'This URL is malformed');
}

if (isset($_GET['platform'])) {
  $interface = InterfaceVersion::getByNameAndPlatform($_GET['interface'], $_GET['platform']);
  if ($interface === null) {
    error('Unknown Interface', 'The interface ' . $_GET['interface'] . ' does not exist in platform ' . $_GET['platform'] . ' the database.');
  }
}
else {
  $interfaces = XPCOMInterface::getByName($_GET['interface']);
  if ($interfaces === null) {
    error('Unknown Interface', 'The interface ' . $_GET['interface'] . ' does not exist in the database.');
  }
  $interface = $interfaces->getNewestVersion();
}

$smarty->assign('interface', $interface);
$smarty->assign('platform', $interface->platform);
$smarty->prepare('interface.tpl', $interface->id);
$smarty->display();
?>