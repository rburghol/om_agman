<?php  
module_load_include('module', 'om_agman');
$status = module_load_include('inc', 'om_agman', 'src/lib/om_agman_frac');
// Get URL arguments
$args = arg(); // this is a Drupal function that pulls things in that were passed like a/b/c
//dpm($args,'args in via URL'); // provides debugging information -- comment out for production
$ao = 0; // Argument Offset -- 0 if this is a page, and 2 if this is a node
$vineyard_id = $args[$ao + 1];
$block_id = count($args) > ($ao + 1) ? $args[$ao + 2] : 'all';
//dsm("Searching for vineyard $vineyard_id and blocks $block_id");
$u = drupal_get_query_parameters(); // this is a Drupal function that pulls things that were passed in like "a=100&b=200&c=300"
if (isset($u['year'])) {
  $yr = $u['year'];
} else {
  $yr = date('Y');
  if (intval(date('m')) > 11) {
    $yr += 1;
  }
}

// set up date range from year passed in
$startdate = $yr . '-01-01';
$enddate = $yr . '-12-31';

// get all blocks wfor this vineyard if a specific block was NOT requested
if ($block_id == 'all') {
  $block_ids = dh_get_facility_mps($vineyard_id, 'landunit');
} else {
  $block_ids = array($block_id);
}

//dpm($risky_frac,'risky');

$alerts = om_agman_group_frac_check($vineyard_id, $block_ids, $startdate, $enddate, array());
//dpm($alerts,'alerts');
om_agman_show_formatted_frac_alerts($alerts, 0);

// got through all blocks
foreach ($block_ids as $block_id) {
  $block = entity_load_single('dh_feature', $block_id);
  if (is_object($block)) {
    $rez = om_agman_frac_count($block_id, $vineyard_id, $startdate, $enddate, array());
    $alerts = om_agman_group_frac_check($vineyard_id, array($block_id), $startdate, $enddate, array());
    $output = om_agman_show_formatted_frac_alerts($alerts, 0, 'table');
    $label = "Name: <strong>" . $block->name . "</strong><br>";
    echo $label;
    if (count($alerts) > 0) {
      echo $output;
    } else {
      echo "No FRAC application alerts.";
    }
    echo "<hr>";
  }
}
?>