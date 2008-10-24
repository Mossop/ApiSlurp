<?php
require_once('setup.php');

$smarty->assign('interface', $_GET['interface']);
$id = get_interface($_GET['interface']);

$platforms = get_platform_names($id);

if (isset($_GET['platform'])) {
  $platform = $_GET['platform'];
}
else {
  $platform = get_newest_platform($platforms);
}
$pl = get_platform($platform);

$cache = $id.'.'.$pl;

if (!$smarty->is_cached('interface.tpl', $cache)) {
  $smarty->assign('platform', $platform);
  
  $smarty->assign('platforms', $platforms);
  $smarty->assign('constants', $db->arrayQuery('SELECT comment, type, name, text AS value FROM members WHERE '.
                                               'platform=' . $pl . ' AND interface=' . $id . ' AND kind="const" ORDER BY value'));
  $smarty->assign('attributes', $db->arrayQuery('SELECT comment, text AS readonly, type, name FROM members WHERE '.
                                                'platform=' . $pl . ' AND interface=' . $id . ' AND kind="attribute" ORDER BY name'));
  $smarty->assign('methods', $db->arrayQuery('SELECT comment, type, name FROM members WHERE '.
                                             'platform=' . $pl . ' AND interface=' . $id . ' AND kind="method" ORDER BY name'));
}
$smarty->display('interface.tpl', $cache);
?>