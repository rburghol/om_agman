<?php

$plugin = array(
  'variable' =>  array(
    'varname' => 'GLRV',
    'vardesc' => 'Grapevine Leaf Roll Virus.',
    'vocabulary' => 'fungal_pathogens',
    'varunits' => 'n/a',
    'varkey' => 'org_glrv',
    'datatype' => 'organism',
    'varcode' => 'org_glrv',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'code',
    // Uses same plugin as other pathogens
    'plugin' => 'dHVariablePluginEfficacy',
    'options' => '',
    'varabbrev' => 'GLRV',
  ),
);

?>