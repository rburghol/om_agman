<?php
  module_load_include('inc', 'dh', 'plugins/dh.display');
  $uid = -1;
  $update_props = TRUE;
  // sql to get records with redundant erefs
  $q = "  select f.name, st_astext(fg.dh_geofield_geom), mp.name as block_name
    from dh_feature as f 
    left outer join field_data_dh_geofield as fg 
    on (
      f.hydroid = fg.entity_id 
      and fg.entity_type = 'dh_feature'
    ) 
    left outer join field_data_dh_link_facility_mps as l 
    on (
      f.hydroid = l.dh_link_facility_mps_target_id
    ) 
    left outer join dh_feature as mp 
    on (
      mp.hydroid = l.entity_id
    ) 
    left outer join field_data_dh_geofield as mg 
    on (
      mp.hydroid = mg.entity_id 
      and mg.entity_type = 'dh_feature'
    ) 
    where f.bundle = 'facility' 
    and mp.bundle = 'landunit' 
    and mg.dh_geofield_geom is null
  ";
  
  $result = db_query($q);
  // If we want to do a single one uncomment these lines:
  /* 
  $result = array(
    0 => new STDClass,
  );
  $result[0]->adminid = 299;
  */
  echo $q;
  
  foreach ($result as $record) {
    // get events
    
    // Load some entity.
    $dh_feature = entity_load_single('dh_feature', $record->hydroid);
    $vid = dh_getMpFacilityHydroId($dh_feature);
    $v = entity_load_single('dh_feature', $vid);
    $default_geofield = $v->dh_geofield; 
    if ($update_props and is_object($dh_feature)) {
      $dh_feature->dh_link_feature_mgr_id = array('und' => array( 0 => array('target_id' => $uid) ));
      $dh_feature->dh_geofield = $default_geofield;
      $dh_feature->save();
    }
    error_log("Setting geom for $record->hydroid to geom from $vid ");
    break;
    //$dh_adminreg_feature->save();
    echo "saved $record->name \n";
  }
  
?>
