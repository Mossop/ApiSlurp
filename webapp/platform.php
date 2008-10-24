<?php
require_once('setup.php');

$smarty->assign('platform', $_GET['platform']);
$id = get_platform($_GET['platform']);
$smarty->assign('interfaces', get_interface_names($id));
$smarty->assign('platforms', get_platform_names());

$smarty->display('platform.tpl', $id);
?>