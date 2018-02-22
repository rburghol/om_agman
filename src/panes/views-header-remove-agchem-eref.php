<?php
global $user;
//dpm($user);
$params = drupal_get_query_parameters();
if ( isset($params['action']) ) {
  // Load entity tree for this link:
  // * Load the event associated with the event (passed in via the URL with the eventid)
  // * Load the first block associated with the event
  // * Load the facility associated with the block
  // Check permissions
  // * Check user perms on the facility
  $args = arg();
  //dpm($args);
  $facid = $args[1];
  $has_perms = FALSE;
  $admins = dh_get_user_mgr_features($user->uid);
  if (in_array($facid, $admins)) {
    $has_perms = TRUE;
  }
  // Handle delete
  // * Process delete or spit out an error and log to watchdog
  // * Read the adminid of event and adminid of chem to remove from URL
  $eventid = $params['eventid'];
  $chemid = $params['agchemid'];
  $action = $params['action'];
  
  if ($has_perms) {
    $eref_ids = array(); // to stash erefs to delete if requested
    $link_field = 'field_link_to_registered_agchem';
    $entity = new StdClass;
    $event = entity_load_single('dh_adminreg_feature', $eventid);
    $chem = entity_load_single('dh_adminreg_feature', $chemid);
    if (!$event) {
      drupal_set_message("Non-existent event entity id $eventid submitted.");
      return true;
    }
    if (!$chem) {
      drupal_set_message("Non-existent chem entity id $chemid submitted.");
      return true;
    }
    if (!property_exists($event, $link_field)) {
      drupal_set_message("Non-existent event entity id $argument submitted.");
      return true;
    }
    $value = array('target_id' => $chemid);
    $lang = array_shift(array_keys($event->{$link_field}));
    if (!$lang) {
      $lang = 'und';
    }
    $linked = false;
    foreach ($event->{$link_field}[$lang] as $key => $ref) {
      //dpm($ref,'ref');
      if ($ref['target_id'] == $chemid) {
        $linked = true;
        $linkey = $key;
        if($ref['erefid']) {
          $eref_ids[] = $ref['erefid'];
        }
      }
    }
    //dpm($eref_ids,'ids to delete');
    //drupal_set_message("Initial event links: " . print_r($event->{$link_field},1));
    switch ($action) {

      case 'addlink':
        if (!$linked) {
          $event->{$link_field}[$lang][] = $value;
          //entity_save('dh_adminreg_feature', $event);
          drupal_set_message("event $event->name linked to user ID $chemid");
        } else {
          drupal_set_message("event $event->name already linked to user ID $chemid");
        }
      break;
      
      case 'deletelink':
      if ($linked) {
        //dpm($event->{$link_field}[$lang][$linkey],'removing chems');
        unset($event->{$link_field}[$lang][$linkey]);
        entity_save('dh_adminreg_feature', $event);
        drupal_set_message("$chem->name removed from $event->name on " . date('Y-m-d',$event->startdate));
      }
      break;
    }
    foreach ($eref_ids as $id) {
      //dsm("dh_entity_timeseries_delete(FALSE, $link_field, $id)");
      //dsm("dh_entity_property_delete(FALSE, $link_field, $id)");
      dh_entity_timeseries_delete(FALSE, $link_field, $id);
      dh_entity_property_delete(FALSE, $link_field, $id);
    }
  } else {
    drupal_set_message(t('You do not have permission to manage this location.'), 'error');
    //drupal_set_message(t('You are not permitted to modify this event.'), 'error');
  }
}
?>