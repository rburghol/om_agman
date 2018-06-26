<?php
module_load_include('inc', 'dh', 'plugins/dh.display');

// viticultural ag management
// canopy management = class dHVariablePluginVitisCanopyMgmt
//   pruning, hedging, tying, leaf pulling, training, shoot thinning
//  should show end time and mark as optional in case users want to track time spent

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
        $dec = floatval($i) / 100.0;
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

class dHVariablePluginVitisVeraison extends dHVariablePluginPercentSelector {
  // @todo: enable t() for varkey, for example, this is easy, but need to figure out how to 
  //        handle in views - maybe a setting in the filter or jumplists itself?
  //  default: agchem_apply_fert_ee
  //       fr: agchem_apply_fert_fr 
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
    $hidden = array('pid', 'startdate', 'enddate', 'featureid', 'entity_type', 'bundle', 'tscode');
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
    $pcts = $this->pct_list(array('<5', 25, 50, 75, 100));
    $rowform['tsvalue'] = array(
      '#title' => t('% veraison'),
      '#type' => 'select',
      '#options' => $pcts,
      '#weight' => 2,
      '#default_value' => !empty($row->tsvalue) ? $row->tsvalue : "0.5",
    );
    $rowform['actions']['submit']['#value'] = t('Save');
    $rowform['actions']['delete']['#value'] = t('Delete');
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

class dHVariablePluginVitisHarvest extends dHVariablePluginPercentSelector {

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

class dHVariablePluginVitisBudBreak extends dHVariablePluginDefault {
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


class dHVariablePluginFruitChemSample extends dHVariablePluginDefault {
  // @todo: enable t() for varkey, for example, this is easy, but need to figure out how to 
  //        handle in views - maybe a setting in the filter or jumplists itself?
  //  default: agchem_apply_fert_ee
  //       fr: agchem_apply_fert_fr 
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
  }
  public function hiddenFields() {
    return array('tid', 'featureid', 'entity_type', 'bundle','tscode');
  }
  
  
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    //dpm($row,'row');
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform['tsvalue']['#title'] = 'Avg. Brix:';
    $rowform['tsvalue']['#type'] = 'markup';
    $rowform['tsvalue']['#markup'] = $row->tsvalue;
    $rowform['tstext']['#title'] = t('Sample brix values (csv)');
    $this->hideFormRowEditFields($rowform);
  }
  
  public function formRowSave(&$rowvalues, &$row) {
    parent::formRowSave($rowvalues, $row);
    // special save handlers
    // later can have different tokens to allow brix, acidity, etc. but now just assume all csv brix
    $brix = explode(',', $row->tstext['und'][0]['value']);
    
    $row->tsvalue = round(array_sum($brix) / count($brix),1); 
  }

}
?>