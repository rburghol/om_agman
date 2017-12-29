<?php
$view = new view();
$view->name = 'ipm_timeseries_calendar_events';
$view->description = '';
$view->tag = 'default';
$view->base_table = 'dh_timeseries';
$view->human_name = 'IPM - TimeSeries Calendar Events';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Master */
$handler = $view->new_display('default', 'Master', 'default');
$handler->display->display_options['title'] = 'IPM Events - GrapeIPM.org';
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['access']['type'] = 'none';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'mini';
$handler->display->display_options['pager']['options']['items_per_page'] = '3';
$handler->display->display_options['pager']['options']['offset'] = '0';
$handler->display->display_options['pager']['options']['id'] = '0';
$handler->display->display_options['style_plugin'] = 'table';
$handler->display->display_options['style_options']['columns'] = array(
  'tid' => 'tid',
  'entity_type' => 'entity_type',
  'featureid' => 'featureid',
  'tstime' => 'tstime',
  'varkey' => 'varkey',
  'tstime_1' => 'tstime_1',
  'rendered_entity' => 'rendered_entity',
  'modified' => 'modified',
  'rendered_entity_1' => 'rendered_entity_1',
);
$handler->display->display_options['style_options']['default'] = '-1';
$handler->display->display_options['style_options']['info'] = array(
  'tid' => array(
    'sortable' => 0,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
  'entity_type' => array(
    'sortable' => 0,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
  'featureid' => array(
    'sortable' => 0,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
  'tstime' => array(
    'sortable' => 0,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
  'varkey' => array(
    'sortable' => 0,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
  'tstime_1' => array(
    'sortable' => 0,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
  'rendered_entity' => array(
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
  'modified' => array(
    'sortable' => 0,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
  'rendered_entity_1' => array(
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
);
/* No results behavior: Global: Text area */
$handler->display->display_options['empty']['area']['id'] = 'area';
$handler->display->display_options['empty']['area']['table'] = 'views';
$handler->display->display_options['empty']['area']['field'] = 'area';
$handler->display->display_options['empty']['area']['empty'] = TRUE;
$handler->display->display_options['empty']['area']['content'] = '<i>You have no upcoming events planned.</i>';
$handler->display->display_options['empty']['area']['format'] = 'full_html';
/* Relationship: Admin Event */
$handler->display->display_options['relationships']['crossts_dh_adminreg_feature']['id'] = 'crossts_dh_adminreg_feature';
$handler->display->display_options['relationships']['crossts_dh_adminreg_feature']['table'] = 'dh_timeseries';
$handler->display->display_options['relationships']['crossts_dh_adminreg_feature']['field'] = 'crossts_dh_adminreg_feature';
$handler->display->display_options['relationships']['crossts_dh_adminreg_feature']['ui_name'] = 'Admin Event';
$handler->display->display_options['relationships']['crossts_dh_adminreg_feature']['label'] = 'Admin Event';
/* Relationship: Vineyard */
$handler->display->display_options['relationships']['crossts_dh_feature']['id'] = 'crossts_dh_feature';
$handler->display->display_options['relationships']['crossts_dh_feature']['table'] = 'dh_timeseries';
$handler->display->display_options['relationships']['crossts_dh_feature']['field'] = 'crossts_dh_feature';
$handler->display->display_options['relationships']['crossts_dh_feature']['ui_name'] = 'Vineyard';
$handler->display->display_options['relationships']['crossts_dh_feature']['label'] = 'Vineyard';
/* Relationship: Block */
$handler->display->display_options['relationships']['crossts_dh_feature_1']['id'] = 'crossts_dh_feature_1';
$handler->display->display_options['relationships']['crossts_dh_feature_1']['table'] = 'dh_timeseries';
$handler->display->display_options['relationships']['crossts_dh_feature_1']['field'] = 'crossts_dh_feature';
$handler->display->display_options['relationships']['crossts_dh_feature_1']['ui_name'] = 'Block';
$handler->display->display_options['relationships']['crossts_dh_feature_1']['label'] = 'Block';
/* Field: DH TimeSeries: Label */
$handler->display->display_options['fields']['tid']['id'] = 'tid';
$handler->display->display_options['fields']['tid']['table'] = 'dh_timeseries';
$handler->display->display_options['fields']['tid']['field'] = 'tid';
/* Field: DH TimeSeries: entity_type */
$handler->display->display_options['fields']['entity_type']['id'] = 'entity_type';
$handler->display->display_options['fields']['entity_type']['table'] = 'dh_timeseries';
$handler->display->display_options['fields']['entity_type']['field'] = 'entity_type';
/* Field: DH TimeSeries: featureid */
$handler->display->display_options['fields']['featureid']['id'] = 'featureid';
$handler->display->display_options['fields']['featureid']['table'] = 'dh_timeseries';
$handler->display->display_options['fields']['featureid']['field'] = 'featureid';
/* Field: DH TimeSeries:  */
$handler->display->display_options['fields']['tstime']['id'] = 'tstime';
$handler->display->display_options['fields']['tstime']['table'] = 'dh_timeseries';
$handler->display->display_options['fields']['tstime']['field'] = 'tstime';
$handler->display->display_options['fields']['tstime']['label'] = '';
$handler->display->display_options['fields']['tstime']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['tstime']['date_format'] = 'long';
$handler->display->display_options['fields']['tstime']['second_date_format'] = 'long';
/* Field: DH VariableDefinition: Variable Key (may be short name or = varname) */
$handler->display->display_options['fields']['varkey']['id'] = 'varkey';
$handler->display->display_options['fields']['varkey']['table'] = 'dh_variabledefinition';
$handler->display->display_options['fields']['varkey']['field'] = 'varkey';
/* Field: DH TimeSeries: Rendered dH TimeSeries */
$handler->display->display_options['fields']['rendered_entity']['id'] = 'rendered_entity';
$handler->display->display_options['fields']['rendered_entity']['table'] = 'views_entity_dh_timeseries';
$handler->display->display_options['fields']['rendered_entity']['field'] = 'rendered_entity';
$handler->display->display_options['fields']['rendered_entity']['label'] = 'DESCRIPTION';
$handler->display->display_options['fields']['rendered_entity']['link_to_entity'] = 1;
$handler->display->display_options['fields']['rendered_entity']['display'] = 'event_description';
$handler->display->display_options['fields']['rendered_entity']['view_mode'] = 'plugin';
$handler->display->display_options['fields']['rendered_entity']['bypass_access'] = 0;
/* Field: DH TimeSeries: Modified */
$handler->display->display_options['fields']['modified']['id'] = 'modified';
$handler->display->display_options['fields']['modified']['table'] = 'dh_timeseries';
$handler->display->display_options['fields']['modified']['field'] = 'modified';
$handler->display->display_options['fields']['modified']['label'] = 'DTSTAMP';
$handler->display->display_options['fields']['modified']['date_format'] = 'ical';
$handler->display->display_options['fields']['modified']['second_date_format'] = 'long';
/* Field: DH TimeSeries:  */
$handler->display->display_options['fields']['tstime_1']['id'] = 'tstime_1';
$handler->display->display_options['fields']['tstime_1']['table'] = 'dh_timeseries';
$handler->display->display_options['fields']['tstime_1']['field'] = 'tstime';
$handler->display->display_options['fields']['tstime_1']['label'] = 'DTSTART';
$handler->display->display_options['fields']['tstime_1']['date_format'] = 'ical';
$handler->display->display_options['fields']['tstime_1']['second_date_format'] = 'long';
/* Field: DH TimeSeries: Rendered dH TimeSeries */
$handler->display->display_options['fields']['rendered_entity_1']['id'] = 'rendered_entity_1';
$handler->display->display_options['fields']['rendered_entity_1']['table'] = 'views_entity_dh_timeseries';
$handler->display->display_options['fields']['rendered_entity_1']['field'] = 'rendered_entity';
$handler->display->display_options['fields']['rendered_entity_1']['label'] = 'SUMMARY';
$handler->display->display_options['fields']['rendered_entity_1']['alter']['text'] = 'Spray Event';
$handler->display->display_options['fields']['rendered_entity_1']['alter']['trim_whitespace'] = TRUE;
$handler->display->display_options['fields']['rendered_entity_1']['alter']['strip_tags'] = TRUE;
$handler->display->display_options['fields']['rendered_entity_1']['link_to_entity'] = 1;
$handler->display->display_options['fields']['rendered_entity_1']['display'] = 'event_title';
$handler->display->display_options['fields']['rendered_entity_1']['view_mode'] = 'full';
$handler->display->display_options['fields']['rendered_entity_1']['bypass_access'] = 0;
/* Sort criterion: DH TimeSeries:  */
$handler->display->display_options['sorts']['tstime']['id'] = 'tstime';
$handler->display->display_options['sorts']['tstime']['table'] = 'dh_timeseries';
$handler->display->display_options['sorts']['tstime']['field'] = 'tstime';
/* Contextual filter: DH Feature: HydroID - Unique Feature ID */
$handler->display->display_options['arguments']['hydroid']['id'] = 'hydroid';
$handler->display->display_options['arguments']['hydroid']['table'] = 'dh_feature';
$handler->display->display_options['arguments']['hydroid']['field'] = 'hydroid';
$handler->display->display_options['arguments']['hydroid']['relationship'] = 'crossts_dh_feature';
$handler->display->display_options['arguments']['hydroid']['default_action'] = 'not found';
$handler->display->display_options['arguments']['hydroid']['default_argument_type'] = 'fixed';
$handler->display->display_options['arguments']['hydroid']['summary']['number_of_records'] = '0';
$handler->display->display_options['arguments']['hydroid']['summary']['format'] = 'default_summary';
$handler->display->display_options['arguments']['hydroid']['summary_options']['items_per_page'] = '25';
$handler->display->display_options['arguments']['hydroid']['break_phrase'] = TRUE;
$handler->display->display_options['arguments']['hydroid']['comparator'] = 'eq';
$handler->display->display_options['arguments']['hydroid']['op_location'] = 'on';
/* Contextual filter: DH Feature: HydroID - Unique Feature ID */
$handler->display->display_options['arguments']['hydroid_1']['id'] = 'hydroid_1';
$handler->display->display_options['arguments']['hydroid_1']['table'] = 'dh_feature';
$handler->display->display_options['arguments']['hydroid_1']['field'] = 'hydroid';
$handler->display->display_options['arguments']['hydroid_1']['relationship'] = 'crossts_dh_feature_1';
$handler->display->display_options['arguments']['hydroid_1']['default_action'] = 'default';
$handler->display->display_options['arguments']['hydroid_1']['default_argument_type'] = 'php';
$handler->display->display_options['arguments']['hydroid_1']['default_argument_options']['code'] = '$blocks = dh_get_facility_mps($view->args[0]);
return implode(\',\', $blocks);';
$handler->display->display_options['arguments']['hydroid_1']['summary']['number_of_records'] = '0';
$handler->display->display_options['arguments']['hydroid_1']['summary']['format'] = 'default_summary';
$handler->display->display_options['arguments']['hydroid_1']['summary_options']['items_per_page'] = '25';
$handler->display->display_options['arguments']['hydroid_1']['break_phrase'] = TRUE;
$handler->display->display_options['arguments']['hydroid_1']['comparator'] = 'eq';
$handler->display->display_options['arguments']['hydroid_1']['op_location'] = 'on';
/* Contextual filter: DH AdminReg Feature: Dh adminreg feature ID */
$handler->display->display_options['arguments']['adminid']['id'] = 'adminid';
$handler->display->display_options['arguments']['adminid']['table'] = 'dh_adminreg_feature';
$handler->display->display_options['arguments']['adminid']['field'] = 'adminid';
$handler->display->display_options['arguments']['adminid']['relationship'] = 'crossts_dh_adminreg_feature';
$handler->display->display_options['arguments']['adminid']['default_action'] = 'default';
$handler->display->display_options['arguments']['adminid']['default_argument_type'] = 'php';
$handler->display->display_options['arguments']['adminid']['default_argument_options']['code'] = '// get the blocks
$blocks = explode(\',\', $view->args[1]);
$blocks[] = $view->args[0]; //add the facility
$eref_config = array();
$eref_config[\'eref_fieldname\'] = \'dh_link_feature_submittal\';
$eref_config[\'target_entity_id\'] = $blocks;
$eref_config[\'entity_type\'] = \'dh_adminreg_feature\';
$eref_config[\'entity_id_name\'] = \'adminid\';

$events = dh_get_reverse_erefs($eref_config);
//dpm($events,\'events\');
return implode(\',\', $events );';
$handler->display->display_options['arguments']['adminid']['summary']['number_of_records'] = '0';
$handler->display->display_options['arguments']['adminid']['summary']['format'] = 'default_summary';
$handler->display->display_options['arguments']['adminid']['summary_options']['items_per_page'] = '25';
$handler->display->display_options['arguments']['adminid']['break_phrase'] = TRUE;
$handler->display->display_options['arguments']['adminid']['comparator'] = 'eq';
$handler->display->display_options['arguments']['adminid']['op_location'] = 'on';
$handler->display->display_options['filter_groups']['operator'] = 'OR';
$handler->display->display_options['filter_groups']['groups'] = array(
  1 => 'AND',
  2 => 'AND',
  3 => 'AND',
);
/* Filter criterion: DH Feature: HydroID - Unique Feature ID */
$handler->display->display_options['filters']['hydroid']['id'] = 'hydroid';
$handler->display->display_options['filters']['hydroid']['table'] = 'dh_feature';
$handler->display->display_options['filters']['hydroid']['field'] = 'hydroid';
$handler->display->display_options['filters']['hydroid']['relationship'] = 'crossts_dh_feature';
$handler->display->display_options['filters']['hydroid']['operator'] = 'not empty';
$handler->display->display_options['filters']['hydroid']['group'] = 1;
/* Filter criterion: DH TimeSeries: entity_type */
$handler->display->display_options['filters']['entity_type_1']['id'] = 'entity_type_1';
$handler->display->display_options['filters']['entity_type_1']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['entity_type_1']['field'] = 'entity_type';
$handler->display->display_options['filters']['entity_type_1']['value'] = 'dh_feature';
$handler->display->display_options['filters']['entity_type_1']['group'] = 1;
$handler->display->display_options['filters']['entity_type_1']['op_location'] = 'where';
/* Filter criterion: Feature Events to Show */
$handler->display->display_options['filters']['varidid_1']['id'] = 'varidid_1';
$handler->display->display_options['filters']['varidid_1']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['varidid_1']['field'] = 'varidid';
$handler->display->display_options['filters']['varidid_1']['ui_name'] = 'Feature Events to Show';
$handler->display->display_options['filters']['varidid_1']['value'] = array(
  'agchem_inventory_event' => 'agchem_inventory_event',
);
$handler->display->display_options['filters']['varidid_1']['group'] = 1;
$handler->display->display_options['filters']['varidid_1']['vocabulary'] = array(
  'agchem' => 'agchem',
  'agchem_event' => 'agchem_event',
  'agman' => 'agman',
  'agman_equipment' => 'agman_equipment',
);
/* Filter criterion: DH TimeSeries: entity_type */
$handler->display->display_options['filters']['entity_type_2']['id'] = 'entity_type_2';
$handler->display->display_options['filters']['entity_type_2']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['entity_type_2']['field'] = 'entity_type';
$handler->display->display_options['filters']['entity_type_2']['value'] = 'dh_adminreg_feature';
$handler->display->display_options['filters']['entity_type_2']['group'] = 2;
$handler->display->display_options['filters']['entity_type_2']['op_location'] = 'where';
/* Filter criterion: DH TimeSeries: varid (selector) */
$handler->display->display_options['filters']['varidid']['id'] = 'varidid';
$handler->display->display_options['filters']['varidid']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['varidid']['field'] = 'varidid';
$handler->display->display_options['filters']['varidid']['value'] = array(
  'agchem_application_event' => 'agchem_application_event',
);
$handler->display->display_options['filters']['varidid']['group'] = 2;
$handler->display->display_options['filters']['varidid']['vocabulary'] = array(
  'agchem' => 'agchem',
  'agchem_event' => 'agchem_event',
  'agman' => 'agman',
);
/* Filter criterion: DH AdminReg Feature: Dh adminreg feature ID */
$handler->display->display_options['filters']['adminid']['id'] = 'adminid';
$handler->display->display_options['filters']['adminid']['table'] = 'dh_adminreg_feature';
$handler->display->display_options['filters']['adminid']['field'] = 'adminid';
$handler->display->display_options['filters']['adminid']['relationship'] = 'crossts_dh_adminreg_feature';
$handler->display->display_options['filters']['adminid']['operator'] = 'not empty';
$handler->display->display_options['filters']['adminid']['group'] = 2;
/* Filter criterion: DH Feature: HydroID - Unique Feature ID */
$handler->display->display_options['filters']['hydroid_1']['id'] = 'hydroid_1';
$handler->display->display_options['filters']['hydroid_1']['table'] = 'dh_feature';
$handler->display->display_options['filters']['hydroid_1']['field'] = 'hydroid';
$handler->display->display_options['filters']['hydroid_1']['relationship'] = 'crossts_dh_feature_1';
$handler->display->display_options['filters']['hydroid_1']['operator'] = 'not empty';
$handler->display->display_options['filters']['hydroid_1']['group'] = 3;
/* Filter criterion: Block Events (default is none) */
$handler->display->display_options['filters']['varidid_2']['id'] = 'varidid_2';
$handler->display->display_options['filters']['varidid_2']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['varidid_2']['field'] = 'varidid';
$handler->display->display_options['filters']['varidid_2']['ui_name'] = 'Block Events (default is none)';
$handler->display->display_options['filters']['varidid_2']['value'] = array(
  'vitis_veraison' => 'vitis_veraison',
);
$handler->display->display_options['filters']['varidid_2']['group'] = 3;
$handler->display->display_options['filters']['varidid_2']['vocabulary'] = array(
  'agchem' => 'agchem',
  'agchem_event' => 'agchem_event',
  'agman' => 'agman',
  'agman_equipment' => 'agman_equipment',
  'plant_modeling' => 'plant_modeling',
  'vitis' => 'vitis',
);
/* Filter criterion: DH TimeSeries: entity_type */
$handler->display->display_options['filters']['entity_type']['id'] = 'entity_type';
$handler->display->display_options['filters']['entity_type']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['entity_type']['field'] = 'entity_type';
$handler->display->display_options['filters']['entity_type']['value'] = 'dh_feature';
$handler->display->display_options['filters']['entity_type']['group'] = 3;
$handler->display->display_options['filters']['entity_type']['op_location'] = 'where';

/* Display: Table View */
$handler = $view->new_display('page', 'Table View', 'page');
$handler->display->display_options['path'] = 'ipm-timeseries-calendar-events';

/* Display: IPM Events (Date iCal) */
$handler = $view->new_display('feed', 'IPM Events (Date iCal)', 'feed_1');
$handler->display->display_options['pager']['type'] = 'none';
$handler->display->display_options['pager']['options']['offset'] = '0';
$handler->display->display_options['style_plugin'] = 'date_ical';
$handler->display->display_options['style_options']['cal_name'] = '';
$handler->display->display_options['style_options']['unescape_punctuation'] = TRUE;
$handler->display->display_options['row_plugin'] = 'date_ical_fields';
$handler->display->display_options['row_options']['date_field'] = 'tstime_1';
$handler->display->display_options['row_options']['title_field'] = 'rendered_entity_1';
$handler->display->display_options['row_options']['description_field'] = 'rendered_entity';
$handler->display->display_options['row_options']['additional_settings'] = array(
  'skip_blank_dates' => 0,
);
$handler->display->display_options['path'] = 'ipm-facility-ical/%/ipm-events1.ics';

/* Display: IPM Events (Views iCal nonfunc) */
$handler = $view->new_display('feed', 'IPM Events (Views iCal nonfunc)', 'feed_2');
$handler->display->display_options['pager']['type'] = 'none';
$handler->display->display_options['pager']['options']['offset'] = '0';
$handler->display->display_options['style_plugin'] = 'views_ical';
$handler->display->display_options['row_plugin'] = 'views_ical';
$handler->display->display_options['path'] = 'ipm-facility-ical/%/cal12.ics';
$handler->display->display_options['sitename_title'] = 0;

/* Display: Upcoming Dash */
$handler = $view->new_display('page', 'Upcoming Dash', 'page_1');
$handler->display->display_options['defaults']['use_ajax'] = FALSE;
$handler->display->display_options['use_ajax'] = TRUE;
$handler->display->display_options['defaults']['fields'] = FALSE;
/* Field: DH TimeSeries: Label */
$handler->display->display_options['fields']['tid']['id'] = 'tid';
$handler->display->display_options['fields']['tid']['table'] = 'dh_timeseries';
$handler->display->display_options['fields']['tid']['field'] = 'tid';
$handler->display->display_options['fields']['tid']['exclude'] = TRUE;
$handler->display->display_options['fields']['tid']['separator'] = '';
/* Field: DH TimeSeries: entity_type */
$handler->display->display_options['fields']['entity_type']['id'] = 'entity_type';
$handler->display->display_options['fields']['entity_type']['table'] = 'dh_timeseries';
$handler->display->display_options['fields']['entity_type']['field'] = 'entity_type';
$handler->display->display_options['fields']['entity_type']['exclude'] = TRUE;
/* Field: DH TimeSeries: featureid */
$handler->display->display_options['fields']['featureid']['id'] = 'featureid';
$handler->display->display_options['fields']['featureid']['table'] = 'dh_timeseries';
$handler->display->display_options['fields']['featureid']['field'] = 'featureid';
$handler->display->display_options['fields']['featureid']['exclude'] = TRUE;
$handler->display->display_options['fields']['featureid']['separator'] = '';
/* Field: DH TimeSeries:  */
$handler->display->display_options['fields']['tstime']['id'] = 'tstime';
$handler->display->display_options['fields']['tstime']['table'] = 'dh_timeseries';
$handler->display->display_options['fields']['tstime']['field'] = 'tstime';
$handler->display->display_options['fields']['tstime']['label'] = '';
$handler->display->display_options['fields']['tstime']['exclude'] = TRUE;
$handler->display->display_options['fields']['tstime']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['tstime']['date_format'] = 'long';
$handler->display->display_options['fields']['tstime']['second_date_format'] = 'long';
/* Field: DH VariableDefinition: Variable Key (may be short name or = varname) */
$handler->display->display_options['fields']['varkey']['id'] = 'varkey';
$handler->display->display_options['fields']['varkey']['table'] = 'dh_variabledefinition';
$handler->display->display_options['fields']['varkey']['field'] = 'varkey';
$handler->display->display_options['fields']['varkey']['exclude'] = TRUE;
/* Field: DH TimeSeries:  */
$handler->display->display_options['fields']['tstime_1']['id'] = 'tstime_1';
$handler->display->display_options['fields']['tstime_1']['table'] = 'dh_timeseries';
$handler->display->display_options['fields']['tstime_1']['field'] = 'tstime';
$handler->display->display_options['fields']['tstime_1']['label'] = 'Date';
$handler->display->display_options['fields']['tstime_1']['date_format'] = 'ical';
$handler->display->display_options['fields']['tstime_1']['second_date_format'] = 'long';
/* Field: DH TimeSeries: Modified */
$handler->display->display_options['fields']['modified']['id'] = 'modified';
$handler->display->display_options['fields']['modified']['table'] = 'dh_timeseries';
$handler->display->display_options['fields']['modified']['field'] = 'modified';
$handler->display->display_options['fields']['modified']['label'] = 'DTSTAMP';
$handler->display->display_options['fields']['modified']['exclude'] = TRUE;
$handler->display->display_options['fields']['modified']['date_format'] = 'ical';
$handler->display->display_options['fields']['modified']['second_date_format'] = 'long';
/* Field: DH TimeSeries: Rendered dH TimeSeries */
$handler->display->display_options['fields']['rendered_entity_1']['id'] = 'rendered_entity_1';
$handler->display->display_options['fields']['rendered_entity_1']['table'] = 'views_entity_dh_timeseries';
$handler->display->display_options['fields']['rendered_entity_1']['field'] = 'rendered_entity';
$handler->display->display_options['fields']['rendered_entity_1']['label'] = 'SUMMARY';
$handler->display->display_options['fields']['rendered_entity_1']['exclude'] = TRUE;
$handler->display->display_options['fields']['rendered_entity_1']['alter']['text'] = 'Spray Event';
$handler->display->display_options['fields']['rendered_entity_1']['alter']['trim_whitespace'] = TRUE;
$handler->display->display_options['fields']['rendered_entity_1']['alter']['strip_tags'] = TRUE;
$handler->display->display_options['fields']['rendered_entity_1']['link_to_entity'] = 1;
$handler->display->display_options['fields']['rendered_entity_1']['display'] = 'event_title';
$handler->display->display_options['fields']['rendered_entity_1']['view_mode'] = 'full';
$handler->display->display_options['fields']['rendered_entity_1']['bypass_access'] = 0;
/* Field: DH TimeSeries: Rendered dH TimeSeries */
$handler->display->display_options['fields']['rendered_entity_2']['id'] = 'rendered_entity_2';
$handler->display->display_options['fields']['rendered_entity_2']['table'] = 'views_entity_dh_timeseries';
$handler->display->display_options['fields']['rendered_entity_2']['field'] = 'rendered_entity';
$handler->display->display_options['fields']['rendered_entity_2']['label'] = 'Event Details';
$handler->display->display_options['fields']['rendered_entity_2']['link_to_entity'] = 1;
$handler->display->display_options['fields']['rendered_entity_2']['display'] = 'view';
$handler->display->display_options['fields']['rendered_entity_2']['view_mode'] = 'plugin';
$handler->display->display_options['fields']['rendered_entity_2']['bypass_access'] = 0;
$handler->display->display_options['defaults']['arguments'] = FALSE;
/* Contextual filter: DH Feature: HydroID - Unique Feature ID */
$handler->display->display_options['arguments']['hydroid']['id'] = 'hydroid';
$handler->display->display_options['arguments']['hydroid']['table'] = 'dh_feature';
$handler->display->display_options['arguments']['hydroid']['field'] = 'hydroid';
$handler->display->display_options['arguments']['hydroid']['relationship'] = 'crossts_dh_feature';
$handler->display->display_options['arguments']['hydroid']['default_action'] = 'default';
$handler->display->display_options['arguments']['hydroid']['default_argument_type'] = 'php';
$handler->display->display_options['arguments']['hydroid']['default_argument_options']['code'] = 'global $user;
$fs = dh_get_user_mgr_features($user->uid);
return implode(\',\', $fs);';
$handler->display->display_options['arguments']['hydroid']['summary']['number_of_records'] = '0';
$handler->display->display_options['arguments']['hydroid']['summary']['format'] = 'default_summary';
$handler->display->display_options['arguments']['hydroid']['summary_options']['items_per_page'] = '25';
$handler->display->display_options['arguments']['hydroid']['break_phrase'] = TRUE;
$handler->display->display_options['arguments']['hydroid']['comparator'] = 'eq';
$handler->display->display_options['arguments']['hydroid']['op_location'] = 'on';
/* Contextual filter: DH Feature: HydroID - Unique Feature ID */
$handler->display->display_options['arguments']['hydroid_1']['id'] = 'hydroid_1';
$handler->display->display_options['arguments']['hydroid_1']['table'] = 'dh_feature';
$handler->display->display_options['arguments']['hydroid_1']['field'] = 'hydroid';
$handler->display->display_options['arguments']['hydroid_1']['relationship'] = 'crossts_dh_feature_1';
$handler->display->display_options['arguments']['hydroid_1']['default_action'] = 'default';
$handler->display->display_options['arguments']['hydroid_1']['default_argument_type'] = 'php';
$handler->display->display_options['arguments']['hydroid_1']['default_argument_options']['code'] = '$blocks = dh_get_facility_mps(explode(\',\',$view->args[0]));
return implode(\',\', $blocks);';
$handler->display->display_options['arguments']['hydroid_1']['summary']['number_of_records'] = '0';
$handler->display->display_options['arguments']['hydroid_1']['summary']['format'] = 'default_summary';
$handler->display->display_options['arguments']['hydroid_1']['summary_options']['items_per_page'] = '25';
$handler->display->display_options['arguments']['hydroid_1']['break_phrase'] = TRUE;
$handler->display->display_options['arguments']['hydroid_1']['comparator'] = 'eq';
$handler->display->display_options['arguments']['hydroid_1']['op_location'] = 'on';
/* Contextual filter: DH AdminReg Feature: Dh adminreg feature ID */
$handler->display->display_options['arguments']['adminid']['id'] = 'adminid';
$handler->display->display_options['arguments']['adminid']['table'] = 'dh_adminreg_feature';
$handler->display->display_options['arguments']['adminid']['field'] = 'adminid';
$handler->display->display_options['arguments']['adminid']['relationship'] = 'crossts_dh_adminreg_feature';
$handler->display->display_options['arguments']['adminid']['default_action'] = 'default';
$handler->display->display_options['arguments']['adminid']['default_argument_type'] = 'php';
$handler->display->display_options['arguments']['adminid']['default_argument_options']['code'] = '// get the blocks
$blocks = explode(\',\', $view->args[1]);
array_merge($blocks, explode(\',\',$view->args[0])); //add the facility
$eref_config = array();
$eref_config[\'eref_fieldname\'] = \'dh_link_feature_submittal\';
$eref_config[\'target_entity_id\'] = $blocks;
$eref_config[\'entity_type\'] = \'dh_adminreg_feature\';
$eref_config[\'entity_id_name\'] = \'adminid\';

$events = dh_get_reverse_erefs($eref_config);
//dpm($events,\'events\');
return implode(\',\', $events );';
$handler->display->display_options['arguments']['adminid']['summary']['number_of_records'] = '0';
$handler->display->display_options['arguments']['adminid']['summary']['format'] = 'default_summary';
$handler->display->display_options['arguments']['adminid']['summary_options']['items_per_page'] = '25';
$handler->display->display_options['arguments']['adminid']['break_phrase'] = TRUE;
$handler->display->display_options['arguments']['adminid']['comparator'] = 'eq';
$handler->display->display_options['arguments']['adminid']['op_location'] = 'on';
$handler->display->display_options['defaults']['filter_groups'] = FALSE;
$handler->display->display_options['filter_groups']['operator'] = 'OR';
$handler->display->display_options['filter_groups']['groups'] = array(
  1 => 'AND',
  2 => 'AND',
  3 => 'AND',
);
$handler->display->display_options['defaults']['filters'] = FALSE;
/* Filter criterion: DH Feature: HydroID - Unique Feature ID */
$handler->display->display_options['filters']['hydroid']['id'] = 'hydroid';
$handler->display->display_options['filters']['hydroid']['table'] = 'dh_feature';
$handler->display->display_options['filters']['hydroid']['field'] = 'hydroid';
$handler->display->display_options['filters']['hydroid']['relationship'] = 'crossts_dh_feature';
$handler->display->display_options['filters']['hydroid']['operator'] = 'not empty';
$handler->display->display_options['filters']['hydroid']['group'] = 1;
/* Filter criterion: DH TimeSeries: entity_type */
$handler->display->display_options['filters']['entity_type_1']['id'] = 'entity_type_1';
$handler->display->display_options['filters']['entity_type_1']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['entity_type_1']['field'] = 'entity_type';
$handler->display->display_options['filters']['entity_type_1']['value'] = 'dh_feature';
$handler->display->display_options['filters']['entity_type_1']['group'] = 1;
$handler->display->display_options['filters']['entity_type_1']['op_location'] = 'where';
/* Filter criterion: Feature Events to Show */
$handler->display->display_options['filters']['varidid_1']['id'] = 'varidid_1';
$handler->display->display_options['filters']['varidid_1']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['varidid_1']['field'] = 'varidid';
$handler->display->display_options['filters']['varidid_1']['ui_name'] = 'Feature Events to Show';
$handler->display->display_options['filters']['varidid_1']['value'] = array(
  'agchem_inventory_event' => 'agchem_inventory_event',
);
$handler->display->display_options['filters']['varidid_1']['group'] = 1;
$handler->display->display_options['filters']['varidid_1']['vocabulary'] = array(
  'agchem' => 'agchem',
  'agchem_event' => 'agchem_event',
  'agman' => 'agman',
  'agman_equipment' => 'agman_equipment',
);
/* Filter criterion: DH TimeSeries:  */
$handler->display->display_options['filters']['tstime']['id'] = 'tstime';
$handler->display->display_options['filters']['tstime']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['tstime']['field'] = 'tstime';
$handler->display->display_options['filters']['tstime']['operator'] = '>=';
$handler->display->display_options['filters']['tstime']['value']['value'] = '-1 day';
$handler->display->display_options['filters']['tstime']['value']['type'] = 'offset';
$handler->display->display_options['filters']['tstime']['group'] = 1;
/* Filter criterion: DH TimeSeries:  */
$handler->display->display_options['filters']['tstime_1']['id'] = 'tstime_1';
$handler->display->display_options['filters']['tstime_1']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['tstime_1']['field'] = 'tstime';
$handler->display->display_options['filters']['tstime_1']['operator'] = '>=';
$handler->display->display_options['filters']['tstime_1']['value']['value'] = '-1 day';
$handler->display->display_options['filters']['tstime_1']['value']['type'] = 'offset';
$handler->display->display_options['filters']['tstime_1']['group'] = 2;
/* Filter criterion: DH TimeSeries: entity_type */
$handler->display->display_options['filters']['entity_type_2']['id'] = 'entity_type_2';
$handler->display->display_options['filters']['entity_type_2']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['entity_type_2']['field'] = 'entity_type';
$handler->display->display_options['filters']['entity_type_2']['value'] = 'dh_adminreg_feature';
$handler->display->display_options['filters']['entity_type_2']['group'] = 2;
$handler->display->display_options['filters']['entity_type_2']['op_location'] = 'where';
/* Filter criterion: DH TimeSeries: varid (selector) */
$handler->display->display_options['filters']['varidid']['id'] = 'varidid';
$handler->display->display_options['filters']['varidid']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['varidid']['field'] = 'varidid';
$handler->display->display_options['filters']['varidid']['value'] = array(
  'agchem_application_event' => 'agchem_application_event',
);
$handler->display->display_options['filters']['varidid']['group'] = 2;
$handler->display->display_options['filters']['varidid']['vocabulary'] = array(
  'agchem' => 'agchem',
  'agchem_event' => 'agchem_event',
  'agman' => 'agman',
);
/* Filter criterion: DH AdminReg Feature: Dh adminreg feature ID */
$handler->display->display_options['filters']['adminid']['id'] = 'adminid';
$handler->display->display_options['filters']['adminid']['table'] = 'dh_adminreg_feature';
$handler->display->display_options['filters']['adminid']['field'] = 'adminid';
$handler->display->display_options['filters']['adminid']['relationship'] = 'crossts_dh_adminreg_feature';
$handler->display->display_options['filters']['adminid']['operator'] = 'not empty';
$handler->display->display_options['filters']['adminid']['group'] = 2;
/* Filter criterion: DH Feature: HydroID - Unique Feature ID */
$handler->display->display_options['filters']['hydroid_1']['id'] = 'hydroid_1';
$handler->display->display_options['filters']['hydroid_1']['table'] = 'dh_feature';
$handler->display->display_options['filters']['hydroid_1']['field'] = 'hydroid';
$handler->display->display_options['filters']['hydroid_1']['relationship'] = 'crossts_dh_feature_1';
$handler->display->display_options['filters']['hydroid_1']['operator'] = 'not empty';
$handler->display->display_options['filters']['hydroid_1']['group'] = 3;
/* Filter criterion: DH TimeSeries:  */
$handler->display->display_options['filters']['tstime_2']['id'] = 'tstime_2';
$handler->display->display_options['filters']['tstime_2']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['tstime_2']['field'] = 'tstime';
$handler->display->display_options['filters']['tstime_2']['operator'] = '>=';
$handler->display->display_options['filters']['tstime_2']['value']['value'] = '-1 day';
$handler->display->display_options['filters']['tstime_2']['value']['type'] = 'offset';
$handler->display->display_options['filters']['tstime_2']['group'] = 3;
/* Filter criterion: Block Events (default is none) */
$handler->display->display_options['filters']['varidid_2']['id'] = 'varidid_2';
$handler->display->display_options['filters']['varidid_2']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['varidid_2']['field'] = 'varidid';
$handler->display->display_options['filters']['varidid_2']['ui_name'] = 'Block Events (default is none)';
$handler->display->display_options['filters']['varidid_2']['value'] = array(
  'vitis_veraison' => 'vitis_veraison',
);
$handler->display->display_options['filters']['varidid_2']['group'] = 3;
$handler->display->display_options['filters']['varidid_2']['vocabulary'] = array(
  'agchem' => 'agchem',
  'agchem_event' => 'agchem_event',
  'agman' => 'agman',
  'agman_equipment' => 'agman_equipment',
  'plant_modeling' => 'plant_modeling',
  'vitis' => 'vitis',
);
/* Filter criterion: DH TimeSeries: entity_type */
$handler->display->display_options['filters']['entity_type']['id'] = 'entity_type';
$handler->display->display_options['filters']['entity_type']['table'] = 'dh_timeseries';
$handler->display->display_options['filters']['entity_type']['field'] = 'entity_type';
$handler->display->display_options['filters']['entity_type']['value'] = 'dh_feature';
$handler->display->display_options['filters']['entity_type']['group'] = 3;
$handler->display->display_options['filters']['entity_type']['op_location'] = 'where';
$handler->display->display_options['path'] = 'ipm-timeseries-calendar-events';


?>