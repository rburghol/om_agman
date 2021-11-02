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
  'season' => url(implode('/', $a + array(3=>'season') )),
  'material' => url(implode('/', array($a[0], $a[1], $a[2], 'material', $a[4])	)),
  'frac' => url(implode('/', array($a[0], $a[1], $a[2], 'frac', $a[4]))),
  'usda-private' => url(implode('/', array($a[0], $a[1], $a[2], 'usda-private', $a[4]))),
  'phi' => url(implode('/', array($a[0], $a[1], $a[2], 'phi', $a[4]))),
  'ipmir' => url(implode('/', array($a[0], $a[1], $a[2], 'ipmir', $a[4]))),
//  'search' => url(implode('/', array($a[0], $a[1], $a[2], 'search', $a[4]))),
);
$targets = array();
$options = array(
  'default_value' => url($_GET['q']),
);
//dpm($options,'options');
$targets[$urls['season']] = 'Season Timeline';
$targets[$urls['material']] = 'By Material';
$targets[$urls['frac']] = 'By FRAC';
$targets[$urls['usda-private']] = 'USDA Private Grower Report';
$targets[$urls['phi']] = 'Pre-Harvest Interval Date Report';
$targets[$urls['ipmir']] = 'Community IPM Reports';
//$targets[$urls['search']] = 'Search';
$output = drupal_get_form('ctools_jump_menu', $targets, $options);
echo "<b>Show Report:</b><br>" . drupal_render($output);

?>