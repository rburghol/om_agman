<?php

// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/pages/agchem_spray_event_work_order');
module_load_include('module', 'om_agman');
module_load_include('module', 'dh');
module_load_include('module', 'dh_adminreg');

//dpm($plugin,'plug');
// get the arguments from URL, they come in the form farm_hydroid/sub_page_name/event_adminid
$a = arg();
$fix = ($a[0] == 'node') ? 2 : 3; // use 3 for the page, 2 for a node during development
if (!isset($a[$fix])) {
  dsm( "There was a problem, block can not be found.");
} else {
  $fid = $a[$fix];
}

// Parts:
// 1. List of Events
// 2. Growth/Disease/Rain chart
// 3. Fruit Chemistry chart
// 4. Total sprays

// 1. List of Events
// Query events for this block
// Sub in rendered portions where needed -- does the linked AR event have a quick render hook? it should!
// Render the final desired list columns
// 
$event_query = " ( ";
$event_query .= " select ts.tid, ts.tstime, ts.tsvalue, ts.tscode, ts.entity_type, ts.varid, ";
$event_query .= "   feat.hydroid, feat.name, feat.bundle  ";
$event_query .= " from dh_timeseries as ts  ";
$event_query .= " left outer join  ";
$event_query .= " dh_feature as feat  ";
$event_query .= " on ( ";
$event_query .= " ts.featureid = feat.hydroid  ";
$event_query .= " and ts.entity_type = 'dh_feature' ";
$event_query .= " ) ";
$event_query .= " where  ";
$event_query .= " ts.entity_type = 'dh_feature' ";
$event_query .= " and featureid = $fid ";
$event_query .= " and varid not in (select hydroid from dh_variabledefinition where varkey in ('event_dha_default', 'event_dh_link_submittal_feature') )";
$event_query .= " and tstime >= extract(epoch from '2021-01-01'::timestamp) ";
$event_query .= " and tstime <= extract(epoch from '2021-12-31'::timestamp) ";
$event_query .= " ) UNION ( 
select ts.tid, ts.tstime, ts.tsvalue, ts.tscode, ts.entity_type, ts.varid, ";
$event_query .= " 	  arfeat.adminid, arfeat.name, arfeat.bundle  ";
$event_query .= " 	from dh_adminreg_feature as arfeat ";
$event_query .= " 	left outer join field_data_dh_link_feature_submittal as link ";
$event_query .= "     on ( ";
$event_query .= " 	  link.entity_id = arfeat.adminid  ";
$event_query .= " 	) ";
$event_query .= " 	left outer join field_data_dh_link_admin_timeseries as tsalink ";
$event_query .= " 	on ( ";
$event_query .= " 	  arfeat.adminid = tsalink.entity_id ";
$event_query .= " 	)   ";
$event_query .= " 	left outer join dh_timeseries as ts  ";
$event_query .= " 	on ( ";
$event_query .= "       ts.featureid = arfeat.adminid  ";
$event_query .= " 	  and ts.entity_type = 'dh_adminreg_feature' ";
$event_query .= " 	) ";
$event_query .= " 	where link.dh_link_feature_submittal_target_id = $fid  ";
$event_query .= " 	and ts.varid not in (select hydroid from dh_variabledefinition where varkey in ('event_dha_default')) ";
$event_query .= " 	and arfeat.startdate >= extract(epoch from '2021-01-01'::timestamp) ";
$event_query .= " 	and arfeat.startdate <= extract(epoch from '2021-12-31'::timestamp) ";
$event_query .= " ) order by tstime ";

dpm($event_query, 'query');

$event_table = array(
'#theme' => 'table',
'#header' => array('Material', 'Total to Spray', 'Rate (per acre)', 'Rate (per 100 gals)'),
'#rows'  => $chem_details_combined,
);
//dpm($chem_table_combined, 'chem_table_combined');
$output_combined = drupal_render($chem_table_combined);
echo $output_combined;
?>