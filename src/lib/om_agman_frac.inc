<?php

module_load_include('module', 'om');
$status = module_load_include('inc', 'om', 'lib/om_misc_functions');
if (!$status) {
  dsm("Could not load om_misc_functions.inc");
}

// BEGIN FUNCTION LOADING
// ************************************
// BEGIN om_agman_frac_count()
// ************************************
function om_agman_frac_count($block_id, $vineyard_id, $startdate, $enddate, $target_fracs = array()) {  
  $q = " SELECT material_frac, block_name, frac_max_apps, sum(frac_app_count) as frac_app_count ";
  $q .= " FROM ( ";
  $q .= " SELECT CASE ";
  $q .= "    WHEN trim(material_frac.propcode) = 'n.a.' THEN 'n/a' ";
  $q .= "    WHEN trim(material_frac.propcode) = 'N/A' THEN 'n/a' ";
  $q .= "    WHEN trim(material_frac.propcode) = '' THEN 'n/a' ";
  $q .= "    WHEN material_frac.propcode IS NULL THEN 'n/a' ";
  $q .= "    ELSE trim(material_frac.propcode) ";
  $q .= "   END AS material_frac, "; 
  $q .= "   block.name AS block_name, "; 
  $q .= "   frac_max_apps.propvalue AS frac_max_apps, "; 
  $q .= "   COUNT(DISTINCT app_event.adminid) AS frac_app_count"; 
  $q .= " FROM "; 
  $q .= " dh_adminreg_feature app_event"; 
  $q .= " LEFT JOIN field_data_dh_link_feature_submittal link_block ON app_event.adminid = link_block.entity_id AND link_block.entity_type = 'dh_adminreg_feature' "; 
  $q .= " LEFT JOIN dh_feature block ON link_block.dh_link_feature_submittal_target_id = block.hydroid"; 
  $q .= " LEFT JOIN field_data_field_link_to_registered_agchem app_material ON app_event.adminid = app_material.entity_id AND app_material.entity_type = 'dh_adminreg_feature'"; 
  $q .= " LEFT JOIN dh_adminreg_feature material_reg ON app_material.field_link_to_registered_agchem_target_id = material_reg.adminid"; 
  $q .= " LEFT JOIN dh_properties material_frac ON material_reg.adminid = material_frac.featureid  "; 
  $q .= "   AND (material_frac.entity_type = 'dh_adminreg_feature'  "; 
  $q .= "   AND material_frac.varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_frac') "; 
  $q .= " ) "; 
  $q .= " LEFT JOIN field_data_dh_link_facility_mps link_vineyard ON block.hydroid = link_vineyard.entity_id AND link_vineyard.entity_type = 'dh_feature'"; 
  $q .= " LEFT JOIN dh_feature vineyard ON link_vineyard.dh_link_facility_mps_target_id = vineyard.hydroid"; 
  $q .= " LEFT JOIN dh_variabledefinition frac_vardef ON material_frac.varid = frac_vardef.hydroid"; 
  $q .= " LEFT JOIN dh_properties frac_code_options ON frac_vardef.hydroid = frac_code_options.featureid AND frac_code_options.entity_type = 'dh_variabledefinition' and frac_code_options.propname = 'propcode_options'"; 
  $q .= " LEFT JOIN dh_properties frac_code_info ON frac_code_options.pid = frac_code_info.featureid AND frac_code_info.entity_type = 'dh_properties' and frac_code_info.propcode = material_frac.propcode "; 
  $q .= " LEFT JOIN dh_properties frac_max_apps  "; 
  $q .= " ON frac_code_info.pid = frac_max_apps.featureid  "; 
  $q .= "   AND (frac_max_apps.entity_type = 'dh_properties'  "; 
  $q .= "   AND frac_max_apps.varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_max_app_no') "; 
  $q .= " )"; 
  $q .= " LEFT JOIN dh_properties frac_code_info2 ON frac_vardef.hydroid = frac_code_info2.varid"; 
  $q .= " WHERE vineyard.hydroid = $vineyard_id"; 
  $q .= "   AND block.hydroid = $block_id";
  if ($target_fracs != array()){
    $cond = "('".implode("','", $target_fracs)."')";
    $q .= "   AND material_frac.propcode IN $cond";
  }
  $q .= "   AND app_event.bundle = 'agchem_app'"; 
  $q .= "   AND app_event.startdate >= extract(epoch from '$startdate'::timestamp ) "; 
  $q .= "   AND app_event.startdate <= extract(epoch from '$enddate'::timestamp )"; 
  $q .= " GROUP BY block.name,material_frac.propcode,"; 
  $q .= "   frac_max_apps.propvalue "; 
  $q .= " ) as foo ";
  $q .= " GROUP BY block_name,material_frac,"; 
  $q .= "   frac_max_apps ";
  $q .= " ORDER BY block_name, material_frac ";
  $rez = db_query($q);
  return $rez;
}
// ************************************
// END om_agman_frac_count()
// *************************************

// ************************************
// BEGIN om_agman_frac_assess()
//dsm($frac);
// @todo: we will fetch a custom table of app warnings from each frac entry, but till now we use the one from above
// default table of frac app warnings
// later we may have allow a custom set of warnings for agiven hem, but these are defaults and ALL will use them for now
// ************************************
function om_agman_frac_assess($frac, $frac_count){
   $frac_ratings = array(
    array('0', '0'),
    array('2', '1'),
    array('3', '2'),
    array('4', '3'),
    array('1000', '3')
    );
    $frac_ratings_formatted = om_formatCSVMatrix($frac_ratings, 1,  'OK', 0);

    $messages = array(
    array('0', 'OK. Less than max recommended seasonal applications.'),
    array('1', 'Equal to max recommended seasonal applications. No more applications recommended.'),
    array('2', 'You have used the same FRAC with medium or high risk of fungicide resistance 3 times, please revise your schedule.'),
    array('3', 'You have used the same FRAC with medium or high risk of fungicide resistance 4 times, this type of practice significantly increases the risk of resistance development.')
    );
    $messages_formatted = om_formatCSVMatrix($messages, 1,  'OK', 0);

    $risky_frac = range( 1, 50);
    unset($risky_frac[array_search(33, $risky_frac)]);
    unset($risky_frac[array_search(44, $risky_frac)]);
    $message = '';
    //if (is_numeric(substr($frac,0,1))) $message = om_arrayLookup($messages_formatted, $rating, 2, 0, TRUE);
    if (in_array($frac, $risky_frac)) {
      $rating = om_arrayLookup($frac_ratings_formatted, $frac_count, 2, 0, TRUE);
      $message = om_arrayLookup($messages_formatted, $rating, 2, 0, TRUE);
    } else {
      $rating = 0;
      $message = 'This FRAC group is considered low-risk. No maximum recommended for resistance management.';
    }
    return array('rating' => $rating, 'message' => $message);
}

// ************************************
// END om_agman_frac_assess() 
// ************************************

function om_agman_frac_alert($dh_adminreg_feature) {
  $values = array(
    'entity_type' => 'dh_adminreg_feature',
    'featureid' => $dh_adminreg_feature->adminid,
    'varkey' => 'agchem_application_event'
  );
  $phi_ts = NULL;
  $ts_event = dh_timeseries_enforce_singularity($values, 'singular');
  $plugin = dh_variables_getPlugins($ts_event);
  $plugin->load_event_info($dh_adminreg_feature);
  $alerts = $plugin->checkFracStatus($ts_event, $dh_adminreg_feature);
  dpm($alerts, 'alerts on save');
}

?>