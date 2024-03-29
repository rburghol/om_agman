<?php

// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/panes/met_since_last_spray');
module_load_include('module', 'om_agman');
module_load_include('module', 'dh');
module_load_include('module', 'dh_adminreg');

//dpm($plugin,'plug');
// get the arguments from URL, they come in the form farm_hydroid/sub_page_name/event_adminid
$a = arg();
$parms = drupal_get_query_parameters();
$farmid = $a[1];
print("<b>Meteorological Conditions since last spray</b><br>");
$event_query = " WITH last_spray_date as ( ";
$event_query .= " 	select block.hydroid as blockid, sensor.hydroid as sensor_id, ";
$event_query .= " block.name, max(ar_spray.startdate) as spray_date";
$event_query .= " 	FROM dh_feature as farm ";
$event_query .= " 	LEFT OUTER JOIN field_data_dh_link_facility_mps as link_block";
$event_query .= " 	on (";
$event_query .= " 	  farm.hydroid = link_block.dh_link_facility_mps_target_id ";
$event_query .= " 	  AND link_block.entity_type = 'dh_feature'";
$event_query .= " 	)";
$event_query .= " 	LEFT OUTER JOIN dh_feature as block ";
$event_query .= " 	ON (";
$event_query .= " 	  link_block.entity_id = block.hydroid";
$event_query .= " 	  and block.bundle = 'landunit'";
$event_query .= " 	)";
$event_query .= " 	LEFT OUTER JOIN field_data_dh_link_feature_submittal as link_spray ";
$event_query .= " 	ON (";
$event_query .= " 	  dh_link_feature_submittal_target_id = block.hydroid ";
$event_query .= " 	  and link_spray.entity_type = 'dh_adminreg_feature'";
$event_query .= " 	) ";
$event_query .= " 	LEFT OUTER JOIN dh_adminreg_feature as ar_spray ";
$event_query .= " 	ON (";
$event_query .= " 	  ar_spray.adminid = link_spray.entity_id ";
$event_query .= " 	) ";
$event_query .= " 	LEFT OUTER JOIN field_data_dh_link_station_sensor as link_sensor";
$event_query .= " 	ON (";
$event_query .= " 	  farm.hydroid = link_sensor.dh_link_station_sensor_target_id ";
$event_query .= " 	  AND link_sensor.entity_type = 'dh_feature'";
$event_query .= " 	)";
$event_query .= " 	LEFT OUTER JOIN dh_feature as sensor ";
$event_query .= " 	ON (";
$event_query .= " 	   link_sensor.entity_id = sensor.hydroid";
$event_query .= " 	)";
$event_query .= " 	where farm.hydroid = $farmid";
$event_query .= " 	and ar_spray.adminid is not null ";
$event_query .= "   and ar_spray.startdate < extract(epoch from now())";
$event_query .= " 	group by block.hydroid, block.name, sensor.hydroid";
$event_query .= " ), weather_subset as (";
$event_query .= "     select * from dh_timeseries_weather ";
$event_query .= " 	where featureid in (select sensor_id from last_spray_date)";
$event_query .= " 	and tstime > (select min(spray_date) from last_spray_date)";
$event_query .= " )";
$event_query .= " select name, to_timestamp(spray_date),";
$event_query .= " round((sum(met.rain) * 0.0393701)::numeric,1) as rain_in, ";
$event_query .= "   round(sum(met.wet_time)/60.0,1) as wet_time ";
$event_query .= " from last_spray_date";
$event_query .= " left outer join";
$event_query .= " weather_subset as met ";
$event_query .= " on (";
$event_query .= "   met.featureid = sensor_id";
$event_query .= "   and met.entity_type = 'dh_feature'";
$event_query .= "   and met.tstime > spray_date";
$event_query .= " )";
$event_query .= " WHERE met.tstime < extract(epoch from now()) ";
$event_query .= "   and met.varid = 2";
# this insures that we don't get tripped up by future sprays
$event_query .= " group by blockid, name, spray_date, sensor_id";


//dpm($event_query, 'query');
$rez = db_query(
  $event_query
);
$recs = $rez->fetchAllAssoc('name', PDO::FETCH_ASSOC);
//dpm($recs, 'recs');
//dpm($event_query);
$row_one = $recs[array_shift(array_keys($recs))];
// use if showin IDs
//$header = array("blockid", "Name", "sensor_id", "Spray Date","Rain(in)", "Wetness(mins)");
$header = array("Block Name", "Spray Date","Rain(in)", "Wetness(mins)");

$event_table = array(
  '#theme' => 'table',
  '#header' => $header,
  '#rows'  => $recs,
);

$output_combined = drupal_render($event_table);
echo $output_combined;
?>