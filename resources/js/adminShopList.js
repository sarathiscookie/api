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
});
