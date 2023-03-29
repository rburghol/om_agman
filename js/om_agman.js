// Work in progress - goal is to do all auto-calcs

function om_agman_rate_total(span_field_index) {
	console.log(event.target.id)
	rfid = event.target.id // in form of edit-chem-rates-N-rate-propvalue
    rf_pieces = rfid.split('-')
	rfix = rf_pieces[3]
	rate = document.getElementById(rfid).value
	unitconv_id = 'unitconv-' + rfix
	batch_id = 'batch-vol-' + rfix
	total_id = 'total-vol-' + rfix
	console.log(unitconv_id)
	area_id = "edit-event-settings-4-propvalue"
	area_acres = document.getElementById(area_id).value
	unit_conv = document.getElementById(unitconv_id).value
	batch_vol = document.getElementById(batch_id).value
	total_vol = document.getElementById(total_id).value
	total_amount = rate * unitconv
	batch_amount = total_amount * batch_vol / total_vol;
    batch_amount = (batch_amount > 10) ? round(batch_amount,1) : round(batch_amount,2);
	console.log(rate)
	console.log(unit_conv)
	console.log(total_amount)
	console.log(batch_amount)
	//document.getElementById(span_field_index).innerHTML = total_amount;
}
