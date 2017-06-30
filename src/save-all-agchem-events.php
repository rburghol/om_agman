<?php
  module_load_include('inc', 'dh', 'plugins/dh.display');
  $v = $view->args ;
  $uid = 343;
  dpm($v, "<br>View args: ");
  // sql to get records with redundant erefs
  $q = "  select adminid from dh_adminreg_feature ";
  $q .= " where bundle = 'agchem_app' ";
  if ($uid <> -1) {
    $q .= " and uid = $uid ";
  }
  $result = db_query($q);
  // If we want to do a single one uncomment these lines:
  /* 
  $result = array(
    0 => new STDClass,
  );
  $result[0]->adminid = 299;
  */
  foreach ($result as $record) {
    // get events
    // Load some entity.
    $dh_adminreg_feature = entity_load_single('dh_adminreg_feature', $record->adminid);
    // handle all the attached stuff with the form plugin, using the data array
    // load the plugin to handle this aggregate form
    $class = ctools_plugin_load_class('om', 'om_components', 'ObjectModelAgmanSprayAppEvent', 'handler');
    $src = new $class(array());
    $src->dh_adminreg_feature = $dh_adminreg_feature;
    // handle all the attached stuff using the SaveDataObjectsAsForm
    $form = array();
    $form_state = array();
    $src->BuildForm($form, $form_state);
    $src->SaveDataObjectsAsForm();
    //echo "Object" . print_r((array)$src->chemgrid,1) . "\n";
    //echo "Object" . print_r($form,1) . "\n";
    echo "saved $record->adminid \n";
  }
  
?>