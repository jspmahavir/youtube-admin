/**
 * File : appAddEdit.js
 * 
 * This file contain the validation of add app form
 * 
 * Using validation plugin : jquery.validate.js
 * 
 */

$(document).ready(function(){
	
	var addAppForm = $("#addApp");
	
	var validator = addAppForm.validate({
		
		rules:{
            app_name : { required : true },
            email : { required : true, email : true, remote : { url : baseURL + "app/checkEmailExists", type :"post"} },
			password : { required : true },
            cpassword : {required : true, equalTo: "#password"},
            client_json : { required : true },
		},
		messages:{
            app_name : { required : "This field is required" },
            email : { required : "This field is required", email : "Please enter valid email address", remote : "Email already taken" },
			password : { required : "This field is required" },
            cpassword : {required : "This field is required", equalTo: "Please enter same password" },
            client_json : { required : "This field is required" },
		}
    });
    
    var editAppForm = $("#editApp");
	
	var validator = editAppForm.validate({
		
		rules:{
            app_name : { required : true },
            email : { required : true, email : true, remote : { url : baseURL + "app/checkEmailExists", type :"post", data : { appId : function(){ return $("#appId").val(); } } } },
			password : { required : true },
            cpassword : {required : true, equalTo: "#password"},
            client_json : { required : true },
		},
		messages:{
            app_name : { required : "This field is required" },
            email : { required : "This field is required", email : "Please enter valid email address", remote : "Email already taken" },
			password : { required : "This field is required" },
            cpassword : {required : "This field is required", equalTo: "Please enter same password" },
            client_json : { required : "This field is required" },
		}
	});
});
