<?php

function dh_sprayquan_form($form, &$form_state, $dh_adminreg_feature = null, $op = 'edit') {
  // load base form from
  ctools_include('plugins');
  $plugin = ctools_get_plugins('om', 'om_components', 'ObjectModelComponentsAgPlantVitisHandler');
  $class = ctools_plugin_get_class($plugin, 'handler');

  ctools_include('plugins');
  $plugins = ctools_get_plugins('om', 'om_components');
  //dpm($plugins,'all plug');
  $plugin = ctools_get_plugins('om', 'om_components', 'ObjectModelAgmanSprayAppEvent');
  $class = ctools_plugin_get_class($plugin, 'handler');
  //dpm($plugin,'plug');
  $config = array();
  if ($class) {
    $src = new $class($config);
    //dpm($src,'app plugin object');
    $src->dh_adminreg_feature = $dh_adminreg_feature;
    $src->buildForm($form, $form_state);
  }
  
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Update Totals'),
    '#weight' => 40,
  );
  $form['actions']['materials'] = array(
    '#type' => 'submit',
    '#value' => t('Edit Materials'),
    '#weight' => 40,
    '#submit' => array('dh_sprayquan_form_materials')
  );
  switch ($op) {
    case 'add':
    break;
    case 'edit':
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete Plan'),
      '#weight' => 45,
      '#limit_validation_errors' => array(),
      '#submit' => array('dh_sprayquan_form_submit_delete')
    );
    break;
  }
  $form['actions']['cancel'] = array(
    '#type' => 'submit',
    '#value' => t('Cancel'),
    '#weight' => 45,
    '#limit_validation_errors' => array(),
    '#submit' => array('dh_sprayquan_form_cancel')
  );
  $form['actions']['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
    '#weight' => 40,
    '#submit' => array('dh_sprayquan_form_return')
  );
  
  return $form;
}


/**
 * Form API submit callback for the delete button.
 */
function dh_sprayquan_form_submit_delete(&$form, &$form_state) {
  $parms = drupal_get_query_parameters();
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
  } else {
    $url = implode('/', array('ipm-home', $blockid));
  }
  $form_state['redirect'] = 'admin/content/dh_adminreg_feature/manage/' . $form_state['dh_adminreg_feature']->adminid . '/delete&destination=' . $url;
}

function dh_sprayquan_form_save(&$form, &$form_state) {
  form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
  form_load_include($form_state, 'inc', 'dh', 'dh.admin');
  ctools_include('plugins');
  //dpm($form_state, 'form state submitted');
  $class = ctools_plugin_load_class('om', 'om_components', 'ObjectModelAgmanSprayAppEvent', 'handler');
  $src = new $class($config);
  $o = $src->DefineOptions(); // @todo: all functions and variables need to be camelCase not CamelCase - classes are CamelCase
  $options = array();
  foreach ($o as $optname => $attributes) {
    $options[$optname] = isset($form_state['values'][$optname]) ? $form_state['values'][$optname] : NULL;
  }
  // this goes away I think because all are now stored as dh properties?
  //$form_state['values']['field_prop_config']['und'][0]['value'] = serialize($options);
  
  // @todo - handle the feature in the object code 
  //   ** but note that entity_ui_form_sub... doesn't work 
  //      have to hand spin see dh.display.inc
  $dh_adminreg_feature = entity_ui_form_submit_build_entity($form, $form_state);
  $dh_adminreg_feature->save();
  // handle all the attached stuff
  $src->dh_adminreg_feature = $dh_adminreg_feature;
  $src->submitForm($form, $form_state);
  return $dh_adminreg_feature;
}

function dh_sprayquan_form_submit(&$form, &$form_state) {
  $dh_adminreg_feature = dh_sprayquan_form_save($form, $form_state);
}

function dh_sprayquan_form_return(&$form, &$form_state) {
  $dh_adminreg_feature = dh_sprayquan_form_save($form, $form_state);
  $blockid = $dh_adminreg_feature->dh_link_feature_submittal['und'][0]['target_id'];
  $parms = drupal_get_query_parameters();
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
    drupal_goto($url);
    //dpm($url);
    $form_state['redirect'] = $url;
  } else {
    $url = implode('/', array('ipm-home', $blockid));
    drupal_goto($url);
  }
}

function dh_sprayquan_form_cancel($form, &$form_state) {
  $dh_adminreg_feature = entity_ui_form_submit_build_entity($form, $form_state);
  $blockid = $dh_adminreg_feature->dh_link_feature_submittal['und'][0]['target_id'];
  $parms = drupal_get_query_parameters();
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
  } else {
    $url = implode('/', array('ipm-home', $blockid));
  }
  drupal_goto($url);
}

function dh_sprayquan_form_materials($form, &$form_state) {
  // returns to material select form
  $dh_adminreg_feature = dh_sprayquan_form_save($form, $form_state);
  $blockid = $dh_adminreg_feature->dh_link_feature_submittal['und'][0]['target_id'];
  $parms = drupal_get_query_parameters();
  $extras = array();
  if (isset($parms['finaldest'])) {
    $extras['query']['finaldest'] = $parms['finaldest'];
  }
  $url = implode('/', array('ipm-live-events', $blockid, 'materials', $dh_adminreg_feature->adminid));
  drupal_goto($url, $extras);
}

$lu = NULL;
$op = 'add';
$date = date('Y-m-d');
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
    error_reporting(E_ALL);
    // just grab the regular form for proof of concept
    //$elements = entity_ui_get_form('dh_adminreg_feature', $lu, $op, $form_state);

    $form = drupal_render($elements);
    echo $form;
  } else {
    echo "Problem creating/loading object $planid";
  }
} else {
  echo "There was a problem, no block loaded.";
}

?>