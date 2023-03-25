<?php
echo "First line";
echo "What is going on?";
module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('inc', 'dh', 'dh.admin');

function dh_agchem_inventory_form($form, &$form_state, $dh_properties = null, $op = 'edit') {
  // load base form from
  //dpm($form_state,'form_state');
  if ($dh_properties->varid > 0) {
    $varinfo = dh_vardef_info($dh_properties->varid);
    $defname = $varinfo->varkey;
  } else {
    $defname = NULL;
  }
  //$form['pid'] = array(
  //  '#type' => 'hidden',
  //  '#default_value' => $dh_properties->pid,
  //  '#size' => 8,
 // );
  $date_format = 'Y-m-d';
  $form['startdate'] = array(
    '#title' => t('Date Inventory Taken'),
    '#type' => 'date_popup',
    '#date_format' => $date_format,
    '#default_value' => empty($dh_properties->startdate) ? date($date_format) : date($date_format,$dh_properties->startdate)
  );
  $form['startdate']['#weight'] = -1;
  $form['propname'] = array(
    '#title' => t('Property Name'),
    '#type' => 'hidden',
    '#default_value' => empty($dh_properties->propname) ? $defname : $dh_properties->propname,
    '#description' => t('The name of this property (allows multiple to occur)'),
    '#required' => TRUE,
    '#size' => 64,
  );

  $form['propvalue'] = array(
    '#title' => t('Properties Value'),
    '#type' => 'hidden',
    '#default_value' => $dh_properties->propvalue,
    '#description' => t('The numerical value itself.'),
    '#required' => FALSE,
    '#size' => 64,
  );
  
  $form['featureid'] = array(
    '#type' => 'hidden',
    '#default_value' => $dh_properties->featureid,
    '#required' => TRUE,
    '#size' => 64,
  );
  $form['entity_type'] = array(
    '#type' => 'hidden',
    '#default_value' => (isset($dh_properties->entity_type) and ($dh_properties->entity_type <> '')) ? $dh_properties->entity_type : 'dh_feature',
    '#required' => TRUE,
    '#size' => 64,
  );
  
  $form['varid'] = array(
    '#type' => 'hidden',
    '#default_value' => $dh_properties->varid,
    '#required' => TRUE,
    //'#size' => 64,
  );

  $form['bundle'] = array(
	  '#type' => 'hidden',
    '#default_value' => !empty($dh_properties->bundle) ? $dh_properties->bundle : 'dh_properties',
    '#maxlength' => 64,
    '#machine_name' => array(
      'exists' => 'dh_properties_get_types',
      'source' => array('label'),
    ),
  );
  //$form['pid']['#type'] = 'textfield';
  
  //dpm($form_state,'form_state');
  $inventory_grid = dh_agchem_inventory_grid($dh_properties, $form_state);
  if ($inventory_grid) {
    $inventory_grid->getData();
    foreach ($inventory_grid->data as $key => $prop) {
      $prop->varname = '';
      $inventory_grid->data[$key] = $prop;
    }
    $inventory_grid->buildForm($form, $form_state);
    // since the inventory grid is a generic container, that can exist as an insertable component,
    //   but is being used programattically in this case, we need to manually hide columns we don't wish to show
    $hiddens = array();
    $form['message'] = array (
      '#markup' => "",
    );
  } else {
    $form['message'] = array (
      '#markup' => "Could not find class Inventory grid class DHErefPropGrid",
    );
  }
  
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save Inventory'),
    '#weight' => 40,
    //'#submit' => array('dh_agchem_inventory_form_submit')
  );
  $form['actions']['cancel'] = array(
    '#type' => 'submit',
    '#value' => t('Cancel'),
    '#weight' => 45,
    '#limit_validation_errors' => array(),
    '#submit' => array('dh_manage_materials_form_submit_cancel')
  );
  dpm($form);
  return $form;
}

function dh_agchem_inventory_grid($dh_properties, $form_state) {
  ctools_include('plugins');
  $plugin = ctools_get_plugins('dh', 'dh_components', 'DHErefPropGrid');
  $class = ctools_plugin_get_class($plugin, 'handler');
  $config = array(
    'featureid' => array($dh_properties->featureid),
    'varid' => array('agchem_inventory_amt'),
    'prop_entity_type' => 'field_link_agchem_material',
    'eref_entity_type' => 'dh_feature',
    'eref_target_type' => 'dh_adminreg_feature',
    'year' => $form_state['year'],
    'display' => array(
      'properties' => array(
       ),
     ),
  );
 // dpm($config,'dh_agchem_inventory_grid');
  if ($class) {
    $inventory_grid = new $class($config);
    $inventory_grid->limit = 100;
    $hiddens = array('propname', 'pid', 'varid', 'startdate', 'enddate', 'featureid', 'entity_type', 'bundle', 'varunits');
    foreach ($hiddens as $hide_this) {
      $inventory_grid->conf['display']['properties'][$hide_this]['hidden'] = TRUE;
      $inventory_grid->conf['display']['properties'][$hide_this]['default'] = !isset($inventory_grid->conf['display']['properties'][$hide_this]['default']) ? '' : $inventory_grid->conf['display']['properties'][$hide_this]['default'];
    }
    $inventory_grid->conf['display']['properties']['varname']['title'] = t('Material');
    $inventory_grid->conf['display']['properties']['varname']['default'] = '';
    $inventory_grid->conf['display']['properties']['propcode']['title'] = t('Units');
    $inventory_grid->conf['display']['properties']['propcode']['default'] = '';
    $inventory_grid->conf['display']['properties']['propvalue']['title'] = t('Amount');
    $inventory_grid->conf['display']['properties']['propvalue']['default'] = '';
    $inventory_grid->conf['display']['properties']['startdate']['title'] = t('Inventory Date');
    $inventory_grid->conf['display']['properties']['startdate']['default'] = '';
    $inventory_grid->prepareQuery();
    //dpm($inventory_grid,'grid');
    return $inventory_grid;
  } else {
    return FALSE;
  }
}

function dh_agchem_inventory_form_submit(&$form, &$form_state) {
  form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
  form_load_include($form_state, 'inc', 'dh', 'dh.admin');
  ctools_include('plugins');
  $dh_properties = entity_ui_form_submit_build_entity($form, $form_state);
  //dpm($dh_properties , 'entity_ui_form_submit_build_entity');
  $plugin = ctools_get_plugins('dh', 'dh_components', 'DHErefPropGrid');
  $class = ctools_plugin_get_class($plugin, 'handler');
  if ($class) {
    $config = array(
      'featureid' => array($dh_properties->featureid),
      'varkey' => 'agchem_inventory_amt',
    );
    $inventory_grid = new $class($config);
  //dpm($inventory_grid, 'new inventory_grid');
    
    // @todo - handle the feature in the object code 
    //   ** but note that entity_ui_form_sub... doesn't work 
    //      have to hand spin see dh.display.inc
  //dpm($dh_properties, 'dh_properties->save()');
    $dh_properties->save();
    // handle all the attached stuff
    $inventory_grid->submitForm($form, $form_state);
  //dpm($inventory_grid, 'after submitForm inventory_grid');
  }
  $parms = drupal_get_query_parameters();
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
  } else {
    $url = implode('/', array('ipm-facility-materials', $dh_properties->featureid, 'purchasing'));
  }
  drupal_goto($url);
}


function dh_agchem_inventory_form_cancel($form, &$form_state) {
  $parms = drupal_get_query_parameters();
  if (isset($parms['finaldest'])) {
    $url = $parms['finaldest'];
  } else {
    $url = implode('/', array('ipm-home'));
  }
  drupal_goto($url);
}

$lu = NULL;
$op = 'add';
$date = date('Y-m-d');
module_load_include('module', 'dh');
$a = arg();
$facarg = 1; //  
$bundle = 'dh_properties';
if (isset($a[$facarg])) {
  $facid = $a[$facarg];
  $vars = dh_varkey2varid('agchem_inventory_event');
  $varid = array_shift($vars);
  $values = array('featureid' => $facid, 'varid' => $varid, 'entity_type' => 'dh_feature');
  if ($facid) {
    // load existing prop for editing
    $props = dh_get_properties($values, 'singular');
    //dpm($props, 'dh_get_properties');
    if (isset($props['dh_properties'])) {
      $data = entity_load('dh_properties', array_keys($props['dh_properties']));
      $prop = array_shift($data);
      $op = 'edit';
    } else {
      $prop = entity_create('dh_properties', 
        $values + array('bundle' => $bundle, 'propname' => 'Agchem Inventory')
      );
      //dpm($prop,'created');
    }
  }

  $form_state = array();
  $form_state['wrapper_callback'] = 'entity_ui_main_form_defaults';
  $form_state['entity_type'] = 'dh_properties';
  $form_state['bundle'] = is_object($prop) ? $prop->bundle : $bundle;
  form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
  // does this do anything in this context?
  $form_state['build_info']['args'] = array($prop, $op, 'dh_properties');

  // **********************
  // Load the form
  // **********************
  $elements = drupal_build_form('dh_agchem_inventory_form', $form_state);

  $form = drupal_render($elements);
  echo $form;
  
} else {
  echo "Usage: ipm-facility-materials/[farm hydroid]/inventory.";
}

?>