<?php
module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');
// make sure that we have base plugins 
$plugin_def = ctools_get_plugins('dh', 'dh_variables', 'dHVariablePluginAgmanAction');
$class = ctools_plugin_get_class($plugin_def, 'handler');

class dHAgmanAreaMultiDim extends dHVariablePluginDefaultOM {
  // Multi-dimensional area (procode)
  public function getUnitList() {
    return array(
      'ac' => 'acres',
      'ha' => 'hectares',
      'sqmi' => 'mi^2',
      'sqkm' => 'km^2',
    );
  }
  
  public function getFactors() {
    $factors = array(
      'ac' => array(
        'ha' => 0.404686,
        'sqmi' => 0.0015625,
        'sqkm' => 0.00404686,
      ),
    );
    return $factors;
  }
  
  public function convertArea($area, $from, $to) {
    $fact = $this->findFactor($from, $to);
    //dsm(" $from, $to ($fact * $area)");
    return ($fact * $area);
  }
  
  public function findFactor($from, $to) {
    $factors = $this->getFactors();
    if (isset($factors[$from])) {
      if (isset($factors[$from][$to])) {
        return $factors[$from][$to];
      }
    } else {
      if (isset($factors[$to])) {
        // a reverse?
        if (isset($factors[$to][$from])) {
          return 1.0 / $factors[$to][$from];
        }
      }
    }
    return FALSE;

  }
  
}

class dHVariablePluginAppRates extends dHVariablePluginDefaultOM {
  // @todo:
  public function hiddenFields() {
    return array('pid', 'startdate', 'enddate', 'featureid', 'entity_type', 'bundle', 'varunits');
  }
  
  public function amountUnits() {
    return array(
      'floz' => 'fluid oz',
      'gals' => 'gals',
      'qt' => 'quarts',
      'pt' => 'pt',
      'oz' => 'oz',
      'lbs' => 'lbs',
      'g' => 'grams',
      'mg' => 'mg',
    );
  }
  
  public function rateUnits() {
    return array(
      'floz/acre' => 'fluid oz/acre',
      'gals/acre' => 'gals/acre',
      'oz/acre' => 'oz/acre',
      'qt/acre' => 'quarts/acre',
      'tbsp/acre' => 'tbsp/acre',
      'pt/acre' => 'pints/acre',
      'lbs/acre' => 'lbs/acre',
      'oz/gal' => 'oz/gal',
      'floz/gal' => 'fluid oz/gal',
      'pt/cgal' => 'pint/100 gal',
      'lbs/gal' => 'lbs/gal',
    );
  }
  
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform[$this->row_map['code']] = (!$rowform[$this->row_map['code']]) ? array() : $rowform[$this->row_map['code']];
    $rowform[$this->row_map['code']]['#type'] = 'select';
    $rowform[$this->row_map['code']]['#options'] = $this->rateUnits();
    $rowform[$this->row_map['code']]['#size'] = 1;
    // @todo: figure this visibility into one single place
    // thse should automatically be hidden by the optionDefaults setting but for some reason...
    
  }
  
}
class dHVariablePluginAppRatesNonD extends dHVariablePluginDefaultOM {
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
    $rowform[$this->row_map['code']]['#type'] = 'select';
    $rowform[$this->row_map['code']]['#options'] = $this->amountUnits();
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
  
  var $units_varkey = 'agchem_amount_type';
  
  public function hiddenFields() {
    return array('propname', 'pid', 'enddate', 'featureid', 'entity_type', 'bundle', 'varunits');
  }
  

  public function getDefaultUnits($entity) {
    // load the units for the target adminreg chemical registration
    $vars = dh_varkey2varid($this->units_varkey);
    $varid = array_shift($vars);
    $prop_info = array(
      'featureid' => $entity->target_id,
      'entity_type' => 'dh_adminreg_feature',
      'bundle' => 'dh_properties',
      'varid' => $varid,
    );
    //$units = isset($varinfo['varunits'
    $prop = dh_get_properties($prop_info, 'singular');
    //dpm($prop,'prop');
    if ($prop) {
      $pptr = array_shift($prop['dh_properties']);
      $prec = entity_load_single('dh_properties', $pptr->pid);
      //dpm($prec,'prec');
      return $prec->propcode;
    } else {
      return FALSE;
    }
  }
  
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    //dpm($rowform,'rowform');
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform['varname']['#markup'] = $row->target_label;
    $rowform[$this->row_map['code']] = (!$rowform[$this->row_map['code']]) ? array() : $rowform[$this->row_map['code']];
    // @todo: what is this?  I think it is a remnant
    $tos = field_get_items($type, $entity, $map['value']);
    foreach ($froms as $fr) {
      $value[] = $fr['target_id'];
    }
    $rowform[$this->row_map['code']]['#type'] = 'select';
    $rowform[$this->row_map['code']]['#options'] = $this->amountUnits();
    $rowform[$this->row_map['code']]['#size'] = 1;
    $rowform[$this->row_map['code']]['#title'] = '';
    $rowform[$this->row_map['code']]['#default_value'] = strlen($rowform[$this->row_map['code']]['#default_value']) ? $rowform[$this->row_map['code']]['#default_value'] : $this->getDefaultUnits($row);
    $rowform['startdate']['#type'] = 'date_popup';
    $date_format = 'Y-m-d';
    $rowform['startdate']['#default_value'] = empty($row->startdate) ? date('Y-m-d') : date($date_format,$row->startdate);
    // @todo: figure this visibility into one single place
    // these should automatically be hidden by the optionDefaults setting but for some reason...
    $this->hideFormRowEditFields($rowform);
    //dpm($rowform,'rowform');
    
  }
}

class dHVariablePluginInventoryEvent extends dHVariablePluginAgmanAction {
  // @todo:
  
  public function hiddenFields() {
    return array('tid', 'enddate', 'featureid', 'entity_type', 'bundle', 'varunits', 'tsvalue', 'tscode');
  }
  
  public function formRowEdit(&$rowform, $entity) {
    
    $rowform['startdate']['#title'] = t('Date Inventory Taken');
    $rowform['startdate']['#type'] = 'date_popup';
    $date_format = 'Y-m-d';
    $rowform['startdate']['#default_value'] = empty($entity->startdate) ? date('Y-m-d') : date($date_format,$entity->startdate);
    $rowform['startdate']['#date_format'] = $date_format;
    $this->hideFormRowEditFields($rowform);
  }
  
  public function save(&$entity) {
    // add a ts event for this if this is a property
    // we must avoid doing this if it is a TS because we would create an endless recursion
    // @todo: move this to base class or module code
    // time resolutions:
    //   singular - only one ts event ever for this feature/varid combo
    //   tstime_singular - only onets event for this feature/varid/tstime combo
    $feature = $this->getParentEntity($entity);
    if ($entity->entityType() == 'dh_properties') {
      $ts_rec = array(
        'varid' => $entity->varid,
        'tsvalue' => NULL,
        'tscode' => 'inventory_completed',
        //'tstime' => mktime(),
        'tstime' => $entity->startdate,
        'featureid' => $entity->featureid,
        'entity_type' => $feature->entityType(),
      );
      dh_update_timeseries($ts_rec, 'tstime_singular');
    }
  }

  public function buildContent(&$content, &$entity, $view_mode) {
    parent::buildContent($content, $entity, $view_mode);
    // special render handlers when using a content array
    // get all FRAC Codes associated with this entity
    $feature = $this->getParentEntity($entity);
    $content['body'] = array(
      '#type' => 'item',
      '#markup' => "AgChem Inventory done in " . $feature->name,
    );
  }
}

class dHVariablePluginAppRateUnits extends dHVariablePluginAppRates {
  // only used to select units, no values
  // @todo:
  
  public function hiddenFields() {
    $hidden = parent::hiddenFields();
    $hidden[] = 'propvalue';
    return $hidden;
  }
  
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform[$this->row_map['code']] = (!$rowform[$this->row_map['code']]) ? array() : $rowform[$this->row_map['code']];
    $rowform[$this->row_map['code']]['#type'] = 'select';
    $rowform[$this->row_map['code']]['#options'] = $this->rateUnits();
    $rowform[$this->row_map['code']]['#size'] = 1;
    $rowform['propvalue']['#type'] = 'hidden';

  }
}

class dHVariablePluginCanopyScaler extends dHVariablePluginDefaultOM {
  // used to scale spray amounts by growth stage
  // @todo:
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
    $hidden = array('propcode');
    foreach ($hidden as $hide_this) {
      $this->property_conf_default[$hide_this]['hidden'] = 1;
    }
  }
  
  public function scaler(&$form, $fieldname) {
    $scale_opts = array();
    for ($i = 5; $i <= 100; $i += 5) {
      $pct = $i/100;
      $scale_opts["$pct"] = "$i %";
    }
    $form[$fieldname] = (!$form[$fieldname]) ? array() : $form[$fieldname];
    $form[$fieldname]['#type'] = 'select';
    $form[$fieldname]['#options'] = $scale_opts;
    $form[$fieldname]['#size'] = 1;
  }
  
  public function formRowEdit(&$rowform, $row) {
    // apply custom settings here
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $this->scaler($rowform, $this->row_map['value']);
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


class dHVariablePluginAgmanSprayerRate extends dHVariablePluginDefault {
  
  public function hiddenFields() {
    $hidden = array('varid', 'pid', 'propcode', 'entity_type', 'featureid', 'startdate', 'enddate', 'modified', 'label');
    return $hidden;
  }
  
  public function formRowEdit(&$form, $entity) {
    //dpm($form,'form');
    parent::formRowEdit($form, $entity);
    //$form['propname']['#prefix'] = $this->varname;
    $form['propvalue']['#default_value'] = empty($entity->propvalue) ? 100 : $entity->propvalue;
    //dpm($form,'form');
  }
  
  public function entityDefaults(&$entity) {
    // note: the base class does NOT expect &$entity as an argument
    //  so the below does NOT work (even though there are plugins that *think* it works this way
    $entity->propvalue = 100.0;
    // Instead, this is the expected 
    // BUT this doesn't work in all cases either... this is too bad
    $this->entity_defaults['propvalue'] = 100.0;
  }
  
}

class dHVariablePluginAgmanSprayerVolume extends dHVariablePluginDefault {
  
  public function hiddenFields() {
    $hidden = array('varid', 'pid', 'propcode', 'entity_type', 'featureid', 'startdate', 'enddate', 'modified', 'label');
    return $hidden;
  }
  
  public function formRowEdit(&$form, $entity) {
    //dpm($form,'form');
    parent::formRowEdit($form, $entity);
    //$form['propname']['#prefix'] = $this->varname;
    $form['propvalue']['#default_value'] = empty($entity->propvalue) ? 250 : $entity->propvalue;
    //dpm($form,'form');
  }
}

?>