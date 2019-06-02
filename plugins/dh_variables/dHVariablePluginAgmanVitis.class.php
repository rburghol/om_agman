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
    $rowform['tstime']['#weight'] = 1;
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $rowform['tsvalue']['#element_validate'] = array('element_validate_number');
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
    $form['tscode']['#title'] = t('Sub-Area');
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
      'vitis_crop_thinning'=>'Crop-Thinning',
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


class dHVariablePluginVitisShootLength extends dHVariablePluginAgmanAction {
  // @todo: enable t() for varkey, for example, this is easy, but need to figure out how to 
  //        handle in views - maybe a setting in the filter or jumplists itself?
  //  default: agchem_apply_fert_ee
  //       fr: agchem_apply_fert_fr 
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
    $hidden = array('tid', 'startdate', 'endtime', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $this->property_conf_default[$hide_this]['hidden'] = 1;
    }
  }
  
  public function formRowEdit(&$rowform, $row) {
    parent::formRowEdit($rowform, $row); // does hiding etc.
    // apply custom settings here
    $rowform['tsvalue']['#title'] = t('Median Shoot Length (in)');
    $rowform['tsvalue']['#type'] = 'textfield';
    $rowform['tsvalue']['#weight'] = 2;
  }

  public function buildContent(&$content, &$entity, $view_mode) {
    parent::buildContent($content, $entity, $view_mode);
    // special render handlers when using a content array
    // get all FRAC Codes associated with this entity
    $feature = $this->getParentEntity($entity);
    if ($varinfo === FALSE) {
      return;
    }
    $hidden = array('varname', 'tstime', 'tid', 'tsvalue', 'tscode', 'entity_type', 'featureid', 'tsendtime', 'modified', 'label');
    foreach ($hidden as $col) {
      $content[$col]['#type'] = 'hidden';
    }
    
    switch($view_mode) {
      default:
        //$content['title'] = array(
        //  '#type' => 'item',
        //  '#markup' => "$varname @ $pct in " . $feature->name,
        //);
        $content['body'] = array(
          '#type' => 'item',
          '#markup' => "Median Shoot Length @ $entity->tsvalue in. " . $feature->name,
        );
      break;
      case 'ical_summary':
        unset($content['title']['#type']);
        $content = array();
        $content['body'] = array(
          '#type' => 'item',
          '#markup' => "Median Shoot Length @ $entity->tsvalue in. " . $feature->name,
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

class dHVariablePluginIPMIncidentExtent extends dHVariablePluginPercentSelector {
  
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
    );
    return $defaults;
  }
  
  public function buildContent(&$content, &$entity, $view_mode) {
    // special render handlers when using a content array
    // get all FRAC Codes associated with this entity
    $codes = $this->incidentCodes();
    $incident_detail = $codes[$entity->tscode];
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
          '#markup' => "$varname: $incident_detail @ $pct in " . $feature->name,
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
          '#markup' => "$varname: $incident_detail @ $pct in " . $feature->name,
        );
      break;
    }
  }
  
}

class dHVariablePluginIPMIncident extends dHVariablePluginIPMIncidentExtent {
  
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



//class dHVariableOMInfoShare extends dHVariablePluginDefault {
class dHVariableOMInfoShare extends dHVariablePluginCodeAttribute {
  
  public function formRowEdit(&$rowform, $row) {
    parent::formRowEdit($rowform, $row); // does hiding etc.
    $opts = array(
      'locality' => 'Share Locality',
      'geometry' => 'Share Exact Location',
      'none' => 'Do Not Share Location',
    );
    $rowform['propcode']['#title'] = t('Share Event Info?');
    $rowform['propcode']['#type'] = 'select';
    $rowform['propcode']['#options'] = $opts;
    $rowform['propcode']['#default_value'] = !empty($row->propcode) ? $row->propcode : 'locality';
    $rowform['propcode']['#size'] = 1;
  }
  public function attachNamedForm(&$rowform, $row) {
    parent::attachNamedForm($rowform, $row);
    $opts = array(
      'locality' => 'Share Locality Only',
      'geometry' => 'Share Exact Location',
      'none' => 'Do Not Share Location',
    );
    $rowform[$row->propname]['#title'] = t('Share Event Info?');
    $rowform[$row->propname]['#description'] = t('Controls how details of this event are shared with other users.');
    $rowform[$row->propname]['#type'] = 'select';
    $rowform[$row->propname]['#options'] = $opts;
    $rowform[$row->propname]['#default_value'] = !empty($row->propcode) ? $row->propcode : 'locality';
    $rowform[$row->propname]['#size'] = 1;
  }
  
}
class dHVariablePluginIPMDisease extends dHVariablePluginIPMIncident {
  //var $attach_method = 'contained';
  // @todo: debug om class convert_attributes_to_dh_props() and loadProperties()
  //        why aren't they converting location sharing to setting?
  //    Once debugged, un-comment $attach_method = 'contained'
  
  public function convert_attributes_to_dh_props($entity) {
    // this will be called after a form submittal, the added form fields from attached props will be/
    // added as plain fields on the entity, we then grab them by name and handle their contents.
    $props = $this->getDefaults($entity);
    //dpm($props,'props to convert_attributes_to_dh_props');
    foreach ($props as $thisvar) {
      $convert_value = FALSE; // flag to see if we need to convert (in case we are called multiple times)
      $load_property = FALSE;
      $propvalue = NULL;
      $propname = $thisvar['propname'];
      $pn = $this->handleFormPropname($propname);
      // check for conversion from value to property
      // this could need to change as fully loaded objects could be stored as array  that are then loaded as object or handled more completely
      // in Form API *I think*
      // but for now, this handles the case where a property value is stashed on the object
      // cases:
      // - property exists, and IS object: check for form API munged name and copy over, otherwise, do nothing
      // - property exists and is NOT object: stash the value, load the prop object, and setValue to stashed
      // - property does not exist: load property and return
      if (property_exists($entity, $propname) and !is_object($entity->{$propname})) {
        // if the prop is not an object, stash the value and load property, 
        $convert_value = TRUE;
        $propvalue = $entity->{$thisvar['propname']};
        $load_property = TRUE;
      }
      if ( ($pn <> $propname) and property_exists($entity, $pn) ) {
        // handle case where prop name had spaces and was munged by form API
        // we assume that this is not going to be an object sine form API will return just a value
        $propvalue = $entity->{$pn};
        $convert_value = TRUE;
      }
      if (!property_exists($entity, $propname) ) {
        $load_property = TRUE;
      }
      if ($load_property) {
        //dsm("Loading property $thisvar[propname]");
        $this->loadProperties($entity, FALSE, $thisvar['propname']);
      }
      // now, apply the stashed value to the property
      if ($convert_value and is_object($entity->{$propname})) {
        $prop = $entity->{$thisvar['propname']};
        foreach ($prop->dh_variables_plugins as $plugin) {
          // the default method will guess location based on the value unless overridden by the plugin
          $plugin->applyEntityAttribute($prop, $propvalue);
        }
      }
    }
  }
  public function loadProperties(&$entity, $overwrite = FALSE, $propname = FALSE) {
    $props = $this->getDefaults($entity);
    if (!($propname === FALSE)) {
      // a single prop has been requested
      if (!array_key_exists($propname, $props)) {
        watchdog('dh', 'loadProperties(entity, propname) called on dH Variable plugin object but propname = ' . strval($propname) . ' not found');
        return FALSE;
      }
      $props = array($propname => $props[$propname]);
    }
    foreach ($props as $thisvar) {
      if (!isset($thisvar['embed']) or ($thisvar['embed'] === TRUE)) {
        if ($overwrite or !property_exists($entity, $thisvar['propname']) or (property_exists($entity, $thisvar['propname']) and !is_object($entity->{$thisvar['propname']})) ) {
          $thisvar['featureid'] = $entity->{$this->row_map['id']};
          $prop = dh_properties_enforce_singularity($thisvar, 'name');
          if (!$prop) {
            // prop does not exist, so need to create
            // @todo: manage this create the prop then pass defaults
            $prop = entity_create('dh_properties', $thisvar);
            if (isset($thisvar['propvalue_default'])) {
              $prop->propvalue = $thisvar['propvalue_default'];
            }
            if (isset($thisvar['propcode_default'])) {
              $prop->propcode = $thisvar['propcode_default'];
            }
          }
          if (!$prop) {
            watchdog('om', 'Could not Add Properties in plugin loadProperties');
            return FALSE;
          }
          //dpm($prop,'prop');
          // apply over-rides if given
          $prop->vardesc = isset($thisvar['vardesc']) ? $thisvar['vardesc'] : $prop->vardesc;
          $prop->varname = isset($thisvar['varname']) ? $thisvar['varname'] : $prop->varname;
          $entity->{$prop->propname} = $prop;
        }
      }
    }
  }
  public function updateProperties(&$entity) {
    // @todo: move this to the base plugin class 
    $props = $this->getDefaults($entity);
    //dpm($entity, "Calling updateProperties");
    foreach ($props as $thisvar) {
      if (!isset($thisvar['embed']) or ($thisvar['embed'] === TRUE)) {
        //dsm("Saving " . $thisvar['propname']);
        // load the property 
        // if a property with propname is set on $entity, send its value to the plugin 
        //   * plugin should be stored on the property object already
        // if prop on entity is an object already, handle directly, otherwise, load it
        //   the object method is advantageous because we can make things persist
        if (property_exists($entity, $thisvar['propname'])) {
          if (!is_object($entity->{$thisvar['propname']})) {
            // this has been set by the form API as a value 
            // so we need to load/create a property then set the value
        //dsm("Saving manually " . $thisvar['propname']);
            $thisvar['featureid'] = $entity->{$this->row_map['id']};
            $thisvar['propvalue'] = $entity->{$thisvar['propname']};
            $prop = dh_update_properties($thisvar, 'name');
          } else {
            $prop = $entity->{$thisvar['propname']};
            $prop->featureid = $entity->{$this->row_map['id']};
            entity_save('dh_properties', $prop);
          }
        }
      }
    }
  }
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
      'ipm_info_share' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => 'locality',
        'propvalue_default' => 0.0,
        'propname' => 'Info Sharing',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'ipm_info_share',
        'varid' => dh_varkey2varid('ipm_info_share', TRUE),
      ),
    );
    return $defaults;
  }
  
  public function formRowEdit(&$form, $row) {
    parent::formRowEdit($form, $row); // does hiding etc.
    //dpm($row,'entity');
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
    // this moves to this grouped location.  
    // @todo: There may be a better way?  Or more automated, by using 
    // some array hierarchy in getDefaults() routine?
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
  // @todo: make this an incident/extent child (dHVariablePluginIPMIncidentExtent) 
  //        -- will first need to change extent varid, and supply a resonable default for incident
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
    $hidden = array('tid', 'featureid', 'entity_type', 'bundle');
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
    $rowform[$stimename]['#weight'] = 0;
    $rowform[$codename]['#default_value'] = $row->$codename;
    $scale_opts = $this->pct_list(range(0,100,5));
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
    return;
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


class dHVariableReviewedPMG extends dHVariablePluginDefault {
  // two states: User / PMG
  // displayed as superscript
  public function hiddenFields() {
    return parent::hiddenFields() + array('pid', 'varid', 'dh_link_admin_pr_condition');
  }
  public function formRowEdit(&$rowform, $row) {
    parent::formRowEdit($rowform, $row); // does hiding etc.
    $options = array(
      'User' => 'User',
      'PMG' => 'PMG'
    );
    $rowform['propcode']['#type'] = 'select';
    $rowform['propcode']['#options'] = $options;
    $rowform['propcode']['#size'] = 1;
  }
}
?>