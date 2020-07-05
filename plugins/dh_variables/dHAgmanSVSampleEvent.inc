<?php

$plugin = array(
  'label' => t('AgMan - Sentinel Vineyard Sampling Form'),
  'handler' =>  array(
    'class' => 'dHAgmanSVSampleEvent',
    'file' => 'dHVariablePluginAgmanVitis.class.php',
  ),
  'variable' =>  array(
    'varname' => 'Agman - Vineyard Scouting Form',
    'vardesc' => 'Provides a property to describe the area of a land feature.  Units are selectable and function exists to convert from one unit to another (i.e. acres to hectares, etc)',
    'vocabulary' => 'om_agman',
    'varunits' => 'multi',
    'varkey' => 'om_agman_svform',
    'datatype' => 'area',
    'varcode' => 'om_agman_svform',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => '',
    # Uses same plugin as full object
    'plugin' => 'dHAgmanSVSampleEvent',
    'options' => '',
    'varabbrev' => 'SV Form',
  ),
);

?>