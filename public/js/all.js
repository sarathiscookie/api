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

		var name = $("#name").val();
		var email = $("#email").val();
		var username = $("#username").val();
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
				username: username,
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

/**
 * User: Sarath TS
 * Date: 28.05.2019
 * Created for: adminShopList
 */

$(function() {
	/* Checking for the CSRF token */
	$.ajaxSetup({
		headers: {
			"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
		}
	});

	/* Datatable scripts */
	let shopList = $("#shop_list").DataTable({
		lengthMenu: [10, 25, 50, 75, 100],
		order: [1, "desc"],
		processing: true,
		serverSide: true,
		ajax: {
			url: "/admin/dashboard/shop/list/datatables",
			dataType: "json",
			type: "POST"
		},
		deferRender: true,
		columns: [
			{ data: "hash" },
			{ data: "shop" },
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
	let shopButtons;

	try {
		shopButtons = new $.fn.dataTable.Buttons(shopList, {
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
			.appendTo($("#shopButtons"));
	} catch (error) {
		shopButtons = null;
	}

	/* <tfoot> search functionality */
	$(".search-input").on("keyup change", function() {
		var i = $(this).attr("id"); // getting column index
		var v = $(this).val(); // getting search input value
		shopList
			.columns(i)
			.search(v)
			.draw();
	});

	/* Create shop */
	$("button.createShop").on("click", function(e) {
		e.preventDefault();

		var shop = $("#shop").val();
		var company = $("#company").val();
		var mail_driver = $("#mail_driver").val();
		var mail_port = $("#mail_port").val();
		var mail_encryption = $("#mail_encryption").val();
		var mail_host = $("#mail_host").val();
		var mail_from_address = $("#mail_from_address").val();
		var mail_from_name = $("#mail_from_name").val();
		var mail_username = $("#mail_username").val();
		var mail_password = $("#mail_password").val();
		var api_key = $("#api_key").val();
		var customer_number = $("#customer_number").val();
		var password = $("#password").val();

		$.ajax({
			url: "/admin/dashboard/shop/store",
			dataType: "JSON",
			type: "POST",
			data: {
				shop: shop,
				company: company,
				mail_driver: mail_driver,
				mail_port: mail_port,
				mail_encryption: mail_encryption,
				mail_host: mail_host,
				mail_from_address: mail_from_address,
				mail_from_name: mail_from_name,
				mail_username: mail_username,
				mail_password: mail_password,
				api_key: api_key,
				customer_number: customer_number,
				password: password
			}
		})
			.done(function(result) {
				if (result.shopStatus === "success") {
					$("#createShopModal").modal("hide"); // It hides the modal

					shopList.ajax.reload(null, false); //Reload data on table

					$(".responseShopMessage").html(
						'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
							result.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);

					$(".responseShopMessage")
						.show()
						.delay(5000)
						.fadeOut();
				}
			})
			.fail(function(data) {
				if (data.responseJSON.shopStatus === "failure") {
					$(".shopValidationAlert").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
							data.responseJSON.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				}

				if (data.status === 422) {
					$.each(data.responseJSON.errors, function(key, val) {
						$(".shopValidationAlert").html(
							"<p class='alert alert-danger'>" + val + "</p>"
						);
					});
				}
			});
	});

	/* Edit shop */
	$("#shop_list tbody").on("click", "a.editShop", function(e) {
		e.preventDefault();
		var shopid = $(this).data("shopid");

		$(".updateShop_" + shopid).on("click", function(e) {
			e.preventDefault();

			var shop = $("#shop_" + shopid).val();
			var company = $("#company_" + shopid).val();
			var mail_driver = $("#mail_driver_" + shopid).val();
			var mail_port = $("#mail_port_" + shopid).val();
			var mail_encryption = $("#mail_encryption_" + shopid).val();
			var mail_host = $("#mail_host_" + shopid).val();
			var mail_from_address = $("#mail_from_address_" + shopid).val();
			var mail_from_name = $("#mail_from_name_" + shopid).val();
			var mail_username = $("#mail_username_" + shopid).val();
			var mail_password = $("#mail_password_" + shopid).val();
			var api_key = $("#api_key_" + shopid).val();
			var customer_number = $("#customer_number_" + shopid).val();
			var password = $("#password_" + shopid).val();

			$.ajax({
				url: "/admin/dashboard/shop/update",
				dataType: "JSON",
				type: "PUT",
				data: {
					shop: shop,
					company: company,
					mail_driver: mail_driver,
					mail_port: mail_port,
					mail_encryption: mail_encryption,
					mail_host: mail_host,
					mail_from_address: mail_from_address,
					mail_from_name: mail_from_name,
					mail_username: mail_username,
					mail_password: mail_password,
					api_key: api_key,
					customer_number: customer_number,
					password: password,
					shopid: shopid
				}
			})
				.done(function(result) {
					if (result.shopStatusUpdate === "success") {
						$("#editShopModal_" + shopid).modal("hide"); // It hides the modal

						shopList.ajax.reload(null, false); //Reload data on table

						$(".responseShopMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						$(".responseShopMessage")
							.show()
							.delay(5000)
							.fadeOut();
					}
				})
				.fail(function(data) {
					if (data.responseJSON.shopStatusUpdate === "failure") {
						$(".shopUpdateValidationAlert").html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
								data.responseJSON.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
					}

					if (data.status === 422) {
						$.each(data.responseJSON.errors, function(key, val) {
							$(".shopUpdateValidationAlert").html(
								"<p class='alert alert-danger'>" + val + "</p>"
							);
						});
					}
				});
		});
	});

	/* Updating shop status */
	$("#shop_list tbody").on("change", "input.buttonStatus", function(e) {
		e.preventDefault();

		var newStatus = "";

		var shopStatusId = $(this)
			.parent()
			.data("shopstatusid");

		if ($(this).is(":checked") === true) {
			newStatus = "yes";
		} else {
			newStatus = "no";
		}

		$.ajax({
			url: "/admin/dashboard/shop/status/update",
			dataType: "JSON",
			type: "POST",
			data: { newStatus: newStatus, shopStatusId: shopStatusId }
		})
			.done(function(result) {
				shopList.ajax.reload(null, false);
			})
			.fail(function(data) {
				shopList.ajax.reload(null, false);

				if (data.responseJSON.shopStatusChange === "failure") {
					$(".responseShopMessage").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
							data.responseJSON.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				}

				$(".responseShopMessage")
					.show()
					.delay(5000)
					.fadeOut();
			});
	});

	/* Delete shop functionality */
	$("#shop_list tbody").on("click", "a.deleteShop", function(e) {
		e.preventDefault();
		var deleteshopid = $(this).data("deleteshopid");
		var r = confirm("Are you sure you want to remove the shop?");
		if (r == true) {
			$.ajax({
				url: "/admin/dashboard/shop/delete/" + deleteshopid,
				dataType: "JSON",
				type: "DELETE"
			})
				.done(function(result) {
					if (result.deletedShopStatus === "success") {
						$("#editShopModal_" + deleteshopid).modal("hide"); // It hides the modal

						shopList
							.row($(this).parents("tr"))
							.remove()
							.draw();

						$(".responseShopMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						$(".responseShopMessage")
							.show()
							.delay(5000)
							.fadeOut();
					}
				})
				.fail(function(data) {
					if (data.responseJSON.deletedShopStatus === "failure") {
						$(".shopUpdateValidationAlert").html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
								data.responseJSON.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
					}
				});
		}
	});

	/* Clearing data of create shop modal fields */
	$("#createShopModal").on("hidden.bs.modal", function(e) {
		$(this)
			.find("input,textarea,select")
			.val("")
			.end()
			.find("input[type=checkbox], input[type=radio]")
			.prop("checked", "")
			.end();
	});
});

/**
 * User: Sarath TS
 * Date: 04.06.2019
 * Created for: adminUserList
 */

$(function() {
	/* Checking for the CSRF token */
	$.ajaxSetup({
		headers: {
			"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
		}
	});

	/* Datatable scripts */
	let userList = $("#user_list").DataTable({
		lengthMenu: [10, 25, 50, 75, 100],
		order: [1, "desc"],
		processing: true,
		serverSide: true,
		ajax: {
			url: "/admin/dashboard/user/list/datatables",
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
		buttons = new $.fn.dataTable.Buttons(userList, {
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

	/* <tfoot> search functionality */
	$(".search-input").on("keyup change", function() {
		var i = $(this).attr("id"); // getting column index
		var v = $(this).val(); // getting search input value
		userList
			.columns(i)
			.search(v)
			.draw();
	});

	/* Create user */
	$("button.createUser").on("click", function(e) {
		e.preventDefault();

		var name = $("#name").val();
		var email = $("#email").val();
		var username = $("#username").val();
		var phone = $("#phone").val();
		var street = $("#street").val();
		var city = $("#city").val();
		var country = $("#country").val();
		var company = $("#company").val();
		var zip = $("#zip").val();
		var password = $("#password").val();
		var passwordConfirm = $("#password_confirmation").val();

		$.ajax({
			url: "/admin/dashboard/user/store",
			dataType: "JSON",
			type: "POST",
			data: {
				name: name,
				email: email,
				username: username,
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
				if (result.userStatus === "success") {
					$("#createUserModal").modal("hide"); // It hides the modal

					userList.ajax.reload(null, false);

					$(".responseUserMessage").html(
						'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
							result.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);

					$(".responseUserMessage")
						.show()
						.delay(5000)
						.fadeOut();
				}
			})
			.fail(function(data) {
				if (data.responseJSON.userStatus === "failure") {
					$(".userValidationAlert").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
							data.responseJSON.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				}

				if (data.status === 422) {
					$.each(data.responseJSON.errors, function(key, val) {
						$(".userValidationAlert").html(
							"<p class='alert alert-danger'>" + val + "</p>"
						);
					});
				}
			});
	});

	/* Clearing data of create user modal fields */
	$("#createUserModal").on("hidden.bs.modal", function(e) {
		$(this)
			.find("input,textarea,select")
			.val("")
			.end()
			.find("input[type=checkbox], input[type=radio]")
			.prop("checked", "")
			.end();
	});

	/* Edit user */
	$("#user_list tbody").on("click", "a.editUser", function(e) {
		e.preventDefault();
		var userid = $(this).data("userid");

		$(".updateUser_" + userid).on("click", function(e) {
			e.preventDefault();
			var name = $("#name_" + userid).val();
			var phone = $("#phone_" + userid).val();
			var company = $("#company_" + userid).val();
			var street = $("#street_" + userid).val();
			var city = $("#city_" + userid).val();
			var country = $("#country_" + userid).val();
			var zip = $("#zip_" + userid).val();

			$.ajax({
				url: "/admin/dashboard/user/update",
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
					userid: userid
				}
			})
				.done(function(result) {
					if (result.userStatusUpdate === "success") {
						$("#editUserModal_" + userid).modal("hide"); // It hides the modal

						userList.ajax.reload(null, false); //Reload data on table

						$(".responseUserMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						$(".responseUserMessage")
							.show()
							.delay(5000)
							.fadeOut();
					}
				})
				.fail(function(data) {
					if (data.responseJSON.userStatusUpdate === "failure") {
						$(".userUpdateValidationAlert").html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
								data.responseJSON.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
					}

					if (data.status === 422) {
						$.each(data.responseJSON.errors, function(key, val) {
							$(".userUpdateValidationAlert").html(
								"<p class='alert alert-danger'>" + val + "</p>"
							);
						});
					}
				});
		});
	});

	/* Delete user functionality */
	$("#user_list tbody").on("click", "a.deleteEvent", function(e) {
		e.preventDefault();
		var userId = $(this).data("id");
		var r = confirm("Are you sure you want to remove this user?");
		if (r == true) {
			$.ajax({
				url: "/admin/dashboard/user/delete/" + userId,
				dataType: "JSON",
				type: "DELETE"
			})
				.done(function(result) {
					if (result.deletedUserStatus === "success") {
						$("#editUserModal_" + userId).modal("hide"); // It hides the modal

						userList
							.row($(this).parents("tr"))
							.remove()
							.draw();

						$(".responseUserMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						$(".responseUserMessage")
							.show()
							.delay(5000)
							.fadeOut();
					}
				})
				.fail(function(data) {
					if (data.responseJSON.deletedUserStatus === "failure") {
						$(".userUpdateValidationAlert").html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
								data.responseJSON.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
					}
				});
		}
	});

	/* Updating user status */
	$("#user_list tbody").on("change", "input.buttonStatus", function(e) {
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
			url: "/admin/dashboard/user/status/update",
			dataType: "JSON",
			type: "POST",
			data: { newStatus: newStatus, userId: userId }
		})
			.done(function(result) {
				userList.ajax.reload(null, false);
			})
			.fail(function(data) {
				userList.ajax.reload(null, false);

				if (data.responseJSON.userStatusChange === "failure") {
					$(".responseUserMessage").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
							data.responseJSON.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				}

				$(".responseUserMessage")
					.show()
					.delay(5000)
					.fadeOut();
			});
	});
});

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

	/* Multiple select for shops */
	if( $("#shop")[0] ) {
		$( "#shop" ).select2();
	}

	/* Select all shops by clicking checkbox */
	$( "#checkAllShops" ).on('click', function() {
		if( $( "#checkAllShops" ).is(':checked') ) {
			$( "#shop > option:not(:first)" ).prop( "selected", true);
			$( "#shop" ).trigger("change");
		} 
		else {
			$( "#shop > option" ).prop( "selected", false);
			$( "#shop" ).trigger("change");
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

	/* Bottom buttons for datatables */
	let buttons;

	try {
		buttons = new $.fn.dataTable.Buttons(keyList, {
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
	/*$( "#key_list tbody" ).on("click", "a.deleteEvent", function(e) {
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
	});*/

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
	/*$( "#key_list tbody" ).on("change", "input.buttonStatus", function(e) {
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
	});*/

	/* Create key */
	$( "button.createKey" ).on( "click", function(e) {
		e.preventDefault();

		var key_name    = $( "#key_name" ).val();
		var key_type 	= $( "#key_type" ).val();
		var company 	= $( "#company" ).val();
		var shops 		= $( "#shop" ).val();
		var keySplit    = $( "#keys" ).val().split(/\n/);
		var keys      	= [];

		// Convert textareas string value to javascript array separated by new lines.
		for( var i = 0; i < keySplit.length; i++ ) {
			if( keySplit[i] ) {
				keys.push(keySplit[i]);
			}
		}	

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
				shops: shops,
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
	/*$( "#key_list tbody" ).on("click", "a.editKey", function(e) {
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
	});*/
});
