<?php
// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/panes/ipm-upcoming');
$a = arg();
if ($a[0] == 'admin') {
  // we are in a views preview, just grab arg 7 and fudge it for testing
  $a = array('ipm-facility-materials', $a[7]);
}
$date = date('Y-m-d');
$vineyard = $a[1];
$uri = "ipm-live-events/all/materials/add/$vineyard/$date";
$add_spray_link = l(t("Click here to add a new spray event."), $uri, array('query' => array('finaldest' => 'ipm-home')));

$vineyard = entity_load_single('dh_feature', $a[1]);
$text = array();

$text['info'] = array(
  '#weight' => 1, // we want this next
  '#markup' => 
    t("Below is a list of upcoming events for") . " " . $vineyard->name . ".  "
   // . "<br>" . t("In order to add new events, select 'Add a Spray' from the 'Function List' above, or ") 
    . "<br>" . "<b>$add_spray_link</b>"
);
$content = drupal_render($text);
echo $content;
?>