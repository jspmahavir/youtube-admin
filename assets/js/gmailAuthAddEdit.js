/**
 * File : gmailAuthAddEdit.js
 * 
 * This file contain the validation of add edit gmail auth form
 * 
 * Using validation plugin : jquery.validate.js
 * 
 */

$(document).ready(function(){
	
	var authGmailForm = $("#authGmail");
	
	var validator = authGmailForm.validate({
		
		rules:{
            app : { required : true, selected : true},
            user : { required : true, selected : true}
		},
		messages:{
            app : { required : "This field is required", selected : "Please select atleast one option" },
            user : { required : "This field is required", selected : "Please select atleast one option" }
		}
	});
});
