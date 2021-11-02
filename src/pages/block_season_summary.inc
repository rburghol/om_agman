<?php

// use this to call this file 
// module_load_include('inc', 'om_agman', 'src/pages/block_season_summary');
module_load_include('module', 'om_agman');
module_load_include('module', 'dh');
module_load_include('module', 'dh_adminreg');

//dpm($plugin,'plug');
// get the arguments from URL, they come in the form farm_hydroid/sub_page_name/event_adminid
$a = arg();
$fix = ($a[0] == 'node') ? 3 : 2; // use 3 for the page, 2 for a node during development
if (!isset($a[$fix])) {
  dsm( "There was a problem, block can not be found.");
  //dpm($a, "args passed");
} else {
  dpm($a, "args passed with index $fix");
  $blocks = dh_get_facility_mps($a[$fix - 1], 'landunit');
  dpm($blocks,'blocks');
  $fid = $a[$fix];
  dpm($fid,'initial block');
  $fid = ($fid == 'all') ? $blocks[current($blocks)] : $fid;
  dpm($fid,'using block');
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
// exclude standard admin reg event mappings because they are handled in the second query in this UNION
// exclude the scouting report because individual pathogens are already included.
$event_query .= " and varid not in (select hydroid from dh_variabledefinition where varkey in ('event_dha_default', 'event_dh_link_submittal_feature', 'om_agman_svform') )";
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

//dpm($event_query, 'query');
$rez = db_query(
  $event_query, 
  array(), 
  array('fetch' => PDO::FETCH_ASSOC)
);
$recs = $rez->fetchAllAssoc('tid');
//dpm($recs, 'recs');
$row_one = $recs[array_shift(array_keys($recs))];
$header = array_keys($row_one);
$event_table = array(
  '#theme' => 'table',
  '#header' => $header,
  '#rows'  => $recs,
);
// just spit out the raw table for debugging
//$output_combined = drupal_render($event_table);
//echo $output_combined;
//dpm($chem_table_combined, 'chem_table_combined');
// @todo: every TS and Prop object class has a method buildContent
//        that takes the active plugin, and renders the object in the requested mode. 
$ts_objects = entity_load('dh_timeseries', array_keys($recs));
$events = array();
$added_months = array();
$row_one = NULL;
foreach ($ts_objects as $ts) {
  //dpm($ts,'ts');
  $ts_rendered = array();
  $ts_rendered = entity_build_content('dh_timeseries', $ts, 'tiny', NULL);
  $month = date( 'F', dh_handletimestamp($ts->tstime));
  if (!isset($added_months[$month])) {
    $events[] = array(
      '#plain_text' => "<b>$month</b>",
      '#wrapper_attributes' => ['colspan' => 2],
    );
    $added_months[$month] = $month;
  }
  $this_row = array(
    'date' => date( 'Y-m-d', dh_handletimestamp($ts->tstime)), 
    'description' => drupal_render($ts_rendered),
    //'style' => 'background-color: pink'
  );
  $events[] = $this_row;
}
//dpm($events, 'rendered table');
$row_one = $events[array_shift(array_keys($events))];
//dpm($row_one, 'row_one');
$header = array('Date', 'Event Description');
$event_table = array(
  '#theme' => 'table',
  '#header' => $header,
  '#rows'  => $events,
);
$output_combined = drupal_render($event_table);
echo $output_combined;
?>