<?php
require_once('setup.php');

$platform1 = $_GET['platform1'];
$platform2 = $_GET['platform2'];
$newest = get_newest_platform(array($platform1, $platform2));
if ($newest == $platform1) {
  $platform1 = $platform2;
  $platform2 = $newest;
}

$pl1 = get_platform($platform1);
$pl2 = get_platform($platform2);
$cache = $pl1.'.'.$pl2;

if (!$smarty->is_cached('comparePlatform.tpl', $cache)) {
  $smarty->assign("platform1", $platform1);
  $smarty->assign("platform2", $platform2);
  
  $smarty->assign('added_interfaces',
                  $db->singleQuery('SELECT interfaces.interface FROM '.
                                   '(SELECT * FROM platform_interfaces WHERE platform=' . $pl2 .') AS pi1 '.
                                   'LEFT JOIN (SELECT * FROM platform_interfaces WHERE platform=' . $pl1 . ') AS pi2 '.
                                   'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=id '.
                                   'WHERE pi2.platform IS NULL'));
  $smarty->assign('removed_interfaces',
                  $db->singleQuery('SELECT interfaces.interface FROM '.
                                   '(SELECT * FROM platform_interfaces WHERE platform=' . $pl1 .') AS pi1 '.
                                   'LEFT JOIN (SELECT * FROM platform_interfaces WHERE platform=' . $pl2 . ') AS pi2 '.
                                   'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=id '.
                                   'WHERE pi2.platform IS NULL'));
  $smarty->assign('matching_interfaces',
                  $db->singleQuery('SELECT interfaces.interface AS interface FROM '.
                                   '(SELECT * FROM platform_interfaces WHERE platform=' . $pl1 .') AS pi1 '.
                                   'JOIN (SELECT * FROM platform_interfaces WHERE platform=' . $pl2 . ') AS pi2 '.
                                   'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=id WHERE pi1.hash=pi2.hash'));
  $smarty->assign('modified_interfaces',
                  $db->singleQuery('SELECT interfaces.interface AS interface FROM '.
                                   '(SELECT * FROM platform_interfaces WHERE platform=' . $pl1 .') AS pi1 '.
                                   'JOIN (SELECT * FROM platform_interfaces WHERE platform=' . $pl2 . ') AS pi2 '.
                                   'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=id WHERE pi1.hash<>pi2.hash'));
}

$smarty->display('comparePlatform.tpl', $cache);
?>