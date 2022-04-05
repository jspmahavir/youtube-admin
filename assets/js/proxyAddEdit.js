/**
 * File : proxyAddEdit.js
 * 
 * This file contain the validation of add proxy form
 * 
 * Using validation plugin : jquery.validate.js
 * 
 */

$(document).ready(function(){
	
	var addProxyForm = $("#addProxy");
	
	var validator = addProxyForm.validate({
		
		rules:{
			proxy_url : { required : true },
            proxy_port : { required : true, digits : true },
            username : { required : true },
			password : { required : true },
			cpassword : {required : true, equalTo: "#password"}
		},
		messages:{
			proxy_url : { required : "This field is required" },
            proxy_port : { required : "This field is required", digits : "Please enter numbers only" },
            username : { required : "This field is required" },
			password : { required : "This field is required" },
			cpassword : {required : "This field is required", equalTo: "Please enter same password" }
		}
    });
    
    var editProxyForm = $("#editProxy");
	
	var validator = editProxyForm.validate({
		
		rules:{
			proxy_url : { required : true },
            proxy_port : { required : true, digits : true },
            username : { required : true },
			cpassword : { equalTo: "#password"}
		},
		messages:{
			proxy_url : { required : "This field is required" },
            proxy_port : { required : "This field is required", digits : "Please enter numbers only" },
            username : { required : "This field is required" },
			cpassword : { equalTo: "Please enter same password" }
		}
	});
});
