<?php
// argument default
// if user has only one facility, return its ID, otherwise return 'multiple'
global $user;
$a = arg();
ctools_include('jump-menu');
// if none, check for users farms 
if (count($a) <= 1) {
  global $user;
  $farms = dh_get_user_mgr_features($user->uid, 'facility', 'vineyard');
  if (count($farms) == 1) {
    $default = array_shift($farms);
  } else {
    // do NOT show this, 
    return;
  }
}
$urls = array(
  'list' => url(implode('/', array($a[0], $a[1]))),
  //'materials' => url(implode('/', array($a[0], $a[1], 'select'))),
  'search' => url(implode('/', array($a[0], $a[1], 'search'))),
  'inventory' => url(implode('/', array($a[0], $a[1], 'inventory'))),
  'purchasing' => url(implode('/', array($a[0], $a[1], 'purchasing'))),
);
$targets = array();
$options = array(
  'default_value' => url($_GET['q']),
);
$targets[$urls['list']] = 'List Materials';
//$targets[$urls['materials']] = 'Manage Materials';
$targets[$urls['search']] = 'Search for New Materials';
$targets[$urls['inventory']] = 'Update Inventory';
$targets[$urls['purchasing']] = 'Estimate Purchasing';
// change -9999 to 1 if admin should see this also
if ($user->uid == -9999) {
  $urls['materials'] = url(implode('/', array($a[0], $a[1], 'select')));
  $targets[$urls['materials']] = 'Manage Materials';
}
$output = drupal_get_form('ctools_jump_menu', $targets, $options);

// now, we test to see if this farm HAS chems already, because if it doesn't we want to 
// NOT show this menu.

$farm = entity_load_single('dh_feature', $a[1]);
$farm_chems = $farm->field_link_agchem_material['und'];
$farm_chems = array_column($farm_chems, 'target_id');

if (count($farm_chems) > 0) {
  echo "<b>Go To:</b>" . drupal_render($output);
} else {
  if ($a[2] <> 'search') {
    // no chems, and not on search page, must go to search
    $url = implode('/', array($a[0], $a[1], 'search'));
    $options = array();
    $options['query'] = array(
      'setup' => 1,
    );
    drupal_goto($url, $options);
  }
}

?>