<?php
$a = arg();
$u = drupal_get_query_parameters();
if (!isset($a[1])) {
  // block id
  $a[1] = 431;
} else {
  $fid = $a[1];
}
if (!isset($a[2])) {
  // block id
  $a[2] = 431;
} else {
  $luid = $a[2];
}
if (isset($a[3])) {
  $sub = $a[3];
  // arg 4 year, as opposed to the sub-page year 3
  if (isset($u['year'])) {
    $yr = $u['year'];
  } else {
    $yr = date('Y');
    if (intval(date('m')) > 11) {
      $yr += 1;
    }
  }
} else {
  $yr = date('Y');
  if (intval(date('m')) > 11) {
    $yr += 1;
  }
}
$ly = $yr - 1;
$ny = $yr + 1;
echo "<a href='" . base_path() . "?q=$a[0]/$fid/$luid/$sub&year=$ly'>$ly</a>";
echo " | $yr | ";
echo "<a href='" . base_path() . "?q=$a[0]/$fid/$luid/$sub&year=$ny'>$ny</a>";

?>