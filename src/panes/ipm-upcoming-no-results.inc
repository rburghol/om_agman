<?php
$args = arg();
$args[0] = 'ipm-facility-sprayplan';
$add_spray_link = l("Click here to add a new spray event.", implode('/',$args));

$vineyard = entity_load_single('dh_feature', $a[1]);
$text = array();

$text['info'] = array(
  '#weight' => 1, // we want this next
  '#markup' => 
    t("Below is a list of upcoming events for") . " <b>" . $vineyard->name . "</b>.  "
    . t("In order to add more events, select 'Add a Spray' from the 'Function List' above, or ") 
    . $add_spray_link
    . "."
);
?>