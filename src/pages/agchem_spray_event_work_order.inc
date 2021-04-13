<?php
  // use this to call this file 
  // module_load_include('inc', 'om_agman', 'src/pages/agchem_spray_event_work_order');
  module_load_include('module', 'om_agman');
  module_load_include('module', 'dh');
  module_load_include('module', 'dh_adminreg');
  
  //dpm($plugin,'plug');
  // get the arguments from URL, they come in the form farm_hydroid/sub_page_name/event_adminid
  $a = arg();
  $pix = ($a[0] == 'node') ? 2 : 3; // use 3 for the page, 2 for a node during development
  if (!isset($a[$pix])) {
     dsm( "There was a problem, event can not be found.");
  } else {
    $planid = $a[$pix];
    // load existing prop for editing
    $plan = entity_load_single('dh_adminreg_feature', $planid);
    if (!is_object($plan)) {
      dsm( "There was a problem, event can not be found.");
    }
    // load agchem event specific stuff
    ctools_include('plugins');
    $plugins = ctools_get_plugins('om', 'om_components');
    //dpm($plugins,'all plug');
    $plugin = ctools_get_plugins('om', 'om_components', 'ObjectModelAgmanSprayAppEvent');
    $class = ctools_plugin_get_class($plugin, 'handler');
    if ($class) {
      $config = array();
      $event_plugin = new $class($config);
      //dpm($event_plugin,'app plugin object');
      $event_plugin->dh_adminreg_feature = $plan;
      $form = array();
      $form_state = array();
      $event_plugin->buildForm($form, $form_state);
    }
    dpm($event_plugin,'event_plugin');
    dpm($plan,'plan');
    if ($plan->fstatus == 'canceled') {
      $pre = "<s>Note: This event has been marked as canceled. ";
      $suf = "</s>";
    }
    // which of this is correct? 'title' or 'markup'?

    // get ts event associated with this admin record 
    // Note: Eventually the adminreg record will NOT contain all of the application stuff, just a link to the timeseries record. 
    //       For now though, we need to work with both.
    //       But we use the TimeSeries event to load the adminreg event since that is what is presented in all of the 
    //       calendar and dashboard views, as it is easier to track only timeseries events temporally.
    $info = array('entity_type' => 'dh_adminreg_feature', 'featureid' => $planid, 'varkey' => 'agchem_application_event');
    $tsrecs = dh_get_timeseries($info, 'all');
    if (isset($tsrecs['dh_timeseries'])) {
      $rez1 = array_shift($tsrecs['dh_timeseries']);
      $ts = entity_load_single('dh_timeseries', $rez1->tid); 
      dpm($ts,'ts');
    }
    if (is_object($ts)) {
      $ts_plugin = dh_variables_getPlugins($ts);
      $content = array();
      $ts_plugin->buildContent($content, $ts, 'full');
      dpm($ts_plugin,'ts_plugin');
      dpm($content,'content');
      $output = drupal_render($content);
      echo $output;
    }
  }

?>