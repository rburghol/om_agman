<?php
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
    dsm("No Block Indicated.", 'error');
    die;
  }
}
if (count($a) < 3) {
  $a[2] = 'all';
}
$blocks = dh_get_facility_mps($a[1], 'landunit');
$block_selected = ($a[2] == 'all') ? $blocks[current($blocks)] : $a[2];

dpm($blocks,'blocks');
$targets = array();
$options = array(
  'default_value' => url($_GET['q']),
);
$block_urls = array();
foreach ($blocks as $block) {
  $block_path = url(implode('/', array($a[0], $a[1], $block, 'season')));
  if (count($a) > 4) {
    for ($i = 4; $i < count($a); $i++) {
      $block_path .= "/" . $a[$i];
    }
  }
  $block_obj = entity_load_single('dh_feature', $block);
  $targets[$block_path] = $block_obj->name;
}
dpm($targets,'targs');

$output = drupal_get_form('ctools_jump_menu', $targets, $options);
echo "<b>Show Block:</b><br>" . drupal_render($output);

?>