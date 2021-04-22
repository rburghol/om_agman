<?php

// Usage: module_load_include("inc", "om_agman", "src/pages/om_agman_material_explore.inc");

module_load_include('module', 'om_agman');
module_load_include('inc', 'om_agman', 'src/lib/om_agman_search_fn');

function om_agman_material_explore_form($form, &$form_state, $farm_id) {
  $plugin = ctools_get_plugins('dh', 'dh_variables', 'dHVariablePluginFRAC');
  $class = ctools_plugin_get_class($plugin, 'handler');
  dpm($plugin,'plug');
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
    '#submit' => array('om_agman_material_explore_search')
  );
  $form['actions']['reset'] = array(
    '#type' => 'submit',
    '#value' => t('Reset Form'),
    '#weight' => 3,
    '#limit_validation_errors' => array(),
    '#submit' => array('om_agman_material_explore_cancel')
  );
  $criteria['farm_id'] = array('value' => -1);
  
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
  
  $form['chems'] = om_agman_material_explore_opts('search', $criteria, $new_chems); // pass in new_chems to this widget to highlight them in the header
  $form['actions']['#weight'] = 1;
  $form['chems']['#weight'] = 10;
  
  return $form;
}


function om_agman_material_explore_search($form, &$form_state) {
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


function om_agman_material_explore_next($form, &$form_state) {
dpm($form_state,'form state on next');
//dpm($form_state,'form state on search');
//dpm($_POST,'post args');
  $form_state['rebuild'] = TRUE;
  // do we stash selected items in a state variable somehow?  Or think of this as a simple find a chem, save it. Move on. 
  return TRUE;
}

function om_agman_material_explore_prev($form, &$form_state) {
//dpm($form_state,'form state on search');
dpm($form_state,'form state on prev');
//dpm($_POST,'post args');
  $form_state['rebuild'] = TRUE;
  return TRUE;
}


function om_agman_material_explore_cancel($form, &$form_state) {
  $url = 'ipm-material-search';
  drupal_goto($url);
}

//dsm("from the top");
$a = arg();
$farm_id = -1;
$form_state = array();

$form_state['build_info']['args'] = array($farm_id);
form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
//dpm($form_state,'post form_load_include');
$elements = drupal_build_form('om_agman_material_explore_form', $form_state);

//drupal_get_form('om_agman_material_explore_form')
//dpm($elements, 'form');
echo drupal_render($elements);

?>