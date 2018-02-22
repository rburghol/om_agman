// argument default
// if user has only one facility, return its ID, otherwise return 'multiple'
global $user;
// if none, check for users farms 
$farms = dh_get_user_mgr_features($user->uid, 'facility', 'vineyard');
if (count($farms) == 1) {
  return array_shift($farms);
}
return 'multiple';

// argument validator
if (!is_numeric($argument)) {
  return FALSE;
}
return TRUE;