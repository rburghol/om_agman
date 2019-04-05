<?php
  module_load_include('inc', 'dh', 'plugins/dh.display');
  $v = $view->args ;
  $uid = -1;
  $elist = "";
  $update_props = TRUE;
  dpm($v, "<br>View args: ");
  // sql to get records with redundant erefs
  $q = "  select hydroid from dh_feature ";
  $q .= " where bundle = 'landunit' ";
  if ($uid <> -1) {
    $q .= " and uid = $uid ";
  }
  if ($elist <> '') {
    $q .= " and hydroid in ($elist) ";
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
    // Load some entity.
    $feature = entity_load_single('dh_feature', $record->hydroid);
    $area_info = array(
      'varkey' => 'om_agman_area',
      'featureid' => $feature->hydroid,
      'entity_type' => 'dh_feature',
    );
    $area_prop = om_model_getSetProperty($area_info, 'varid');
    $plugin = dh_variables_getPlugins($area_prop);
    if ($area_prop->propvalue === NULL) {
      $area_prop->propvalue = round($plugin->convertArea($feature->dh_areasqkm['und'][0]['value'], 'sqkm', 'ac'),2);
    }
    $area_prop->save();
    $feature->dh_areasqkm['und'][0]['value'] = $plugin->convertArea($area_prop->propvalue, 'ac', 'sqkm');
    //dpm($feature,"Saving");
    $feature->save();
    echo "saved $record->adminid \n";
  }
  
?>
