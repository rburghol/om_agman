<?php
// no plugin defined here, only a variable definition 
$plugin = array(
  'label' => t('AgFruitChem - Malic Acid'),
  'variable' =>  array(
    'varname' => '',
    'vardesc' => 'A measure of the total malic acid concentration.',
    'vocabulary' => 'agman_fruit_chem',
    'varunits' => 'g/L',
    'varkey' => 'malic_acid_gpl',
    'datatype' => 'mass',
    'varcode' => 'malic_acid_gpl',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'value',
    'plugin' => 'dHVariablePluginNumericAttribute',
    'options' => '',
    'varabbrev' => 'ma',
  ),
);

?>