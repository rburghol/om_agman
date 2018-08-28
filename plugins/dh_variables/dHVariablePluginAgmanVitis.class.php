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
  
  public function applyEntityAttribute($property, $value) {
    $property->propcode = $value;
  }
  
  public function getPropertyAttribute($property) {
    return $property->propcode;
  }
}

class dHVariablePluginNumericAttribute extends dHVariablePluginDefault {
  var $default_value = 0.0;
  var $default_code = '';
  
  public function hiddenFields() {
    return array('startdate','featureid','enddate','entity_type','propcode');
  }
  public function formRowEdit(&$rowform, $row) {
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform['propvalue'] = array(
      '#title' => t($varinfo->varname),
      '#type' => 'textfield',
      '#description' => $varinfo->vardesc,
      '#default_value' => !empty($row->propvalue) ? $row->propvalue : NULL,
    );
  }
  public function applyEntityAttribute($property, $value) {
    $property->propvalue = $value;
  }
  
  public function getPropertyAttribute($property) {
    return $property->propvalue;
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
    parent::formRowEdit($rowform, $row); // does hiding etc.
    $this->loadProperties($row);
    // apply custom settings here
    //dpm($row,'row');
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
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
    $rowform['featureid'] = array(
      '#title' => t('Location'),
      '#type' => 'select',
      '#options' => $options,
      '#size' => 1,
      '#weight' => -1,
      '#default_value' => $rowform['featureid']['#default_value'],
    );
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
  
  function rangeList($start, $end, $inc = 1, $scaler = 1) {
    // ex: 0 to 1.0 by 0.1, 
    $range_list = array();
    for ($i = $start; $i <= $end; $i += $inc) {
      $range_list["$i"] = $i;
    }
    return $range_list;
  }
  
  public function setUp(&$entity) {
    parent::setUp($entity);
    //$entity->propname = 'blankShell';
    //dpm($entity,"setUp");
    //$this->loadProperties($entity);
  }
  
  public function create(&$entity) {
    //$entity->propname = 'blankShell';
    //dpm($entity,'create(entity)');
    //$this->loadProperties($entity);
    parent::create();
  }
  
  public function insert(&$entity) {
    //$entity->propname = 'blankShell';
    //dpm($entity,'insert(entity)');
    $this->updateProperties($entity);
    parent::insert();
  }
  
  public function update(&$entity) {
    //$entity->propname = 'blankShell';
    //dpm($entity,'update(entity)');
    $this->updateProperties($entity);
    parent::update();
  }
  
  public function save(&$entity) {
    //$entity->propname = 'blankShell';
    //dpm($entity,'save(entity)');
    parent::save();
  }
  
  public function loadProperties(&$entity) {
    $props = $this->getDefaults($entity);
    //dpm($props,'props to loadProperties');
    foreach ($props as $thisvar) {
      $prop = dh_properties_enforce_singularity($thisvar, 'name');
      //dpm($prop,'prop to load');
      if (!$prop) {
        // prop does not exist, so need to create
        // @todo: manage this create the prop then pass defaults
        //$thisvar['featureid'] = $entity->tid;
        $prop = entity_create('dh_properties', $thisvar);
      }
      if (!$prop) {
        watchdog('om', 'Could not Add Properties in plugin loadProperties');
        return FALSE;
      }
      $entity->{$prop->propname} = $prop;
    }
    //dpm($entity,'props loaded');
  }
  
  public function updateProperties(&$entity) {
    // @todo: move this to the base plugin class 
    //dpm($entity,'updateProperties entity');
    $props = $this->getDefaults($entity);
    //dpm($props,'updateProperties');
    foreach ($props as $thisvar) {
      // load the property 
      // if a property with propname is set on $entity, send its value to the plugin 
      //   * plugin should be stored on the property object already
      // if prop on entity is an object already, handle directly, otherwise, load it
      //   the object method is advantageous because we can make things persist
      if (property_exists($entity, $thisvar['propname'])) {
        if (!is_object($entity->{$thisvar['propname']})) {
          // this has been set by the form API as a value 
          // so we need to load/create a property then set the value
          $prop = dh_properties_enforce_singularity($thisvar, 'name');
        } else {
          $prop = $entity->{$thisvar['propname']};
        }
      }
      //dpm($prop, "updating property $thisvar[propname]");
      if (is_object($prop)) {
        entity_save('dh_properties', $prop);
      }
    }
  }
}
class dHVariablePluginVitisCanopyMgmt extends dHVariablePluginAgmanAction {
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

class dHVariablePluginPercentSelector extends dHVariablePluginAgmanAction {
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
    parent::formRowEdit($rowform, $row); // does hiding etc.
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
  }
  public function hiddenFields() {
    return array('tid', 'varid', 'featureid', 'entity_type', 'bundle', 'tsendtime');
  }
}

class dHVariablePluginIPMIncident extends dHVariablePluginPercentSelector {
  
  public function incidentCodes() {
    return array(
//    'Disease' => array(
//      'org_botrytis' => 'Botrytis',
//      'org_black_rot' => 'Downy Mildew',
//      'hail' => 'Powdery Mildew',
//      'org_black_rot' => 'Black Rot',
//      'org_phomopsis' => 'Phomopsis',
//    ),
      'hail' => 'Hail',
      'insect_damage' => 'Insect Damage',
      'leaf_burn' => 'Leaf Burn',
    );
  }
  public function formRowEdit(&$rowform, $row) {
    parent::formRowEdit($rowform, $row); // does hiding etc.
    $pcts = $this->pct_list(array('<1', 5, 10, 20, 30, 40, 50, 60, 70, 80, 90, '>95'));
    $rowform['tsvalue']['#options'] = $pcts;
    $rowform['tsvalue']['#title'] = t('% of Plants Affected');
    $rowform['tscode']['#title'] = t('Incident Type');
    $rowform['tscode']['#type'] = 'select';
    $rowform['tscode']['#options'] = $this->incidentCodes();
    $rowform['tscode']['#size'] = 1;
  }
}

class dHVariablePluginVitisVeraison extends dHVariablePluginAgmanAction {
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
    return array('tid', 'varid', 'entity_type', 'bundle','tscode', 'tsvalue');
  }
  
  public function getDefaults($entity, &$defaults = array()) {
    parent::getDefaults($entity, $defaults);
    $defaults += array(
      'sample_weight_g' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'default_propvalue' => 0.0,
        'propname' => 'Sample Weight',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'sample_weight_g',
        'varid' => dh_varkey2varid('sample_weight_g', TRUE),
      ),
      'sample_size_berries' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'default_propvalue' => 0.0,
        'propname' => 'Berry Count',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'sample_size_berries',
        'varid' => dh_varkey2varid('sample_size_berries', TRUE),
      ),
      'brix' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'default_propvalue' => 0.0,
        'propname' => 'Brix',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'brix',
        'varid' => dh_varkey2varid('brix', TRUE),
      ),
      'ph' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'default_propvalue' => 3.5,
        'propname' => 'pH',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'ph',
        'varid' => dh_varkey2varid('ph', TRUE),
      ),
      'berry_weight_g' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'default_propvalue' => 0.0,
        'propname' => 'Berry Weight',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'berry_weight_g',
        'varid' => dh_varkey2varid('berry_weight_g', TRUE),
      ),
      'water_content_pct' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'default_propvalue' => 0.5,
        'propname' => 'Water Content',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'water_content_pct',
        'varid' => dh_varkey2varid('water_content_pct', TRUE),
      ), 
      'total_phenolics_aug' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'default_propvalue' => 0.0,
        'propname' => 'Total Phenolics',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'total_phenolics_aug',
        'varid' => dh_varkey2varid('total_phenolics_aug', TRUE),
      ),
      'total_anthocyanin_mgg' => array(
        'entity_type' => $entity->entityType(),
        'propcode' => NULL,
        'default_propvalue' => 0.0,
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
    parent::formRowEdit($rowform, $entity); // does location
    // apply custom settings here
    $varinfo = $entity->varid ? dh_vardef_info($entity->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $dopples = $this->getDefaults($entity);
    //dpm($dopples,'dopples');
    foreach ($dopples as $thisvar) {
      $pn = $this->handleFormPropname($thisvar['propname']);
      $dopple = $entity->{$thisvar['propname']};
      //dpm($dopple,'dopple before dh_update_properties = ' . $pn);
      // transition this over -- if this has dopples, load them, get values, save as prop then delete
      $replicant = $this->loadReplicant($entity, $thisvar['varkey']);
      if (!$replicant->is_new && !$dopple->pid) {
        // this is an existing dopple, transition to an attached property
        $thisvar['propvalue'] = $replicant->tsvalue;
        $thisvar['featureid'] = $entity->tid;
        dh_update_properties($thisvar, 'name');
      }
      // old handler used replicants instead of properties00
      $dopple_form = array();
      dh_variables_formRowPlugins($dopple_form, $dopple);
      $rowform[$pn] = $dopple_form['propvalue'];
      //dpm($rowform[$pn],"Adding $pn to form");
      // @todo: put this in a plugin, and have a method to add a for as single named attribute
      if ($thisvar['varkey'] == 'ph') {
      //dpm($dopple,'dopple = ' . $pn);
        $rowform[$pn]['#type'] = 'select';
        $rowform[$pn]['#options'] = array_merge(
          array(0 => 'NA'),
          $this->rangeList(3, 5, $inc = 0.01)
        );
      } 
    }
    //dpm($entity,'entity after formRowEdit');
    //dpm($rowform,'rowform');
  }
  
  public function formRowSave(&$rowvalues, &$entity) {
    parent::formRowSave($rowvalues, $entity);
    //dpm($rowvalues,'Saving Values');
    //dpm($entity,'Saving entity');
    // at this point, the form api has already set attributes on the entity equal to form values
    // but with names munged to replace spaces with underscores
    // this is at odds with our plugin framework which loads the attached dh_properties as entities
    // not values.  So, Brix and ph, since they are the same with underscores replaced conflict
    // special save handlers
    // so, now we call loadProperties() to insure that all properties are objects
    $this->loadProperties($entity);
    $entity->tsvalue = $rowvalues['Brix']; 
    $dopples = $this->getDefaults($entity);
    if (($rowvalues['Berry_Count'] > 0) and ($rowvalues['Sample_Weight'] > 0)) {
      // auto-calculate berry weight
      $rowvalues['Berry_Weight'] = round(floatval($rowvalues['Sample_Weight']) / floatval($rowvalues['Berry_Count']),3);
    }
    //dpm($dopples,'dopples');
    // check for transition from ts to prop
    foreach ($dopples as $thisvar) {
      $replicant = $this->loadReplicant($entity, $thisvar['varkey']);
      if (!$replicant->is_new) {
        entity_delete('dh_timeseries', $replicant->tid);
      }
      $pn = $this->handleFormPropname($thisvar['propname']);
      if (isset($rowvalues[$pn])) {
        if (property_exists($entity, $thisvar['propname']) and is_object($entity->{$thisvar['propname']})) {
          $prop = $entity->{$thisvar['propname']};
          foreach ($prop->dh_variables_plugins as $plugin) {
            if (method_exists($plugin, 'applyEntityAttribute')) {
              $plugin->applyEntityAttribute($prop, $rowvalues[$pn]);
            } else {
              if (is_numeric($rowvalues[$pn])) {
                $prop->propvalue = $rowvalues[$pn];
              } else {
                $prop->propcode = $rowvalues[$pn];
              }
            }
          }
        }
      }
    }
  }

}
?>