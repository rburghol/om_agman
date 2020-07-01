<?php

$plugin = array(
  'label' => t('OM AgMan - Grapevine Tissue Type'),
  'handler' =>  array(
    'class' => 'dHAgmanVitisPlantTissue',
    'file' => 'dHVariablePluginAgmanVitis.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Agman - Grape Tissue Type',
    'vardesc' => 'Type of plant tissue sampled.',
    'vocabulary' => 'om_agman',
    'varunits' => 'n/a',
    'varkey' => 'om_agman_plant_tissue',
    'datatype' => 'descriptive',
    'varcode' => 'om_agman_plant_tissue',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'code',
    # Uses same plugin as full object
    'plugin' => 'dHAgmanVitisPlantTissue',
    'options' => '',
    'varabbrev' => 'Tissue',
  ),
);

?>