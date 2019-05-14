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

	/* Updating user status */
	$("#datatable_list tbody").on("click", "button.buttonStatus", function(e) {
		e.preventDefault();
		var userId = $(this)
			.parent()
			.data("userid");
		var newStatus = $(this).data("status");
		var oldStatus = $(this)
			.parent()
			.data("userstatus");

		if (newStatus === oldStatus) {
			datatableList.ajax.reload(null, false);
		} else {
			$.ajax({
				url: "/admin/dashboard/manager/status/update",
				dataType: "JSON",
				type: "POST",
				data: { newStatus: newStatus, userId: userId }
			})
				.done(function(result) {
					if (result.status === "success") {
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

					datatableList.ajax.reload(null, false);
				})
				.fail(function() {
					$(".responseMessage").html(
						'<div class="alert alert-danger alert-dismissible fade show" role="alert"><i class="icon fa fa-check-circle"></i> Something went wrong! <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
					);

					$(".responseMessage")
						.show()
						.delay(5000)
						.fadeOut();

					datatableList.ajax.reload(null, false);
				});
		}
	});
});
