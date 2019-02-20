-- app qa -- check units matching/updating

drop view tmp_inventory_units CASCADE;
create or replace view tmp_inventory_units as (
  select c.name as chem_name, 
    c.adminid as chemid, 
    a.pid as inv_pid,
    a.propcode as inv_units, 
    d.propcode as chem_units, 
    b.field_link_agchem_material_erefid as link_id, 
    CASE 
      WHEN a.propcode = 'floz/acre' and d.propcode = 'qt/acre' 
        THEN  1.0 / 32.0 
      WHEN a.propcode = 'oz/acre' and d.propcode = 'floz/acre' 
        THEN 1.0 * 1.0 
      WHEN a.propcode = 'floz/gal' and d.propcode = 'floz/acre' 
        -- hmmmm... this is Oxidate, rate is given in floz/gal but rate limits are oz/acre? Verify...
        THEN 1.0 * 1.0 
      WHEN a.propcode = 'oz/acre' and d.propcode = 'lbs/acre' 
        THEN 1.0 / 16.0 
      WHEN a.propcode = 'oz/acre' and d.propcode = 'floz/acre' 
        THEN 1.0 * 1.0 
      WHEN a.propcode = 'floz/acre' and d.propcode = 'lbs/acre' 
        -- hmmmm... this is Elevate -- Verify...
        THEN 1.0 * 1.0 
      WHEN a.propcode = 'floz/acre' and d.propcode = 'oz/acre' 
      -- which is it? check the label, but no conversion needed
        THEN 1.0 * 1.0 
      WHEN a.propcode = 'floz/acre' and d.propcode = 'pt/acre' 
        THEN  1.0 / 16.0 
      WHEN a.propcode = 'oz/acre' and d.propcode = 'pt/acre' 
      -- floz is it? check the label
        THEN  1.0 / 16.0 
      ELSE -1.0 
      END as event_conv
  -- property for inventory item
  from dh_properties as a 
  -- inv item chem link
  left outer join field_data_field_link_agchem_material as b 
  on (
    a.featureid = b.field_link_agchem_material_erefid 
      and a.entity_type = 'field_link_agchem_material'
    ) 
  -- Chem
  left outer join dh_adminreg_feature as c 
  on (
    b.field_link_agchem_material_target_id = c.adminid
  )
  -- Chem amount units
  left outer join dh_properties as d 
  on (
    d.featureid = c.adminid 
    and d.entity_type = 'dh_adminreg_feature' 
    and d.varid = 178 
  )
  where a.varid = 196
  --and a.propcode <> d.propcode 
);

drop view tmp_event_unit_mismatches;
create view tmp_event_unit_mismatches as (
  select * from tmp_event_units 
  where event_conv > 0 
);

-- show actual conversions
select * from tmp_event_unit_mismatches
where event_conv > 0  and event_conv <> 1.0
order by chem_name;

-- show single event or chem
select *, round((event_total / event_acres)::numeric, 1) as rate_calc from tmp_event_units 
where chemid = 159 
where event_id = 226 ;

-- event adjusted rates.
select chem_name, event_id, event_rate, event_units, event_total, 
  event_acres, event_conv, chem_units, 
  round((event_conv * event_rate)::numeric,1) as rate_adj,
  round((event_conv * event_rate)::numeric,1)
from tmp_event_units 
where event_id = 740 ;

-- Update process
-- 1. Update amounts (revision, then value)
-- 2. Rates (revision, then value)
-- update amount first, then rate, since rate is what is used to detect mismatch
--   if we update rate first we can no longer find the mismatch record.

-- 197 - Serenade ASO
-- 180 - Phostrol
-- 165: Champ WG
-- 201: Miller Sulforix
-- now, show the adjusted rates.
\set chem 201

select chem_name, event_id, event_rate, event_units, event_total, 
  event_acres, event_conv, chem_units, 
  round((event_conv * event_rate)::numeric,1) as rate_adj,
  round((event_conv * event_total)::numeric,1) as total_adj
from tmp_event_units 
where chemid = :chem ;

-- Update Amounts for all Events
update dh_properties_revision set 
  propvalue = event_conv * event_total, 
  propcode = chem_units 
from tmp_event_unit_mismatches 
where pid = amt_pid 
and chemid = :chem;
update dh_properties set 
  propvalue = event_conv * event_total, 
  propcode = chem_units 
from tmp_event_unit_mismatches 
where pid = amt_pid 
and chemid = :chem;

-- Update Rates for all Events
update dh_properties_revision set 
  propvalue = round((event_conv * event_rate)::numeric,1), 
  propcode = chem_units 
from tmp_event_unit_mismatches 
where pid = rate_pid 
and chemid = :chem ;
update dh_properties set 
  propvalue = round((event_conv * event_rate)::numeric,1), 
  propcode = chem_units 
from tmp_event_unit_mismatches 
where pid = rate_pid 
and chemid = :chem ;


-- Dithane I messed up the Amount Update, so now this is done rate only then use batch save to 
-- update the amounts using the event mechanics.

-- Update Rates only for a single Event
update dh_properties_revision set 
  propvalue = round((event_conv * event_rate)::numeric,1), 
  propcode = chem_units 
from tmp_event_unit_mismatches 
where pid = rate_pid 
and chemid = 159 
-- single event
and event_id = 745;
update dh_properties set 
  propvalue = round((event_conv * event_rate)::numeric,1), 
  propcode = chem_units 
from tmp_event_unit_mismatches 
where pid = rate_pid 
and chemid = 159 
-- single event
and event_id = 745;

-- Update Rates for all Events
update dh_properties_revision set 
  propvalue = round((event_conv * event_rate)::numeric,1), 
  propcode = chem_units 
from tmp_event_unit_mismatches 
where pid = rate_pid 
and chemid = 159 ;
update dh_properties set 
  propvalue = round((event_conv * event_rate)::numeric,1), 
  propcode = chem_units 
from tmp_event_unit_mismatches 
where pid = rate_pid 
and chemid = 159 ;
