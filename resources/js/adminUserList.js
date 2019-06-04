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

		var name = $("#name").val();
		var email = $("#email").val();
		var username = $("#username").val();
		var phone = $("#phone").val();
		var street = $("#street").val();
		var city = $("#city").val();
		var country = $("#country").val();
		var company = $("#company").val();
		var zip = $("#zip").val();
		var password = $("#password").val();
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
				company: company,
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
			var name = $("#name_" + userid).val();
			var phone = $("#phone_" + userid).val();
			var company = $("#company_" + userid).val();
			var street = $("#street_" + userid).val();
			var city = $("#city_" + userid).val();
			var country = $("#country_" + userid).val();
			var zip = $("#zip_" + userid).val();

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
					company: company,
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
