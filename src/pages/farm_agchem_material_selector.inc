<?php
// use in any page with 1-line:
//   module_load_include('inc', 'om_agman', 'src/pages/farm_agchem_material_selector');
function dh_agchem_material_select($blockid, &$form, $fname, $viewname, $displayname) {
  $deftable = array(); // need to translate normal select to table select syntax
  $def = $form[$fname]['und']['#default_value']; // get the currently selected values 
  //dpm($form[$fname],"$fname");
  foreach ($def as $selopt) {
    $deftable[$selopt] = 1;
  }
  //dpm($fname,'form');
  $form[$fname]['und']['#empty'] = t('No content available.');
  $form[$fname]['und']['#type'] = 'tableselect';
  $form[$fname]['und']['#default_value'] = $deftable;
  $form[$fname]['und']['#header'] = array(
    'label' => 'Material', 
    'REI'=>'REI', 
    'PHI' => 'PHI', 
    'FRAC' => 'FRAC', 
    'PM' => 'PM', 
    'DM' => 'DM', 
    'Ph' => 'Ph', 
    'BR' => 'BR', 
    'Bot' => 'Bot'
  );
  $opts = array();
  foreach($form[$fname]['und']['#options'] as $id => $result) {
    // do something.
    //dpm($result,'rez');
    // ****************************
    // Use Views un-comment this
    // $id = $result->adminid;
    // ****************************
    // format the material
    $material = entity_load_single('dh_adminreg_feature', $id);
    $criteria = array();
    $varkeys = array(
      'REI'=>'agchem_rei', 
      'PHI' => 'agchem_phi', 
      'FRAC' => 'agchem_frac', 
      'PMG' => 'ipm_vt_pmg_material', 
      'PM' => 'org_powdery_mildew', 
      'DM' => 'org_downy_mildew', 
      'Ph' => 'org_phomopsis', 
      'BR' => 'org_black_rot', 
      'Bot' => 'org_botrytis'
    );
    $valkeys = array(
      'REI'=>'agchem_rei', 
      'PHI' => 'agchem_phi', 
    );
    $codekeys = array(
      'PM' => 'org_powdery_mildew', 
      'DM' => 'org_downy_mildew', 
      'Ph' => 'org_phomopsis', 
      'BR' => 'org_black_rot', 
      'Bot' => 'org_botrytis'
    );
    $vars = dh_vardef_varselect_options(array("varkey in ('" . implode("', '", array_values($varkeys)) . "')"));
    $criteria[] = array(
      'name' => 'varid',
      'op' => 'IN',
      'value' => array_keys($vars),
    );
    $material->loadComponents($criteria);
    //dpm($criteria, 'criteria');
    
    // BEGIN - tableselect
    // is col is -  // field_link_to_registered_agchem_target_id
    //dpm($material, 'material');
    $pmg = isset($material->dh_properties['ipm_vt_pmg_material']) ? $material->dh_properties['ipm_vt_pmg_material']->propcode : '';
    //dpm($material->dh_properties['ipm_vt_pmg_material'],'pmg prop');
    $opts[$id] = array(
      'label' => $material->name . "<sup>" . $pmg . "</sup>", 
    );
    if ($pmg <> '') {
      $opts[$id]['label'] = "<strong>" . $opts[$id]['label'] . "</strong>";
    }
    foreach ($valkeys as $label => $key) {
      if (isset($material->dh_properties[current($material->prop_varkey_map[$key])])) { 
        $opts[$id][$label] = $material->dh_properties[current($material->prop_varkey_map[$key])]->propvalue;
      } else {
        $opts[$id][$label] = 'und';
      }
    }
    foreach ($codekeys as $label => $key) {
      if (isset($material->dh_properties[current($material->prop_varkey_map[$key])])) { 
        $opts[$id][$label] = $material->dh_properties[current($material->prop_varkey_map[$key])]->propcode;
      } else {
        $opts[$id][$label] = 'und';
      }
    }
    $delim = '';
    foreach (array_unique($material->prop_varkey_map['agchem_frac']) as $propk) {
      $opts[$id]['FRAC'] .= $delim . $material->dh_properties[$propk]->propcode;
      $delim = ', ';
    }
    // END - tableselect
  }
  $form[$fname]['und']['#options'] = $opts;
}

function dh_manage_materials_form($form, &$form_state, $dh_feature = null, $op = 'edit') {
  if ($dh_feature === NULL) {
    return FALSE;
  }
  field_attach_form('dh_feature', $dh_feature, $form, $form_state);
  //$hiddens = array('dh_link_feature_mgr_id', 'dh_link_admin_location', 'dh_link_facility_mps', 'dh_link_admin_fa_usafips', 'dh_link_admin_fa_or');
  $unattached = field_info_instances('dh_feature', 'facility');
  unset($unattached['field_link_agchem_material']);
  foreach ($unattached as $fieldname => $fieldsettings) {
    unset($form[$fieldname]);
  }
  // custom config for agchem select view
  $fname = 'field_link_agchem_material';
  $form[$fname]['und']['#title'] = 'Select Registered Spray Materials to Include for this Farm';
  dh_agchem_material_select($blockid, $form, $fname, 'ipm_manage_farm_material', 'entityreference_1');

  $form['data']['#tree'] = TRUE;

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Material List'),
    '#weight' => 40,
  );
  switch ($op) {
    case 'add':
    $form['actions']['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#weight' => 45,
      '#limit_validation_errors' => array(),
      '#submit' => array('dh_manage_materials_form_submit_cancel')
    );
    break;
  }
  return $form;
}

function dh_manage_materials_form_submit_cancel($form, &$form_state) {
  $parms = drupal_get_query_parameters();
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
  } else {
    $url = implode('/', array('ipm-home'));
  }
  drupal_goto($url);
}

/**
 * Form API submit callback for the type form.
 */
function dh_manage_materials_form_submit(&$form, &$form_state) {
  drupal_set_message("Materials List Updated");
form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
  form_load_include($form_state, 'inc', 'dh', 'dh.admin');
  // BREAKS HERE
  $dh_feature = entity_ui_form_submit_build_entity($form, $form_state);
  $dh_feature->save();
  $parms = drupal_get_query_parameters();
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
  } else {
    $url = implode('/', array('ipm-facility-materials', $dh_feature->hydroid, 'inventory'));
  }
  drupal_goto($url);
}

/**
 * Form API submit callback for the delete button.
 */
function dh_manage_materials_form_submit_delete(&$form, &$form_state) {
  $form_state['redirect'] = 'admin/content/dh_features/manage/' . $form_state['dh_feature']->hydroid . '/delete';
}

global $user;
// we are given the block featureid as input
// need to fetch the vineyard/facility parent of block
// get the available material list from the parent vineyard 
$plan = NULL;
$op = 'add';
$a = arg();
if (isset($a[1])) {
  $vid = $a[1];
} else {
  $vid = FALSE;
}
if (is_numeric($vid)) {
  $result = entity_load('dh_feature', array($vid));
  $plan = $result[$vid];
  $op = 'edit';
  if (is_object($plan)) {
    $form_state = array();
    $form_state['wrapper_callback'] = 'entity_ui_main_form_defaults';
    $form_state['entity_type'] = 'dh_feature';
    $form_state['bundle'] = 'submittal';
    form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
    // set things before initial form_state build
    $form_state['build_info']['args'] = array($plan, 'add', 'dh_feature');

    // **********************
    // Load the form
    // **********************
    //$elements = drupal_get_form('dh_manage_materials_form');
    $elements = drupal_build_form('dh_manage_materials_form', $form_state);
    //$elements = entity_ui_get_bundle_add_form('dh_feature', 'facility');
    // entity_ui_get_form($entity_type, $entity, $op = 'edit', $form_state = array())

    error_reporting(E_ALL);
    // just grab the regular form for proof of concept
    $form = drupal_render($elements);
    echo $form;
  } else {
    echo "could not load entity $vid";
  }
} else {
echo "No farm selected";
}
?>