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
    $rowform['tstime']['#weight'] = 0;
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
      '#weight' => 1,
      '#default_value' => $form['featureid']['#default_value'],
    );
    $form['tscode']['#type'] = 'textfield';
    $form['tscode']['#title'] = t('Row or Sub-Block');
    $form['tscode']['#weight'] = 1;
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
  var $loval = 0.05;
  var $lolabel = '<=5%';
  var $attach_method = 'contained';
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
        'vardesc' => 'Fraction of plants affected (0.0-1.0)',
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
        'vardesc' => 'Fraction affected tissue per plant (0.0-1.0)',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'ipm_extent',
        'varid' => dh_varkey2varid('ipm_extent', TRUE),
      ),
      'Location' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => 'leaves',
        'propvalue_default' => 0.0,
        'propname' => 'Location',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'ipm_tissue',
        'varid' => dh_varkey2varid('ipm_tissue', TRUE),
      ),
      'Sharing' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => 'locality',
        'propvalue_default' => 0.0,
        'propname' => 'Sharing',
        'singularity' => 'name_singular',
        '#weight' => 5,
        'featureid' => $entity->identifier(),
        'varkey' => 'ipm_info_share',
        'varid' => dh_varkey2varid('ipm_info_share', TRUE),
      ),
    );
    return $defaults;
  }
  
  public function incidentCodes() {
    // sub-class this to provide extra info 
    return array();
  }
  
  public function formRowEdit(&$form, $row) {
    parent::formRowEdit($form, $row); // does hiding etc.
    $pcts = array('<1');
    for ($i = 5; $i < 95; $i+= 5) {
      $pcts[] = $i;
    }
    $pcts[] = '>95';
    $pcts = $this->pct_list($pcts);
    $form['tsvalue']['#options'] = $pcts;
    $form['tsvalue']['#description'] = t('% of Plants Affected.  To use incident/extent notation click below to expand the section labeled Advanced');
    $form['tsvalue']['#weight'] = 3;
    
    $form['Advanced']['Advanced'] = $form['Advanced'];
    $form['Advanced']['#title'] = t('Advanced');
    $form['Advanced']['#type'] = 'fieldset';
    $form['Advanced']['#collapsible'] = TRUE;
    $form['Advanced']['#collapsed'] = TRUE;
    $form['Advanced']['#weight'] = 4;
    
    $adv = $row->Advanced;
    //dpm($row,'row');
    //dpm($adv,'adv');
    //dpm($form,'form');
    //dpm($adv->propvalue,'propvalue');
    if (floatval($adv->propvalue) > 0) {
      // using advanced notation, so show as expanded
      $form['Advanced']['#collapsed'] = FALSE;
      $form['tsvalue']['#type'] = 'hidden';
      $form['tsvalue']['#prefix'] = round($row->tsvalue * 100.0, 2) . "%";
      $form['tsvalue']['#element_validate'] = array('element_validate_number');
      unset( $form['tsvalue']['#options']);
    }
    // this moves to this grouped location.  
    // @todo: There may be a better way?  Or more automated, by using 
    // some array hierarchy in getDefaults() routine?
    $form['Advanced']['Incidence'] = $form['Incidence'];
    $form['Advanced']['Extent'] = $form['Extent'];
    unset($form['Incidence']);
    unset($form['Extent']);
    //dpm($form,'form');
    
  }
  
  public function save(&$entity) {
    if ($entity->Advanced > 0) {
      // use advanced notation
      $entity->tsvalue = $entity->Incidence * $entity->Extent;
    }
    parent::save($entity);
  }
  
  public function getIncidentDetail($entity ) {
    $codes = $this->incidentCodes();
    $incident_detail = !empty($entity->tscode) and isset($codes[$entity->tscode]) ? $codes[$entity->tscode] : $varname;
    $incident_detail = count($codes) > 0 ? $codes[$entity->tscode] : $varname;
    return $incident_detail;
  }
  
  public function buildContent(&$content, &$entity, $view_mode) {
    // special render handlers when using a content array
    // get all FRAC Codes associated with this entity
    // Note: Views result sets MUST have tid column included, even if hidden, in order to show a rendered ts entity.
    $feature = $this->getParentEntity($entity);
    $varinfo = $entity->varid ? dh_vardef_info($entity->varid) : FALSE;
    $varname = $varinfo->varname;
    $incident_detail = $this->getIncidentDetail($entity );
    if ($varinfo === FALSE) {
      return;
    }
    $hidden = array('varname', 'tstime', 'tid', 'tsvalue', 'tscode', 'entity_type', 'featureid', 'tsendtime', 'modified', 'label');
    foreach ($hidden as $col) {
      $content[$col]['#type'] = 'hidden';
    }
    // stash rendered tsvalue, tscode and featureid in case these are used elsewhere
    $content['tscode']['#markup'] = $incident_detail;
    $content['featureid']['#markup'] = $feature->name;
    $pct = ($entity->tsvalue <= $this->loval) ? $this->lolabel : round(100.0 * $entity->tsvalue) . '%';
    $content['tsvalue']['#markup'] = $pct;
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
      case 'tsvalue':
        unset($content['tsvalue']['#title']);
        unset($content['tstext']);
      break;
      case 'tscode':
        unset($content['tscode']['#title']);
        unset($content['tstext']);
      break;
      case 'featureid':
        unset($content['featureid']['#title']);
        unset($content['tstext']);
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
  var $attach_method = 'contained';
  
  public function incidentCodes() {
    /*
    return array(
      'hail' => 'Hail',
      'frost' => 'Frost Damage',
      'insect_damage' => 'Insect Damage',
      'leaf_burn' => 'Leaf Burn',
    );
    */
    $opts = array(
      'cutworms' => 'Climbing Cutworms',
      'bm_stinkbugs' => 'Brown marmorated stink bug',
      'gbm' => 'Grape berry moth',
      'swd' => 'Spotted wing drosophila',
      'gfb' => 'Grape flea beetle',
      'rblr' => 'Redbanded leafroller',
      'yj' => 'Yellowjackets',
      'rose_chafer' => 'Rose chafer',
      'gcurculio' => 'Grape curculio',
      'glooper' => 'Grapevine looper',
      'jbeetle' => 'Japanese Beetle',
      'ermite' => 'European red mite',
      'tgallmaker' => 'Tumid gallmaker',
      'spotted_lanternfly' => 'Spotted Lanternfly',
    );
    
    asort($opts);
    $opts['other'] = 'Other (describe in comments)'; // put this at the end
    return $opts;
  }
  public function formRowEdit(&$form, $row) {
    parent::formRowEdit($form, $row); // does hiding etc.
    $form['tscode']['#title'] = t('Insect Type');
    $form['tscode']['#type'] = 'select';
    $form['tscode']['#options'] = $this->incidentCodes();
    $form['tscode']['#size'] = 1;
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
  
  public function formRowEdit(&$form, $row) {
    // parent method handles location stuff
    parent::formRowEdit($form, $row);
    // apply custom settings here
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    
    $form['tstime']['#type'] = 'date_popup';
    $pcts = $this->pct_list(array('<5', 25, 50, 75, 100));
    $form['tsvalue'] = array(
      '#title' => t('% Veraison'),
      '#type' => 'select',
      '#options' => $pcts,
      '#weight' => 2,
      '#default_value' => !empty($row->tsvalue) ? $row->tsvalue : "0.5",
    );
    $form['actions']['submit']['#value'] = t('Save');
    $form['actions']['delete']['#value'] = t('Delete');
    /*
    $hidden = array('pid', 'startdate', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $form[$hide_this]['#type'] = 'hidden';
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
  
  public function getOptions() {  
    $opts = array(
      'locality' => 'Share County/City in Community Reports',
      'none' => 'Do Not Share',
 //     'geometry' => 'Share Exact Location',
    );
    return $opts;
  }
  
  public function formRowEdit(&$form, $row) {
    parent::formRowEdit($form, $row); // does hiding etc.
    $opts = $this->getOptions();
    $form['propcode']['#title'] = t('Share Event Info?');
    $form['propcode']['#type'] = 'select';
    $form['propcode']['#options'] = $opts;
    $form['propcode']['#default_value'] = !empty($row->propcode) ? $row->propcode : 'locality';
    $form['propcode']['#size'] = 1;
    $form['propcode']['#description'] = t('This setting controls whether or not your report will be shared in maps, alerts, and summary information with other users of GrapeIPM.org.');
  }
  public function attachNamedForm(&$rowform, $row) {
    parent::attachNamedForm($rowform, $row);
    $opts = $this->getOptions();
    $mname = $this->handleFormPropname($row->propname);
    $rowform[$mname]['#title'] = t('Share Event Info?');
    $rowform[$mname]['#description'] = t('This setting controls whether or not your report will be shared in maps, alerts, and summary information with other users of GrapeIPM.org.');
    $rowform[$mname]['#type'] = 'select';
    $rowform[$mname]['#options'] = $opts;
    $rowform[$mname]['#default_value'] = !empty($row->propcode) ? $row->propcode : 'locality';
    $rowform[$mname]['#size'] = 1;
  }
  
  public function applyEntityAttribute(&$property, $value) {
    // @todo: this needs to be more robust, as it assumes only one way to handle an attached property.
    //        bvut for now this will work.
    $property->propcode = $value;
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

class dHVariableVitisQuickGrowthStage extends dHVariablePluginAgmanAction {
  // this combines all growth stages into one since they are all of the format "stage" (propcode) and % (value)
  // this is a class to allow adding growth stage as an attachment to other events 
  // we use 50% values for this.  Users can later select other options 
  // Can we use getDefaults(), with featureid = $entity->featureid, and record type 
}

class dHVariablePluginFruitChemSample extends dHVariablePluginAgmanAction {
  // @todo: enable t() for varkey, for example, this is easy, but need to figure out how to 
  //        handle in views - maybe a setting in the filter or jumplists itself?
  //  default: agchem_apply_fert_ee
  //       fr: agchem_apply_fert_fr 
  var $attach_method = 'contained'; // will force all getDefaults() props to be on the form unless they are marked 'embed' = FALSE
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
  }
  public function hiddenFields() {
    return array('tid', 'varid', 'entity_type', 'bundle', 'tsvalue');
  }
  
  public function getDefaults($entity, &$defaults = array()) {
    parent::getDefaults($entity, $defaults);
    $defaults += array(
      'sample_size_berries' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'Berry Count',
        'title' => 'Number of Berries',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'sample_size_berries',
        'varid' => dh_varkey2varid('sample_size_berries', TRUE),
      ),
      'sample_weight_g' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'Sample Weight',
        'title' => 'Weight of Berries',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'vardesc' => "counted above, i.e. destemmed berries (g)",
        'varkey' => 'sample_weight_g',
        'varid' => dh_varkey2varid('sample_weight_g', TRUE),
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
        'title' => 'Average Berry Weight',
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
        'title' => 'Seed Browning',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'seed_lignification',
        'vardesc' => "Percent of Seed surface colored brown.",
        'varid' => dh_varkey2varid('seed_lignification', TRUE),
      ),
      'total_acidity_gpl' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'Total Acidity',
        'title' => 'Titratable acidity (TA, g/L)',
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
        '#weight' => 9,
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
      'total_sugar_mgb' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'TSL',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'total_sugar_mgb',
        'varid' => dh_varkey2varid('total_sugar_mgb', TRUE),
      ),
    );
    return $defaults;
  }
  
  public function formRowEdit(&$form, $entity) {
    parent::formRowEdit($form, $entity); // does location
    //dpm($dopple,'dopple = ' . $pn);
    // override pH format
    // @todo: put this in plugin, or just eliminate, why should we have a select list for pH?
    //        maybe just a validator code is all that is needed
    /*
    dpm($form,'form before ph settings');
    $form['pH']['#type'] = 'select';
    $form['pH']['#options'] = array_merge(
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
      $bw = floatval($rowvalues['Sample_Weight']) / floatval($rowvalues['Berry_Count']);
      $rowvalues['Berry_Weight'] = round($bw,3);
      $entity->{"Berry Weight"} = round($bw,3);
      if (($rowvalues['Brix'] > 0)) {
        // tS g/b = S g-S/100g-Berry * Berry-weight g * 1000.0 mg/g = Brix * 10 * Berry_Weight 
        $entity->{"TSL"} = floatval($rowvalues['Brix']) * 10.0 * $bw;
      }
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
    $link = $this->getLink($entity);
    switch($view_mode) {
      case 'ical_summary':
        unset($content['title']['#type']);
        $content = array();
        $content['body'] = array(
          '#type' => 'item',
          '#markup' => "$varname @ $entity->tsvalue brix in " . $feature->name,
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
          '#markup' => "$varname @ $entity->tsvalue brix in " . $feature->name,
        );
      break;
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
      'PMG' => 'PMG',
      '' => 'Unknown'
    );
    $rowform['propcode']['#type'] = 'select';
    $rowform['propcode']['#default_value'] = isset($row->propcode) ? $row->propcode : '';
    $rowform['propcode']['#options'] = $options;
    $rowform['propcode']['#size'] = 1;
  }
}

class dHAgmanVitisPlantTissue extends dHOMAlphanumericConstant {
  
  public function hiddenFields() {
    $hidden = array('varname', 'varid', 'pid', 'propvalue', 'entity_type', 'featureid', 'startdate', 'enddate', 'modified', 'label');
    return $hidden;
  }
  
  public function getCodeOptions() {
    $opts = array(
      'leaf' => 'Leaf',
      'stem' => 'Stem',
      'cluster' => 'Cluster',
      'berry' => 'Berry',
      'petiole' => 'Petiole',
      'rachis' => 'Rachis',
    );
    return $opts;  
  }
  
  public function formRowEdit(&$form, $entity) {
    parent::formRowEdit($form, $entity);
    if (!$entity->varid) {
      return FALSE;
    }
    $opts = $this->getCodeOptions();
    //dpm($public_vars,'public vars');
    $form['propcode'] = array(
      '#title' => t($entity->propname),
      '#type' => 'select',
      '#empty_option' => t('- Select -'),
      '#options' => $opts,
      '#description' => $entity->vardesc,
      '#default_value' => !empty($entity->propcode) ? $entity->propcode : "",
    );
  }
  
}

class dHVariablePluginIPMDisease extends dHVariablePluginIPMIncident {
  var $loval = 0.01;
  var $lolabel = "<=1%"; 
  // @todo: debug om class convert_attributes_to_dh_props() and loadProperties()
  //        why aren't they converting location sharing to setting?
  //    Once debugged, un-comment $attach_method = 'contained'
  
  public function getDefaults($entity, &$defaults = array()) {
    parent::getDefaults($entity, $defaults);
    $defaults += array(
      'tissue_type' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'tissue_type',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'vardesc' => 'Portion of plant sampled.',
        'attach_method' => 'contained',
        'title' => 'Plant Part',
        'varid' => dh_varkey2varid('om_agman_plant_tissue', TRUE),
      ),
    );
    return $defaults;
  }
  public function formRowSave(&$rowvalues, &$row) {
    parent::formRowSave($rowvalues, $row);
    //dpm($rowvalues, 'submitted');
    // special save handlers
  }
  public function incidentCodes() {
    // do this as a query of variables in the 
    $options = dh_varkey_varselect_options(array("vocabulary = 'fungal_pathogens'"));
    asort($options);
    return $options;
  }
  
  public function getIncidentDetail($entity ) {
    $codes = $this->incidentCodes();
    $incident_detail = !empty($entity->tscode) and isset($codes[$entity->tscode]) ? $codes[$entity->tscode] : $varname;
    dpm($entity,'ent');
    $incident_detail .= ' on ' . $entity->tissue_type->propcode;
    return $incident_detail;
  }
  
  public function formRowEdit(&$form, $row) {
    parent::formRowEdit($form, $row); // does hiding etc.
    $form['tscode']['#title'] = t('Organism Type');
    $form['tscode']['#type'] = 'select';
    $form['tscode']['#options'] = $this->incidentCodes();
    $form['tscode']['#size'] = 1;
    $form['tscode']['#weight'] = 2;
    //dpm($form,'form');
  }
  
  public function attachNamedForm(&$rowform, $row) {
    // @todo: move this to the base IPMIncidentExtent class 
    parent::attachNamedForm($rowform, $row);
    // if this is attached, we only show a single data entry form since we don't yet support multi in attached.
    // we should expect that the property will have an indication of the type in use: severity (default), incident or extent 
    $mname = $this->handleFormPropname($row->propname);
    $rowform[$mname]['#title'] = t($row->title);
    $rowform[$mname]['#type'] = 'textfield';
    $rowform[$mname]['#element_validate'] = array('element_validate_number');
    $rowform[$mname]['#default_value'] = !empty($row->propvalue) ? $row->propvalue : 0.0;
    //dpm($row, "Attaching");
  }
  
  public function save(&$entity) {
    /*
    dpm($entity,'entity save()');
    $dbt = debug_backtrace();
    dsm($dbt, "debug_backtrace()");
    */
    parent::save($entity);
    //dpm($entity,'saved');
  }
}

class dHAgmanSVSampleEvent extends dHVariablePluginAgmanAction {
  // 
  var $attach_method = 'contained'; // how to attach props found in getDefaults() 
  
  public function hiddenFields() {
    $hidden = array('varname', 'varid', 'tid', 'tsvalue', 'tscode', 'entity_type', 'featureid', 'tsendtime', 'modified', 'label');
    return $hidden;
  }
  
  public function getDefaults($entity, &$defaults = array()) {
    parent::getDefaults($entity, $defaults);
    $defaults += array(
      'Sharing' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => 'locality',
        'propvalue_default' => 0.0,
        'propname' => 'Sharing',
        'singularity' => 'name_singular',
        '#weight' => 4,
        'attach_method' => 'contained',
        'featureid' => $entity->identifier(),
        'varkey' => 'ipm_info_share',
        'varid' => dh_varkey2varid('ipm_info_share', TRUE),
      ),
      'leaf_black_rot' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => 'org_black_rot',
        'propvalue_default' => 0.0,
        'propname' => 'leaf_black_rot',
        'title' => 'Black Rot (leaf)',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'om_class_Constant',
        'attach_method' => 'contained',
        'propcode_mode' => 'read_only',
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
        'tissue_type' => 'leaf',
        'block' => 'Leaf Samples',
        '#weight' => 5,
      ),
      'leaf_powdery_mildew' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => 'org_powdery_mildew',
        'propvalue_default' => 0.0,
        'propname' => 'leaf_powdery_mildew',
        'title' => 'Powdery Mildew (leaf)',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'om_class_Constant',
        'attach_method' => 'contained',
        'propcode_mode' => 'read_only',
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
        'tissue_type' => 'leaf',
        'block' => 'Leaf Samples',
        '#weight' => 5,
      ),
      'cluster_black_rot' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => 'org_black_rot',
        'propvalue_default' => 0.0,
        'propname' => 'cluster_black_rot',
        'title' => 'Black Rot (cluster)',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varkey' => 'om_class_Constant',
        'attach_method' => 'contained',
        'propcode_mode' => 'read_only',
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
        'tissue_type' => 'cluster',
        'block' => 'Cluster Samples',
        '#weight' => 6,
      ),
    );
    return $defaults;
  }
  
  public function formRowEdit(&$form, $entity) {
    parent::formRowEdit($form, $entity); // does hiding etc.
    
    // @todo: add a sample method (3, 7, 10 or estimated)
    
    // Comment this for the moment until debugging the plant part setting propcode on the diseases.
    /*
    $attribs = $this->getDefaults($entity);
    // @todo: move to separate blocks.  This might be best residing in some parent class 
    foreach ($attribs as $att) {
      if (!isset($form[$block])) {
        $form[$block] = array();
        $form[$block]['#title'] = t($block);
        $form[$block]['#type'] = 'fieldset';
        $form[$block]['#collapsible'] = TRUE;
        $form[$block]['#collapsed'] = FALSE;
      }
      if (isset($att['block'])) {
        $form[$att['block']][$att['propname']] = $form[$att['propname']];
        unset($form[$att['propname']]);
      }
    }
    */
    //dpm($form, 'form');
  }
  
  public function save(&$entity) {
    // @todo: copy properties to dopples 
    // @todo: apply location sharing component settings to all children 
    parent::save($entity);
  }  
  
  public function updateProperties(&$entity) {
    parent::updateProperties($entity);
  }
  
  
  public function updateLinked(&$entity) {
    // @todo: this code should support the om_object
    // now, create linked 
    // ultimately this will just support anything defined by dHOMLinkage which could create a setLocalhostLinkedValue
    // But for now we just manually do here, to flesh out logic for use later.
    // - check the getDefaults() list, or the properties list for this 
    //   in other words, ANY property that is attached to this could define a linkage 
    //   the properties in this prototype will have the ability to create timeseries entries
    //   from the parent form information and the individual pieces.
    $props = $this->getDefaults($entity);
    foreach ($props as $thisvar) {
      // load the disease property from this parent object, should already reside on this $entity as named prop
      // skip if not a disease prop 
      if (!isset($thisvar['tissue_type'])) {
        continue;
      }
      $prop = $entity->{$thisvar['propname']};
      // - Load link properties for this disease prop 
      // - @todo: find all links with loadComponents($criteria = array())
      //   for now we just load the linked property for this, named as propname = linked 
      //   dHOMLinkage use load_single_property which calks om_getSet
      //   make link_type = 4, which is a newly defined class 
      $varinfo = array(
        'propname' => 'linked_ts', 
        'varkey' => 'om_map_model_linkage', 
        'link_type' => 4, 
        'entity_type' => 'dh_properties',
        'featureid' => $prop->pid,
        'propcode' => 'dh_timeseries'
      );
      $plugin = dh_variables_getPlugins($prop); 
      $plugin->loadSingleProperty($prop, 'linked_ts', $varinfo, FALSE);
      // @todo: if we put this into the definition of the disease observation data structure, we can remove the 
      //        call to save this property 
      $link_plugin = dh_variables_getPlugins($prop->linked_ts); 
      if ($prop->linked_ts->propvalue > 0) {
        // have to fix the tsvalue due to weird behavior when drupal loads number fields
        $result = db_query("select propvalue from dh_properties where pid = " . $prop->linked_ts->pid);
        $propvalue = $result->fetchField();
        $prop->linked_ts->propvalue = $propvalue;
        $ts = $link_plugin->getLinkedEntity($prop->linked_ts);
        $ts->tscode = $prop->propcode;
        $ts->tsvalue = $prop->propvalue;
      } else {
        // create 
        // @todo: move this code into the dHOMLinkage plugin 
        $ts_info = array(
          'featureid' => $entity->featureid,
          'entity_type' => $entity->entity_type,
          'varid' => dh_varkey2varid('ipm_outbreak', TRUE),
          'tscode' => $prop->propcode,
          'tsvalue' => $prop->propvalue,
          'tstime' => $entity->tstime,
          'Sharing' => $entity->Sharing->propcode,
          'tissue_type' => $thisvar['tissue_type']
        );
        $ts = entity_create('dh_timeseries', $ts_info); // says get all matching tstime
      }
      // @todo: once this goes into the dHOMLinkage plugin we can delete call to save this property 
      if ($prop->linked_ts->is_new or ($prop->linked_td->propvalue == NULL)) {
        $prop->linked_ts->propvalue = intval($ts->tid);
        $prop->linked_ts->save();
      }
      $ts->save();
      dpm($ts, 'ts');
      // iterate through replicant_proplist and copy from parent to replicant 
      /*
        function getLinkedEntity(&$entity) {
          $entity->src_entity_type = $entity->propcode;
          $entity->src_entity_id = $entity->propvalue;
          $entity->src_entity = entity_load_single($entity->src_entity_type, $entity->src_entity_id);
          return $entity->src_entity;
        }
       */
    }
  }
}

?>