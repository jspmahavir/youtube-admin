/**
 * File : clientAddEdit.js
 * 
 * This file contain the validation of add client form
 * 
 * Using validation plugin : jquery.validate.js
 * 
 */

$(document).ready(function(){
	
	var addClientForm = $("#addClient");
	
	var validator = addClientForm.validate({
		
		rules:{
			client_name : { required : true },
            api_key : { required : true},
            white_listed_ip : { required : true }
		},
		messages:{
			client_url : { required : "This field is required" },
            client_port : { required : "This field is required" },
            white_listed_ip : { required : "This field is required" }
		}
    });
    
    var editClientForm = $("#editClient");
	
	var validator = editClientForm.validate({
		
		rules:{
			client_name : { required : true },
            api_key : { required : true},
            white_listed_ip : { required : true }
		},
		messages:{
			client_url : { required : "This field is required" },
            client_port : { required : "This field is required" },
            white_listed_ip : { required : "This field is required" }
		}
	});
});
