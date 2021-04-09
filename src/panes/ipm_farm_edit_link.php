<?php

$a = arg();
// load farm
$facility = entity_load_single('dh_feature', $a[1]);
$facid = $a[1];
$el =  = l(
  "&nbsp;", 
  "ipm-edit-vineyard/edit/$facid&destination=ipm-facility-info/$facid",
  array('class' => array('editlink'))
);

$dl = l(
  "&nbsp;", 
  "admin/content/dh_features/manage/$facid/delete",
  array('class' => array('subtractlink'))
);
echo "Viewing: $facility$name " . $el . " " . $dl;
?>