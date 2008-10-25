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
$cache = $pl1['id'].'.'.$pl2['id'];

if (!$smarty->is_cached('comparePlatform.tpl', $cache)) {
  $smarty->assign("platform1", $pl1);
  $smarty->assign("platform2", $pl2);
  
  $smarty->assign('added_interfaces',
                  sql_column('SELECT interfaces.interface FROM '.
                             '(SELECT * FROM plat_ifaces WHERE platform=' . $pl2['id'] .') AS pi1 '.
                             'LEFT JOIN (SELECT * FROM plat_ifaces WHERE platform=' . $pl1['id'] . ') AS pi2 '.
                             'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=interfaces.id '.
                             'WHERE pi2.platform IS NULL ORDER BY interfaces.interface'));
  $smarty->assign('removed_interfaces',
                  sql_column('SELECT interfaces.interface FROM '.
                             '(SELECT * FROM plat_ifaces WHERE platform=' . $pl1['id'] .') AS pi1 '.
                             'LEFT JOIN (SELECT * FROM plat_ifaces WHERE platform=' . $pl2['id'] . ') AS pi2 '.
                             'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=interfaces.id '.
                             'WHERE pi2.platform IS NULL ORDER BY interfaces.interface'));
  $smarty->assign('matching_interfaces',
                  sql_column('SELECT interfaces.interface AS interface FROM '.
                             '(SELECT * FROM plat_ifaces WHERE platform=' . $pl1['id'] .') AS pi1 '.
                             'JOIN (SELECT * FROM plat_ifaces WHERE platform=' . $pl2['id'] . ') AS pi2 '.
                             'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=interfaces.id WHERE pi1.hash=pi2.hash ORDER BY interfaces.interface'));
  $smarty->assign('modified_interfaces',
                  sql_column('SELECT interfaces.interface AS interface FROM '.
                             '(SELECT * FROM plat_ifaces WHERE platform=' . $pl1['id'] .') AS pi1 '.
                             'JOIN (SELECT * FROM plat_ifaces WHERE platform=' . $pl2['id'] . ') AS pi2 '.
                             'ON pi1.interface=pi2.interface JOIN interfaces ON pi1.interface=interfaces.id WHERE pi1.hash<>pi2.hash ORDER BY interfaces.interface'));
}

$smarty->display('comparePlatform.tpl', $cache);
?>