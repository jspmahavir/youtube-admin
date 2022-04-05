/**
 * File : commentAddEdit.js
 * 
 * This file contain the validation of add comment form
 * 
 * Using validation plugin : jquery.validate.js
 * 
 */

$(document).ready(function(){
	
	var addCommentForm = $("#addComment");
	
	var validator = addCommentForm.validate({
		
		rules:{
			comment : { required : true }
		},
		messages:{
			comment_url : { required : "This field is required" }
		}
    });
    
    var editCommentForm = $("#editComment");
	
	var validator = editCommentForm.validate({
		
		rules:{
			comment_name : { required : true }
		},
		messages:{
			comment_url : { required : "This field is required" }
		}
	});
});
