<?php
require_once('setup.php');

$interface = $_GET['interface'];
$platform1 = $_GET['platform1'];
$platform2 = $_GET['platform2'];

$newest = get_newest_platform(array($platform1, $platform2));
if ($newest == $platform1) {
  $platform1 = $platform2;
  $platform2 = $newest;
}

$iid = get_interface($interface);
$pl1 = get_platform($platform1);
$pl2 = get_platform($platform2);
$cache = $iid.'.'.$pl1.'.'.$pl2;

function member_compare($a, $b) {
  if ($a['name'] == $b['name']) {
    return 0;
  }
  return ($a['name'] < $b['name']) ? -1 : 1;
}

function get_items($iid, $pl1, $pl2, $type) {
  global $db;

  $new = $db->arrayQuery('SELECT m1.name AS name, m1.text AS text FROM '.
                         '(SELECT * FROM members WHERE platform=' . $pl2 . ' AND interface=' . $iid . ' AND kind="' . sqlesc($type) . '") AS m1 '.
               'LEFT JOIN (SELECT * FROM members WHERE platform=' . $pl1 . ' AND interface=' . $iid . ' AND kind="' . sqlesc($type) . '") AS m2 '.
                         'ON m1.name=m2.name WHERE m2.name IS NULL');
  $old = $db->arrayQuery('SELECT m1.name AS name, m1.text AS text FROM '.
                         '(SELECT * FROM members WHERE platform=' . $pl1 . ' AND interface=' . $iid . ' AND kind="' . sqlesc($type) . '") AS m1 '.
               'LEFT JOIN (SELECT * FROM members WHERE platform=' . $pl2 . ' AND interface=' . $iid . ' AND kind="' . sqlesc($type) . '") AS m2 '.
                         'ON m1.name=m2.name WHERE m2.name IS NULL');
  $mod = $db->arrayQuery('SELECT m1.name AS name, m1.text AS old, m2.text AS new FROM '.
                         '(SELECT * FROM members WHERE platform=' . $pl1 . ' AND interface=' . $iid . ' AND kind="' . sqlesc($type) . '") AS m1 '.
                    'JOIN (SELECT * FROM members WHERE platform=' . $pl2 . ' AND interface=' . $iid . ' AND kind="' . sqlesc($type) . '") AS m2 '.
                         'ON m1.name=m2.name WHERE m1.hash<>m2.hash');
  $sam = $db->arrayQuery('SELECT m1.name AS name, m1.text AS text FROM '.
                         '(SELECT * FROM members WHERE platform=' . $pl1 . ' AND interface=' . $iid . ' AND kind="' . sqlesc($type) . '") AS m1 '.
                    'JOIN (SELECT * FROM members WHERE platform=' . $pl2 . ' AND interface=' . $iid . ' AND kind="' . sqlesc($type) . '") AS m2 '.
                         'ON m1.name=m2.name WHERE m1.hash=m2.hash');

  $items = array();
  foreach ($new as $item) {
    $new = array('name' => $item['name'], 'text' => $item['text']);
    array_push($items, array('name' => $item['name'], 'state' => 'added', 'old' => array(), 'new' => $new));
  }
  foreach ($old as $item) {
    $old = array('name' => $item['name'], 'text' => $item['text']);
    array_push($items, array('name' => $item['name'], 'state' => 'removed', 'old' => $old, 'new' => array()));
  }
  foreach ($sam as $item) {
    $old = array('name' => $item['name'], 'text' => $item['text']);
    array_push($items, array('name' => $item['name'], 'state' => 'matching', 'old' => $old, 'new' => $new));
  }
  foreach ($mod as $item) {
    $old = array('name' => $item['name'], 'text' => $item['old']);
    $new = array('name' => $item['name'], 'text' => $item['new']);
    array_push($items, array('name' => $item['name'], 'state' => 'modified', 'old' => $old, 'new' => $new));
  }
  usort($items, 'member_compare');
  return $items;
}

if (!$smarty->is_cached('compareInterface.tpl', $cache)) {
  $smarty->assign('interface', $interface);
  $smarty->assign('platform1', $platform1);
  $smarty->assign('platform2', $platform2);
  $smarty->assign('platforms', get_platform_names($iid));

  $smarty->assign('constants', get_items($iid, $pl1, $pl2, 'const'));
  $smarty->assign('attributes', get_items($iid, $pl1, $pl2, 'attribute'));
  $smarty->assign('methods', get_items($iid, $pl1, $pl2, 'method'));
}

$smarty->display('compareInterface.tpl', $cache);
?>