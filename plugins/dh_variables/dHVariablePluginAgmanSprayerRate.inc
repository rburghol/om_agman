<?php

$plugin = array(
  'label' => t('AgMan - Sprayer Rate per Acre'),
  'handler' =>  array(
    'class' => 'dHVariablePluginAgmanSprayerRate',
    'file' => 'dHVariablePluginAppRates.class.php',
  ),
  'variable' =>  array(
    'varname' => 'Agman: Sprayer Default Rate',
    'vardesc' => 'Default Spray Volume per acre.',
    'vocabulary' => 'agman_equipment',
    'varunits' => 'gal',
    'varkey' => 'agman_sprayrate_default_galac',
    'datatype' => 'volume',
    'varcode' => 'agman_sprayrate_default_galac',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'value',
    'plugin' => 'dHVariablePluginAgmanSprayerRate',
    'options' => '',
    'varabbrev' => 'Spray Rate',
  ),
);

?>