<?php

// use in any page with 1-line:
//   module_load_include('inc', 'om_agman', 'src/pages/agchem_spray_event_materials');

module_load_include('module', 'om_agman');

function dh_app_plan_form($form, &$form_state, $dh_adminreg_feature = null, $op = 'edit') {
  if ($dh_adminreg_feature === NULL) {
    $props = array(
      'bundle' => 'agchem_app', 
    );
    $dh_adminreg_feature = entity_create('dh_adminreg_feature', $props);
    $form_state['entity_type'] = 'dh_adminreg_feature';
  }
  if ($op == 'clone') {
    $dh_adminreg_feature->name .= ' (cloned)';
    $dh_adminreg_feature->bundle = '';
  }
  if ($dh_adminreg_feature->adminid > 0) {
    $form['adminid'] = array(
      '#type' => 'hidden',
      '#default_value' => $dh_adminreg_feature->adminid,
    );  
  }

  $form['name'] = array(
    '#title' => t('Plan Name'),
    '#type' => 'textfield',
    '#default_value' => empty($dh_adminreg_feature->name) ? 'Planned Spray' : $dh_adminreg_feature->name,
    '#description' => t('Name'),
    '#required' => TRUE,
    '#size' => 30,
  );  

  $form['ftype'] = array(
    '#title' => t('FType'),
    '#type' => 'hidden',
    '#default_value' => 'agchem_app_plan',
    '#description' => t('FType'),
    '#required' => TRUE,
    '#size' => 30,
  );  
  if (trim($dh_adminreg_feature->hydrocode) == '') {
    $dh_adminreg_feature->hydrocode = str_replace(' ', '_', strtolower($dh_adminreg_feature->name ));
  }
  $form['admincode'] = array(
    '#title' => t('AdminCode'),
    '#type' => 'hidden',
    '#default_value' => '',
    //'#default_value' => $dh_adminreg_feature->hydrocode,
    '#description' => t('The unique identifier used by the originating agency of this dH Feature type.'),
    '#required' => FALSE,
    '#size' => 30,
  );
  $form['fstatus'] = array(
    '#title' => t('Status'),
    '#type' => 'select',
    '#options' => array(
      'planned' => t('Planned'),
      'completed' => t('Completed'),
      'cancelled' => t('Cancelled'),
      'post_harvest' => t('Post-Harvest'),
    ),
    '#default_value' => empty($dh_adminreg_feature->fstatus) ? 'planned' : $dh_adminreg_feature->fstatus,
    '#required' => TRUE,
    '#multiple' => FALSE,
  );
  $date_format = 'Y-m-d';
  // should have code in here to guess based on the phase/or passed in from the URL
  $form['startdate'] = array(
    '#title' => t('Application Date'),
    '#description' => t('Planned date for this spray.'),
    '#required' => TRUE,
    '#default_value' => empty($dh_adminreg_feature->startdate) ? $dh_adminreg_feature->startdate : date($date_format,$dh_adminreg_feature->startdate),
    '#date_format' => $date_format,
    '#type' => 'date_select',
    '#date_year_range' => '-5:+5',
  );
  // Machine-readable type name.
  $form['bundle'] = array(
    '#type' => 'hidden',
    '#default_value' => !empty($dh_adminreg_feature->bundle) ? $dh_adminreg_feature->bundle : 'agchem_app',
    '#maxlength' => 32,
    //'#attributes' => array('disabled' => 'disabled'),
    '#machine_name' => array(
      'exists' => 'dh_adminreg_feature_get_types',
      'source' => array('label'),
    ),
    '#description' => t('A unique machine-readable name for this model type. It must only contain lowercase letters, numbers, and underscores.'),
  );


  field_attach_form('dh_adminreg_feature', $dh_adminreg_feature, $form, $form_state);
  // now, adjust the block ids form to show only those for this vineyard.
  $dh_adminreg_feature->farmid = property_exists($dh_adminreg_feature, 'farmid') ? $dh_adminreg_feature->farmid : FALSE;
  //dpm($dh_adminreg_feature,'feature');
  om_agman_form_block_select($form['dh_link_feature_submittal'], $dh_adminreg_feature->farmid);
  $hiddens = array(
    'dh_link_admin_submittal_pr', 
    //'dh_link_feature_submittal', 
    'dh_link_admin_timeseries',
  );
  foreach ($hiddens as $hidethis) {
    if (isset($form[$hidethis])) {
      $form[$hidethis]['#type'] = 'hidden';
    }
  }
  
  // add material options for this farm
  $fname = 'field_link_to_registered_agchem';
  $form[$fname]['und']['#title'] = 'Select Registered Spray Materials to Include for this Farm';
  $blockid = $dh_adminreg_feature->dh_link_feature_submittal['und'][0]['target_id'];
  om_agman_agchem_materials_select($dh_adminreg_feature->farmid, $form, $fname, 'ipm_block_select_formulary', 'page_1', 'dh_adminreg_feature_field_data_field_link_agchem_material_ad');
  $form['data']['#tree'] = TRUE;
  //dpm($form[$fname],'original chem form');
  
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save & Enter Amounts'),
    '#weight' => 40,
    '#submit' => array('dh_app_plan_form_sprayquan')
  );
  switch ($op) {
    case 'add':
    $form['actions']['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#weight' => 45,
      '#limit_validation_errors' => array(),
      '#submit' => array('dh_app_plan_form_submit_cancel')
    );
    break;
    case 'edit':
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete Spray Event'),
      '#weight' => 45,
      '#limit_validation_errors' => array(),
      '#submit' => array('dh_app_plan_form_submit_delete')
    );
    break;
  }
  //dpm($form, 'form');
  return $form;
}

function dh_app_plan_form_submit_cancel($form, &$form_state) {
  $parms = drupal_get_query_parameters();
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
    drupal_goto($url);
    //dpm($url);
    $form_state['redirect'] = $url;
  } else {
    $url = $form_state['redirect'] ? $form_state['redirect'] : 'ipm-facility-sprayplan';
    drupal_goto($url);
  }
}

/**
 * Form API submit callback for the type form.
 */
function dh_app_plan_form_save(&$form, &$form_state) {
  form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
  form_load_include($form_state, 'inc', 'dh', 'dh.admin');
  // load the object
  $dh_adminreg_feature = entity_ui_form_submit_build_entity($form, $form_state);
  //@todo: 1 houtr per acre
  $dh_adminreg_feature->enddate = dh_handletimestamp($dh_adminreg_feature->startdate) + 3600;
  $dh_adminreg_feature->save();
  return $dh_adminreg_feature;
}

function dh_app_plan_form_sprayquan(&$form, &$form_state) {
  dpm(t('This application is designed to be an aid to help your pesticide use planning. However, it is your responsibility to keep, read, and follow the labels and SDS.'), NULL, 'warning');
  $blockid = $form_state['values']['dh_link_feature_submittal']['und'][0]['target_id'];
  $dh_adminreg_feature = dh_app_plan_form_save($form, $form_state);
  $url = implode('/', array('ipm-live-events', $blockid, 'sprayquan', $dh_adminreg_feature->adminid));
  $extras = array();
  $parms = drupal_get_query_parameters();
  if (isset($parms['finaldest'])) {
    $extras['query']['finaldest'] = $parms['finaldest'];
  }
  drupal_goto($url, $extras);
  //$form_state['redirect'] = $url;
}

/**
 * Form API submit callback for the delete button.
 */
function dh_app_plan_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/content/dh_adminreg_feature/manage/' . $form_state['dh_adminreg_feature']->adminid . '/delete';
}

global $user;
// Use: q=ipm-live-events/[blockid]/materials/[action or e-adminid]/[vineyardid]/[date]?finaldest=ipm-home/146/all
// Ex: q=ipm-live-events/all/materials/add/146/2017-03-26?finaldest=ipm-home/146/all
// we are given the block featureid as input
// need to fetch the vineyard/facility parent of block
// get the available material list from the parent vineyard 
$planid = 'add';
$op = 'add';
$a = arg();
$parms = drupal_get_query_parameters();
//dpm($a);
$startdate = date('Y-m-d');
if (isset($a[1])) {
  $blockid = $a[1];
}
if (isset($a[3])) {
  $planid = $a[3];
}
if (isset($a[4])) {
  $farmid = $a[4];
}
// validate here and require a valid farmid/hydroid
if (!is_numeric($farmid) and !is_numeric($blockid)) {
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
  } else {
    $url = 'ipm-home';
  }
  drupal_set_message("You must include a valid farm id or block id.", 'error');
  drupal_goto($url);
}
$farmid = (!is_numeric($farmid)) ? om_agman_get_block_farmid($blockid) : $farmid;
// if farmid == all and blockid == all, drupal_set_message and go to the finaldest url
// but we should never reach this condition
if (isset($a[5])) {
  $startdate = $a[5];
}
$farm = entity_load_single('dh_feature', $farmid);
//dpm($farm);
if (empty($farm->field_link_agchem_material['und'])) {
  $url = "ipm-facility-materials/$farmid/select";
  drupal_set_message("You must first add spray materials to your tool box.");
  $url .= "&finaldest=" . implode("/", $a);
  drupal_goto($url);  
}
if ($planid == 'add') {
  if ($blockid == 'all') {
    // get all block for this farm
    $blocks = dh_get_facility_mps($farmid, 'landunit');
    $block_ids = array('und'=>array());
    foreach ($blocks as $blockid) {
      $block_ids['und'][] = array('target_id' => $blockid);
    }
  } else {
    $block_ids = array(
      'und' => array( 
        0 => array(
          'target_id' => $blockid
        ) 
      )
    );
  }
  $plan = entity_create('dh_adminreg_feature', 
    array(
      'bundle' => 'agchem_app', 
      'startdate' => strtotime($startdate), 
      'dh_link_feature_submittal' => 
        $block_ids,
      //array(
      //  'und' => array( 
      //    0 => array(
      //      'target_id' => $blockid
      //    ) 
      //  )
      //)
    )
  );
  $op = 'add';
} else {
  $result = entity_load('dh_adminreg_feature', array($planid));
  $plan = $result[$planid];
  $op = 'edit';
}
$plan->farmid = $farmid; // pass the farm id in
$form_state = array();
$form_state['wrapper_callback'] = 'entity_ui_main_form_defaults';
$form_state['entity_type'] = 'dh_adminreg_feature';
$form_state['bundle'] = 'agchem_app';
$redirect = implode("/",array($a[0],$a[1]));
$form_state['redirect'] = $redirect;
form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
// set things before initial form_state build
$form_state['build_info']['args'] = array($plan, $op, 'dh_adminreg_feature');

// **********************
// Load the form
// **********************
//$elements = drupal_get_form('dh_app_plan_form');
$elements = drupal_build_form('dh_app_plan_form', $form_state);
//$elements = entity_ui_get_bundle_add_form('dh_adminreg_feature', 'facility');
// entity_ui_get_form($entity_type, $entity, $op = 'edit', $form_state = array())

// just grab the regular form for proof of concept
$form = drupal_render($elements);
$link = implode("/",array($a[0],$a[1],$a[2],$a[3]));
echo "<div><a href='" . base_path() . "?q=$redirect'>Return to Management Dashboard</a></div>";
echo $form;
?>