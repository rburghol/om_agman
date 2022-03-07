<?php

// use in any page with 1-line:
//   module_load_include('inc', 'om_agman', 'src/pages/ipm_show_farm_chemlist');

$p = drupal_get_query_parameters();
$a = arg();
if ($a[0] == 'admin') {
  // we are in a views preview, just grab arg 7 and fudge it for testing
  $a = array('ipm-facility-materials', $a[7]);
}
//dpm($a,'args');
$vineyard = entity_load_single('dh_feature', $a[1]);
$text = array();

$text['info'] = array(
  '#weight' => 1, // we want this next
  '#markup' => 
    t("Below is a list of spray materials that will appear in the spray material list for ") . "<b>" . $vineyard->name . "</b>.  "
    . t("In order to add more materials, select 'Search for New Materials' from the drop-down list above, or ") 
    . l(t("Click here to find new spray materials"), implode('/', array($a[0], $a[1], 'search')))
    . "."
);
$message = drupal_render($text);
echo $message;
?>