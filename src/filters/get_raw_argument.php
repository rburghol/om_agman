<?php
$a = arg(); // we want raw values
dpm($a);
$argpos = 1;
if ($a[0] == 'admin') {
  // we are in views preview and need to adjust appropriately to get the right path pos
  $argpos += 6;
}
//dpm($a[$argpos],"Argpos $argpos");
return $a[$argpos];


?>