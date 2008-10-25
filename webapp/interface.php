<?php
require_once('setup.php');

$id = get_interface($_GET['interface']);

$platforms = get_platform_names($id);

if (isset($_GET['platform'])) {
  $platform = get_platform($_GET['platform']);
}
else {
  $platform = get_platform(get_newest_platform($platforms));
}
$pli = get_plat_iface($id, $platform['name']);
$pli['name'] = $_GET['interface'];
$smarty->assign('interface', $pli);

$cache = $pli['id'];

$smarty->prepare('interface.tpl', $cache);

$smarty->assign('platform', $platform);

$smarty->assign('platforms', $platforms);
$smarty->assign('constants', sql_array('SELECT id, comment, type, name, text AS value FROM members WHERE '.
                                       'pint=' . $pli['id'] . ' AND kind="const" ORDER BY value'));
$smarty->assign('attributes', sql_array('SELECT id, comment, text AS readonly, type, name FROM members WHERE '.
                                        'pint=' . $pli['id'] . ' AND kind="attribute" ORDER BY name'));
$methods = sql_array('SELECT id, comment, type, name FROM members WHERE '.
                     'pint=' . $pli['id'] . ' AND kind="method" ORDER BY name');

foreach ($methods as &$method) {
  $method['params'] = sql_array('SELECT type, name FROM parameters WHERE member=' .
                                $method['id'] . ' ORDER BY pos');
}
$smarty->assign('methods', $methods);

$smarty->display();
?>