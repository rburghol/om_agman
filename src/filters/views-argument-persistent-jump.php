// argument default
// if there are no args, we either just got here or we clicked a menu that can't pass dynamic args
// therefore, we need to guess.  If the user wants to show "all" clicking will give us that

// nav jump list needs to save it's args
// also, all views that use this jump list need to save their args
// when we migrate to D8: https://atendesigngroup.com/blog/storing-session-data-drupal-8 

// return vineyared_id
return isset($_SESSION['om_agman']['facility']) ? $_SESSION['om_agman']['facility'] : 'all';
// return block_id
return isset($_SESSION['om_agman']['landunit']) ? $_SESSION['om_agman']['landunit'] : 'all';

// default
$vineyard_id = isset($_SESSION['om_agman']['facility']) ? $_SESSION['om_agman']['facility'] : 'all';
$block_id = isset($_SESSION['om_agman']['landunit']) ? $_SESSION['om_agman']['landunit'] : 'all';

// validation
$_SESSION['om_agman']['facility'] = $argument;
$_SESSION['om_agman']['landunit'] = $argument;
return TRUE;