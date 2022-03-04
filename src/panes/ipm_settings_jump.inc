<?php
// use in any page with 1-line:
//   module_load_include('inc', 'om_agman', 'src/panes/ipm_settings_jump');
// argument default
// if user has only one facility, return its ID, otherwise return 'multiple'
$a = arg();
ctools_include('jump-menu');
// if none, check for users farms 
$farm_id = $a[2]; // 1 if in panel, 2 if testing in node
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
$options = array(
  'default_value' => url($_GET['q']),
);
$rq = array('query' => array('destination' => "ipm-facility-equip/$farm_id"));
$urls = array(
  'equipment' => url(implode('/', array('ipm-facility-equip', $farm_id, 'all', 'edit')), $rq),
  'materials' => url(implode('/', array('ipm-facility-materials', $farm_id, 'search')), $rq),
  'locations' => url(implode('/', array('ipm-facility-info', $farm_id, 'all', 'editblocks')), $rq),
  'vineyard' => url(implode('/', array('ipm-edit-vineyard', $farm_id)), $rq),
);
$targets = array();
$targets[$urls['equipment']] = 'Spray Equipment';
$targets[$urls['materials']] = 'Spray Materials';
$targets[$urls['locations']] = 'Vineyard Blocks';
$targets[$urls['vineyard']] = 'Vineyard Info';
$output = drupal_get_form('ctools_jump_menu', $targets, $options);

// show the menu
echo "<b>Change Settings for:</b>" . drupal_render($output);
  
?>