<?php  
$vineyard_id = 146;
$block_id = 147;
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

echo $q;

?>