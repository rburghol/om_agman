<?php


function om_agman_material_query($criteria = array()) {
  
  /*
  $farm_id = isset($criteria['farm_id']) ? $criteria['farm_id']['value'] : -1;
  //dpm($criteria,'criteria');
  $query = "select farm.hydroid, chem.adminid, chem.name, 
    br.propvalue as br, 
    dm.propvalue as dm,
    pm.propvalue as pm,
    array_accum(frac.propcode) as frac_codes,
    array_accum(ai.propname) as ai
  from dh_adminreg_feature as chem 
  left outer join field_data_field_link_agchem_material as clink 
  on (
    clink.field_link_agchem_material_target_id = chem.adminid
    and clink.entity_id = $farm_id
  )
  left outer join dh_feature as farm 
  on (
    farm.hydroid = clink.entity_id
    and clink.entity_type = 'dh_feature' 
  ) 
  left outer join dh_properties as br 
  on (
    br.featureid = chem.adminid
    and br.entity_type = 'dh_adminreg_feature'
    and br.varid in (select hydroid from dh_variabledefinition where varkey = 'org_black_rot')
  )
  left outer join dh_properties as dm 
  on (
    dm.featureid = chem.adminid
    and dm.entity_type = 'dh_adminreg_feature'
    and dm.varid in (select hydroid from dh_variabledefinition where varkey = 'org_downy_mildew')
  )
  left outer join dh_properties as pm 
  on (
    pm.featureid = chem.adminid
    and pm.entity_type = 'dh_adminreg_feature'
    and pm.varid in (select hydroid from dh_variabledefinition where varkey = 'org_powdery_mildew')
  )
  left outer join dh_properties as frac
  on (
    frac.featureid = chem.adminid
    and frac.entity_type = 'dh_adminreg_feature'
    and frac.varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_frac')
  )
  left outer join dh_properties as ai
  on (
    ai.featureid = chem.adminid
    and ai.entity_type = 'dh_adminreg_feature'
    and ai.varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_ai')
  )
  where 
  chem.ftype in ('insecticide', 'fungicide', 'herbicide', 'fertilizer', 'pesticide', 'other') ";
  if (isset($criteria['linked'] and ($criteria['linked'] === TRUE))) {
    $query .= " and farm.hydroid is not null ";
  }
  if (isset($criteria['frac'])) {
    $query .= " and chem.adminid in (" ;
    $query .= "   select featureid from dh_properties where   ";
    $query .= "   entity_type = 'dh_adminreg_feature' ";
    $query .= "   and varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_frac') ";
    $query .= "   and propcode = '" . $criteria['frac']['value'] . "' ";
    $query .= " ) ";
  }
  */
  if (isset($criteria['chems'])) {
    $query .= " and chem.adminid in (" . implode(",", $criteria['chems']['value']) . " ) ";
  }
  $query .= " group by farm.hydroid, chem.adminid, chem.name, br.propvalue, dm.propvalue, pm.propvalue";
  $query .= " order by chem.name, br.propvalue, dm.propvalue, pm.propvalue ";
  dpm($query,'q');
  return $query;
}

/*
function om_agman_material_search_opts($mode, $criteria, $new_chems = array(), $page_size = 25, $page = 1) {
  // $mode = 
  //   select - to be used as a selector, so show all options 
  //   confirm - just use the values that are passed in view criteria['chems']
  //   search - same as select, except do not include check boxes. Use -1 as $farm_id 
  //     tbd: we could pass in args to filter on the query side if it were faster 
  //$query = om_agman_material_query($farm_id, $show_all, $criteria); // see https://github.com/rburghol/om_agman/issues/240
  // filter on the display side 
  switch($mode) {
    // tbd: enable sql side filtering if performance gains cab be had? 
    case 'confirm':
      $sql_criteria = $criteria;
    break;
    
    default:
      $sql_criteria = array();
      $sql_criteria['farm_id'] = $criteria['farm_id'];
    break;
  }
  $query = om_agman_material_query($sql_criteria); // see https://github.com/rburghol/om_agman/issues/240
  //dpm($query,'q');
  $agchem_result = db_query($query);
  
  $opts = array();
  $selected = array();
  // Get formatting for 
  $plugin = ctools_get_plugins('dh', 'dh_variables', 'dHVariablePluginEfficacy');
  $class = ctools_plugin_get_class($plugin, 'handler');
  //dpm($plugin,'plug');
  $config = array();
  if ($class) {
    $eff = new $class($config);
    $eff_abbrevs = $eff->effAbbrev();
    $eff_full = $eff->effFull();
  } else {
    $ea = array(
      '' => 'U',
      1 => 'E',
      2 => 'G',
      3 => 'G_F',
      4 => 'F',
      5 => 'P',
      6 => 'N',
      7 => 'Lab',
    );
    $f = array(
      '' => 'Unknown',
      1 => 'Excellent',
      2 => 'Good',
      3 => 'Good/Fair',
      4 => 'Fair',
      5 => 'Poor',
      6 => 'None',
      7 => 'Labelled',
    );
  }
  //dpm($eff_abbrevs,'abbrevs');
  $count = 0;
  while($chem_record = $agchem_result->fetchObject()) {
    $id = $chem_record->adminid;
    $count++;
    if ($chem_record->hydroid > 0) {
      $selected[$chem_record->adminid] = 1;
    }
    $chem_record->frac_codes = trim($chem_record->frac_codes,'{}');
    $chem_record->ai = implode(', ', array_unique(explode(',', trim(trim(strtolower($chem_record->ai),'{}'), '"'))));
    //dpm($chem_record);
    $valkeys = array(
      'REI'=>'agchem_rei', 
      'PHI' => 'agchem_phi', 
    );
    $codekeys = array(
      'PM' => 'pm', 
      'DM' => 'dm', 
      'Ph' => 'ph', 
      'BR' => 'br', 
      'Bot' => 'bot'
    );

    // BEGIN - tableselect
    $opts[$id] = array(
      'label' => $chem_record->name . ' ' . $chem_record->ftype, 
    );
    $opts[$id]['AI'] = $chem_record->ai;
    $opts[$id]['REI'] = $chem_record->rei;
    $opts[$id]['PHI'] = $chem_record->phi;
    $opts[$id]['FRAC'] = in_array($chem_record->frac_codes, array('""', 'NULL')) ? 'unknown' : $chem_record->frac_codes;

    foreach ($codekeys as $label => $key) {
      $opts[$id][$label] = $eff_abbrevs[$chem_record->{$key}];
    }
    // TBD: for paging, we could use the following style override to hide the items
    //      that are NOT on the current page.  Hidden items DO get returned as selected 
    //      form values if their checkbox would be selected.  This could be done on rebuild
    //      without negatively impacting the form integrity.
    // don't filter on query, use hiding instead
    // ****************************
    // FILTER DISPLAY
    // ****************************
    if (isset($criteria['frac'])) {
      if (!in_array($criteria['frac']['value'], explode(',', $chem_record->frac_codes))) {
        $opts[$id]['#attributes'] = array('style' => 'display: none');
      }
    }
    if (isset($criteria['ai'])) {
      // $chem_record->ai is already converted to lower case 
      if (strpos($chem_record->ai, strtolower($criteria['ai']['value'])) === FALSE) {
        $opts[$id]['#attributes'] = array('style' => 'display: none');
      }
    }
    if (isset($criteria['name'])) {
      if (strpos(strtolower($chem_record->name), strtolower($criteria['name']['value'])) === FALSE) {
        $opts[$id]['#attributes'] = array('style' => 'display: none');
      }
    }
    // ****************************
    // END - FILTER DISPLAY
    // ****************************
    $delim = '';
    // we now have a frac aggregator which saves as a short propcode
    // END - tableselect
  }
  //dpm($opts,'opts');
  
  switch ($mode) {
    case 'search': 
    // just show a table
      $element = array(
        '#theme' => 'table',
        '#type' => 'table',
        '#rows' => $opts,
        '#size' => 25,
      );
    break;
    
    default:
      $element = array(
        '#type' => 'tableselect',
        '#options' => $opts,
        '#default_value' => $selected,
        '#required' => TRUE,
        '#multiple' => TRUE,
        '#size' => 25,
      );
    break;
  }
  
//dpm($count,'count');
  $element['#empty'] = t('No content available.');
  $element['#required'] = TRUE;
  $element['#header'] = array(
    'label' => 'Material', 
    'AI'=>'Active Ingredient(s)', 
    'REI'=>'REI', 
    'PHI' => 'PHI', 
    'FRAC' => 'FRAC', 
    'PM' => 'PM', 
    'DM' => 'DM', 
    'Ph' => 'Ph', 
    'BR' => 'BR', 
    'Bot' => 'Bot'
  );
  return $element;
}
*/

?>