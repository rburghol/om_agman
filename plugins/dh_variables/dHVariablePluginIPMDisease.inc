<?php

$plugin = array(
  'label' => t('IPM Disease Outbreak'),
  'handler' =>  array(
    'class' => 'dHVariablePluginIPMDisease',
    'file' => 'dHVariablePluginAgmanVitis.class.php',
  ),
  'variable' =>  array(
    'varname' => 'Disease Report',
    'vardesc' => 'Report observation of disease.',
    'vocabulary' => 'plant_ipm',
    'varunits' => '%',
    'varkey' => 'ipm_outbreak',
    'datatype' => 'percent',
    'varcode' => 'ipm_outbreak',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => '',
    'plugin' => 'dHVariablePluginIPMDisease',
    'options' => '',
    'varabbrev' => 'dis',
  ),
);

?>