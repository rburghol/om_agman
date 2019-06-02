<?php

$plugin = array(
  'label' => t('IPM - Part of Pest Management Guide Material Review (PMG)'),
  'handler' =>  array(
    'class' => 'dHVariableReviewedPMG',
    'file' => 'dHVariablePluginAgmanVitis.class.php',
  ),
  'variable' =>  array(
    'varname' => 'Part of Pest Management Guide Material Review (PMG)',
    'vardesc' => 'Reviewed and rated for efficacy by the Pest Management Guide.',
    'vocabulary' => 'om_agman',
    'varunits' => 'n/a',
    'varkey' => 'ipm_vt_pmg_material',
    'datatype' => 'preference',
    'varcode' => 'ipm_vt_pmg_material',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'code',
    # Uses same plugin as full object
    'plugin' => 'dHVariableReviewedPMG',
    'options' => '',
    'varabbrev' => 'Sharing',
  ),
);

?>