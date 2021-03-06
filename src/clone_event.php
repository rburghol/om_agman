<?php
  module_load_include('module', 'dh');
  module_load_include('inc', 'dh', 'plugins/dh.display');
  module_load_include('module', 'om_agman');
  
  function om_agman_event_clone_form($form, &$form_state, $dh_adminreg_feature, $farmid) {
    dpm($dh_adminreg_feature, 'element to clone');
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
    // grab copies of the fields from the original form
    $form_copy = $form;
    $form_state_copy = $form_state;
    field_attach_form('dh_adminreg_feature', $dh_adminreg_feature, $form_copy, $form_state_copy);
  
    $form['adminid'] = array(
      '#type' => 'hidden',
      '#default_value' => $dh_adminreg_feature->adminid,
    );
    $form['name'] = array(
      '#title' => t('Event Name'),
      '#type' => 'textfield',
      '#default_value' => empty($dh_adminreg_feature->name) ? 'New Event' : $dh_adminreg_feature->name . '(' . t('copy') . ')',
      '#description' => t('Name for new event'),
      '#required' => TRUE,
      '#size' => 30,
    );
    om_agman_form_block_select($form['dh_link_feature_submittal'], $farmid);
    if ($farmid == $src_farmid) {
      // use the same settings for Blocks
      $form['dh_link_feature_submittal'] = $form_copy['dh_link_feature_submittal'];
    }
    
    $form['description'] = $form_copy['description'];
    
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['save'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
      '#weight' => 40,
      '#submit' => array('om_agman_event_clone_form_save')
    );
    $form['actions']['save_edit'] = array(
      '#type' => 'submit',
      '#value' => t('Change Spray Details'),
      '#weight' => 40,
      '#submit' => array('om_agman_event_clone_form_save_and_edit')
    );
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
  
  function om_agman_event_clone_form_submit($form, $form_state) {
    $adminid = $form_state['values']['adminid'];
    $entity = entity_load_single($entity_type, $adminid);
  }
  
  function om_agman_event_clone_form_cancel($form, $form_state) {
    
  }
  
  function dh_adminreg_entity_clone($entity) {
    dpm($entity, 'src entity');
    // clone event 
    $info = $entity->entityInfo();
    dpm($info,'entity info');
    $values = array();
    $extras = array('bundle', 'name', 'startdate', 'enddate'); // if any that don't come through the entityInfo process that we need 
    //$extras = array();
    $prop_info = array_values($info['property info']);
    $prop_info = is_array($prop_info) ? $prop_info : array();
    $copyable = array_unique(array_merge($extras, $prop_info));
    dpm($copyable,'copy field and props');
    foreach ($copyable as $pname) {
      if (isset($entity->{$pname})) {
        if ($pname <> 'propname') {
          $values[$pname] = $entity->{$pname};
        }
      }
    }
    $dest_entity = entity_create($entity->entityType(), $values);
    dpm($dest_entity, 'clone');

    // clone entity reference 
    // for each eref field   
    /*
    // iterate through reference fields 
    // iterate through references 
    // all parts of eref stays the same
    // ** Maybe just have to unset the eref_id field ??
    
    // Use same procedure as above "dh_get_dh_propnames()" + "om_copy_properties()" to replicate
    //    the properties on the referenced agchems 
    // Add method to save for agchem event, copy erefs to property om_map_model_linkage
        // if the target_type is set on the instance, use that, otherwise look at the field
    */
    
    
    list(, , $bundle) = entity_extract_ids($entity_type, $entity);
    $finfo_all = field_info_instances($entity_type, $bundle);
    dpm($finfo_all,'all fields');
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
      //dpm($finfo,'finfo');
      if (!empty($ttype)) {
        $ref_props[$key] = array();
        $dest_entity->{$key} = $entity->{$key};
        $refs = &$dest_entity->{$key};
        if (isset($refs['und'])) {
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
    dpm($dest_entity,'after adjusting erefs');
    // reload - does this grab eref_id?
    $dest_entity = entity_load_single('dh_adminreg_feature', $dest_entity->identifier());
    // Now clone properties 
    // - must add a clone() method on the object
    // clone event props and entity reference props
    $propnames = dh_get_dh_propnames($entity->entityType(), $entity->identifier());
    foreach ($propnames as $propname) {
      om_copy_properties($entity, $dest_entity, $propname, TRUE, TRUE, TRUE);    
    }
    // clone entity reference properties 
    // also, stash these as property based model links, to facilitate future replacement of entity references
    foreach ($finfo_all as $key => $finfo) {
      if (isset($finfo['settings']['target_type'])) {
        $ttype = $finfo['settings']['target_type'];
      } else {
        // @todo: should this be default, that is, should we never check the instance first?
        // not on the instance, so try field_info_field(field_info_field
        $finfo = field_info_field("$key");
        $ttype = $finfo['settings']['target_type'];
      }
      //dpm($finfo,'finfo');
      if (!empty($ttype)) {
        error_log("Handling eref $key to $ttype");
        $refs = &$dest_entity->{$key};
        error_log("Ref field:" . print_r($refs,1));
        if (isset($refs['und'])) {
          foreach ($refs['und'] as $k => $ref) {
            if (isset($refs['und'][$k]['erefid'])) {
              $target_id = $refs['und'][$k]['target_id'];
              $src_eref = new erefEntity($key, $ref_props[$key][$target_id]['featureid']);
              $dest_eref = new erefEntity($key, $refs['und'][$k]['erefid']);
              error_log("Copying props from " . $src_eref->entityType() . ' => ' . $src_eref->identifier());
              $propnames = dh_get_dh_propnames($src_eref->entityType(), $src_eref->identifier());
              error_log("Props: " . print_r($propnames,1));
              foreach ($propnames as $propname) {
                om_copy_properties($src_eref, $dest_eref, $propname, TRUE, TRUE, TRUE);    
              }
              // @todo: stash these as property based model links, to facilitate future replacement of entity references
              //  should probably put this as a function in the OM module
              /*
              $target_entity = entity_load_single('dh_adminreg_feature', $target_id);
              $refprop_info = array(
                'entity_type' => $dest_entity->entityType(),
                'propvalue' => $target_id,
                'propcode' => 'dh_adminreg_feature',
                'linktype' => 3,
                'propname' => $target_entity->name,
                'varkey' => 'om_map_model_linkage',
              );
              */
            }
          }
        } 
      }
    }
    // do a final save
    $dest_entity->save();
    return $dest_entity;
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
  $form_state['build_info']['args'] = array($dh_adminreg_feature, $farmid);

  // **********************
  // Load the form
  // **********************
  //$elements = drupal_get_form('dh_app_plan_form');
  $elements = drupal_build_form('om_agman_event_clone_form', $form_state);
  $form = drupal_render($elements);
  echo $form;
  
?>
