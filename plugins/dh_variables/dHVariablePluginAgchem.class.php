<?php
module_load_include('inc', 'dh', 'plugins/dh.display');

class dHVariablePluginSimpleFertilizer extends dHVariablePluginDefault {
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
    list($n, $p, $k) = explode('-',$row->$codename);
    $vals = array(
      'n'=>$n,
      'p'=>$p,
      'k'=>$k,
    );
    $date_format = 'Y-m-d h:i';
    $rowform['tstime']['#date_format'] = $date_format;
    $rowform['tstime']['#default_value'] = empty($dh_timeseries->tstime) 
      ? date($date_format) 
      : date($date_format,$dh_timeseries->tstime)
    ;
    $rowform[$codename] = array(
      '#type' => 'agchem_npk',
    );
    $rowform[$codename]['#default_value'] = $row->$codename;
    //$rowform[$codename]['#input'] = TRUE;
    //$rowform[$codename]['#value'] = $vals;
    // @todo: understand how date makes a multi field form
      // date has:
        // date_select_element_value_callback - turns date pieces into properly formatted date
      // tutorial: https://www.drupal.org/node/169815
    $rowform[$codename]['#theme_wrappers'] = array('form_element');
    $rowform[$codename]['#process'] = array('om_agman_form_process_npk');
    // store these as children of propcode/tscode
    //om_agman_form_process_npk($rowform[$codename]);
    // store these as children of a new variable
    $hidden = array('pid', 'startdate', 'enddate', 'featureid', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $rowform[$hide_this]['#type'] = 'hidden';
    }
    dpm($rowform,'raw form');
  }
  
  public function formRowSave(&$rowvalues, &$row) {
    parent::formRowSave($rowvalues, $row);
    $codename = $this->row_map['code'];
    $row->$codename = implode('-', array($rowvalues['n'], $rowvalues['p'], $rowvalues['k']));
    // special save handlers
  }

}
?>