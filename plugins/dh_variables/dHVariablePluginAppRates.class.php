<?php
module_load_include('inc', 'dh', 'plugins/dh.display');

class dHVariablePluginAppRates extends dHVariablePluginDefault {
  // @todo:
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
    $hidden = array('pid', 'startdate', 'enddate', 'featureid', 'entity_type', 'bundle');
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
    $hidden = array('pid', 'startdate', 'enddate', 'featureid', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $rowform[$hide_this]['#type'] = 'hidden';
    }
    
  }
  
}
class dHVariablePluginAppRatesNonD extends dHVariablePluginDefault {
  // @todo:
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
    $hidden = array('pid', 'startdate', 'enddate', 'featureid', 'entity_type', 'bundle', 'propcode');
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
    // this version of the plugin is non-dimensional
    // so not units, relies on another
    // @todo: figure this visibility into one single place
    // thse should automatically be hidden by the optionDefaults setting but for some reason...
    $hidden = array('pid', 'startdate', 'enddate', 'featureid', 'entity_type', 'bundle', 'propcode');
    foreach ($hidden as $hide_this) {
      $rowform[$hide_this]['#type'] = 'hidden';
    }
    
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
?>