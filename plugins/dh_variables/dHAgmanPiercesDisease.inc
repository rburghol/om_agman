<?php

$plugin = array(
  'variable' =>  array(
    'varname' => 'Pierces Disease',
    'vardesc' => 'Pierces Disease.',
    'vocabulary' => 'fungal_pathogens',
    'varunits' => 'n/a',
    'varkey' => 'org_pierces_disease',
    'datatype' => 'organism',
    'varcode' => 'org_pierces_disease',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'code',
    // Uses same plugin as other pathogens
    'plugin' => 'dHVariablePluginEfficacy',
    'options' => '',
    'varabbrev' => 'PD',
  ),
);

?>