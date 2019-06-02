<?php

$plugin = array(
  'label' => t('IPM - Event Location Sharing'),
  'handler' =>  array(
    'class' => 'dHVariableOMInfoShare',
    'file' => 'dHVariablePluginAgmanVitis.class.php',
  ),
  'variable' =>  array(
    'varname' => 'IPM Event Location Sharing',
    'vardesc' => 'Provides a property to provide sharing settings.',
    'vocabulary' => 'om_agman',
    'varunits' => 'n/a',
    'varkey' => 'ipm_info_share',
    'datatype' => 'preference',
    'varcode' => 'ipm_info_share',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'code',
    # Uses same plugin as full object
    'plugin' => 'dHVariableOMInfoShare',
    'options' => '',
    'varabbrev' => 'Sharing',
  ),
);

?>