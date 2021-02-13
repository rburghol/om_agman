<?php  
module_load_include('module', 'om_agman');
$status = module_load_include('inc', 'om_agman', 'src/lib/om_agman_frac');
// Get URL arguments
$args = arg(); // this is a Drupal function that pulls things in that were passed like a/b/c
dpm($args,'args in via URL'); // provides debugging information -- comment out for production
$ao = 0; // Argument Offset -- 0 if this is a page, and 2 if this is a node
$vineyard_id = $args[$ao + 1];
$block_id = count($args) > ($ao + 1) ? $args[$ao + 2] : 'all';
dsm("Searching for vineyard $vineyard_id and blocks $block_id");
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

// got through all blocks
foreach ($block_ids as $block_id) {
  $rez = om_agman_frac_count($block_id, $vineyard_id, $startdate, $enddate, $target_fracs = array());

  //dpm($q,'q');
  // This formats the result for printing, this can stay as is more or less 
  $header = array("FRAC", 'Max/year', '# of Apps', 'Rating', 'Status');
  $data = array();
  $row_count = 0;
  while ($row = $rez->fetchAssoc()) {
    if ($row_count == 0) {
      echo "<strong>" . $row['block_name'] . "</strong><br>";
      $row_count++;
    }
    $frac_count = $row['frac_app_count'];
    $frac = empty(trim($row['material_frac'])) ? 'n/a' : $row['material_frac'];
    $row['material_frac'] = $frac;
    unset($row['block_name']);
    
    $status = om_agman_frac_assess($frac, $frac_count);
    $rating = $status['rating'];
    $message = $status['message']; 
    
    $row['rating'] = $rating;
    $row['message'] = $message;
    $data[] = array_values($row);
  }
  //dpm($data,'data');
  $display = array(
    '#theme' => 'table',
    '#header' => $header,
    '#rows' => $data,
  );
  $output = render($display);

  echo $output;
  echo "<hr>";
}
?>