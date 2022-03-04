#!/user/bin/env drush
<?php
$farm_id = 146;

$q = "
  select a.hydroid,
    count(sl.*),
    chem.name as chem_name,
    chem.adminid as chem_id
  from dh_feature as a 
  left outer join field_data_dh_link_facility_mps as b 
  on (
    b.dh_link_facility_mps_target_id = a.hydroid
  )
  left outer join field_data_dh_link_feature_submittal as sl
  on (
    sl.dh_link_feature_submittal_target_id = b.entity_id 
  )
  left outer join field_data_field_link_to_registered_agchem as al
  on (
    al.entity_id = sl.entity_id
  )
  left outer join dh_adminreg_feature as chem 
  on (
    chem.adminid = al.field_link_to_registered_agchem_target_id
  )
  where a.hydroid = $farm_id 
  and chem.adminid not in (
    select b.adminid
    from dh_feature as a
    left outer join field_data_field_link_agchem_material as l
    on (
      a.hydroid = l.entity_id
    and l.entity_type = 'dh_feature'
    )
    left outer join dh_adminreg_feature as b
    on (
      b.adminid = l.field_link_agchem_material_target_id
    )
    where a.hydroid = $farm_id 
    and b.adminid is not null
  )
  group by a.hydroid, chem.name, chem.adminid ";
$result = db_query($q);
$farm = entity_load_single('dh_feature', $farm_id);
$lang = array_shift(array_keys($farm->field_link_agchem_material));
if (!$lang) {
  $lang = 'und';
}
$updated = FALSE;
dpm($farm,'farm');
dpm($farm->field_link_agchem_material,'old links');
while ($chem = $result->fetchAssoc()) {
  $value = array('target_id' => $chem['chem_id']);
  $farm->field_link_agchem_material[$lang][] = $value;
  //dpm($farm, 'farm');
  $updated = TRUE;
}

if ($updated) {
  dpm($farm,'farm w/new links');
  dsm("Saving $farm->name");
  $farm->save();
} else {
  dsm("No missing chems found");
}  
?>