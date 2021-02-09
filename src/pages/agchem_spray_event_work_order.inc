<?php
  // use this to call this file 
  // module_load_include('inc', 'om_agman', 'src/pages/agchem_spray_event_work_order');
  module_load_include('module', 'om_agman');
  module_load_include('module', 'dh');
  module_load_include('module', 'dh_adminreg');
  
  // load agchem event specific stuff
  ctools_include('plugins');
  $plugins = ctools_get_plugins('om', 'om_components');
  //dpm($plugins,'all plug');
  $plugin = ctools_get_plugins('om', 'om_components', 'ObjectModelAgmanSprayAppEvent');
  $class = ctools_plugin_get_class($plugin, 'handler');
  //dpm($plugin,'plug');
  // get the arguments from URL, they come in the form farm_hydroid/sub_page_name/event_adminid
  $a = arg();
  if (!isset($a[3])) {
     dsm( "There was a problem, event can not be found.");
  } else {
    $planid = $a[3];
    // load existing prop for editing
    $plan = entity_load_single('dh_adminreg_feature', $planid);
    if (!is_object($plan)) {
      dsm( "There was a problem, event can not be found.");
    }
    if ($plan->fstatus == 'canceled') {
      $pre = "<s>Note: This event has been marked as canceled. ";
      $suf = "</s>";
    }
    // which of this is correct? 'title' or 'markup'?

    // get ts event associated with this admin record 
    $info = array('entity_type' => 'dh_adminreg_feature', 'featureid' => $planid, 'varkey' => 'agchem_application_event');
    $tsrecs = dh_get_timeseries($info, 'all');
    if (isset($tsrecs['dh_timeseries'])) {
      $rez1 = array_shift($tsrecs['dh_timeseries']);
      $ts = entity_load_single('dh_timeseries', $rez1->tid); 
      dpm($ts,'ts');
    }
    if (is_object($ts)) {
      $plugin = dh_variables_getPlugins($ts);
      $content = array();
      $plugin->buildContent($content, $ts, 'full');
      $output = drupal_render($content);
      echo $output;
    }
  }

?>