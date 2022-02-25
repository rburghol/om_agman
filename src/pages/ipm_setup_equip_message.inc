<?php


// use in any page with 1-line:
//   module_load_include('inc', 'om_agman', 'src/pages/ipm_setup_equip_message');

$p = drupal_get_query_parameters();
$setup = isset($p['setup']) ? $p['setup'] : 0;
$text = array();
if ($setup) {
  $text['message'] = array(
    '#markup' => t('Step 3 of 4: Set up sprayer information.'),
    '#prefix' => '<h3>',
    '#suffix' => '</h3>',
  );
}
$message = drupal_render($text);
echo $message;
?>