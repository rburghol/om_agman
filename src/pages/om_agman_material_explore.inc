<?php

// Usage: module_load_include("inc", "om_agman", "src/pages/om_agman_material_explore.inc");

module_load_include('module', 'om_agman');
module_load_include('inc', 'om_agman', 'src/lib/om_agman_search_fn');

function om_agman_material_explore_form($form, &$form_state, $farm_id) {
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
  $ftype_options = array('fungicide', 'herbicide', 'insecticide', 'fertilizer', 'other');
  $ftype_options = array_combine($ftype_options, $ftype_options);
  //dpm($phi_options,'phi');
  $form['ftype'] = array(
    '#title' => t('Material Type'),
    '#type' => 'select',
    '#default_value' => array(),
    '#options' => $ftype_options,
    '#description' => t('Search for material type (fungicide, herbicide, etc.'),
    '#required' => FALSE,
    '#multiple' => FALSE,
    '#empty_option' => '- Any -',
    '#size' => 1,
  );  
  $form['frac'] = $frac_opts;
  $form['frac']['#title'] = 'Filter by Mode of Action';
  $form['frac']['#empty_option'] = '- All Modes - ';
  $form['name'] = array(
    '#title' => t('Material Name'),
    '#type' => 'textfield',
    '#default_value' => '',
    '#description' => t('Search for material mode of action such as FRAC and IRAC code.'),
    '#required' => FALSE,
    '#size' => 30,
  );  
  // get target organisms
  $plugin = ctools_get_plugins('dh', 'dh_variables', 'dHVariablePluginIPMDisease');
  $class = ctools_plugin_get_class($plugin, 'handler');
  //dpm($plugin,'plug');
  $config = array();
  
  if ($class) {
    $src = new $class($config);
    $target_orgs = $src->incidentCodes();
    $form['target_org'] = array(
      '#title' => t('Filter by Target Organism (fungicide only)'),
      '#type' => 'select',
      '#default_value' => array(),
      '#options' => $target_orgs,
      '#description' => t('Search for materials that are labelled for a specific organism.'),
      '#required' => FALSE,
      '#multiple' => FALSE,
      '#empty_option' => '- Any -',
      '#size' => 1,
    );  
  } else {
    watchdog("Cannot get list of target organisms.  Please report to system administrator.");
  }

  $form['ai'] = array(
    '#title' => t('Active Ingredient'),
    '#type' => 'textfield',
    '#default_value' => '',
    '#description' => t('Search for material with active ingredient name containing the entered text.'),
    '#required' => FALSE,
    '#size' => 30,
  );  
  // retrieve an efficacy rating property to use plugin to format the search drop-down
  
  $phi_options = array_merge(range(0,14), range(21,63,7));
  $phi_options = array_combine($phi_options, $phi_options);
  array_walk($phi_options, function(&$value, $index){
    $value .= ' days';
  });
  //dpm($phi_options,'phi');
  $form['phi'] = array(
    '#title' => t('Pre-Harvest Interval (PHI)'),
    '#type' => 'select',
    '#default_value' => array(),
    '#options' => $phi_options,
    '#description' => t('Search for material with pre=hearvest interval less than the select value.'),
    '#required' => FALSE,
    '#multiple' => FALSE,
    '#empty_option' => '- No Limit -',
    '#size' => 1,
  );  

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
  if (!empty($form_state['input']['phi'])) {
    $criteria['phi'] = array('value' => $form_state['input']['phi']);
  }
  if (!empty($form_state['input']['ftype'])) {
    $criteria['ftype'] = array('value' => $form_state['input']['ftype']);
  }
  if (!empty($form_state['input']['target_org'])) {
    $criteria['target_org'] = array('value' => $form_state['input']['target_org']);
  }
  // Only show PMG in this form.
  $criteria['pmg'] = array('value' => 'PMG');
  $criteria['filter_mode'] = array('value' => 'query');
  // 
  $new_chems = array();
  $form['chems'] = om_agman_material_search_opts('search', $criteria, $new_chems); // pass in new_chems to this widget to highlight them in the 
  //dpm($form['chems'],' chems table');
  $form['actions']['#weight'] = 1;
  $form['chems']['#weight'] = 10;
  $form['chems']['#prefix'] .= t('The superscript <sup>PMG</sup> indicates that this material is reviewed in the Pest Management Guide.');
  $form['chems']['#suffix'] .= t('The superscript <sup>PMG</sup> indicates that this material is reviewed in the Pest Management Guide.');
  
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