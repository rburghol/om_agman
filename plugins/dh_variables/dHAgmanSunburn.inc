<?php

$plugin = array(
  'variable' =>  array(
    'varname' => 'Berry Sunburn',
    'vardesc' => 'Sunburn.',
    'vocabulary' => 'fungal_pathogens',
    'varunits' => 'n/a',
    'varkey' => 'berry_sunburn',
    'datatype' => 'organism',
    'varcode' => 'berry_sunburn',
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