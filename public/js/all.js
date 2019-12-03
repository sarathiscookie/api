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
 * Date: 12.08.2019
 * Created for: adminShopList
 */

$(function() {
	/* Checking for the CSRF token */
	$.ajaxSetup({
		headers: {
			"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
		}
	});

	/* Hide & Show token while selecting amazon and ebay */
	let shop_names_token;
	function shopGlobalVariabeFn() {
		shop_names_token = {
			shop_name_amazone: window.environment.shop_name_amazone,
			shop_name_ebay: window.environment.shop_name_ebay,
		};

		return shop_names_token;
	}

	$( "#shop_name" ).change(function() {
		if( ($("option:selected", this).text() === shopGlobalVariabeFn().shop_name_amazone) || ($("option:selected", this).text() === shopGlobalVariabeFn().shop_name_ebay) ) {
			$( ".shop_token_div" ).show();
		}
		else {
			$( ".shop_token_div" ).hide();
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
		let shop_token 		  	    = $("#shop_token").val();
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
				shop_token: shop_token,
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

		let shopid    = $(this).data("shopid");
		let shoptoken = $(this).data("shoptoken");

		// Show & Hide shops default when page loads initially
		if( ($( "#shop_name_"+shopid+" option:selected" ).text() === shopGlobalVariabeFn().shop_name_amazone) || ($( "#shop_name_"+shopid+" option:selected" ).text() === shopGlobalVariabeFn().shop_name_ebay) ) {
			$( ".shop_token_div_"+shopid ).html('<div class="form-group col-md-12"><label for="shop_token">Token</label><input id="shop_token_'+shopid+'" type="text" class="form-control" name="shop_token" value = "'+shoptoken+'" maxlength="255"></div>');
		}
		else {
			$( ".shop_token_div_"+shopid ).hide();
		}
		
		// Hide & Show token while choosing amazon or ebay
		$( "#shop_name_"+shopid ).on('change', function() {
			if( ($( "#shop_name_"+shopid+" option:selected" ).text() === shopGlobalVariabeFn().shop_name_amazone) || ($( "#shop_name_"+shopid+" option:selected" ).text() === shopGlobalVariabeFn().shop_name_ebay) ) {
				$( ".shop_token_div_"+shopid ).show();
				$( ".shop_token_div_"+shopid ).html('<div class="form-group col-md-12"><label for="shop_token">Token</label><input id="shop_token_'+shopid+'" type="text" class="form-control" name="shop_token" value = "'+shoptoken+'" maxlength="255"></div>');
			}
			else {
				$( ".shop_token_div_"+shopid ).hide();
			}
		});

		$(".updateShop_" + shopid).on("click", function(e) {
			e.preventDefault();

			let shop_name 			 	= $("#shop_name_" + shopid).val();
			let shop_company 		 	= $("#shop_company_" + shopid).val();
			let shop_token 		 	    = ( $( "#shop_name_"+shopid ).val() > 1 ) ? $("#shop_token_" + shopid).val() : '';
			let shop_mail_driver 	 	= $("#shop_mail_driver_" + shopid).val();
			let shop_mail_port 		 	= $("#shop_mail_port_" + shopid).val();
			let shop_mail_encryption 	= $("#shop_mail_encryption_" + shopid).val();
			let shop_mail_host 		   	= $("#shop_mail_host_" + shopid).val();
			let shop_mail_from_address 	= $("#shop_mail_from_address_" + shopid).val();
			let shop_mail_from_name 	= $("#shop_mail_from_name_" + shopid).val();
			let shop_mail_username 		= $("#shop_mail_username_" + shopid).val();
			let shop_mail_password 		= $("#shop_mail_password_" + shopid).val();
			let shop_api_key 			= $("#shop_api_key_" + shopid).val();
			let shop_customer_number 	= $("#shop_customer_number_" + shopid).val();
			let shop_password 			= $("#shop_password_" + shopid).val();

			$.ajax({
				url: "/admin/dashboard/shop/update",
				dataType: "JSON",
				type: "PUT",
				data: {
					shop_name: shop_name,
					shop_company: shop_company,
					shop_token: shop_token,
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
					shop_password: shop_password,
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

		var name 			= $("#name").val();
		var email 			= $("#email").val();
		var username 		= $("#username").val();
		var phone 			= $("#phone").val();
		var street 			= $("#street").val();
		var city 			= $("#city").val();
		var country 		= $("#country").val();
		var user_company    = $("#user_company").val();
		var zip 			= $("#zip").val();
		var password 		= $("#password").val();
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
				user_company: user_company,
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

	/* Edit user */
	$("#user_list tbody").on("click", "a.editUser", function(e) {
		e.preventDefault();
		var userid = $(this).data("userid");

		$(".updateUser_" + userid).on("click", function(e) {
			e.preventDefault();
			var name 		 = $("#name_" + userid).val();
			var phone 		 = $("#phone_" + userid).val();
			var user_company = $("#user_company_" + userid).val();
			var street 		 = $("#street_" + userid).val();
			var city 		 = $("#city_" + userid).val();
			var country 	 = $("#country_" + userid).val();
			var zip  	     = $("#zip_" + userid).val();

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
					user_company: user_company,
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

	// Delete key instruction data and files.
	$( "#key_list tbody, div.keyinstructiondata" ).on("click", "a.deleteKeyInstruction", function(e) {
		e.preventDefault();

		let keydeleteinstructionid = $(this).data( "keydeleteinstructionid" );
		let confirmDelete          = confirm("Are you sure you want to remove the file?");
		if(confirmDelete === true) {
			$.ajax({
			url: "/admin/dashboard/key/instruction/delete/" + keydeleteinstructionid,
			dataType: "JSON",
			type: "DELETE"
		})
		.done(function(result) {
			if (result.keyInstructionDeleteStatus === "success") {
				
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
			if (data.responseJSON.keyInstructionDeleteStatus === "failure") {
				$( ".responseKeyMessage" ).html(
					'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
					data.responseJSON.message +
					'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
			}
		});
		}
	});
	
});

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
/**
 * User: Sarath TS
 * Date: 16.08.2019
 * Created for: productList
 */

$(function() {
	/* Checking for the CSRF token */
	$.ajaxSetup({
		headers: {
			"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
		}
	});

	let shopId = "";
	let companyId = "";
	let productList;
	let selected;

	// Setting href attribute when changing shop
	$("#product_shop").on("change", function() {
		shopId = $(this).val();
		$(".getProducts").attr(
			"href",
			"/admin/dashboard/product/list/" + shopId + "/" + companyId
		);
	});

	// Setting href attribute when select a company
	$("#product_company").on("change", function() {
		companyId = $(this).val();
		$(".getProducts").attr(
			"href",
			"/admin/dashboard/product/list/" + shopId + "/" + companyId
		);
	});

	// Alert message if user forgot to fill the fields
	$(".getProducts").on("click", function(e) {
		if (
			$("#product_shop").val() == "" ||
			$("#product_company").val() == ""
		) {
			e.preventDefault();
			alert("Please fill the data");
		}
	});

	//On page loading it works
	dataTableFn(null, null, null);

	/* Datatable scripts */
	function dataTableFn(productCategoryId, visible, available) {
		productList = $("#product_list").DataTable({
			pageLength: 20,
			order: [1, "desc"],
			processing: true,
			serverSide: true,
			lengthChange: false,
			ajax: {
				url: "/admin/dashboard/product/list/datatables",
				dataType: "json",
				type: "POST",
				data: {
					productListShopID: $(".productListShopIdClass").val(),
					productListCompanyId: $(".productListCompanyIdClass").val(),
					pageActive: function() {
						let productListTableInfo = $("#product_list")
							.DataTable()
							.page.info();
						return productListTableInfo.page + 1;
					},
					productCategoryId: productCategoryId,
					visible: visible,
					available: available
				},
				dataSrc: function(result) {
					$(".shop_categories_options").remove();

					if (result.categoryDetails.length > 0) {
						if (result.categoryDetails.length > 1) {
							$("#shopCategoriesSelect").append(
								'<option class="shop_categories_options" value="allCategories">All Categories</option>'
								);
						}
						for (let i = 0;i < result.categoryDetails.length;i++) {
							if (productCategoryId == result.categoryDetails[i].shop_category_id) {
								selected = 'selected="selected"';
							} 
							else {
								selected = "";
							}
							$("#shopCategoriesSelect").append(
								'<option class="shop_categories_options" ' +
								selected +
								' value="' +
								result.categoryDetails[i].shop_category_id +
								'">' +
								result.categoryDetails[i].name +
								"</option>"
								);
						}
					} 
					else {
						$(".noCategoriesFound").remove();
						$("#shopCategoriesSelect").append(
							'<option class="shop_categories_options" value="0">Categories are not available for this shop</option>'
							);
					}

					return result.data;
				}
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
	}

	// Datatable search min 3 char length needed
	$("input[type=search]")
		.unbind() // Unbind previous default bindings
		.bind("input", function(e) {
			// Bind our desired behavior
			// If the length is 3 or more characters, or the user pressed ENTER, search
			if (this.value.length >= 3 || e.keyCode == 13) {
				productList.search(this.value).draw();
			}

			if (this.value == "") {
				productList.search("").draw();
			}
			return;
		});

    //Function for shop category id
	function shopCategoriesFun(filterShopCategoryId)	{

		let shopCategoryIdFilter;
		if( (filterShopCategoryId !== null) || (filterShopCategoryId !== '') ) {
			shopCategoryIdFilter = filterShopCategoryId;
		}
		else {
			shopCategoryIdFilter = null;
		}

		return shopCategoryIdFilter;
	}		

	//Filter for category
	$("#shopCategoriesSelect").change(function() {

		let categoryId = $("#shopCategoriesSelect").val();

		if ( (categoryId !== null) || (categoryId !== '') ) {

			//Passing available class name to function
			let availableStatus = statusFunc(
				$(".availableActive").hasClass("btn-success"),
				$(".availableDisable").hasClass("btn-success"),
				$(".availableAll").hasClass("btn-success")
			);

			//Passing visible class name to function
			let visibleActiveStatus = statusFunc(
				$(".visibleActive").hasClass("btn-success"),
				$(".visibleDisable").hasClass("btn-success"),
				$(".visibleAll").hasClass("btn-success")
			);

			productList.destroy();
			dataTableFn(categoryId, visibleActiveStatus, availableStatus);
		}

	});

	//Filter for visible
	$(".visibleActive").on("click", function(e) {
		e.preventDefault();
		if ($(this).hasClass("btn-secondary")) {
			$(this)
				.addClass("btn-success")
				.removeClass("btn-secondary");
			$(".visibleDisable")
				.addClass("btn-secondary")
				.removeClass("btn-success");
			$(".visibleAll")
				.addClass("btn-secondary")
				.removeClass("btn-success");

			//Passing class name to function
			let availableActiveStatus = statusFunc(
				$(".availableActive").hasClass("btn-success"),
				$(".availableDisable").hasClass("btn-success"),
				$(".availableAll").hasClass("btn-success")
			);

			//Filter to visible active
			let visibleActiveCategoryId = shopCategoriesFun($("#shopCategoriesSelect").val());
			
			productList.destroy();
			dataTableFn(visibleActiveCategoryId, 1, availableActiveStatus);
		}
	});

	$(".visibleDisable").on("click", function(e) {
		e.preventDefault();
		if ($(this).hasClass("btn-secondary")) {
			$(this)
				.addClass("btn-success")
				.removeClass("btn-secondary");
			$(".visibleActive")
				.addClass("btn-secondary")
				.removeClass("btn-success");
			$(".visibleAll")
				.addClass("btn-secondary")
				.removeClass("btn-success");

			//Passing class name to function
			let availableDisableStatus = statusFunc(
				$(".availableActive").hasClass("btn-success"),
				$(".availableDisable").hasClass("btn-success"),
				$(".availableAll").hasClass("btn-success")
			);

			//Filter to visible disable
			let visibleDisableCategoryId = shopCategoriesFun($("#shopCategoriesSelect").val());

			productList.destroy();
			dataTableFn(visibleDisableCategoryId, 0, availableDisableStatus);
		}
	});

	$(".visibleAll").on("click", function(e) {
		e.preventDefault();
		if ($(this).hasClass("btn-secondary")) {
			$(this)
				.addClass("btn-success")
				.removeClass("btn-secondary");
			$(".visibleActive")
				.addClass("btn-secondary")
				.removeClass("btn-success");
			$(".visibleDisable")
				.addClass("btn-secondary")
				.removeClass("btn-success");

			//Passing class name to function
			let availableAllStatus = statusFunc(
				$(".availableActive").hasClass("btn-success"),
				$(".availableDisable").hasClass("btn-success"),
				$(".availableAll").hasClass("btn-success")
			);

			//Filter to visible all
			let visibleAllCategoryId = shopCategoriesFun($("#shopCategoriesSelect").val());

			productList.destroy();
			dataTableFn(visibleAllCategoryId, null, availableAllStatus);
		}
	});

	//Filter for available
	$(".availableActive").on("click", function(e) {
		e.preventDefault();
		if ($(this).hasClass("btn-secondary")) {
			$(this)
				.addClass("btn-success")
				.removeClass("btn-secondary");
			$(".availableDisable")
				.addClass("btn-secondary")
				.removeClass("btn-success");
			$(".availableAll")
				.addClass("btn-secondary")
				.removeClass("btn-success");

			//Passing class name to function
			let visibleActiveStatus = statusFunc(
				$(".visibleActive").hasClass("btn-success"),
				$(".visibleDisable").hasClass("btn-success"),
				$(".visibleAll").hasClass("btn-success")
			);

			//Filter to available active
			let availableActiveCategoryId = shopCategoriesFun($("#shopCategoriesSelect").val());

			productList.destroy();
			dataTableFn(availableActiveCategoryId, visibleActiveStatus, 1);
		}
	});

	$(".availableDisable").on("click", function(e) {
		e.preventDefault();
		if ($(this).hasClass("btn-secondary")) {
			$(this)
				.addClass("btn-success")
				.removeClass("btn-secondary");
			$(".availableActive")
				.addClass("btn-secondary")
				.removeClass("btn-success");
			$(".availableAll")
				.addClass("btn-secondary")
				.removeClass("btn-success");

			//Passing class name to function
			let visibleDisableStatus = statusFunc(
				$(".visibleActive").hasClass("btn-success"),
				$(".visibleDisable").hasClass("btn-success"),
				$(".visibleAll").hasClass("btn-success")
			);

			//Filter to available disable
			let availableDisableCategoryId = shopCategoriesFun($("#shopCategoriesSelect").val());

			productList.destroy();
			dataTableFn(availableDisableCategoryId, visibleDisableStatus, 0);
		}
	});

	$(".availableAll").on("click", function(e) {
		e.preventDefault();
		if ($(this).hasClass("btn-secondary")) {
			$(this)
				.addClass("btn-success")
				.removeClass("btn-secondary");
			$(".availableActive")
				.addClass("btn-secondary")
				.removeClass("btn-success");
			$(".availableDisable")
				.addClass("btn-secondary")
				.removeClass("btn-success");

			//Passing class name to function
			let visibleAllStatus = statusFunc(
				$(".visibleActive").hasClass("btn-success"),
				$(".visibleDisable").hasClass("btn-success"),
				$(".visibleAll").hasClass("btn-success")
			);

			//Filter to available all
			let availableAllCategoryId = shopCategoriesFun($("#shopCategoriesSelect").val());

			productList.destroy();
			dataTableFn(availableAllCategoryId, visibleAllStatus, null);
		}
	});

	//Function to set status for classes
	function statusFunc(activeClass, disableClass, allClass) {
		let status;

		if (activeClass) {
			status = 1;
		} else if (disableClass) {
			status = 0;
		} else {
			status = null;
		}

		return status;
	}

    // If clicks on settings button then store api_product_id, shop name_id, company_id and module_status in to the products table.
	$("#product_list tbody").on( "click", "a.moduleAtag", function(e){
		e.preventDefault();

		let productApiId         = $(this).data("productid");
		let productListShopId    = $(".productListShopIdClass").val();
		let productListCompanyId = $(".productListCompanyIdClass").val();

		$.ajax({
			url: "/admin/dashboard/product/store",
			dataType: "JSON",
			type: "POST",
			data: {
				productApiId: productApiId,
				productListShopId: productListShopId,
				productListCompanyId: productListCompanyId
			}
		})
		.done(function(data) {
			if(data.productStatus === 'success') {
				$("#moduleModal_"+productApiId).modal('show');
			}
		})
		.fail(function(data) {
			if(data.responseJSON.productStatus === 'failure') {
				$("#moduleModal_"+productApiId).modal('hide');
			}
		});
	});

	// Storing module settings
	$("#product_list tbody").on( "click", "button.saveModuleDetails", function(e){
		e.preventDefault();
		console.log('clicked');
	});

});

/**
 * User: Sarath TS
 * Date: 17.10.2019
 * Created for: adminModuleList
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
	let moduleList = $("#module_list").DataTable({
		lengthMenu: [10, 25, 50, 75, 100],
		order: [1, "desc"],
		processing: true,
		serverSide: true,
		ajax: {
			url: "/admin/dashboard/module/list/datatables",
			dataType: "json",
			type: "POST"
		},
		deferRender: true,
		columns: [
			{ data: "hash" },
			{ data: "module" },
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
		moduleList
			.columns(i)
			.search(v)
			.draw();
	});

	/* Create module */
	$("button.createModule").on("click", function(e) {
		e.preventDefault();

		let module_name = $("#module").val();

		$.ajax({
			url: "/admin/dashboard/module/store",
			dataType: "JSON",
			type: "POST",
			data: {
				module: module_name
			}
		})
		.done(function(result) {
			if (result.moduleStatus === "success") {
					$("#createModuleModal").modal("hide"); // It hides the modal

					moduleList.ajax.reload(null, false); //Reload data on table

					$(".responseModuleMessage").html(
						'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
						result.message +
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

					$(".responseModuleMessage")
					.show()
					.delay(5000)
					.fadeOut();
				}
		})
		.fail(function(data) {
			if (data.responseJSON.moduleStatus === "failure") {
				$(".moduleValidationAlert").html(
					'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
					data.responseJSON.message +
					'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
			}

			if (data.status === 422) {
				$.each(data.responseJSON.errors, function(key, val) {
					$(".moduleValidationAlert").html(
						"<p class='alert alert-danger'>" + val + "</p>"
						);
				});
			}
		});
	});

	/* Clearing data of create module modal fields */
	$("#createModuleModal").on("hidden.bs.modal", function(e) {
		
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

	/* Edit module details */
	$("#module_list tbody").on("click", "a.editModule", function(e) {
		var moduleid = $(this).data("moduleid");

		$(".updateModule_" + moduleid).on("click", function(e) {
			e.preventDefault();

			var module_name = $("#module_" + moduleid).val();

			$.ajax({
				url: "/admin/dashboard/module/update",
				dataType: "JSON",
				type: "PUT",
				data: {
					module: module_name,
					moduleid: moduleid
				}
			})
				.done(function(result) {
					if (result.moduleStatusUpdate === "success") {
						$("#editModuleModal_" + moduleid).modal("hide"); // It hides the modal

						moduleList.ajax.reload(null, false); //Reload data on table

						$(".responseModuleMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
								result.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);

						$(".responseModuleMessage")
							.show()
							.delay(5000)
							.fadeOut();
					}
				})
				.fail(function(data) {
					if (data.responseJSON.moduleStatusUpdate === "failure") {
						$(".moduleUpdateValidationAlert").html(
							'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
								data.responseJSON.message +
								'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
					}

					if (data.status === 422) {
						$.each(data.responseJSON.errors, function(key, val) {
							$(".moduleUpdateValidationAlert").html(
								"<p class='alert alert-danger'>" + val + "</p>"
							);
						});
					}
				});
		});
	});

	/* module status changing functionality */
	$("#module_list tbody").on("change", "input.buttonStatus", function(e) {
		e.preventDefault();

		var newStatus = "";

		var moduleStatusId = $(this)
			.parent()
			.data("modulestatusid");

		if ($(this).is(":checked") === true) {
			newStatus = "yes";
		} else {
			newStatus = "no";
		}

		$.ajax({
			url: "/admin/dashboard/module/status/update",
			dataType: "JSON",
			type: "POST",
			data: { newStatus: newStatus, moduleStatusId: moduleStatusId }
		})
			.done(function(result) {
				moduleList.ajax.reload(null, false);
			})
			.fail(function(data) {
				moduleList.ajax.reload(null, false);

				if (data.responseJSON.moduleStatusChange === "failure") {
					$(".responseModuleMessage").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
							data.responseJSON.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				}

				$(".responseModuleMessage")
					.show()
					.delay(5000)
					.fadeOut();
			});
	});

	/* Delete module functionality */
	$("#module_list tbody").on("click", "a.deleteModule", function(e) {
		e.preventDefault();
		var deletemoduleid = $(this).data("deletemoduleid");
		var r = confirm("Are you sure you want to remove the module?");
		if (r == true) {
			$.ajax({
				url: "/admin/dashboard/module/delete/" + deletemoduleid,
				dataType: "JSON",
				type: "DELETE"
			})
			.done(function(result) {
				if (result.deletedModuleStatus === "success") {
						$("#editModuleModal_" + deletemoduleid).modal("hide"); // It hides the modal

						moduleList
						.row($(this).parents("tr"))
						.remove()
						.draw();

						$(".responseModuleMessage").html(
							'<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> ' +
							result.message +
							'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
							);

						$(".responseModuleMessage")
						.show()
						.delay(5000)
						.fadeOut();
					}
			})
			.fail(function(data) {
				if (data.responseJSON.deletedModuleStatus === "failure") {
					$(".moduleUpdateValidationAlert").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
						data.responseJSON.message +
						'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
						);
				}
			});
		}
	});


});	
/**
 * User: Sarath TS
 * Date: 02.12.2019
 * Created for: orderList
 */

$(function() {
	/* Checking for the CSRF token */
	$.ajaxSetup({
		headers: {
			"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
		}
	});

	let orderList = '';
	let orderCompany = '';
    let orderListDateRange = '';

	// On page load this function works
	orderDatatableFunc(orderCompany, orderListDateRange);

	/* Datatable script */
	function orderDatatableFunc(orderCompany, orderListDateRange) {
		orderList = $( "#order_list" ).DataTable({
			lengthMenu: [10, 25, 50, 75, 100],
			order: [1, "desc"],
			processing: true,
			serverSide: true,
			ajax: {
				url: "/admin/dashboard/order/list/datatables",
				dataType: "json",
				type: "POST",
				data: {
					orderCompany: orderCompany, 
					orderListDateRange: orderListDateRange
				}
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
	}

	/* Date range script */
	$( "#orderListDateRange" ).daterangepicker({
        autoUpdateInput: false,
        ranges: {
            'Letzten 7 Tage': [moment().subtract(7, 'days'), moment()],
            'Letzten 30 Tage': [moment().subtract(30, 'days'), moment()],
            'Dieser Monat': [moment().startOf('month'), moment().endOf('month')],
            'Letzter Monat': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        locale: {
            format: 'DD.MM.YYYY',
            applyLabel: "Bestätigen",
            cancelLabel: "Löschen",
            daysOfWeek: [
                "So",
                "Mo",
                "Di",
                "Mi",
                "Do",
                "Fr",
                "Sa"
            ],
        }
    });

    $( "#orderListDateRange" ).on("apply.daterangepicker", function(ev, picker) {
        $(this).val(picker.startDate.format('DD.MM.YYYY') + '-' + picker.endDate.format('DD.MM.YYYY'));
    });

    $( "#orderListDateRange" ).on("cancel.daterangepicker", function(ev, picker) {
        let data = $(this).val("");
        orderList.destroy();
        orderDatatableFunc(orderCompany, orderListDateRange);
    });

    /* Generate order list */
    $( "#generateOrders" ).on("click", function(e) {
    	e.preventDefault();

    	orderCompany = $( "#orderCompany" ).val();
    	orderListDateRange = $( "#orderListDateRange" ).val();

    	if(orderCompany !== '' && orderListDateRange !== '') {
    		orderList.destroy();
    		orderDatatableFunc(orderCompany, orderListDateRange);
    	}
    	else {
            $('.alertMsg').html('<div class="alert alert-warning alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button> Leere Felder bitte ausfüllen</div>');
        }
    	
    });

});	