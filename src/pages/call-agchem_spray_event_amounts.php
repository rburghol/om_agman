<?php

$lu = NULL;
$op = 'add';
$date = date('Y-m-d');
module_load_include('inc', 'om_agman', 'src/pages/agchem_spray_event_amounts');
module_load_include('module', 'dh');
module_load_include('module', 'dh_adminreg');
$a = arg();
if (isset($a[1])) {
  $blockid = $a[1];
  
  if (isset($a[3])) {
    $planid = $a[3];
  } else {
    $planid = 'add';
  }
  if ($planid <> 'add') {
    // load existing prop for editing
    $plan = entity_load_single('dh_adminreg_feature', $planid);
    $op = 'edit';
  } else {
    // we need to create this
    $plan = entity_create('dh_adminreg_feature', array(
      'bundle' => 'agchem_app', 
      'startdate' => strtotime($date), 
      'dh_link_feature_submittal' => array('und' => array( 0 => array('target_id' => $blockid) )))
    );
    $op = 'add';
  }
  if (is_object($plan)) {
    $form_state = array();
    $form_state['wrapper_callback'] = 'entity_ui_main_form_defaults';
    $form_state['entity_type'] = 'dh_adminreg_feature';
    $form_state['bundle'] = 'agchem_app';
    form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
    // does this do anything in this context?
    $form_state['build_info']['args'] = array($plan, $op, 'dh_adminreg_feature');

    // **********************
    // Load the form
    // **********************
    $elements = drupal_build_form('dh_sprayquan_form', $form_state);
    //$elements = entity_ui_get_bundle_add_form('dh_adminreg_feature', 'mnw_file');
    // entity_ui_get_form($entity_type, $entity, $op = 'edit', $form_state = array())

    // just grab the regular form for proof of concept
    //$elements = entity_ui_get_form('dh_adminreg_feature', $lu, $op, $form_state);
    //dpm($elements,'form');
    $form = drupal_render($elements);
    echo $form;
  } else {
    echo "Problem creating/loading object $planid";
  }
} else {
  echo "There was a problem, no block loaded.";
}


?>