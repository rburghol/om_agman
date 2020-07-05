<?php

$plugin = array(
  'variable' =>  array(
    'varname' => 'Misc. Virus',
    'vardesc' => 'Any non-specific viral pathogen.',
    'vocabulary' => 'fungal_pathogens',
    'varunits' => 'n/a',
    'varkey' => 'org_virus',
    'datatype' => 'organism',
    'varcode' => 'org_virus',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'code',
    // Uses same plugin as other pathogens
    'plugin' => 'dHVariablePluginEfficacy',
    'options' => '',
    'varabbrev' => 'Virus',
  ),
);

?>