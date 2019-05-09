/**
 * User: Sarath TS
 * Date: 05.08.2019
 */

/* Datatable scripts */

let datatableList;

function fetchData(url) {
	datatableList = $("#datatable_list").DataTable({
		lengthMenu: [10, 25, 50, 75, 100],
		order: [[1, "desc"]],
		processing: true,
		serverSide: true,
		ajax: {
			url: url,
			dataType: "json",
			type: "POST"
		},
		deferRender: true,
		columns: [
			{ data: "hash" },
			{ data: "name" },
			{ data: "email" },
			{ data: "created_at" },
			{ data: "active" },
			{ data: "actions" }
		],
		columnDefs: [
			{
				orderable: false,
				targets: [0, 4, 5]
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
	let buttons = new $.fn.dataTable.Buttons(datatableList, {
		buttons: [
			{
				extend: "csv",
				exportOptions: {
					columns: [1, 2, 3, 4]
				}
			},
			{
				extend: "excel",
				exportOptions: {
					columns: [1, 2, 3, 4]
				}
			},
			{
				extend: "pdf",
				orientation: "portrait",
				pageSize: "LEGAL",
				exportOptions: {
					columns: [1, 2, 3, 4]
				}
			}
		]
	})
		.container()
		.appendTo($("#buttons"));
}
