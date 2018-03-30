#!/user/bin/env drush
<?php

function get_risk_model(&$entities, $hydrocode) {
  $efq = new EntityFieldQuery;
  $efq->entityCondition('entity_type', 'dh_feature');
  $efq->propertyCondition('hydrocode', $hydrocode, '=');
  //$efq->propertyCondition('bundle', 'weather_risk_model', '=');
//dpm($efq);
  $result = $efq->execute();
  if (isset($result['dh_feature'])) {
    $rez = array_shift($result['dh_feature']);
    if (property_exists($rez, 'hydroid')) {
      $entities[$hydrocode] = $rez->hydroid;
      return $rez->hydroid;
    }
  }
  return FALSE;
}

$file = '/var/www/incoming/risks.csv';
$handle = fopen($file, 'r');
// actual $keys = array('begintime', 'records', 'battV_min', 'airTC_avg', 'relav_humid', 'LWmV_avg', 'LWMdry_tot', 'LWMcon_tot', 'LWMwet_tot', 'rain_mm_tot', 'time_interval', 'hydrocode');
$keys = array('hydrocode', 'tstime', 'tsendtime', 'varname', 'tstext', 'tsvalue', 'title', 'runid');
$i = 0;
echo "Processed ";
$summaries = array(); 
$entities = array(); 
$risk_models = array(); // a list of individual risk_models handled
// $summaries = array(
//   2 => array(
//     '2017-06-01' => '2017-05-01',
// if current date does not match last date, do summary using tstime_date_singular setting
while ($values = fgetcsv($handle)) {
  $i++;
  if ($i == 1) {
    continue;
  }
  $risk_record = array_combine($keys, $values);
  $varid = dh_varkey2varid($risk_record['varname']);
  $varid = is_array($varid) ? array_shift($varid) : $varid;
  $risk_record['varid'] = $varid;
  //dpm($risk_record,'weather');
  $risk_model = isset($entities[$risk_record['hydrocode']]) ? $entities[$risk_record['hydrocode']] : get_risk_model($entities, $risk_record['hydrocode']);
  if ($risk_model) {
    $risk_models[$risk_model] = $risk_model; // add to list for later summary
    $risk_record['featureid'] = $risk_model;
    $risk_record['entity_type'] = 'dh_feature';
    $risk_record['tscode'] = $risk_record['tstext'];
    //dpm($values, 'values');
    $tid = dh_update_timeseries($risk_record);
    $risk_record['tid'] = $tid;
    //echo print_r($risk_record, 1) . "\n";
    // just save one entry for each date
    if (!isset($summaries[$risk_model])) {
      $summaries[$risk_model] = array();
    }
    $summaries[$risk_model][date('Y-m-d', dh_handletimestamp($risk_record['tstime']))] = date('Y-m-d', dh_handletimestamp($risk_record['tstime']));
  }  
  if ( ($i/5) == intval($i/5)) {
    echo "... $i ";
  }
}
echo " - total $i records ";
$last24_varkeys = array(
  'frisk_pd_last24hrs_botrytis_idx', 
  'frisk_pd_last24hrs_blackrot_idx', 
  'frisk_pd_last24hrs_phomopsis_idx', 
  'frisk_pd_last24hrs_pmildew_idx'
);

foreach ($risk_models as $risk_model) {
  foreach ($last24_varkeys as $varkey) {
    $values = array(
      'featureid' => $risk_model,
      'entity_type' => 'dh_feature',
      'tstime' => date('Y-m-d'), // this will be overwritten by the plugin as there is only 1
      'varid' => $varkey
    );
    $tid = dh_update_timeseries($values, 'singular');
    echo "Updated $risk_model - $thisdate for varid = $varkey = tid $tid\n";
  }
}
?>
