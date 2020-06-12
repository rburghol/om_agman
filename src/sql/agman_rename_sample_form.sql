-- update properties to rename for new Grape Sample form
update dh_properties set propname = 'Seed Browning' 
where propname = 'Seed Lignification' 
and varid in (select hydroid from dh_variabledefinition where varkey = 'seed_lignification') 
;

-- 
update dh_properties set propname = 'Average Berry Weight' 
where propname = 'Berry Weight' 
and varid in (select hydroid from dh_variabledefinition where varkey = 'berry_weight_g') 
;

-- 
update dh_properties set propname = 'Number of Berries' 
where propname = 'Berry Count' 
and varid in (select hydroid from dh_variabledefinition where varkey = 'sample_size_berries') 
;

update dh_properties set propname = 'Weight of Berries' 
where propname = 'Berry Count' 
and varid in (select hydroid from dh_variabledefinition where varkey = 'sample_weight_g') 
;