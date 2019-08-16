/**
 * User: Sarath TS
 * Date: 12.08.2019
 * Created for: adminSupplierList
 */

$(function() {
	"use strict";

	/* Checking for the CSRF token */
	$.ajaxSetup({
		headers: {
			"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
		}
	});

	/* Select multiple companies */
	if( $( "#supplier_company" )[0] ) {
		$( "#supplier_company" ).select2();
	}

	/* Datatable scripts */
	let supplierList = $("#supplier_list").DataTable({
		lengthMenu: [10, 25, 50, 75, 100],
		order: [1, "desc"],
		processing: true,
		serverSide: true,
		ajax: {
			url: "/admin/dashboard/supplier/list/datatables",
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

	/* Create supplier */
	$("button.createSupplier").on("click", function(e) {
		e.preventDefault();

		let supplier_name    = $("#supplier_name").val();
		let supplier_email   = $("#email").val();
		let supplier_company = $("#supplier_company").val();
		let supplier_phone 	 = $("#supplier_phone").val();
		let supplier_country = $("#supplier_country").val();
		let supplier_city 	 = $("#supplier_city").val();
		let supplier_street  = $("#supplier_street").val();
		let supplier_zip     = $("#supplier_zip").val();

		$.ajax({
			url: "/admin/dashboard/supplier/store",
			dataType: "JSON",
			type: "POST",
			data: {
				supplier_name: supplier_name,
				email: supplier_email,
				supplier_company: supplier_company,
				supplier_phone: supplier_phone,
				supplier_country: supplier_country,
				supplier_city: supplier_city,
				supplier_street: supplier_street,
				supplier_zip: supplier_zip
			}
		})
		.done(function(result) {
			if (result.supplierStatus === "success") {
				$("#createSupplierModal").modal("hide"); // It hides the modal

				supplierList.ajax.reload(null, false);

				$(".responseSupplierMessage").html(
					'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
					result.message +
					'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);

				$(".responseSupplierMessage")
				.show()
				.delay(5000)
				.fadeOut();
			}
		})
		.fail(function(data) {
			if (data.responseJSON.supplierStatus === "failure") {
				$(".supplierValidationAlert").html(
					'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
					data.responseJSON.message +
					'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
			}

			if (data.status === 422) {
				$.each(data.responseJSON.errors, function(key, val) {
					$(".supplierValidationAlert").html(
						"<p class='alert alert-danger'>" + val + "</p>"
						);
				});
			}
		});
	});

	/* Clearing data of create supplier modal fields */
	$("#createSupplierModal").on("hidden.bs.modal", function(e) {

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

	/* Updating supplier status */
	$("#supplier_list tbody").on("change", "input.buttonStatus", function(e) {
		e.preventDefault();

		let newStatus = "";

		let supplierStatusId = $(this)
			.parent()
			.data("supplierstatusid");

		if ($(this).is(":checked") === true) {
			newStatus = "yes";
		} else {
			newStatus = "no";
		}

		$.ajax({
			url: "/admin/dashboard/supplier/status/update",
			dataType: "JSON",
			type: "POST",
			data: { newStatus: newStatus, supplierStatusId: supplierStatusId }
		})
			.done(function(result) {
				supplierList.ajax.reload(null, false);
			})
			.fail(function(data) {
				supplierList.ajax.reload(null, false);

				if (data.responseJSON.supplierStatusChange === "failure") {
					$(".responseSupplierMessage").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
							data.responseJSON.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				}

				$(".responseMessage")
					.show()
					.delay(5000)
					.fadeOut();
			});
	});

	/* Edit supplier */
	$("#supplier_list tbody").on("click", "a.editSupplier", function(e) {
		e.preventDefault();
		let supplierid = $(this).data("supplierid");

		/* Select multiple companies */
		if( $( "#supplier_company_" + supplierid )[0] ) {
			$( "#supplier_company_" + supplierid ).select2();
		}
		
		$(".updateSupplier_" + supplierid).on("click", function(e) {
			e.preventDefault();
			let supplier_name    = $("#supplier_name_" + supplierid).val();
			let supplier_phone   = $("#supplier_phone_" + supplierid).val();
			let supplier_company = $("#supplier_company_" + supplierid).val();
			let supplier_street  = $("#supplier_street_" + supplierid).val();
			let supplier_city    = $("#supplier_city_" + supplierid).val();
			let supplier_country = $("#supplier_country_" + supplierid).val();
			let supplier_zip     = $("#supplier_zip_" + supplierid).val();

			$.ajax({
				url: "/admin/dashboard/supplier/update",
				dataType: "JSON",
				type: "PUT",
				data: {
					supplier_name: supplier_name,
					supplier_phone: supplier_phone,
					supplier_street: supplier_street,
					supplier_city: supplier_city,
					supplier_country: supplier_country,
					supplier_company: supplier_company,
					supplier_zip: supplier_zip,
					supplierid: supplierid
				}
			})
				.done(function(result) {
					if (result.supplierStatusUpdate === "success") {
						$("#editSupplierModal_" + supplierid).modal("hide"); // It hides the modal

						supplierList.ajax.reload(null, false); //Reload data on table

						$(".responseSupplierMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						$(".responseSupplierMessage")
							.show()
							.delay(5000)
							.fadeOut();
					}
				})
				.fail(function(data) {
					if (data.responseJSON.supplierStatusUpdate === "failure") {
						$(".supplierUpdateValidationAlert").html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
								data.responseJSON.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
					}

					if (data.status === 422) {
						$.each(data.responseJSON.errors, function(key, val) {
							$(".supplierUpdateValidationAlert").html(
								"<p class='alert alert-danger'>" + val + "</p>"
							);
						});
					}
				});
		});
	});

	/* Delete supplier functionality */
	$("#supplier_list tbody").on("click", "a.deleteSupplierEvent", function(e) {
		e.preventDefault();
		let supplierUserId = $(this).data("id");
		let r 			   = confirm("Are you sure you want to remove the user?");
		if (r == true) {
			$.ajax({
				url: "/admin/dashboard/supplier/delete/" + supplierUserId,
				dataType: "JSON",
				type: "DELETE"
			})
				.done(function(result) {
					if (result.deletedSupplierStatus === "success") {
						$("#editSupplierModal_" + supplierUserId).modal("hide"); // It hides the modal

						supplierList
							.row($(this).parents("tr"))
							.remove()
							.draw();

						$(".responseSupplierMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						$(".responseSupplierMessage")
							.show()
							.delay(5000)
							.fadeOut();
					}
				})
				.fail(function(data) {
					if (data.responseJSON.deletedSupplierStatus === "failure") {
						$(".supplierUpdateValidationAlert").html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
								data.responseJSON.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
					}
				});
		}
	});

	/* <tfoot> search functionality */
	$(".search-input").on("keyup change", function() {
		var i = $(this).attr("id"); // getting column index
		var v = $(this).val(); // getting search input value
		supplierList
			.columns(i)
			.search(v)
			.draw();
	});
});	