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

		let shop_name         		= $("#shop_name").val();
		let shop_company      		= $("#shop_company").val();
		let shop_mail_driver 	  	= $("#shop_mail_driver").val();
		let shop_mail_port 		  	= $("#shop_mail_port").val();
		let shop_mail_encryption   	= $("#shop_mail_encryption").val();
		let shop_mail_host 		  	= $("#shop_mail_host").val();
		let shop_mail_from_address 	= $("#shop_mail_from_address").val();
		let shop_mail_from_name 	= $("#shop_mail_from_name").val();
		let shop_mail_username 	  	= $("#shop_mail_username").val();
		let shop_mail_password 	  	= $("#shop_mail_password").val();
		let shop_api_key 		  	= $("#shop_api_key").val();
		let shop_customer_number  	= $("#shop_customer_number").val();
		let shop_password 		  	= $("#shop_password").val();

		$.ajax({
			url: "/admin/dashboard/shop/store",
			dataType: "JSON",
			type: "POST",
			data: {
				shop_name: shop_name,
				shop_company: shop_company,
				shop_mail_driver: shop_mail_driver,
				shop_mail_port: shop_mail_port,
				shop_mail_encryption: shop_mail_encryption,
				shop_mail_host: shop_mail_host,
				shop_mail_from_address: shop_mail_from_address,
				shop_mail_from_name: shop_mail_from_name,
				shop_mail_username: shop_mail_username,
				shop_mail_password: shop_mail_password,
				shop_api_key: shop_api_key,
				shop_customer_number: shop_customer_number,
				shop_password: shop_password
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
	 * In create page, hide shops when page loads. It shows only when select a company.
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
	$('.fa-question-circle').tooltip({
		container: 'body'
	});

	/* Multiple select for shops */
	if( $("#shop_select")[0] ) {
		$( "#shop_select" ).select2();
	}

	/* Select all shops by clicking checkbox */
	$( "#checkAllShops" ).on('click', function() {
		if( $( "#checkAllShops" ).is(':checked') ) {
			$( "#shop_select > option:not(:first)" ).prop( "selected", true);
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

		var key_name     = $( "#key_name" ).val();
		var key_type 	 = $( "#key_type" ).val();
		var company 	 = $( "#company" ).val();
		var shops 		 = $( "#shop_select" ).val();
		var act_number 	 = $( "#activation_number" ).val();
		var replaceSpace = $( "#keys" ).val().replace(/\s/g, ",").split(',');
		var keys      	 = [];

		// Convert textareas string value to javascript array separated by new lines.
		for( var i = 0; i < replaceSpace.length; i++ ) {
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
				company: company,
				shops: shops,
				keys: keys,
				act_number: act_number
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
		let keyContainerId 			= $(this).data( "keycontainerid" );
		let keyContainerCompanyId 	= $(this).data( "keycontainercompanyid" );

		// Multiple select for shops
		if( $( ".shop_edits_"+keyContainerId )[0] ) {
			$( ".shop_edits_"+keyContainerId ).select2();
		}

        // In edit page, select shops while changing company
		$( "#company_edit_"+keyContainerId ).change(function() {
		    
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
			var key_name_edit 			= $( "#key_name_edit_"+keyContainerId).val();
			var company_edit 			= $( "#company_edit_"+keyContainerId).val();
			var shop_edit  				= $( "#shop_edits_"+keyContainerId).val();
			var activation_number_edit 	= $( "#activation_number_edit_"+keyContainerId).val();
			var keys_edit_replace 	    = $( "#keys_edit_"+keyContainerId).val().replace(/\s/g, ",").split(',');
			var keys_edit = [];

			// Convert textareas string value to javascript array separated by new lines.
			for( var i = 0; i < keys_edit_replace.length; i++ ) {
				if( keys_edit_replace[i] ) {
					keys_edit.push(keys_edit_replace[i]);
				}
			}

			$.ajax({
				url: "/admin/dashboard/key/update",
				dataType: "JSON",
				type: "PUT",
				data: {
					key_name_edit: key_name_edit,
					company_edit: company_edit,
					shop_edit: shop_edit,
					activation_number_edit: activation_number_edit,
					keys_edit: keys_edit,
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
});
