<?php

function dh_blockcrop_form($form, &$form_state, $dh_properties = null, $op = 'edit') {
  //dpm($dh_properties);
  $form['propname'] = array(
    '#title' => t('Variety Name'),
    '#type' => 'textfield',
    '#default_value' => $dh_properties->propname,
    '#description' => t('Variety Name'),
    '#required' => TRUE,
    '#size' => 30,
  );
  
  $form['varid'] = array(
    '#title' => t('Variable'),
    '#type' => 'hidden',
    '#default_value' => $dh_properties->varid,
    '#description' => t('The unique identifier for this Variable.'),
    '#required' => TRUE,
    '#multiple' => FALSE,
  ); 

  $form['featureid'] = array(
    '#title' => t('Parent Feature'),
    '#type' => 'hidden',
    '#default_value' => $dh_properties->featureid,
    '#description' => t('The unique identifier for this Variable.'),
    '#required' => TRUE,
    '#multiple' => FALSE,
  );
  // Machine-readable type name.
  $form['bundle'] = array(
    '#type' => 'hidden',
    '#default_value' => $dh_properties->bundle,
    '#maxlength' => 32,
  );

  field_attach_form('dh_properties', $dh_properties, $form, $form_state);
  //$hiddens = array();
  $hiddens = array('field_prop_config');
  foreach ($hiddens as $hidethis) {
    if (isset($form[$hidethis])) {
      $form[$hidethis]['#type'] = 'hidden';
    }
  }
  //include_once("/var/www/html/d.alpha/modules/emapping/plugins/emapping_source.inc");
//module_load_include('module', 'views');
//module_load_include('inc', 'views', 'includes/admin.inc');
  if (isset($dh_properties->field_prop_config)) {
    $config = unserialize($dh_properties->field_prop_config['und'][0]['value']);
  } else {
    $config = array();
  }
  ctools_include('plugins');
  $plugin = ctools_get_plugins('om', 'om_components', 'ObjectModelComponentsAgPlantVitisHandler');
  $class = ctools_plugin_get_class($plugin, 'handler');
  if ($class) {
    $src = new $class($config);
    $src->buildOptionsForm($form, $form_state);
  }
  $form['data']['#tree'] = TRUE;
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Variety Info'),
    '#weight' => 40,
  );

  return $form;
}


function dh_blockcrop_form_submit(&$form, &$form_state) {
  form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
  form_load_include($form_state, 'inc', 'dh', 'dh.admin');
  ctools_include('plugins');
  $class = ctools_plugin_load_class('om', 'om_components', 'ObjectModelComponentsAgPlantVitisHandler', 'handler');
  $src = new $class($config);
  $o = $src->DefineOptions();
  $options = array();
  foreach ($o as $optname => $attributes) {
    $options[$optname] = isset($form_state['values'][$optname]) ? $form_state['values'][$optname] : NULL;
  }
  $form_state['values']['field_prop_config']['und'][0]['value'] = serialize($options);
  $dh_properties = entity_ui_form_submit_build_entity($form, $form_state);
  $dh_properties->save();
  $url = implode('/', array('ipm-facility-info', dh_getMpFacilityHydroId($dh_properties->featureid)));
  $form_state['redirect'] = $url;
}

$lu = NULL;
$op = 'add';
module_load_include('module', 'dh');
$a = arg();
if (count($a) >= 2) {
  $luid = $a[1];
}
if (isset($luid)) {
  $block = entity_load_single('dh_feature', $luid);
  $var = dh_vardef_info('agman_plant');
  $loaded = array();
  if (is_object($var) and is_object($block)) {
    $loaded = $block->loadComponents(array('varid' => $var->hydroid));
  }
  if (count($loaded) > 0) {
    // load existing prop for editing
    $crop = entity_load_single('dh_properties', $loaded[0]);
    $op = 'edit';
  } else {
    // we need to create this
    $defaults = array(
      'bundle' => 'agplant_vitis', 
      'entity_type' => 'dh_feature', 
      'featureid' => $luid,
      'propname' => '',
      'varid' => $var->hydroid,
    );
    $op = 'add';
    $crop = entity_create('dh_properties', $defaults);
  }
  if (is_object($crop)) {
    $form_state = array();
    $form_state['wrapper_callback'] = 'entity_ui_main_form_defaults';
    $form_state['entity_type'] = 'dh_properties';
    $form_state['bundle'] = 'agplant_vitis';
    form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
    $form_state['build_info']['args'] = array($crop, $op, 'dh_properties');

    // **********************
    // Load the form
    // **********************
    $elements = drupal_build_form('dh_blockcrop_form', $form_state);
    //$elements = entity_ui_get_bundle_add_form('dh_properties', 'mnw_file');
    // entity_ui_get_form($entity_type, $entity, $op = 'edit', $form_state = array())
    error_reporting(E_ALL);
    // just grab the regular form for proof of concept
    //$elements = entity_ui_get_form('dh_properties', $lu, $op, $form_state);

    $form = drupal_render($elements);
    echo $form;
  } else {
    echo "Problem creating/loading object $luid";
  }
} else {
  echo "There was a problem, no block loaded.";
}

?>