<?php
require_once('setup.php');

$smarty->prepare('index.tpl');

$smarty->assign('platforms', Platform::getAllPlatforms());
$smarty->assign('interfaces', XPCOMInterface::getAllInterfaces());

$smarty->display();
?>