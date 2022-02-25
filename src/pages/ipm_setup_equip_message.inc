<?php


// use in any page with 1-line:
//   module_load_include('inc', 'om_agman', 'src/pages/ipm_setup_equip_message');

$p = drupal_get_query_parameters();
$setup = isset($p['setup']) ? $p['setup'] : 0;
$text = array();
if ($setup) {
  $text['message'] = array(
    '#markup' => t('Step 3 of 4: Set up sprayer volume & rate information.'),
    '#prefix' => '<h3>',
    '#suffix' => '</h3>',
  );
} else {
  $text['message'] = array(
    '#markup' => t('Configure: Set up sprayer volume & rate information.'),
    '#prefix' => '<h3>',
    '#suffix' => '</h3>',
  );
}
$text['info'] = array(
  '#weight' => -5, // we want this next
  '#markup' => t(
    "In the fields below, please describe the volume of your sprayer, and approximate spray volume needed to cover an acre of vineyard. These value will be used to populate the defaults when you record a spray event."
  ),
);
$message = drupal_render($text);
echo $message;
?>