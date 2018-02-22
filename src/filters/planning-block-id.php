global $user;
$block_id = isset($_SESSION['om_agman']['landunit']) ? $_SESSION['om_agman']['landunit'] : 'all';
if ($block_id == 'all') {
  $blocks = dh_get_facility_mps(explode(',',$view->args[0]));
} else {
  $blocks = array($block_id) ;
}
return implode(',', $blocks);