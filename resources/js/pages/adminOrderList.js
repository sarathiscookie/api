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