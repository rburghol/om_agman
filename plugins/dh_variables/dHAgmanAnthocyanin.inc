<?php
// no plugin defined here, only a variable definition 
$plugin = array(
  'label' => t('Total Anthocyanin'),
  'variable' =>  array(
    'varname' => 'Total Anthocyanin',
    'vardesc' => 'Total anthocyanin (520 nm absorbance; AU per g berry weight)',
    'vocabulary' => 'agman_fruit_chem',
    'varunits' => 'mgg',
    'varkey' => 'total_anthocyanin_mgg',
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