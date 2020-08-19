<?php

$plugin = array(
  'variable' =>  array(
    'varname' => 'Nutrient Defficiency',
    'vardesc' => 'Symptons of suspected nutrient defficiency observed.',
    'vocabulary' => 'plant_ipm',
    'varunits' => 'n/a',
    'varkey' => 'ipm_nutdef',
    'datatype' => 'observation',
    'varcode' => 'ipm_nutdef',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'code',
    // Uses same plugin as other pathogens
    'plugin' => 'dHVariablePluginEfficacy',
    'options' => '',
    'varabbrev' => 'N.Def',
  ),
);

?>