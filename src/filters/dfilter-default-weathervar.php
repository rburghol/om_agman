// addlink deletelink
$actions = array('addlink', 'deletelink');
if (in_array($argument, $actions)) {
  $handler->argument = 'weather_obs_daily_sum,weather_daily_dark_sum';
}
return TRUE;