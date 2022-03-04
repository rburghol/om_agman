<?php


// use in any page with 1-line:
//   module_load_include('inc', 'om_agman', 'src/pages/ipm_setup_chems_message');

$p = drupal_get_query_parameters();
$setup = isset($p['setup']) ? $p['setup'] : 0;
$text = array();
if ($setup) {
  $elements['message'] = array(
    '#weight' => -6, // we want this at the very top
    '#markup' => t('Step 4 of 4: Search spray materials to add to your vineyard list.'),
    '#prefix' => '<h3>',
    '#suffix' => '</h3>',
  );
} else {
  $text['message'] = array(
    '#weight' => -6, // we want this first
    '#markup' => t('Configure: Manage your spray materials.'),
    '#prefix' => '<h3>',
    '#suffix' => '</h3>',
  );
}
$text['info'] = array(
  '#weight' => -5, // we want this next
  '#markup' => t(
    "Below is a list of spray materials that are in our database.  Select the materials that you wish to appear in your spray planning list.  You may use the 'Search Settings' fields to filter on Material Type, Mode of Action, Brand Name, Target Organism (fungicide only), Active Ingredient and Pre-Harvest Interval (PHI)."
  ),
);
$message = drupal_render($text);
echo $message;
?>