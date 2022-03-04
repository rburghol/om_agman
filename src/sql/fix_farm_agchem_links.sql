-- vineyard is dh_feature
-- dh_link_feature_submittal links agchem submittal doc to vineyard
-- field_link_to_registered_agchem links agchem submittal doc to agchem 
-- need to find all chems that are linked to events that this farms 
-- blocks have applications for in case farm=>chem linkages get deleted
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
where a.hydroid = 146 
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
  where a.hydroid = 146 
  and b.adminid is not null
)
group by a.hydroid, chem.name, chem.adminid 
;
