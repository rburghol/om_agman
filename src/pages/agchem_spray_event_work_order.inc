<?php
  // use this to call this file 
  // module_load_include('inc', 'om_agman', 'src/pages/agchem_spray_event_work_order');
  module_load_include('module', 'om_agman');
  module_load_include('module', 'dh');
  module_load_include('module', 'dh_adminreg');
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
    
    if ($feature->fstatus == 'canceled') {
      $pre = "<s>Note: This event has been marked as canceled. ";
      $suf = "</s>";
    }
    // which of this is correct? 'title' or 'markup'?
    $content['title']['#markup'] = "<h3><center>$pre" . date('Y-m-d', $feature->startdate) . ": " . $title . "$suf</center></h3>";
    //$content['title']['#title'] = "<h3><center>$pre" . date('Y-m-d', $feature->startdate) . ": " . $title . "$suf</center></h3>";
    $content['body'] = array(
      '#type' => 'item',
      '#markup' => $pre . '<b>Blocks:</b> ' . $feature->block_names,
    );
    // 
    $content['body']['#markup'] .= "<br><b>Volume:</b> " . $feature->agchem_spray_vol_gal->propvalue . " gals";
    $chem_list = "<ul><li>" . implode('</li><li>', $feature->chem_items) . "</li></ul>";
    $content['body']['#markup'] .= "<br><b>Materials:</b> $chem_list";
    //$content['body']['#markup'] .= "<br><b>Materials:</b> $feature->chem_list";
    $content['body']['#markup'] .= "<b>PHI:</b> $feature->phi_date ($feature->phi_chem)";
    $content['body']['#markup'] .= "<br><b>REI:</b> $feature->rei_date ($feature->rei_chem)" . $suf;

    $entity->title = $title;
    $content['modified']['#markup'] = '(modified on ' . date('Y-m-d', $feature->modified) . ")"; 
  }

?>