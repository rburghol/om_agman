<?php

$plugin = array(
  'variable' =>  array(
    'varname' => 'Grapevine Yellows',
    'vardesc' => 'North American Grapevine Yellows (NAGY).',
    'vocabulary' => 'fungal_pathogens',
    'varunits' => 'n/a',
    'varkey' => 'org_nagy',
    'datatype' => 'organism',
    'varcode' => 'org_nagy',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'code',
    // Uses same plugin as other pathogens
    'plugin' => 'dHVariablePluginEfficacy',
    'options' => '',
    'varabbrev' => 'NAGY',
  ),
);

?>