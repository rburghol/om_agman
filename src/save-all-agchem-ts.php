<?php
  module_load_include('inc', 'dh', 'plugins/dh.display');
  $uid = -1;
  //$elist = "745,471,435,655,628,629,623,429,432,570,571,569,568,566,547,536,535,534,533,532,531,530,529,519,518,516,515,514,513,511,512,510,495,494,454,453,452,451,310,331,391,393,299,295,382,378,376,328,375,363,362,361,360,359,343,287,342,450,446,358,341,233";
  $elist = "652";
  $update_props = TRUE;
  // sql to get records with redundant erefs
  $q = "  select tid from dh_timeseries ";
  $q .= " where varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_application_event') ";
  if (strlen($elist) > 0) {
    $q .= " and featureid in ($elist) ";
  }

  $result = db_query($q);
  echo $q;
  
  foreach ($result as $record) {
    // get events
    // Load some entity.
    $dh_ts = entity_load_single('dh_timeseries', $record->tid);
    entity_save($dh_ts);
    echo "saved $record->tid \n";
  }
  
?>
