<?php
// use in any page with 1-line:
//   module_load_include('inc', 'om_agman', 'src/panes/ipm_settings_jump');
// argument default
// if user has only one facility, return its ID, otherwise return 'multiple'
$a = arg();
ctools_include('jump-menu');
// if none, check for users farms 
$farm_id = $a[1]; // 1 if in panel, 2 if testing in node
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
  'overview' => url(implode('/', array('ipm-facility-equip', $farm_id, 'all')), $rq),
  'equipment' => url(implode('/', array('ipm-facility-equip', $farm_id, 'all', 'edit')), $rq),
  'materials' => url(implode('/', array('ipm-facility-materials', $farm_id)), $rq),
  'locations' => url(implode('/', array('ipm-facility-info', $farm_id, 'all', 'editblocks')), $rq),
  'vineyard' => url(implode('/', array('ipm-edit-vineyard', $farm_id)), $rq),
  'allfarms' => url(implode('/', array('ipm-user-farms', $farm_id)), $rq),
);
$targets = array();
$targets[$urls['overview']] = t('Farm Settings Overview');
$targets[$urls['equipment']] = t('Spray Equipment');
$targets[$urls['materials']] = t('Spray Materials');
$targets[$urls['locations']] = t('Vineyard Blocks');
$targets[$urls['vineyard']] = t('Vineyard Info');
$targets[$urls['allfarms']] = t('Manage All Farms');
$output = drupal_get_form('ctools_jump_menu', $targets, $options);
dpm($options,'options');
dpm($targets,'targs');
// show the menu
echo "<b>Change Settings for:</b>" . drupal_render($output);
  
?>