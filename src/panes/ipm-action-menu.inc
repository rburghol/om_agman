<?php
// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/panes/ipm-action-menu');
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
    $a[] = $default;
  } else {
    // do NOT show this, 
    return;
  }
}
$finaldest = 'ipm-home';
$vineyard = $a[1];


$targets = array(
  'Spray Events' => array(
    url("ipm-live-events/all/materials/add/$vineyard&finaldest=$finaldest") => 'Add a Spray',
  ),
);

$options = array(
  'default_value' => url($_GET['q']),
);
dpm($targets,'targs');
//dpm($options,'options');

$output = drupal_get_form('ctools_jump_menu', $targets, $options);  
echo "<b>Select Function (adds sprays events, reports, etc.):</b><br>" . drupal_render($output);

?>