#!/user/bin/env drush
<?php
// this is for farms that accidentically had their agchem links removed.
// or removed on purpose, but they want them back.
// it goes through events associated with that farm and finds the chems 
// that were sprayed, since those are not deleted when a farm removes a 
// chem from a previous spray.
$farm_id = drush_shift();
$updated = 0;
$q = "
  select entity_id from (
  select field_link_agchem_material_erefid, entity_id, count(*) 
  from field_data_field_link_agchem_material 
  group by field_link_agchem_material_erefid, entity_id
) as foo 
where count > 1 and entity_id = $farm_id
group by entity_id ";

$result = db_query($q);
while ($entity = $result->fetchAssoc()) {
  
  $farm = entity_load_single('dh_feature', $entity['entity_id']);
  $farm_chems = $farm->field_link_agchem_material['und'];
  // we need to remove the old erefid from this?  formapi does. so we do it here.
  // we did NOT do this prior and had problems with duplicate erefid fields, which caused inventory issues
  $farm_chems = array_column($farm_chems, 'target_id');
  echo("Existing: " . print_r($farm->field_link_agchem_material['und'],1));
  $farm->field_link_agchem_material['und'] = array(); 
  // add the existing ones back
  foreach ($farm_chems as $chem_id) {
    $farm->field_link_agchem_material['und'][] = array('target_id' => $chem_id);
  }
  echo("would be changed to: " . print_r($farm->field_link_agchem_material['und'],1));
  //$farm->save();
  echo("would have Saved $farm->name / $farm->hydroid \n");
  //dpm($farm, 'farm');
  $updated = TRUE;
}

if (!$updated) {
  dsm("No misconfigured chems found");
}  
?>