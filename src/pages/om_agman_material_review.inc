<?php

module_load_include('module', 'om_agman');
module_load_include('inc', 'om_agman', 'src/lib/om_agman_search_fn');

function om_agman_material_review_form($form, &$form_state, $farm_id, $new_chems) {
//dpm($farm_id, 'farm id');
  // type 'button' does not submit.
  // This is useful in that it also does not call a rebuild,
  // but it does send values to the form that can be used to alter the query results.
  // preserving the state for the next step.
  $form['actions']['cancel'] = array(
    '#type' => 'submit',
    '#value' => t('Cancel'),
    '#weight' => 3,
    '#limit_validation_errors' => array(),
    '#submit' => array('om_agman_material_review_cancel')
  );
  $form['actions']['save'] = array(
    '#type' => 'submit',
    '#value' => t('Add Selected to Inventory'),
    '#weight' => 2,
    '#submit' => array('om_agman_material_review_save')
  );
  
  $criteria = array(
    'farm_id' => array('value' => $farm_id),
    'chems' => array('value' => $new_chems),
  );
  
  if (!empty($form_state['input']['frac'])) {
    $criteria['frac'] = array('value' => $form_state['input']['frac']);
  }
  if (!empty($form_state['input']['ai'])) {
    $criteria['ai'] = array('value' => $form_state['input']['ai']);
  }
  if (!empty($form_state['input']['name'])) {
    $criteria['name'] = array('value' => $form_state['input']['name']);
  }
  // 
  $form['farm_id'] = array(
    '#type' => 'hidden',
    '#default_value' => $farm_id,
  );
  $form['chems'] = om_agman_material_search_opts('confirm', $criteria, $new_chems); // pass in new_chems to this widget to highlight them in the header
  $form['actions']['#weight'] = 1;
  $form['chems']['#weight'] = 10;
  $form['chems']['#default_value'] = array_combine($new_chems, $new_chems);
  return $form;
}


function om_agman_material_review_save($form, &$form_state) {
  // make this a simple work flow where we confirm that the user wants to do this with an entirely different page.  
  // this will simplify handling. Also, it will allow us to warn the user if they are *removing* a chem from their
  // inventory that has planned applications.
  //dpm($form_state,'form state on save');
  // grab chems, and send to a next form for confirmation that we wish to add them 
  $parms = drupal_get_query_parameters();
  $sel_chems = array_values(array_filter($form_state['values']['chems']));
  $farm = entity_load_single('dh_feature', $form_state['values']['farm_id']);
  $farm_chems = $farm->field_link_agchem_material['und'];
  $farm_chems = array_column($farm_chems, 'target_id');
  $new_chems = array_diff($sel_chems, $farm_chems);
  //dpm($new_chems, 'new_chems');
  foreach ($new_chems as $chem_id) {
    $farm->field_link_agchem_material['und'][] = array('target_id' => $chem_id);
  }
  $farm->save();
  // got to the review and save page 
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
  } else {
    $url = implode('/', array('ipm-facility-materials'));
  }
  drupal_goto($url);
}


function om_agman_material_review_cancel($form, &$form_state) {
  $parms = drupal_get_query_parameters();
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
  } else {
    $url = implode('/', array('ipm-home'));
  }
  drupal_goto($url);
}

$new_chems = array();
$parms = drupal_get_query_parameters();
$chems = $parms['chems'];
//dsm("from the top");
$a = arg();
$farm_id = $a[1];
if (empty($chems)) {
  drupal_set_message("No new material selected.");
  $url = implode('/', array("ipm-facility-materials", $farm_id, 'search'));
  drupal_goto($url);
}
$form_state = array();
$form_state['build_info']['args'] = array($farm_id, $chems);
form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
//dpm($form_state,'post form_load_include');
$elements = drupal_build_form('om_agman_material_review_form', $form_state);

//drupal_get_form('om_agman_material_search_form')
//dpm($elements, 'form');
echo drupal_render($elements);

?>