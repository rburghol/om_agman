<?php
// get last inventory date for feature
$info = array(
  'featureid' => $view->args[0],
  'entity_type' => 'dh_feature',
  'varkey' => 'agchem_inventory_event',
);
$inv = dh_get_properties($info, 'singular');
$date = FALSE;
$pid = array_shift($inv['dh_properties']);
if ($pid) {
  $prop = entity_load_single('dh_properties', $pid->pid);
  $date = property_exists($prop, 'startdate') ? date('Y-m-d',$prop->startdate) : FALSE;
}
if ($date) {
  return $date;
} else {
  return date('Y-m-d');
}

?>