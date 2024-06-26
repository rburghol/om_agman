-- get the base inventory query
\set facid 6298
select target_label, from_id, featureid AS eref_id, target_id, pid, propvalue from (
  select var.hydroid as varid,  var.varname, var.varkey, var.varunits,  p.pid, eref.field_link_agchem_material_erefid as featureid,  'field_link_agchem_material' AS entity_type, p.bundle,   p.startdate, p.enddate, p.propname, p.propcode,  p.propvalue, p.modified,  targ.name as target_label,  ent.hydroid as from_id, eref.field_link_agchem_material_target_id as target_id  
  from dh_feature as ent  
  left outer join field_data_field_link_agchem_material as eref  
  on (    eref.entity_id = ent.hydroid    AND eref.entity_type = 'dh_feature'  )  
  left outer join dh_adminreg_feature as targ  
  on (    eref.field_link_agchem_material_target_id = targ.adminid  )  
  left outer join dh_variabledefinition as var 
  on (var.varkey in ('agchem_inventory_amt'))  
  left outer join dh_properties as p  
  on (    
    p.featureid = eref.field_link_agchem_material_erefid    
	AND var.hydroid = p.varid    
	AND p.entity_type = 'field_link_agchem_material'  
  )  
  WHERE var.hydroid IS NOT NULL  AND (ent.hydroid in (:facid))  ORDER BY targ.name  LIMIT 100
) as foo;

-- check for erefid duplicates (shouldn't happen, but there are a bunch):
select * from (
  select field_link_agchem_material_erefid, entity_id, count(*) 
  from field_data_field_link_agchem_material 
  group by field_link_agchem_material_erefid, entity_id
) as foo 
where count > 1;


-- get facids with any duplicate erefids 
select entity_id from (
  select field_link_agchem_material_erefid, entity_id, count(*) 
  from field_data_field_link_agchem_material 
  group by field_link_agchem_material_erefid, entity_id
) as foo 
where count > 1
group by entity_id;

-- find dupe eref_ids for a single facility
select entity_id, field_link_agchem_material_erefid from (
  select field_link_agchem_material_erefid, entity_id, count(*)
  from field_data_field_link_agchem_material
  group by field_link_agchem_material_erefid, entity_id
) as foo
where count > 1
group by entity_id, field_link_agchem_material_erefid
order by entity_id;
