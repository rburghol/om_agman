<?php
// use in any page with 1-line:
//   module_load_include('inc', 'om_agman', 'src/pages/batch-edit-blocks');
module_load_include('inc', 'dh', 'plugins/dh.display');

class dHFacilityBlocks extends EntityGroupConfigurator {

  var $base_entity_type = 'dh_feature';
  var $base_bundle;
  var $form_columns;
  var $base_entity_id; // needs to be set during __construct to permit functioninig
  var $save_method = 'default'; // 'default' uses SubmitFormCustom
  var $blank_blocks = 1; // # of blank blocks to add
  
  function __construct($conf) {
    if (!isset($conf['base_entity_id'])) {
      watchdog('om_agman', 'base_entity_id must be set to call children linked via dh_link_facility_mps');
    } else {
      $this->base_entity_id = $conf['base_entity_id'];
    }
    parent::__construct($conf);
  }
  
  function SubmitFormCustom(array &$form, $form_state) {
    global $user;
    $uid = $user->uid;
    if ($form_state['values']['vineyard_id'] > 0) {
      $vid = $form_state['values']['vineyard_id'] ;
      $v = entity_load_single('dh_feature', $vid);
      $default_geofield = $v->dh_geofield;
      foreach ($form_state['values'][$this->groupname] as $record_group) {
        // if hydroid > 0 save the block
        $feature = NULL;
        if (empty($record_group['hydroid'])) {
          // on the final row, check to see if it is formatted
          //dpm($record_group,"New Record Group?");
          if (empty($record_group['hydroid']) and strlen($record_group['name'])) {
            $record_group['dh_link_facility_mps'] = array(
              'und' => array( 0 => array('target_id' => $vid) )
            );
            $record_group['dh_geofield'] = $default_geofield;
            $record_group['dh_link_feature_mgr_id'] = array('und' => array( 0 => array('target_id' => $uid) ));
            $record_group['bundle'] = 'landunit';
            $feature = entity_create('dh_feature', $record_group);
            drupal_set_message("Created block $feature->name");
            $feature->save();
          }
        } else {
          // load the entity
          $feature = entity_load_single('dh_feature', $record_group['hydroid']);
        }
        if (is_object($feature)) {
          $feature->name = $record_group['name'];
          $area_info = array(
            'varkey' => 'om_agman_area',
            'featureid' => $feature->hydroid,
            'entity_type' => 'dh_feature',
          );
          $area_prop = om_model_getSetProperty($area_info, 'varid');
          $area_prop->propvalue = $record_group['area'];
          $area_prop->save();
          $plugin = dh_variables_getPlugins($area_prop);
          $feature->dh_areasqkm['und'][0]['value'] = $plugin->convertArea($area_prop->propvalue, 'ac', 'sqkm');
          //dpm($feature,"Saving");
          //dpm($record_group, "from record group");
          entity_save('dh_feature', $feature);
          if (!empty($record_group['agman_plant'])) {
            $variety = array(
              'varid' => dh_varkey2varid('agman_plant', TRUE),
              'propname' => 'Variety',
              'bundle' => 'dh_properties',
              'featureid' => $feature->hydroid,
              'entity_type' => 'dh_feature',
              'propcode' => $record_group['agman_plant']
            );
            //dpm($variety, "Updating Variety info");
            dh_update_properties($variety, 'name');
          }
        }
      }
    } else {
      drupal_set_message("Vineyard ID Missing");
    }
  }
  
  // getData() requires a properly configured entity field query
  function getData() {
    // uses EFQ to get related blocks that have already been created
    parent::getData();
    // get all properties
    foreach ($this->data as $block) {
      if (method_exists($block, 'loadComponents')) {
        $block->loadComponents();
      }
    }
    // Add some blanks now
    //dpm($this->data, 'data retrieved');
    for ($i = 1; $i <= $this->blank_blocks; $i++) {
      $defaults = array(
        'dh_link_facility_mps' => array('und' => array( 0 => array('target_id' => $vid) )),
      );
      $bb = entity_create('dh_feature', $defaults);
      $this->data[] = $bb;
    }
    // 
  }
  
  // prepareQuery() sets up the EFQ to retrieve related
  function prepareQuery() {
    parent::prepareQuery();
    // set up the EFQ for related blocks
    // - load the hydroid of the facility, 
    // - use a field condition for 
    $this->efq->fieldCondition('dh_link_facility_mps','target_id',$this->base_entity_id);
    $this->efq->propertyCondition('bundle', 'landunit', '=');
    // Add some blanks
  }
  
  // prepareQuery() sets up the EFQ to retrieve related
  function buildForm(&$form, &$form_state) {
    // add custom things here. 
    parent::buildForm($form, $form_state);
    $dh_feature = $form_state['build_info']['args'][0];
    dpm($dh_feature, "farm object");
    if (!is_object($dh_feature)) {
      return $form;
    }
    // ********** Add special annotations and instructions 
    // if this is a the first block of a given vineyard, we handle it specially
    // if this is a blank row in a vineyard that already has one or more blocks, we add less info
    $blocks = dh_get_facility_mps($dh_feature->hydroid, 'landunit');
    dpm($blocks, "This farm has blocks");
    if (empty($blocks)) {
      $form['message'] = array(
        '#weight' => -6, // we want this at the very top
        '#markup' => t('Step 2 of 4: Add blocks to your vineyard.'),
        '#prefix' => '<h3>',
        '#suffix' => '</h3>',
      );
      $form['info'] = array(
        '#weight' => -5, // we want this next
        '#markup' => t(
          'Congratulations on setting up a vineyard! Now, you must add at least one block to your vineyard.'
          . '<br>In the fields below, please type the block name, the area in acres, (in order to enable spray calculations), and the variety or varieties planted in the block.'
          
        ),
      );
    }
    return $form;
  }
  
  public function formRowDefaults(&$rowform, $row) {
    parent::formRowDefaults($rowform, $row);
    $rowform['hydroid'] = array(
      '#type' => 'hidden',
      '#default_value' => $row->hydroid,
   //   '#required' => TRUE,
    );
    $rowform['name'] = array(
      '#type' => 'textfield',
      '#default_value' => $row->name,
   //   '#required' => TRUE,
      '#size' => 32,
      '#coltitle' => 'Block Name', 
 //     '#element_validate' => array('om_agman_validate_block'),
    );
    //dpm($row->dh_areasqkm,'area');
    // Use property with plugin dHAgmanAreaMultiDim
    $area_prop = FALSE;
    if ($row->hydroid > 0) {
      // look for property
      $area_info = array(
        'varkey' => 'om_agman_area',
        'featureid' => $row->hydroid,
        'entity_type' => 'dh_feature',
      );
      $area_prop = om_model_getSetProperty($area_info, 'varid');
      if ($area_prop->propvalue === NULL) {
        $plugin = dh_variables_getPlugins($area_prop);
        $area_prop->propvalue = round($plugin->convertArea($row->dh_areasqkm['und'][0]['value'], 'sqkm', 'ac'),2);
      }
    }
    // allow null here in case of blank end row
    $fval = ($row->dh_areasqkm['und'][0]['value'] > 0) ? $row->dh_areasqkm['und'][0]['value'] : NULL;
    $area_value =
      is_object($area_prop) 
      ? $area_prop->propvalue 
      : $fval
    ;
    // load property (or create if not existing)
    // convert value if existing
    $rowform['area'] = array(
      '#coltitle' => t('Area (ac)'),
      '#type' => 'textfield',
      '#element_validate' => array('element_validate_number'),
      '#default_value' => $area_value,
      '#description' => t('Area of this block.'),
      //'#required' => TRUE,
      '#size' => 10,
    );  
    // variety 
    //dpm($row,'row');
    //$variety = $row->{}
    $variety = array(
      'varkey' => 'agman_plant',
      'featureid' => $row->hydroid,
      'entity_type' => 'dh_feature',
    );
    $plant = dh_properties_enforce_singularity($variety, 'varid');
    //dpm($variety,'variety info');
    //dpm($plant,'variety planted');
    $rowform['agman_plant'] = array(
      '#type' => 'textfield',
      '#default_value' => empty($row->agman_plant) ? $plant->propcode : $row->agman_plant,
   //   '#required' => TRUE,
      '#size' => 64,
      '#coltitle' => 'Variety', 
    );
    // variety 
    
    // ********** Add special annotations and instructions 
    // if this is a the first block of a given vineyard, we handle it specially
    // if this is a blank row in a vineyard that already has one or more blocks, we add less info
    if (empty($row->hydroid)) {
      $rowform['name']['#suffix'] = t('');
      $rowform['area']['#prefix'] = t('');
      $rowform['agman_plant']['#prefix'] = t('');
    }
    
  }
  
  public function addFormActions(&$form, $form_state) {
    parent::addFormActions($form, $form_state);
    $form['actions']['submit']['#submit'] = array('om_multiple_landunit_form_submit');
    $form['actions']['submit_another'] = array(
      '#type' => 'submit',
      '#value' => t('Save & Add Another'),
      '#weight' => 40,
      '#submit' => array('om_multiple_landunit_form_save_another')
      // @todo: do this as ajax
    );
    $form['actions']['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#weight' => 50,
      '#submit' => array('om_multiple_landunit_form_cancel')
      // @todo: do this as ajax
    );
  }
}

function om_multiple_landunit_form($form, &$form_state, $dh_feature = null, $op = 'edit') {
  $conf = array(
    'base_entity_id' => $dh_feature->hydroid,
    'groupname' => 'om_multiple_landunit',
  );
  $form['vineyard_id'] = array(
    '#type' => 'hidden',
    '#default_value' => $dh_feature->hydroid,
  );
  $blocks = new dHFacilityBlocks($conf);
  $blocks->groupname = 'om_multiple_landunit';
  $blocks->prepareQuery();
  $blocks->getData();
  $blocks->buildForm($form, $form_state);
  $blocks->addFormActions($form, $form_state);
  //dpm($blocks,'blocks');
  return $form;
}

function om_agman_validate_block($form, $form_state) {
  // if a n entry has a hydroid, or does NOT have hydroid but is adding
  foreach ($form_state['values'] as $group => $values) {
    foreach($values as $row => $rowvalues) {
      // 
      if (empty($rowvalues['hydroid'])) {
        // the blank line at the end, handle separately
        // only handle if some attribute is set
        if (!empty($rowvalues['name']) or !empty($rowvalues['dh_areasqkm'])) {
          $required = array('name', 'dh_areasqkm');
        } else {
          $required = array();
        }
      } else {
        // require all attributes, including hydroid
        $required = array('hydroid', 'name', 'dh_areasqkm');
      }
      foreach ($required as $var) {
        switch ($var) {
          case 'name':
          if (!strlen($rowvalues[$var])) {
            form_error($form[$group][$row][$var], t('Name is a required field.'));
          }
          break;
          case 'dh_areasqkm':
          if (!strlen($rowvalues[$var])) {
            form_error($form[$group][$row][$var], t('Area must be greater than zero.'));
          }
          if (!is_numeric($rowvalues[$var])) {
            form_error($form[$group][$row][$var], t('Area must be a number.'));
          }
          break;
        }
      }
    }
  }
}

function om_multiple_landunit_form_save($form, &$form_state) {
  //dpm($form_state,'form_state');
  $conf = array(
    'base_entity_id' => $form_state['values']['vineyard_id'],
  );
  $blocks = new dHFacilityBlocks($conf);
  $blocks->groupname = 'om_multiple_landunit';
  $blocks->prepareQuery();
  $blocks->SubmitForm($form, $form_state);
}
/**
 * Form API submit callback for the type form.
 */
function om_multiple_landunit_form_save_another($form, &$form_state) {
  om_multiple_landunit_form_save($form, $form_state);
  $vineyard_id = $form_state['values']['vineyard_id'];
  $url = "ipm-facility-info/$vineyard_id/all/editblocks";
  drupal_goto($url);
}
function om_multiple_landunit_form_submit($form, &$form_state) {
  om_multiple_landunit_form_save($form, $form_state);
  $vineyard_id = $form_state['values']['vineyard_id'];
  $url = isset($form_state['finaldest']) ? $form_state['finaldest'] : "ipm-facility-info/$vineyard_id/all";
  drupal_goto($url);
}

function om_multiple_landunit_form_cancel($form, &$form_state) {
  $vineyard_id = $form_state['values']['vineyard_id'];
  $url = isset($form_state['finaldest']) ? $form_state['finaldest'] : "ipm-facility-info/$vineyard_id/all";
  drupal_goto($url);
}

global $user;
$a = arg();

if (isset($a[1])) {
  $vid = $a[1];
  $v = entity_load_single('dh_feature', $vid);
  $ok = is_object($v) ? TRUE : FALSE;
} else {
  $ok = FALSE;
}

if ($ok) {
  // default block location
  $form_state = array();
  $form_state['wrapper_callback'] = 'entity_ui_main_form_defaults';
  $form_state['entity_type'] = 'dh_feature';
  $form_state['bundle'] = 'facility';
  //$form_state['redirect'] = 'ipm-facility-info';
  form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
  $form_state['build_info']['args'] = array($v, 'add', 'dh_feature');

  // **********************
  // Load the form
  // **********************
  //$elements = drupal_get_form('dh_landunit_form');
  $elements = drupal_build_form('om_multiple_landunit_form', $form_state);
  //$elements = entity_ui_get_bundle_add_form('dh_feature', 'landunit');
  // entity_ui_get_form($entity_type, $entity, $op = 'edit', $form_state = array())
  error_reporting(E_ALL);
  // just grab the regular form for proof of concept
  //$elements = entity_ui_get_form('dh_feature', $lu, $op, $form_state);

  $form = drupal_render($elements);
  echo $form;
}
if (!$ok) {
  drupal_set_message("Error: You must provide a valid vineyard ID.");
}

?>