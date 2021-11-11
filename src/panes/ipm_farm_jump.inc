<?php
// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/panes/ipm_farm_jump');
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
    dsm("No Block Indicated.", 'error');
    die;
  }
}
if (count($a) < 3) {
  $a[2] = 'all';
}
dpm($farms,'farms');
$targets = array();
$options = array(
  'default_value' => url($_GET['q']),
);
$block_urls = array();
foreach ($farms as $farm) {
  $b = $a;
  $b[1] = $farm;
  if ($a[1] <> $farm) {
    $b[2] = 'all'; // if this is not the current farm, we can't select down to the block level 
  }
  $farm_path = url(implode('/', $b));
  $farm_obj = entity_load_single('dh_feature', $farm);
  $targets[$farm_path] = $farm_obj->name;
}
//dpm($targets,'targs');

$output = drupal_get_form('ctools_jump_menu', $targets, $options);
echo "<b>Show Farm:</b><br>" . drupal_render($output);

?>