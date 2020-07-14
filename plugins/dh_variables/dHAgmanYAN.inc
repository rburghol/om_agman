<?php
// no plugin defined here, only a variable definition 
$plugin = array(
  'label' => t('AgFruitChem - Yeast Assimilable Nitrogen (YAN)'),
  'variable' =>  array(
    'varname' => '',
    'vardesc' => 'Yeast Assimilable Nitrogen (YAN).',
    'vocabulary' => 'agman_fruit_chem',
    'varunits' => 'mg/L',
    'varkey' => 'yan',
    'datatype' => 'concentration',
    'varcode' => 'yan',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'value',
    'plugin' => 'dHVariablePluginNumericAttribute',
    'options' => '',
    'varabbrev' => 'yan',
  ),
);

?>