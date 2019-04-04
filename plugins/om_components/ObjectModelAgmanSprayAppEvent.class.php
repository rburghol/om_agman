<?php
module_load_include('inc', 'dh', 'plugins/dh.display');

class ObjectModelAgmanSprayAppEvent extends ObjectModelComponentsDefaultHandler {
  // provides configuration screen for application event (submittal)
  // Gets default values from:
  //   parent block(s) - eref to target featureid
  //     - total area to spray
  //   parent farm - eref'ed to block feature
  //     - sprayer volume
  // 
  var $dh_adminreg_feature = FALSE;
  var $dh_farm_feature = FALSE;
  var $dh_block_feature = FALSE;
  var $event_prop_conf = FALSE;
  var $chemgrid = FALSE;
  var $eventprops = FALSE;
  var $eventconstants = FALSE;
  var $agchem_event_area;
  var $agchem_total_spray_rate_galac;
  var $agchem_event_canopy_frac;
  var $agchem_spray_vol_gal;
  var $agchem_batch_gal;
  
  public function eventPropDefaultConf() {
    $conf = array();
    $conf['prop_entity_type'] = 'dh_adminreg_feature';
    $conf['entity_type'] = 'dh_properties';
    $conf['base_bundle'] = 'dh_properties';
    $conf['display'] = array(
      'bundle' => 'dh_properties'
    );
    $conf['groupname'] = 'event_settings'; 
    $conf['render_layout'] = 'unformatted_striped'; 
    $conf['display'] = array('properties' => array());
    $hiddens = array('propname', 'startdate', 'enddate', 'featureid', 'entity_type', 'propcode', 'pid');
    foreach ($hiddens as $prop) {
      $conf['display']['properties'][$prop] = array('hidden' => TRUE);
    }
    if (!($this->dh_adminreg_feature->adminid > 0)) {
      drupal_set_message("No application event feature exists, cannot add attributes to non-existent feature");
      return FALSE;
    }
    $conf['featureid'] = array($this->dh_adminreg_feature->adminid);
    // first load the event props or create if they do not exist.
    $this->LoadEventProperties($conf);
    $this->LoadBlockProperties($conf);
    $this->LoadFarmProperties($conf);
    // always update event area to total block area in case it's changed
    $this->setEventDefault($conf, 'agchem_event_area', round($this->GetTotalArea(),2), TRUE);
    // canopy frac default is 1.0 - later, if we use a growth model we can adjust based on date or other
    $this->setEventDefault($conf, 'agchem_event_canopy_frac', 1.0);
    if ($this->dh_farm_feature) {
      $this->setEventDefault($conf, 'agchem_batch_gal', $this->dh_farm_feature->dh_properties['agchem_sprayer_vol']->propvalue);
      // this one we over-write since it is calculated, and should be done at load AND save
      $this->setEventDefault($conf, 'agchem_total_spray_rate_galac', $this->dh_farm_feature->dh_properties['agman_sprayrate_default_galac']->propvalue);
    } else {
      watchdog('om_agman', "did not find dh_farm_feature->dh_properties['agchem_sprayer_vol'] or ['agman_sprayrate_default_galac']");
      $this->setEventDefault($conf, 'agchem_batch_gal', 100);
      $this->setEventDefault($conf, 'agchem_total_spray_rate_galac', 100);
    }
    // default spray volume is area * default rate/area
    $this->setEventDefault($conf, 'agchem_spray_vol_gal', 
      $this->dh_adminreg_feature->dh_properties['agchem_total_spray_rate_galac']->propvalue 
      *  $this->dh_adminreg_feature->dh_properties['agchem_event_area']->propvalue * $this->dh_adminreg_feature->dh_properties['agchem_event_canopy_frac']->propvalue, TRUE
    );
    //dpm($this->dh_adminreg_feature,'admin feature');
    //dpm($conf,'conf');
    return $conf;
  }
  
  public function GetTotalArea() {
    $area = 0;
    foreach ($this->dh_block_feature as $block) {
      $area += $block->dh_areasqkm['und'][0]['value'];
    }
    return $area * 247.1;
  }
  
  public function LoadEventProperties(&$conf, $reload = FALSE) {
    $conf['add'] = 1; // make this insert blank slots where needed
    // default for agchem_batch_gal = vineyard->agchem_sprayer_vol
    // the conf['varid'] list will also be later used to order the variables
    $conf['varid'] = array(
      'agchem_total_spray_rate_galac', 
      'agchem_batch_gal',
      'agchem_event_canopy_frac', 
      'agchem_spray_vol_gal', 
    );
    $criteria = array();    // load necessary properties for this event
    $vars = dh_vardef_varselect_options(array("varkey in ('" . implode("', '", array_values($conf['varid'])) . "')"));
    $criteria[] = array(
      'name' => 'varid',
      'op' => 'IN',
      'value' => array_keys($vars),
    );
    $conf['varid_ordered'] = array();
    foreach ($conf['varid'] as $delta => $varkey) {
      $id = dh_varkey2varid($varkey);
      $conf['varid_ordered'][] = $id[0];
    }
    $this->dh_adminreg_feature->loadComponents($criteria);
    //dpm($this->dh_adminreg_feature,'after loadComponents');
  }
  
  public function setEventDefault(&$conf, $varkey, $value, $overwrite = FALSE) {
    // adds setting in the prop group grid
    // also adds a blank property with the same settings on the Event object
    // if it does not exist
    if (!isset($this->dh_adminreg_feature->dh_properties[$varkey])) {
      $prop_values = array(
        'varkey' => $varkey, 
        'propvalue' => $value, 
        'entity_type' => 'dh_adminreg_feature',
        'bundle' => 'dh_properties',
        'featureid' => (!$this->dh_adminreg_feature->adminid) ? -1 : $this->dh_adminreg_feature->adminid,
      );
      $this->dh_adminreg_feature->dh_properties[$varkey] = entity_create('dh_properties', $prop_values);
    }
    // if value exists, make sure it is validated
    if (!empty($this->dh_adminreg_feature->dh_properties[$varkey]->propvalue) and !$overwrite) {
      // if we already have a valid value, we retrieve it from the event object 
      // instead of the supplied $value
      $value = isset($this->dh_adminreg_feature->dh_properties[$varkey]) ? $this->dh_adminreg_feature->dh_properties[$varkey]->propvalue : $value;
    } else {
      // set the value on the event property to the correct default as well
      $this->dh_adminreg_feature->dh_properties[$varkey]->propvalue = $value;
    }
    // add setting default in prop grid
    $conf['row_data_defaults'][$varkey] = array(
      'criteria' => array(
        'varkey' => array(
          'value' => $varkey,
        ),
        'pid' => array(
          'value' => NULL,
        ),
      ),
      'values' => array(
        'propvalue' => $value,
      ),
    );
  }
  
  public function LoadBlockProperties(&$conf, $reload = FALSE) {
    // load necessary properties for this event
    if (!$this->dh_adminreg_feature) {
      return FALSE;
    }
    //dpm($this->dh_adminreg_feature,'admin feature');
    $blockids = array();
    if (isset($this->dh_adminreg_feature->dh_link_feature_submittal['und'])) {
      foreach ($this->dh_adminreg_feature->dh_link_feature_submittal['und'] as $link) {
        $blockids[] = $link['target_id'];
      }
    }
    if (empty($blockids)) {
      drupal_set_message("Could not find block id.  Cannot load block info.");
      return FALSE;
    }
    $this->dh_block_feature = entity_load('dh_feature', array($blockids));
    if (!$this->dh_block_feature) {
      drupal_set_message("Block load $blockid failed.");
      return FALSE;
    }
    //dpm($this->dh_block_feature,'block');
    // get the block area and set in a property
    $criteria = array();    // load necessary properties for this event
    $vars = dh_vardef_varselect_options(array("varkey in ('agchem_event_area')"));
    $criteria[] = array(
      'name' => 'varid',
      'op' => 'IN',
      'value' => array_keys($vars),
    );
    $this->dh_adminreg_feature->loadComponents($criteria);
    //dpm($this->dh_adminreg_feature, '$this->dh_adminreg_feature');
  }
  
  public function LoadFarmProperties(&$conf, $reload = FALSE) {
    // load necessary farm properties for this event
    if (!$this->dh_block_feature) {
      return FALSE;
    }
    $oneblock = current($this->dh_block_feature);
    if (is_object($oneblock)) {
      $farmid = isset($oneblock->dh_link_facility_mps['und']) ? $oneblock->dh_link_facility_mps['und'][0]['target_id'] : FALSE;
      //dpm($farmid,"farm id");
      if (!$farmid) {
        return FALSE;
      }
      $this->dh_farm_feature = entity_load_single('dh_feature', $farmid);
      if (!$this->dh_farm_feature) {
        return FALSE;
      }
      $farmvars = array(
        'agchem_batch_gal' => 'agchem_sprayer_vol', 
        'agchem_total_spray_rate_galac' => 'agman_sprayrate_default_galac'
      );
      $criteria = array();
      $vars = dh_vardef_varselect_options(array("varkey in ('" . implode("', '", array_values($farmvars)) . "')"));
      $criteria[] = array(
        'name' => 'varid',
        'op' => 'IN',
        'value' => array_keys($vars),
      );
      $loaded = $this->dh_farm_feature->loadComponents($criteria);
      if (!isset($this->dh_farm_feature->dh_properties['agchem_sprayer_vol'])) {
        $prop_values = array(
          'varkey' => 'agchem_sprayer_vol', 
          'propvalue' => 100, 
          'entity_type' => 'dh_feature',
          'bundle' => 'dh_properties',
          'featureid' => $farmid,
        );
        $this->dh_farm_feature->dh_properties['agchem_sprayer_vol'] = entity_create('dh_properties', $prop_values);
      }
      if (!isset($this->dh_farm_feature->dh_properties['agman_sprayrate_default_galac'])) {
        $prop_values = array(
          'varkey' => 'agman_sprayrate_default_galac', 
          'propvalue' => 100, 
          'entity_type' => 'dh_feature',
          'bundle' => 'dh_properties',
          'featureid' => $farmid,
        );
        $this->dh_farm_feature->dh_properties['agman_sprayrate_default_galac'] = entity_create('dh_properties', $prop_values);
      }
    }
  }
  
  public function MaterialEventPropConfDefault() {
    $conf = array(
      'event_id' => $this->dh_adminreg_feature->adminid,
      'groupname' => 'chem_rates',
      'batch_amount' => $this->dh_adminreg_feature->dh_properties['agchem_batch_gal']->propvalue,
      'total_amount' =>  $this->dh_adminreg_feature->dh_properties['agchem_spray_vol_gal']->propvalue,
      'event_area' => $this->dh_adminreg_feature->dh_properties['agchem_event_area']->propvalue,
      'canopy_frac' => $this->dh_adminreg_feature->dh_properties['agchem_event_canopy_frac']->propvalue,
    );
    return $conf;
  }
  
  public function buildForm(&$form, $form_state) {
    // when we go to D8 this will be relevant
    //   public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    // until then, we use the old school method
    if (!is_object($this->dh_adminreg_feature)) {
      return FALSE;
    }
    // @todo: retrieve block from plan, and get area of block for 
    //        default area to apply to
    // @todo: retrieve vineyard (facility) from block, and get 
    //        volume of sprayer for default sprayer vol
    module_load_include('inc', 'dh', 'plugins/dh.display');
    $event_conf = $this->eventPropDefaultConf();
    if (!$event_conf) {
      return FALSE;
    }
    parent::buildForm($form, $form_state);
    $form['name'] = array(
      '#title' => t('Title'),
      '#type' => 'textfield',
      '#default_value' => ($this->dh_adminreg_feature->name <> '') ? $this->dh_adminreg_feature->name : 'Spray Event',
      '#description' => t('Event Description'),
      '#required' => TRUE,
      '#size' => 30,
      '#weight' => 1,
    );
    $date_format = 'Y-m-d H:i';
    // should have code in here to guess based on the phase/or passed in from the URL
    $form['startdate'] = array(
      '#title' => t('Application Start Date/Time'),
      '#description' => t('Planned date for this spray.'),
      '#required' => TRUE,
      '#default_value' => empty($this->dh_adminreg_feature->startdate) ? $this->dh_adminreg_feature->startdate : date($date_format,$this->dh_adminreg_feature->startdate),
      '#date_format' => $date_format,
      '#type' => 'date_select',
      '#date_year_range' => '-5:+5',
      '#weight' => 2,
    );
    // should have code in here to guess based on the phase/or passed in from the URL
    $form['enddate'] = array(
      '#title' => t('End Date/Time'),
      '#description' => t('This will be used to calculate re-entry and post-harvest intervals.'),
      '#required' => FALSE,
      '#default_value' => (empty($this->dh_adminreg_feature->enddate) or ($this->dh_adminreg_feature->enddate < $this->dh_adminreg_feature->startdate)) 
        ? date($date_format,$this->dh_adminreg_feature->startdate + 3600) 
        : date($date_format,$this->dh_adminreg_feature->enddate),
      '#date_format' => $date_format,
      '#type' => 'date_select',
      '#date_year_range' => '-5:+5',
      '#weight' => 2,
    );
    //dpm($this->dh_adminreg_feature,"event object");
    $form['show_agchem_event_area'] = array(
      '#prefix' => t('Total Area to Spray: '),
      '#attributes' => array( 'class' => array('control-label')),
      '#suffix' => $this->dh_adminreg_feature->dh_properties['agchem_event_area']->varunits,
      '#markup' => empty($this->dh_adminreg_feature->dh_properties['agchem_event_area']->propvalue) ? 0 : $this->dh_adminreg_feature->dh_properties['agchem_event_area']->propvalue,
      //'#description' => t('Event Description'),
      '#weight' => 4,
    );
    //dpm($this->dh_adminreg_feature,"event object");
    $form['agchem_event_area'] = array(
      '#title' => t('Total Area to Spray'),
      '#type' => 'hidden',
      '#suffix' => $this->dh_adminreg_feature->dh_properties['agchem_event_area']->varunits,
      '#default_value' => empty($this->dh_adminreg_feature->dh_properties['agchem_event_area']->propvalue) ? 0 : $this->dh_adminreg_feature->dh_properties['agchem_event_area']->propvalue,
      //'#description' => t('Event Description'),
      '#disabled' => FALSE,
      '#dh_properties.pid' => $this->dh_adminreg_feature->dh_properties['agchem_event_area']->pid,
      '#attributes' => array('maxlength' => 128, 'size' => 16, 'readonly' => TRUE),
    );
    $form['ftype'] = array(
      '#title' => t('FType'),
      '#type' => 'hidden',
      '#default_value' => 'agchem_app_plan',
      '#required' => TRUE,
    );
    $form['fstatus'] = array(
      '#type' => 'radios',
      '#title' => t('Status'),
      '#weight' => 2,
      '#default_value' => $this->dh_adminreg_feature->fstatus,
      '#options' => array(
        'planned' => t('Planned'),
        'completed' => t('Complete'),
        'cancelled' => t('Cancelled'),
      ),
    );
    // Machine-readable type name.
    $form['bundle'] = array(
      '#type' => 'hidden',
      '#default_value' => $this->dh_adminreg_feature->bundle,
      '#maxlength' => 32,
    );
    // @todo: figure out why we need to hide BEFORE we attach, otherwise, the dh_link_admin_submittal_pr gets hosed
    $hidden = array('field_prop_config', 'enabled', 'dh_link_admin_submittal_pr', 'dh_link_admin_timeseries', 'field_link_to_registered_agchem');
    foreach ($hidden as $hidethis) {
      $form[$hidethis]['#type'] = 'hidden';
    }
    field_attach_form('dh_adminreg_feature', $this->dh_adminreg_feature, $form, $form_state);
    // 'dh_link_feature_submittal', 
    //$form[$fname]['und']['#options'] = $opts;
    om_agman_form_block_select($form['dh_link_feature_submittal'], $this->dh_farm_feature->hydroid);
    $form['dh_link_feature_submittal']['#weight'] = 3;
    foreach ($hiddens as $hidethis) {
      if (isset($form[$hidethis])) {
        $form[$hidethis]['#type'] = 'hidden';
      }
    }
    // config info may be hard-wired here since we may not need a widget for this
    // type of very specific form. Also, much of config comes from the adminreg entity anyhow
    /*
    if (isset($this->dh_adminreg_feature->field_prop_config)) {
      $config = unserialize($this->dh_adminreg_feature->field_prop_config['und'][0]['value']);
    } else {
      $config = array();
    }
    */
    $var_order= $event_conf['varid'];
    $eventprops = new dhPropertiesGroup($event_conf);
    $eventprops->prepareQuery();
    $eventprops->getData();
    // sort items
    $sorted = array();
    foreach ($eventprops->data as $el) {
      $sorted[array_search($el->varid, $event_conf['varid_ordered'])] = $el;
    }
    // apply defaults to data array
    ksort($sorted);
    $eventprops->data = $sorted;
    $eventprops->buildForm($form, $form_state);
    // quick and dirty set the event area default 
    // @todo: allow us to pass this in to the dhPropertiesGroup
    $cfrac = 1.0;
    $chem_conf = $this->MaterialEventPropConfDefault();
    $chemgrid = new ObjectModelAgmanSprayMaterialProps($chem_conf);
    $chemgrid->prepareQuery();
    $chemgrid->getData();
    //dpm($chemgrid,'chemgrid');
    $chemgrid->buildForm($form, $form_state);
    //$form['chemgrid'] = array('#markup' => "Query: " . $chemgrid->query);
    $form['event_settings']['#weight'] = 5;
    $form['event_settings']['#prefix'] = '<div class="input-group input-group-sm">';
    $form['event_settings']['#suffix'] = '</div">';
    $form['chem_rates']['#weight'] = 6;
    $form['chem_rates']['#prefix'] = '<div class="input-group input-group-lg">';
    $form['chem_rates']['#prefix'] .= '<span class="warning">';
    $form['chem_rates']['#prefix'] .= t('Notice: This application is design to be an aid to help your pesticide use planning. However, it is your responsibility to keep, read, and follow the labels and SDS.');
    $form['chem_rates']['#prefix'] .= '</span>';
    $form['chem_rates']['#suffix'] = '</div">';
    $form['description']['#weight'] = 7;
    $form['data']['#tree'] = TRUE;
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save Block Info'),
      '#weight' => 40,
    );
    $this->eventprops = $eventprops;
    $this->chemgrid = $chemgrid;
    //dpm($form,'form');
  }
  public function submitForm(array &$form, $form_state) {
    //dpm($form_state,'form_state');
    // @todo: for validation fails silently for hidden fields - make sure we have good default
    // calling $this->eventPropDefaultConf() 
    //   returns the "group_name" prop which tells this object class
    //   what form_values group to look for, i.e. $form_values[groupname][1][varname]
    $eventprops = new dhPropertiesGroup($this->eventPropDefaultConf());
    //dpm($eventprops,'event prop to save');
    $eventprops->validateForm($form, $form_state);
    $eventprops->submitForm($form, $form_state);
    // save materials and quantities
    $conf = $this->MaterialEventPropConfDefault();
    $chemgrid = new ObjectModelAgmanSprayMaterialProps($conf);
    $chemgrid->validateForm($form, $form_state);
    $chemgrid->submitForm($form, $form_state);
    // save/update timeseries event attached to feature and erefed to spray plan
    $this->saveEventTimeseries($form, $form_state);
  }
  
  public function SaveDataObjectsAsForm() {
    if (is_object($this->eventprops)) {
      $this->eventprops->SaveDataObjectsAsForm();
    } else {
      watchdog('om_agman_spray_event', "SaveDataObjectsAsForm() called without eventprops object");
    }
    // try to add a default if not set 
    if (!is_object($this->chemgrid)) {
      watchdog('om_agman_spray_event', "SaveDataObjectsAsForm() called without chemgrid object");
      $chem_conf = $this->MaterialEventPropConfDefault();
      $this->chemgrid = new ObjectModelAgmanSprayMaterialProps($chem_conf);
      $this->chemgrid->prepareQuery();
      $this->chemgrid->getData();
      //dpm($this->chemgrid,'chemgrid');
      $this->chemgrid->buildForm($form, $form_state);
    }
    if (is_object($this->chemgrid)) {
      //dpm($this->chemgrid,"chemgrid to check data array");
      $this->chemgrid->SaveDataObjectsAsForm();
    } 
  }
  
  public function saveEventTimeseries(&$form, $form_state) {
    // save a record of this transaction in the spray table
    // see if one exists already (check the eref attached to this spray plan AR feature)
    // load dh_link_admin_timeseries
    // get tid of linked event from target_id 
    // if empty, create new
    // save
  }
  
  public function EventDelete(&$form, $form_state) {
    // delete record and associated data (props, erefs, timeseries)
    // most of this should be handled in the base entity classes
    // but we need to handle the delete of the timeseries that is attached 
    // to the block and erefed here to the agchem appplication event
  }
}

class ObjectModelAgmanSprayMaterialProps extends dhPropertiesGroup {
  // @todo:
  // 1. query app event adminreg table joins entityreference to ag_chem adminreg feature 
    // to get dh_properties:agchem_rate_group to get rate & amount variables needed
    // then joins on dh_properties twice to get the appropriate rate and amount values
  // 2. get_data() returns std_class with rate_varid, amount_varid, rate_value, amount_value
  // 3. form row defaults shows rate_varid, rate_value, amount_varid, amount_value
    // sets entity_type and bundle from defaults - later may allow custom setting
  // 4. submitForm expects simplified rate_varid, rate_value, amount_varid, amount_value
  // 5. javascript needed for onChange rate_value to set amount_value
  // 6. configure headers
  // 7. populate rate type properties on chem reg records
  // 8. what happens if we change the rate type on the chem reg?
    // all properties and ts would have to be transformed from previous rate var to new one
    // 
  var $event_id = FALSE;
  var $env = array();
  var $eref_name = FALSE;
  var $eref_entity = 'from'; // from or to
  var $rate_varkey = 'agchem_rate'; 
  var $amount_varkey = 'agchem_amount'; 
  var $batch_amount = 1; 
  var $total_amount = 1; 
  var $event_area = 1; 
  var $canopy_frac = 1; 
  var $save_method = 'form_entity_map';
  var $form_entity_map; // @todo - move this to parent class
  public function __construct($conf = array()) {
    parent::__construct($conf);
    //dpm($conf,'conf');
    //dpm($this,'chems');
  }
  
  function getPropEntityInfo() {
    // this may move into generic eref handler
    if (!$this->eref_name) {
      return FALSE;
    }
    $ei = array(
      'entity keys' => array(
        'id' => $this->eref_name . '_erefid',
        'target' => $this->eref_name . '_target_id',
      ),
      'base table' => 'field_data_' . $this->eref_name
    );
    entity_get_info($this->prop_entity_type);
    if (!isset($ei['entity keys']['id']) or !isset($ei['base table'])) {
      // fail with malformed entity exception
      //dpm($ei,"Problem with entity info from entity_get_info($this->prop_entity_type)");
      return FALSE;
    }
    // insure only numeric
    return $ei;
  }
  
  public function entityDefaults() {
    parent::entityDefaults();
    // get default list and order of form columns from blank form
    // HEADERS - sets 
    $this->entity_defaults['dh_adminreg_feature'];
    $this->entity_defaults['batch_amount'] = 1;
    $this->entity_defaults['total_amount'] = 1;
    $this->entity_defaults['event_area'] = 1;
    $this->entity_defaults['canopy_frac'] = 1;
    $this->entity_defaults['eref_name'] = 'field_link_to_registered_agchem';
    $this->entity_defaults['event_id'] = FALSE;
    $this->entity_defaults['groupname'] = 'prop_group';
    $this->entity_defaults['prop_entity_type'] = 'field_link_to_registered_agchem';
  }
  
  public function entityOptions(&$form, $form_state) {
    parent::entityOptions($form, $form_state);
    // set entity type as visible selector of entity references
    // @todo: put logic in base class to allow any entity or entityreference (anything with pk)
    //   to be compatible with dhPropertiesGroup configurator
    $erefs = field_read_fields(array('type' => 'entityreference'));
  }
  
  function prepareQuery() {
    //$ok = parent::prepareQuery();
    //if (!$ok) {
    //  return FALSE;
    //}
    $this->applyEntityTokens();
    $this->applySettings();
    
    $ei = $this->getPropEntityInfo();
    $eref_pkcol = $ei['entity keys']['id'];
    $eref_tbl = $ei['base table'];
    $eref_target = $ei['entity keys']['target'];
    // get app plan adminreg id
    // get linked chems
    // get rate group for each chem
      // varkey = agchem_rate_group
      // propcode = vocab of rate group: agchem_event_lbs, agchem_event_oz
    // get individual components of application
      // datatype = rate
      // datatype = amount
    // iterate through refs
    $q = "  select plan.adminid as planid, chem.adminid as chemid, ";    
    $q .= " chem.name as name, chemlink.$eref_pkcol as chemlink_id, ";
    $q .= " chemlink.$eref_pkcol as rate_featureid, chemlink.$eref_pkcol as amount_featureid, ";
    $q .= " rate.pid as rate_pid, amount.pid as amount_pid, ";    
    $q .= " rv.hydroid as rate_varid, rate.propvalue as rate_propvalue, ";
    $q .= " CASE ";
    // allows for user settable rate units but defaults to chems reg units if specified
    //$q .= "   WHEN (rate.propcode IS NOT NULL) AND (rate.propcode <> '') THEN rate.propcode ";
    // only use global rate units, thisinsures that if chem def is updated, all changes propagate
    // this also requires that if the units recommended change, a new chem must be registered
    $q .= "   WHEN (rate_type.propcode IS NOT NULL) AND (rate_type.propcode <> '') THEN rate_type.propcode ";
    $q .= "   ELSE 'oz/acre' ";
    $q .= " END as rate_units, ";
    // Note: amount units are handled in form processing/mapping since they are derived from rate units
    $q .= " rate_limit_lo.propvalue as rate_lo, rate_limit_hi.propvalue as rate_hi, ";
    $q .= " av.hydroid as amount_varid, amount.propvalue as amount_propvalue ";
    $q .= " from {dh_adminreg_feature} as plan ";
    $q .= " left outer join {$eref_tbl} as chemlink ";
    $q .= " on ( ";
    $q .= "   plan.adminid = chemlink.entity_id ";
    $q .= "   and chemlink.entity_type = 'dh_adminreg_feature' ";
    $q .= " ) ";
    $q .= " left outer join {dh_adminreg_feature} as chem ";
    $q .= " on ( ";
    $q .= "   chem.adminid = chemlink.$eref_target ";
    $q .= "   and chemlink.entity_type = 'dh_adminreg_feature' ";
    $q .= " ) ";
    $q .= " left outer join {dh_variabledefinition} as rv ";
    $q .= " on (rv.varkey = '$this->rate_varkey') ";
    $q .= " left outer join {dh_variabledefinition} as av ";
    $q .= " on (av.varkey = '$this->amount_varkey') ";
    // join to the application rate property (non-dim)
    $q .= " left outer join {dh_properties} as rate ";
    $q .= " on ( ";
    $q .= "   rate.featureid = chemlink.$eref_pkcol ";
    $q .= "   and rate.entity_type = '$this->eref_name' ";
    $q .= "   and rate.varid = rv.hydroid ";
    $q .= " ) ";
    // join to the value (non-dim)
    // & datatype 'amount'
    $q .= " left outer join {dh_properties} as amount ";
    $q .= " on ( ";
    $q .= "   amount.featureid = chemlink.$eref_pkcol ";
    $q .= "   and amount.entity_type = '$this->eref_name' ";
    $q .= "   and amount.varid = av.hydroid ";
    $q .= " ) ";
    // get the default rate variable, keyed by varname, 
    // varid indicates which var to use for rate (flozac, ozac, ...)
    $q .= " left outer join {dh_properties} as ratetype ";
    $q .= " on ( ";
    $q .= "   ratetype.featureid = chem.adminid ";
    $q .= "   and ratetype.entity_type = 'dh_adminreg_feature' ";
    $q .= "   and ratetype.propname = 'agchem_rate_type' ";
    $q .= " ) ";
    $q .= " left outer join {dh_variabledefinition} as llv ";
    $q .= " on (llv.varkey = 'agchem_rate_lo_nond') ";
    $q .= " left outer join {dh_properties} as rate_limit_lo ";
    $q .= " on ( ";
    $q .= "   rate_limit_lo.featureid = chem.adminid ";
    $q .= "   and rate_limit_lo.entity_type = 'dh_adminreg_feature' ";
    $q .= "   and rate_limit_lo.varid = llv.hydroid ";
    $q .= " ) ";
    $q .= " left outer join {dh_variabledefinition} as lhv ";
    $q .= " on (lhv.varkey = 'agchem_rate_hi_nond') ";
    $q .= " left outer join {dh_properties} as rate_limit_hi ";
    $q .= " on ( ";
    $q .= "   rate_limit_hi.featureid = chem.adminid ";
    $q .= "   and rate_limit_hi.entity_type = 'dh_adminreg_feature' ";
    $q .= "   and rate_limit_hi.varid = lhv.hydroid ";
    $q .= " ) ";
    $q .= " left outer join {dh_variabledefinition} as art ";
    $q .= " on (art.varkey = 'agchem_rate_type') ";
    $q .= " left outer join {dh_properties} as rate_type ";
    $q .= " on ( ";
    $q .= "   rate_type.featureid = chem.adminid ";
    $q .= "   and rate_type.entity_type = 'dh_adminreg_feature' ";
    $q .= "   and rate_type.varid = art.hydroid ";
    $q .= " ) ";
    $q .= " WHERE plan.adminid = $this->event_id ";
    $this->query = $q;
    //dpm($q,'query');
    return TRUE;
  }
  
  function getData() {
    if (!isset($this->query) or !$this->query) {
      // malformed or non existent query
      return FALSE;
    }
    $this->data = array();
    //get the chems linked to this event
    $q = db_query($this->query);
    //dpm($q, "initial data");
    foreach ($q as $prow) {
      // get the 2 props that describe each linkage - rate and amount (total)
      // create StdClass object to hold all data since we have multiple props on the same line
      if ($prow->pid == NULL) {
        // this is an insert request
        // not sure if we do anything different between these two cases?
        // maybe check here for default variables?
        //dpm($prow, "Creating blank");
        $this->data[] = $prow;
      } else {
        // not sure if we do anything different between these two cases?
        // maybe check here for default variables?
        $this->data[] = $prow;
      }
    }
  }
  
  public function formRowDefaults(&$rowform, $row) {
    // Row Record:
    //    planid, chemid, name, 
    //    rate_pid, rate_varid, rate_propvalue,
    //    amount_pid, amount_varid, amount_propvalue
    $pc = $this->conf['display']['properties'];
    //dpm($pc, "Prop conf");
    $fc = $this->conf['display']['fields'];
    // static non-dimensional varid
    /*
    $rowform['rate_varid'] = array(
      '#type' => 'hidden',
      '#coltitle' => 'Rate Var',
      '#value' => empty($row->rate_varid) ? $this->rate_varkey : $row->rate_varid,
      '#required' => TRUE,
    );
    */
    //dpm($row,'row');
    // set up rate limits now so we can make a default guess if this is a new record
    $rate_limits = array();
    if ($row->rate_lo > 0) {
      $rate_limits[] = $row->rate_lo;
    }
    if ($row->rate_hi > 0) {
      $rate_limits[] = $row->rate_hi;
    }
    // evaluate the recs
    sort($rate_limits);
    //dpm($this,'this');
    //dpm($row,'row');
    $rate_units = empty($row->rate_units) ? 'floz/acre' : $row->rate_units;
    $plugin = new dHVariablePluginAppRates;
    $all_units = $plugin->rateUnits();
    $pretty_units = isset($all_units[$rate_units]) ? $all_units[$rate_units] : $rate_units;
    
    $scale = $this->scaleFactor($this->canopy_frac, $rate_units);
    //$rate_adjusted = array_map(function($el) { return $el * $this->canopy_frac; }, $rate_limits);
    $rate_adjusted = array_map(function($el, $frac) { return $el * $frac; }, $rate_limits, array_fill(0,count($rate_limits),$scale));
    $rate_suggestions = empty($rate_limits) ? '---' : implode(' to ', $rate_adjusted) . " $pretty_units ";
    $rate_range = empty($rate_limits) ? '---' : implode(' to ', $rate_limits) . " $pretty_units";
    
    $rowform['rate_range'] = array(
      '#type' => 'fieldset',
      '#coltitle' => 'Material Label Range',
      //'#markup' => 'Test Test',
    );
    $rowform['rate_range']['base_rate'] = array(
      '#type' => 'item',
      '#markup' => 
        "<strong>$row->name</strong>"
        ."<br>&nbsp;&nbsp;($rate_range)",
    );
    $vol_per_vols = array('oz/gal');
    // dont scale if it is a concentration based since volume is already scaled
    if (!in_array($rate_units, $vol_per_vols)) {
      // load ai % if available
      $ai_info = array(
        'varkey' => 'agchem_ai',
        'entity_type' => 'dh_adminreg_feature',
        'featureid' => $row->chemid,
      );
      $result = dh_get_properties($ai_info);
      if (property_exists($result, 'dh_properties')) {
        
      }
    }
    // final units
    $ra_conv = array(
      'oz/acre' => 'oz',
      'oz/gal' => 'oz',
      'floz/acre' => 'floz',
      'lbs/acre' => 'lbs',
      'gals/acre' => 'gals',
      'pt/acre' => 'pt',
      'pt/gal' => 'pt',
      'pt/cgal' => 'pt',
      'qt/acre' => 'qt',
    );
    # dynamically adjusting rate range scaler
    for ($r = 5; $r <= 100; $r += 5) {
      // create a set of conditionals
      // @todo: this is keyed against selct list named 'event_settings[3][propvalue]' 
      //        which is obviously risky and subject to change if we modify
      //        Thus, we should chagne this to "addAttachedProperties" method in OM module 
      $cf = $r/100.0;
      $scale = $this->scaleFactor($cf, $rate_units);
      $ra = array_map(function($el, $frac) { return $el * $frac; }, $rate_limits, array_fill(0,count($rate_limits),$scale));
      $rs = empty($rate_limits) ? '---' : implode(' to ', $ra) . " $pretty_units ";
      $rate_select_key = $r/100.0;
      $rowform['rate_range']["rate_$r"] = array(
        '#type' => 'item',
        '#markup' => '&nbsp;&nbsp; * ' . ($scale * 100) . '% of full canopy'
          . '<br>&nbsp;&nbsp; = ' . $rs,
        '#states' => array(
          'visible' => array(
            ':input[name="event_settings[3][propvalue]"]' => array('value' => "$rate_select_key"),
          ),
        ),
      );
    }
    // $scale is used here NOT canopy_frac since scale is canopy_frac adjusted in case of concentration based
    // disabled to insure new work flow
    //$row->rate_propvalue = empty($row->rate_propvalue) ? $scale * round(array_sum($rate_limits) / count($rate_limits),1) : $row->rate_propvalue;
    $rowform['rate_propvalue'] = array(
      '#coltitle' => 'Rate',
      '#title' => 'Rate',
      '#required' => TRUE,
      //'#prefix' => '<div class="input-group input-group-sm">',
      //'#prefix' => '<div class="col-xs-12">',
      '#suffix' => $pretty_units,
      '#type' => 'textfield',
      '#element_validate' => array('element_validate_number'),
      '#size' => 8,
      //'#attributes' => array('disabled' => 'disabled'),
      //'#attributes' => array( 'size' => 16),
      '#default_value' => $row->rate_propvalue,
    );
    
    // batch total
    // this can be refreshed in the form via javascript?
    // check if batch size is > total volume to spray, make match = total
    $this->batch_amount = ($this->batch_amount > $this->total_amount) ? $this->total_amount : $this->batch_amount;
    // @todo: make this based on units, right now it just assumes rate is in oz/ac
    if ($this->event_area > 0) {
      $unitconv = 1.0 * $this->event_area;
    } else {
      $unitconv = 1.0;
    }
    $unitconv = $this->rateFactor($this->event_area, $this->batch_amount, $rate_units);
    switch ($row->rate_units) {
      default:
        // quantity per acre
        $batch_val = $row->rate_propvalue * $unitconv * $this->batch_amount / $this->total_amount;
        $batch_val = ($batch_val > 10) ? round($batch_val,1) : round($batch_val,2);
        $total_val = $row->rate_propvalue * $unitconv;
        $total_val = ($total_val > 10) ? round($total_val,1) : round($total_val,2);
      break;
    }
    $amount_units = empty($row->rate_units) ? '' : $ra_conv[$row->rate_units];
    $rowform['batch_total'] = array(
      '#coltitle' => 'Per Tank / Total',
      //'#markup' => $batch_val . " $amount_units",
      '#markup' => $batch_val . " $amount_units" . " / " . $total_val . " $amount_units",
    );
    // helper conversions for recs in qt and pint
    $con_small = array(
      'pt' => 16.0, 'qt' => 32.0
    );
    list($num, $denom) = explode('/',$row->rate_units);
    //dpm($con_small," $num, $denom, $row->rate_units ");
    if ( ($batch_val <= 10.0) and in_array($num, array_keys($con_small)) ) {
      // @todo add a conversion to floz 
      $rac = $con_small[$num];
      $rowform['batch_total']['#markup'] .= 
        '<br>(' 
        . round($batch_val * $rac, 1) 
        . ' / ' . round($total_val * $rac, 1) 
        . ' floz)'
      ;
    }
    /*
    $rowform['amount_propvalue'] = array(
      '#coltitle' => 'Total Spray',
      '#markup' => $total_val . " $amount_units",
      '#default_value' => $total_val,
    );
    */
    // textual description of rate units
    // linked from chem admin record, ozac, flozac, ozgal, flozgal, lbsac, lbsgal
    $rowform['rate_units'] = array(
      '#type' => 'hidden',
      '#default_value' => empty($row->rate_units) ? '' : $row->rate_units,
    );
    $rowform['amount_pid'] = array(
      '#type' => 'hidden',
      '#default_value' => $row->amount_pid,
    );
    $rowform['rate_pid'] = array(
      '#type' => 'hidden',
      '#value' => $row->rate_pid,
    );
    $rowform['rate_featureid'] = array(
      '#type' => 'hidden',
      '#value' => empty($row->rate_featureid) ? NULL : $row->rate_featureid,
      '#required' => TRUE,
    );
    // variable dimensional varid, should be specified by Chem admin record
    // dh_property - agchem_rate_group
    // if this is not specified on the chem admin record, show a select list
    // default to flozac
    $rowform['amount_featureid'] = array(
      '#type' => 'hidden',
      '#default_value' => empty($row->amount_featureid) ? NULL : $row->amount_featureid,
      '#required' => TRUE,
    );
    
    $this->formRowVisibility($rowform, $row);
    
    // need to spoof a form_state for the row to properly load attached fields
    
  }
  
  public function scaleFactor($canopy_frac, $rate_units) {
    // rate_scale - f(canopy_frac, rate_units)
    //   if vol/vol or mass/vol rate then adjustment is 1.0 (none), if vol/area or mass/area then 
    //   scale = % canopy_frac
    $volume = array('gal', 'gals', 'liter', 'liters', 'l', 'ml');
    list($num, $denom) = explode('/',$rate_units);
    if (in_array($denom, $volume)) {
      $rate_scale = 1.0;
    } else {
      $rate_scale = $canopy_frac;
    }
    return $rate_scale;
  }
  
  public function rateFactor($area, $water_volume, $rate_units) {
    // we get both the acreage and the batch total water volume
    // so we can do either area dependent or concentration dependent
    // this can be refreshed in the form via javascript?
    // @todo: make this based on units, right now it just assumes rate is in oz/ac
    $volume = array('gal', 'cgals', 'cgal', 'gals', 'liter', 'liters', 'l', 'ml');
    list($num, $denom) = explode('/',$rate_units);
    if (in_array($denom, $volume)) {
      if (in_array($denom, array('cgal','cgals'))) {
        // this is per 100 gal, so divide by 100
        $factor = $water_volume / 100.0;
      } else {
        $factor = $water_volume;
      }
    } else {
      if ($area > 0) {
        $factor = 1.0 * $area;
      } else {
        $factor = 1.0;
      }
    }
    return $factor;
  }
  
  public function FormEntityMap(&$form_entity_map = array(), $row = array()) {
    // @todo - create stub for this in parent class
    // the rate
    $form_entity_map['rate'] = array(
      'entity_type' => 'dh_properties',
      'entity_class' => 'entity', // entity, entityreference, field
      'description' => 'Desired rate per area or volume of field.',
      'bundle' => 'dh_properties',
      'debug' => FALSE,
      'notnull_fields' => array('propvalue'),
      'entity_key' => array(
        'fieldname'=> 'pid',
        'value_src_type' => 'form_key', 
        'value_val_key' => 'rate_pid', 
      ),
      'handler' => '', // NOT USED YET - could later add custom functions
      'fields' => array(
        // field_name - name in the destination entity 
        // value_src_type - type of  
        // value_src_type - form_field, EntityFieldQuery, 
          // token, constant, env (environment var)
        'featureid' => array(
          'fieldname'=> 'featureid',
          'value_src_type' => 'form_key', 
          'value_val_key' => 'rate_featureid', 
        ),
        'entity_type' => array(
          'fieldname'=> 'entity_type',
          'value_src_type' => 'constant', 
          'value_val_key' => $this->eref_name, 
        ),
        'varid' => array(
          'fieldname'=> 'varid', 
          'value_src_type' => 'constant', 
          'value_val_key' => dh_varkey2varid($this->rate_varkey, TRUE), 
        ),
        'propvalue' => array (
          'fieldname'=> 'propvalue',
          'value_src_type' => 'form_key', 
          'value_val_key' => 'rate_propvalue', 
        ),
        'propcode' => array (
          'fieldname'=> 'propcode',
          'value_src_type' => 'constant', 
          'value_val_key' => $row['rate_units'], 
        ),
      ),
      'resultid' => 'pid_rate',
    );
    if ($this->event_area > 0) {
      $unitconv = 1.0 * $this->event_area;
    } else {
      $unitconv = 1.0;
    }
    $unitconv = $this->rateFactor($this->event_area, $this->total_amount, $row['rate_units']);
    $ra_conv = array(
      'oz/acre' => 'oz',
      'floz/acre' => 'floz',
      'lbs/acre' => 'lbs',
      'gals/acre' => 'gals',
    );
    $total_val = $row['rate_propvalue'] * $unitconv;
    $total_val = ($total_val > 10) ? round($total_val,1) : round($total_val,2);
    $form_entity_map['amount'] = array(
      'entity_type' => 'dh_properties',
      'entity_class' => 'entity', // entity, entityreference, field
      'description' => 'Total Applied.',
      'bundle' => 'dh_properties',
      'debug' => FALSE,
      'notnull_fields' => array('propvalue'),
      'entity_key' => array(
        'fieldname'=> 'pid',
        'value_src_type' => 'form_key', 
        'value_val_key' => 'amount_pid', 
      ),
      'handler' => '', // NOT USED YET - could later add custom functions
      'fields' => array(
        // field_name - name in the destination entity 
        // value_src_type - type of  
        // value_src_type - form_field, EntityFieldQuery, 
          // token, constant, env (environment var)
        'featureid' => array(
          'fieldname'=> 'featureid',
          'value_src_type' => 'form_key', 
          'value_val_key' => 'amount_featureid', 
        ),
        'entity_type' => array(
          'fieldname'=> 'entity_type',
          'value_src_type' => 'constant', 
          'value_val_key' => $this->eref_name, 
        ),
        'varid' => array(
          'fieldname'=> 'varid', 
          'value_src_type' => 'constant', 
          'value_val_key' => dh_varkey2varid($this->amount_varkey, TRUE), 
        ),
        'propvalue' => array (
          'fieldname'=> 'propvalue',
          'value_src_type' => 'constant', 
          'value_val_key' => $total_val, 
        ),
        'propcode' => array (
          'fieldname'=> 'propcode',
          'value_src_type' => 'constant', 
          'value_val_key' => $ra_conv[$row['rate_units']], 
        ),
      ),
      'resultid' => 'pid_amount',
    );
  }
  
  public function checkSpatial() {
    // @todo: move to parent class
    // check if it's a draft, if so, return
    switch ($dbtype) {
      case 'pgsql':
        $value = db_query('SELECT substring(PostGIS_Version() from 1 for 3)')->fetchField();
        if (empty($value)) {
          $error = 'Could not detect postGIS version - spatial imports unavailable';
          drupal_set_message($error);
          $spatial = FALSE;
        }
      break;
      case 'mysql':
        $value = db_query("SELECT asText(geomfromtext('POINT(1 1)'))")->fetchField();
        if (empty($value)) {
          $error = 'Could not detect MySQL OpenGIS Functions';
          drupal_set_message($error);
          $spatial = FALSE;
        }
      break;
    }
    return $spatial;
  }
  
  public function buildOptionsForm(&$form, $form_state) {
    // Form for configuration when adding to interface
    //   public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    // when we go to D8 this will be relevant
    // until then, we use the old school method
    parent::buildOptionsForm($form, $form_state);
    // we may total over-ride this, but use some of the other guts to do querying
  }
  
  function submitForm(array &$form, $form_state) {
    // introduce prototype code here to insure that we prevent double-click issues 
    // that save multiple properties onto the agchem links
    // this can be something that is set at the vardef level 
    // or the form_entity_map level which includes criteria for uniqueness
    // 
    parent::submitForm($form, $form_state);
  }
  
  function SubmitFormEntityMap(array &$form, $form_state) {
    // @todo: migrate this to base class dHPropertiesGroup after further testing
    // uses entity_map to handle all inserts and updates
    //dpm($form_state,'SubmitFormEntityMap');
    foreach ($form_state['values'][$this->groupname] as $record_group) {
      $form_entity_map = array();
      // set up defaults
     
      $this->FormEntityMap($form_entity_map, $record_group);
      foreach ($form_entity_map as $config) {
        $values = array();
        if (!isset($config['bundle'])) {
          $config['bundle'] = null;
        }
        $entity_type = $config['entity_type'];
        $bundle = $config['bundle'];
        // is this an edit or insert?
        // load the key
        $pk = $this->HandleFormMap($config['entity_key'], $record_group);
        // load the values array
        $values = array();
        $values['groupname'] = $this->groupname; // pass this in for special hook handling
        foreach ($config['fields'] as $key => $map) {
          if ($map['value_src_type']) {
            $values[$key] = $this->HandleFormMap($map, $record_group);
          } else {
            // @todo - throw an error or alert about malformed entry
          }
        }
        //dpm($record_group, 'record group');
        //dpm($pk, 'pk field for group');
        if ($pk) {
          // PK set, so this is an update
          //$values['pid'] = $pk;
          $e = dh_properties_enforce_singularity($values, 'singular');
          //dpm($e, "dh_properties_enforce_singularity returns entity ");
          /*
          $e = entity_load_single($entity_type, $pk);
          foreach ($values as $key => $val) {
            $e->{$key} = $val;
          }
          */
        } else {
          // no PK set, so this is an insert
          //dpm($values,'calling entity_create with values');
          $values['bundle'] = $bundle;
          $e = entity_create($entity_type, $values);
        }
        // now that values are assigned load formRowPlugins for the row and process save functions
        $this->formRowPlugins($record_group, $e, 'save');
        if ($e) {
          //dpm($e,'entity to save');
          entity_save($entity_type, $e);
        }
      }
    }
  }
}
?>