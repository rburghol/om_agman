select 'fungicide_formulary_registration_epa_info_etc_', 'dh_adminreg_feature',  
a.adminid,  
case  
  WHEN ( reg.registration_id_value IS NULL) OR  
  
 (reg.registration_id_value = '') THEN admincode  
 ELSE reg.registration_id_value  
END as guid,  
-1, 0, ''from dh_adminreg_feature as a left outer join field_data_registration_id as reg on (a.adminid = reg.entity_id)left outer join feeds_item as bon (a.adminid = b.entity_id and b.id = 'fungicide_formulary_registration_epa_info_etc_')where b.entity_id is null and a.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer')and a.bundle = 'registration';

select entity_id, guid from feeds_item where  id = 'fungicide_formulary_registration_epa_info_etc_' and modified >= now() - interval '1 hour';
select entity_id, guid from feeds_item where  id = 'fungicide_formulary_registration_epa_info_etc_' and modified >= now() - interval '1 hour';


select entity_id, guid from feeds_item where  id = 'fungicide_formulary_registration_epa_info_etc_' and imported >= now() - interval '1 hour';
select entity_id, guid from feeds_item where  id = 'fungicide_formulary_registration_epa_info_etc_' and imported >= extract (epoch from now() - interval '1 hour');
select entity_id, guid from feeds_item where  id = 'fungicide_formulary_registration_epa_info_etc_';
select 'fungicide_formulary_registration_epa_info_etc_', 'dh_adminreg_feature',  
a.adminid,  
case  
  WHEN ( reg.registration_id_value IS NULL) OR  
  
 (reg.registration_id_value = '') THEN admincode  
 ELSE reg.registration_id_value  
END as guid,  
-1, 0, ''from dh_adminreg_feature as a left outer join field_data_registration_id as reg on (a.adminid = reg.entity_id)left outer join feeds_item as bon (a.adminid = b.entity_id and b.id = 'fungicide_formulary_registration_epa_info_etc_')where b.entity_id is null and a.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer')and a.bundle = 'registration';


update feeds_item set guid = reg.registration_id_value from dh_adminreg_feature as a left outer join field_data_registration_id as reg on (a.adminid = reg.entity_id)where feeds_item.entity_id = adminid and id = 'fungicide_formulary_registration_epa_info_etc_' and guid <> reg.registration_id_value;


update feeds_item set guid = reg.registration_id_value from dh_adminreg_feature as a left outer join field_data_registration_id as reg on (a.adminid = reg.entity_id)where feeds_item.entity_id = adminid and id = 'fungicide_formulary_registration_epa_info_etc_' and guid <> reg.registration_id_value;
select entity_id, guid from feeds_item where  id = 'fungicide_formulary_registration_epa_info_etc_';
select entity_id, guid from feeds_item where  id = 'fungicide_formulary_registration_epa_info_etc_' and guid like ' %';
update feeds_item set guid = reg.registration_id_value from dh_adminreg_feature as a left outer join field_data_registration_id as reg on (a.adminid = reg.entity_id)where feeds_item.entity_id = adminid and id = 'fungicide_formulary_registration_epa_info_etc_' and guid <> reg.registration_id_value;
select entity_id, guid from feeds_item where  id = 'fungicide_formulary_registration_epa_info_etc_' and guid like ' %';
select entity_id, guid from feeds_item where  id = 'fungicide_formulary_registration_epa_info_etc_' and guid like ' %';
update feeds_item set guid = reg.registration_id_value from dh_adminreg_feature as a left outer join field_data_registration_id as reg on (a.adminid = reg.entity_id)where feeds_item.entity_id = adminid and id = 'fungicide_formulary_registration_epa_info_etc_' and guid <> reg.registration_id_value;
select * from feeds_item where id = 'ipm_fungicide_frac_code_importer' and ;
select * from feeds_item where id = 'ipm_fungicide_frac_code_importer' and imported > 0;
select * from feeds_item where id = 'ipm_fungicide_frac_code_importer' and imported > extract(epoch from now() - interval '1 day');
select * from feeds_item where id = 'ipm_fungicide_frac_code_importer' and imported > extract(epoch from now() - interval '1 day');
update feeds_item set guid = reg.registration_id_value from dh_adminreg_feature as a left outer join field_data_registration_id as reg on (a.adminid = reg.entity_id)where feeds_item.entity_id = adminid and id = 'fungicide_formulary_registration_epa_info_etc_' and guid <> reg.registration_id_value;
select * from feeds_item where id = 'ipm_fungicide_frac_code_importer' and imported > extract(epoch from now() - interval '1 day');
select * from feeds_item  where entity_id = 486;
update feeds_item set guid = reg.registration_id_value from dh_adminreg_feature as a left outer join field_data_registration_id as reg on (a.adminid = reg.entity_id)where feeds_item.entity_id = adminid and id = 'fungicide_formulary_registration_epa_info_etc_' and guid <> reg.registration_id_value;
select * from feeds_item  where entity_id = 486;
select * from feeds_item  where entity_id = 486;
select * from feeds_item  where id = 'ipm_fungicide_frac_code_importer; and guid like 'U0%';';
select * from feeds_item  where id = 'ipm_fungicide_frac_code_importer' and guid like 'U0%';
select * from feeds_item  where id = 'ipm_fungicide_frac_code_importer' and guid like '%U0%';
select * from feeds_item  where id = 'ipm_fungicide_frac_code_importer' and guid like '%M1%';
select * from feeds_item  where id = 'ipm_fungicide_frac_code_importer' and guid like '%M1%';
select * from feeds_item  where id = 'ipm_fungicide_frac_code_importer' and guid like '%M1%';
select * from feeds_item  where id = 'ipm_fungicide_frac_code_importer' and guid like '%M1%';
select * from feeds_item  where id = 'ipm_fungicide_frac_code_importer' and guid like '%M1%';
select * from feeds_item  where id = 'ipm_fungicide_frac_code_importer' and guid like '%M1%';
select * from feeds_item  where id = 'ipm_fungicide_frac_code_importer';
select 'ipm_fungicide_frac_code_importer', 'dh_properties',  
a.pid, frac.featureid || '-' || frac.propcode as guid,  
-1, 0, ''from dh_adminfrac_feature as a left outer join dh_properties as frac on (a.adminid = frac.featureid)left outer join feeds_item as bon (frac.pid = b.entity_id and b.id = 'ipm_fungicide_frac_code_importer')where b.entity_id is null and a.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer')and a.bundle = 'registration';
select 'ipm_fungicide_frac_code_importer', 'dh_properties',  
a.pid, frac.featureid || '-' || frac.propcode as guid,  
-1, 0, ''from dh_adminreg_feature as a left outer join dh_properties as frac on (a.adminid = frac.featureid)left outer join feeds_item as bon (frac.pid = b.entity_id and b.id = 'ipm_fungicide_frac_code_importer')where b.entity_id is null and a.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer')and a.bundle = 'registration';
select 'ipm_fungicide_frac_code_importer', 'dh_properties',  
frac.pid, frac.featureid || '-' || frac.propcode as guid,  
-1, 0, ''from dh_adminreg_feature as a left outer join dh_properties as frac on (a.adminid = frac.featureid)left outer join feeds_item as bon (frac.pid = b.entity_id and b.id = 'ipm_fungicide_frac_code_importer')where b.entity_id is null and a.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer')and a.bundle = 'registration';
select 'ipm_fungicide_frac_code_importer', 'dh_properties',  
frac.pid, frac.featureid || '-' || frac.propcode as guid,  
-1, 0, ''from dh_adminreg_feature as a left outer join dh_properties as frac on (a.adminid = frac.featureid)left outer join feeds_item as bon (frac.pid = b.entity_id and b.id = 'ipm_fungicide_frac_code_importer')where b.entity_id is null and frac.varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_frac')and a.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer')and a.bundle = 'registration';
insert into feeds_item (id,entity_type, entity_id, guid, hash, feed_nid, url)select 'ipm_fungicide_frac_code_importer', 'dh_properties',  
frac.pid, frac.featureid || '-' || frac.propcode as guid,  
-1, 0, ''from dh_adminreg_feature as a left outer join dh_properties as frac on (a.adminid = frac.featureid)left outer join feeds_item as bon (frac.pid = b.entity_id and b.id = 'ipm_fungicide_frac_code_importer')where b.entity_id is null and frac.varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_frac')and a.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer')and a.bundle = 'registration';
select * from feeds_item where id = 'ipm_fungicide_frac_code_importer' and imported > extract(epoch from now() - interval '1 day');
select * from feeds_item where id = 'ipm_fungicide_frac_code_importer' and imported > extract(epoch from now() - interval '1 hour');
select * from feeds_item where id = 'ipm_fungicide_lo_rate';
select * from feeds_item where id = 'ipm_fungicide_lo_rate';
select * from feeds_item where id = 'ipm_fungicide_lo_rate';
select * from feeds_item where id = 'ipm_fungicide_hi_rate';
select 'ipm_fungicide_lo_rate', 'dh_properties',  
frac.pid, frac.featureid as guid,  
-1, 0, ''from dh_adminreg_feature as a left outer join dh_properties as frac on (a.adminid = frac.featureid)left outer join feeds_item as bon (frac.pid = b.entity_id and b.id = 'ipm_fungicide_lo_rate')where b.entity_id is null and frac.varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_rate_lo_nond')and a.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer')and a.bundle = 'registration';
select 'ipm_fungicide_lo_rate', 'dh_properties',  
frac.pid, frac.featureid as guid,  
-1, 0, ''from dh_adminreg_feature as a left outer join dh_properties as frac on (a.adminid = frac.featureid)left outer join feeds_item as bon (frac.pid = b.entity_id and b.id = 'ipm_fungicide_lo_rate')where b.entity_id is null and frac.varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_rate_lo_nond')and a.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer')and a.bundle = 'registration';
select 'ipm_fungicide_lo_rate', 'dh_properties',  
frac.pid, frac.featureid as guid,  
-1, 0, ''from dh_adminreg_feature as a left outer join dh_properties as frac on (a.adminid = frac.featureid)left outer join feeds_item as bon (frac.pid = b.entity_id and b.id = 'ipm_fungicide_lo_rate')where b.entity_id is null and frac.varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_rate_lo_nond')and a.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer')and a.bundle = 'registration';


-- PHI
insert into feeds_item (id,entity_type, entity_id, guid, hash, feed_nid, url)
select 'ipm_fungicide_lo_rate', 'dh_properties',  
  frac.pid, frac.featureid as guid,  
  -1, 0, ''
from dh_adminreg_feature as a 
left outer join dh_properties as frac 
on (a.adminid = frac.featureid)
left outer join feeds_item as b
on (
  frac.pid = b.entity_id 
  and b.id = 'ipm_fungicide_lo_rate'
)
where b.entity_id is null 
  and frac.varid in (
    select hydroid from dh_variabledefinition where varkey = 'agchem_rate_lo_nond'
  )
  and a.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer')
  and a.bundle = 'registration'
;


-- Lo App Rates Codes
insert into feeds_item (id,entity_type, entity_id, guid, hash, feed_nid, url)
select 'ipm_fungicide_lo_rate', 'dh_properties',  
  frac.pid, frac.featureid as guid,  
  -1, 0, ''
from dh_adminreg_feature as a 
left outer join dh_properties as frac 
on (a.adminid = frac.featureid)
left outer join feeds_item as b
on (
  frac.pid = b.entity_id 
  and b.id = 'ipm_fungicide_lo_rate'
)
where b.entity_id is null and frac.varid in (
  select hydroid from dh_variabledefinition where varkey = 'agchem_rate_lo_nond')
  and a.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer')
  and a.bundle = 'registration'
;

-- Hi App Rates Codes
-- insures that manually added materials are not duplicated on new imports
insert into feeds_item (id,entity_type, entity_id, guid, hash, feed_nid, url)select 'ipm_fungicide_hi_rate', 'dh_properties',  
frac.pid, frac.featureid as guid,  
-1, 0, ''from dh_adminreg_feature as a left outer join dh_properties as frac on (a.adminid = frac.featureid)left outer join feeds_item as bon (frac.pid = b.entity_id and b.id = 'ipm_fungicide_hi_rate')where b.entity_id is null and frac.varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_rate_hi_nond')and a.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer')and a.bundle = 'registration';
