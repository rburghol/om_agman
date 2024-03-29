// Work in progress - goal is to do all auto-calcs
/*
function mymodule_form_alter(&$form, &$form_state, $form_id) {
  //dpm($form_id);
  if($form_id == 'equipment_node_form'){
	 $rate = variable_get('small_fixes_current_dollar_rate');
	 $form['live_result'] = array(
		'#type' => 'textfield',
		'#title' => t('Amount Purchased (USD)'),
		'#disabled' => TRUE,
	 );

	 drupal_add_js(drupal_get_path('module', 'mymodule') . '/small_fixes_live.js');
	 drupal_add_js(array('small_fixes' => array('currentrate' => $rate)), 'setting');
	 
  }
*/

(function ($) {
  Drupal.behaviors.om_agman = {
	attach: function () {
		document.getElementById("edit-live-result").defaultValue = document.getElementById('edit-field-amount-ngn-each-und-0-value').value; //this line sets default value for the field
		$('#edit-field-amount-ngn-each-und-0-value').keyup(function(e) {
			var currentRate = Drupal.settings.om_agman.currentrate;
		    var ngn = document.getElementById('edit-field-amount-ngn-each-und-0-value').value;
			
			 $("#edit-live-result").val(ngn/currentRate);
		});
	}
  };
})(jQuery);