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
      $newplan = $ts_plugin->getParentEntity($ts);
      $ts_plugin->load_event_info($newplan);
// data is all loaded into newplan variable -- load_event_info is the crucial line that does this
// $newplan->chem_details has the name, rate and units of each chem in an array
      dpm($newplan,'newplan');
      $content = array();
      $ts_plugin->buildContent($content, $ts, 'plugin');
      dpm($ts_plugin,'ts_plugin');
      dpm($content,'content');

      $event_title = $plan->name;
//      $event_startdate = new DateTime();
//      $event_startdate->setTimestamp($plan->startdate);
      $event_startdate = $plan->startdate;
      $event_startdate = date("m/d/Y", $event_startdate);
      $event_enddate = new DateTime();
      $event_enddate->setTimestamp($plan->enddate);
      $event_status = $plan->fstatus;
      $block_names = $newplan->block_names;
      $event_area = $plan->dh_properties['agchem_event_area']->propvalue;
      $event_spray_rate = $plan->dh_properties['agchem_total_spray_rate_galac']->propvalue;
      $spray_tank_size = $plan->dh_properties['agchem_batch_gal']->propvalue;
      $scale_volume = $plan->dh_properties['agchem_event_canopy_frac']->propvalue*100;
      $spray_volume = $plan->dh_properties['agchem_spray_vol_gal']->propvalue;
      $note = $newplan->description['und']['0']['value'];
//      $prop_list = [
//       '#theme' => 'item_list',
//       '#list_type' => 'ul',
//       '#items' => [
//        "<b> Title: </b>" . $event_title,
//        "<b> Application Start Date/Time: </b>" . $event_startdate->format('Y-m-d //H:i:s'),
//        "<b> Application End Date/Time: </b>" . $event_enddate->format('Y-m-d //H:i:s'),
//        "<b> Event Status: </b>" . ucfirst($event_status),
//        "<b> Blocks to Spray: </b>" . $block_names,
//        "<b> Volume to Spray (at full canopy): </b>" . $event_spray_rate . " //gals/acre",
//        "<b> Percent of full canopy (to scale spray and material volume): </b>" .  //$scale_volume . "%",
//        "<b> Total Spray Area: </b>" . $event_area . " acres",
//        "<b> Total Volume of Spray: </b>" . $spray_volume . " gallons",
//        "<b> Spray Tank Size: </b>" . $spray_tank_size . " gallons",
//        "<b> Note: </b>" . $note,
//       ],
//      ];
//      dpm($prop_list, 'prop_list');
//      $prop_output = drupal_render($prop_list);
//      echo $prop_output;
//

      $title = [
        '#markup' => "<h2><center>Farm Spray Work Order</center></h2> 
        <h3><center>Mount Alto Vineyards</center></h3>
        <p><br><b>&nbsp;&nbsp;Date: </b>$event_startdate<br>
        <b>&nbsp;&nbsp;Locations/Block(s): </b>$block_names<br></p>
        <p><b>&nbsp;&nbsp;Area to Spray: </b>$event_area acres<br>
        <b>&nbsp;&nbsp;Estimate Total Volume to Spray: </b>$spray_volume gallons (at $event_spray_rate gals/acre)<br>
        <b>&nbsp;&nbsp;Growth Stage %: </b>$scale_volume%<br></p>
        <p><b>&nbsp;&nbsp;Amounts/Quantities to Spray: </b></p>",
      ];
      dpm($title, 'title');
      $title_output = drupal_render($title);
      echo $title_output;

      $chem_details = $newplan->chem_details;
//      $chem_table = array(
//       '#theme' => 'table',
//       '#header' => array('Agrichemical Name', 'Amount', 'Units'),
//       '#rows'  => $newplan->chem_details,
//      );
//     dpm($chem_table, 'chem_table');
//     $output = drupal_render($chem_table);
//     echo $output;

//     $chem_details_chosen = $newplan->chem_details;
//     for ($x = 0; $x <= count($newplan->chem_details)-1; $x++) {
//      $chem_details_chosen[$x]['units'] = $chem_details_chosen[$x]['units'] . //"/acre";
//     }
//     $chem_table_chosen = array(
//      '#theme' => 'table',
//      '#header' => array('Agrichemical Name', 'Chosen Amount', 'Units'),
//      '#rows'  => $chem_details_chosen,
//     );
//     dpm($chem_table_chosen, 'chem_table_chosen');
//     $output_chosen = drupal_render($chem_table_chosen);
//     echo $output_chosen;

//     $chem_details_total = clone $chem_details;
     $chem_details_total = $chem_details;
     for ($x = 0; $x <= count($newplan->chem_details)-1; $x++) {
      $chem_details_total[$x]['rate'] = $chem_details_total[$x]['rate']*$event_area;
     }
     $chem_table_total = array(
      '#theme' => 'table',
      '#header' => array('Agrichemical Name', 'Total Amount', 'Units'),
      '#rows'  => $chem_details_total,
     );
     dpm($chem_table_total, 'chem_table_total');
     $output_total = drupal_render($chem_table_total);
     echo $output_total;

     $chem_details_ten_gal = $newplan->chem_details;
     for ($x = 0; $x <= count($newplan->chem_details)-1; $x++) {
      $chem_details_ten_gal[$x]['rate'] = $chem_details_ten_gal[$x]['rate']*10/$spray_volume;
//      $chem_details_ten_gal[$x]['units'] = $chem_details_ten_gal[$x]['units'] . "/10 //gallons";
     }
     $chem_table_ten_gal = array(
      '#theme' => 'table',
      '#header' => array('Agrichemical Name', 'Amount (per 10 gals)', 'Units (per 10 gals)'),
      '#rows'  => $chem_details_ten_gal,
     );
     dpm($chem_table_ten_gal, 'chem_table_ten_gal');
     $output_ten_gal = drupal_render($chem_table_ten_gal);
     echo $output_ten_gal;

     $chem_details_hun_gal = $newplan->chem_details;
     for ($x = 0; $x <= count($newplan->chem_details)-1; $x++) {
      $chem_details_hun_gal[$x]['rate'] = $chem_details_hun_gal[$x]['rate']*10;
     }
     $chem_table_hun_gal = array(
      '#theme' => 'table',
      '#header' => array('Agrichemical Name', 'Amount (per 100 gals)', 'Units (per 100 gals)'),
      '#rows'  => $chem_details_hun_gal,
     );
     dpm($chem_table_hun_gal, 'chem_table_hun_gal');
     $output_hun_gal = drupal_render($chem_table_hun_gal);
     echo $output_hun_gal;
    }
  }

      $note = [
        '#markup' => "<p><b>&nbsp;&nbsp;Note: </b><hr>&nbsp;&nbsp;$note<br><hr></p>",
      ];
      dpm($note, 'note');
      $note_output = drupal_render($note);
      echo $note_output;

?>