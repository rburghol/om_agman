// Work in progress - goal is to do all auto-calcs

function om_agman_rate_total(span_field_index) {
	console.log(event.target.id)
	rfid = event.target.id // in form of edit-chem-rates-N-rate-propvalue
	rf_pieces = rfid.split('-')
	rfix = rf_pieces[3]
	tfid = "batch-total-" + rfix
	console.log(tfid)
	document.getElementById(span_field_index).innerHTML = document.getElementById(rfid).value;
}
