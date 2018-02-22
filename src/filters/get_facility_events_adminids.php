// must assemble a list of block hydroids for the selected vineayrd hydroid
// then must include them as csv here and remove the eref to the vineyard
// then obtain a list of all events that are referencing these blocks so the eref table never need be joined
// this is tested as a robust solution for up to 2,048 event IDs (roughly 200 years worth of records)
// this will avoid double counting
$blocks = dh_get_facility_mps($argument);
if (!empty($blocks)) {
  $eref_config = array();
  $eref_config['eref_fieldname'] = 'dh_link_feature_submittal';
  $eref_config['target_entity_id'] = $blocks;
  $eref_config['entity_type'] = 'dh_adminreg_feature';
  $eref_config['entity_id_name'] = 'adminid';

  $events = dh_get_reverse_erefs($eref_config);
  //dpm($events,'events');
    
  $handler->argument = implode(',', $events );
  //dpm($handler->argument,'new arg');
  return TRUE;
}
return FALSE;