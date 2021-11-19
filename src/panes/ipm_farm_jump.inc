<?php
// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/panes/ipm_farm_jump');
// argument default
// if user has only one facility, return its ID, otherwise return 'multiple'
$a = arg();
ctools_include('jump-menu');
// if none, check for users farms 
global $user;
$farms = dh_get_user_mgr_features($user->uid, 'facility', 'vineyard');
//dpm($a,'args');
//dpm($farms,'farms');
$red = FALSE;
// Check for re-direct, if no filters are set, or if a location filter is an illegal value 
if (!isset($a[1])) {
  $a[1] = isset($_SESSION['om_agman']['facility']) ? $_SESSION['om_agman']['facility'] : 'all';
  $red = TRUE;
}
// check to see if user has at least 1 farm, if so, default to the first one.
if ( (count($farms) >= 1) and ($a[1] == 'all')) {
  $red = TRUE;
  $a[1] = current($farms);
}

// check permissions
if (!in_array($a[1], $farms)) {
  $sfac = $_SESSION['om_agman']['facility'];
  $a[1] = in_array($sfac, $farms) ? $_SESSION['om_agman']['facility'] : 'all';
  $red = TRUE;
}

// Finally, redirect to the location management page if we have no blocks
if ( $a[1] <> 'all' ) {
  $blockids = dh_get_facility_mps($a[1], 'landunit');
  if (empty($blockids) and ($a[0] <> 'ipm-facility-info')) {
    drupal_set_message("You must create at least 1 block on your farm to access these features.");
    $a[0] = 'ipm-facility-info';
    $red = TRUE;
  }
}
if (count($a) < 3) {
  $a[2] = 'all';
}


if ($red) {
  //dpm($a,'final args');
  drupal_goto(implode('/',$a));
}

// save the current values for the session
if ($a[1] <> 'all') {
  $vineyard_id = $a[1];
}
if ($a[2] <> 'all') {
  $block_id = $a[2];
}
$_SESSION['om_agman']['facility'] = $vineyard_id;
$_SESSION['om_agman']['landunit'] = $block_id;
//dpm($farms,'farms');
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

$jump_menu = drupal_get_form('ctools_jump_menu', $targets, $options);
//dpm($jump_menu['jump'],'form');
$jump_menu['jump']['#title'] .= "Show Farm:";
//dpm($jump_menu['jump'],'form 2');
echo drupal_render($jump_menu);

?>