<?php
  module_load_include('module', 'dh');
  module_load_include('inc', 'dh', 'plugins/dh.display');
  $adminid = 7237; // put a valid event to test here
  $entity_type = 'dh_adminreg_feature';
  $entity = entity_load_single($entity_type, $adminid);
  // clone event 
  $info = $entity->entityInfo();
  $values = array();
  $extras = array(); // if any that don't come through the entityInfo process that we need 
  $copyable = array_unique(array_merge($extras, array_values($info['property info'])));
  dpm($copyable,'copy field and props');
  foreach ($copyable as $pname) {
    if (isset($src_prop->{$pname})) {
      if ($pname <> 'propname') {
        $values[$pname] = $src_prop->{$pname};
      }
    }
  }
  $dest_entity = entity_create($entity->entityType(), $values);
  dpm($dest_entity, 'clone');
  // - must add a clone() method on the object
  // clone event props.  Easy enough with 
  // load subComponents 
  $propnames = dh_get_dh_propnames('dh_properties', $entity->identifier());
  foreach ($propnames as $propname) {
    om_copy_properties($entity, $dest_entity, $propname, $fields = TRUE, $defprops = FALSE, $allprops = FALSE);    
  }
  /*
  // clone entity reference 
  // for each eref field 
  // iterate through reference fields 
  // iterate through references 
  // source id changes to adminid of new feature 
  // all other parts of eref stays the same
  // Use same procedure as above "dh_get_dh_propnames()" + "om_copy_properties()" to replicate
  //    the properties on the referenced agchems 
  // Add method to save for agchem event, copy erefs to property om_map_model_linkage
      // if the target_type is set on the instance, use that, otherwise look at the field
  list(, , $bundle) = entity_extract_ids($entity_type, $entity);
  $finfo = field_info_instances($entity_type, $bundle);
  if (isset($finfo[$thisreftype]['settings']['target_type'])) {
    $ttype = $finfo[$thisreftype]['settings']['target_type'];
  } else {
    // @todo: should this be default, that is, should we never check the instance first?
    // not on the instance, so try field_info_field(field_info_field
    $finfof = field_info_field("$thisreftype");
    $ttype = $finfof['settings']['target_type'];
  }
  if (isset($refs['und']) and $ttype) {
    foreach ($refs['und'] as $ref) {
      $tid = $ref['target_id'];
      dh_perms_contact_perms($uid, $tid, $ttype, $entity_id_cache, $user_perm_cache, $max_depth);
    }
  } 
  */
?>
