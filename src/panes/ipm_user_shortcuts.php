<?php
global $user;
$a = arg();
//error_log("User id: " . $user->uid);
$uid = $user->uid;
$pg = 'ipm-edit-vineyard&dh_link_feature_mgr_id=';
$url =  base_path() . "?q=" . $pg . $uid;
$ytube_channel = l("GrapeIPM.org Youtube", "https://www.youtube.com/channel/UC3HaAskPBOb9teuR0Y8MnKA");
$ytube_1st = l("Initial Setup", "https://www.youtube.com/watch?v=VeXiFqRPQNA");
$ytube_batch = l("Managing Blocks", 'https://www.youtube.com/watch?v=LL6pURlq184');
$ytube_ical = l("Smartphone Calendar Sync", "https://www.youtube.com/watch?v=C3TxYTPykRM");
$ytube_gcal = l("Google Calendar Sync", "https://youtu.be/f6DXM8FbOPk");
$ytube_frost = l("Frost and Insect Damage Events", "https://youtu.be/nrqPqfrC6cw");
$ipm_guide = l("2019 Pest Management Guide - Grapes: Diseases and Insects in Vineyards", "https://www.pubs.ext.vt.edu/content/dam/pubs_ext_vt_edu/456/456-017/ENTO-337C.pdf");
$blog = l("Grape disease management information (Mizuho's blog, including a link to the latest PMG)", "http://grapepathology.blogspot.com/");

echo "<ul>";
echo "<li><a href='$url'>Click here to Add a Vineyard</a>";
echo "<li>How-to ( $ytube_channel)<ul>";
echo "<li>" . $ytube_1st . "</li>";
echo "<li>" . $ytube_batch . "</li>";
echo "<li>" . $ytube_ical . "</li>";
echo "<li>" . $ytube_gcal . "</li>";
echo "<li>" . $ytube_frost . "</li>";
echo "</ul></li>";
echo "<li>" . $ipm_guide . "</li>";
echo "<li>" . $blog . "</li>";
echo "</ul>";

?>
