<?php

$plugin = array(
  'variable' =>  array(
    'varname' => 'Other Disease',
    'vardesc' => 'Any non-specific pathogen -- describe in comments.',
    'vocabulary' => 'fungal_pathogens',
    'varunits' => 'n/a',
    'varkey' => 'org_other',
    'datatype' => 'organism',
    'varcode' => 'org_other',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'code',
    // Uses same plugin as other pathogens
    'plugin' => 'dHVariablePluginEfficacy',
    'options' => '',
    'varabbrev' => 'Other',
  ),
);

?>