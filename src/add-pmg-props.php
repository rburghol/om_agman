<?php
  module_load_include('inc', 'dh', 'plugins/dh.display');
  $uid = -1;
  //$elist = "745,471,435,655,628,629,623,429,432,570,571,569,568,566,547,536,535,534,533,532,531,530,529,519,518,516,515,514,513,511,512,510,495,494,454,453,452,451,310,331,391,393,299,295,382,378,376,328,375,363,362,361,360,359,343,287,342,450,446,358,341,233";
  $elist = "";
  $update_props = TRUE;
  // sql to get records with redundant erefs
  $q = "  select adminid from dh_adminreg_feature ";
  $q .= " where bundle = 'registration' and ftype in ('fungicide', 'herbicide', 'insecticide', 'other', 'fertilizer') ";
  if ($uid <> -1) {
    $q .= " and uid = $uid ";
  }
  if ($elist <> '') {
    $q .= " and adminid in ($elist) ";
  }
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
    $dh_adminreg_feature = entity_load_single('dh_adminreg_feature', $record->adminid);
    if ($update_props) {
      $ipm_vt_pmg_material = array(
        'varkey' => 'ipm_vt_pmg_material',
        'featureid' => $dh_adminreg_feature->adminid,
        'entity_type' => 'dh_adminreg_feature',
        'propcode' => '',
        'propname' => 'ipm_vt_pmg_material'
      );
      //error_log("Saving frac controller " . print_r($frac_group,1));
      om_model_getSetProperty($ipm_vt_pmg_material, 'name');
    }
    //$dh_adminreg_feature->save();
    echo "saved $record->name \n";
  }
  
?>
