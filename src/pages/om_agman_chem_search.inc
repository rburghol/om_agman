<?php

module_load_include('module', 'om_agman');
module_load_include('inc', 'om_agman', 'src/lib/om_agman_search_fn');

function om_agman_material_search_form($form, &$form_state, $farm_id) {
//dpm($farm_id, 'farm id');
  //dpm($form_state,'form to om_agman_material_search_form');
  //if ($form_state['input']['op'] == 'Search') {
  //  dpm($form_state['input'],'Searching');
  //}
  $plugin = ctools_get_plugins('dh', 'dh_variables', 'dHVariablePluginFRAC');
  $class = ctools_plugin_get_class($plugin, 'handler');
  //dpm($plugin,'plug');
  $config = array();
  if ($class) {
    $src = new $class($config);
    $arr = array();
    $src->formRowEdit($arr, $empty_object);
    $frac_opts = $arr['propcode'];
  }
  $form['frac'] = $frac_opts;
  $form['frac']['#title'] = 'Filter by FRAC';
  $form['name'] = array(
    '#title' => t('Material Name'),
    '#type' => 'textfield',
    '#default_value' => '',
    '#description' => t('Search for material with name containing the entered text.'),
    '#required' => FALSE,
    '#size' => 30,
  );  

  $form['ai'] = array(
    '#title' => t('Active Ingredient'),
    '#type' => 'textfield',
    '#default_value' => '',
    '#description' => t('Search for material with active ingredient name containing the entered text.'),
    '#required' => FALSE,
    '#size' => 30,
  );  
  // retrieve an efficacy rating property to use plugin to format the search drop-down

  $form['actions'] = array('#type' => 'actions');
  
  // type 'button' does not submit.
  // This is useful in that it also does not call a rebuild,
  // but it does send values to the form that can be used to alter the query results.
  // preserving the state for the next step.
  $form['actions']['search'] = array(
    '#type' => 'button',
    #'#type' => 'submit',
    '#value' => t('Search'),
    '#weight' => 1,
    '#limit_validation_errors' => array(),
    '#submit' => array('om_agman_material_search_search')
  );
  $form['actions']['cancel'] = array(
    '#type' => 'submit',
    '#value' => t('Cancel'),
    '#weight' => 3,
    '#limit_validation_errors' => array(),
    '#submit' => array('om_agman_material_search_cancel')
  );
  $form['actions']['save'] = array(
    '#type' => 'submit',
    '#value' => t('Add Selected to Inventory'),
    '#weight' => 2,
    '#submit' => array('om_agman_material_search_save')
  );
  $criteria['farm_id'] = array('value' => $farm_id);
  
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
  $new_chems = array();
  if (isset($form_state['input']['chems'])) {
    $sel_chems = array_values(array_filter($form_state['input']['chems']));
    //dpm($sel_chems,'selected chems');
    $farm = entity_load_single('dh_feature', $farm_id);
    $farm_chems = $farm->field_link_agchem_material['und'];
    $farm_chems = array_column($farm_chems, 'target_id');
    //dpm($farm_chems,'farm chems');
    $new_chems = array_diff($sel_chems, $farm_chems);
    //dpm($new_chems, 'new_chems');
  }
  $form['farm_id'] = array(
    '#type' => 'hidden',
    '#default_value' => $farm_id,
  );
  //$form['chems'] = om_agman_material_search_opts('select', $criteria, $new_chems); // pass in new_chems to this widget to highlight them in the header
  $form['actions']['#weight'] = 1;
  $form['chems']['#weight'] = 10;
  
  return $form;
}


function om_agman_material_search_search($form, &$form_state) {
  //dpm($form_state,'form state on search');
  //dpm($_POST,'post args');
  $a = arg();
  $path = implode("/", $a);
  foreach ( array('frac', 'ai', 'name') as $crit) {
    $params[$crit] = $form_state['input'][$crit];
  }
  //dpm($params,"search_search params");
  drupal_goto($path, array('query' => $params));

  #  $form_state['rebuild'] = FALSE;
  return TRUE;
}


function om_agman_material_search_next($form, &$form_state) {
dpm($form_state,'form state on next');
//dpm($form_state,'form state on search');
//dpm($_POST,'post args');
  $form_state['rebuild'] = TRUE;
  // do we stash selected items in a state variable somehow?  Or think of this as a simple find a chem, save it. Move on. 
  return TRUE;
}

function om_agman_material_search_prev($form, &$form_state) {
//dpm($form_state,'form state on search');
dpm($form_state,'form state on prev');
//dpm($_POST,'post args');
  $form_state['rebuild'] = TRUE;
  return TRUE;
}

function om_agman_material_search_save($form, &$form_state) {
  // make this a simple work flow where we confirm that the user wants to do this with an entirely different page.  
  // this will simplify handling. Also, it will allow us to warn the user if they are *removing* a chem from their
  // inventory that has planned applications.
  //dpm($form_state,'form state on save');
  // grab chems, and send to a next form for confirmation that we wish to add them 
  $sel_chems = array_values(array_filter($form_state['values']['chems']));
  dpm($sel_chems,'selected chems');
  $farm = entity_load_single('dh_feature', $form_state['values']['farm_id']);
  $farm_chems = $farm->field_link_agchem_material['und'];
  $farm_chems = array_column($farm_chems, 'target_id');
  dpm($farm_chems,'farm chems');
  $new_chems = array_diff($sel_chems, $farm_chems);
  dpm($new_chems, 'new_chems');
  // got to the review and save page 
  $a = arg();
  $path = implode("/", $a);
  $params = array();
  foreach ( $new_chems as $chem_id) {
    $params['chems'][] = $chem_id;
  }
  //dpm($params,"search_search params");
  drupal_goto($path, array('query' => $params));
  return TRUE;
}


function om_agman_material_search_cancel($form, &$form_state) {
  $parms = drupal_get_query_parameters();
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
  } else {
    $url = implode('/', array('ipm-home'));
  }
  drupal_goto($url);
}

//dsm("from the top");
$farm_id = 146;
$form_state = array();
$form_state['build_info']['args'] = array($farm_id);
form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
//dpm($form_state,'post form_load_include');
$elements = drupal_build_form('om_agman_material_search_form', $form_state);

//drupal_get_form('om_agman_material_search_form')
//dpm($elements, 'form');
echo drupal_render($elements);

?>