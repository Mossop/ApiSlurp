<?php
require_once('setup.php');

$smarty->prepare('index.tpl');
$smarty->assign('platforms', Platform::getAllPlatforms());
$smarty->assign('modules', XPCOMInterface::getAllInterfacesByModule());
$smarty->display();
?>