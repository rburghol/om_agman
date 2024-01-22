-- last spray date 
select block.hydroid, block.name, max(ar_spray.startdate) 
FROM dh_feature as farm 
LEFT OUTER JOIN field_data_dh_link_facility_mps as link_block
on (
  farm.hydroid = link_block.dh_link_facility_mps_target_id 
  AND link_block.entity_type = 'dh_feature'
)
LEFT OUTER JOIN dh_feature as block 
ON (
  link_block.entity_id = block.hydroid
  and block.bundle = 'landunit'
)
LEFT OUTER JOIN field_data_dh_link_feature_submittal as link_spray 
ON (
  dh_link_feature_submittal_target_id = block.hydroid 
  and link_spray.entity_type = 'dh_adminreg_feature'
) 
LEFT OUTER JOIN dh_adminreg_feature as ar_spray 
ON (
  ar_spray.adminid = link_spray.entity_id 
) 
where farm.hydroid = 146
and ar_spray.adminid is not null 
group by block.hydroid, block.name;
