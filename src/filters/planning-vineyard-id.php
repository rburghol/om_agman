global $user;
$vineyard_id = isset($_SESSION['om_agman']['facility']) ? $_SESSION['om_agman']['facility'] : 'all';
// the planning view does NOT allow multiple Vineyards (only multiple blocks)
// so if 'all' is found in URL or SESSION, we return 'multiple' and then reject in validation
// validation checks for a string, and fails, since 'all' is a special word we must rewrite
if ($vineyard_id == 'all') {
  $vineyard_id = 'multiple';
}
return $vineyard_id;