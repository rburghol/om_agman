<?php
// no plugin defined here, only a variable definition 
$plugin = array(
  'label' => t('AgFruitChem - Cluster Weight'),
  'variable' =>  array(
    'varname' => 'Cluster Weight',
    'vardesc' => 'Mean weight of clusters sampled.',
    'vocabulary' => 'agman_fruit_chem',
    'varunits' => 'g',
    'varkey' => 'cluster_weight_g',
    'datatype' => 'mass',
    'varcode' => 'cluster_weight_g',
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