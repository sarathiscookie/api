/**
 * User: Sarath TS
 * Date: 16.08.2019
 * Created for: productList
 */

$(function () {
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
	$("#product_shop").on("change", function () {
		shopId = $(this).val();
		$(".getProducts").attr(
			"href",
			"/admin/dashboard/product/list/" + shopId + "/" + companyId
		);
	});

	// Setting href attribute when select a company
	$("#product_company").on("change", function () {
		companyId = $(this).val();
		$(".getProducts").attr(
			"href",
			"/admin/dashboard/product/list/" + shopId + "/" + companyId
		);
	});

	// Alert message if user forgot to fill the fields
	$(".getProducts").on("click", function (e) {
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
					pageActive: function () {
						let productListTableInfo = $("#product_list")
							.DataTable()
							.page.info();
						return productListTableInfo.page + 1;
					},
					productCategoryId: productCategoryId,
					visible: visible,
					available: available
				},
				dataSrc: function (result) {
					$(".shop_categories_options").remove();

					if (result.categoryDetails.length > 0) {
						if (result.categoryDetails.length > 1) {
							$("#shopCategoriesSelect").append(
								'<option class="shop_categories_options" value="allCategories">All Categories</option>'
							);
						}
						for (let i = 0; i < result.categoryDetails.length; i++) {
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
		.bind("input", function (e) {
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
	function shopCategoriesFun(filterShopCategoryId) {

		let shopCategoryIdFilter;
		if ((filterShopCategoryId !== null) || (filterShopCategoryId !== '')) {
			shopCategoryIdFilter = filterShopCategoryId;
		}
		else {
			shopCategoryIdFilter = null;
		}

		return shopCategoryIdFilter;
	}

	//Filter for category
	$("#shopCategoriesSelect").change(function () {

		let categoryId = $("#shopCategoriesSelect").val();

		if ((categoryId !== null) || (categoryId !== '')) {

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
	$(".visibleActive").on("click", function (e) {
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

	$(".visibleDisable").on("click", function (e) {
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

	$(".visibleAll").on("click", function (e) {
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
	$(".availableActive").on("click", function (e) {
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

	$(".availableDisable").on("click", function (e) {
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

	$(".availableAll").on("click", function (e) {
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
	$("#product_list tbody").on("click", "a.moduleAtag", function (e) {
		e.preventDefault();

		let productApiId = $(this).data("productid");
		let productListShopId = $(".productListShopIdClass").val();
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
			.done(function (data) {
				if (data.productStatus === 'success') {
					$("#moduleModal_" + productApiId).modal('show');
				}
			})
			.fail(function (data) {
				if (data.responseJSON.productStatus === 'failure') {
					$("#moduleModal_" + productApiId).modal('hide');
				}
			});
	});

	// Add modules for products
	$("#product_list tbody").on("click", "button.saveModuleDetails", function (e) {
		e.preventDefault();

		let product_id = $(this).data("addmoduleproductid");
		let module_id = $("#module_id_" + product_id).val();

		$.ajax({
			url: "/admin/dashboard/product/add/module",
			dataType: "JSON",
			type: "POST",
			data: {
				product_id: product_id,
				module_id: module_id,
			}
		})
			.done(function (result) {
				if (result.moduleSettingStatus === 'success') {

					$(".addModuleSettingsStatus_" + product_id).html('<div class="alert alert-success alert-dismissible fade show" role="alert">' + result.message + '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>');

					setTimeout(function () {
						$("#moduleModal_" + product_id).modal('hide');
						$('.modal-backdrop').remove();
					}, 2000);
				}
			})
			.fail(function (data) {
				$(".addModuleSettingsStatus_" + product_id).html(
					'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +
					data.responseJSON.message +
					'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
				);
			});

		productList.ajax.reload(null, false);

	});

	// Delete modules from products
	$("#product_list tbody").on("click", "i.module_settings", function (e) {
		e.preventDefault();

		let modulesettingsid = $(this).data('modulesettingsid');

		let con = confirm("Are you sure you want to remove this module?");

		if (con === true) {
			$( ".module_settings_spinner_" + modulesettingsid ).html('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>&nbsp');

			$.ajax({
				url: "/admin/dashboard/product/delete/module/"+modulesettingsid,
				dataType: "JSON",
				type: "DELETE"
			})
				.done(function (result) {
					if(result.deletedModuleSettingStatus === 'success') {
						setTimeout( function() {
							$( ".module_settings_spinner_" + modulesettingsid ).fadeOut(300);
						}, 1800);

						productList.ajax.reload(null, false);
					}
				})
				.fail(function (data) {
					$( ".module_settings_spinner_" + modulesettingsid ).html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="fas fa-times-circle"></i> ' +data.responseJSON.message +'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);
				});
		}
	});

});
