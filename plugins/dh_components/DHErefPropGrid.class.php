<?php
module_load_include('inc', 'dh', 'plugins/dh.display');

class DHErefPropGrid extends dhPropertiesGroup {
  // @todo:
    // 
  var $event_id = FALSE;
  var $env = array();
  var $prop_entity_type = FALSE;
  var $eref_entity = 'from'; // from or to
  var $eref_entity_type = FALSE; // from or to
  var $eref_target_type = FALSE; // from or to
  var $form_entity_map; // @todo - move this to parent class
  var $add = TRUE; // always add missing props
  public function __construct($conf = array()) {
    parent::__construct($conf);
    $this->prop_entity_type = isset($conf['prop_entity_type']) ? $conf['prop_entity_type'] : FALSE;
    $this->eref_entity_type = isset($conf['eref_entity_type']) ? $conf['eref_entity_type'] : FALSE;
    $this->eref_target_type = isset($conf['eref_target_type']) ? $conf['eref_target_type'] : FALSE;
    //dpm($conf,'conf');
    //dpm($this,'inv grid');
  }
  
  public function entityDefaults() {
    parent::entityDefaults();
    // get default list and order of form columns from blank form
    // HEADERS - sets 
    $this->entity_defaults['groupname'] = 'eref_props';
  }
  
  function getPropEntityInfo() {
    // this may move into generic eref handler
    if (!$this->prop_entity_type) {
      return FALSE;
    }
    $ei = array(
      'entity keys' => array(
        'id' => $this->prop_entity_type . '_erefid',
        'target' => $this->prop_entity_type . '_target_id',
      ),
      'base table' => 'field_data_' . $this->prop_entity_type
    );
    //dpm($ei, 'eref info');
    if (!isset($ei['entity keys']['id']) or !isset($ei['base table'])) {
      // fail with malformed entity exception
      //dpm($ei,"Problem with entity info from entity_get_info($this->prop_entity_type)");
      return FALSE;
    }
    // insure only numeric
    return $ei;
  }
  
  function getSourceEntityInfo() {
    // this may move into generic eref handler
    if (!$this->eref_entity_type) {
      return FALSE;
    }
    $ei = entity_get_info($this->eref_entity_type);
    if (!isset($ei['entity keys']['id']) or !isset($ei['base table'])) {
      // fail with malformed entity exception
      //dpm($ei,"Problem with entity info from entity_get_info($this->prop_entity_type)");
      return FALSE;
    }
    // insure only numeric
    return $ei;
  }
  
  function getTargetEntityInfo() {
    // this may move into generic eref handler
    if (!$this->prop_entity_type) {
      return FALSE;
    }
    $tei = entity_get_info($this->eref_target_type);
    if (!isset($tei['entity keys']['id']) or !isset($tei['base table'])) {
      // fail with malformed entity exception
      dpm($ei,"Problem with entity info from entity_get_info($this->eref_target_type)");
      return FALSE;
    }
    // insure only numeric
    return $tei;
  }
  
  public function prepareQuery() {
    //parent::prepareQuery();
    //dpm($this, "DHErefPropGrid");
    $this->applyEntityTokens();
    $this->applySettings();
    //dpm($this, "DHErefPropGrid after settings");
    //parent::prepareQuery();
    $this->featureid = array_filter($this->featureid, 'is_numeric');
    $er = $this->getPropEntityInfo();
    if (!$er) {
      return FALSE;
    }
    $eref_pkcol = $er['entity keys']['id'];
    $eref_tbl = $er['base table'];
    $eref_target = $er['entity keys']['target'];
    $ei = $this->getSourceEntityInfo();
    if (!$ei) {
      return FALSE;
    }
    $eidcol = $ei['entity keys']['id'];
    $eref_entity_table = $ei['base table'];
    //dpm($ei,'Entity info');
    // @todo: put these into play
    $tei = $this->getTargetEntityInfo();
    if (!$tei) {
      return FALSE;
    }
    //dpm($tei,'Target Entity info');
    $tidcol = $tei['entity keys']['id'];
    $tlabel = $tei['entity keys']['label'];
    $eref_target_entity_table = $tei['base table'];
    // get varid
    // create a query that outer joins if insert ability is requested
    $q = "  select var.hydroid as varid, ";
    $q .= " var.varname, var.varkey, ";
    $q .= " p.*, var.varunits, ";
    $q .= " targ.$tlabel as target_label, ";
    $q .= " ent.$eidcol as from_id, eref.$eref_target as target_id ";
    $q .= " from {$eref_entity_table} as ent ";
    $q .= " left outer join {$eref_tbl} as eref ";
    $q .= " on ( ";
    $q .= "   eref.entity_id = ent.$eidcol ";
    $q .= "   AND eref.entity_type = '$this->eref_entity_type' ";
    $q .= " ) ";
    $q .= " left outer join {$eref_target_entity_table} as targ ";
    $q .= " on ( ";
    $q .= "   eref.$eref_target = targ.$tidcol ";
    $q .= " ) ";
    $q .= " left outer join {dh_variabledefinition} as var ";
    if (count($this->varid) > 0) {
      $v1 = $this->varid[min(array_keys($this->varid))];
      if (intval($v1) > 0) {
        $varids = implode(", ", $this->varid);
        $q .= " on (var.hydroid in ($varids)) ";
      } else {
        $varids = implode("', '", $this->varid);
        $q .= " on (var.varkey in ('$varids')) ";
      }
    } else {
      // fail with malformed query exception - 
      //dpm($this->varid, "malformed query - no varid specified");
      drupal_set_message("malformed query - no varid specified");
      return FALSE;
    }
    $q .= " left outer join {dh_properties} as p ";
    $q .= " on ( ";
    $q .= "   p.featureid = eref.$eref_pkcol ";
    $q .= "   AND var.hydroid = p.varid ";
    $q .= "   AND p.entity_type = '$this->prop_entity_type' ";
    $q .= " ) ";
    // we must have a match in the TS table
    $q .= " WHERE var.hydroid IS NOT NULL ";
    if (count($this->featureid) > 0) {
      $features = implode(", ", $this->featureid);
      $q .= " AND (ent.$eidcol in ($features)) ";
      //$q .= " AND (ent.$eref_target in ($features)) ";
    }
    $q .= " LIMIT $this->limit ";
    $q .= " ORDER BY targ.$tlabel ";
    $this->query = $q;
    //dpm($q, "Query");
    //dpm($this->query, "Query");
  }
  
  function getData() {
    if (!isset($this->query) or !$this->query) {
      // malformed or non existent query
      return FALSE;
    }
    $this->data = array();
    $q = db_query($this->query);
    //dpm($q, "initial data");
    foreach ($q as $prow) {
      if ($prow->pid == NULL) {
        // this is an insert request
        $prow->propname = $prow->varkey;
        $prow->entity_type = $this->prop_entity_type;
        //dpm($prow, "Creating blank");
        $blank = entity_create($this->base_entity_type, (array)$prow);
        //dpm($blank, "before applying defaults");
        $this->applyRowDataDefaults($blank);
        //dpm($blank, "after applying defaults");
        $this->data[] = $blank;
      } else {
        // @todo: move this entity_load into a plugin method to allow sub-classes to override and add info
        $dh_properties = array_shift(entity_load($this->base_entity_type, array($prow->pid)));
        // add variable info
        $dh_properties->varunits = $prow->varunits;
        $dh_properties->varname = $prow->varname;
        // add eref info
        $dh_properties->from_id = $prow->from_id;
        $dh_properties->target_id = $prow->target_id;
        $dh_properties->target_label = $prow->target_label;
        $this->data[] = $dh_properties;
      }
    }
    //dpm($this->data, "Final data");
    // now, go through the returned data and if we have "show_blank" property set
    // we append new object form entries for these
    // create a matrix of entity_type, property_conditions & field_conditions?
    // a better approach would be to use a query that would return prefilled null records like an outer join
  }
  
  public function fieldOptions(&$form, $form_state) {
    $form['entity_settings'][$this->groupname]['display']['fields'] = $this->fieldElements();
  }
  
  public function propertyOptions(&$form, $form_state) {
    $form['entity_settings'][$this->groupname]['display']['properties'] = $this->propertyElements();
  }
  
  public function entityOptions(&$form, $form_state) {
    // @todo: this is NOT YET FUNCTIONAL - CAN ONLY BE CALLED BY CODE
    //parent::entityOptions($form, $form_state);
    if (!isset($form['entity_settings'])) {
      $form['entity_settings'] = array(
      );
    }
    if (!isset($form['entity_settings'][$this->groupname])) {
      $form['entity_settings'][$this->groupname] = array(
        '#type' => 'fieldset',
        '#tree' => TRUE,
        '#title' => $this->group_title,
        '#collapsible' => TRUE,
        '#collapsed' => FALSE,
        '#description' => $this->group_description,
      );
    }
    
    $form['entity_settings'][$this->groupname]['featureid'] = array(
      '#title' => t('Feature IDs'),
      '#type' => 'textfield',
      '#default_value' => (strlen($this->conf['featureid']) > 0) ? $this->conf['featureid'] : NULL,
      '#description' => t('What entity id to retrieve TS values for.'),
      '#size' => 30,
      '#required' => FALSE,
    );  
    $addoptions = array(0 => t('FALSE'), 1 => t('TRUE'));
    $form['entity_settings'][$this->groupname]['add'] = array(
      '#title' => 'Add New Records?',
      '#type' => 'select',
      '#default_value' => isset($this->conf['add']) ? $this->conf['add'] : 0,
      '#description' => t('Checking this will enable Add Form.  The specific handler sub-class must opt to handle this.  The default form will not support this (currently).'),
      '#options' => $addoptions,
    );
    $conditions = array();
    $options = dh_vardef_vocab_options(TRUE);
    $form['entity_settings'][$this->groupname]['vocabulary'] = array(
      '#title' => t('Vocabulary'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => (count($this->conf['vocabulary']) > 0) ? $this->conf['vocabulary'] : NULL,
      '#description' => t('What vocabulary to retrieve variables for - must save and re-open to update variable list.'),
      '#size' => 5,
      '#multiple' => TRUE,
      '#required' => FALSE,
    );
    $conditions = array();
    if (count($this->conf['vocabulary']) > 0) {
      $vocab_clause = "vocabulary in ( '" . implode("', '", $this->conf['vocabulary']) . "')";
      //dpm($vocab_clause, "Vocab Clause");
      $conditions = array($vocab_clause);
    }
    $options = dh_vardef_varselect_options($conditions);
    $form['entity_settings'][$this->groupname]['varid'] = array(
      '#title' => t('Variables'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => (count($this->conf['varid']) > 0) ? $this->conf['varid'] : NULL,
      '#description' => t('What varid to retrieve TS values for.'),
      '#size' => 12,
      '#multiple' => TRUE,
      '#required' => FALSE,
      '#prefix' => "<div id='update-varid-options'>",
      '#suffix' => '</div>',
    );
    //error_log("Showing the entity selector");
    // @todo: show entity choices here, then have a chained list below that to select entity references
    //        Or, alternatively, show entity references, then a multi-select list of entities that use that
    //        entity reference to choose from/restrict query to
    $entities = entity_get_info();
    $form['entity_settings'][$this->groupname]['prop_entity_type'] = array(
      '#title' => t('Entity Type'),
      '#type' => 'select',
      '#options' => array_combine( array_keys($entities) , array_keys($entities) ),
      '#default_value' => !empty($this->prop_entity_type) ? $this->prop_entity_type : 'dh_feature',
      '#description' => t('Entity Type'),
      '#required' => TRUE,
    );
    //error_log("Finished the entity selector" . print_r($form['entity_settings']['prop_entity_type'],1));
    $form['entity_settings'][$this->groupname]['id'] = array(
      '#title' => t('Properties IDs'),
      '#type' => 'textfield',
      '#default_value' => (strlen($this->conf['id']) > 0) ? $this->conf['id'] : NULL,
      '#description' => t('What pid to retrieve Property values for.'),
      '#size' => 30,
      '#required' => FALSE,
    );
    $form['entity_settings'][$this->groupname]['addurl'] = array(
      '#title' => t('Add URL'),
      '#type' => 'textfield',
      '#default_value' => (strlen($this->conf['addurl']) > 0) ? $this->conf['addurl'] : NULL,
      '#description' => t('URL For add screen (tokens allowed).'),
      '#size' => 30,
      '#required' => FALSE,
    );  
    $form['entity_settings'][$this->groupname]['editurl'] = array(
      '#title' => t('Edit URL'),
      '#type' => 'textfield',
      '#default_value' => (strlen($this->conf['editurl']) > 0) ? $this->conf['editurl'] : NULL,
      '#description' => t('URL For edit screen (tokens allowed).'),
      '#size' => 30,
      '#required' => FALSE,
    );  
    $erefs = field_read_fields(array('type' => 'entityreference'));
    $eref_opts = array();
    $eref_keys = array_keys($erefs);
    $eref_opts = array_combine($eref_keys, $eref_keys);
    //dpm($erefs,"Erefs");
    $form['entity_settings'][$this->groupname]['prop_entity_type'] = array(
      '#type' => 'select',
      '#title' => 'Choose Entity Reference',
      '#default_value' => (strlen($this->conf['prop_entity_type']) > 0) ? $this->conf['prop_entity_type'] : NULL,
      '#options' => $eref_opts,
    );
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
}
?>