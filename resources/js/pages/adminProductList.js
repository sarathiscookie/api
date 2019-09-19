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

	let shopId = '';
	let companyId = '';

	// Setting href attribute when changing shop
	$( "#product_shop" ).on( "change", function() {
	    shopId = $( this ).val();
		$( ".getProducts" ).attr( "href", "/admin/dashboard/product/list/"+shopId+"/"+companyId);
	});

	// Setting href attribute when select a company
	$( "#product_company" ).on( "change", function() {
	    companyId = $( this ).val();
		$( ".getProducts" ).attr( "href", "/admin/dashboard/product/list/"+shopId+"/"+companyId);
	});

	// Alert message if user forgot to fill the fields
	$( ".getProducts" ).on( "click", function (e){
		if( ($( "#product_shop" ).val() == '') || ($( "#product_company" ).val() == '') ) {
			e.preventDefault();
			alert('Please fill the data');
		}
	});

	/* Datatable scripts */
	let productList = $("#product_list").DataTable({
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
					let productListTableInfo = $("#product_list").DataTable().page.info();
					return productListTableInfo.page + 1;
				}
			},
			dataSrc: function(result) {
				$( ".shop_categories_options" ).remove();
				if(result.categoryDetails.length > 0) {
					if(result.categoryDetails.length > 1) {
						$( "#shopCategoriesSelect" ).append('<option class="shop_categories_options" value="0">All Categories</option>'); 
					}
					for(let i = 0; i < result.categoryDetails.length; i++) {
						$( "#shopCategoriesSelect" ).append('<option class="shop_categories_options" value="'+ result.categoryDetails[i].shop_category_id +'">'+ result.categoryDetails[i].name +'</option>');
					}
				}
				else  {
					$( ".noCategoriesFound" ).remove();
					$( "#shopCategoriesSelect" ).append('<option class="shop_categories_options" value="">Categories are not available for this shop</option>'); 
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

	// Datatable search min 3 char length needed
    $('input[type=search]') .unbind() // Unbind previous default bindings
        .bind("input", function(e) { // Bind our desired behavior
            // If the length is 3 or more characters, or the user pressed ENTER, search
            if(this.value.length >= 3 || e.keyCode == 13) {
            	productList.search(this.value).draw();
            }

            if(this.value == "") {
            	productList.search("").draw();
            }
            return;
        });


});	
