<?php

module_load_include('module', 'om_agman');

function om_agman_material_query($farm_id, $show_all = TRUE, $criteria = array()) {
$query = "select farm.hydroid, chem.adminid, chem.name, 
  br.propvalue as br, 
  dm.propvalue as dm,
  pm.propvalue as pm,
  array_accum(frac.propcode) as frac_codes
from dh_adminreg_feature as chem 
left outer join field_data_field_link_agchem_material as clink 
on (
  clink.field_link_agchem_material_target_id = chem.adminid
  and clink.entity_id = $farm_id
)
left outer join dh_feature as farm 
on (
  farm.hydroid = clink.entity_id
  and clink.entity_type = 'dh_feature' 
) 
left outer join dh_properties as br 
on (
  br.featureid = chem.adminid
  and br.entity_type = 'dh_adminreg_feature'
  and br.varid in (select hydroid from dh_variabledefinition where varkey = 'org_black_rot')
)
left outer join dh_properties as dm 
on (
  dm.featureid = chem.adminid
  and dm.entity_type = 'dh_adminreg_feature'
  and dm.varid in (select hydroid from dh_variabledefinition where varkey = 'org_downy_mildew')
)
left outer join dh_properties as pm 
on (
  pm.featureid = chem.adminid
  and pm.entity_type = 'dh_adminreg_feature'
  and pm.varid in (select hydroid from dh_variabledefinition where varkey = 'org_powdery_mildew')
)
left outer join dh_properties as frac
on (
  frac.featureid = chem.adminid
  and frac.entity_type = 'dh_adminreg_feature'
  and frac.varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_frac')
)
where 
chem.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer', 'pesticide', 'other') ";
if (!$show_all) {
  $query .= " and farm.hydroid is not null ";
}
if (isset($criteria['frac'])) {
  $query .= " and chem.adminid in (" ;
  $query .= "   select featureid from dh_properties where   ";
  $query .= "   entity_type = 'dh_adminreg_feature' ";
  $query .= "   and varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_frac') ";
  $query .= "   and propcode = '" . $criteria['frac']['value'] . "' ";
  $query .= " ) ";
}
$query .= " group by farm.hydroid, chem.adminid, chem.name, br.propvalue, dm.propvalue, pm.propvalue";
return $query;
}

function om_agman_material_search_opts($farm_id) {
  $query = om_agman_material_query($farm_id); // see https://github.com/rburghol/om_agman/issues/240
  $agchem_result = db_query($query);
  $opts = array();
  $selected = array();

  while($chem_record = $agchem_result->fetchObject()) {
    $id = $chem_record->adminid;
    if ($chem_record->hydroid > 0) {
      $selected[$chem_record->adminid] = 1;
    }
    $chem_record->frac_codes = trim($chem_record->frac_codes,'{}');
    //dpm($chem_record);
    $valkeys = array(
      'REI'=>'agchem_rei', 
      'PHI' => 'agchem_phi', 
    );
    $codekeys = array(
      'PM' => 'pm', 
      'DM' => 'dm', 
      'Ph' => 'ph', 
      'BR' => 'br', 
      'Bot' => 'bot'
    );

    // BEGIN - tableselect
    $opts[$id] = array(
      'label' => $chem_record->name . ' ' . $chem_record->ftype, 
    );
    $opts[$id]['REI'] = $chem_record->rei;
    $opts[$id]['PHI'] = $chem_record->phi;
    $opts[$id]['FRAC'] = $chem_record->frac_codes;

    foreach ($codekeys as $label => $key) {
      $opts[$id][$label] = $chem_record->{$key};
    }
    $delim = '';
    // we now have a frac aggregator which saves as a short propcode
    // END - tableselect
  }
  //dpm($opts,'opts');

  $element = array(
    '#type' => 'tableselect',
    '#options' => $opts,
    '#default_value' => $selected,
    '#required' => TRUE,
    '#multiple' => TRUE,
  );
  $element['#empty'] = t('No content available.');
  $element['#required'] = TRUE;
  $element['#header'] = array(
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
  return $element;
}

function om_agman_material_search_form($form, &$form_state, $farm_id) {
dpm($farm_id, 'farm id');
dpm($form_state,'form state post-build');

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
  $form['fracs'] = $frac_opts;
  $form['fracs']['#title'] = 'Filter by FRAC';
  
  $form['chems'] = om_agman_material_search_opts($farm_id);

  $form['actions'] = array('#type' => 'actions');

  $form['actions']['search'] = array(
    '#type' => 'submit',
    '#value' => t('Search'),
    '#weight' => 45,
    '#limit_validation_errors' => array(),
    '#submit' => array('om_agman_material_search_search')
  );
  $form['actions']['cancel'] = array(
    '#type' => 'submit',
    '#value' => t('Cancel'),
    '#weight' => 45,
    '#limit_validation_errors' => array(),
    '#submit' => array('om_agman_material_search_cancel')
  );
  $form['actions']['save'] = array(
    '#type' => 'submit',
    '#value' => t('Save'),
    '#weight' => 40,
    '#submit' => array('om_agman_material_search_save')
  );
  
  return $form;
}


function om_agman_material_search_search($form, &$form_state) {
dpm($form_state,'form state on search');
dpm($_POST,'post args');
  return TRUE;
}

function om_agman_material_search_save($form, &$form_state) {
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


$farm_id = 146;
$form_state = array();
$form_state['build_info']['args'] = array($farm_id);
form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
$elements = drupal_build_form('om_agman_material_search_form', $form_state);

//drupal_get_form('om_agman_material_search_form')
dpm($elements, 'form');
echo drupal_render($elements);

?>