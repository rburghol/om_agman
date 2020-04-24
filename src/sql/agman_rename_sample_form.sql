-- update properties to rename for new Grape Sample form
update dh_properties set propname = 'Seed Browning' 
where varid in (select hydroid from dh_variabledefinition where varkey = 'seed_lignification') 
;


-- update properties_revision to rename for new Grape Sample form
update dh_properties_revision set propname = 'Seed Browning' 
where varid in (select hydroid from dh_variabledefinition where varkey = 'seed_lignification') 
;