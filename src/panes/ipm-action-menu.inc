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
    url("ipm-facility-sprayplan/$vineyard/all") => 'Go to Season Spray Planner',
  ),
  'Disease/Insect/Weather Recording' => array(),
);

function om_agman_varJump($varkeys) {
  $targets = array();
  $gc = "varkey in ('" . implode("', '", $varkeys) . "')";
  $vars = dh_vardef_varselect_options(array($gc));
  foreach ($vars as $varid => $vname) {
    $vinfo = dh_vardef_info($varid);
    $uri = url("ipm-events/$vineyard/tsform/?edit&featureid=$vineyard&edit&varid=$varid&destination=ipm-home/$vineyard");
    // dh_vardef_varselect_options gives funky formatting, so we override here
    $targets[$uri] = $vinfo->varname;
  }
  return $targets;
}


//*******************************************
// Growth Stages 
//*********************************************
$vks = array('vitis_bud_swell', 'vitis_bud_break', 'vitis_veraison' , 'vitis_med_shoot_length', 'vitis_bunch_closure');
$targets['Growth Stage Recording'] = om_agman_varJump($vks);

//*******************************************
// Disease/Insect/Weather Recording
//*********************************************
$vks = array('ipm_event', 'ipm_frost', 'ipm_outbreak', 'om_agman_svform' , 'ipm_hail', 'ipm_spray_injury');
$targets['Disease/Insect/Weather Recording'] = om_agman_varJump($vks);


//*******************************************
// Canopy Management/Fruit Sampling/Other
//*********************************************
$vks = array('agman_pruning', 'agman_sample_petiole', 'agman_fruit_chem', 'agman_harvest' );
$targets['Canopy Management/Fruit Sampling/Harvest'] = om_agman_varJump($vks);


$options = array(
  'default_value' => url($_GET['q']),
);
//dpm($targets,'targs');
//dpm($options,'options');

$output = drupal_get_form('ctools_jump_menu', $targets, $options);  
echo "<b>Function List </b>(adds sprays events, reports, etc.):<br>" . drupal_render($output);

?>