<?php
// use in any page with 1-line:
//   module_load_include('inc', 'om_agman', 'src/panes/ipm_materials_list');
$a = arg();
$farm_id = $a[1];
$max_length = 50;
$farm = entity_load_single('dh_feature', $farm_id);
$farm_chems = $farm->field_link_agchem_material['und'];
//dpm($farm ,'farm ');
$farm_chems = array_column($farm_chems, 'target_id');

function om_agman_farm_materials($farm_id, $material_ftype = '') {
  $q = "  select a.hydroid as farm_id, a.name as farm_name, ";
  $q .= "   b.adminid, b.name as chem_name ";
  $q .= " from dh_feature as a ";
  $q .= " left outer join field_data_field_link_agchem_material as l ";
  $q .= " on ( ";
  $q .= "    a.hydroid = l.entity_id ";
  $q .= "    and l.entity_type = 'dh_feature' ";
  $q .= "  ) ";
  $q .= "  left outer join dh_adminreg_feature as b ";
  $q .= " on ( ";
  $q .= "     b.adminid = l.field_link_agchem_material_target_id ";
  $q .= "  ) ";
  $q .= "  where a.hydroid = $farm_id ";
  if ($material_ftype <> '') {
    $q .= "  and c.ftype = '$material_ftype' ";
  }

  //dpm($q,'q');
  $chems = array();
  $brief_list = array();
  $result = db_query($q);

  while ($chem = $result->fetchAssoc()) {
   $chems[] = $chem;
  }
  return $chems;
}
// Get all chems for this farm
if (!empty($farm_id)) {
  $chems = om_agman_farm_materials($farm_id);
  // compile a list of the first 24 for display purposes
  $brief_list = array_column($chems, 'chem_name');
  if (count($brief_list) > $max_length) {
    $brief_list = array_slice($brief_list, 0, $max_length);
  }

  $text = array();

  $l_title = t("Click here to manage add or remove materials from this list. ");
  $l_url = "/ipm-facility-materials/$farm_id/search";
  $l_manage = l( $l_title, $l_url, array(
    'attributes' => array(
        'class' => array('editlink')
      )
    )
  );
  $text['info'] = array(
    '#weight' => 1, // we want this first
    '#markup' => t("The following spray materials are linked to ") . $farm->name . "."
                 . (" and will appear in your spray planning screens.") 
  );

  // Create the list of chemicals
  $brief_str = implode(", ", array_values($brief_list));
  $et_al = ".";
  if (count($brief_list) < count($chems)) {
    $et_al = "<br> ... and " . (count($chems) - count($brief_list)) . " others.";
  }
  $text['list'] = array(
    '#theme' => 'item_list',
    '#list_type' => 'ul',
    '#items' => [$brief_str . $et_al],
    '#wrapper_attributes' => ['class' => 'container'],
    '#weight' => 2, // we want this next
  );
  // only show the link to edit if we are NOT on the list edit page.
  //dpm($a,'a');
  if ($a[0] <> 'ipm-facility-materials') {
    $text['link'] = array(
      '#weight' => 3, // we want this last
      '#markup' => "<b>" . $l_manage . "</b>"
    );
  }
  $message = drupal_render($text);
  echo $message;
}
?>