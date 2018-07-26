#!/user/bin/env drush
<?php

module_load_include('inc', 'dh', 'plugins/dh.display');
$v = $view->args ;
$uid = -1;
error-log("<br>View args: " . print_r($v,1));
echo "Usage: drush scr modules/om_agman/src/save-all-agchem-events.php\n";
// sql to get records with redundant erefs
$q = "  select hydroid, name from dh_feature ";
$q .= " where bundle = 'facility' ";
$q .= " and ftype = 'vineyard' ";
if ($uid <> -1) {
  $q .= " and uid = $uid ";
}
$vineyards = db_query($q);

while ($values = $vineyards->fetchAssoc()) {
  error_log("Found:" . print_r($values,1));
  $info = array(
    'varkey' => 'dh_auth_code',
    'featureid' => $values['hydroid'],
    'bundle' => 'dh_properties',
    'entity_type' => 'dh_feature',
    'propname' => 'Authorization Code'
  );
  $vineyard = $values['name'];
  $result = dh_get_properties($info, 'singular');
  if (isset($result['dh_properties'])) {
    $auth_pids = array_keys($result['dh_properties']);
    $pid = array_shift($auth_pids);
    $auth_obs = entity_load_single('dh_properties', $pid);
    if (empty($auth_obs->propcode)) {
      error_log("Saving Obejct to generate code");
      $auth_obs->regenerate = TRUE;
      $auth_obs->save();
    }
    echo "Found $info[varkey] for $vineyard = $auth_obs->propcode \n";
  } else {
    // need to create
    echo "Creating $vineyard - \n" . print_r($info,1);
    $pid = dh_update_properties($info, 'singular');
    $auth_obs = entity_load_single('dh_properties', $pid);
    $auth_obs->regenerate = TRUE;
    $auth_obs->save();
    echo "Created $info[varkey] for $vineyard = $auth_obs->propcode \n";
  }
}
?>
