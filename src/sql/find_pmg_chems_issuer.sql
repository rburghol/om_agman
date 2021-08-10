select entity_id, dh_link_admin_reg_issuer_target_id 
from field_data_dh_link_admin_reg_issuer 
where entity_id in (
  select featureid from dh_properties
  where varid in (
    select hydroid from dh_variabledefinition where varkey = 'ipm_vt_pmg_material'
  )
  and propcode = 'PMG'
  and dh_link_admin_reg_issuer_target_id <> 149
);