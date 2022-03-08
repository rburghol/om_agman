<?php
// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/panes/ipm_year_jump');
// argument default
// if user has only one facility, return its ID, otherwise return 'multiple'
$a = arg();
ctools_include('jump-menu');
// if none, check for users farms 
$thisyear = date('Y');
if (intval(date('m')) > 10) {
  $thisyear += 1;
}
$params = drupal_get_query_parameters();
$targets = array();
$options = array(
  'default_value' => url(
    $_GET['q'], 
    array(
      'query' => $params
    )
  ),
);
//dpm($options,'opts');
$block_urls = array();
$years = range($thisyear - 10, $thisyear + 2);
foreach ($years as $year) {
  $url_options = array(
    'query' => array(
      'year' => $year
    )
  );
  if (!isset($params['year'])) {
    // blank out the query if the current year is used by default here
    if ($thisyear == $year) {
      $url_options = array();
    }
  }
  $base_path = url(
    implode('/', $a), 
    $url_options
  );
  $targets[$base_path] = $year;
}
//dpm($targets,'targs');

$output = drupal_get_form('ctools_jump_menu', $targets, $options);
echo "<b>" . t("Select year to show:") . "</b><br>" . drupal_render($output);

?>