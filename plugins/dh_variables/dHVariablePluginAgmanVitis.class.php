<?php
module_load_include('inc', 'dh', 'plugins/dh.display');

// viticultural ag management
// canopy management = class dHVariablePluginVitisCanopyMgmt
//   pruning, hedging, tying, leaf pulling, training, shoot thinning
//  should show end time and mark as optional in case users want to track time spent


class dHVariablePluginCodeAttribute extends dHVariablePluginDefault {
  var $default_code = '';
  
  public function hiddenFields() {
    return array('tstime','featureid','tsendtime','entity_type','tsvalue');
  }
  public function formRowEdit(&$rowform, $row) {
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform['tscode'] = array(
      '#title' => t($varinfo->varname),
      '#type' => 'textfield',
      '#description' => $varinfo->vardesc,
      '#default_value' => !empty($row->tscode) ? $row->tscode : "0.0",
    );
  }
}
class dHVariablePluginNumericAttribute extends dHVariablePluginDefault {
  var $default_value = 0.0;
  var $default_code = '';
  
  public function hiddenFields() {
    return array('tstime','featureid','tsendtime','entity_type','tscode');
  }
  public function formRowEdit(&$rowform, $row) {
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform['tsvalue'] = array(
      '#title' => t($varinfo->varname),
      '#type' => 'textfield',
      '#description' => $varinfo->vardesc,
      '#default_value' => !empty($row->tsvalue) ? $row->tsvalue : "0.0",
    );
  }
}

class dHVariablePluginAgmanAction extends dHVariablePluginDefault {
  // provides location management standardization
  // and some common functions like pct_list() handling
  
  public function getDefaults($entity, &$defaults = array()) {
    //parent::getDefaults($entity, $defaults);
    // Example:
    /*
    $defaults += array(
      'berry_weight_g' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'propvalue' => 0.0,
        'propname' => 'Berry Weight',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'berry_weight_g',
        'varid' => dh_varkey2varid('berry_weight_g', TRUE),
      ),
    );
    */
    return $defaults;
  }
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    //dpm($row,'row');
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    foreach ($hidden as $hide_this) {
      $rowform[$hide_this]['#type'] = 'hidden';
    }
    // get facility
    $feature = $this->getParentEntity($row);
    if ($feature->bundle <> 'facility') {
      // this is a block, get the parent
      $facility = dh_getMpFacilityEntity($feature);
      $bundle = $feature->bundle;
      $ftype = $feature->ftype;
    } else {
      $facility = $feature;
      $bundle = 'landunit';
      $ftype = FALSE;
    }
    $options = dh_facility_tree_select($facility->hydroid, TRUE, $bundle, $ftype);
  }
    
  public function pct_list($inc = 10) {
    $pcts = array();
    if (is_array($inc)) {
      // we already have our list of percents, just work it out
      foreach ($inc as $i) {
        $dec = floatval(preg_replace('/\D/', '', $i)) / 100.0;
        $pcts["$dec"] = $i . " %";
      }
    } else {
      $i = $inc;
      while ($i <= 100) {
        $dec = floatval($i) / 100.0;
        $pcts["$dec"] = $i . " %";
        $i += $inc;
      }
    }
    return $pcts;
  }
  
}
class dHVariablePluginVitisCanopyMgmt extends dHVariablePluginDefault {
  // @todo: enable t() for varkey, for example, this is easy, but need to figure out how to 
  //        handle in views - maybe a setting in the filter or jumplists itself?
  //  default: agchem_apply_fert_ee
  //       fr: agchem_apply_fert_fr 
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
    $hidden = array('pid', 'startdate', 'enddate', 'featureid', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $this->property_conf_default[$hide_this]['hidden'] = 1;
    }
  }
  
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    //dpm($row,'row');
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $codename = $this->row_map['code'];
    $valname = $this->row_map['value'];
    $stimename = $this->row_map['start'];
    $etimename = $this->row_map['end'];
    $actions = array(
      'vitis_pruning_winter'=>'Dormant Pruning (all)',
      'vitis_pruning_winter_1st'=>'Dormant Pruning (1st)',
      'vitis_pruning_winter_2nd'=>'Dormant Pruning (2nd)',
      'vitis_pruning_hedging'=>'Hedging',
      'vitis_deleaf_fruitzone'=>'Leaf Pulling in Fruit Zone',
    );
    $rowform[$timename]['#weight'] = 0;
    $rowform[$codename] = array(
      '#title' => t('Activity'),
      '#type' => 'select',
      '#options' => $actions,
      '#weight' => 1,
    );
    $pcts = array();
    for ($i = 1; $i <= 20; $i++) {
      $dec = $i * 0.05;
      $pcts["$dec"] = $i * 5;
    }
    $rowform[$valname] = array(
      '#title' => t('% of block completed'),
      '#type' => 'select',
      '#options' => $pcts,
      '#weight' => 2,
      '#default_value' => !empty($row->$valname) ? $row->$valname : 1.0,
    );
    $rowform[$codename]['#default_value'] = $row->$codename;
    $hidden = array('pid', 'startdate', 'featureid', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $rowform[$hide_this]['#type'] = 'hidden';
    }
  }
  
  public function formRowSave(&$rowvalues, &$row) {
    parent::formRowSave($rowvalues, $row);
    $codename = $this->row_map['code']['name'];
    $row->$codename = implode('-', array($rowvalues['n'], $rowvalues['p'], $rowvalues['k']));
    // special save handlers
  }

}

class dHVariablePluginPercentSelector extends dHVariablePluginDefault {
  public function pct_list($inc = 10) {
    $pcts = array();
    if (is_array($inc)) {
      // we already have our list of percents, just work it out
      foreach ($inc as $i) {
        $dec = floatval(preg_replace('/\D/', '', $i)) / 100.0;
        $pcts["$dec"] = $i . " %";
      }
    } else {
      $i = $inc;
      while ($i <= 100) {
        $dec = floatval($i) / 100.0;
        $pcts["$dec"] = $i . " %";
        $i += $inc;
      }
    }
    return $pcts;
  }
  
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    //dpm($row,'row');
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform['tstime']['#type'] = 'date_popup';
    $pcts = $this->pct_list(array('<5', 25, 50, 75, 100));
    $rowform['tsvalue'] = array(
      '#title' => t($varinfo->varname),
      '#type' => 'select',
      '#options' => $pcts,
      '#weight' => 2,
      '#description' => $varinfo->vardesc,
      '#default_value' => !empty($row->tsvalue) ? $row->tsvalue : "0.5",
    );
    $rowform['actions']['submit']['#value'] = t('Save');
    $rowform['actions']['delete']['#value'] = t('Delete');
    $hidden = array('pid', 'startdate', 'featureid', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $rowform[$hide_this]['#type'] = 'hidden';
    }
  }
}

class dHVariablePluginVitisVeraison extends dHVariablePluginPercentSelector {
  // @todo: enable t() for varkey, for example, this is easy, but need to figure out how to 
  //        handle in views - maybe a setting in the filter or jumplists itself?
  //  default: agchem_apply_fert_ee
  //       fr: agchem_apply_fert_fr 
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
    $hidden = array('pid', 'startdate', 'enddate', 'entity_type', 'bundle', 'tscode');
    foreach ($hidden as $hide_this) {
      $this->property_conf_default[$hide_this]['hidden'] = 1;
    }
  }
  
  public function formRowEdit(&$rowform, $row) {
    // parent method handles location stuff
    parent::formRowEdit($rowform, $row);
    // apply custom settings here
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    
    $rowform['featureid'] = array(
      '#title' => t('Location'),
      '#type' => 'select',
      '#options' => $options,
      '#size' => 1,
      '#weight' => -1,
      '#default_value' => $rowform['featureid']['#default_value'],
    );
    $rowform['tstime']['#type'] = 'date_popup';
    $pcts = $this->pct_list(array('<5', 25, 50, 75, 100));
    $rowform['tsvalue'] = array(
      '#title' => t('% Veraison'),
      '#type' => 'select',
      '#options' => $pcts,
      '#weight' => 2,
      '#default_value' => !empty($row->tsvalue) ? $row->tsvalue : "0.5",
    );
    $rowform['actions']['submit']['#value'] = t('Save');
    $rowform['actions']['delete']['#value'] = t('Delete');
    /*
    $hidden = array('pid', 'startdate', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $rowform[$hide_this]['#type'] = 'hidden';
    }
    */
  }
  
  public function formRowSave(&$rowvalues, &$row) {
    parent::formRowSave($rowvalues, $row);
    $codename = $this->row_map['code']['name'];
    $row->$codename = implode('-', array($rowvalues['n'], $rowvalues['p'], $rowvalues['k']));
    // special save handlers
  }

  public function buildContent(&$content, &$entity, $view_mode) {
    // special render handlers when using a content array
    // get all FRAC Codes associated with this entity
    $feature = $this->getParentEntity($entity);
    $hidden = array('varname', 'tstime', 'tid', 'tsvalue', 'tscode', 'entity_type', 'featureid', 'tsendtime', 'modified', 'label');
    foreach ($hidden as $col) {
      $content[$col]['#type'] = 'hidden';
    }
    $pct = ($entity->tsvalue <= 0.05) ? "<=5%" : round(100.0 * $entity->tsvalue) . '%';
    switch($view_mode) {
      default:
        //$content['title'] = array(
        //  '#type' => 'item',
        //  '#markup' => "Verasion @ $pct in " . $feature->name,
        //);
        $content['body'] = array(
          '#type' => 'item',
          '#markup' => "Veraison @ $pct in " . $feature->name,
        );
      break;
      case 'ical_summary':
        //$content['title'] = array(
        //  '#type' => 'item',
        //  '#markup' => "Verasion @ $pct in " . $feature->name,
        //);
        unset($content['title']['#type']);
        $content = array();
        $content['body'] = array(
          '#type' => 'item',
          '#markup' => "Verasion @ $pct in " . $feature->name,
        );
      break;
    }
  }
}

class dHVariablePluginVitisHarvest extends dHVariablePluginAgmanAction {

  public function hiddenFields() {
    return array('tid', 'featureid', 'entity_type', 'bundle', 'tscode');
  }
  
  public function formRowEdit(&$rowform, $row) {
    parent::formRowEdit($rowform, $row); // does hiding etc.
    // apply custom settings here
    //dpm($row,'row');
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $pcts = $this->pct_list(array(10,20,30,40,50, 60, 70, 80, 90, 100));
    $rowform['tstime']['#type'] = 'date_popup';
    $rowform['tstime']['#title'] = 'Beginning';
    $rowform['tstime']['#date_format'] = 'Y-m-d';
    $rowform['tsendtime']['#type'] = 'date_popup';
    $rowform['tsendtime']['#title'] = 'End';
    $rowform['tsendtime']['#date_format'] = 'Y-m-d';
    $rowform['tsvalue'] = array(
      '#title' => t('% Harvested'),
      '#type' => 'select',
      '#options' => $pcts,
      '#weight' => 2,
      '#default_value' => !empty($row->tsvalue) ? $row->tsvalue : "1",
    );
    $rowform['actions']['submit']['#value'] = t('Save');
    $rowform['actions']['delete']['#value'] = t('Delete');
  }

}

class dHVariablePluginVitisBudBreak extends dHVariablePluginAgmanAction {
  // @todo: ba
  // Function:
  //  * Creates a paired set of TS events, linked by featureid and date 
  //    1. bud break incidence % (of vines in block)
  //    2. bud break extent % (median of buds per vine) 
  var $extent_varkey = 'vitis_bud_break_extent';
  var $incidence_varkey = 'vitis_bud_break';
  var $save_method = 'form_entity_map';
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
    $hidden = array('tid', 'featureid', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $this->property_conf_default[$hide_this]['hidden'] = 1;
    }
  }
  
  public function alterData(&$row) {
    // do the parent first which loads the incidence var
    // then use an EFQ to get the extent var
    $varid = array_shift(dh_varkey2varid($this->extent_varkey));
    $stimename = $this->row_map['start'];
    if (!$row->entity_type) {
      return FALSE;
    } else {
      $ext_info = array(
        'featureid' => $row->featureid,
        'entity_type' => $row->entity_type,
        'bundle' => 'dh_timeseries',
        'varid' => $varid,
        'tstime' => $row->tstime,
      );
      // 'tstime_singular' forces this entity to have only 1 value per feaureid/varid/tstime
      //dpm($ext_info,' dh_get_timeseries ext_info');
      $ext_tsrec = dh_get_timeseries($ext_info, 'tstime_singular');
      //dpm($this,'this at alterData');
      //dpm($ext_tsrec,' dh_get_timeseries returned');
      if ($ext_tsrec) {
        $rec = array_shift($ext_tsrec['dh_timeseries']);
        $ext_ts = entity_load_single('dh_timeseries', $rec->tid);
      } else {
        $ext_ts = new StdClass;
        $ext_ts->tid = NULL;
        $ext_ts->varid = $varid;
        $ext_ts->tstime = $row->tstime;
        $ext_ts->featureid = $row->featureid;
        $ext_ts->entity_type = $row->entity_type;
        $ext_ts->bundle = 'dh_timeseries';
        $ext_ts->tscode = NULL;
        $ext_ts->tsvalue = NULL;
      }
      $row->ext_tid = $ext_ts->tid;
      $row->ext_varid = $ext_ts->varid;
      $row->ext_tstime = $ext_ts->tstime;
      $row->ext_featureid = $ext_ts->featureid;
      $row->ext_entity_type = $ext_ts->entity_type;
      $row->ext_bundle = $ext_ts->bundle;
      $row->ext_code = $ext_ts->tscode;
      $row->ext_value = $ext_ts->tsvalue;
    }
  }
  
  public function formRowEdit(&$rowform, $row) {
    parent::formRowEdit($rowform, $row); // does hiding etc.
    // apply custom settings here
    //dpm($row,'row');
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $codename = $this->row_map['code'];
    $valname = $this->row_map['value'];
    $stimename = $this->row_map['start'];
    $etimename = $this->row_map['end'];
    $rowform[$stimename]['#weight'] = 0;
    $scale_opts = array();
    $rowform[$codename]['#default_value'] = $row->$codename;
    for ($i = 5; $i <= 100; $i += 5) {
      $pct = $i/100;
      $scale_opts["$pct"] = ($i == 5) ? "<= $i%" : "$i %";
    }
    $rowform[$valname]['#type'] = 'select';
    $rowform[$valname]['#options'] = $scale_opts;
    $rowform[$valname]['#size'] = 1;
    $rowform[$valname] = array(
      '#title' => t('% of plants in bud break'),
      '#type' => 'select',
      '#options' => $scale_opts,
      '#weight' => 2,
      '#default_value' => !empty($row->$valname) ? $row->$valname : 1.0,
    );
    // now load the extent info
    $this->alterData($row);
    $rowform['ext_tid'] = array(
      '#type' => 'hidden',
      '#default_value' => $row->ext_tid,
    );
    $rowform['ext_featureid'] = array(
      '#type' => 'hidden',
      '#default_value' => $row->ext_featureid,
    );
    $rowform['ext_varid'] = array(
      '#type' => 'hidden',
      '#default_value' => $row->ext_varid,
    );
    $rowform['ext_bundle'] = array(
      '#type' => 'hidden',
      '#default_value' => $row->ext_bundle,
    );
    $rowform['ext_entity_type'] = array(
      '#type' => 'hidden',
      '#default_value' => $row->ext_entity_type,
    );
    $scale_opts = array();
    for ($i = 10; $i <= 100; $i += 10) {
      $pct = $i/100;
      $scale_opts["$pct"] = ($i == 10) ? "<= $i%" : "$i %";
    }
    $rowform['ext_value'] = array(
      '#title' => t('Median % of buds broken per plant'),
      '#type' => 'select',
      '#options' => $scale_opts,
      '#weight' => 2,
      '#default_value' => $row->ext_value,
      '#required' => TRUE,
    );
    $hidden = array('pid', 'tsendtime', 'featureid', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $rowform[$hide_this]['#type'] = 'hidden';
    }
    return;
  }

  public function formRowSave(&$rowvalues, &$row){
    // @todo - create stub for this in parent class
    // this saves the second part of this 
    $ext_rec = array(
      'tid' => $row->ext_tid,
      'varid' => $row->ext_varid,
      'tsvalue' => $row->ext_value,
      'tscode' => $row->ext_code,
      'tstime' => $row->tstime,
      'featureid' => $row->featureid,
      'entity_type' => $row->entity_type,
    );
    dh_update_timeseries($ext_rec, 'tstime_singular');
  }

}


class dHVariablePluginFruitChemSample extends dHVariablePluginAgmanAction {
  // @todo: enable t() for varkey, for example, this is easy, but need to figure out how to 
  //        handle in views - maybe a setting in the filter or jumplists itself?
  //  default: agchem_apply_fert_ee
  //       fr: agchem_apply_fert_fr 
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
  }
  public function hiddenFields() {
    return array('tid', 'featureid', 'entity_type', 'bundle','tscode', 'tsvalue');
  }
  
  public function getDefaults($entity, &$defaults = array()) {
    parent::getDefaults($entity, $defaults);
    $defaults += array(
      'berry_weight_g' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'propvalue' => 0.0,
        'propname' => 'Berry Weight',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'berry_weight_g',
        'varid' => dh_varkey2varid('berry_weight_g', TRUE),
      ),
      'brix' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'propvalue' => 0.0,
        'propname' => 'Brix',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'brix',
        'varid' => dh_varkey2varid('brix', TRUE),
      ),
      'ph' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'propvalue' => 3.5,
        'propname' => 'pH',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'ph',
        'varid' => dh_varkey2varid('ph', TRUE),
      ),
      'water_content_pct' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'propvalue' => 0.5,
        'propname' => 'Water Content',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'water_content_pct',
        'varid' => dh_varkey2varid('water_content_pct', TRUE),
      ), 
      'total_phenolics_aug' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'propvalue' => 0.0,
        'propname' => 'Total Phenolics',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'total_phenolics_aug',
        'varid' => dh_varkey2varid('total_phenolics_aug', TRUE),
      ),
      'total_anthocyanin_mgg' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'propvalue' => 0.0,
        'propname' => 'Total anthocyanin',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'total_anthocyanin_mgg',
        'varid' => dh_varkey2varid('total_anthocyanin_mgg', TRUE),
      ),
    );
    return $defaults;
  }
  
  public function formRowEdit(&$rowform, $entity) {
    parent::formRowEdit($rowform, $row); // does location
    // apply custom settings here
    //dpm($entity,'entity');
    $varinfo = $entity->varid ? dh_vardef_info($entity->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $this->hideFormRowEditFields($rowform);
    $dopples = $this->getDefaults($entity);
    foreach ($dopples as $thisvar) {
      $dopple = $this->loadReplicant($entity, $thisvar['varkey']);
      $dopple_form = array();
      //dpm($dopple,'dopple = ' . $thisvar['varkey']);
      dh_variables_formRowPlugins($dopple_form, $dopple);
      $rowform[$dopple->varkey] = $dopple_form['tsvalue'];
    }
  }
  
  public function formRowSave(&$rowvalues, &$entity) {
    parent::formRowSave($rowvalues, $entity);
    //dpm($rowvalues,'Saving Row');
    //dpm($entity,'Saving entity');
    // special save handlers
    $entity->tsvalue = $rowvalues['brix']; 
    $dopples = $this->getDefaults($entity);
    //dpm($dopples,'dopples');
    foreach ($dopples as $thisvar) {
      $dopple = $this->loadReplicant($entity, $thisvar['varkey']);
      //dpm($dopple,'dopple = ' . $thisvar['varkey']);
      $dopple->tsvalue = $rowvalues[$thisvar['varkey']];
      entity_save('dh_timeseries', $dopple);
    }
  }
  
  public function loadReplicant(&$entity, $varkey, $exclude_cached = FALSE, $repl_bundle = FALSE) {
    // to prevent infinite loops of accidentally recursive replicants 
    // we need some protections:
    //   $exclude_cached - if it was retrieved from the cache it might be recursive

    if ($entity->entityType() == 'dh_properties') {
      $bundle = !$repl_bundle ? 'dh_properties' : $repl_bundle;
      $replicant_info = array(
        'featureid' => $entity->featureid,
        'entity_type' => $entity->entity_type,
        'bundle' => $bundle,
        'varkey' => $varkey,
      );
      $replicant_entity = dh_properties_enforce_singularity($replicant_info, 'singular');
    } else {
      // must be timeseries
      $replicant_info = array(
        'featureid' => $entity->featureid,
        'entity_type' => $entity->entity_type,
        'tstime' => $entity->tstime,
        'tsendtime' => $entity->tsendtime,
        'varkey' => $varkey,
      );
      $replicant_entity = dh_timeseries_enforce_singularity($replicant_info, 'tstime_singular');
    }
    if (!is_object($replicant_entity)) {
      $replicant_entity = entity_create($entity->entityType(), $replicant_info);
    }
    // check custody chain -- return false if a match exists indicating recursion
    if (in_array($entity, $entity->entity_chain)) {
      $replicant_entity = FALSE;
    } else {
      $entity->entity_chain[] = &$replicant_entity;
      $replicant_entity->entity_chain = $entity->entity_chain;
    }
    return $replicant_entity;
  }

}
?>