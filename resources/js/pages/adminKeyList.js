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
	 * Hide shops select box when page loads. It shows only when select a company.
	 */
	$( "#company" ).change(function() {
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
					$( ".options" ).remove();

					let shopId = '';
					let shopName = '';
					for(let i = 0; i < data.shops.length; i++) {
						shopId = data.shops[i].id;
						shopName = data.shops[i].shop;
						$( "#optionChoose" ).after('<option class="options" value="'+ shopId +'">'+ shopName +'</option>');
					}
				}
				else {
					$( "#noShopsAlert" ).show();
					$( "#shopSelectBoxDiv" ).hide();
					$( "#noShopsAlert" ).html(
					'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> No found any shops for this company<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
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

	/* Tag Handler for keys */
	$( "#keyTagHandler" ).tagHandler();

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

	/* Bottom buttons for datatables */
	let buttons;

	try {
		buttons = new $.fn.dataTable.Buttons(datatableList, {
			buttons: [
				{
					extend: "csv",
					exportOptions: {
						columns: [1, 2]
					}
				},
				{
					extend: "excel",
					exportOptions: {
						columns: [1, 2]
					}
				},
				{
					extend: "pdf",
					orientation: "portrait",
					pageSize: "LEGAL",
					exportOptions: {
						columns: [1, 2]
					}
				}
			]
		})
			.container()
			.appendTo($("#buttons"));
	} catch (error) {
		buttons = null;
	}

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
		datatableList
			.columns(i)
			.search(v)
			.draw();
	});

	/* Updating key status */
	$( "#key_list tbody" ).on("change", "input.buttonStatus", function(e) {
		e.preventDefault();

		var newStatus = "";

		var keyId = $(this)
			.parent()
			.data( "keyid" );

		if ($(this).is( ":checked" ) === true) {
			newStatus = "yes";
		} else {
			newStatus = "no";
		}

		$.ajax({
			url: "/admin/dashboard/key/status/update",
			dataType: "JSON",
			type: "POST",
			data: { newStatus: newStatus, keyId: keyId }
		})
			.done(function(result) {
				keyList.ajax.reload(null, false);
			})
			.fail(function(data) {
				keyList.ajax.reload(null, false);

				if (data.responseJSON.keyStatusChange === "failure") {
					$( ".responseMessage" ).html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
							data.responseJSON.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				}

				$( ".responseMessage" )
					.show()
					.delay(5000)
					.fadeOut();
			});
	});

	/* Create key */
	$( "button.createKey" ).on( "click", function(e) {
		e.preventDefault();

		var key_name    = $( "#key_name" ).val();
		var key_type 	= $( "#key_type" ).val();
		var company 	= $( "#company" ).val();
		var shop 		= $( "#shop" ).val();
		var keys      	= [];

		// Keys are in ul li. Get all keys from li and pushed in to array.
		$( ".tagItem" ).each(function() {
			keys.push( $(this).text() );
		});

		var act_number 	= $( "#activation_number" ).val();
		var count 		= $( "#count" ).val();

		$.ajax({
			url: "/admin/dashboard/key/store",
			dataType: "JSON",
			type: "POST",
			data: {
				key_name: key_name,
				key_type: key_type,
				company: company,
				shop: shop,
				keys: keys,
				act_number: act_number,
				count: count
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
		var managerid = $(this).data("keyid");

		$( ".updateManager_" + managerid).on("click", function(e) {
			e.preventDefault();
			var name = $( "#name_" + managerid).val();
			var phone = $( "#phone_" + managerid).val();
			var company = $( "#company_" + managerid).val();
			var street = $( "#street_" + managerid).val();
			var city = $( "#city_" + managerid).val();
			var country = $( "#country_" + managerid).val();
			var zip = $( "#zip_" + managerid).val();

			$.ajax({
				url: "/admin/dashboard/manager/update",
				dataType: "JSON",
				type: "PUT",
				data: {
					name: name,
					phone: phone,
					street: street,
					city: city,
					country: country,
					company: company,
					zip: zip,
					managerid: managerid
				}
			})
				.done(function(result) {
					if (result.managerStatusUpdate === "success") {
						$( "#editManagerModal_" + managerid).modal("hide"); // It hides the modal

						datatableList.ajax.reload(null, false); //Reload data on table

						$( ".responseMessage" ).html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						$( ".responseMessage" )
							.show()
							.delay(5000)
							.fadeOut();
					}
				})
				.fail(function(data) {
					if (data.responseJSON.managerStatusUpdate === "failure") {
						$( ".managerUpdateValidationAlert" ).html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
								data.responseJSON.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
					}

					if (data.status === 422) {
						$.each(data.responseJSON.errors, function(key, val) {
							$( ".managerUpdateValidationAlert" ).html(
								"<p class='alert alert-danger'>" + val + "</p>"
							);
						});
					}
				});
		});
	});
});
