// Work in progress - goal is to do all auto-calcs

function om_agman_rate_total(span_field_index) {
	console.log(event.target.id)
	rfid = event.target.id // in form of edit-chem-rates-N-rate-propvalue
    rf_pieces = rfid.split('-')
	rfix = rf_pieces[3]
	rate = document.getElementById(rfid).value
	unitconv_id = 'chem_rates[' + rfix + '][unitconv]'
	console.log(unitconv_id)
	area_id = "edit-event-settings-4-propvalue"
	area_acres = document.getElementById(area_id).value
	unit_conv = document.getElementById(unitconv_id).value
	total_amount = rate
	console.log(total_amount)
	console.log(unit_conv)
	//document.getElementById(span_field_index).innerHTML = total_amount;
}
