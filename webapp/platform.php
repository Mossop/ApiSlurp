<?php
require_once('setup.php');

$platform = get_platform($_GET['platform']);
$smarty->assign('platform', $platform);
$smarty->assign('interfaces', get_interface_names($platform['id']));
$smarty->assign('platforms', get_platform_names());

$smarty->display('platform.tpl', $platform['id']);
?>