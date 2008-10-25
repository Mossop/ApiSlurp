<?php
require_once('setup.php');

$smarty->assign('platforms', Platform::getAllPlatforms());
$smarty->assign('interfaces', get_interface_names());

$smarty->display('index.tpl');
?>