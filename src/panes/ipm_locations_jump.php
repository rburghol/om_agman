<?php
$a = arg();
if (!isset($a[1])) {
  $vineyard_id = isset($_SESSION['om_agman']['facility']) ? $_SESSION['om_agman']['facility'] : 'all';
} else {
  $vineyard_id = $a[1];
}
if (!isset($a[2])) {
  $block_id = isset($_SESSION['om_agman']['landunit']) ? $_SESSION['om_agman']['landunit'] : 'all';
} else {
  $block_id = $a[2];
}
$args = array();
$args[] = $vineyard_id;
$args[] = $block_id;
// save the current values for the session
$_SESSION['om_agman']['facility'] = $vineyard_id;
$_SESSION['om_agman']['landunit'] = $block_id;
$view = views_get_view('list_user_vineyards_blocks'); 
$view->set_display('page_3'); 
//$view->set_arguments($a);
$view->pre_execute($args );
$view->execute();
//$content = $view->render(); 
//echo $content;
$q = explode('/',$_GET['q']);;
$page_name = array_shift($q);

// this replicates the code for  views_plugin_style_jump_menu::render()
// found in/ views/plugins/views_plugin_style_jump_menu.inc
$sets = $view->style_plugin->render_grouping($view->style_plugin->view->result, $view->style_plugin->options['grouping']);

// Turn this all into an $options array for the jump menu.
$view->style_plugin->view->row_index = 0;
$options = array();
$paths = array();
foreach ($sets as $title => $records) {
  foreach ($records as $row_index => $row) {
    $view->style_plugin->view->row_index = $row_index;
    $path = strip_tags(decode_entities($view->style_plugin->get_field($view->style_plugin->view->row_index, $view->style_plugin->options['path'])));
    // **************************************************
    $path = str_replace('url-placeholder', $a[0], $path);
    // Putting a '/' in front messes up url() so let's take that out
    // so users don't shoot themselves in the foot.
    $base_path = base_path();
    if (strpos($path, $base_path) === 0) {
      $path = drupal_substr($path, drupal_strlen($base_path));
    }

    // use drupal_parse_url() to preserve query and fragment in case the user
    // wants to do fun tricks.
    $url_options = drupal_parse_url($path);

    $path = url($url_options['path'], $url_options);
    $field = strip_tags(decode_entities($view->style_plugin->row_plugin->render($row)));
    $key = md5($path . $field) . "::" . $path;
    if ($title) {
      $options[$title][$key] = $field;
    }
    else {
      $options[$key] = $field;
    }
    $paths[$path] = $key;
    $view->style_plugin->view->row_index++;
  }
}
unset($view->style_plugin->view->row_index);
$default_value = '';
$default_value = '';
if ($view->style_plugin->options['default_value']) {
  $lookup_options = array();
  // We need to check if the path is absolute
  // or else language is not taken in account.
  if ($view->style_plugin->view->display[$view->style_plugin->view->current_display]->display_options['fields'][$view->style_plugin->options['path']]['absolute']) {
    $lookup_options['absolute'] = TRUE;
  }
  // detect if default values have been selected and we have enabled detect_views_arg_override
  $view->style_plugin->options['views_argument_override'] = TRUE;
  if ($view->style_plugin->options['views_argument_override']) {
    $pieces = array();
    //dsm('over-riding args');
    $pieces[] = $page_name;
    $pieces = array_merge($pieces, $view->style_plugin->view->args);
    $pieces = implode('/', $pieces);
  } else {
    $pieces = $_GET['q'];
  }
  $lookup_url = url($pieces, $lookup_options);
  if (!empty($paths[$lookup_url])) {
    $default_value = $paths[$lookup_url];
  }
}
$base_path = base_path();
$url_options = drupal_parse_url($showall);
$showall = url($url_options['path'], $url_options);
// note: base_path() returns a trailing slash, so we join it separate from the arguments
$showall = $base_path . implode('/', array($page_name, 'all', 'all'));
$key = md5($showall) . "::" . $showall;
$options[$key] = '** Show All Locations';
    
asort($options);
//dpm($options);
ctools_include('jump-menu');
$settings = array(
  'hide' => $view->style_plugin->options['hide'],
  'button' => $view->style_plugin->options['text'],
  'title' => $view->style_plugin->options['label'],
  'choose' => $view->style_plugin->options['choose'],
  'inline' => $view->style_plugin->options['inline'],
  'default_value' => $default_value,
);

$form = drupal_get_form('ctools_jump_menu', $options, $settings);
//dpm($form);
$content = drupal_render($form);
echo $content;
?>