<?php

module_load_include('module', 'om_agman');

function om_token_replace_all(&$replacements, $data, $options) {
  $replacements = $data;
}

function om_tokenize($base, $indata, &$outdata, $delim = ':', $allowed = array(), $enc = array('[', ']')) {
  if (!is_array($indata)) {
    return $indata;
  }
  foreach ($indata as $key => $value) {
    $newbase = empty($base) ? $key : $base . $delim . $key;
    if (is_array($value) ) {
      $toke = om_tokenize($newbase, $value, $outdata, $delim, $allowed);
    } else {
      // we reached the end
      if (!empty($allowed )) {
        // we only allow some specific ending props
        if (in_array($key, $allowed )) {
          $outdata[$enc[0] . "$newbase" . $enc[1]] = $value;
        }
      } else {
        $outdata[$enc[0] . "$newbase" . $enc[1]] = $value;
      }
    }
  }
}


$tid = 120456722;
$ts = entity_load_single('dh_timeseries', $tid);
$plugin = dh_variables_getPlugins($ts);
$plugin->loadProperties($ts);

dpm($ts,'ts');
$tsa = json_decode(json_encode($ts), true);
dpm( $tsa,'ts array');
$tout = array();
om_tokenize('', $tsa, $tout, ':', array('propcode', 'propname', 'pid', 'propvalue', 'entity_type', 'featureid'));
dpm($tout,'flattened');

// d8 version
// Token::replace($text, $data);

 $prop = array('propname' => 'Sharing', 'propcode' => '[Sharing:propcode]', 'black_rot' => '[leaf_black_rot:propvalue]');
dpm($prop,'before');

$tsb = array('[Sharing:propcode]' => $ts->Sharing->propcode);
$prop['propcode'] = token_replace($prop['propcode'], $tout, array('callback'=>'om_token_replace_all'));
$prop['black_rot'] = token_replace($prop['black_rot'], $tout, array('callback'=>'om_token_replace_all'));
dpm($prop,'after');


?>