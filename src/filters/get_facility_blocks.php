global $user;
$facs = $view->args[0];
if ($facs == 'all') {
  $facs = dh_get_user_mgr_features($user->uid, 'facility', 'vineyard');
}
$blocks = dh_get_facility_mps($facs, 'landunit');
return implode(',', $blocks);