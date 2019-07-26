/**
 * User: Sarath TS
 * Date: 20.06.2019
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

	/**
	 * In create page, hide shops when page loads. It shows only when select a company.
	 */
	$( "#key_company" ).change(function() {
		let companyId = $(this).val();

		if(companyId === '') { 
			$( "#divShop" ).hide(); 
		}

		$.ajax({
			url: "/admin/dashboard/key/get/shops/" + companyId,
			dataType: "JSON",
			type: "GET"
		})
		.done(function(data) {
			if(data.shopAvailableStatus === 'success') {

				$( "#divShop" ).show();

				if( data.shops.length > 0 ) {
					$( "#noShopsAlert" ).hide();
					$( "#shopSelectBoxDiv" ).show();
					$( ".key_shop_options" ).remove();

					let shopId = '';
					let shopName = '';
					for(let i = 0; i < data.shops.length; i++) {
						shopId = data.shops[i].id;
						shopName = data.shops[i].shop;
						$( "#shop_select" ).append('<option class="key_shop_options" value="'+ shopId +'">'+ shopName +'</option>');
					}
				}
				else {
					$( "#noShopsAlert" ).show();
					$( "#shopSelectBoxDiv" ).hide();
					$( "#noShopsAlert" ).html(
					'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> Not found any shops for this company<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				}

			}
		})
		.fail(function(data) {
			if(data.responseJSON.shopAvailableStatus === 'failure') {
				$( "#divShop" ).show();
				$( "#divShop" ).html(
					'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
					data.responseJSON.message +
					'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
			}
		});
	});

    /* Tooltip */
	$( ".createKeyInstruction" ).tooltip({
		container: 'body'
	});

	/* Multiple select for shops */
	if( $("#shop_select")[0] ) {
		$( "#shop_select" ).select2();
	}

	/* Select all shops by clicking checkbox */
	$( "#checkAllShops" ).on('click', function() {
		if( $( "#checkAllShops" ).is(':checked') ) {
			$( "#shop_select > option" ).prop( "selected", true);
			$( "#shop_select" ).trigger("change");
		} 
		else {
			$( "#shop_select > option" ).prop( "selected", false);
			$( "#shop_select" ).trigger("change");
		}
	});

	/* Datatable scripts */
	let keyList = $( "#key_list" ).DataTable({
		lengthMenu: [10, 25, 50, 75, 100],
		order: [1, "desc"],
		processing: true,
		serverSide: true,
		ajax: {
			url: "/admin/dashboard/key/list/datatables",
			dataType: "json",
			type: "POST"
		},
		deferRender: true,
		columns: [
			{ data: "hash" },
			{ data: "name" },
			{ data: "active" },
			{ data: "actions" }
		],
		columnDefs: [
			{
				orderable: false,
				targets: [0, 2, 3]
			}
		],
		language: {
			sEmptyTable: "Keine Daten in der Tabelle vorhanden",
			sInfo: "_START_ bis _END_ von _TOTAL_ Einträgen",
			sInfoEmpty: "0 bis 0 von 0 Einträgen",
			sInfoFiltered: "(gefiltert von _MAX_ Einträgen)",
			sInfoPostFix: "",
			sInfoThousands: ".",
			sLengthMenu: "_MENU_ Einträge anzeigen",
			sLoadingRecords: "Wird geladen...",
			sProcessing: "Bitte warten...",
			sSearch: "Suchen",
			sZeroRecords: "Keine Einträge vorhanden.",
			oPaginate: {
				sFirst: "Erste",
				sPrevious: "Zurück",
				sNext: "Nächste",
				sLast: "Letzte"
			},
			oAria: {
				sSortAscending:
					": aktivieren, um Spalte aufsteigend zu sortieren",
				sSortDescending:
					": aktivieren, um Spalte absteigend zu sortieren"
			}
		}
	});

	/* Delete key functionality */
	$( "#key_list tbody" ).on("click", "a.deleteEvent", function(e) {
		e.preventDefault();
		var keyId = $(this).data( "id" );
		var r = confirm("Are you sure you want to remove the key?");
		if (r == true) {
			$.ajax({
				url: "/admin/dashboard/key/delete/" + keyId,
				dataType: "JSON",
				type: "DELETE"
			})
			.done(function(result) {
				if (result.deletedKeyStatus === "success") {
						$( "#editKeyModal_" + keyId).modal("hide"); // It hides the modal

						keyList
						.row($(this).parents("tr"))
						.remove()
						.draw();

						$( ".responseKeyMessage" ).html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
							result.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
							);

						$( ".responseKeyMessage" )
						.show()
						.delay(5000)
						.fadeOut();
					}
				})
			.fail(function(data) {
				if (data.responseJSON.deletedKeyStatus === "failure") {
					$( ".keyUpdateValidationAlert" ).html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
						data.responseJSON.message +
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
				}
			});
		}
	});

	/* <tfoot> search functionality */
	$( ".search-input" ).on("keyup change", function() {
		var i = $(this).attr("id"); // getting column index
		var v = $(this).val(); // getting search input value
		keyList
			.columns(i)
			.search(v)
			.draw();
	});

	/* Updating key status */
	$( "#key_list tbody" ).on("change", "input.buttonStatus", function(e) {
		e.preventDefault();

		var newStatus = "";

		var keycontid = $(this)
			.parent()
			.data( "keycontid" );

		if ($(this).is( ":checked" ) === true) {
			newStatus = "yes";
		} else {
			newStatus = "no";
		}

		$.ajax({
			url: "/admin/dashboard/key/status/update",
			dataType: "JSON",
			type: "POST",
			data: { newStatus: newStatus, keycontid: keycontid }
		})
			.done(function(result) {
				keyList.ajax.reload(null, false);
			})
			.fail(function(data) {
				keyList.ajax.reload(null, false);

				if (data.responseJSON.keyContainerStatusChange === "failure") {
					$( ".responseKeyMessage" ).html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
							data.responseJSON.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				}

				$( ".responseKeyMessage" )
					.show()
					.delay(5000)
					.fadeOut();
			});
	});

	/* Create key */
	$( "button.createKey" ).on( "click", function(e) {
		e.preventDefault();

		let key_name     	      = $( "#key_name" ).val();
		let key_type 	 	      = $( "#key_type" ).val();
		let key_company  	  	  = $( "#key_company" ).val();
		let key_shops 		 	  = $( "#shop_select" ).val();
		let key_activation_number = $( "#activation_number" ).val();
		let replaceSpace 	      = $( "#keys" ).val().replace(/\s/g, ",").split(',');
		let keys      	 	      = [];

		// Convert textareas string value to javascript array separated by new lines.
		for( let i = 0; i < replaceSpace.length; i++ ) {
			if( replaceSpace[i] ) {
				keys.push(replaceSpace[i]);
			}
		}

		$.ajax({
			url: "/admin/dashboard/key/store",
			dataType: "JSON",
			type: "POST",
			data: {
				key_name: key_name,
				key_type: key_type,
				key_company: key_company,
				key_shops: key_shops,
				keys: keys,
				key_activation_number: key_activation_number
			}
		})
		.done(function(result) {
			if (result.keyStatus === "success") {
					$( "#createKeyModal" ).modal( "hide" ); // It hides the modal

					keyList.ajax.reload(null, false);

					$( ".responseKeyMessage" ).html(
						'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
						result.message +
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

					$( ".responseKeyMessage" )
					.show()
					.delay(5000)
					.fadeOut();
				}
		})
		.fail(function(data) {
			if (data.responseJSON.keyStatus === "failure") {
				$( ".keyValidationAlert" ).html(
					'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
					data.responseJSON.message +
					'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
			}

			if (data.status === 422) {
				$.each(data.responseJSON.errors, function(key, val) {
					$( ".keyValidationAlert" ).html(
						"<p class='alert alert-danger'>" + val + "</p>"
						);
				});
			}
		});
	});

	/* Clearing data of create manager modal fields */
	$( "#createKeyModal" ).on( "hidden.bs.modal", function(e) {

		// On model close, it will hide alert messages and multiselect. Reason is, it shows default when model opens.
		$( "p .alert, .alert-danger" ).hide();
		$( ".select2-selection__choice" ).hide();

		$(this)
			.find("input,textarea,select")
			.val("")
			.end()
			.find("input[type=checkbox], input[type=radio]")
			.prop("checked", "")
			.end();
	});

	/* Edit manager */
	$( "#key_list tbody" ).on("click", "a.editKey", function(e) {
		e.preventDefault();
		
		let keyContainerId 			= $(this).data( "keycontainerid" );
		let keyContainerCompanyId 	= $(this).data( "keycontainercompanyid" );

		// Multiple select for shops
		if( $( ".shop_edits_"+keyContainerId )[0] ) {
			$( ".shop_edits_"+keyContainerId ).select2();
		}

        // In edit page, select shops while changing company
		$( "#key_company_name_"+keyContainerId ).change(function() {
		    
		    let companyEditId = $(this).val();

			$.ajax({
				url: "/admin/dashboard/key/get/shops/" + companyEditId,
				dataType: "JSON",
				type: "GET"
			})
			.done(function(data) {
				if(data.shopAvailableStatus === 'success') {
					$( ".div_shop_edit_"+keyContainerId ).show();

					$( ".no_shop_alert_"+keyContainerId ).hide();

					$( ".first_option_shop_edit_"+keyContainerId ).remove();
			
			        $( ".shop_edits_"+keyContainerId ).html( '<option class="second_option_shop_edit_'+keyContainerId+'" value="" disabled="disabled">Choose Shop</option>' );

					if( data.shops.length > 0 ) {
						$( ".second_option_shop_result_"+keyContainerId ).remove();

						let shopEditId = '';
						let shopEditName = '';

						for(let i = 0; i < data.shops.length; i++) {
							shopEditId = data.shops[i].id;
							shopEditName = data.shops[i].shop;
							$( ".second_option_shop_edit_"+keyContainerId ).after('<option class="second_option_shop_result_'+keyContainerId+'" value="'+ shopEditId +'">'+ shopEditName +'</option>');
						}
					}
					else {
						$( ".no_shop_alert_"+keyContainerId ).show();
						$( ".no_shop_alert_"+keyContainerId ).html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> Not found any shops for this company <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
							);
						$( ".div_shop_edit_"+keyContainerId ).hide();
					}

				}
			})
			.fail(function(data) {
				if(data.responseJSON.shopAvailableStatus === 'failure') {
					$( ".no_shop_alert_"+keyContainerId ).show();
					$( ".no_shop_alert_"+keyContainerId ).html(
					'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
					data.responseJSON.message +
					'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
					$( ".div_shop_edit_"+keyContainerId ).hide();
				}
			});
		});
		
		// Update keys and cointainers 
		$( ".updateKeyContainer_" + keyContainerId).on("click", function(e) {
			e.preventDefault();

			let key_name 			  = $( "#key_name_edit_"+keyContainerId).val();
			let key_company 		  = $( "#key_company_name_"+keyContainerId).val();
			let key_shop  			  = $( "#shop_edits_"+keyContainerId).val();
			let key_activation_number = $( "#activation_number_edit_"+keyContainerId).val();
			let keys_edit_replace 	  = $( "#keys_edit_"+keyContainerId).val().replace(/\s/g, ",").split(',');
			let keys_edit 		      = [];

			// Convert textareas string value to javascript array separated by new lines.
			for( let i = 0; i < keys_edit_replace.length; i++ ) {
				if( keys_edit_replace[i] ) {
					keys_edit.push(keys_edit_replace[i]);
				}
			}

			$.ajax({
				url: "/admin/dashboard/key/update",
				dataType: "JSON",
				type: "PUT",
				data: {
					key_name: key_name,
					key_company: key_company,
					key_shop: key_shop,
					key_activation_number: key_activation_number,
					keys: keys_edit,
					key_container_id: keyContainerId
				}
			})
			.done(function(result) {
				if (result.keyUpdatedStatus === "success") {
						$( "#editKeyModal_"+keyContainerId).modal("hide"); // It hides the modal

						keyList.ajax.reload(null, false); //Reload data on table

						$( ".responseKeyMessage" ).html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
							result.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
							);

						$( ".responseKeyMessage" )
						.show()
						.delay(5000)
						.fadeOut();
					}
			})
			.fail(function(data) {
				if (data.responseJSON.keyUpdatedStatus === "failure") {
					$( ".keyUpdateValidationAlert_"+keyContainerId ).html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
						data.responseJSON.message +
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
				}

				if (data.status === 422) {
					$.each(data.responseJSON.errors, function(key, val) {
						$( ".keyUpdateValidationAlert_"+keyContainerId ).html(
							"<p class='alert alert-danger'>" + val + "</p>"
							);
					});
				}
			});
		});
	});

    /* Create Key Instructions */
	$( "#key_list tbody" ).on("click", "a.createKeyInstruction", function(e) {
		e.preventDefault();
		
		let keyInstructionContainerId = $(this).data( "keyinstructioncontainerid" );

		/* Clearing data of create manager modal fields */
		$("#keyInstructionModal_" + keyInstructionContainerId).on( "hidden.bs.modal", function(e) {

		// On model close, it will hide alert messages. Reason is, it shows default when model opens.
		$( "p .alert, .alert-danger" ).hide();

		$(this)
		.find("input,textarea,select")
		.val("")
		.end()
		.find("input[type=checkbox], input[type=radio]")
		.prop("checked", "")
		.end();
	    });

		$( ".createKeyInstruction_" + keyInstructionContainerId).on("click", function(e) {
			e.preventDefault();

			let formData = new FormData();
			formData.append('key_instruction_file', $( "#key_instruction_file_"+keyInstructionContainerId)[0].files[0]);
			formData.append('key_instruction_container_id', keyInstructionContainerId);
			formData.append('key_instruction_language', $( "#key_instruction_language_"+keyInstructionContainerId ).val());

			$.ajax({
				url: "/admin/dashboard/key/instruction/store",
				dataType: "JSON",
				type: "POST",
				contentType: false, // It is the type of data you're sending, so application/json; charset=utf-8 is a common one, as is application/x-www-form-urlencoded; charset=UTF-8, which is the default. 
				processData: false,
				data: formData
			})
			.done(function(result) {
				if(result.keyInstructionStatus === 'success') {
					$("#keyInstructionModal_" + keyInstructionContainerId).modal("hide"); // It hides the modal
					keyList.ajax.reload(null, false); //Reload data on table

					$(".responseKeyMessage").html(
						'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
						result.message +
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

					$(".responseKeyMessage")
					.show()
					.delay(5000)
					.fadeOut();	
				}
			})
			.fail(function(data) {
				if (data.responseJSON.keyInstructionStatus === "failure") {
					$(".keyInstructionValidationAlert").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
						data.responseJSON.message +
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
				}

				if (data.status === 422) {
					$.each(data.responseJSON.errors, function(key, val) {
						$(".keyInstructionValidationAlert").html(
							"<p class='alert alert-danger'>" + val + "</p>"
							);
					});
				}
			});
		});
	});

    //Download key instructions
    $( "#key_list tbody, div.keyinstructiondata" ).on("click", "a.downloadKeyInstruction", function(e) {
		e.preventDefault();

		let keyInstructionId = $(this).data( "keyinstructionid" );
		let keyInstructionUrl = $(this).data( "keyinstructionurl" );

		$.ajax({
			url: "/admin/dashboard/key/instruction/download/file",
			dataType: "JSON",
			type: "POST",
			data: {keyInstructionId: keyInstructionId, keyInstructionUrl: keyInstructionUrl}
		})
		.done(function(result) {
		})
		.fail(function(data) {
		});
		
	});
});
