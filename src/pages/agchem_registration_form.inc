<?php
// use in any page with 1-line:
//   module_load_include('inc', 'om_agman', 'src/pages/agchem_registration_form.inc');
module_load_include('module', 'om_agman');

function agchem_registration_form($form, &$form_state, $dh_adminreg_feature = null, $op = 'edit') {
  if ($op == 'clone') {
    $dh_adminreg_feature->name .= ' (cloned)';
    $dh_adminreg_feature->bundle = '';
  }
  
  if ($op == 'clone') {
    $dh_adminreg_feature->name .= ' (cloned)';
    $dh_adminreg_feature->bundle = '';
  }

  $form['name'] = array(
    '#title' => t('Formulary Name'),
    '#type' => 'textfield',
    '#default_value' => $dh_adminreg_feature->name,
    '#description' => t('Name'),
    '#required' => TRUE,
    '#size' => 50,
    '#weight' => -1,
  );
  $types = array(
    'fungicide' => 'Fungicide',
    'insecticide' => 'Insecticide',
    'herbicide' => 'Herbicide',
    'fertilizer' => 'Fertilizer',
    'other' => 'Other',
  );
  
  $form['ftype'] = array(
    '#title' => t('Type'),
    '#type' => 'select',
    '#options' => $types,
    '#default_value' => $dh_adminreg_feature->ftype,
    '#description' => t('The type of chemical class.'),
    '#required' => TRUE,
    '#multiple' => FALSE,
    '#weight' => 1,
  );

  if (trim($dh_adminreg_feature->admincode) == '') {
    $dh_adminreg_feature->admincode = str_replace(' ', '_', strtolower($dh_adminreg_feature->name ));
  }
  $form['admincode'] = array(
    '#title' => t('admincode'),
    '#type' => 'hidden',
    '#default_value' => $dh_adminreg_feature->admincode,
    '#description' => t('The unique identifier used by the originating agency of this dH Feature type.'),
    '#required' => TRUE,
    '#size' => 30,
  );
  $form['fstatus'] = array(
    '#title' => t('Status'),
    '#type' => 'select',
    '#options' => array(
      'proposed' => t('Proposed/Unknown/Other'),
      'active' => t('Active'),
      'inactive' => t('Out of Service/Temporarily Inactive'),
      'abandoned' => t('Abandoned'),
      'duplicate' => t('Duplicate'),
    ),
    '#default_value' => $dh_adminreg_feature->fstatus,
    '#description' => t('The current status of this organization. Please select Active if still in operation.'),
    '#required' => FALSE,
    '#multiple' => FALSE,
    '#weight' => 2,
  );

  field_attach_form('dh_adminreg_feature', $dh_adminreg_feature, $form, $form_state);
  // reformat attached fields
  $form['dh_link_admin_reg_issuer']['und']['#title'] = 'Registering Authority';
  $form['dh_link_admin_reg_issuer']['und']['#multiple'] = FALSE;
  global $user;
  dpm($user->roles,'user');
  dpm($form,'form');
  //dpm($form['dh_link_admin_reg_issuer'],'options');
  // is admin?
  $is_admin = in_array('IPM Admin', $user->roles);
  $is_admin = ($is_admin === FALSE) ? in_array('administrator', $user->roles) : FALSE;
  if ($is_admin === FALSE) {
    // restrict options to user-defined if not an admin
    $uix = array_search('User Defined', $form['dh_link_admin_reg_issuer']['und']['#options']);
    $form['dh_link_admin_reg_issuer']['und']['#options'] = array($uix => 'User Defined');
  }
  $form['registration_id']['und'][0]['value']['#required'] = TRUE;
  $form['registration_id']['und'][0]['value']['#description'] = t('The id issued by the reulatory authority, or in the case of a user-defined material, or non-registered, a unique identifier (for user-defined this can be the same as material name).');
  //dpm($dh_adminreg_feature,'feature');
  $hiddens = array('dh_link_admin_reg_holder', 'dh_link_admin_record_mgr_id', 'dh_link_admin_dha_usafips', 'dh_geofield', 'field_dha_local_gov', 'dh_link_admin_timeseries');
  //$hiddens = array('dh_link_admin_location', 'dh_link_organization_mps');
  foreach ($hiddens as $hidethis) {
    if (isset($form[$hidethis])) {
      $form[$hidethis]['#type'] = 'hidden';
    }
  }

  // set weights on other fields:
  $form['description']['#title'] = 'Notes/Comments';
  $form['description']['#weight'] = 11;
  $form['field_supporting_docs']['#weight'] = 10;
  
  $form['data']['#tree'] = TRUE;
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
    '#weight' => 40,
  );
  $form['actions']['cancel'] = array(
    '#type' => 'submit',
    '#value' => t('Cancel'),
    '#weight' => 41,
    '#limit_validation_errors' => array(),
    '#submit' => array('agchem_registration_form_submit_cancel')
  );
  $form['actions']['rates'] = array(
    '#type' => 'submit',
    '#value' => t('Save & Edit Rates'),
    '#weight' => 42,
  );
  if (!empty($dh_adminreg_feature->adminid)) {
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete'),
      '#weight' => 50,
      //'#limit_validation_errors' => array(),
      '#submit' => array('agchem_registration_form_delete_confirm')
    );
  }
  //dpm($form,'form');
  return $form;
}

function agchem_registration_form_submit_cancel($form, &$form_state) {
  // just honor the destination parameter
  $parms = drupal_get_query_parameters();
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
    drupal_goto($url);
    //dpm($url);
    $form_state['redirect'] = $url;
  } else {
    $url = implode('/', array('ipm-home'));
    drupal_goto($url);
  }
}

function agchem_registration_form_delete_confirm($form, &$form_state, $dh_adminreg_feature) {
  $form['#dh_adminreg_feature'] = $dh_adminreg_feature;
  dpm($form);
  $form['adminid'] = array('#type' => 'value', '#value' => $dh_adminreg_feature->adminid);
  return confirm_form($form, 
    t('Are you sure you want to delete %title?', array('%title' => $dh_adminreg_feature->name)),
    'dh_adminreg_feature/' . $dh_adminreg_feature->adminid,
    t('This action cannot be undone.'),
    t('Delete'),
    t('Cancel')
  );
}

function agchem_registration_form_confirm_delete(&$form, &$form_state) {
  dpm($form);
  $dh_adminreg_feature = entity_ui_form_submit_build_entity($form, $form_state);
  unset($_GET['destination']);
  //if (empty($dh_adminreg_feature->adminid)) {
  //  return $form;
  //}
  $form['adminid'] = array(
    '#type' => 'textfield',
    '#value' => $dh_adminreg_feature->adminid,
    '#weight' => 0,
  );
  $form['message'] = array(
    '#type' => 'markup',
    '#value' => t('Are you sure you want to delete?'),
    '#weight' => 10,
  );
  $form['actions']['confirm'] = array(
    '#type' => 'submit',
    '#value' => t('Yes, Delete'),
    '#weight' => 20,
    '#limit_validation_errors' => array(),
    '#submit' => array('agchem_registration_form_delete')
  );
  $form['actions']['disconfirm'] = array(
    '#type' => 'submit',
    '#value' => t('Nope, I changed my mind'),
    '#weight' => 30,
    '#limit_validation_errors' => array(),
    '#submit' => array('agchem_registration_form_return')
  );
  //dpm($form,'form');
  return $form;
}

/**
 * Form API submit callback for the type form.
 */
function agchem_registration_form_save(&$form, &$form_state) {
  form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
  form_load_include($form_state, 'inc', 'dh', 'dh.admin');
  $dh_adminreg_feature = entity_ui_form_submit_build_entity($form, $form_state);
  $dh_adminreg_feature->admincode = $dh_adminreg_feature->registration_id['und'][0]['value'];
  $dh_adminreg_feature->save();
  $frac_group = array(
    'varkey' => 'grouped_prop_controller',
    'featureid' => $dh_adminreg_feature->adminid,
    'entity_type' => 'dh_adminreg_feature',
    'propcode' => 'agchem_frac',
    'propname' => 'FRAC Codes'
  );
  //error_log("Saving frac controller " . print_r($frac_group,1));
  om_model_getSetProperty($frac_group, 'name');
  $ai_group = array(
    'varkey' => 'grouped_prop_controller',
    'featureid' => $dh_adminreg_feature->adminid,
    'entity_type' => 'dh_adminreg_feature',
    'propcode' => 'agchem_ai',
    'propname' => 'Active Ingredients'
  );
  $aiprop = om_model_getSetProperty($ai_group, 'name');
  //dpm($aiprop,'ai add result');
  return $dh_adminreg_feature;
}

/**
 * Form API submit callback for the type form.
 */
function agchem_registration_form_submit(&$form, &$form_state) {
  $dh_adminreg_feature = agchem_registration_form_save($form, $form_state);
  dsm("Saving $dh_adminreg_feature->name");
  //dpm($form_state['triggering_element']);
  // @todo: 
  // - enforce unique registration ID, regardless if it is a custom - provide guidance
  // - make admincode equal to registration iD
  if ($form_state['triggering_element']['#id'] == 'edit-rates') {
    $url = implode('/', array('ipm-fungicide-manage', $dh_adminreg_feature->adminid));
    //dpm($url, "Redirecting to rate edit screen");
    $parms = drupal_get_query_parameters();
    if (isset($parms['finaldest'])) {
      $destination = $parms['finaldest'];
      $url .= "&destination=$destination";
    } 
  } else {
    $parms = drupal_get_query_parameters();
    if (isset($parms['finaldest'])) {
      $url = $parms['finaldest'];
    } else {
      $url = implode('/', array('ipm-home'));
    }
  }
  drupal_goto($url);
}

/**
 * Form API submit callback for the delete button.
 */
function agchem_registration_form_submit_delete(&$form, &$form_state) {
  if (!empty($form_state['values']['adminid'])) {
    entity_delete('dh_adminreg_feature', $form_state['values']['adminid']);
  }
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
    drupal_goto($url);
    //dpm($url);
    $form_state['redirect'] = $url;
  } else {
    $url = implode('/', array('ipm-home'));
    drupal_goto($url);
  }
}

global $user;
$op = 'add';
$a = arg();
$fid = $a[1];
if ($fid > 0) {
  $result = entity_load('dh_adminreg_feature', array($fid));
  $organization = $result[$fid];
  $op = 'edit';
} else {
  $params = drupal_get_query_parameters();
  $issuer = $params['dh_link_admin_reg_issuer'];
  $organization = entity_create('dh_adminreg_feature', 
    array(
      'name' => 'New Formulary',
      'bundle' => 'registration',
      'dh_link_admin_reg_issuer' =>  array('und' => array( 0 => array('target_id' => $issuer) )),
    )
  );
}
$form_state = array();
$form_state['wrapper_callback'] = 'entity_ui_main_form_defaults';
$form_state['entity_type'] = 'dh_adminreg_feature';
$form_state['bundle'] = 'organization';
form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
// set things before initial form_state build
$form_state['build_info']['args'] = array($organization, $op, 'dh_adminreg_feature');

// **********************
// Load the form
// **********************
$elements = drupal_build_form('agchem_registration_form', $form_state);
$form = drupal_render($elements);
echo $form;
?>