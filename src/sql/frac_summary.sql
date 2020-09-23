SELECT material_frac.propcode AS material_frac, 
  block.name AS block_name, 
  frac_max_apps.propvalue AS frac_max_apps, 
  COUNT(DISTINCT app_event.adminid) AS frac_app_count
FROM 
dh_adminreg_feature app_event
LEFT JOIN field_data_dh_link_feature_submittal link_block ON app_event.adminid = link_block.entity_id AND link_block.entity_type = 'dh_adminreg_feature'
LEFT JOIN dh_feature block ON link_block.dh_link_feature_submittal_target_id = block.hydroid
LEFT JOIN field_data_field_link_to_registered_agchem app_material ON app_event.adminid = app_material.entity_id AND app_material.entity_type = 'dh_adminreg_feature'
LEFT JOIN dh_adminreg_feature material_reg ON app_material.field_link_to_registered_agchem_target_id = material_reg.adminid
LEFT JOIN dh_properties material_frac ON material_reg.adminid = material_frac.featureid AND (material_frac.entity_type = 'dh_adminreg_feature' AND material_frac.varid = ( '112' ))
LEFT JOIN field_data_dh_link_facility_mps link_vineyard ON block.hydroid = link_vineyard.entity_id AND link_vineyard.entity_type = 'dh_feature'
LEFT JOIN dh_feature vineyard ON link_vineyard.dh_link_facility_mps_target_id = vineyard.hydroid
LEFT JOIN dh_variabledefinition frac_vardef ON material_frac.varid = frac_vardef.hydroid
LEFT JOIN dh_properties frac_code_options ON frac_vardef.hydroid = frac_code_options.featureid AND frac_code_options.entity_type = 'dh_variabledefinition' and frac_code_options.propname = 'propcode_options'
LEFT JOIN dh_properties frac_code_info ON frac_code_options.pid = frac_code_info.featureid AND frac_code_info.entity_type = 'dh_properties' and frac_code_info.propcode = material_frac.propcode 
LEFT JOIN dh_properties frac_max_apps ON frac_code_info.pid = frac_max_apps.featureid AND (frac_max_apps.entity_type = 'dh_properties' AND frac_max_apps.varid = ( '130' ))
LEFT JOIN dh_properties frac_code_info2 ON frac_vardef.hydroid = frac_code_info2.varid
WHERE vineyard.hydroid = '146'
  AND block.hydroid = '147'
  AND app_event.bundle = 'agchem_app'
  AND app_event.startdate >= 1577854800 
  AND app_event.startdate <= extract(epoch from '2020/12/31'::timestamp )
GROUP BY block.name,material_frac.propcode,
  frac_max_apps.propvalue, frac_max_apps.propvalue;