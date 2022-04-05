/**
 * File : serverAddEdit.js
 * 
 * This file contain the validation of add server form
 * 
 * Using validation plugin : jquery.validate.js
 * 
 */

$(document).ready(function(){
	
	var addServerForm = $("#addServer");
	var validator = addServerForm.validate({
		
		rules:{
			server_ip : { required : true },
			server_provider : { required : true },
			maximum_thread : { required : true, digits : true },
			end_point : { required : true }
		},
		messages:{
			server_ip : { required : "This field is required" },
			server_provider : { required : "This field is required" },
			maximum_thread : { required : "This field is required", digits : "Please enter numbers only"},
			end_point : { required : "This field is required" }
		}
    });
    
    var editServerForm = $("#editServer");
	var validator = editServerForm.validate({
		
		rules:{
			server_ip : { required : true },
			server_provider : { required : true },
			maximum_thread : { required : true, digits : true },
			end_point : { required : true }
		},
		messages:{
			server_ip : { required : "This field is required" },
			server_provider : { required : "This field is required" },
			maximum_thread : { required : "This field is required", digits : "Please enter numbers only"},
			end_point : { required : "This field is required" }
		}
    });
});
