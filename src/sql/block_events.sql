-- get all events that are associated with this vineyard, including blocks, or adminreg records for vineyards/blocks
-- notes: 
--   * varid 192 is created for each block that is linked to an adminreg/spray record.  
--     so, you hace to filter out those records, OR, *only* get records attached 
--     to the block, but then load the corresponding Adminreg record 
--     but really, the adminreg record is a problem since we want to get the 
--     timeseries record that the adminreg record is linked to, since that has the 
--     tidy summary.
--     but really, the tidy summary should be accesible from the linked ts rec 
(
	select ts.tid, ts.tstime, to_timestamp(ts.tstime), ts.tsvalue, ts.tscode, ts.entity_type, ts.varid,
	  feat.hydroid, feat.name, feat.bundle 
	from dh_timeseries as ts 
	left outer join 
	dh_feature as feat 
	on (
	  ts.featureid = feat.hydroid 
	  and ts.entity_type = 'dh_feature'
	)
	where 
	ts.entity_type = 'dh_feature' 
	and featureid = 147 
	and varid not in (select hydroid from dh_variabledefinition where varkey in ('event_dha_default', 'event_dh_link_submittal_feature') )
	and tstime >= extract(epoch from '2021-01-01'::timestamp)
	and tstime <= extract(epoch from '2021-12-31'::timestamp)
) UNION (
  select ts.tid, ts.tstime, to_timestamp(ts.tstime), ts.tsvalue, ts.tscode, ts.entity_type, ts.varid,
	  arfeat.adminid, arfeat.name, arfeat.bundle 
	from dh_adminreg_feature as arfeat
	left outer join field_data_dh_link_feature_submittal as link
    on (
	  link.entity_id = arfeat.adminid 
	)
	left outer join field_data_dh_link_admin_timeseries as tsalink
	on (
	  arfeat.adminid = tsalink.entity_id
	)  
	left outer join dh_timeseries as ts 
	on (
      ts.featureid = arfeat.adminid 
	  and ts.entity_type = 'dh_adminreg_feature'
	)
	where link.dh_link_feature_submittal_target_id = 147 
	-- filter out varid 191 which is created by default for every adminreg record to say "adminreg record created" 
	and ts.varid <> 191 
	and arfeat.startdate >= extract(epoch from '2021-01-01'::timestamp)
	and arfeat.startdate <= extract(epoch from '2021-12-31'::timestamp)
) 
order by tstime 
;
