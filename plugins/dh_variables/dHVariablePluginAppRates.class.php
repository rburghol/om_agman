<?php
module_load_include('inc', 'dh', 'plugins/dh.display');

class dHVariablePluginAppRates extends dHVariablePluginDefault {
  // @todo:
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
  }
  public function optionDefaults($conf = array()) {
    parent::optionDefaults($conf);
    //dpm($this->hiddenFields(), 'hiding optionDefaults');
    foreach ($this->hiddenFields() as $hide_this) {
      $this->property_conf_default[$hide_this]['hidden'] = 1;
    }
  }
  public function hideFormRowEditFields(&$rowform) {
    //dpm($this->hiddenFields(), 'hiding hideFormRowEditFields');
    foreach ($this->hiddenFields() as $hide_this) {
      $rowform[$hide_this]['#type'] = 'hidden';
    }
  }
  
  public function hiddenFields() {
    return array('pid', 'startdate', 'enddate', 'featureid', 'entity_type', 'bundle', 'varunits');
  }
  
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform[$this->row_map['code']] = (!$rowform[$this->row_map['code']]) ? array() : $rowform[$this->row_map['code']];
    $ra_units = array(
      'floz/acre' => 'fluid oz/acre',
      'gals/acre' => 'gals/acre',
      'oz/acre' => 'oz/acre',
      'lbs/acre' => 'lbs/acre',
      'oz/gal' => 'oz/gal',
      'floz/gal' => 'fluid oz/gal',
      'lbs/gal' => 'lbs/gal',
    );
    $rowform[$this->row_map['code']]['#type'] = 'select';
    $rowform[$this->row_map['code']]['#options'] = $ra_units;
    $rowform[$this->row_map['code']]['#size'] = 1;
    // @todo: figure this visibility into one single place
    // thse should automatically be hidden by the optionDefaults setting but for some reason...
    $this->hideFormRowEditFields($rowform);
    
  }
  
}
class dHVariablePluginAppRatesNonD extends dHVariablePluginDefault {
  // @todo:
  
  public function hiddenFields() {
    return array('pid', 'startdate', 'enddate', 'featureid', 'entity_type', 'bundle', 'propcode');
  }
  
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    // this version of the plugin is non-dimensional
    // so not units, relies on another
    // @todo: figure this visibility into one single place
    // thse should automatically be hidden by the optionDefaults setting but for some reason...
    $this->hideFormRowEditFields($rowform);
    
  }
  
}

class dHVariablePluginAppAmounts extends dHVariablePluginAppRates {
  // @todo:
  
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform[$this->row_map['code']] = (!$rowform[$this->row_map['code']]) ? array() : $rowform[$this->row_map['code']];
    $ra_units = array(
      'floz' => 'fluid oz',
      'gals' => 'gals',
      'oz' => 'oz',
      'lbs' => 'lbs',
      'g' => 'grams',
      'mg' => 'mg',
    );
    $rowform[$this->row_map['code']]['#type'] = 'select';
    $rowform[$this->row_map['code']]['#options'] = $ra_units;
    $rowform[$this->row_map['code']]['#size'] = 1;
    // @todo: figure this visibility into one single place
    // thse should automatically be hidden by the optionDefaults setting but for some reason...
    $hidden = array('pid', 'propvalue','startdate', 'enddate', 'featureid', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $rowform[$hide_this]['#type'] = 'hidden';
    }
    
  }
}

class dHVariablePluginInventoryAmounts extends dHVariablePluginAppRates {
  // @todo:
  
  public function hiddenFields() {
    return array('propname', 'pid', 'enddate', 'featureid', 'entity_type', 'bundle', 'varunits');
  }
  
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform['varname']['#markup'] = $row->target_label;
    $rowform[$this->row_map['code']] = (!$rowform[$this->row_map['code']]) ? array() : $rowform[$this->row_map['code']];
    $ra_units = array(
      'floz' => 'fluid oz',
      'gals' => 'gals',
      'oz' => 'oz',
      'lbs' => 'lbs',
      'g' => 'grams',
      'mg' => 'mg',
    );
    $tos = field_get_items($type, $entity, $map['value']);
    foreach ($froms as $fr) {
      $value[] = $fr['target_id'];
    }
    $rowform[$this->row_map['code']]['#type'] = 'select';
    $rowform[$this->row_map['code']]['#options'] = $ra_units;
    $rowform[$this->row_map['code']]['#size'] = 1;
    $rowform[$this->row_map['code']]['#title'] = '';
    $rowform['startdate']['#type'] = 'date_popup';
    $date_format = 'Y-m-d';
    $rowform['startdate']['#default_value'] = empty($row->startdate) ? date('Y-m-d') : date($date_format,$row->startdate);
    // @todo: figure this visibility into one single place
    // thse should automatically be hidden by the optionDefaults setting but for some reason...
    $this->hideFormRowEditFields($rowform);
    
  }
}
class dHVariablePluginAppRateUnits extends dHVariablePluginDefault {
  // only used to select units, no values
  // @todo:
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
    $hidden = array('propvalue');
    foreach ($hidden as $hide_this) {
      $this->property_conf_default[$hide_this]['hidden'] = 1;
    }
  }
  
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform[$this->row_map['code']] = (!$rowform[$this->row_map['code']]) ? array() : $rowform[$this->row_map['code']];
    $ra_units = array(
      'floz/acre' => 'fluid oz/acre',
      'gals/acre' => 'gals/acre',
      'oz/acre' => 'oz/acre',
      'lbs/acre' => 'lbs/acre',
      'oz/gal' => 'oz/gal',
      'floz/gal' => 'fluid oz/gal',
      'lbs/gal' => 'lbs/gal',
    );
    $rowform[$this->row_map['code']]['#type'] = 'select';
    $rowform[$this->row_map['code']]['#options'] = $ra_units;
    $rowform[$this->row_map['code']]['#size'] = 1;
    // @todo: figure this visibility into one single place
    // thse should automatically be hidden by the optionDefaults setting but for some reason...
    $hidden = array('pid', 'propvalue','startdate', 'enddate', 'featureid', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $rowform[$hide_this]['#type'] = 'hidden';
    }
  }
}

class dHVariablePluginCanopyScaler extends dHVariablePluginDefault {
  // used to scale spray amounts by growth stage
  // @todo:
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
    $hidden = array('propcode');
    foreach ($hidden as $hide_this) {
      $this->property_conf_default[$hide_this]['hidden'] = 1;
    }
  }
  
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $valcol = $this->row_map['value'];
    $rowform[$valcol] = (!$rowform[$valcol]) ? array() : $rowform[$valcol];
    $scale_opts = array();
    for ($i = 5; $i <= 100; $i += 5) {
      $pct = $i/100;
      $scale_opts["$pct"] = "$i %";
    }
    $rowform[$valcol]['#type'] = 'select';
    $rowform[$valcol]['#options'] = $scale_opts;
    $rowform[$valcol]['#size'] = 1;
    // on change, update:
      // total spray volume = default_rate * canopy_frac
      // each rows recommended rates = low_rate * canopy_frac, hi_rate * canopy_frac (text only)
    // @todo: figure out how to automatically update the related when this changes
    //$rowform[$valcol]['#attribute']['onchange'][] = $rowform[$valcol]['#behaviors'] +  ;
    
    // @todo: figure this visibility into one single place
    // thse should automatically be hidden by the optionDefaults setting but for some reason...
    $hidden = array('pid', 'varunits', 'propcode','startdate', 'enddate', 'featureid', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $rowform[$hide_this]['#type'] = 'hidden';
    }
    
  }
  
  public function formRowSave(&$rowvalues, &$row) {
    // special form save handlers
    //$valcol = $this->row_map['value'];
    //$rowvalues[$valcol] = $rowvalues[$valcol]
  }
  
}
?>