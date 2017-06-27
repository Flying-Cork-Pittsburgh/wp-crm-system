jQuery(document).ready(function($) {
	$("#post").submit(function(e) {
		update_title();
		set_submission_date();
	});
	function update_title() {
		var customerName = jQuery("#customer-name").val();
		var postTitle = jQuery('#post-title');
		postTitle.val( customerName );
	}
	function set_submission_date() {
		var dateField = $("#post-date");
		dateField.val( get_date() );
		return dateField.val();
	}
	function get_date() {
		var today = new Date();
		var date = today.getFullYear() + '-' + (today.getMonth() ) + '-' + today.getDate();
		var time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();
		var dateTime = date + ' ' + time;
		return dateTime;
	}
});