/**
 * User: Sarath TS
 * Date: 21.05.2019
 * Created for: adminCompanyList
 */

$(function() {
	"use strict";

	/* Checking for the CSRF token */
	$.ajaxSetup({
		headers: {
			"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
		}
	});

	/* Datatable scripts */
	let companyList = $("#company_list").DataTable({
		lengthMenu: [10, 25, 50, 75, 100],
		order: [1, "desc"],
		processing: true,
		serverSide: true,
		ajax: {
			url: "/admin/dashboard/company/list/datatables",
			dataType: "json",
			type: "POST"
		},
		deferRender: true,
		columns: [
			{ data: "hash" },
			{ data: "company" },
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

	/* <tfoot> search functionality */
	$(".search-input").on("keyup change", function() {
		var i = $(this).attr("id"); // getting column index
		var v = $(this).val(); // getting search input value
		companyList
			.columns(i)
			.search(v)
			.draw();
	});

	/* Create company */
	$("button.createCompany").on("click", function(e) {
		e.preventDefault();

		var company = $("#company").val();
		var phone = $("#phone").val();
		var street = $("#street").val();
		var city = $("#city").val();
		var country = $("#country").val();
		var zip = $("#zip").val();

		$.ajax({
			url: "/admin/dashboard/company/store",
			dataType: "JSON",
			type: "POST",
			data: {
				company: company,
				phone: phone,
				street: street,
				city: city,
				country: country,
				zip: zip
			}
		})
			.done(function(result) {
				if (result.companyStatus === "success") {
					$("#createCompanyModal").modal("hide"); // It hides the modal

					companyList.ajax.reload(null, false); //Reload data on table

					$(".responseCompanyMessage").html(
						'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
							result.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);

					$(".responseCompanyMessage")
						.show()
						.delay(5000)
						.fadeOut();
				}
			})
			.fail(function(data) {
				if (data.responseJSON.companyStatus === "failure") {
					$(".companyValidationAlert").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
							data.responseJSON.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				}

				if (data.status === 422) {
					$.each(data.responseJSON.errors, function(key, val) {
						$(".companyValidationAlert").html(
							"<p class='alert alert-danger'>" + val + "</p>"
						);
					});
				}
			});
	});

	/* Clearing data of create company modal fields */
	$("#createCompanyModal").on("hidden.bs.modal", function(e) {
		
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

	/* Edit company details */
	$("#company_list tbody").on("click", "a.editCompany", function(e) {
		var companyid = $(this).data("companyid");

		$(".updateCompany_" + companyid).on("click", function(e) {
			e.preventDefault();

			var company = $("#company_" + companyid).val();
			var phone = $("#phone_" + companyid).val();
			var street = $("#street_" + companyid).val();
			var city = $("#city_" + companyid).val();
			var country = $("#country_" + companyid).val();
			var zip = $("#zip_" + companyid).val();

			$.ajax({
				url: "/admin/dashboard/company/update",
				dataType: "JSON",
				type: "PUT",
				data: {
					company: company,
					phone: phone,
					street: street,
					city: city,
					country: country,
					zip: zip,
					companyid: companyid
				}
			})
				.done(function(result) {
					if (result.companyStatusUpdate === "success") {
						$("#editCompanyModal_" + companyid).modal("hide"); // It hides the modal

						companyList.ajax.reload(null, false); //Reload data on table

						$(".responseCompanyMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						$(".responseCompanyMessage")
							.show()
							.delay(5000)
							.fadeOut();
					}
				})
				.fail(function(data) {
					if (data.responseJSON.companyStatusUpdate === "failure") {
						$(".companyUpdateValidationAlert").html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
								data.responseJSON.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
					}

					if (data.status === 422) {
						$.each(data.responseJSON.errors, function(key, val) {
							$(".companyUpdateValidationAlert").html(
								"<p class='alert alert-danger'>" + val + "</p>"
							);
						});
					}
				});
		});
	});

	/* Delete company functionality */
	$("#company_list tbody").on("click", "a.deleteCompany", function(e) {
		e.preventDefault();
		var deletecompanyid = $(this).data("deletecompanyid");
		var r = confirm("Are you sure you want to remove the company?");
		if (r == true) {
			$.ajax({
				url: "/admin/dashboard/company/delete/" + deletecompanyid,
				dataType: "JSON",
				type: "DELETE"
			})
				.done(function(result) {
					if (result.deletedCompanyStatus === "success") {
						$("#editCompanyModal_" + deletecompanyid).modal("hide"); // It hides the modal

						companyList
							.row($(this).parents("tr"))
							.remove()
							.draw();

						$(".responseCompanyMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						$(".responseCompanyMessage")
							.show()
							.delay(5000)
							.fadeOut();
					}
				})
				.fail(function(data) {
					if (data.responseJSON.deletedCompanyStatus === "failure") {
						$(".companyUpdateValidationAlert").html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
								data.responseJSON.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
					}
				});
		}
	});

	/* Company status changing functionality */
	$("#company_list tbody").on("change", "input.buttonStatus", function(e) {
		e.preventDefault();

		var newStatus = "";

		var companyStatusId = $(this)
			.parent()
			.data("companystatusid");

		if ($(this).is(":checked") === true) {
			newStatus = "yes";
		} else {
			newStatus = "no";
		}

		$.ajax({
			url: "/admin/dashboard/company/status/update",
			dataType: "JSON",
			type: "POST",
			data: { newStatus: newStatus, companyStatusId: companyStatusId }
		})
			.done(function(result) {
				companyList.ajax.reload(null, false);
			})
			.fail(function(data) {
				companyList.ajax.reload(null, false);

				if (data.responseJSON.companyStatusChange === "failure") {
					$(".responseCompanyMessage").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
							data.responseJSON.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				}

				$(".responseCompanyMessage")
					.show()
					.delay(5000)
					.fadeOut();
			});
	});
});
