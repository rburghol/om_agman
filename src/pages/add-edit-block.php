<?php
function dh_landunit_form($form, &$form_state, $dh_feature = null, $op = 'edit') {

  if ($dh_feature === NULL) {
    $props = array(
      'bundle' => 'landunit', 
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
    '#title' => t('Block Name'),
    '#type' => 'textfield',
    '#default_value' => $dh_feature->name,
    '#description' => t('Block Name'),
    '#required' => TRUE,
    '#size' => 30,
  );  

  $form['ftype'] = array(
    '#title' => t('FType'),
    '#type' => 'hidden',
    '#default_value' => 'vineyard_block',
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
    '#type' => 'hidden',
    '#default_value' => isset($dh_feature->bundle) ? $dh_feature->bundle : 'landunit',
    '#maxlength' => 32,
  );


  field_attach_form('dh_feature', $dh_feature, $form, $form_state);
  $hiddens = array('dh_link_feature_mgr_id', 'dh_link_admin_location', 'dh_link_facility_mps');
  //$hiddens = array('dh_link_admin_location', 'dh_link_facility_mps');
  foreach ($hiddens as $hidethis) {
    if (isset($form[$hidethis])) {
      $form[$hidethis]['#type'] = 'hidden';
    }
  }
  $form['dh_areasqkm']['und'][0]['value']['#title'] = 'Area (ac)';
  // convert to acres for display - will reconvert back to km2 at save time
  $form['dh_areasqkm']['und'][0]['value']['#default_value'] = round($dh_feature->dh_areasqkm['und'][0]['value'] * 247.1,2);
  //$form['dh_areasqkm'][
  //dpm($dh_feature,'dh_feature');
  //dpm($form,'form');

  $form['data']['#tree'] = TRUE;

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Block'),
    '#weight' => 40,
  );
  switch ($op) {
    case 'add':
    $form['actions']['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#weight' => 45,
      '#limit_validation_errors' => array(),
      '#submit' => array('dh_landunit_form_submit_cancel')
    );
    break;
    case 'edit':
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete Block'),
      '#weight' => 45,
      '#limit_validation_errors' => array(),
      '#submit' => array('dh_landunit_form_submit_delete')
    );
    break;
  }
  return $form;
}

function dh_landunit_form_submit_cancel($form, &$form_state) {
  $url = $form_state['redirect'] ? $form_state['redirect'] : '';
  drupal_goto($url);
}

/**
 * Form API submit callback for the type form.
 */
function dh_landunit_form_submit(&$form, &$form_state) {
  form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
  form_load_include($form_state, 'inc', 'dh', 'dh.admin');
  $dh_feature = entity_ui_form_submit_build_entity($form, $form_state);
  if (!$dh_feature->hydroid) {
    // new feature
    $config_variety = TRUE;
  } else {
    $config_variety = FALSE;
  }
  $dh_feature->dh_areasqkm['und'][0]['value'] = round($dh_feature->dh_areasqkm['und'][0]['value'],2) / 247.105;
  $dh_feature->save();
  if ($config_variety) {
    $url = implode('/', array('ipm-landunit-info', $dh_feature->hydroid, 'editcrop'));
  } else {
    $url = implode('/', array('ipm-landunit-info', $dh_feature->hydroid));
  }
  $form_state['redirect'] = $url;
}

/**
 * Form API submit callback for the delete button.
 */
function dh_landunit_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/content/dh_features/manage/' . $form_state['dh_feature']->hydroid . '/delete';
}
global $user;
$uid = $user->uid;
$lu = NULL;
$op = 'add';
$a = arg();
$ok = FALSE;
if (isset($a[1])) {
  if (intval($a[1])) {
    // block edit called
    $luid = $a[1];
    $ok = TRUE;
  } else {
    $luid = FALSE;
    if (isset($a[2]) and intval($a[2])){
      $vid = $a[2];
      $ok = TRUE;
    } else {
      $ok = FALSE;
    }
  }
}
if ($ok) {
  
  // default block location
  $v = entity_load_single('dh_feature', $vid);
  $default_geofield = $v->dh_geofield;
  dpm($default_geofield);
  if ($luid) {
    $lu = entity_load_single('dh_feature', $luid);
    if ($lu) {
      $op = 'edit';
    }
  }
  $lu = is_object($lu) ? $lu : entity_create('dh_feature', array('bundle' => 'landunit', 
      'dh_link_facility_mps' => array('und' => array( 0 => array('target_id' => $vid) )), 
      'dh_link_feature_mgr_id' => array('und' => array( 0 => array('target_id' => $uid) )),
      'dh_geofield' => $default_geofield,
    )
  );

  $form_state = array();
  $form_state['wrapper_callback'] = 'entity_ui_main_form_defaults';
  $form_state['entity_type'] = 'dh_feature';
  $form_state['bundle'] = 'landunit';
  $form_state['redirect'] = 'ipm-facility-info';
  form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
  $form_state['build_info']['args'] = array($lu, 'add', 'dh_feature');

  // **********************
  // Load the form
  // **********************
  //$elements = drupal_get_form('dh_landunit_form');
  $elements = drupal_build_form('dh_landunit_form', $form_state);
  //$elements = entity_ui_get_bundle_add_form('dh_feature', 'landunit');
  // entity_ui_get_form($entity_type, $entity, $op = 'edit', $form_state = array())
  error_reporting(E_ALL);
  // just grab the regular form for proof of concept
  //$elements = entity_ui_get_form('dh_feature', $lu, $op, $form_state);

  $form = drupal_render($elements);
  echo $form;
} else {
  
}
?>