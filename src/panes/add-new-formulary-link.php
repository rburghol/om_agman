<?php

$a = arg();
$auth = dh_adminreg_get_adminreg_entity('user-defined', 'authority');
$authid = array_shift($auth);
$l = l(
  t("Add a new spray material or formulation here."), 
  "ipm-chemical-registration-form/add",
  array('query' => array('dh_link_admin_reg_issuer' => $authid, 'finaldest'=>implode('/', $a)))
);
echo t("Can't find the material you need? ") . $l;
?>