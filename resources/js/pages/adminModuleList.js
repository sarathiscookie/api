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
				$(".companyValidationAlert").html(
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


});	