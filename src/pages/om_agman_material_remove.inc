<?php

module_load_include('module', 'om_agman');
module_load_include('inc', 'om_agman', 'src/lib/om_agman_search_fn');

function om_agman_material_remove_form($form, &$form_state, $farm_id, $remove_chems) {
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
    '#submit' => array('om_agman_material_remove_cancel')
  );
  $form['actions']['save'] = array(
    '#type' => 'submit',
    '#value' => t('Remove Selected from Inventory'),
    '#weight' => 2,
    '#submit' => array('om_agman_material_remove_save')
  );
  
  $criteria = array(
    'farm_id' => array('value' => $farm_id),
    'chems' => array('value' => $remove_chems),
  );
  // 
  $form['farm_id'] = array(
    '#type' => 'hidden',
    '#default_value' => $farm_id,
  );
  //dpm($form,'form');
  $form['chems'] = om_agman_material_search_opts('confirm', $criteria, $remove_chems); // pass in remove_chems to this widget to highlight them in the header
  $form['actions']['#weight'] = 1;
  $form['chems']['#weight'] = 10;
  $form['chems']['#default_value'] = array_combine($remove_chems, $remove_chems);
  return $form;
}


function om_agman_material_remove_save($form, &$form_state) {
  // make this a simple work flow where we confirm that the user wants to do this with an entirely different page.  
  // this will simplify handling. Also, it will allow us to warn the user if they are *removing* a chem from their
  // inventory that has planned applications.
  //dpm($form_state,'form state on save');
  // grab chems, and send to a next form for confirmation that we wish to add them 
  $parms = drupal_get_query_parameters();
  $sel_chems = array_values(array_filter($form_state['values']['chems']));
  $farm = entity_load_single('dh_feature', $form_state['values']['farm_id']);
  //dpm($sel_chems, 'sel_chems to remove');
  
  foreach ($farm->field_link_agchem_material['und'] as $key => $ref) {
    if (in_array($farm->field_link_agchem_material['und'][$key]['target_id'], $sel_chems)) {
      unset($farm->field_link_agchem_material['und'][$key]);
    }
  }
  //dpm($farm,'farm');
  $farm->save();
  // got to the review and save page 
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
  } else {
    $url = implode('/', array('ipm-facility-materials'));
  }
  drupal_goto($url);
}


function om_agman_material_remove_cancel($form, &$form_state) {
  $parms = drupal_get_query_parameters();
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
  } else {
    $url = implode('/', array('ipm-home'));
  }
  drupal_goto($url);
}

$parms = drupal_get_query_parameters();
$remove_chems = $parms['chems'];
$remove_chems = is_array($remove_chems) ? $remove_chems : array($remove_chems);
//dsm("from the top");
$a = arg();
$farm_id = $a[1];
$form_state = array();
$form_state['build_info']['args'] = array($farm_id, $remove_chems);
form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
//dpm($form_state,'post form_load_include');
$elements = drupal_build_form('om_agman_material_remove_form', $form_state);

//drupal_get_form('om_agman_material_search_form')
//dpm($elements, 'form');
echo drupal_render($elements);

?>