// get the blocks
$facs = $view->args[0];
if ($facs == 'all') {
  global $user;
  $facs = dh_get_user_mgr_features($user->uid);
}  
if ($view->args[1] == 'all') {
  $blocks = dh_get_facility_mps($facs, 'landunit');
} else {
  $blocks = explode(',', $view->args[1]);
}
array_merge($blocks, explode(',',$view->args[0])); //add the facility
$eref_config = array();
$eref_config['eref_fieldname'] = 'dh_link_feature_submittal';
$eref_config['target_entity_id'] = $blocks;
$eref_config['entity_type'] = 'dh_adminreg_feature';
$eref_config['entity_id_name'] = 'adminid';

$events = dh_get_reverse_erefs($eref_config);
//dpm($events,'events');
return implode(',', $events );