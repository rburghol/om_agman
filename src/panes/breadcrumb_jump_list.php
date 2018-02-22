<?php

function jump_link($results, $zindex, $selected_id, $title, $page) {
  $selected = false;
  //$p = "<div class=\"flyingjumplist\" style=\"z-index: $zindex\"><ul>";
  $p = "<div width=120px class=\"breadcrumbmenu\" style=\"z-index: $zindex\"><ul>";
  $links = array();
  //dpm($results);
  $all_args = property_exists($results[0], 'dh_link_facility_mps_dh_feature_hydroid') ? "/" . $results[0]->hydroid : ''; 
  $link = l('Show All', "$page$all_args", array());
  $links[] = "<li>$link</li>";
  foreach ($results as $rez) {
    $name = property_exists($rez, 'dh_link_facility_mps_dh_feature_name') ? $rez->dh_feature_name . ":" . $rez->dh_link_facility_mps_dh_feature_name : $rez->dh_feature_name;
    $id = property_exists($rez, 'dh_link_facility_mps_dh_feature_hydroid') ? $rez->dh_link_facility_mps_dh_feature_hydroid : $rez->hydroid; 
    $id_filter = property_exists($rez, 'dh_link_facility_mps_dh_feature_hydroid') ? $rez->hydroid . '/' .$rez->dh_link_facility_mps_dh_feature_hydroid : $rez->hydroid; 
    if ($id and ($id == $selected_id)) {
      $selected = $name;
    }
    $link = l($name, "$page/$id_filter", array());
    $links[] = "<li>$link</li>";
    //dpm("Comparing", "if ($id == $selected_id) ");
  }
  if ($selected) {
    $p .= "<li><a href=\"#\">$title: $selected</a>";
  } else {
    $p .= "<li><a href=\"#\">$title</a>";
  }
	$p .= "<ul>";
  $p .= implode('', $links);
	$p .= "</li></ul></div>";
  return $p;
}

function dh_agchem_jump_user_vineyards_css($args, $page) {
  $view = views_get_view('list_user_vineyards'); 
  $view->set_display('page_2'); 
  $view->set_exposed_input(array());
  $view->set_arguments(array());
  $view->set_offset(0);
  $view->pre_execute();
  $view->execute();
  //dpm($view->result, 'dh_agchem_jump_user_vineyards result');
  $content = jump_link($view->result, 4, $args[0], 'Vineyard', $page);
  return $content;
}

function dh_agchem_jump_user_vineyards($args) {
  $view = views_get_view('list_user_vineyards'); 
  $view->set_display('page_2'); 
  $view->set_exposed_input(array());
  $view->set_offset(0);
  //$view->set_arguments($args);
  $view->pre_execute();
  $view->execute();
  //dpm($view->result, 'dh_agchem_jump_user_vineyards result');
  $content = $view->render(); 
  return $content;
  
}

function dh_agchem_jump_user_blocks_css($args, $page) {
  $view = views_get_view('list_user_vineyards_blocks'); 
  $view->set_display('page'); 
  $view->set_arguments($args);
  $view->set_offset(0);
  $view->set_exposed_input(array());
  $view->pre_execute();
  $view->execute();
  //dpm($view->result, 'dh_agchem_jump_user_blocks result');
  $content = jump_link($view->result, 4, $args[1], 'Block', $page);
  return $content;
  
}

function dh_agchem_jump_user_blocks($args) {
  $view = views_get_view('list_user_vineyards_blocks'); 
  $view->set_display('page'); 
  $view->set_exposed_input(array());
  $view->set_arguments($args);
  $view->set_offset(0);
  $view->pre_execute();
  $view->execute();
  //dpm($view->result, 'dh_agchem_jump_user_blocks result');
  $content = $view->render(); 
  return $content;
  
}

$args = arg();
//$args = array(); // for testing
//dpm($args);
$page = array_shift($args);
$base = implode("/", $args);
$query = drupal_get_query_parameters();
$link = l("Bloom x", $base, array( 
  'attributes' => array(
    'onclick' => 'return confirm( "Are you sure you want to delete?" ); '
  ), 
  'query' => $query 
  ) 
);

//echo "<table ><tr align=left width=240><td align=left valign=top>" . dh_agchem_jump_user_vineyards($args) . '</td><td align=left valign=top>/</td><td align=left valign=top>' . dh_agchem_jump_user_blocks($args) . "</td></tr></table>";
$home = l("Home", "ipm-home");
echo "<table  width=240><tr align=left width=240><td align=left valign=top>" 
  . $home 
  . '</td><td align=left valign=top>/</td><td align=left valign=top>' 
  . dh_agchem_jump_user_vineyards_css($args, $page) 
  . '</td><td align=left valign=top>/</td><td align=left valign=top>' 
  . dh_agchem_jump_user_blocks_css($args, $page) . "</td></tr></table>";

if ($vineyard == 'all') {
  $block = 'all';
}

$uvq = '';
/*
<div class=\"centeredmenu\" style=\"z-index: $zindex\"><ul>
	<li><a href=\"#\">Actions</a>
	<ul>
		<li>$link</li>
		<li><a href=\"#\">Bud Break</a></li>
		<li><a href=\"#\">Bud Swell</a></li>
		<li>Crop Planted</li>
		<li>Fertilizer Applied</li>
		<li>Herbicide Applied</li>
		<li>Insecticide Applied</li>
		<li>Leaf Senescence</li>
		<li>Lime Application</li>
		<li>Median Shoot Length</li>
		<li>Pruning</li>
	</ul>
	</li>
</ul>
</div>

<hr />
<div class=\"centeredmenu\" style=\"z-index: 3\">
<ul>
	<li><a href=\"#\">Place 1</a>

	<ul>
		<li><a href=\"#\">Bloom</a></li>
		<li><a href=\"#\">Bud Break</a></li>
		<li><a href=\"#\">Bud Swell</a></li>
		<li><a href=\"#\">Crop Planted</a></li>
		<li>Fertilizer Applied</li>
		<li>Herbicide Applied</li>
		<li>Insecticide Applied</li>
		<li>Leaf Senescence</li>
		<li>Lime Application</li>
		<li>Median Shoot Length</li>
		<li>Pruning</li>
	</ul>
	</li>
	<li><a href=\"#\">Place 2</a>
	<ul>
		<li><a href=\"#\">Bloom</a></li>
		<li><a href=\"#\">Bud Break</a></li>
		<li><a href=\"#\">Bud Swell</a></li>
		<li><a href=\"#\">Crop Planted</a></li>
		<li>Fertilizer Applied</li>
		<li>Herbicide Applied</li>
		<li>Insecticide Applied</li>
		<li>Leaf Senescence</li>
		<li>Lime Application</li>
		<li>Median Shoot Length</li>
		<li>Pruning</li>
	</ul>
	</li>
</ul>
</div>

<div class=\"centeredmenu\" style=\"z-index: 2\">
<ul>
	<li><a href=\"#\">+</a>

	<ul>
		<li><a href=\"#\">Bloom</a></li>
		<li><a href=\"#\">Bud Break</a></li>
		<li><a href=\"#\">Bud Swell</a></li>
		<li><a href=\"#\">Crop Planted</a></li>
		<li>Fertilizer Applied</li>
		<li>Herbicide Applied</li>
		<li>Insecticide Applied</li>
		<li>Leaf Senescence</li>
		<li>Lime Application</li>
		<li>Median Shoot Length</li>
		<li>Pruning</li>
	</ul>
	</li>
</ul>
</div>
";

//echo $p;
*/

?>