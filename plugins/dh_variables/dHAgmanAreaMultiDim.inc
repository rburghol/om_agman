<?php

$plugin = array(
  'label' => t('AgMan - Feature Area'),
  'handler' =>  array(
    'class' => 'dHAgmanAreaMultiDim',
    'file' => 'dHVariablePluginAppRates.class.php',
  ),
  'variable' =>  array(
    'varname' => 'AgMan Feature Area, Multi-Dimensional',
    'vardesc' => 'Provides a property to describe the area of a land feature.  Units are selectable and function exists to convert from one unit to another (i.e. acres to hectares, etc)',
    'vocabulary' => 'om_agman',
    'varunits' => 'multi',
    'varkey' => 'om_agman_area',
    'datatype' => 'area',
    'varcode' => 'om_agman_area',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'value,code',
    # Uses same plugin as full object
    'plugin' => 'dHAgmanAreaMultiDim',
    'options' => '',
    'varabbrev' => 'Area',
  ),
);

?>