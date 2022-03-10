<?php
// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/panes/ipm_setup_complete');
$parms = drupal_get_query_parameters();
$a = arg();
/*
if (!empty($parms['setup'])) {
  $farm = entity_load_single('dh_feature', $a[1]);
  
  $text = array();
  $text['message'] = array(
    '#weight' => -6, // we want this at the very top
    '#markup' => '<b>' 
      . t('Congratulations!') 
      . t('You have completed setting up your vineyard. ')
      . t('You may now begin using this application to assist you with Integrated Pest Management. ')
      . t('Follow the links below  or select \'Planning\' from the main menu to plan your spray events.')
      . t('If you have questions, select \'Help\' from the main menu to see more information about how to use this site.')
    '#prefix' => '<h4>',
    '#suffix' => '</h4>',
  );
  // Show this header 
  $message = drupal_render($text);
  echo $message;

}
*/  
?>