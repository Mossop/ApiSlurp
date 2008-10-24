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
$plid = $db->singleQuery('SELECT plat_ifaces.id FROM plat_ifaces JOIN platforms ON plat_ifaces.platform=platforms.id '.
                         'WHERE platforms.platform="' . sqlesc($platform) . '" AND interface=' . $id, true);

$cache = $plid;

if (!$smarty->is_cached('interface.tpl', $cache)) {
  $smarty->assign('platform', $platform);
  
  $smarty->assign('platforms', $platforms);
  $smarty->assign('constants', $db->arrayQuery('SELECT id, comment, type, name, text AS value FROM members WHERE '.
                                               'pint=' . $plid . ' AND kind="const" ORDER BY value'));
  $smarty->assign('attributes', $db->arrayQuery('SELECT id, comment, text AS readonly, type, name FROM members WHERE '.
                                                'pint=' . $plid . ' AND kind="attribute" ORDER BY name'));
  $methods = $db->arrayQuery('SELECT id, comment, type, name FROM members WHERE '.
                             'pint=' . $plid . ' AND kind="method" ORDER BY name');

  foreach ($methods as &$method) {
    $method['params'] = $db->arrayQuery('SELECT type, name FROM parameters WHERE member=' .
                                        $method['id'] . ' ORDER BY pos');
  }
  $smarty->assign('methods', $methods);
}
$smarty->display('interface.tpl', $cache);
?>