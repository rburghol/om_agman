<?php
module_load_include('inc', 'dh', 'plugins/dh.display');
$file = './modules/om_agman/data/Grape fungicides_us_no-comments.csv';
$handle = fopen($file, 'r');
//$cols = array('hydrocode', 'tstime', 'tsendtime', 'varname', 'tstext', 'tsvalue', 'title', 'runid');
$cols = array(
  'AI_English' => array(
    'propcode' => 'AI_English',
    'propvalue' => 'percentage',
    'varkey' => 'agchem_ai',
    'required' => array('propcode', 'propvalue')
  ),
  'AI_English2' => array(
    'propcode' => 'AI_English2',
    'propvalue' => 'percentage2',
    'varkey' => 'agchem_ai',
    'required' => array('propcode', 'propvalue')
  ),
);
$i = 0;
echo "Processed ";
$summaries = array(); 
$entities = array(); 
$warnings = array();
$entity_type = 'dh_adminreg_feature';
// $summaries = array(
//   2 => array(
//     '2017-06-01' => '2017-05-01',
// if current date does not match last date, do summary using tstime_date_singular setting
$colnames = fgetcsv($handle);
while ($row = fgetcsv($handle)) {
  // Load some entity.
  $values = array_combine($colnames, $row);
  $admincode = $values['epa_id'];
  $recs = dh_adminreg_get_adminreg_entity($admincode, 'registration');
  if (count($recs) == 0) {
    error_log("Cannot find $entity_type with admincode $admincode" . print_r($recs,1));
    continue;
  }
  if (count($recs) > 1) {
    $msg = "Multiple entries (" 
      . count($recs) . ") found for admincode $admincode " 
      . "(" . implode(',', $recs) . ")"
    ;
    if (!in_array($msg, $warnings)) {
      $warnings[] = $msg;
    }
  }
  $adminid = array_shift($recs);
  $feature = entity_load_single('dh_adminreg_feature', $adminid);
  foreach ($cols as $colkey => $thiscol) {
    if (!isset($values[$colkey])) {
      error_log("Missing data column $colkey -- skipping");
    }
    $prop_info = array(
      'varkey' => $thiscol['varkey'],
      'featureid' => $feature->adminid,
      'entity_type' => $entity_type,
      'propname' => $thiscol['varkey'],
      'propcode' => $values[$thiscol['propcode']],
      'propvalue' => $values[$thiscol['propvalue']],
    );
    $import = TRUE;
    foreach ($thiscol['required'] as $req) {
      if (empty($prop_info[$req])) {
        error_log("Required variable $req not found. Skipping.");
        $import = FALSE;
        continue;
      }
    }
    if ($import) {
      $thisprop = om_model_getSetProperty($prop_info, 'propcode_singular');
      $thisprop->save();
      echo print_r($prop_info,1) . "saved with PID: $thisprop->pid \n";
      $i++;
    }
  }
}
echo "Finished importing $i records.\n";
echo "Warnings: " . print_r($warnings,1) . " \n";
?>
