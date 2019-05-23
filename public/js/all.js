/* globals Chart:false, feather:false */

(function() {
  "use strict";

  // Graphs
  /*var ctx = document.getElementById('myChart')*/
  // eslint-disable-next-line no-unused-vars
  /*var myChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday'
      ],
      datasets: [{
        data: [
          15339,
          21345,
          18483,
          24003,
          23489,
          24092,
          12034
        ],
        lineTension: 0,
        backgroundColor: 'transparent',
        borderColor: '#007bff',
        borderWidth: 4,
        pointBackgroundColor: '#007bff'
      }]
    },
    options: {
      scales: {
        yAxes: [{
          ticks: {
            beginAtZero: false
          }
        }]
      },
      legend: {
        display: false
      }
    }
  })*/

})();

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
				type: "DELETE",
				success: function(result) {
					if (result) {
						datatableList
							.row($(this).parents("tr"))
							.remove()
							.draw();
						$(".responseMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> <strong> Well Done! </strong>' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
						$(".responseMessage")
							.show()
							.delay(5000)
							.fadeOut();
					}
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
			.fail(function() {
				$(".responseMessage").html(
					'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> Something went wrong! <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
				);

				$(".responseMessage")
					.show()
					.delay(5000)
					.fadeOut();

				datatableList.ajax.reload(null, false);
			});
	});

	/* Create manager */
	$("button.createManager").on("click", function(e) {
		e.preventDefault();

		var name = $("#name").val();
		var email = $("#email").val();
		var phone = $("#phone").val();
		var street = $("#street").val();
		var city = $("#city").val();
		var country = $("#country").val();
		var company = $("#company").val();
		var zip = $("#zip").val();
		var password = $("#password").val();
		var passwordConfirm = $("#password_confirmation").val();

		$.ajax({
			url: "/admin/dashboard/manager/store",
			dataType: "JSON",
			type: "POST",
			data: {
				name: name,
				email: email,
				phone: phone,
				street: street,
				city: city,
				country: country,
				company: company,
				zip: zip,
				password: password,
				password_confirmation: passwordConfirm
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
					$(".validationAlert").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
							data.responseJSON.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				}

				if (data.status === 422) {
					$.each(data.responseJSON.errors, function(key, val) {
						$(".validationAlert").html(
							"<p class='alert alert-danger'>" + val + "</p>"
						);
					});
				}
			});
	});

	/* Clearing data of create manager modal fields */
	$("#createManagerModal").on("hidden.bs.modal", function(e) {
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

		$(".updateManager_" + managerid).on("click", function(e) {
			e.preventDefault();
			var name = $("#name_" + managerid).val();
			var phone = $("#phone_" + managerid).val();
			var company = $("#company_" + managerid).val();
			var street = $("#street_" + managerid).val();
			var city = $("#city_" + managerid).val();
			var country = $("#country_" + managerid).val();
			var zip = $("#zip_" + managerid).val();

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
					if (result.successUpdateManager === "success") {
						$("#editManagerModal_" + managerid).modal("hide"); // It hides the modal
						$(".responseMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						datatableList.ajax.reload(null, false);
					}
				})
				.fail(function(data) {
					if (data.responseJSON.failureUpdateManager === "failure") {
						$(".updateValidationAlert").html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
								data.responseJSON.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
					}

					if (data.status === 422) {
						$.each(data.responseJSON.errors, function(key, val) {
							$(".updateValidationAlert").html(
								"<p class='alert alert-danger'>" + val + "</p>"
							);
						});
					}
				});
		});
	});
});

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

	/* Bottom buttons for datatables */
	let companyButtons;

	try {
		companyButtons = new $.fn.dataTable.Buttons(companyList, {
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
			.appendTo($("#companyButtons"));
	} catch (error) {
		companyButtons = null;
	}

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
				if (data.responseJSON.companyStatusChange === "failure") {
					companyList.ajax.reload(null, false);

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
