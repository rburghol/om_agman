<?php


// use in any page with 1-line:
//   module_load_include('inc', 'om_agman', 'src/pages/ipm_setup_chems_message');

$p = drupal_get_query_parameters();
$setup = isset($p['setup']) ? $p['setup'] : 0;
$farm = entity_load_single('dh_feature', $form_state['values']['farm_id']);
$farm_chems = $farm->field_link_agchem_material['und'];
$farm_chems = array_column($farm_chems, 'target_id');
if (empty($farm_chems)) {
  $setup = 1;
}
$text = array();
if ($setup) {
  $text['message'] = array(
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
// Show this header 
$message = drupal_render($text);
echo $message;


// Show the currently linked chems 
module_load_include('inc', 'om_agman', 'src/panes/ipm_materials_list');

// show the search info header 
$text = array();
$text['info'] = array(
  '#weight' => -5, // we want this next
  '#markup' => t(
    "To add additional materials to your vineyard spray planning list, click the checkboxes next to the desired material in the search results below.")
	. "<br>" . t("A") . " <input type='checkbox' checked>" . t(" indicates that this material is already in your list.")
    . t("	You may use the 'Search Filters' fields to filter on Material (Brand) Name, Material Type, Mode of Action, Target Organism (fungicide only), Active Ingredient and Pre-Harvest Interval (PHI).")
  ,
);
$message = drupal_render($text);
echo $message;
?>