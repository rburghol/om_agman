-- inventory events to change
select a.featureid, a.tsvalue, a.tscode 
from dh_timeseries as a 
where a.varid in (select hydroid from dh_variabledefinition where varkey = 'ipm_event')
;



-- match up changes 
create temp view tmp_ipmvar as (
	select a.tid as ipm_tid, a.featureid, b.varkey, b.varid as newvarid, a.tsvalue, a.tscode 
	from dh_timeseries as a 
	left outer join (
	  select varkey, hydroid as varid, 
		CASE 
		  WHEN varkey = 'ipm_hail' THEN 'hail' 
		  WHEN varkey = 'ipm_frost' THEN 'frost' 
		  WHEN varkey = 'ipm_spray_injury' THEN 'leaf_burn' 
		  WHEN varkey = 'ipm_event' THEN 'insect_damage' 
		  ELSE 'ipm_event'  
		END as oldcode 
	  from dh_variabledefinition
	) as b 
	on (
	  a.tscode = b.oldcode 
	)
	where a.varid in (select hydroid from dh_variabledefinition where varkey = 'ipm_event')
)
;

update dh_timeseries set tscode = NULL,
  varid = a.newvarid 
from tmp_ipmvar as a 
where newvarid is not null and varkey <> 'insect_damage' and tscode <> 'insect_damage' 
and tid = ipm_tid
;

update dh_timeseries set tscode = NULL,
  varid = a.hydroid 
from dh_variabledefinition as a 
where a.varkey = 'ipm_hail'
and dh_timeseries.varid in (select hydroid from dh_variabledefinition where varkey = 'ipm_event')
and tscode = 'hail'
;

