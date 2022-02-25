<?php

$plugin = array(
  'label' => t('AgMan - Sprayer Rate per Acre'),
  'handler' =>  array(
    'class' => 'dHVariablePluginAgmanSprayerVolume',
    'file' => 'dHVariablePluginAppRates.class.php',
  ),
  'variable' =>  array(
    'varname' => 'Sprayer Tank Volume',
    'vardesc' => 'Maximum volume of liquid contained by sprayer.',
    'vocabulary' => 'agman_equipment',
    'varunits' => 'gal',
    'varkey' => 'agchem_sprayer_vol',
    'datatype' => 'capacity',
    'varcode' => 'agchem_sprayer_vol',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'value',
    'plugin' => 'dHVariablePluginAgmanSprayerVolume',
    'options' => '',
    'varabbrev' => 'Tank Size',
  ),
);

?>