<?php

$plugin = array(
  'label' => t('VDACS License #'),
  'handler' =>  array(
    'class' => 'dHVariablePluginAgchemLicensee',
    'file' => 'dHVariablePluginAgchem.class.php',
  ),
  'variable' =>  array(
    'varname' => 'VDCS License #',
    'vardesc' => 'Name and registration number of authorized pesticide applicator.',
    'vocabulary' => 'licensing',
    'varunits' => 'n/a',
    'varkey' => 'vdacs_pesticide_applicator',
    'datatype' => 'id',
    'varcode' => 'vdacs_pesticide_applicator',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'name,code',
    # Uses same plugin as full object
    'plugin' => 'dHVariablePluginAgchemLicensee',
    'options' => '',
    'varabbrev' => 'VDACS No.',
  ),
);

?>