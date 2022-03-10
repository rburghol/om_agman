<?php
// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/panes/ipm-reports-menu');
// argument default
// if user has only one facility, return its ID, otherwise return 'multiple'
$a = arg();
ctools_include('jump-menu');
// if none, check for users farms 
if (count($a) <= 1) {
  global $user;
  $farms = dh_get_user_mgr_features($user->uid, 'facility', 'vineyard');
  if (count($farms) == 1) {
    $default = array_shift($farms);
    $a[] = $default;
  } else {
    // do NOT show this, 
    return;
  }
}
$urls = array(
  'season' => url(implode('/', array_replace($a, array(3=>'season')) )),
  'material' => url("ipm-user-reports/$a[1]/all" ),
  'frac' => url(implode('/', array_replace($a, array(3=>'frac')) )),
  'usda-private' => url(implode('/', array_replace($a, array(3=>'usda-private')) )),
  'phi' => url(implode('/', array_replace($a, array(3=>'phi')) )),
  'ipmir' => url(implode('/', array_replace($a, array(3=>'ipmir')) )),
//  'search' => url(implode('/', array_replace($a, array(3=>'season')) )),
);
$targets = array();
$options = array(
  'default_value' => url($_GET['q']),
);
dpm($urls,'urls');
$targets[$urls['season']] = 'Season Overview (shows all events)';
$targets[$urls['material']] = 'Spray Summary by Material';
$targets[$urls['frac']] = 'Spray Summary by FRAC';
$targets[$urls['usda-private']] = 'USDA Private Grower Report';
$targets[$urls['phi']] = 'Pre-Harvest Interval Date Report';
$targets[$urls['ipmir']] = 'Community IPM Reports';
//$targets[$urls['search']] = 'Search';
$output = drupal_get_form('ctools_jump_menu', $targets, $options);
echo "<b>Show Report:</b><br>" . drupal_render($output);

?>