/**
 * File : accountAddEdit.js
 * 
 * This file contain the validation of add account form
 * 
 * Using validation plugin : jquery.validate.js
 * 
 */

$(document).ready(function(){
	
	var addAccountForm = $("#addAccount");
	
	var validator = addAccountForm.validate({
		
		rules:{
            email : { required : true, email : true, remote : { url : baseURL + "account/checkEmailExists", type :"post"} },
			password : { required : true },
            cpassword : {required : true, equalTo: "#password"},
            last_login_ip : { required : true },
		},
		messages:{
            email : { required : "This field is required", email : "Please enter valid email address", remote : "Email already taken" },
			password : { required : "This field is required" },
            cpassword : {required : "This field is required", equalTo: "Please enter same password" },
            last_login_ip : { required : "This field is required" },
		}
    });
    
    var editAccountForm = $("#editAccount");
	
	var validator = editAccountForm.validate({
		
		rules:{
            email : { required : true, email : true, remote : { url : baseURL + "account/checkEmailExists", type :"post", data : { accountId : function(){ return $("#accountId").val(); } } } },
            password : { required : true },
            cpassword : {required : true, equalTo: "#password"},
            last_login_ip : { required : true },
		},
		messages:{
            email : { required : "This field is required", email : "Please enter valid email address", remote : "Email already taken" },
			password : { required : "This field is required" },
            cpassword : { equalTo: "Please enter same password" },
            last_login_ip : { required : "This field is required" },
		}
	});
});
