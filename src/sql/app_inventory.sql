select material.adminid as material_id,
  app_amount.propvalue as app_amount,
  
from material 
left outer join event_material_link 
left outer join event_material_amount
left outer join event
left outer join event_block
left outer join block_facility
left outer join material_facility
where material_facility.hydroid = 146
and block_facility.hydroid