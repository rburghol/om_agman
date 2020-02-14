<?php
  module_load_include('module', 'dh');
  module_load_include('inc', 'dh', 'plugins/dh.display');
  $adminid = 7237; // put a valid event to test here
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
            $refprops[$key][$target_id] = array(
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
?>
