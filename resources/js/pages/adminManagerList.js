/**
 * User: Sarath TS
 * Date: 04.05.2019
 * Created for: adminManagerList
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
	if( $( "#manager_company" )[0] ) {
		$( "#manager_company" ).select2();
	}

	/* Datatable scripts */
	let datatableList = $("#manager_list").DataTable({
		lengthMenu: [10, 25, 50, 75, 100],
		order: [1, "desc"],
		processing: true,
		serverSide: true,
		ajax: {
			url: "/admin/dashboard/manager/list/datatables",
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

	/* Delete manager functionality */
	$("#manager_list tbody").on("click", "a.deleteEvent", function(e) {
		e.preventDefault();
		var userId = $(this).data("id");
		var r = confirm("Are you sure you want to remove the user?");
		if (r == true) {
			$.ajax({
				url: "/admin/dashboard/manager/delete/" + userId,
				dataType: "JSON",
				type: "DELETE"
			})
				.done(function(result) {
					if (result.deletedManagerStatus === "success") {
						$("#editManagerModal_" + userId).modal("hide"); // It hides the modal

						datatableList
							.row($(this).parents("tr"))
							.remove()
							.draw();

						$(".responseMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						$(".responseMessage")
							.show()
							.delay(5000)
							.fadeOut();
					}
				})
				.fail(function(data) {
					if (data.responseJSON.deletedManagerStatus === "failure") {
						$(".managerUpdateValidationAlert").html(
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
		datatableList
			.columns(i)
			.search(v)
			.draw();
	});

	/* Updating manager status */
	$("#manager_list tbody").on("change", "input.buttonStatus", function(e) {
		e.preventDefault();

		var newStatus = "";

		var userId = $(this)
			.parent()
			.data("userid");

		if ($(this).is(":checked") === true) {
			newStatus = "yes";
		} else {
			newStatus = "no";
		}

		$.ajax({
			url: "/admin/dashboard/manager/status/update",
			dataType: "JSON",
			type: "POST",
			data: { newStatus: newStatus, userId: userId }
		})
			.done(function(result) {
				datatableList.ajax.reload(null, false);
			})
			.fail(function(data) {
				datatableList.ajax.reload(null, false);

				if (data.responseJSON.managerStatusChange === "failure") {
					$(".responseMessage").html(
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

	/* Create manager */
	$("button.createManager").on("click", function(e) {
		e.preventDefault();

		let manager_name 			 = $("#name").val();
		let manager_email 			 = $("#email").val();
		let manager_username 		 = $("#username").val();
		let manager_phone 			 = $("#phone").val();
		let manager_street 			 = $("#street").val();
		let manager_city 			 = $("#city").val();
		let manager_country 		 = $("#country").val();
		let manager_company 		 = $("#manager_company").val();
		let manager_zip 			 = $("#zip").val();
		let manager_password 	     = $("#password").val();
		let manager_password_confirm = $("#password_confirmation").val();

		$.ajax({
			url: "/admin/dashboard/manager/store",
			dataType: "JSON",
			type: "POST",
			data: {
				name: manager_name,
				email: manager_email,
				username: manager_username,
				phone: manager_phone,
				street: manager_street,
				city: manager_city,
				country: manager_country,
				manager_company: manager_company,
				zip: manager_zip,
				password: manager_password,
				password_confirmation: manager_password_confirm
			}
		})
		.done(function(result) {
			if (result.managerStatus === "success") {
					$("#createManagerModal").modal("hide"); // It hides the modal

					datatableList.ajax.reload(null, false);

					$(".responseMessage").html(
						'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
						result.message +
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

					$(".responseMessage")
					.show()
					.delay(5000)
					.fadeOut();
				}
			})
		.fail(function(data) {
			if (data.responseJSON.managerStatus === "failure") {
				$(".managerValidationAlert").html(
					'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
					data.responseJSON.message +
					'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
			}

			if (data.status === 422) {
				$.each(data.responseJSON.errors, function(key, val) {
					$(".managerValidationAlert").html(
						"<p class='alert alert-danger'>" + val + "</p>"
						);
				});
			}
		});
	});

	/* Clearing data of create manager modal fields */
	$("#createManagerModal").on("hidden.bs.modal", function(e) {

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
	$("#manager_list tbody").on("click", "a.editManager", function(e) {
		e.preventDefault();
		var managerid = $(this).data("managerid");

		/* Select multiple companies */
		if( $( "#manger_company_" + managerid )[0] ) {
			$( "#manger_company_" + managerid ).select2();
		}
		
		$(".updateManager_" + managerid).on("click", function(e) {
			e.preventDefault();
			let manager_edit_name    = $("#name_" + managerid).val();
			let manager_edit_phone 	 = $("#phone_" + managerid).val();
			let manager_edit_company = $("#manger_company_" + managerid).val();
			let manager_edit_street  = $("#street_" + managerid).val();
			let manager_edit_city    = $("#city_" + managerid).val();
			let manager_edit_country = $("#country_" + managerid).val();
			let manager_edit_zip     = $("#zip_" + managerid).val();

			$.ajax({
				url: "/admin/dashboard/manager/update",
				dataType: "JSON",
				type: "PUT",
				data: {
					name: manager_edit_name,
					phone: manager_edit_phone,
					street: manager_edit_street,
					city: manager_edit_city,
					country: manager_edit_country,
					manager_company: manager_edit_company,
					zip: manager_edit_zip,
					managerid: managerid
				}
			})
				.done(function(result) {
					if (result.managerStatusUpdate === "success") {
						$("#editManagerModal_" + managerid).modal("hide"); // It hides the modal

						datatableList.ajax.reload(null, false); //Reload data on table

						$(".responseMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						$(".responseMessage")
							.show()
							.delay(5000)
							.fadeOut();
					}
				})
				.fail(function(data) {
					if (data.responseJSON.managerStatusUpdate === "failure") {
						$(".managerUpdateValidationAlert").html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
								data.responseJSON.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
					}

					if (data.status === 422) {
						$.each(data.responseJSON.errors, function(key, val) {
							$(".managerUpdateValidationAlert").html(
								"<p class='alert alert-danger'>" + val + "</p>"
							);
						});
					}
				});
		});
	});
});
