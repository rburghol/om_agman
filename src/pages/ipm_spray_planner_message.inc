<?php


// use in any page with 1-line:
//   module_load_include('inc', 'om_agman', 'src/pages/ipm_spray_planner_message');

$p = drupal_get_query_parameters();
$setup = isset($p['setup']) ? $p['setup'] : 0;
$text = array();

$text['message'] = array(
  '#weight' => -6, // we want this at the very top
  '#markup' => t('Spray Planner: View and edit all spray events.'),
  '#prefix' => '<h3>',
  '#suffix' => '</h3>',
);
// Show this header 
$message = drupal_render($text);
echo $message;
$edit_icon = "<a href='./' class='editlink'>&nbsp;</a>";
$copy_icon = "<a href='./' class='copylink'>&nbsp;</a>";
// show the planning info header 
$text = array();
$text['info'] = array(
  '#weight' => -5, // we want this next
  '#markup' => 
    t("This page shows a list of all spray activities, grouped by week.")
    . t("You may view previous or future seasons by clicking on the list labelled 'Select Year'.")
    . t("The columns on the right side of the planning table indicate the estimated efficacy of planned spray materials for preventing outbreaks for Powdery Mildew (PM), Black Rot (BR), Downy Mildew (DM), Phomopsis (Pho) and Botrytis (Bot).")
    . t("The section on this page entitled 'High Risk FRAC Warnings' will show information about specific FRAC categories that may be used too often in a single year. ")
);
$text['list'] = array(
  '#theme' => 'item_list',
  '#list_type' => 'ul',
  '#items' => array(
    t("To add an event in any week, click on 'Add Spray' and follow the instructions. ") . " ",
    t("To edit an existing spray event, click on the title, indicated by the edit icon ") . $edit_icon . " ",
    t("To make a copy of a spray event, click on copy icon ") . $copy_icon . " "
  ),
  '#wrapper_attributes' => ['class' => 'container'],
  '#weight' => 2, // we want this next
);
$message = drupal_render($text);
echo $message;
?>