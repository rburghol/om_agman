WITH last_spray_date as (
	select block.hydroid as blockid, block.name, sensor.hydroid as sensor_id, max(ar_spray.startdate) as spray_date
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
	LEFT OUTER JOIN field_data_dh_link_station_sensor as link_sensor
	ON (
	  farm.hydroid = link_sensor.dh_link_station_sensor_target_id 
	  AND link_sensor.entity_type = 'dh_feature'
	)
	LEFT OUTER JOIN dh_feature as sensor 
	ON (
	   link_sensor.entity_id = sensor.hydroid
	)
	where farm.hydroid = 146
	and ar_spray.adminid is not null 
	group by block.hydroid, block.name, sensor.hydroid
), weather_subset as (
    select * from dh_timeseries_weather 
	where featureid in (select sensor_id from last_spray_date)
	and tstime > (select min(spray_date) from last_spray_date)
)
select blockid, sensor_id, to_timestamp(spray_date), sum(met.rain) * 0.0393701 as rain_in
from last_spray_date
left outer join
weather_subset as met 
on (
  met.featureid = sensor_id
  and met.entity_type = 'dh_feature'
  and met.tstime > spray_date
)
WHERE met.tstime < extract(epoch from now()) 
  and met.varid = 2
group by blockid, spray_date, sensor_id
;
