<?php
function dh_vineyard_form($form, &$form_state, $dh_feature = null, $op = 'edit') {
  if ($dh_feature === NULL) {
    $props = array(
      'bundle' => 'facility', 
    );
    $dh_feature = entity_create('dh_feature', $props);
    $form_state['entity_type'] = 'dh_feature';
  }
  if ($op == 'clone') {
    $dh_feature->name .= ' (cloned)';
    $dh_feature->bundle = '';
  }

/*  $form['name'] = array(
    '#title' => t('Label'),
    '#type' => 'textfield',
    '#default_value' => $dh_feature->name,
    '#description' => t('The human-readable name of this dH Feature type.'),
    '#required' => TRUE,
    '#size' => 30,
  );*/

  $form['name'] = array(
    '#title' => t('Vineyard Name'),
    '#type' => 'textfield',
    '#default_value' => $dh_feature->name,
    '#description' => t('Name'),
    '#required' => TRUE,
    '#size' => 30,
  );  

  $form['ftype'] = array(
    '#title' => t('FType'),
    '#type' => 'hidden',
    '#default_value' => 'vineyard',
    '#description' => t('FType'),
    '#required' => TRUE,
    '#size' => 30,
  );  
  if (trim($dh_feature->hydrocode) == '') {
    $dh_feature->hydrocode = str_replace(' ', '_', strtolower($dh_feature->name ));
  }
  $form['hydrocode'] = array(
    '#title' => t('HydroCode'),
    '#type' => 'hidden',
    '#default_value' => 'test',
    //s'#default_value' => $dh_feature->hydrocode,
    '#description' => t('The unique identifier used by the originating agency of this dH Feature type.'),
    '#required' => FALSE,
    '#size' => 30,
  );
  $form['fstatus'] = array(
    '#title' => t('Status'),
    '#type' => 'hidden',
    '#options' => array(
      'proposed' => t('Proposed/Unknown/Other'),
      'active' => t('Active'),
      'inactive' => t('Out of Service/Temporarily Inactive'),
      'abandoned' => t('Abandoned'),
    ),
    '#default_value' => 'active',
    '#description' => t('The unique identifier used by the originating agency of this dH Feature type.'),
    '#required' => TRUE,
    '#multiple' => FALSE,
  );
  // Machine-readable type name.
  $form['bundle'] = array(
    '#type' => 'machine_name',
    '#default_value' => isset($dh_feature->bundle) ? $dh_feature->bundle : '',
    '#maxlength' => 32,
    '#attributes' => array('disabled' => 'disabled'),
    '#machine_name' => array(
      'exists' => 'dh_feature_get_types',
      'source' => array('label'),
    ),
    '#description' => t('A unique machine-readable name for this model type. It must only contain lowercase letters, numbers, and underscores.'),
  );


  field_attach_form('dh_feature', $dh_feature, $form, $form_state);
  //$hiddens = array('dh_link_feature_mgr_id', 'dh_link_admin_location', 'dh_link_facility_mps', 'dh_link_admin_fa_usafips', 'dh_link_admin_fa_or');
  $hiddens = array('field_link_agchem_material', 'dh_link_admin_location', 'dh_link_facility_mps', 'dh_link_admin_fa_usafips', 'dh_link_admin_fa_or');
  foreach ($hiddens as $hidethis) {
    if (isset($form[$hidethis])) {
      $form[$hidethis]['#type'] = 'hidden';
    }
  }
  global $user;
  //dpm($user->roles,'roles');
  //if (in_array(array('ipm_admin','administrator'), $user->roles)) {
  if (in_array('administrator', $user->roles) or in_array('IPM Admin', $user->roles)) {
    $form['dh_link_feature_mgr_id']['und']['#title'] = 'Add/Remove users who are allowed to manage this vineyard.';
  } else {
    $form['dh_link_feature_mgr_id']['#type'] = 'hidden';
  }

  $form['data']['#tree'] = TRUE;

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Vineyard Info'),
    '#weight' => 40,
  );
  switch ($op) {
    case 'add':
    $form['actions']['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#weight' => 45,
      '#limit_validation_errors' => array(),
      '#submit' => array('dh_vineyard_form_submit_cancel')
    );
    break;
    case 'edit':
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete Vineyard'),
      '#weight' => 45,
      '#limit_validation_errors' => array(),
      '#submit' => array('dh_vineyard_form_submit_delete')
    );
    break;
  }
  return $form;
}

function dh_vineyard_form_submit_cancel($form, &$form_state) {
  $url = $form_state['redirect'] ? $form_state['redirect'] : '';
  drupal_goto($url);
}

/**
 * Form API submit callback for the type form.
 */
function dh_vineyard_form_submit(&$form, &$form_state) {
  form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
  form_load_include($form_state, 'inc', 'dh', 'dh.admin');
  $dh_feature = entity_ui_form_submit_build_entity($form, $form_state);
  $dh_feature->save();
  $form_state['redirect'] = 'ipm-facility-info/' . $dh_feature->hydroid ;
}

/**
 * Form API submit callback for the delete button.
 */
function dh_vineyard_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/content/dh_features/manage/' . $form_state['dh_feature']->hydroid . '/delete';
}
global $user;
$vineyard = NULL;
$op = 'add';
$a = arg();
if (isset($a[2])) {
  $vid = $a[2];
}
if (isset($vid)) {
  $vineyard = entity_load_single('dh_feature', $vid);
  $op = 'edit';
} else {
  $vineyard = entity_create('dh_feature', array('bundle' => 'facility'));
}
$form_state = array();
$form_state['wrapper_callback'] = 'entity_ui_main_form_defaults';
$form_state['entity_type'] = 'dh_feature';
$form_state['bundle'] = 'facility';
$form_state['values']['name'] = 'A New Vineyard';
$form_state['redirect'] = 'ipm-facility-info';
form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
// set things before initial form_state build
$form_state['build_info']['args'] = array($vineyard, $op, 'dh_feature');

// **********************
// Load the form
// **********************
//$elements = drupal_get_form('dh_vineyard_form');
$elements = drupal_build_form('dh_vineyard_form', $form_state);
//$elements = entity_ui_get_bundle_add_form('dh_feature', 'facility');
// entity_ui_get_form($entity_type, $entity, $op = 'edit', $form_state = array())

error_reporting(E_ALL);
// just grab the regular form for proof of concept
$form = drupal_render($elements);
echo $form;
?>