<?php
// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/panes/ipm-upcoming-no-results');
$args = arg();
if ($a[0] == 'admin') {
  // we are in a views preview, just grab arg 7 and fudge it for testing
  $a = array('ipm-facility-materials', $a[7]);
}
$args[0] = 'ipm-facility-sprayplan';
$add_spray_link = l(t("Click here to add a new spray event."), implode('/',$args));

$vineyard = entity_load_single('dh_feature', $a[1]);
$text = array();

$text['info'] = array(
  '#weight' => 1, // we want this next
  '#markup' => 
    t("You have no events upcoming for") . " <b>" . $vineyard->name . "</b>.  "
    . t("In order to add events, select 'Add a Spray' from the 'Function List' above, or ") 
    . "<b>$add_spray_link</b>"
);

// Could do a list if it works best
//       '#theme' => 'item_list',
//       '#list_type' => 'ul',
//       '#items' => [
//        "<b> Item 1: </b>" . $i1,
//        "<b> Item 2: </b>" . $i2,
$content = drupal_render($text);
echo $content;
?>