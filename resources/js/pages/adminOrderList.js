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

	let orderList;

	// On page load this function works
	orderDatatableFunc();

	/* Datatable scripts */
	function orderDatatableFunc() {
		orderList = $( "#order_list" ).DataTable({
			lengthMenu: [10, 25, 50, 75, 100],
			order: [1, "desc"],
			processing: true,
			serverSide: true,
			ajax: {
				url: "/admin/dashboard/order/list/datatables",
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
	}

});	