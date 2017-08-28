<?php
module_load_include('inc', 'dh', 'plugins/dh.display');

class dHVariablePluginEfficacy extends dHVariablePluginDefault {
  
  public function effAbbrev() {
    return array(
      '' => 'U',
      1 => 'E',
      2 => 'G',
      3 => 'G_F',
      4 => 'F',
      5 => 'P',
      6 => 'N',
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
    );
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
  }
  
}

class dHVariablePluginFRAC extends dHVariablePluginDefault {
  public function formRowEdit(&$form, $entity) {
    $form['propvalue']['#type'] = 'hidden';
    // original codes from: http://www.frac.info/publications/downloads
    // see edited list in G: https://docs.google.com/spreadsheets/d/1cktc0J5jkIcCd7GPI109dwvLebBmWFHqLbxrxP4032Y/edit#gid=1074563920
    $fracs = array('04', '08', '32', '31', '01', '10', '22', '20', '43', '47', '39', '07', '11', '21', '29', '30', '38', '45', '09', '23', '24', '25', '41', '13', '12', '02', 'n.a.', '06', '14', '28', '44', '46', '48', '49', '03', '05', '17', '18', '26', '19', '40', '16.1', '16.2', '16.3', '27', '33', '34', '35', '36', '37', '42', 'M01', 'M02', 'M03', 'M04', 'M05', 'M06', 'M07', 'M08', 'M09', 'M10', 'M11', 'NC', 'U06', 'U08', 'U12', 'U13', 'U14', 'U16', 'U17', 'U18', 'P01', 'P02', 'P03', 'P04', 'P05', 'P06', 'BM01', 'BM02');
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
  }
  
  public function save(&$entity) {
    
  }
  
}

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
    //$group->buildForm($rowform, $blank);
    //$rowform[$codename]['#process'] = array('om_agman_form_process_npk');
    //$rowform[$codename]['#input'] = TRUE;
    //$rowform[$codename]['#value'] = $vals;
    // @todo: understand how date makes a multi field form
      // date has:
        // date_select_element_value_callback - turns date pieces into properly formatted date
      // tutorial: https://www.drupal.org/node/169815
    // store these as children of propcode/tscode
    //om_agman_form_process_npk($rowform[$codename]);
    // store these as children of a new variable
    
    $hidden = array('pid', 'startdate', 'enddate', 'featureid', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $rowform[$hide_this]['#type'] = 'hidden';
    }
    //dpm($rowform,'raw form');
  }
  
  public function formRowSave(&$rowvalues, &$row) {
    //dpm($rowvalues,'save');
    parent::formRowSave($rowvalues, $row);
    $codename = $this->row_map['code'];
    $row->$codename = implode('-', array($rowvalues['n'], $rowvalues['p'], $rowvalues['k']));
    //dpm($row);
    // special save handlers
  }

}


class dHAgchemApplicationEvent extends dHVariablePluginDefault {
    
  public function dh_getValue($entity, $ts = FALSE, $propname = FALSE, $config = array()) {
    switch ($propname) {
      case 'event_title':
        $blocks = array();
        $blocks_names = array();
        $feature = $this->getParentEntity($entity);
        $field_blocks = field_get_items('dh_adminreg_feature', $feature, 'dh_link_feature_submittal');
        foreach ($field_blocks as $to) {
          $blocks[] = $to['target_id'];
        }
        $block_entities = entity_load('dh_feature', $blocks);
        foreach ($block_entities as $fe) {
          $block_names[] = $fe->name;
          if (!$vineyard) {
            $vineyard = dh_getMpFacilityHydroId($fe->hydroid);
          }
        }
        $title = $feature->name; 
        $title = $title . ' on ' .implode(' ', $block_names);
        return $title;
      break;
      case 'event_description':
        $blocks = array();
        $blocks_names = array();
        $feature = $this->getParentEntity($entity);
        $field_blocks = field_get_items('dh_adminreg_feature', $feature, 'dh_link_feature_submittal');
        foreach ($field_blocks as $to) {
          $blocks[] = $to['target_id'];
        }
        $block_entities = entity_load('dh_feature', $blocks);
        foreach ($block_entities as $fe) {
          $block_names[] = $fe->name;
          if (!$vineyard) {
            $vineyard = dh_getMpFacilityHydroId($fe->hydroid);
          }
        }
        $chems = array();
        $chem_names = array();
        $field_chems = field_get_items('dh_adminreg_feature', $feature, 'field_link_to_registered_agchem');
        foreach ($field_chems as $to) {
          $chems[$to['target_id']] = array(
            'adminid' => $to['target_id'],
            'eref_id' => $to['eref_id'],
          );
        }
        $chem_entities = entity_load('dh_adminreg_feature', array_keys($chems));
        foreach ($chem_entities as $fe) {
          $chem_names[] = $fe->name;
          // @todo: create and use properties plugin to render rate and amounts info
        }
        $feature = $this->getParentEntity($entity);
        $description = $title . ' on ' .implode(' ', $block_names);
        $description .= ' ' .implode(', ', $chem_names);
        // see docs for drupal function l() for link config syntax
        // get list of blocks
        // get list of chems
        $uri = token_replace("[site:url]ipm-live-events/$vineyard/sprayquan/$feature->adminid");
        $description .= l(' - View :' . $uri, $uri, array('absolute' => TRUE));
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
    // @todo: handle teaser mode and full mode with plugin support
    //        this won't happen till we enable at module level however, now it only 
    //        is shown when selecting "plugin" in the view mode in views
    $hidden = array_keys($content);
    foreach ($hidden as $col) {
      $content[$col]['#type'] = 'hidden';
    }
    $feature = $this->getParentEntity($entity);
    $blocks = array();
    $blocks_names = array();
    $field_blocks = field_get_items('dh_adminreg_feature', $feature, 'dh_link_feature_submittal');
    foreach ($field_blocks as $to) {
      $blocks[] = $to['target_id'];
    }
    $block_entities = entity_load('dh_feature', $blocks);
    foreach ($block_entities as $fe) {
      $block_names[] = $fe->name;
      if (!$vineyard) {
        $vineyard = dh_getMpFacilityHydroId($fe->hydroid);
      }
    }
    $chems = array();
    $chem_names = array();
    $field_chems = field_get_items('dh_adminreg_feature', $feature, 'field_link_to_registered_agchem');
    foreach ($field_chems as $to) {
      $chems[$to['target_id']] = array(
        'adminid' => $to['target_id'],
        'eref_id' => $to['eref_id'],
      );
    }
    $chem_entities = entity_load('dh_adminreg_feature', array_keys($chems));
    foreach ($chem_entities as $fe) {
      $chem_names[] = $fe->name;
      // @todo: create and use properties plugin to render rate and amounts info
    }
    $title = $feature->name; 
    $entity->tscode = $title . ' on ' .implode(' ', $block_names);
    // see docs for drupal function l() for link config syntax
    // get list of blocks
    // get list of chems
    $uri = "/ipm-live-events/$vineyard/sprayquan/$feature->adminid";
    $link = array(
      '#type' => 'link',
      '#prefix' => '&nbsp;',
      '#suffix' => '<br>',
      '#title' => 'Go to ' . $uri,
      '#href' => $uri,
      'query' => array(
        'finaldest' => 'ipm-home/all/all/',
      ),
    );
    switch ($view_mode) {
      case 'teaser':
        unset($content['title']['#type']);
        $content['body']['#type']= 'item'; 
        $content['body']['#markup'] = $title; 
      break;
      
      case 'ical_summary':
        unset($content['title']['#type']);
        #$content['body']['#type']= 'item'; 
        $content['body']['#markup'] = $title; 
        $content = array();
        $content['body']['#markup'] .= ' on ' .implode(' ', $block_names);
      break;
      
      case 'full':
      case 'plugin':
      default:
        $content['body']['#markup'] = $title; 
        $content['body']['#markup'] .= 'Blocks: ' .implode(', ', $block_names) . "\n";
        $content['body']['#markup'] .= 'Materials: ' .implode(', ', $chem_names) . "\n";
        $content['link'] = $link; 
        $content['modified']['#markup'] = '(modified on ' . date('Y-m-d', $feature->modified) . ")"; 
      break;
    }
  }
}
?>