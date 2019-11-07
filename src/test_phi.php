<?php
  module_load_include('module', 'dh');
  module_load_include('inc', 'dh', 'plugins/dh.display');
  $ph = om_agman_get_block_phi(649, 'agchem_application_event', '2019-01-01', '2019-12-31', TRUE);
  error_log("PHI tid = $ph ");
?>
