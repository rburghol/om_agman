<?php
// argument default
// if user has only one facility, return its ID, otherwise return 'multiple'
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
$output = drupal_get_form('ctools_jump_menu', $targets, $options);
echo "<b>Go To:</b>" . drupal_render($output);
?>