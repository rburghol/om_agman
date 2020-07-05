<?php

$plugin = array(
  'variable' =>  array(
    'varname' => 'Crown Gall',
    'vardesc' => 'Crown gall of grapevine.',
    'vocabulary' => 'fungal_pathogens',
    'varunits' => 'n/a',
    'varkey' => 'org_crown_gall',
    'datatype' => 'organism',
    'varcode' => 'org_crown_gall',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'code',
    // Uses same plugin as other pathogens
    'plugin' => 'dHVariablePluginEfficacy',
    'options' => '',
    'varabbrev' => 'Crown Gall',
  ),
);

?>