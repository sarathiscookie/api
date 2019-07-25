/**
 * User: Sarath TS
 * Date: 24.07.2019
 * Created for: adminKeyList
 */

$(function() {
	"use strict";

	/* Checking for the CSRF token */
	$.ajaxSetup({
		headers: {
			"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
		}
	});


    $( "button.createKeyInstruction" ).on( "click", function(e) {

    	e.preventDefault();

    	
    });




});	