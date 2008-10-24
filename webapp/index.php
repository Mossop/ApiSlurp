<?php
require_once('setup.php');

$smarty->assign('platforms', get_platform_names());
$smarty->assign('interfaces', get_interface_names());

$smarty->display('index.tpl');
?>