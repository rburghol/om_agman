<?php
// no plugin defined here, only a variable definition 
$plugin = array(
  'label' => t('AgFruitChem - Total Sugar Load'),
  'variable' =>  array(
    'varname' => 'Total Sugar Load',
    'vardesc' => 'A measure of the total sugar per berry.',
    'vocabulary' => 'agman_fruit_chem',
    'varunits' => 'mg/berry',
    'varkey' => 'total_sugar_mgb',
    'datatype' => 'mass',
    'varcode' => 'total_sugar_mgb',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'value',
    'plugin' => 'dHVariablePluginNumericAttribute',
    'options' => '',
    'varabbrev' => 'tS',
  ),
);

?>