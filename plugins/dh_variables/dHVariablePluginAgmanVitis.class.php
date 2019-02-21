<?php
module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');
// make sure that we have base plugins 
$plugin_def = ctools_get_plugins('dh', 'dh_variables', 'dHOMmodelElement');
$class = ctools_plugin_get_class($plugin_def, 'handler');

// viticultural ag management
// canopy management = class dHVariablePluginVitisCanopyMgmt
//   pruning, hedging, tying, leaf pulling, training, shoot thinning
//  should show end time and mark as optional in case users want to track time spent


class dHVariablePluginAgmanAction extends dHVariablePluginDefaultOM {
  // provides location management standardization
  // and some common functions like pct_list() handling
  // and content formatting
  public function hiddenFields() {
    return array('tid', 'varid', 'entity_type', 'bundle');
  }
  
  public function getDefaults($entity, &$defaults = array()) {
    //parent::getDefaults($entity, $defaults);
    // Example:
    /*
    $defaults += array(
      'berry_weight_g' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
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
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $this->loadProperties($row);
    // use special vineyard -> block selector
    $this->addLocationSelector($rowform, $row);
    // apply custom settings here
    $this->addAttachedProperties($rowform, $row);
    //dpm($row,'row');
  }
  
  public function addLocationSelector(&$form, &$entity) {
    // get facility
    // @todo: handle location only if this is a stand-alone for editing location, otherwise it is a child attribute
    $feature = $this->getParentEntity($entity);
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
    $form['featureid'] = array(
      '#title' => t('Location'),
      '#type' => 'select',
      '#options' => $options,
      '#size' => 1,
      '#weight' => -1,
      '#default_value' => $form['featureid']['#default_value'],
    );
    $form['tscode']['#type'] = 'textfield';
    $form['tscode']['#title'] = t('Row or Sub-Block');
    $form['tscode']['#weight'] = 0;
    $form['tscode']['#description'] = t('Alphanumeric code or description of sub-area for sampling.');
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
  
  function rangeList($start, $end, $inc = 1, $round = 0) {
    // ex: 0 to 1.0 by 0.1, 
    $range_list = array();
    for ($i = $start; $i <= $end; $i += $inc) {
      $i = round($i, $round);
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
  public function getLink($entity) {
    $args = arg();
    $page = ((strlen($args[0]) > 0) and (strpos($view_mode, 'ical') === false )) ? $args[0] : 'ipm-home';
    if (strpos($page, 'ical') !== false) {
      // catch this -- hack
      // @todo: make finaldest part of the entity render plugin in views so we don't have to do this
      $page = 'ipm-home';
    }
    $uri = "ipm-events/" . $entity->featureid . "/tsform/$entity->tid&finaldest=$page";
    $varinfo = $entity->varid ? dh_vardef_info($entity->varid) : FALSE;
    $varname = $varinfo->varname;
    $link = array(
      '#type' => 'link',
      '#prefix' => '&nbsp; ',
      '#suffix' => '<br>',
      '#title' => date('Y-m-d',$entity->tstime) . ": $varname event",
      '#href' => $uri,
      'query' => array(
        'finaldest' => $page,
      ),
      '#options' => array(
        'attributes' => array(
           'class' => array('editlink')
         ),
      ),
    );
    return $link;
  }
    
  public function dh_getValue($entity, $ts = FALSE, $propname = FALSE, $config = array()) {
    // Get and Render Chems & Rates
    $feature = $this->getParentEntity($entity);
    $varinfo = $entity->varid ? dh_vardef_info($entity->varid) : FALSE;
    $varname = $varinfo->varname;
    $args = arg();
    $page = (strlen($args[0]) > 0) ? $args[0] : 'ipm-home';
    if (strpos($page, 'ical') !== false) {
      // catch this -- hack
      // @todo: make finaldest part of the entity render plugin in views so we don't have to do this
      $page = 'ipm-home';
    }
    $pct = (floatval($entity->tsvalue) <= 0.05) ? "<=5%" : round(100.0 * floatval($entity->tsvalue)) . '%';
    switch ($propname) {
      case 'event_title':
        $title = date('Y-m-d',$entity->tstime) . ": $varname event";
        return $title;
      break;
      case 'event_description':
        //$description = "$varname @ $pct in " . $feature->name;
        $description = "$varname in " . $feature->name . " @ $entity->tsvalue " . $pct;
        $link = $this->getLink($entity);
        $uri = token_replace("[site:url]" . $link['#href']);
        $query = $link['query'];
        $query['absolute'] = TRUE;
        $description .= l('\nView:' . $uri, $uri, $query);
        return $description;
      break;
      
      default:
        if (property_exists($propname, $entity)) {
          return $entity->{$propname};
        } else {
          return $entity->varname;
        }
      break;
    }
    
  }
  
  public function buildContent(&$content, &$entity, $view_mode) {
    // special render handlers when using a content array
    // get all FRAC Codes associated with this entity
    $feature = $this->getParentEntity($entity);
    $varinfo = $entity->varid ? dh_vardef_info($entity->varid) : FALSE;
    $varname = $varinfo->varname;
    if ($varinfo === FALSE) {
      return;
    }
    $hidden = array('varname', 'tstime', 'tid', 'tsvalue', 'tscode', 'entity_type', 'featureid', 'tsendtime', 'modified', 'label');
    foreach ($hidden as $col) {
      $content[$col]['#type'] = 'hidden';
    }
    $pct = ($entity->tsvalue <= 0.05) ? "<=5%" : round(100.0 * $entity->tsvalue) . '%';
    $link = $this->getLink($entity);
    switch($view_mode) {
      case 'ical_summary':
        //$content['title'] = array(
        //  '#type' => 'item',
        //  '#markup' => "Verasion @ $pct in " . $feature->name,
        //);
        unset($content['title']['#type']);
        $content = array();
        $content['body'] = array(
          '#type' => 'item',
          '#markup' => "$varname @ $pct in " . $feature->name,
        );
      break;
      default:
        //$content['title'] = array(
        //  '#type' => 'item',
        //  '#markup' => "$varname @ $pct in " . $feature->name,
        //);
        $content['title'] = $link;
        $content['body'] = array(
          '#type' => 'item',
          '#markup' => "$varname @ $pct in " . $feature->name,
        );
      break;
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
    $hidden = array('pid', 'startdate', 'enddate', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $this->property_conf_default[$hide_this]['hidden'] = 1;
    }
  }
  
  public function pct_list($inc = 10) {
    $pcts = parent::pct_list($inc);
    $pcts["0"] = "TBD";
    return $pcts;
  }
  
  public function getActions() {
    $actions = array(
      'vitis_pruning_winter'=>'Dormant Pruning (all)',
      'vitis_pruning_winter_1st'=>'Dormant Pruning (1st)',
      'vitis_pruning_winter_2nd'=>'Dormant Pruning (2nd)',
      'vitis_training_thinning'=>'Shoot Thinning',
      'vitis_training_tying'=>'Shoot Positioning/Tying',
      'vitis_deleaf_fruitzone'=>'Leaf Pulling in Fruit Zone',
      'vitis_pruning_hedging'=>'Hedging',
    );
    return $actions;
  }
  
  public function formRowEdit(&$rowform, $row) {
    parent::formRowEdit($rowform, $row); // does hiding etc.
    // apply custom settings here
    //dpm($row,'row');
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $actions = $this->getActions();
    $rowform['tstime']['#weight'] = 0;
    $rowform['tscode'] = array(
      '#title' => t('Activity'),
      '#type' => 'select',
      '#options' => $actions,
      '#weight' => 1,
    );
    $pcts = $this->pct_list(10);
    //$pcts = array();
    //for ($i = 1; $i <= 20; $i++) {
   //   $dec = $i * 0.05;
   //   $pcts["$dec"] = $i * 5 . " %";
    //}
    $rowform['tsvalue'] = array(
      '#title' => t('% of block completed'),
      '#type' => 'select',
      '#options' => $pcts,
      '#weight' => 2,
      '#empty_value' => 0,
      '#empty_option' => 'TBD',
      '#default_value' => !empty($row->tsvalue) ? $row->tsvalue : 0,
    );
    $rowform['tscode']['#default_value'] = $row->tscode;
  }
  
  public function formRowSave(&$rowvalues, &$row) {
    parent::formRowSave($rowvalues, $row);
    //$codename = $this->row_map['code']['name'];
    //$row->$codename = implode('-', array($rowvalues['n'], $rowvalues['p'], $rowvalues['k']));
    // special save handlers
  }

  public function buildContent(&$content, &$entity, $view_mode) {
    parent::buildContent($content, $entity, $view_mode);
    // special render handlers when using a content array
    // get all FRAC Codes associated with this entity
    $feature = $this->getParentEntity($entity);
    $varinfo = $entity->varid ? dh_vardef_info($entity->varid) : FALSE;
    $varname = $varinfo->varname;
    if ($varinfo === FALSE) {
      return;
    }
    $hidden = array('varname', 'tstime', 'tid', 'tsvalue', 'tscode', 'entity_type', 'featureid', 'tsendtime', 'modified', 'label');
    foreach ($hidden as $col) {
      $content[$col]['#type'] = 'hidden';
    }
    $actions = $this->getActions();
    $activity = isset($actions[$entity->tscode]) ? $actions[$entity->tscode] : "Pruning/Canopy";
    $pct = ($entity->tsvalue <= 0.05) ? "<=5%" : round(100.0 * $entity->tsvalue) . '%';
    $pcts = $this->pct_list(5);
    $pct = isset($pcts[$entity->tsvalue]) ? $pcts[$entity->tsvalue] : "TBD";
    switch($view_mode) {
      default:
        //$content['title'] = array(
        //  '#type' => 'item',
        //  '#markup' => "$varname @ $pct in " . $feature->name,
        //);
        $content['body'] = array(
          '#type' => 'item',
          '#markup' => "$activity @ $pct in " . $feature->name,
        );
      break;
      case 'ical_summary':
        unset($content['title']['#type']);
        $content = array();
        $content['body'] = array(
          '#type' => 'item',
          '#markup' => "$activity @ $pct in " . $feature->name,
        );
      break;
    }
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
    //dpm($row,'plugin row');
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
    $pcts = array('<1');
    for ($i = 5; $i < 95; $i+= 5) {
      $pcts[] = $i;
    }
    $pcts[] = '>95';
    $pcts = $this->pct_list($pcts);
    $rowform['tsvalue']['#options'] = $pcts;
    $rowform['tsvalue']['#title'] = t('% of Plants Affected');
    $rowform['tscode']['#title'] = t('Incident Type');
    $rowform['tscode']['#type'] = 'select';
    $rowform['tscode']['#options'] = $this->incidentCodes();
    $rowform['tscode']['#size'] = 1;
  }
}

class dHVariablePluginIPMDisease extends dHVariablePluginIPMIncident {
  
  public function incidentCodes() {
    // do this as a query of variables in the 
    $options = dh_varkey_varselect_options(array("vocabulary = 'fungal_pathogens'"));
    asort($options);
    return $options;
    return array(
      'org_black_rot' => 'Black Rot',
      'org_botrytis' => 'Botrytis',
      'org_black_rot' => 'Downy Mildew',
      'org_phomopsis' => 'Phomopsis',
      'org_powdery_mildew' => 'Powdery Mildew',
      'org_ripe_rot' => 'Ripe Rot',
    );
  }
  
  public function getDefaults($entity, &$defaults = array()) {
    parent::getDefaults($entity, $defaults);
    $defaults += array(
      'ipm_advanced' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0,
        'propname' => 'Advanced',
        'vardesc' => 'Use incidence * extent formula to calculate overall occurence rate.',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'ipm_advanced',
        'varid' => dh_varkey2varid('ipm_advanced', TRUE),
      ),
      'ipm_incidence' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.5,
        'propname' => 'Incidence',
        'vardesc' => 'Fraction of plants effected (0.0-1.0)',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'ipm_incidence',
        'varid' => dh_varkey2varid('ipm_incidence', TRUE),
      ),
      'ipm_extent' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.5,
        'propname' => 'Extent',
        'vardesc' => 'Fraction effected tissue per plant (0.0-1.0)',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'ipm_extent',
        'varid' => dh_varkey2varid('ipm_extent', TRUE),
      ),
      'ipm_tissue' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => 'leaves',
        'propvalue_default' => 0.0,
        'propname' => 'Location',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'ipm_tissue',
        'varid' => dh_varkey2varid('ipm_tissue', TRUE),
      ),
    );
    return $defaults;
  }
  
  public function formRowEdit(&$form, $row) {
    parent::formRowEdit($form, $row); // does hiding etc.
    // done in parent now, override if ranges are insufficient
    //$pcts = $this->pct_list(array('<1', 5, 10, 15, 20, 25, 30, 40, 50, 60, 70, 80, 90, '>95'));
    //$form['tsvalue']['#options'] = $pcts;
    $form['tsvalue']['#default_value'] = ($row->tid > 0) ? $row->tsvalue : 0.25;
    $form['tsvalue']['#title'] = t('% Affected (incidence * extent)');
    $form['tsvalue']['#weight'] = 1;
    $form['tsvalue']['#type'] = 'select';
    $form['tscode']['#title'] = t('Organism Type');
    $form['tscode']['#type'] = 'select';
    $form['tscode']['#options'] = $this->incidentCodes();
    $form['tscode']['#size'] = 1;
    
    $form['Advanced']['Advanced'] = $form['Advanced'];
    $form['Advanced']['#type'] = 'fieldset';
    $form['Advanced']['#collapsible'] = TRUE;
    $form['Advanced']['#collapsed'] = TRUE;
    $form['Advanced']['#weight'] = 2;
    $adv = $row->Advanced;
    //dpm($adv,'row');
    //dpm($adv->propvalue,'propvalue');
    if ($adv->propvalue > 0) {
      // using advanced notation, so show as expanded
      $form['Advanced']['#collapsed'] = FALSE;
      $form['tsvalue']['#type'] = 'hidden';
      $form['tsvalue']['#prefix'] = round($row->tsvalue * 100.0, 2) . "%";
    }
    $form['Advanced']['Incidence'] = $form['Incidence'];
    $form['Advanced']['Extent'] = $form['Extent'];
    unset($form['Incidence']);
    unset($form['Extent']);
  }
  
  public function save($entity) {
    if ($entity->Advanced > 0) {
      // use advanced notation
      $entity->tsvalue = $entity->Incidence * $entity->Extent;
    }
    parent::save();
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
    return array('tid', 'varid', 'entity_type', 'bundle', 'tsvalue');
  }
  
  public function getDefaults($entity, &$defaults = array()) {
    parent::getDefaults($entity, $defaults);
    $defaults += array(
      'sample_weight_g' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'Sample Weight',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'sample_weight_g',
        'varid' => dh_varkey2varid('sample_weight_g', TRUE),
      ),
      'sample_size_berries' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'Berry Count',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'sample_size_berries',
        'varid' => dh_varkey2varid('sample_size_berries', TRUE),
      ),
      'brix' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'Brix',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'brix',
        'varid' => dh_varkey2varid('brix', TRUE),
      ),
      'ph' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 3.0,
        'propname' => 'pH',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'ph',
        'varid' => dh_varkey2varid('ph', TRUE),
      ),
      'berry_weight_g' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'Berry Weight',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'berry_weight_g',
        'varid' => dh_varkey2varid('berry_weight_g', TRUE),
      ),
      'seed_lignification' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'Seed Lignification',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'seed_lignification',
        'varid' => dh_varkey2varid('seed_lignification', TRUE),
      ),
      'total_acidity_gpl' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'Total Acidity',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'total_acidity_gpl',
        'varid' => dh_varkey2varid('total_acidity_gpl', TRUE),
      ),
      'water_content_pct' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.5,
        'propname' => 'Water Content',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'water_content_pct',
        'varid' => dh_varkey2varid('water_content_pct', TRUE),
      ), 
      'total_phenolics_aug' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'Total Phenolics',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'total_phenolics_aug',
        'varid' => dh_varkey2varid('total_phenolics_aug', TRUE),
      ),
      'total_anthocyanin_mgg' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
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
    //dpm($dopple,'dopple = ' . $pn);
    // override pH format
    // @todo: put this in plugin, or just eliminate, why should we have a select list for pH?
    //        maybe just a validator code is all that is needed
    /*
    dpm($rowform,'form before ph settings');
    $rowform['pH']['#type'] = 'select';
    $rowform['pH']['#options'] = array_merge(
      array(0 => 'NA'),
      $this->rangeList(2.0, 5.0, $inc = 0.01, 2)
    );
    */
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
    $entity->tsvalue = $rowvalues['Brix']; 
    if (($rowvalues['Berry_Count'] > 0) and ($rowvalues['Sample_Weight'] > 0)) {
      // auto-calculate berry weight
      $rowvalues['Berry_Weight'] = round(floatval($rowvalues['Sample_Weight']) / floatval($rowvalues['Berry_Count']),3);
      $entity->{"Berry Weight"} = round(floatval($rowvalues['Sample_Weight']) / floatval($rowvalues['Berry_Count']),3);
    }
  }

}
?>