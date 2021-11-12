<?php
// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/panes/ipm_year_jump');
// argument default
// if user has only one facility, return its ID, otherwise return 'multiple'
$a = arg();
ctools_include('jump-menu');
// if none, check for users farms 
$thisyear = date('Y');
$targets = array();
$options = array(
  'default_value' => url(
    $_GET['q'], 
    array(
      'query' =>drupal_get_query_parameters()
    )
  ),
);
//dpm($options,'opts');
$block_urls = array();
$years = range($thisyear - 10, $thisyear + 2);
foreach ($years as $year) {
  $base_path = url(
    implode('/', $a), 
    array(
      'query' => array(
        'year' => $year
      )
    )
  );
  $targets[$base_path] = $year;
}
//dpm($targets,'targs');

$output = drupal_get_form('ctools_jump_menu', $targets, $options);
echo "<b>Show Year:</b><br>" . drupal_render($output);

?>