$p = drupal_get_query_parameters();
if (isset($p['year'])) {
  $yr = $p['year'];
} else {
  $yr = date('Y');
  if (intval(date('m')) > 10) {
    $yr += 1;
  }
}
return $yr;