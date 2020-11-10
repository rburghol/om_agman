<?php  
module_load_include('module', 'om');
$status = module_load_include('inc', 'om', 'lib/om_misc_functions');
if (!$status) {
  dsm("Could not load om_misc_functions.inc");
}
$args = arg();
dpm($args,'args');
$vineyard_id = $args[1];
$block_id = $args[2];
$startdate = '2020-01-01';
$enddate = '2020-12-31';


$q = "SELECT material_frac.propcode AS material_frac, "; 
$q .= "   block.name AS block_name, "; 
$q .= "   frac_max_apps.propvalue AS frac_max_apps, "; 
$q .= "   COUNT(DISTINCT app_event.adminid) AS frac_app_count"; 
$q .= " FROM "; 
$q .= " dh_adminreg_feature app_event"; 
$q .= " LEFT JOIN field_data_dh_link_feature_submittal link_block ON app_event.adminid = link_block.entity_id AND link_block.entity_type = 'dh_adminreg_feature'"; 
$q .= " LEFT JOIN dh_feature block ON link_block.dh_link_feature_submittal_target_id = block.hydroid"; 
$q .= " LEFT JOIN field_data_field_link_to_registered_agchem app_material ON app_event.adminid = app_material.entity_id AND app_material.entity_type = 'dh_adminreg_feature'"; 
$q .= " LEFT JOIN dh_adminreg_feature material_reg ON app_material.field_link_to_registered_agchem_target_id = material_reg.adminid"; 
$q .= " LEFT JOIN dh_properties material_frac ON material_reg.adminid = material_frac.featureid AND (material_frac.entity_type = 'dh_adminreg_feature' AND material_frac.varid = ( '112' ))"; 
$q .= " LEFT JOIN field_data_dh_link_facility_mps link_vineyard ON block.hydroid = link_vineyard.entity_id AND link_vineyard.entity_type = 'dh_feature'"; 
$q .= " LEFT JOIN dh_feature vineyard ON link_vineyard.dh_link_facility_mps_target_id = vineyard.hydroid"; 
$q .= " LEFT JOIN dh_variabledefinition frac_vardef ON material_frac.varid = frac_vardef.hydroid"; 
$q .= " LEFT JOIN dh_properties frac_code_options ON frac_vardef.hydroid = frac_code_options.featureid AND frac_code_options.entity_type = 'dh_variabledefinition' and frac_code_options.propname = 'propcode_options'"; 
$q .= " LEFT JOIN dh_properties frac_code_info ON frac_code_options.pid = frac_code_info.featureid AND frac_code_info.entity_type = 'dh_properties' and frac_code_info.propcode = material_frac.propcode "; 
$q .= " LEFT JOIN dh_properties frac_max_apps ON frac_code_info.pid = frac_max_apps.featureid AND (frac_max_apps.entity_type = 'dh_properties' AND frac_max_apps.varid = ( '130' ))"; 
$q .= " LEFT JOIN dh_properties frac_code_info2 ON frac_vardef.hydroid = frac_code_info2.varid"; 
$q .= " WHERE vineyard.hydroid = $vineyard_id"; 
$q .= "   AND block.hydroid = $block_id"; 
$q .= "   AND app_event.bundle = 'agchem_app'"; 
$q .= "   AND app_event.startdate >= extract(epoch from '$startdate'::timestamp ) "; 
$q .= "   AND app_event.startdate <= extract(epoch from '$enddate'::timestamp )"; 
$q .= " GROUP BY block.name,material_frac.propcode,"; 
$q .= "   frac_max_apps.propvalue, frac_max_apps.propvalue"; 

// default table of frac app warnings
$messages = array(
  array('0', 'OK. Less than max recommended seasonal applications.'),
  array('2', 'Equal to max recommended seasonal applications. No more applications recommended.'),
  array('3', 'You have used the same FRAC with medium or high risk of fungicide resistance 3 times, please revise your schedule.'),
  array('4', 'You have used the same FRAC with medium or high risk of fungicide resistance 4 times, this type of practice significantly increases the risk of resistance development.'),
  array('1000', 'You have used the same FRAC with medium or high risk of fungicide resistance 4 times, this type of practice significantly increases the risk of resistance development.')
);
$messages_formatted = om_formatCSVMatrix($messages, 1,  'OK', 0);

$risky_frac = range( 1, 50);
unset($risky_frac[array_search(33, $risky_frac)]);

$header = array();
$rez = db_query($q);
$header = array("FRAC", 'Max/year', '# of Apps', 'Status');

while ($row = $rez->fetchAssoc()) {
  $num = $row['frac_app_count'];
  $frac = $row['material_frac'];
  unset($row['block_name']);
  //dsm($frac);
  // @todo: we will fetch a custom table of app warnings from each frac entry, but till now we use the one from above
  $message = '';
  //if (is_numeric(substr($frac,0,1))) $message = om_arrayLookup($messages_formatted, $num, 2, 0, TRUE);
  if (in_array($frac, $risky_frac)) $message = om_arrayLookup($messages_formatted, $num, 2, 0, TRUE);
  $row['message'] = $message;
  $data[] = array_values($row);
}
//dpm($data,'data');
$display = array(
  '#theme' => 'table',
  '#header' => $header,
  '#rows' => $data,
);
$output = render($display);

echo $output;
?>