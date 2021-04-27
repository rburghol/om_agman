<?php


function om_agman_material_query($criteria = array()) {
  
  $farm_id = isset($criteria['farm_id']) ? $criteria['farm_id']['value'] : -1;
  //dpm($criteria,'criteria');
  $query = "select farm.hydroid, chem.adminid, chem.name, chem.ftype, ";
  $query .="  br.propvalue as br,  ";
  $query .="  dm.propvalue as dm, ";
  $query .="  pm.propvalue as pm, ";
  $query .="  ph.propvalue as ph, ";
  $query .="  bot.propvalue as bot, ";
  $query .="  rei.propvalue as rei, ";
  $query .="  phi.propvalue as phi, ";
  $query .="  max(pmg.propcode) as pmg, ";
  // this will work for any target org since when we use the target org filter
  // it will only select one anyhow.  Otherwise, this will select the lowest varid target org
  // but that's ok since it won't be usd. This is a bit hinky, and it only is needed because 
  // we are struggling to make this SQL deliver all results to the browser to maintain compliance 
  // with correct formAPI useage.  Maybe there is a better way?
  $query .="  min(target_org.varid) as target_org_id, "; // tbd: this data model could be made to handle any and all organisms (weeds, insects too) by ditching the varid = pathogen-variable-id for a custom entity link property 
  $query .="  min(target_org.propvalue) as target_org_eff, ";
  $query .="  array_accum(frac.propcode) as frac_codes, ";
  $query .="  array_accum(ai.propname) as ai ";
  // tbd: we could do searching 2 different, ($filter_in_sql = true or False) by reporting in a field if this is a match
  //      and then deciding whether or not to filter on "match" or not in an external query like so:
  //   select * from (
  //      select farm.hydroid, chem.name, ..., 
  //      CASE 
  //   if (isset($criteria['target_frac']) {
  //      WHEN target_frac IS NOT NULL THEN 1
  //   }
  //   if (isset($criteria['target_org']) {
  //      WHEN target_org IS NOT NULL THEN 1
  //    }
  //   if (isset($criteria['rei']) {
  //      WHEN rei IS NOT NULL THEN 1
  //    }
  //    ELSE 0 AS search_match
  //    ... (more sql)
  //   ) as foo 
  //  if ($filter_in_sql) {
  //   WHERE search_match is not null
  //  }
  $query .="from dh_adminreg_feature as chem  ";
  $query .="left outer join field_data_field_link_agchem_material as clink ";
  $query .="  on (";
  $query .="    clink.field_link_agchem_material_target_id = chem.adminid";
  $query .="    and clink.entity_id = $farm_id";
  $query .="  )";
  $query .="  left outer join dh_feature as farm ";
  $query .="  on (";
  $query .="    farm.hydroid = clink.entity_id";
  $query .="    and clink.entity_type = 'dh_feature' ";
  $query .="  ) ";
  $query .="  left outer join dh_properties as br ";
  $query .="  on (";
  $query .="    br.featureid = chem.adminid";
  $query .="    and br.entity_type = 'dh_adminreg_feature'";
  $query .="    and br.varid in (select hydroid from dh_variabledefinition where varkey = 'org_black_rot')";
  $query .="  )";
  $query .="  left outer join dh_properties as dm ";
  $query .="  on (";
  $query .="    dm.featureid = chem.adminid";
  $query .="    and dm.entity_type = 'dh_adminreg_feature'";
  $query .="    and dm.varid in (select hydroid from dh_variabledefinition where varkey = 'org_downy_mildew')";
  $query .="  )";
  $query .="  left outer join dh_properties as pm ";
  $query .="  on (";
  $query .="    pm.featureid = chem.adminid";
  $query .="    and pm.entity_type = 'dh_adminreg_feature'";
  $query .="    and pm.varid in (select hydroid from dh_variabledefinition where varkey = 'org_powdery_mildew')";
  $query .="  )";
  $query .="  left outer join dh_properties as bot ";
  $query .="  on (";
  $query .="    bot.featureid = chem.adminid";
  $query .="    and bot.entity_type = 'dh_adminreg_feature'";
  $query .="    and bot.varid in (select hydroid from dh_variabledefinition where varkey = 'org_botrytis')";
  $query .="  )";
  $query .="  left outer join dh_properties as ph ";
  $query .="  on (";
  $query .="    ph.featureid = chem.adminid";
  $query .="    and ph.entity_type = 'dh_adminreg_feature'";
  $query .="    and ph.varid in (select hydroid from dh_variabledefinition where varkey = 'org_phomopsis')";
  $query .="  )";
  $query .="  left outer join dh_properties as rei ";
  $query .="  on (";
  $query .="    rei.featureid = chem.adminid";
  $query .="    and rei.entity_type = 'dh_adminreg_feature'";
  $query .="    and rei.varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_rei')";
  $query .="  )";
  $query .="  left outer join dh_properties as phi ";
  $query .="  on (";
  $query .="    phi.featureid = chem.adminid";
  $query .="    and phi.entity_type = 'dh_adminreg_feature'";
  $query .="    and phi.varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_phi')";
  $query .="  )";
  $query .="  left outer join dh_properties as pmg ";
  $query .="  on (";
  $query .="    pmg.featureid = chem.adminid";
  $query .="    and pmg.entity_type = 'dh_adminreg_feature'";
  $query .="    and pmg.varid in (select hydroid from dh_variabledefinition where varkey = 'ipm_vt_pmg_material')";
  $query .="  )";
  // always include a target org, but if there was not one submitted, just join a null variable to have the value fields
  // we will only filter on this in list mode here, but there is code in the form widget display to filter out on 
  // efficacy. 
  $tvid = isset($criteria['target_org']) ? $criteria['target_org']['value'] : 'show_nothing_at_all';
  $query .= " left outer join dh_properties as target_org ";
  $query .= " on (";
  $query .= "   target_org.featureid = chem.adminid ";
  $query .= "   and target_org.entity_type = 'dh_adminreg_feature' ";
  $query .= "   and target_org.varid in (select hydroid from dh_variabledefinition where varkey = '" . $criteria['target_org']['value']  . "')";
  $query .= "   and target_org.propvalue is not null ";
  // if we ask for target org, we must make sure the efficacy is between 1 and 7, but is NOT equal to 6 (N for none)
  // this use of 6 is bullshit data modeling.  It should be zero.  We will need to make this amendment to the database via SQL
  // some time soon.  But for now, we filter here, then later if we have an efficacy minimum, it will throw a >= that should still 
  // be compatible with this query logic. 
  $query .= " and (target_org.propvalue >= 1 and target_org.propvalue <> 6) "; ;
  $query .= " )";  
  $query .= " left outer join dh_properties as frac
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
  if (isset($criteria['linked']) and ($criteria['linked'] === TRUE)) {
    $query .= " and farm.hydroid is not null ";
  }
  if (isset($criteria['phi'])) {
    $query .= " and phi.propvalue <= " . intval($criteria['phi']['value']);
  }
  if (isset($criteria['ftype'])) {
    $query .= " and chem.ftype = '" . $criteria['ftype']['value'] . "'";
  }
  
  if (isset($criteria['name'])) {
    $query .= " and chem.name ilike '%" . $criteria['name']['value'] . "%'"; ;
  }
  if (isset($criteria['pmg'])) {
    $query .= " and pmg.propcode ilike '%" . $criteria['pmg']['value'] . "%'"; ;
  }
  
  if (isset($criteria['ai'])) {
    $query .= " and chem.adminid in (" ;
    $query .= "   select featureid from dh_properties where   ";
    $query .= "   entity_type = 'dh_adminreg_feature' ";
    $query .= "   and varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_ai') ";
    $query .= "   and propcode ilike '%" . $criteria['ai']['value'] . "%' ";
    $query .= " ) ";
  }
  // could this be used in a "report match" type query, with the target frac as a separate additional join?
  if (isset($criteria['frac'])) {
    $query .= " and chem.adminid in (" ;
    $query .= "   select featureid from dh_properties where   ";
    $query .= "   entity_type = 'dh_adminreg_feature' ";
    $query .= "   and varid in (select hydroid from dh_variabledefinition where varkey = 'agchem_frac') ";
    $query .= "   and propcode = '" . $criteria['frac']['value'] . "' ";
    $query .= " ) ";
  }
  if (isset($criteria['chems'])) {
    $query .= " and chem.adminid in (" . implode(",", $criteria['chems']['value']) . " ) ";
  }
  if (isset($criteria['target_eff'])) {
    $query .= " and target_org.propvalue >= " . $criteria['target_eff']['value'];
  }
  $query .= " group by farm.hydroid, chem.adminid, chem.name, br.propvalue, dm.propvalue, ph.propvalue, pm.propvalue, bot.propvalue, rei.propvalue, phi.propvalue";
  $query .= " order by chem.name, br.propvalue, dm.propvalue, pm.propvalue ";
  //dpm($query,'q');
  //dpm($criteria,'criteria');
  return $query;
}


function om_agman_material_search_opts($mode, $criteria, $new_chems = array(), $page_size = 25, $page = 1) {
  // $mode = 
  //   current - shows a farms current chems 
  //   select - to be used as a selector, so show all options 
  //   confirm - just use the values that are passed in view criteria['chems']
  //   search - same as select, except do not include check boxes. Use -1 as $farm_id 
  //     tbd: we could pass in args to filter on the query side if it were faster 
  //$query = om_agman_material_query($farm_id, $show_all, $criteria); // see https://github.com/rburghol/om_agman/issues/240
  // filter on the display side 
  switch($mode) {
    case 'confirm':
      $sql_criteria = $criteria;
    break;
    
    case 'search':
      $sql_criteria = $criteria;
    break;
    
    case 'current':
      $sql_criteria = array();
      $sql_criteria['farm_id'] = $criteria['farm_id'];
      $sql_criteria['linked'] = TRUE;
    break;
    
    default:
      $sql_criteria = array();
      $sql_criteria['farm_id'] = $criteria['farm_id'];
      $sql_criteria['target_org'] = $criteria['target_org'];
    break;
  }
  $query = om_agman_material_query($sql_criteria); // see https://github.com/rburghol/om_agman/issues/240
  //dpm($query,'q');
  $agchem_result = db_query($query);
  $element_header = array(
    'label' => 'Material', 
    'ftype'=>'Type', 
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
    $eff_abbrevs = array(
      '' => 'U',
      1 => 'E',
      2 => 'G',
      3 => 'G_F',
      4 => 'F',
      5 => 'P',
      6 => 'N',
      7 => 'Lab',
    );
    $eff_full = array(
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
  // target orgs
  // this is a crap[py work around because we cant figure out how to dynamically update form select options without triggering the "illegal value"
  // that drupal formapi reports when we do that (it is a security feature but there must be a safe way around it)
  // since the filter is base on the varkey, but the easiest property join returns the varid, we do a quick query to retrieve the varid 
  // for use later in our filters
  $target_org_id = FALSE;
  if (isset($criteria['target_org'])) {
    $target_org_var = dh_vardef_info($criteria['target_org']['value']); 
    $target_org_id = $target_org_var->hydroid; 
    $target_org_name = $target_org_var->varname; 
    if (!in_array($target_org_var->varkey, array('org_black_rot', 'org_downy_mildew', 'org_phomopsis', 'org_powdery_mildew', 'org_botrytis'))) {
      $element_header['target'] = $target_org_name;
      $codekeys[$target_org_name] = 'target_org_eff';
    }
  }
  //dpm($target_org_id,' target varid');
  //dpm($eff_abbrevs,'abbrevs');
  $count = 0;
  while($chem_record = $agchem_result->fetchObject()) {
    $id = $chem_record->adminid;
    $count++;
    if ($chem_record->hydroid > 0) {
      $selected[$chem_record->adminid] = 1;
    }
    $chem_record->frac_codes = implode(', ', array_unique(explode(',', trim($chem_record->frac_codes,'{}'))));
    $chem_record->ai = implode(', ', array_unique(explode(',', str_replace('"', "", trim(strtolower($chem_record->ai),'{}')))));
    //dpm($chem_record);

    // BEGIN - tableselect  
    $opts[$id]['label'] .= ($chem_record->pmg == 'PMG') ? $chem_record->name . "<sup>PMG</sup>" : $chem_record->name;
    $opts[$id]['ftype'] = $chem_record->ftype;
    $opts[$id]['AI'] = $chem_record->ai;
    $opts[$id]['REI'] = $chem_record->rei;
    $opts[$id]['PHI'] = $chem_record->phi;
    $opts[$id]['FRAC'] = in_array($chem_record->frac_codes, array('""', 'NULL')) ? 'unknown' : $chem_record->frac_codes;

    foreach ($codekeys as $label => $key) {
      $opts[$id][$label] = $eff_abbrevs[$chem_record->{$key}];
      if ($count == 1) {
        dpm($eff_abbrevs[$chem_record->{$key}], $label . ' and ' .$key);
      }
    }
    // TBD: for paging, we could use the following style override to hide the items
    //      that are NOT on the current page.  Hidden items DO get returned as selected 
    //      form values if their checkbox would be selected.  This could be done on rebuild
    //      without negatively impacting the form integrity.
    // don't filter on query, use hiding instead
    // NOTE: When in normal table mode (not tableselect) this hiding does not appear to work.  Instead we filter in SQL
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
    if (isset($criteria['ftype'])) {
      if (strpos(strtolower($chem_record->ftype), strtolower($criteria['ftype']['value'])) === FALSE) {
        $opts[$id]['#attributes'] = array('style' => 'display: none');
      }
    }
    if (isset($criteria['phi'])) {
      if (intval($chem_record->phi) > intval($criteria['phi']['value'])) {
        $opts[$id]['#attributes'] = array('style' => 'display: none');
      }
    }
    // this is just presence, efficacy is separate
    // see rant above about using "6" as the "no effect" indicator.
    if (isset($criteria['target_org']) and property_exists($chem_record, 'target_org_id')) {
      if ( 
        (intval($chem_record->target_org_id) != intval($target_org_id))
        or (intval($chem_record->target_org_eff)  == 6)
      ) {
        $opts[$id]['#attributes'] = array('style' => 'display: none');
      }
    }
    if ($count == 1) {
      dpm($chem_record,'record');
      //dpm($criteria,'criteria');
    }
    // If this is the search mode, we are looking for chems that do NOT have a link,
    if ($mode == 'select') {
      if ($chem_record->hydroid > 0) {
        $opts[$id]['label'] = "**" . $opts[$id]['label'];
      }
      
      // Now disabled 
      // We tried to hide those that are already part of this farm 
      // disabled. This caused confusion where there was a similar chem name and it created the impression that the user was missing a chem
      // so we are showing these again.  It might be better to warn the users at the top of the page, or other that 
      // if it is NOT linked, it will not have a hydroid, so we know any that DO have a hydroid are to be hidden
      /*
      if ($chem_record->hydroid > 0) {
        $opts[$id]['#attributes'] = array('style' => 'display: none');
      }
      */
    }
    if ($count == 1) {
      dpm($opts[$id], 'final record');
    }
    // ****************************
    // END - FILTER DISPLAY
    // ****************************
    $delim = '';
    // we now have a frac aggregator which saves as a short propcode
    // END - tableselect
  }
  dpm($opts,'opts');
  
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
  $element['#header'] = $element_header;
  return $element;
}


?>