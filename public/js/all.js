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

/**
 * User: Sarath TS
 * Date: 04.08.2019
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

	/* Passing url to datatable */
	const url = "/admin/dashboard/manager/list/datatables";
	fetchData(url);

	/* Delete manager functionality */
	$("#datatable_list tbody").on("click", "a.deleteEvent", function(e) {
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
});
