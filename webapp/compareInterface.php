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
$platform1 = get_platform($platform1);
$platform2 = get_platform($platform2);

$id = get_interface($interface);
$pli1 = get_plat_iface($id, $platform1['name']);
$pli1['name'] = $interface;
$pli2 = get_plat_iface($id, $platform2['name']);
$pli2['name'] = $interface;
$cache = $pli1['id'].'.'.$pli2['id'];

function member_name_compare($a, $b) {
  if ($a['name'] == $b['name']) {
    return 0;
  }
  return ($a['name'] < $b['name']) ? -1 : 1;
}

function member_value_compare($a, $b) {
  if ($a['new']['value'] == $b['new']['value']) {
    return 0;
  }
  return ($a['new']['value'] < $b['new']['value']) ? -1 : 1;
}

function map_array($mapping, $array) {
  $result = array();

  foreach ($mapping as $key => $newkey) {
    $result[$key] = $array[$newkey];
  }
  return $result;
}

$constmap = array('id' => 'id', 'comment' => 'comment', 'type' => 'type', 'name' => 'name', 'value' => 'text');
$attrmap = array('id' => 'id', 'comment' => 'comment', 'type' => 'type', 'name' => 'name', 'readonly' => 'text');
$methmap = array('id' => 'id', 'comment' => 'comment', 'type' => 'type', 'name' => 'name');

$oldmap = array('id' => 'oldid', 'name' => 'name', 'type' => 'oldtype', 'comment' => 'oldcomment', 'text' => 'oldtext');
$newmap = array('id' => 'newid', 'name' => 'name', 'type' => 'newtype', 'comment' => 'newcomment', 'text' => 'newtext');

function get_items($pli1, $pli2, $type, $mapping, $sortfunc) {
  global $oldmap, $newmap;

  $new = sql_array('SELECT m1.id AS id, m1.name AS name, m1.type AS type, m1.comment AS comment, m1.text AS text FROM '.
                   '(SELECT * FROM members WHERE pint=' . $pli2 . ' AND kind="' . sqlesc($type) . '") AS m1 '.
         'LEFT JOIN (SELECT * FROM members WHERE pint=' . $pli1 . ' AND kind="' . sqlesc($type) . '") AS m2 '.
                   'ON m1.name=m2.name WHERE m2.name IS NULL');
  $old = sql_array('SELECT m1.id AS id, m1.name AS name, m1.type AS type, m1.comment AS comment, m1.text AS text FROM '.
                   '(SELECT * FROM members WHERE pint=' . $pli1 . ' AND kind="' . sqlesc($type) . '") AS m1 '.
         'LEFT JOIN (SELECT * FROM members WHERE pint=' . $pli2 . ' AND kind="' . sqlesc($type) . '") AS m2 '.
                   'ON m1.name=m2.name WHERE m2.name IS NULL');
  $mod = sql_array('SELECT m1.id AS oldid, m1.name AS name, m1.type AS oldtype, m1.comment AS oldcomment, m1.text AS oldtext, '.
                   'm2.id AS newid, m2.type AS newtype, m2.comment AS newcomment, m2.text AS newtext FROM '.
                   '(SELECT * FROM members WHERE pint=' . $pli1 . ' AND kind="' . sqlesc($type) . '") AS m1 '.
              'JOIN (SELECT * FROM members WHERE pint=' . $pli2 . ' AND kind="' . sqlesc($type) . '") AS m2 '.
                   'ON m1.name=m2.name WHERE m1.hash<>m2.hash');
  $sam = sql_array('SELECT m1.id AS oldid, m1.name AS name, m1.type AS oldtype, m1.comment AS oldcomment, m1.text AS oldtext, '.
                   'm2.id AS newid, m2.type AS newtype, m2.comment AS newcomment, m2.text AS newtext FROM '.
                   '(SELECT * FROM members WHERE pint=' . $pli1 . ' AND kind="' . sqlesc($type) . '") AS m1 '.
              'JOIN (SELECT * FROM members WHERE pint=' . $pli2 . ' AND kind="' . sqlesc($type) . '") AS m2 '.
                   'ON m1.name=m2.name WHERE m1.hash=m2.hash');

  $items = array();
  foreach ($new as $item) {
    $list = map_array($mapping, $item);
    if ($type == 'method') {
      $list['params'] = sql_array('SELECT type, name FROM parameters WHERE member=' . $list['id'] . ' ORDER BY pos');
    }
    array_push($items, array('name' => $item['name'], 'state' => 'added', 'old' => $list, 'new' => $list));
  }
  foreach ($old as $item) {
    $list = map_array($mapping, $item);
    if ($type == 'method') {
      $list['params'] = sql_array('SELECT type, name FROM parameters WHERE member=' . $list['id'] . ' ORDER BY pos');
    }
    array_push($items, array('name' => $item['name'], 'state' => 'removed', 'old' => $list, 'new' => $list));
  }
  foreach ($sam as $item) {
    $oldlist = map_array($mapping, map_array($oldmap, $item));
    $newlist = map_array($mapping, map_array($newmap, $item));
    if ($type == 'method') {
      $oldlist['params'] = sql_array('SELECT type, name FROM parameters WHERE member=' . $oldlist['id'] . ' ORDER BY pos');
      $newlist['params'] = sql_array('SELECT type, name FROM parameters WHERE member=' . $newlist['id'] . ' ORDER BY pos');
    }
    array_push($items, array('name' => $item['name'], 'state' => 'matching', 'old' => $oldlist, 'new' => $newlist));
  }
  foreach ($mod as $item) {
    $oldlist = map_array($mapping, map_array($oldmap, $item));
    $newlist = map_array($mapping, map_array($newmap, $item));
    if ($type == 'method') {
      $oldlist['params'] = sql_array('SELECT type, name FROM parameters WHERE member=' . $oldlist['id'] . ' ORDER BY pos');
      $newlist['params'] = sql_array('SELECT type, name FROM parameters WHERE member=' . $newlist['id'] . ' ORDER BY pos');
    }
    array_push($items, array('name' => $item['name'], 'state' => 'modified', 'old' => $oldlist, 'new' => $newlist));
  }
  usort($items, $sortfunc);
  return $items;
}

if (!$smarty->is_cached('compareInterface.tpl', $cache)) {
  $smarty->assign('interface', array('name' => $interface, 'old' => $pli1, 'new' => $pli2));
  $smarty->assign('platform1', $platform1);
  $smarty->assign('platform2', $platform2);
  $smarty->assign('platforms', get_platform_names($id));

  $smarty->assign('constants', get_items($pli1['id'], $pli2['id'], 'const', $constmap, 'member_value_compare'));
  $smarty->assign('attributes', get_items($pli1['id'], $pli2['id'], 'attribute', $attrmap, 'member_name_compare'));
  $smarty->assign('methods', get_items($pli1['id'], $pli2['id'], 'method', $methmap, 'member_name_compare'));
}

$smarty->display('compareInterface.tpl', $cache);
?>