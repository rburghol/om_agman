global $user;
$vineyard_id = isset($_SESSION['om_agman']['facility']) ? $_SESSION['om_agman']['facility'] : 'all';
return $vineyard_id;
// ************ DISABLED *****
//   Not needed and causes error 
//   May be useful if we ever decide
//   to let the user select multiple vineyards, but not ALL at once?
// *****************
if ($vineyard_id == 'all') {
  $fs = dh_get_user_mgr_features($user->uid);
} else {
  $fs = array($vineyard_id);
}
return implode(',', $fs);