<?php
// this will sho high risk FRACs, if they exist in this plan.
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

// get all blocks wfor this vineyard if a specific block was NOT requested
if (empty($block_id) or ($block_id == 'all') ) {
  $block_ids = dh_get_facility_mps($vineyard_id, 'landunit');
} else {
  $block_ids = array($block_id);
}

// set up date range from year passed in
$startdate = $yr . '-01-01';
$enddate = $yr . '-12-31';

$alerts = om_agman_group_frac_check($vineyard_id, $block_ids, $startdate, $enddate, array());
//dpm($alerts, 'alert');
$display = om_agman_show_formatted_frac_alerts($alerts, 2, 'table');
echo "<h3>High Risk FRAC Warnings:</h3>";
if (count($display['#rows']) > 0) {
  $output = render($display);
  echo $output;
} else {
  echo "None.";
}
	
?>