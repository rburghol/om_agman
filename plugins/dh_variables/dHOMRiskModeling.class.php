<?php
module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');

class dHVPDiseaseRiskSummary extends dHVariablePluginDefault {
  var $obs_varkey = 'frisk_botrytis_index';
  var $summary_varkey = 'frisk_pd_last24hrs_botrytis_idx';
  var $risk_codes = array(0=> 'none', 1=>'low', 2=>'moderate', 3=>'high');
  
  public function summarizeTimePeriod($entity_type, $featureid, $varkey, $begin, $end) {
    $begin = dh_handletimestamp($begin);
    $end = dh_handletimestamp($end);
    $varid = implode(',', dh_varkey2varid($varkey));
    $q = "  select featureid, entity_type, ";
    $q .= "   $begin as tstime, $end as tsendtime, ";
    $q .= "   min(tsvalue) as min_value, ";
    $q .= "   max(tsvalue) as max_value, ";
    $q .= "   avg(tsvalue) as mean_value";
    $q .= " from {dh_timeseries}  ";
    $q .= " where featureid = $featureid ";
    $q .= "   and entity_type = '$entity_type' ";
    $q .= "   and tstime >= $begin ";
    $q .= "   and tstime < $end ";
    $q .= "   and varid = $varid ";
    $q .= " group by featureid, entity_type ";
    //dpm($q,"query for var = $varkey");
    $result = db_query($q);
    $record = $result->fetchAssoc();
    if (empty($record)) {
      $record = array(
        'tstime' => $begin,
        'tsendtime' => $end,
        'min_value' => NULL,
        'max_value' => NULL,
        'mean_value' => NULL,
      );
    }
    //dpm($record,'record');
    return $record;
  }
  
  public function darkVarinfo(&$entity){
    $starthour = 21; // 9 pm, could later calculate this algorithmically based on julian day
    $endhour = 5; // a summertime default
    list($yesteryear, $yestermonth, $yesterday) = explode ('-', date('Y-m-d', (dh_handletimestamp($entity->tstime) - 86400)));
    $begin = implode('-', array($yesteryear, $yestermonth, $yesterday)) . " $starthour:00:00";
    $end = date('Y-m-d', dh_handletimestamp($entity->tstime)) . " $endhour:00:00";
    $varids = dh_varkey2varid($this->summary_varkey);
    $darkinfo = array(
      'featureid' => $entity->featureid,
      'tstime' => dh_handletimestamp($begin),
      'tsendtime' => dh_handletimestamp($end),
      'entity_type' => $entity->entity_type,
      'varid' => array_shift( $varids),
    );
    return $darkinfo;
  }
  
  public function summarizeDaily($entity) {
    // $entity is the dh_timeseries entity in question
    $date = date('Y-m-d', dh_handletimestamp($entity->tstime));
    $begin = $date . " 00:00:00";
    $end = $date . " 23:59:59";
    //dpm('range'," $begin, $end");
    $summary = $this->summarizeTimePeriod($entity->entity_type, $entity->featureid, $this->obs_varkey, $begin, $end);
    $varids = dh_varkey2varid($this->summary_varkey);
    //dpm($varids, $this->daily_varkey);
    $summary['varid'] = array_shift( $varids);
    return $summary;
  }
  
  public function summarizeWeekly($entity) {
    // $entity is the dh_timeseries entity in question
    $ts = dh_handletimestamp($entity->tstime);
    $day = date('w', $ts);
    $begin = date('Y-m-d', $ts - $day * 86400);
    $end = date('Y-m-d', $ts + (7-$day) * 86400);
    //dpm('range'," $begin, $end");
    $summary = $this->summarizeTimePeriod($entity->entity_type, $entity->featureid, $this->obs_varkey, $begin, $end);
    //dpm($varids, $this->daily_varkey);
    $summary['varid'] = dh_varkey2varid($this->summary_varkey, TRUE);
    return $summary;
  }
  
  public function summarizeMonthly($entity) {
    // $entity is the dh_timeseries entity in question
    $ts = dh_handletimestamp($entity->tstime);
    $begin = date('Y-m', $ts) . '-01';
    $end = date('Y-m', $ts) . '-' . date("t", $ts);
    //dpm('range'," $begin, $end");
    $summary = $this->summarizeTimePeriod($entity->entity_type, $entity->featureid, $this->obs_varkey, $begin, $end);
    //dpm($varids, $this->daily_varkey);
    $summary['varid'] = dh_varkey2varid($this->summary_varkey, TRUE);
    //dpm($summary, 'summarizeMonthly');
    return $summary;
  }
  
  public function summarizeDarknessTimePeriod($entity) {
    // $entity is the dh_timeseries entity in question
    $starthour = 21; // 9 pm, could later calculate this algorithmically based on julian day
    $endhour = 5; // a summertime default
    list($yesteryear, $yestermonth, $yesterday) = explode ('-', date('Y-m-d', (dh_handletimestamp($entity->tstime) - 86400)));
    //dpm(array($yesteryear, $yestermonth, $yesterday),'yesterday');
    $begin = implode('-', array($yesteryear, $yestermonth, $yesterday)) . " $starthour:00:00";
    $end = date('Y-m-d', dh_handletimestamp($entity->tstime)) . " $endhour:00:00";
    //dpm('range'," $begin, $end");
    $summary = $this->summarizeTimePeriod($entity->entity_type, $entity->featureid, $this->obs_varkey, $begin, $end);
    $varids = dh_varkey2varid($this->summary_varkey);
    //dpm($varids, $this->summary_varkey);
    $summary['varid'] = array_shift( $varids);
    return $summary;
  }
}

// Daily DiseaseRisk Summary including:
// * Daily averages
// * Night time conditions
// we could employ 2 strategies here:
//   1. Recalculate this as a result of insert/update of a realtime observation
//   2. Recalculate this as a result of explicit call to Daily Summary
// Method #1 would eliminate any potential for the summary to be out of synch with the observed
// Method #2 would be more time efficient, since method #1 would execute 96 times per day on 15 min data
// Also, this could be linked as part of the save() event of realtime data if we desired essentially 
// merging #1 and #2.  So #2 is best for now.
// don't recalculate if the given timestamp is not inside the darkness window
// this will reduce redundant saves
/* 
// Method #1 - run as plugin on realtime variable
list($year, $month, $day, $hour) = explode ('-', date('Y-m-d', $tstime));
list($year, $month, $day, $hour) = explode ('-', date('Y-m-d', $tstime));
if ( ($hour <= $endhour) or ($hour >= $starthour) ) {
  return FALSE;
}
if ($hour <= $endhour) {
  list($yesteryear, $yestermonth, $yesterday) = explode ('-', date('Y-m-d', ($tstime - 86400)));
}
if ($hour >= $starthour) {
  list($morrowyear, $morrowmonth, $morrowday) = explode ('-', date('Y-m-d', ($tstime + 86400)));
}
*/

// Method #2 - run as summary that looks at other variable
// See below


class dHVPLast24DiseaseRisk extends dHVPDiseaseRiskSummary {
  // Create a Most recent data summary
  var $obs_varkey = 'frisk_botrytis_index';
  var $summary_varkey = 'frisk_pd_last24hrs_botrytis_idx';
  
  public function __construct($conf = array()) {
    parent::__construct($conf);
    $hidden = array('tid', 'tstime', 'tsendtime', 'featureid', 'entity_type', 'bundle');
    foreach ($hidden as $hide_this) {
      $this->property_conf_default[$hide_this]['hidden'] = 1;
    }
  }
  
  public function formRowEdit(&$rowform, $entity) {
    // apply custom settings here
    parent::formRowEdit($rowform, $entity);
  }
  
  public function summarizeLast24Hours($entity) {
    // $entity is the dh_timeseries entity in question
    $varids = dh_varkey2varid($this->obs_varkey);
    //dpm($varids, $this->darkness_varkey);
    $varid = array_shift( $varids);
    $date = dh_handletimestamp(date('Y-m-d'));
    $entity->tstime = $date;
    //dpm('range'," $begin, $end");
    $summary = $this->summarizeDaily($entity);
    $varids = dh_varkey2varid($this->summary_varkey);
    //dpm($varids, $this->darkness_varkey);
    $summary['varid'] = array_shift( $varids);
    return $summary;
  }

  public function save(&$entity){
    // Find the last 24 hours in this entities time series
    $summary = $this->summarizeLast24Hours($entity);
    // update this entity (ts weather) to have the last 24 data in it
    $entity->tstime = $summary['tstime'];
    $entity->tsendtime = $summary['tsendtime'];
    $entity->tsvalue = $summary['max_value'];
    $entity->tscode = ($summary['max_value'] === NULL) ? 'unknown' : $this->risk_codes[$summary['max_value']];
    //dpm($entity,'final entity');
    parent::save($entity);
  }
  /*
  public function buildContent(&$content, &$entity, $view_mode) {
    // @todo: handle teaser mode and full mode with plugin support
    //        this won't happen till we enable at module level however, now it only 
    //        is shown when selecting "plugin" in the view mode in views
    $content['#view_mode'] = $view_mode;
    // hide all to begin then allow individual mode to control visibility
    $hidden = array('varname', 'tstime', 'tid', 'tsvalue');
    foreach ($hidden as $col) {
      $content[$col]['#type'] = 'hidden';
    }
    
    //$summary = $this->summarizeDarknessTimePeriod($entity);
    //dpm($summary,'summary realtime');
    $sumrecs = dh_get_timeseries($this->darkVarinfo($entity));
    if (isset($sumrecs['dh_timeseries'])) {
      //dpm($result,"found records - checking singularity settings");
      $data = entity_load('dh_timeseries', array_keys($summary['dh_timeseries']));
      $summary = array_shift($data);
    }
    //dpm($summary,'summary saved');
    // @todo: fix this up 
    $uri = "ipm-live-events/$vineyard/sprayquan/$feature->adminid";
    $link = array(
      '#type' => 'link',
      '#prefix' => '&nbsp;',
      '#suffix' => '<br>',
      '#title' => 'Go to ' . $uri,
      '#href' => $uri,
      'query' => array(
        'finaldest' => 'ipm-home/all/all/',
      ),
    );
    switch ($view_mode) {
      case 'teaser':
      case 'summary':
      // summary is like teaser except that Drupal adds label as title to teaser regardless
        $content['last_updated'] = array(
          '#type' => 'item',
          '#markup' => date('Y-m-d h:m', $entity->tsendtime),
        );
        $header = array('Temp (lo / hi / dark)',	'RH',	'Wet hrs. (all/dark)');
        $rows = array(
          0=>array(
              round($entity->temp, 1) . '°F (' 
              . round($entity->tmax, 1) . ' / '
              . round($entity->tmin, 1) . ' / '
              . round( $summary->temp, 1) . ')'
            ,
            round($entity->rh,1) . ' %',
            round($entity->wet_time/60,1) . " / " . round($summary->wet_time/60.0,1)),
        );
        $content['table'] = array(
          '#theme' => 'table',
          '#header' => $header,
          '#rows' => $rows,
          '#attributes' => array (
            'class' => array('views-table', 'cols-3', 'table', 'table-hover', 'table-striped'),
          ),
        );
        $content['link'] = $link; 
        $content['modified']['#markup'] = '(modified on ' . date('Y-m-d', $entity->tstime) . ")"; 
      break;
      
      // @todo: develop weather summary suitable for iCal???
      case 'ical_summary':
        unset($content['title']['#type']);
        #$content['body']['#type']= 'item'; 
        $content['body']['#markup'] = $title; 
        $content = array();
      break;
      
      case 'full':
      case 'plugin':
      default:
      // @todo: what should the full view look like??
        $content['title'] = array(
          '#type' => 'item',
          '#markup' => $title,
        );         
        $content['blocks'] = array(
          '#type' => 'item',
          '#markup' => '<b>Blocks:</b> ' .implode(', ', $block_names),
        );         
        $content['materials'] = array(
          '#type' => 'item',
          '#markup' => '<b>Materials:</b> ' .implode(', ', $chem_names),
        );
        $content['link'] = $link; 
        $entity->title = $title;
        $content['modified']['#markup'] = '(modified on ' . date('Y-m-d', $feature->modified) . ")"; 
      break;
    }
  }
  */
}

class dHVPLast24BotrytisRisk extends dHVPLast24DiseaseRisk {
  // Create a Most recent data summary
  var $obs_varkey = 'frisk_botrytis_index';
  var $summary_varkey = 'frisk_pd_last24hrs_botrytis_idx';
}

class dHVPLast24PowderyRisk extends dHVPLast24DiseaseRisk {
  // Create a Most recent data summary
  var $obs_varkey = 'frisk_powderymildew_index';
  var $summary_varkey = 'frisk_pd_last24hrs_pmildew_idx';
}


class dHVPLast24BlackRotRisk extends dHVPLast24DiseaseRisk {
  // Create a Most recent data summary
  var $obs_varkey = 'frisk_blackrot_index';
  var $summary_varkey = 'frisk_pd_last24hrs_blackrot_idx';
}


class dHVPLast24PhomopsisRisk extends dHVPLast24DiseaseRisk {
  // Create a Most recent data summary
  var $obs_varkey = 'frisk_phomopsis_index';
  var $summary_varkey = 'frisk_pd_last24hrs_phomopsis_idx';
}

?>