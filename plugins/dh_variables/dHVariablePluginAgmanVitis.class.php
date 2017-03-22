<?php
module_load_include('inc', 'dh', 'plugins/dh.display');

// vitixultural ag management
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
    $codename = $this->row_map['code']['name'];
    $valname = $this->row_map['value']['name'];
    $stimename = $this->row_map['value']['start'];
    $etimename = $this->row_map['value']['end'];
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
?>