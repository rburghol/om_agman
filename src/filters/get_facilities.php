global $user;
$vineyard_id = isset($_SESSION['om_agman']['facility']) ? $_SESSION['om_agman']['facility'] : 'all';
if ($vineyard_id == 'all') {
  $fs = dh_get_user_mgr_features($user->uid);
} else {
  $fs = array($vineyard_id);
}
return implode(',', $fs);