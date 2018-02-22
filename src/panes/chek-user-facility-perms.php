global $user;
//dpm($user);
$params = drupal_get_query_parameters();
// * Check user perms on the facility
$args = arg();
//dpm($args);
$facid = $args[1];

$admins = dh_get_user_mgr_features($user->uid);
if (in_array($facid, $admins) or !$facid) {
  // if facid is empty we are at a decision point anyhow, so no worries
  $has_perms = TRUE;
} else {
  $has_perms = FALSE;
  drupal_set_message("You do not have permissions to manage this location.");
}
return $has_perms;