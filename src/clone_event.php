<?php
  module_load_include('module', 'dh');
  module_load_include('inc', 'dh', 'plugins/dh.display');
  $adminid = 7237; // put a valid event to test here
  $entity_type = 'dh_adminreg_feature';
  $entity = entity_load_single($entity_type, $adminid);
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
  // - must add a clone() method on the object
  // clone event props.  Easy enough with 
  // load subComponents 
  /*
  $propnames = dh_get_dh_propnames('dh_properties', $entity->identifier());
  foreach ($propnames as $propname) {
    om_copy_properties($entity, $dest_entity, $propname, TRUE, TRUE, TRUE);    
  }
  */

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
      $dest_entity->{$key} = $entity->{$key};
      $refs = &$dest_entity->{$key};
      if (isset($refs['und'])) {
        foreach ($refs['und'] as $k => $ref) {
          //dpm($ref, 'ref');
          //$tid = $ref['target_id'];
          //dh_perms_contact_perms($uid, $tid, $ttype, $entity_id_cache, $user_perm_cache, $max_depth);
          if (isset($refs['und'][$k]['erefid'])) {
            unset($refs['und'][$k]['erefid']);
          }
        }
      } 
    }
  }
  $dest_entity->save();
  dpm($dest_entity,'after adjusting erefs');
  
?>
