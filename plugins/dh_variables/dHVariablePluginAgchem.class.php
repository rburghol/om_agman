<?php
module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('inc', 'om_agman', 'src/lib/om_agman_frac');
$plugin_def = ctools_get_plugins('dh', 'dh_variables', 'dHVariablePluginAgmanAction');
$class = ctools_plugin_get_class($plugin_def, 'handler');

class dHVariablePluginEfficacy extends dHVariablePluginDefault {
  
  public function effAbbrev() {
    return array(
      '' => '--',
      1 => 'E',
      2 => 'G',
      3 => 'G_F',
      4 => 'F',
      5 => 'P',
      6 => 'N',
      7 => 'Lab',
      8 => 'Var',
    );
  }
  
  public function effFull() {
    return array(
      '' => 'Unknown',
      1 => 'Excellent',
      2 => 'Good',
      3 => 'Good/Fair',
      4 => 'Fair',
      5 => 'Poor',
      6 => 'None',
      7 => 'Labelled',
      8 => 'Variable',
    );
  }
  
  public function get_eff_tables() {
    // 0 is the worst, but 1 is the best.
    // how about Efficacy ranking?
    $eff_tables = array(
      'efficacy_abbrev' => $this->effAbbrev(),
      'efficacy_full' => $this->effFull(),
      'efficacy_ranking' => array(
        '' => -10, // not available is always the worst, unless something promotes the target!
        0 => -10, // not available is always the worst, unless something promotes the target!
        1 => 10, // Excellent 10 is top ranked (until we have Hors Categorie)
        2 => 8, // Good
        3 => 6, // Good/Fair
        4 => 5, // Fair
        5 => 4, // perfect
        6 => -10, // none 
        7 => 4, // Labelled is akin to poor
        8 => 5, // variable is equated with Fair
      ),
      'efficacy_sym' => array(
        0=> '?',
        1=> '++',
        2=> '+',
        3=> '+',
        4=> '-',
        5=> '--',
        6=> '∅',
        7=> 'L',
      ),
      'efficacy_color' => array(
        0=> '#e5e5e5',
        1=> '#009900',
        2=> '#33b233',
        3=> '#66cc66',
        4=> '#993599',
        5=> '#ccffcc',
        6=> '#ffffff',
        7=> '#993599',
      )
    );
    return $eff_tables;
  }
  
  public function rank_efficacies($effs) {
    // sorts through the convoluted hierarchy of ranks and chooses the best one.
    $tables = $this->get_eff_tables();
    $ranks = $tables['efficacy_ranking'];
    $best_rank = min(array_values($ranks));
    $best_eff = 0;
    foreach ($effs as $eff) {
      $rank = isset($ranks[$eff]) ? $ranks[$eff] : min(array_values($ranks));
      if ($rank > $best_rank) {
        $best_rank = $rank;
        $best_eff = $eff;
      }
    }
    //if (!empty($effs)) {
    //  dpm($effs,'effs to best eff: ' . $best_eff . " and rank: " . $best_rank);
    //}
    return $best_eff;
  }
  
  public function formRowEdit(&$form, $entity) {
    $form['propcode']['#type'] = 'hidden';
    $form['propvalue']['#type'] = 'select';
    $form['propvalue']['#options'] = $this->effFull();
    $form['propvalue']['#default_value'] = $entity->propvalue;
    $form['propvalue']['#size'] = 1;
    $form['propvalue']['#multiple'] = FALSE;
  }
  
  public function save(&$entity) {
    $abbrevs = $this->effAbbrev();
    $entity->propcode = $abbrevs[$entity->propvalue];
    return parent::save($entity);
  }
  
}

class dHVariablePluginAgchemAI extends dHVariablePluginDefault {
  
  public function hiddenFields() {
    return array('pid', 'varid', 'featureid', 'entity_type', 'bundle', 'dh_link_admin_pr_condition');
  }
  
  public function aiList() {
    $aivarid = dh_varkey2varid('agchem_ai', TRUE);
    $q = "  select propcode as key, propcode as val from dh_properties ";
    $q .= " where varid = $aivarid ";
    $q .= " group by propcode ";
    $q .= " order by propcode ";
    $result = db_query($q);
    return $result->fetchAllKeyed();
  }
  
  public function formRowEdit(&$form, $entity) {
    $form['propvalue']['#title'] = '% Active Ingredient';
    $ailist = $this->aiList();
    $form['propcode']['#type'] = 'textfield';
    $form['propcode']['#title'] = 'a.i. Name';
    $form['propcode']['#maxlength'] = 128;
    $form['propcode']['#autocomplete_path'] = 'om_agman/active_ingredient';
    $form['propcode']['#multiple'] = FALSE;
    foreach ($this->hiddenFields() as $hide_this) {
      $form[$hide_this]['#type'] = 'hidden';
    }
  }
  
  public function save(&$entity) {
    $entity->propname = $entity->propcode;
    return parent::save($entity);
  }
  
}

class dHVariablePluginAgchemPHI extends dHVariablePluginDefault {
      
  public function buildContent(&$content, &$entity, $view_mode) {
    parent::buildContent($content, $entity, $view_mode);
    switch ($view_mode) {
      case 'tiny':
      // note, this is not detailed in the modes available in dh.module, so this will not be available in Views
        $content = array();
        $content['body'] = array(
          '#type' => 'item',
          '#markup' => "<b>Harvest Allowable as of </b> " . date('Y-m-d h:m:s', dh_handletimestamp($entity->tsendtime)) . " (PHI Chems: $entity->tscode)",
        );
      break;
    }
  }
  
  public function formRowEdit(&$form, $entity) {
    $form['propcode']['#type'] = 'hidden';
    $form['propvalue']['#suffix'] = ' days';
  }
  
  public function findPHIEvent($entity, $startdate, $enddate) {
    // @todo: Is this function needed? 
    /*
    // load the block feature that the ts is attached to 
    // Find all spray events on this block
    // load the spray event, check it's phi date
    // update this PHI date if > $phi_ts->tstime 
    // @todo: handle next generation which will be a timeseries event for every block including application amounts, etc.
    $entity_type = $entity->entityType();
    $entity_id = $entity->identifier();
    // get all events dh_adminreg_feature of agchem_app_plan with links to blockid joined to ts value of adminreg feature 
    $q = "  select tid from dh_timeseries ";
    $q .= " where featureid = $entity_id ";
    $q .= "   and entity_type = $entity_type ";
    $q .= "   and tstime >= " . dh_handletimestamp($startdate);
    $q .= "   and tsendtime <= " . dh_handletimestamp($enddate);
    $q .= "   and varid in (select hydroid from dh_variabledefinition where varkey = 'event_dh_link_submittal_feature') ";
    $result = db_query($q);
    
    foreach ($result as $record) {
      // get events
      // Load some entity.
      $dh_ts = entity_load_single('dh_timeseries', $record->tid);
      // load agchem timeseries associate with this event because that is what calculates the PHI / REI 
      $values = array(
        'entity_type' => 'dh_adminreg_feature',
        'featureid' = $dh_ts->tsvalue,
        'varkey' => 'agchem_application_event',
        'tstime' => dh_handletimestamp($startdate),
        'tsendtime' => dh_handletimestamp($enddate),
      );
      $phi_ts = NULL;
      $efq_result = dh_get_timeseries($values, 'trange');
      $data = entity_load('dh_timeseries', array_keys($efq_result['dh_timeseries']));
      foreach ($data as $app_ts) {
        // load the app plugin,
        $plugin = array_shift($app_ts->dh_variables_plugins);
        // if the plugin has the proper methods to load the feature and calculate PHI, then we can check it 
        // check PHI date against the latest PHI found yet 
        // @todo: Finish the commented out block
        if (($phi_ts === NULL) or ($app_ts->tstime > $phi_ts->tstime) ) {
          $phi_ts = array(
            'entity_type' => $entity_type,
            'featureid' => $entity_id,
            'varkey' => ???
            'tstime' => $phitime,
            'tsendtime' => $event_endtime,
          );
        }
      }
      if (!($phi_ts === NULL)) {
        // if we have found any events with a valid PHI setting, we will have a 
      }
      echo "saved $record->tid \n";
    }
    */
    // END - findPHIEvent
  }
}

class dHVariablePluginAgchemREI extends dHVariablePluginDefault {
  
  public function reiCode() {
    return array(
      'all' => 'All',
      'ptg' => 'Pruning, Tying, Girdling',
      'other' => 'Other',
    );
  }
  
  public function formRowEdit(&$form, $entity) {
    $form['propcode']['#type'] = 'select';
    $form['propcode']['#options'] = $this->reiCode();
    $form['propcode']['#default_value'] = !empty($entity->propcode) ? $entity->propcode : 'all';
    $form['propcode']['#size'] = 1;
    $form['propcode']['#multiple'] = FALSE;
    // value
    $form['propvalue']['#suffix'] = ' hours';
  }
  
  public function save(&$entity) {
    return parent::save($entity);
  }
}

class dHVariablePluginAgchemMaxApps extends dHVariablePluginDefault {
  
  public function formRowEdit(&$form, $entity) {
    $form['propcode']['#type'] = 'hidden';
    $form['propcode']['#prefix'] = 'per year';
  }
}

class dHVariablePluginFRAC extends dHVariablePluginDefault {
  public function formRowEdit(&$form, $entity) {
    $form['propvalue']['#type'] = 'hidden';
    // original codes from: http://www.frac.info/publications/downloads
    // see edited list in G: https://docs.google.com/spreadsheets/d/1cktc0J5jkIcCd7GPI109dwvLebBmWFHqLbxrxP4032Y/edit#gid=1074563920
    $fracs = array('4', '8', '32', '31', '1', '10', '22', '20', '43', '47', '39', '7', '11', '21', '29', '30', '38', '45', '9', '23', '24', '25', '41', '13', '12', '2', 'n.a.', '6', '14', '28', '44', '46', '48', '49', '3', '5', '17', '18', '26', '19', '40', '16.1', '16.2', '16.3', '27', '33', '34', '35', '36', '37', '42', '50', 'M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'NC', 'U06', 'U08', 'U12', 'U13', 'U14', 'U16', 'U17', 'U18', 'P01', 'P02', 'P03', 'P04', 'P05', 'P06', 'P07', 'BM01', 'BM02');
    sort($fracs);
    $frac_options = array_combine($fracs, $fracs);
    // fixes bad abbreviations
    $fixes = array('M1' => 'M01', 'M2' => 'M02', 'M3' => 'M03', 'M4' => 'M04', 'M5' => 'M05', 'M6' => 'M06', 'M7' => 'M07', 'M8' => 'M08', 'M9' => 'M09', 'U6' => 'U06', 'U8' => 'U08', 'P1' => 'P01', 'P2' => 'P02', 'P3' => 'P03', 'P4' => 'P04', 'P5' => 'P05', 'P6' => 'P06', 'BM1' => 'BM01', 'BM2' => 'BM02');
    $selected = !empty($entity->propcode) ? $entity->propcode : FALSE;
    $selected = isset($fixes[$selected]) ? $fixes[$selected] : $selected;
    array_replace($selected, $letter_fracs);
    $form['propcode']['#type'] = 'select';
    $form['propcode']['#options'] = $frac_options;
    $form['propcode']['#default_value'] = array($selected);
    $form['propcode']['#size'] = 1;
    $form['propcode']['#empty_option'] = t('- Select -');
    $form['propcode']['#multiple'] = FALSE;
    if (!($selected)) {
      $form['propcode']['#default_value'] = array();
    }
    $form['#weight'] = 1;
  }
  
  public function buildContent(&$content, &$entity, $view_mode) {
    // special render handlers when using a content array
    // get all FRAC Codes associated with this entity
    $values = array(
      'varid' => $entity->varid,
      'featureid' => $entity->featureid,
      'entity_type' => $entity->entity_type,
    );
    $result = dh_get_properties($values, 'all');
    if (isset($result['dh_properties'])) {
      $frac_pids = array_keys($result['dh_properties']);
      $frac_obs = entity_load('dh_properties', $frac_pids);
    }
    //dpm($frac_obs,'frac obs');
    $fracs = array();
    foreach ($frac_obs as $frac) {
      $fracs[] = $frac->propcode;
    }
    switch($view_mode) {
      default:
        $content['title'] = array(
          '#type' => 'item',
          '#markup' => implode(',', $fracs),
        );
      break;
    }
  }
  
  public function insert(&$entity) {
    $entity->propname = 'frac:' . $entity->propcode;
    return parent::insert($entity);
  }
  public function update(&$entity) {
    $entity->propname = 'frac:' . $entity->propcode;
    return parent::update($entity);
  }
  public function save(&$entity) {
    $entity->propname = 'frac:' . $entity->propcode;
    return parent::save($entity);
  }
  
}

class dHVariablePluginSimpleFertilizer extends dHVariablePluginAgmanAction {
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
  
  public function hiddenFields() {
    return array('tid', 'entity_type', 'bundle', 'varid', 'tsendtime');
  }

  function process_npk($code) {
    $keys = array('n', 'p', 'k');
    $element = array();
    //$element['#parents'] = !isset($element['#parents']) ? array() : $element['#parents'];
    if (empty($code)) {
      $code = '0-0-0';
    }
    $vals = explode('-', $code);
    foreach ($keys as $key) {
      $val = array_shift($vals);
      $element[$key] = array(
        '#input' => TRUE,
        '#prefix' => strtoupper($key),
        '#type' => 'textfield',
        '#default_value' => $val,
        '#size' => 3,
        '#width' => 3,
        '#maxlength' => 6,
        '#required' => FALSE,
        '#parents' => array(),
      );
      $element[$key]['#parents'][] = $key;
    }
    return $element;
  }
  
  public function formRowEdit(&$rowform, $row) {
    parent::formRowEdit($rowform, $row);
    // apply custom settings here
    //dpm($row,'row');
    $varinfo = $row->varid ? dh_vardef_info($row->varid) : FALSE;
    if (!$varinfo) {
      return FALSE;
    }
    $codename = $this->row_map['code'];
    /*
    // I think this is no longer used?
    list($n, $p, $k) = explode('-',$row->$codename);
    $vals = array(
      'n'=>$n,
      'p'=>$p,
      'k'=>$k,
    );
    */
    $date_format = 'Y-m-d';
    $rowform['tstime']['#type'] = 'date_popup';
    $rowform['tstime']['#date_format'] = $date_format;
    $rowform['tstime']['#default_value'] = empty($row->tstime) 
      ? date($date_format) 
      : date($date_format,$row->tstime)
    ;
    // @todo: figure out how to make a custom widget
    
    //$rowform[$codename] = array(
    //  '#type' => 'agchem_npk',
    //
    
    $ra_units = array(
      'lbs' => 'lbs',
      'oz' => 'oz',
      'tons' => 'tons',
      'kg' => 'kg',
      'g' => 'g',
    );
    $rowform['units']['#type'] = 'select';
    $rowform['units']['#options'] = $ra_units;
    $rowform['units']['#size'] = 1;
    $unit_rec = array(
      'varid' => dh_varkey2varid('agchem_rate_type', TRUE),
      'featureid' => $row->tid,
      'entity_type' => 'dh_timeseries',
    );
    $unit_selected = dh_properties_enforce_singularity($unit_rec, 'singular');
    //dpm($unit_selected,'unit ');
    $rowform['units']['#default_value'] = $unit_selected->propcode;
    
    $rowform[$codename]['#type'] = 'hidden';
    $pieces = $this->process_npk($row->$codename);
    //dpm($rowform[$codename]);
    $group = new EntityGroupConfigurator;
    $group->render_layout = 'table';
    $group->form_columns = array('n', 'p', 'k');
    $group->data = array(0 => $pieces);
    $blank = array();
    $group->tabularize($pieces, 'odd');
    $rowform['n-p-k'] = array();
    $rowform['n-p-k'][] = array(
      '#markup' => "<table>",
    );
    $rowform['n-p-k'][] = $pieces;
    $rowform['n-p-k'][] = array(
      '#markup' => "</table>",
    );
    
    //dpm($rowform,'raw form');
  }
  
  public function formRowSave(&$rowvalues, &$row) {
    //dpm($rowvalues,'save');
    parent::formRowSave($rowvalues, $row);
    $codename = $this->row_map['code'];
    $row->$codename = implode('-', array($rowvalues['n'], $rowvalues['p'], $rowvalues['k']));
    $unit_rec = array(
      'varid' => dh_varkey2varid('agchem_rate_type', TRUE),
      'propname' => 'units',
      'propcode' => $rowvalues['units'],
      'propvalue' => NULL,
      'bundle' => 'dh_properties',
      'featureid' => $row->tid,
      'entity_type' => 'dh_timeseries',
    );
    dh_update_properties($unit_rec, 'propcode_singular');
    //dpm($row);
    // special save handlers
    // amount, and concentration (%) of each element
    // varkeys = chem_fraction (code = formula/symbol), chem_amount(code = formula/symbol) 
    $constits = array(
      'n', 'p', 'k'
    );
    foreach ($constits as $con) {
      $conc_rec = array(
        'varid' => dh_varkey2varid('chem_pct', TRUE),
        'propname' => '%' . $con,
        'propvalue' => $rowvalues[$con],
        'propcode' => $con,
        'bundle' => 'dh_properties',
        'featureid' => $row->tid,
        'entity_type' => 'dh_timeseries',
      );
      dh_update_properties($conc_rec, 'propcode_singular');
      // inherits units from tsevent tscode
      $amt_rec = array(
        'varid' => dh_varkey2varid('chem_amount', TRUE),
        'propname' => 'Total ' . $con,
        'propvalue' => $rowvalues['tsvalue'] * $rowvalues[$con] / 100.0,
        'bundle' => 'dh_properties',
        'propcode' => $con,
        'featureid' => $row->tid,
        'entity_type' => 'dh_timeseries',
      );
      $amt[$con] = $rowvalues['tsvalue'] * $rowvalues[$con] / 100.0;
      dh_update_properties($amt_rec, 'propcode_singular');
    }
    drupal_set_message("Saved fertilizer event, $rowvalues[tsvalue] $rowvalues[units] of $rowvalues[tscode] for total of " . implode('-',$amt) ."$rowvalues[units] of each on " . date('Y-m-d', dh_handletimestamp($rowvalues['tstime'])));
  }

}


class dHAgchemApplicationEvent extends dHVariablePluginDefault {
  // a timeseries attached to the adminreg feature spray event to hold summary info and easy access for calendars etc.
  public function editLink($entity) {
    $feature = $this->getParentEntity($entity);    
    $uri = "ipm-live-events/" . $feature->vineyard->hydroid . "/sprayquan/$feature->adminid&finaldest=$page";
    $link = array(
      '#type' => 'link',
      '#prefix' => '&nbsp; ',
      '#suffix' => '<br>',
      '#title' => 'Go to ' . $uri,
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
  
  public function printLink($entity) {
    $feature = $this->getParentEntity($entity);    
    $uri = "dh_adminreg_feature/" . $feature->vineyard->hydroid . "/print/" . $feature->bundle;
    $link = array(
      '#type' => 'link',
      '#prefix' => '&nbsp; ',
      '#suffix' => '<br>',
      '#title' => 'Go to ' . $uri,
      '#href' => $uri,
      'query' => array(
        'finaldest' => $page,
      ),
      '#options' => array(
        'attributes' => array(
           'class' => array('print-page')
         ),
      ),
    );
    return $link;
    
  }
  
  public function viewLink($entity) {
    
  }
  
  public function dh_getValue($entity, $ts = FALSE, $propname = FALSE, $config = array()) {
    // Get and Render Chems & Rates
    $feature = $this->getParentEntity($entity);
    $this->load_event_info($feature);
    $args = arg();
    $page = (strlen($args[0]) > 0) ? $args[0] : 'ipm-home';
    if (strpos($page, 'ical') !== false) {
      // catch this -- hack
      // @todo: make finaldest part of the entity render plugin in views so we don't have to do this
      $page = 'ipm-home';
    }
    switch ($propname) {
      case 'event_title':
        $title = $feature->vineyard->name . ": " . $feature->name . ' on ' . $feature->block_names;
        return $title;
      break;
      case 'event_description':
        $title = "<b>What:</b>" . $feature->vineyard->name . " - " . $feature->name;
        $description = $title . ' on ' . $feature->block_names;
        $description .= " - " . $feature->agchem_spray_vol_gal->propvalue . " gals H2O";
        $description .= '\nw/' . $feature->chem_list;
        $description .= '\nPHI:' ."$feature->phi_date ($feature->phi_chem)";
        $description .= '\nREI:' ."$feature->rei_date ($feature->rei_chem)";
        $description .= "<br><div class='small'>Note: Pre-Harvest Interval (PHI) and Re-Entry Intervals (REI) are based on the material with the longest interval.</div>";
        // see docs for drupal function l() for link config syntax
        // get list of blocks
        // get list of chems
        $uri = token_replace("[site:url]ipm-live-events/" . $feature->vineyard->hydroid . "/sprayquan/$feature->adminid&finaldest=$page");
        $description .= l('\nView:' . $uri, $uri, array('absolute' => TRUE));
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
  
  public function load_event_info(&$feature, $reload = FALSE) {
    // given an adminreg event feature, returns the chems and their attributes
    if ($feature->loaded and !$reload) {
      return;
    }
    $chems = array();
    $chem_names = array();
    $field_chems = field_get_items('dh_adminreg_feature', $feature, 'field_link_to_registered_agchem');
     foreach ($field_chems as $to) {
      $chems[$to['target_id']] = array(
        'adminid' => $to['target_id'],
        'eref_id' => $to['erefid'],
      );
    }
    $feature->chems = $chems;
    $feature->enddate = 
      (empty($feature->enddate) or ($feature->enddate < $feature->startdate)) 
      ? $feature->startdate + 3600 
      : $feature->enddate;
    $vol_info = array(
      'featureid' => $feature->adminid,
      'entity_type' => 'dh_adminreg_feature',
      'bundle' => 'dh_properties',
      'varkey' => 'agchem_spray_vol_gal',
    );
    $vol_prop = dh_properties_enforce_singularity($vol_info, 'singular');
    $batch_info = $vol_info;
    $batch_info['varkey'] = 'agchem_batch_vol';
    $feature->agchem_batch_vol = dh_properties_enforce_singularity($batch_info, 'singular');
    $area_info = $vol_info;
    $area_info['varkey'] = 'agchem_event_area';
    $feature->agchem_event_area = dh_properties_enforce_singularity($area_info, 'singular');
    //dpm($vol_prop,'vol prop');
    // PHI Defaults
    $feature->phi_ts = $feature->enddate;
    $feature->phi_chems = array(); // chem w/limiting PHI
    $feature->phi_info = 'unknown'; // chem w/limiting PHI
    $feature->agchem_spray_vol_gal = $vol_prop;
    $feature->event_fracs = array(); // list of fracs
    // REI Defaults
    $feature->rei_ts = $feature->enddate;
    $feature->rei_chems = array(); // chem w/limiting PHI
    $chem_details = array(); // chem w/limiting PHI
    $feature->rei_info = 'unknown'; // chem w/limiting PHI
    foreach ($feature->chems as $cix => $cheminfo) {
      $chem = entity_load_single('dh_adminreg_feature', $cheminfo['adminid']);
      // load fracs for this chem 
      $frac_info = array('featureid' => $chem->adminid, 'entity_type'=>'dh_adminreg_feature', 'propname' => 'FRAC Codes');
      $chem_fracs = om_model_getSetProperty($frac_info, 'name', FALSE);
      //dpm($chem_fracs,'chem frac prop');
      $frac_plugin = dh_variables_getPlugins($chem_fracs);
      if (is_object($frac_plugin)) {
        $c_fracs = explode(',', $frac_plugin->getCodeList($chem_fracs));
        //dpm($c_fracs,'fracs');
        $feature->event_fracs = array_unique( array_merge($feature->event_fracs, $c_fracs ) );
      }
      // load base linked props info
      $chem_pi = array(
        'featureid' => $cheminfo['eref_id'],
        'entity_type' => 'field_link_to_registered_agchem',
        'bundle' => 'dh_properties'
      );
      // per acre/volume rate used
      $rate_pi = $chem_pi + array('propname' => 'agchem_rate', 'varkey' => 'agchem_rate');
      $chem->rate = dh_properties_enforce_singularity($rate_pi, 'singular');
      // total amount to mix/apply
      $amt = array(
        'featureid' => $cheminfo['eref_id'],
        'entity_type' => 'field_link_to_registered_agchem',
        'bundle' => 'dh_properties',
        'varkey' => 'agchem_amount',
      );
      $chem->amount = dh_properties_enforce_singularity($amt, 'singular');
      // amount units (from chem)
      $amt_unit = array(
        'featureid' => $chem->adminid,
        'entity_type' => 'dh_adminreg_feature',
        'bundle' => 'dh_properties',
        'varkey' => 'agchem_amount_type',
      );
      $chem->units = dh_properties_enforce_singularity($amt_unit, 'singular');
      // @todo: create and use properties plugin to render rate and amounts info
      
      // REI
      // @todo: create and use properties plugin to render REI info
      $this->getREIInfo($feature, $chem);
      // PHI
      // @todo: create and use properties plugin to render PHI info
      $this->getPHIInfo($feature, $chem);
      
      $chem_names[] = $chem->name . ' @ ' . $chem->amount->propvalue . ' ' . $chem->units->propcode;
      $chem_details[] = array(
        'name' => $chem->name,
        'rate' => $chem->rate->propvalue, 
        'amount' => $chem->amount->propvalue, 
        'units' => $chem->units->propcode
      );
      $feature->chems[$cix] = $chem;
    }
    $chem_list = implode(', \n', $chem_names);
    $feature->chem_items = $chem_names;
    $feature->chem_details = $chem_details;
    $feature->chem_list = $chem_list;
    // Handle Final PHI Date & REI Date
    $this->getPHIDate($feature);
    $this->getREIDate($feature);
    // load block and vineyard info
    $blocks = array();
    $blocks_names = array();
    $field_blocks = field_get_items('dh_adminreg_feature', $feature, 'dh_link_feature_submittal');
    foreach ($field_blocks as $to) {
      $blocks[] = $to['target_id'];
    }
    $feature->block_entities = entity_load('dh_feature', $blocks);
    foreach ($feature->block_entities as $fe) {
      $block_names[] = $fe->name;
      if (!property_exists($feature, 'vineyard')) {
        $vid = dh_getMpFacilityHydroId($fe->hydroid);
        if ($vid) {
          $feature->vineyard = entity_load_single('dh_feature', $vid);
        }
      }
    }
    $feature->block_names = implode(', ', $block_names);
    //dpm($feature,'feature');
    $feature->loaded = TRUE;
  }
  
  public function save(&$entity) {
    //error_log("$entity->varname save() called");
    parent::save($entity);
  }
  public function update(&$entity) {
    //error_log("$entity->varname update() called");
    parent::update($entity);
    $feature = $this->getParentEntity($entity);
    //dpm($feature,'feature');
    $this->load_event_info($feature);
    $this->setBlockPHI($entity, $feature);
    $this->setBlockREI($entity, $feature);
    // because of the handling ot dh_entity_ts_event hooks, this gets called twice every save() of an adminreg feature.  
    // this causes the messages to be sent out twice, which is a UI/UX problem.
    // thus, calls to checkFracStatus must be done by request only.
    //$this->checkFracStatus($entity, $feature);
    // Add additional plumbing to copy relevant data to this event and to the linked TS events for each block.
    // must include smart handling for blocks that have been removed from the event 
    // since linked events are a sub-type of linked event master class, we have a reference to the original event 
    // as tsvalue = tid of this event, therefore, we can query for events that have tsvalue = tid whose featureid is NOT in this list of blocks
    // - uses dh_link_feature_submittal to connect blocks to adminreg events 
    //    - the adminreg feature already has the loaded dh_feature entities included in an array "block_entities" so we can omit the dh_link... if desired
  }
  
  public function insert(&$entity) {
    parent::insert($entity); 
    //dpm($entity,'entity');
    //$this->load_event_info($feature);
    //$this->setBlockPHI($feature);
    //$this->setBlockREI($feature);
  }
  
  public function checkFracStatus($entity, $feature) {
    // Performs summary of FRAC useage pertinent to the chems in this event.
    // Groups by frac and risk level, report all blocks at that risk level.
    // "Warning: FRAC 7, has 3 applications on Block 1, Block2, and Cab14. This can lead to problems. Please modify."
    // @ todo: move the grouping/formatting functions into om_agman_frac.inc 
    $vineyard_id = $feature->vineyard->hydroid;
    $target_fracs = $feature->event_fracs;
    $date = dh_handletimestamp($feature->startdate);
    $yr = date('Y', $date);
    $startdate = $yr . '-01-01';
    $enddate = $yr . '-12-31';
    $block_ids = array_keys($feature->block_entities); // may have to grab the IDs from the objects
    $alerts = om_agman_group_frac_check($vineyard_id, $block_ids, $startdate, $enddate, $target_fracs);
    //dpm($alerts,'alerts');
    return($alerts);
  }
  
  public function setBlockREI(&$feature) {
    // @todo: add this 
  }
  
  public function setEventPHI(&$entity, &$feature) {
    // Every pre-harvest application event TS record should have a PHI property attached to it.
    // these records can then be used to rapdily determine the single PHI for this Block
    // adds a single record, by year
    $chems = substr(implode(', ', $feature->phi_chems), 0, 254);
    $appdate = dh_handletimestamp($feature->enddate);
    $phi_date = dh_handletimestamp($feature->phi_date);
    // this is the PHI property for this application event ts record for the admin feature
    $phi_prop_info = array(
      'featureid' => $entity->tid,
      'entity_type' => 'dh_timeseries',
      'propname' => 'agchem_phi',
      'varkey' => 'agchem_phi',
      'startdate' => $appdate,
      'enddate' => $phi_date,
      'propcode' => $chems,
    );
    //error_log("Saving phi " . print_r($phi_prop_info,1));
    $phi_prop = dh_properties_enforce_singularity($phi_prop_info, 'singular', TRUE);
    if ( ($feature->fstatus == 'post_harvest') or empty($feature->phi_date) ) {
      if ($phi_prop) {
        // this used to have a PHI prop, but is no longer a pre-harvest event 
        // so delete this property 
        entity_delete($phi_prop);
      }
    } else {
      if (!$phi_prop) {
        // this used to have a PHI prop, but is no longer a pre-harvest event 
        // so delete this property 
        $phi_prop = entity_create('dh_properties', $phi_prop_info);
      }
      $phi_prop->save();
    }
    return $phi_prop;
  }
  
  function getBlockTSPHI($fe, $sstime, $setime) {
    $block_phi_info = array(
      'featureid' => $fe->hydroid,
      'entity_type' => 'dh_feature',
      'varkey' => 'agchem_phi',
      'tstime' => $sstime,
      'tsendtime' => $setime,
    );
    $block_phi_ts = dh_timeseries_enforce_singularity($block_phi_info, 'trange_singular', FALSE);
    return $block_phi_ts; 
  }
  
  public function delete($entity) {
    // @todo: figure out how to handle deleted events
  }
  
  public function setBlockPHI(&$entity, &$feature) {
    // Every pre-harvest application event TS record should have a PHI property attached to it.
    // set this events PHI prop now 
    $event_phi_prop = $this->setEventPHI($entity, $feature);
    $event_year = date('Y', dh_handletimestamp($feature->enddate));
    $sstime = dh_handletimestamp("$event_year-01-01");
    $setime = dh_handletimestamp("$event_year-12-31");
    foreach ($feature->block_entities as $fe) {
      // retrieve the app event related to this block with highest PHI 
      $block_phi_event = om_agman_get_block_phi($fe->hydroid, 'agchem_application_event', $sstime, $setime, FALSE);
      $values = array(
        'entity_type' => 'dh_timeseries',
        'featureid' => $block_phi_event->tid,
        'propname' => 'agchem_phi'
      );
      // retrieve the event PHI prop from the limiting event for this block
      $phi_event_prop = om_model_getSetProperty($values, 'name', FALSE);
      // Retrieve existing PHI timeseries record for this block/year 
      // and insure only a single record for each block, per growing year
      $block_phi_ts = $this->getBlockTSPHI($fe, $sstime, $setime);
      //dpm($block_phi_event,'biggest phi ' . date('Y-m-d', $block_phi_event->tsendtime));
      if (is_object($phi_event_prop)) {
        $phi_feature = entity_load_single('dh_adminreg_feature', $block_phi_event->featureid);
        $chems = substr(implode(', ', $phi_feature->phi_chems), 0, 254);
        if (!is_object($block_phi_ts)) {
          // if this block doe not already have a saved PHI event for this seasonk we create one
          $block_phi_info = array(
            'featureid' => $fe->hydroid,
            'entity_type' => 'dh_feature',
            'varid' => dh_varkey2varid('agchem_phi', TRUE),
          );
          //dsm("Adding a new PHI record for block $fe->name on " . date('Y-m-d',$phi_event_prop->enddate));
          //dpm($block_phi_info,'to entity_create');
          $block_phi_ts = entity_create('dh_timeseries', $block_phi_info);
          //dpm($block_phi_ts,'ts');
        }
        $block_phi_ts->tstime = $phi_event_prop->startdate;
        $block_phi_ts->tsendtime = $phi_event_prop->enddate;
        $block_phi_ts->tscode = $phi_event_prop->propcode; 
        $block_phi_ts->tsvalue = $block_phi_event->featureid; // this is the adminid of the limiting event 
        $block_phi_ts->save();
        //dsm("Recording PHI event for block $fe->name on " . date('Y-m-d',$block_phi_ts->tsendtime));
      } else {
        // @todo: Somewhere later in the routine, look for blocks that have been removed from this event.
        //        and update their PHIs
        // delete the PHI event if one exists.
        if (is_object($block_phi_ts) and !$block_phi_ts->is_new) {
          entity_delete($block_phi_ts);
        }
      }
    }
  }
  
  
  public function getPHIDate(&$feature) {
    //@todo: put this in agchem PHI plugin
    $phi_ts = new DateTime();
    $phi_ts->setTimestamp($feature->phi_ts);
    $feature->phi_date = $phi_ts->format("Y-m-d");
    $feature->phi_chem = count($feature->phi_chems) ? implode(", ", $feature->phi_chems) : 'none';
  }
  
  public function getREIDate(&$feature) {
    //@todo: put this in agchem REI plugin
    //@todo: put this in agchem PHI plugin
    $rei_ts = new DateTime();
    $rei_ts->setTimestamp($feature->rei_ts);
    $feature->rei_date = $rei_ts->format("Y-m-d g:i A");
    $feature->rei_chem = count($feature->rei_chems) ? implode(", ", $feature->rei_chems) : 'none';
  }
  
  public function getPHIInfo(&$feature, &$chem) {
    //dpm($chem,'Called getPHIInfo for ' . $chem->name);
    // PHI - load chem PHI property of agchem
    // @todo: this can be migrated to the chem PHI variable as a plugin that will get auto added upon load
    $criteria = array(  
     0 => array(
      'name' => 'varid',
      'op' => 'IN',
      'value' => dh_varkey2varid('agchem_phi'),
      ),
    );
    
    $phi_info = array(
      'featureid' => $chem->adminid,
      'entity_type' => 'dh_adminreg_feature',
      'bundle' => 'dh_properties',
      'varkey' => 'agchem_phi',
    );
    $chem->agchem_phi = dh_properties_enforce_singularity($phi_info, 'singular');
    $chem->agchem_phi->propvalue = empty($chem->agchem_phi->propvalue) ? 0 : $chem->agchem_phi->propvalue;
    //$chem->loadComponents($criteria);
    //dpm($chem,'agchem obj');
    if (isset($chem->agchem_phi) and is_object($chem->agchem_phi) ) {
      $this_phi = $feature->enddate + $chem->agchem_phi->propvalue * 86400;
      if ($feature->phi_ts < $this_phi) {
        $feature->phi_ts = $this_phi;
        $feature->phi_chems = array($chem->name);
      } else {
        // check if multiple have the same PHI
        if ($feature->phi_ts <= $this_phi) {
          $feature->phi_ts = $this_phi;
          $feature->phi_chems[] = $chem->name;
        }
      }
    }
    
  }
  
  public function getREIInfo(&$feature, &$chem) {
    //dpm($chem,'Called getREIInfo for ' . $chem->name);
    // @todo: this can be migrated to the chem REI variable as a plugin that will get auto added upon load
    $criteria = array(  
     0 => array(
      'name' => 'varid',
      'op' => 'IN',
      'value' => dh_varkey2varid('agchem_rei'),
      ),
    );
    
    $rei_info = array(
      'featureid' => $chem->adminid,
      'entity_type' => 'dh_adminreg_feature',
      'bundle' => 'dh_properties',
      'varkey' => 'agchem_rei',
    );
    //@todo: replace this with a universal named property loader
    $chem->agchem_rei = dh_properties_enforce_singularity($rei_info, 'singular');
    $chem->agchem_rei->propvalue = empty($chem->agchem_rei->propvalue) ? 0 : $chem->agchem_rei->propvalue;
    //$chem->loadComponents($criteria);
    //dpm($chem,'agchem obj');
    if (isset($chem->agchem_rei) and is_object($chem->agchem_rei) ) {
      switch ($chem->agchem_rei->propcode) {
        case 'days':
          $tunits = 86400;
        break;
        case 'hours':
        default:
          $tunits = 3600;
        break;
      }
      $this_rei = $feature->enddate + $chem->agchem_rei->propvalue * $tunits;
      $rei_ts = new DateTime();
      $rei_ts->setTimestamp($this_rei);
      $rei_date = $rei_ts->format("Y-m-d g:i A");
      $event_date = date("Y-m-d g:i A", $feature->enddate);
      //dsm("$chem->name REI: $event_date($feature->enddate) + " . $chem->agchem_rei->propvalue . " * $tunits = $rei_date");
      if ($feature->rei_ts < $this_rei) {
        $feature->rei_ts = $this_rei;
        $feature->rei_chems = array($chem->name);
      } else {
        // check if multiple have the same rei
        if ($feature->rei_ts <= $this_rei) {
          $feature->rei_ts = $this_rei;
          $feature->rei_chems[] = $chem->name;
        }
      }
    }
    
  }
  
  public function buildContent(&$content, &$entity, $view_mode) {
    // @todo: handle teaser mode and full mode with plugin support
    //        this won't happen till we enable at module level however, now it only 
    //        is shown when selecting "plugin" in the view mode in views
    $now = dh_handletimestamp(date('Y-m-d'));
    $args = arg();
    $page = ((strlen($args[0]) > 0) and (strpos($view_mode, 'ical') === false )) ? $args[0] : 'ipm-home';
    if (strpos($page, 'ical') !== false) {
      // catch this -- hack
      // @todo: make finaldest part of the entity render plugin in views so we don't have to do this
      $page = 'ipm-home';
    }
    $content['#view_mode'] = $view_mode;
    $hidden = array('varname', 'tstime', 'tid', 'tsvalue', 'tscode', 'entity_type', 'featureid', 'tsendtime', 'modified', 'label');
    foreach ($hidden as $col) {
      $content[$col]['#type'] = 'hidden';
    }
    $feature = $this->getParentEntity($entity);
    // *****************************
    // Get and Render Chems & Rates
    $this->load_event_info($feature);
    $title = $feature->vineyard->name . " - " . $feature->name;
    $entity->tscode = $title . ' on ' . $feature->block_names;
    // see docs for drupal function l() for link config syntax
    // get list of blocks
    // get list of chems
    $uri = "ipm-live-events/" . $feature->vineyard->hydroid . "/sprayquan/$feature->adminid&finaldest=$page";
    $edit_link = array(
      '#type' => 'link',
      '#prefix' => '&nbsp; ',
      '#suffix' => '<br>',
      '#title' => 'Go to ' . $uri,
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
    // Edit standalone href link
    $edit_l = l(format_date($feature->startdate, 'short') . ' ' . $title, $uri, array('attributes' => array('class' => 'editlink')));
    $wo_uri = "ipm-live-events/" . $feature->vineyard->hydroid . "/workorder/$feature->adminid&destination=$page";
    $wo_tiny = l(' ', $wo_uri);
    // Other URIs
    $copy_uri = "ipm-live-events/" . $feature->vineyard->hydroid . "/clone/$feature->adminid&destination=$page";
    $copy_l = l(" ", $copy_uri, array('attributes' => array('class' => 'copylink', 'title' => 'Copy this event')));
    $delete_uri = "admin/content/dh_adminreg_feature/manage/" . $feature->adminid . "/delete&destination=$page";
    $delete_l = l(" ", $delete_uri, array('attributes' => array('class' => 'subtractlink', 'title' => 'Delete this event')));
    switch ($view_mode) {
      case 'teaser':
        $content['title'] = array(
          '#type' => 'item',
          '#markup' => $title,
        );
        $content['blocks'] = array(
          '#type' => 'item',
          '#markup' => '<b>Blocks:</b> ' . $feature->block_names
        );
        $content['materials'] = array(
          '#type' => 'item',
          '#markup' => '<b>Materials:</b> ' . $feature->chem_list,
        );
        $content['phi'] = array(
          '#type' => 'item',
          '#markup' => '<b>Pre-Harvest:</b> ' . "$feature->phi_date ($feature->phi_chem)",
        );
        $content['link'] = $edit_link; 
        $entity->title = date('Y-m-d', $feature->startdate) . $title;
        $content['modified']['#markup'] = '(modified on ' . date('Y-m-d', $feature->modified) . ")"; 
      break;
      
      case 'ical_summary':
        unset($content['title']['#type']);
        #$content['body']['#type']= 'item'; 
        $content['body']['#markup'] = "<b>What:</b>" . $title; 
        $content = array();
        $content['body']['#markup'] .= ' on ' .  $feature->block_names;
        $content['body'] = array(
          '#type' => 'item',
          '#markup' => '<b>Blocks:</b> ' .  $feature->block_names,
        );
        $content['body']['#markup'] .= "<br><b>Volume:</b> " . $feature->agchem_spray_vol_gal->propvalue . " gals";
        $content['body']['#markup'] .= "<br><b>Materials:</b> $feature->chem_list";
        $content['body']['#markup'] .= "<br><b>PHI:</b> $feature->phi_date ($feature->phi_chem)";
        $content['body']['#markup'] .= "<br><b>REI: .$feature->rei_date ($feature->rei_chem)";
        $content['body']['#markup'] .= "<br><div class='small'>Note: Pre-Harvest Interval (PHI) and Re-Entry Intervals (REI) are based on the material with the longest interval.</div>";
      break;
      
      case 'tiny':
      // note, this is not detailed in the modes available in dh.module, so this will not be available in Views
        $content = array('body'=>array());
        #$content['body']['#type']= 'item'; 
        $chem_list = implode(', ', array_column($feature->chem_details, 'name'));
        $content['body']['#markup'] .= "<b>Spray:</b>" . l( $chem_list, $wo_uri);
      break;
      
      case 'full':
        $this->renderWorkOrder($content, $entity, $feature);
      break;
      
      case 'plugin':
      default: 
        if ($feature->fstatus == 'cancelled') {
          $pre = "<s>";
          $suf = "</s>";
        }
        $content['title']['#markup'] = $pre . '<b>Date:</b> ' . $edit_l . '  &nbsp;' . $copy_l . '  &nbsp;' . $delete_l . $suf;
        $content['title']['#title'] = format_date($feature->startdate, 'short') . ": " . $title;
        $content['body'] = array(
          '#type' => 'item',
          '#markup' => ''
        );
        $epa_print_link = l(
          "Print WPS Report", 
          "print/dh_adminreg_feature/$entity->featureid/print/agchem_app",
          array('attributes' => array('class' => array('print-page')))
        );
        $work_order_print_link = l(
          "Print Work Order", 
          "print/ipm-live-events/" . $feature->vineyard->hydroid . "/workorder/$feature->adminid",
          array('attributes' => array('class' => array('print-page')))
        );
        $content['body']['#markup'] .= $epa_print_link . " /" . $work_order_print_link;
        $content['body']['#markup'] .= '<br><b>Blocks:</b> ' . $feature->block_names;
        if ($now > $entity->tstime) {
          $content['body']['#prefix'] = '<div class="help-block">';
          $content['body']['#suffix'] = '</div>';
        }
        $content['body']['#markup'] .= "<br><b>Volume:</b> " . $feature->agchem_spray_vol_gal->propvalue . " gals";
        $chem_list = "<ul><li>" . implode('</li><li>', $feature->chem_items) . "</li></ul>";
        $content['body']['#markup'] .= "<br><b>Materials:</b> $chem_list";
        //$content['body']['#markup'] .= "<br><b>Materials:</b> $feature->chem_list";
        $content['body']['#markup'] .= "<b>PHI:</b> $feature->phi_date ($feature->phi_chem)";
        $content['body']['#markup'] .= "<br><b>REI:</b> $feature->rei_date ($feature->rei_chem)";
        $content['body']['#markup'] .= "<br><div class='small'>Note: Pre-Harvest Interval (PHI) and Re-Entry Intervals (REI) are based on the material with the longest interval.</div>" . $suf;

        $entity->title = $title;
        $content['modified']['#markup'] = '(modified on ' . date('Y-m-d', $feature->modified) . ")"; 
      break;
    }
  }
  
  public function renderWorkOrder(&$content, &$entity, $feature) { 
    // 
    //dpm($feature,'feature');
    //dpm($feature->chems,'chems');
    // just for testing, this won't be included in the final work order.
    $this->checkFracStatus($entity, $feature);
    $content['general'] = array(
      '#type' => 'container'
    );
    $content['general']['title']['#type'] = 'item';
    $content['general']['title']['#markup'] = "<b>Event Title: </b>" . $feature->name;
    $content['general']['blocks']['#type'] = 'item';
    $content['general']['blocks']['#markup'] = "<b>Block(s): </b>" . $feature->block_names;
    $content['general']['area']['#type'] = 'item';
    $content['general']['area']['#markup'] = "<b>Area: </b>" . $feature->agchem_event_area->propvalue;
    $content['general']['volume']['#type'] = 'item';
    $content['general']['volume']['#markup'] = "<b>Total Volume: </b>" . $feature->agchem_spray_vol_gal->propvalue . ' gals';
    if ($feature->agchem_spray_vol_gal->propvalue > $feature->agchem_batch_vol->propvalue) {
      $batch_count = ($feature->agchem_spray_vol_gal->propvalue / $feature->agchem_batch_vol->propvalue);
      $batch_vol = $feature->agchem_batch_vol->propvalue;
      $final_batch_vol = $feature->agchem_spray_vol_gal->propvalue - ($batch_count * $feature->agchem_batch_vol->propvalue);
      $blab = "tanks";
    } else {
      $batch_count = 1;
      $batch_vol = $feature->agchem_spray_vol_gal->propvalue;
      $final_batch_vol = 0;
      $blab = "tank";
    }
    $content['general']['volume']['#markup'] .= " ($batch_count $blab";
    $content['general']['volume']['#markup'] .= ($batch_count > 1) ? "@" . $batch_vol . " gals, final tank is $final_batch_vol gals)" : ")";
  }
}


class dHVariablePluginAgchemLicensee extends dHVariablePluginDefault {
  
  public function hiddenFields() {
    return array('pid', 'varid', 'featureid', 'entity_type', 'bundle', 'dh_link_admin_pr_condition');
  }
  
  public function formRowEdit(&$form, $entity) {
    parent::formRowEdit($form, $entity);
    $form['propcode']['#title'] = t('License #');
    $form['propname']['#title'] = t('License Holder');
    $form['propname']['#description'] = t('Full name of authorized pesticide applicator as it appears on license.');
  }
}

//$plugin_def = ctools_get_plugins('dh', 'dh_variables', 'dHVariablePluginIPMIncident');
//$class = ctools_plugin_get_class($plugin_def, 'handler');

?>