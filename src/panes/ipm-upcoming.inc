<?php
// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/panes/ipm-upcoming');
$a = arg();
if ($a[0] == 'admin') {
  // we are in a views preview, just grab arg 7 and fudge it for testing
  $a = array('ipm-facility-materials', $a[7]);
}
$a[0] = 'ipm-facility-sprayplan';
$add_spray_link = l(t("Click here to add a new spray event."), implode('/',$a));

$vineyard = entity_load_single('dh_feature', $a[1]);
$text = array();

$text['info'] = array(
  '#weight' => 1, // we want this next
  '#markup' => 
    t("Below is a list of upcoming events for") . $vineyard->name . ".  "
    . "<br>" . t("In order to add new events, select 'Add a Spray' from the 'Function List' above, or ") 
    . "<br>" . "<b>$add_spray_link</b>"
);
$content = drupal_render($text);
echo $content;
?>