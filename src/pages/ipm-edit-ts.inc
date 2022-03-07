<?php
// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/pages/ipm-edit-ts');
module_load_include('module', 'dh');
module_load_include('inc', 'dh', 'dh.admin');
$ts = NULL;
$op = 'add';
$a = arg();
$p = drupal_get_query_parameters();
$tsdef = array(
  'bundle' => 'dh_timeseries', 
  'entity_type' => 'dh_feature',
  'varid' => $p['varid'],
  'featureid' => intval($a[1]),
);
if (isset($a[3])) {
  if (intval($a[3])) {
    $tsid = $a[3];
  } else {
    // must be add which is handled by default
  }
}
if (isset($tsid)) {
  $ts = entity_load_single('dh_timeseries', $tsid);
  if ($ts) {
    $op = 'edit';
  }
}

$ts = is_object($ts) ? $ts : entity_create('dh_timeseries', $tsdef);
$vardef = dh_vardef_info($ts->varid);

$text['info'] = array(
  '#weight' => -1, // we want this next
  '#markup' => 
    "<h3>" . t(ucwords($op)) . " " . t($vardef->varname) . "</h3>"
);
// just grab the regular form for proof of concept
$elements = entity_ui_get_form('dh_timeseries', $ts, $op, $form_state);
$elements['info'] = $text['info'];
//dpm($elements,'final form');
// now handled in 
//dh_timeseries_formRowPlugins($elements, $ts, 'dh_timeseries');
$form = drupal_render($elements);
echo $form;
?>