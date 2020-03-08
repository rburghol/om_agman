<?php
  module_load_include('module', 'dh');
  module_load_include('inc', 'dh', 'plugins/dh.display');
  
  function om_agman_event_clone_form($form, &$form_state, $dh_adminreg_feature, $farmid, $src_entity_id) {
    //dpm($dh_adminreg_feature, 'element to clone');
    /*
    // TEST
    ctools_include('plugins');
    $plugins = ctools_get_plugins('om', 'om_components');
    //dpm($plugins,'all plug');
    $plugin = ctools_get_plugins('om', 'om_components', 'ObjectModelAgmanSprayAppEvent');
    $class = ctools_plugin_get_class($plugin, 'handler');
    //dpm($plugin,'plug');
    $config = array();
    if ($class) {
      $src = new $class($config);
      //dpm($src,'app plugin object');
      $src->dh_adminreg_feature = $dh_adminreg_feature;
      $src->eventPropDefaultConf();
      $src_farm = $src->dh_farm_feature;
      $src_farmid = $src_farm->hydroid;
    } else {
      drupal_set_message(t("Cannot find event to clone.  Returning."), 'error');
      return $form;
    }
    */
    // grab copies of the fields from the original form, but we don't want to show them all.
    $form_copy = $form;
    $form_state_copy = $form_state;
    field_attach_form('dh_adminreg_feature', $dh_adminreg_feature, $form_copy, $form_state_copy);
    // add copied fields 
    if (isset($form_copy['ftype'])) {
      $form['ftype'] = $form_copy['ftype'];
    }
  
    $form['adminid'] = array(
      '#type' => 'hidden',
      '#default_value' => $dh_adminreg_feature->adminid,
    );
    $form['src_entity_id'] = array(
      '#type' => 'hidden',
      '#default_value' => $src_entity_id,
    );
    $form['name'] = array(
      '#title' => t('Event Name'),
      '#type' => 'textfield',
      '#default_value' => empty($dh_adminreg_feature->name) ? 'New Event' : $dh_adminreg_feature->name . '(' . t('copy') . ')',
      '#description' => t('Name for new event'),
      '#required' => TRUE,
      '#size' => 30,
    );
    $form['fstatus'] = array(
      '#title' => t('Status'),
      '#type' => 'select',
      '#options' => array(
        'planned' => t('Planned'),
        'completed' => t('Completed'),
        'cancelled' => t('Cancelled'),
        'post_harvest' => t('Post-Harvest'),
      ),
      '#default_value' => empty($dh_adminreg_feature->fstatus) ? 'planned' : $dh_adminreg_feature->fstatus,
      '#required' => TRUE,
      '#multiple' => FALSE,
    );
    $date_format = 'Y-m-d';
    // should have code in here to guess based on the phase/or passed in from the URL
    $form['startdate'] = array(
      '#title' => t('Application Date'),
      '#description' => t('Planned date for this spray.'),
      '#required' => TRUE,
      '#default_value' => empty($dh_adminreg_feature->startdate) ? $dh_adminreg_feature->startdate : date($date_format,$dh_adminreg_feature->startdate),
      '#date_format' => $date_format,
      '#type' => 'date_select',
      '#date_year_range' => '-5:+5',
    );
    
    $dur_hours = ($dh_adminreg_feature->enddate > $dh_adminreg_feature->startdate) ? 
               ($dh_adminreg_feature->enddate - $dh_adminreg_feature->startdate) / 3600.0 : 4; // duration in hours
    $dur_hours = ($dur_hours > 16) ? 16.0 : ceil($dur_hours * 2) / 2;// round to next greatest half hour
    $drange = range(0.5, 16, 0.5);
    $form['duration'] = array(
      '#type' => 'select',
      '#default_value' => $dur_hours,
      '#multiple' => FALSE,
      '#options' => $drange,
      '#description' => 'Event duration in hours, for calculating re-entry intervals. Events lasting longer than 16 hours must be split into separate events.'
    );
    
    /*
    // TEST
    dpm($form,'form before checking changes');
    if ($farmid == $src_farmid) {
      // use the same settings for Blocks
      om_agman_form_block_select($form_copy['dh_link_feature_submittal'], $farmid);
      $form['dh_link_feature_submittal'] = $form_copy['dh_link_feature_submittal'];
    } else {
      // add a new one with no blocks selected
      om_agman_form_block_select($form['dh_link_feature_submittal'], $farmid);
    }
    */
    dpm($form,'form');
    
    $form['description'] = $form_copy['description'];
    
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['save'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
      '#weight' => 40,
      '#submit' => array('om_agman_event_clone_form_save')
    );
    /*
    $form['actions']['save_edit'] = array(
      '#type' => 'submit',
      '#value' => t('Change Spray Details'),
      '#weight' => 40,
      '#submit' => array('om_agman_event_clone_form_save_and_edit')
    );
    */
    $form['actions']['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#weight' => 45,
      '#limit_validation_errors' => array(),
      '#submit' => array('dh_app_plan_form_submit_cancel')
    );
    dpm($form,'form');
    return $form;
  }
  
  function om_agman_event_clone_form_save(&$form, &$form_state) {
    dpm($form, 'save form');
    dpm($form_state, 'save form_state');
    $adminid = $form_state['values']['src_entity_id'];
    // Load original 
    //$entity = entity_load_single('dh_adminreg_feature', $adminid);
    
    // OR, use form stuff 
    form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
    form_load_include($form_state, 'inc', 'dh', 'dh.admin');
    form_load_include($form_state, 'inc', 'dh_adminreg', 'dh_adminreg.admin');
    // load the object
    $entity = entity_ui_form_submit_build_entity($form, $form_state);
    dpm($entity,'src entity');
    // Create clone
    // apply selected blocks, title, and description.
    // then clone and save.
    // Extra entity properties if any that don't come through the entityInfo process that we need 
    // in the case of adminreg features it seems we have NONE come through?
    $extras = array('bundle', 'name', 'startdate', 'enddate', 'ftype', 'fstatus'); 
    $new_entity = dh_entity_clone($entity, $extras);
    // recalculate enddate 
    $new_entity->enddate = dh_handletimestamp($form_state['values']['startdate']) + 3600.0 * $form_state['values']['duration'];
    //dpm($new_entity,'new entity');
    $new_entity->save();
    // reload so we can set ientity ref erefid
    $new_entity = entity_load_single('dh_adminreg_feature', $new_entity->identifier());
    // swap entity references
    dh_copyFields($entity, $new_entity);
    $new_entity->save();
    dh_copyProps($entity, $new_entity);
    // Now, handle any changes in blocks that affect area and totals
    // load the plugin to handle this aggregate form
    $class = ctools_plugin_load_class('om', 'om_components', 'ObjectModelAgmanSprayAppEvent', 'handler');
    $src = new $class(array());
    $src->dh_adminreg_feature = $new_entity;
    // handle all the attached stuff using the SaveDataObjectsAsForm
    $dummy_form = array();
    $dummy_form_state = array();
    $dummy_form_state['values']['dh_link_feature_submittal'] = $form_state['dh_link_feature_submittal'];
    $src->BuildForm($dummy_form, $dummy_form_state);
    echo "Loaded $src->name ($record->adminid) area = " . $src->agchem_event_area . "\n";
    dpm($form_state,'form state');
    dpm($src->dh_adminreg_feature,'new event');
    foreach ($form_state['dh_link_feature_submittal'] as $block_link) {
      $src->dh_adminreg_feature->dh_link_feature_submittal['und'][] = array('target_id' => $block_link['target_id']);
    }
    $src->SaveDataObjectsAsForm();
    // Finally, save
    $new_entity->save();
    return;
  }
  
  function om_agman_event_clone_form_cancel($form, $form_state) {
    
  }
  
  function dh_entity_clone($entity, $extras = array()) {
    //dpm($entity, 'src entity');
    // clone event 
    $info = $entity->entityInfo();
    //dpm($info,'entity info');
    $values = array();
    $prop_info = array_values($info['property info']);
    $prop_info = is_array($prop_info) ? $prop_info : array();
    $copyable = array_unique(array_merge($extras, $prop_info));
    //dpm($copyable,'copy field and props');
    foreach ($copyable as $pname) {
      if (isset($entity->{$pname})) {
        if ($pname <> 'propname') {
          $values[$pname] = $entity->{$pname};
        }
      }
    }
    $dest_entity = entity_create($entity->entityType(), $values);
    return $dest_entity;
  }
  
  function dh_copyProps($entity, &$dest_entity) {
    // special handling of erefs that possess erefid, and can have properties attached.
    // reload in case this is a newly cloned dest_entity in order to insure erefs have erefid set
    if ($dest_entity->is_new) {
      $dest_entity = entity_load_single('dh_adminreg_feature', $dest_entity->identifier());
    }
    // Now clone properties 
    // - must add a clone() method on the object
    // clone event props and entity reference props
    $propnames = dh_get_dh_propnames($entity->entityType(), $entity->identifier());
    foreach ($propnames as $propname) {
      om_copy_properties($entity, $dest_entity, $propname, TRUE, TRUE, TRUE);    
    }
    return TRUE;
  }
  
  function dh_copyFields($entity, &$dest_entity) {
    list(, , $bundle) = entity_extract_ids($entity->entityType(), $entity);
    $finfo_all = field_info_instances($entity->entityType(), $bundle);
    $ref_props = array();
    foreach ($finfo_all as $key => $finfo) {
      if (isset($finfo['settings']['target_type'])) {
        $ttype = $finfo['settings']['target_type'];
      } else {
        // @todo: should this be default, that is, should we never check the instance first?
        // not on the instance, so try field_info_field(field_info_field
        $finfo = field_info_field("$key");
        $ttype = $finfo['settings']['target_type'];
      }
      $dest_entity->{$key} = $entity->{$key};
      //dpm($finfo,'finfo');
      if (!empty($ttype)) {
        $refs = &$dest_entity->{$key};
        if (isset($refs['und'])) {
          $ref_props[$key] = array();
          foreach ($refs['und'] as $k => $ref) {
            $target_id = $refs['und'][$k]['target_id'];
            if (isset($refs['und'][$k]['erefid'])) {
              $ref_props[$key][$target_id] = array(
                'entity_type' => $key,
                'propvalue' => $target_id,
                'featureid' => $refs['und'][$k]['erefid'],
              );
              unset($refs['und'][$k]['erefid']);
            }
          }
        } 
      }
    }
    $dest_entity->save();
    // save and reload
    $dest_entity = entity_load_single('dh_adminreg_feature', $dest_entity->identifier());
    //dpm($dest_entity,'post reload() entity');
    // special handling of erefs that possess erefid, and can have properties attached.
    foreach ($ref_props as $key => $src_refs) {
      //dpm($src_refs, "Handling eref $key");
      foreach ($dest_entity->{$key}['und'] as $tk => $ref) {
        if (isset($ref['erefid'])) {
          $target_id = $ref['target_id'];
          $src_eref = new erefEntity($key, $src_refs[$target_id]['featureid']);
          $dest_eref = new erefEntity($key, $ref['erefid']);
          //dpm($src_eref,'from eref');
          //dpm($dest_eref,'to eref');
          $propnames = dh_get_dh_propnames($src_eref->entityType(), $src_eref->identifier());
          //dpm($propnames, "Propnames ");
          foreach ($propnames as $propname) {
            $copy = om_copy_properties($src_eref, $dest_eref, $propname, TRUE, TRUE, TRUE);
            //dpm($copy,'copy');
          }
        }
      } 
    }
    return TRUE;
  }
     
  global $user;
  // params = ?ipm-live-events/farmid/clone/adminid/
  $a = arg();
  $parms = drupal_get_query_parameters();
  if (isset($a[1])) {
    $farmid = $a[1];
  }
  if (isset($a[3])) {
    $adminid = $a[3];
  }
  if (isset($parms['finaldest'])) {
    $finaldest = $parms['finaldest'];
  } else {
    $finaldest = 'ipm-home';
  }
  // validate here and require a valid farmid/hydroid
  if (!is_numeric($farmid)) {
    drupal_set_message("You must include a valid farm id or block id.", 'error');
    drupal_goto($finaldest);
  }
  // check permissions on farm 
  $user_farms = dh_get_user_mgr_features($user->uid, 'facility', 'vineyard');
  if (!in_array($farmid, $user_farms)) {
    // - if farmid is not one of users own, check to see if the event has 
    //   been marked as a template (property shared_template, propvalue=1)
    // otherwise return home
    drupal_set_message("You do not have permission to view Event $adminid.  If you believe that you have recieved this in error, please contact the system administrator.", 'error');
    drupal_goto($finaldest);
  }
  $entity_type = 'dh_adminreg_feature';
  // spoof class for entity reference 
  class erefEntity {
    var $entity_type;
    var $entity_id;
    public function __construct($entity_type, $entity_id) {
      $this->entity_type = $entity_type;
      $this->entity_id = $entity_id;
    }
    public function entityType() {
      return $this->entity_type;
    }
    public function identifier() {
      return $this->entity_id;
    }
  }
  
  $dh_adminreg_feature = entity_load_single($entity_type, $adminid);
  $form_state = array();
  $form_state['wrapper_callback'] = 'entity_ui_main_form_defaults';
  $form_state['entity_type'] = 'dh_adminreg_feature';
  $form_state['bundle'] = 'agchem_app';
  $redirect = implode("/",array($a[0],$a[1]));
  $form_state['redirect'] = $redirect;
  form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
  // set things before initial form_state build
  
  // BEGIN - New Stuff 
  // New Stuff - try to clone first 
  ctools_include('plugins');
  $plugins = ctools_get_plugins('om', 'om_components');
  $plugin = ctools_get_plugins('om', 'om_components', 'ObjectModelAgmanSprayAppEvent');
  $class = ctools_plugin_get_class($plugin, 'handler');
  $config = array();
  if ($class) {
    $src = new $class($config);
    //dpm($src,'app plugin object');
    $src->dh_adminreg_feature = $dh_adminreg_feature;
    $src->eventPropDefaultConf();
    $src_farm = $src->dh_farm_feature;
    $src_farmid = $src_farm->hydroid;
  }
  $extras = array('bundle', 'name', 'startdate', 'enddate', 'ftype', 'fstatus'); 
  $new_entity = dh_entity_clone($dh_adminreg_feature, $extras);
  if ($farmid <> $src_farmid) {
    // use the same settings for Blocks
    $new_entity->dh_link_feature_submittal = array('und'=>array());
  }
  $form_state['build_info']['args'] = array($new_entity, $farmid, $adminid);
  // END - New Stuff 
  // TEST
  //$form_state['build_info']['args'] = array($dh_adminreg_feature, $farmid);

  // **********************
  // Load the form
  // **********************
  //$elements = drupal_get_form('dh_app_plan_form');
  $elements = drupal_build_form('om_agman_event_clone_form', $form_state);
  $form = drupal_render($elements);
  echo $form;
  
?>
