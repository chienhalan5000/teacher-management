<!-- Main content -->
<div class="content-wrapper">

<!-- Page header -->
<div class="page-header page-header-default">
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo base_url() ?>"><i class="icon-home2 position-left"></i> Trang chủ</a></li>
			<li class="active">Quản lý giảng viên</li>
		</ul>
	</div>
</div>
<!-- /page header -->


<!-- Content area -->
<div class="content">
<!-- Basic responsive configuration -->
<div class="panel panel-flat">
	<div class="panel-body">
		<button style="margin-left: 10px" class="btn btn-success btn-xs" onclick="add_teacher()"><i
				class="icon-user-plus"></i> Thêm giảng viên
		</button>
		<button class="btn btn-default btn-xs" data-action="reload"><i
				class="glyphicon glyphicon-refresh"></i> Tải lại trang
		</button>
	</div>

	<table class="table datatable-responsive">
		<thead>
		<tr>
			<th>STT</th>
			<th>Họ và đệm</th>
			<th>Tên</th>
			<th>Tên tài khoản</th>
			<th>Email</th>
			<th>Email cá nhân</th>
			<th>Số điện thoại</th>
			<th>Loại giảng viên</th>
			<th class="text-center">Actions</th>
		</tr>
		</thead>
		<tbody>
		<?php
			$stt = 1;
			foreach ($teachers_list as $teacher) {
				$class='';
				if ($teacher->status_delete_yn == 'y') {
					$class ='class="danger"';
				}
				echo '<tr '.$class.'>';
				echo '<td>' . $stt . '</td>';
				echo '<td>' . $teacher->lastname . '</td>';
				echo '<td>' . $teacher->firstname . '</td>';
				echo '<td>' . $teacher->username . '</td>';
				echo '<td>' . $teacher->email . '</td>';
				echo '<td>' . $teacher->email_person . '</td>';
				echo '<td>' . $teacher->mobile_number . '</td>';
				echo '<td>' . $teacher->role_name . '</td>';
				echo '<td class="text-center"><ul class="icons-list">
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown">
								<i class="icon-menu9"></i>
							</a>

							<ul class="dropdown-menu dropdown-menu-right">
								<li><a onclick="edit_teacher(' . "'" . $teacher->id . "'" . ')"><i class="glyphicon glyphicon-pencil"></i> Sửa </a></li>
								<li><a onclick="lock_teacher(' . "'" . $teacher->id . "'" . ')"><i class="icon-user-lock"></i> Khoá/ mở tài khoản</a></li>
								<li><a onclick="delete_confirm(' . "'" . $teacher->id . "'" . ')"><i class="icon-trash"></i> Xoá tài khoản</a></li>
							</ul>
						</li>
					</ul></td>';
				echo '</tr>';
				$stt++;
			}
		?>
		</tbody>
	</table>
</div>
<!-- /basic responsive configuration -->

<script type="text/javascript">

	var save_method; //for save method string
	var table;

	//Them account moi
	function add_teacher() {
		save_method = 'add';
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string
		$('#modal_form').modal('show'); // show bootstrap modal
		$('.modal-title').text('Thêm tài khoản giảng viên'); // Set Title to Bootstrap modal title
	}

	function edit_teacher(id) {
		save_method = 'update';
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string

		//Ajax Load data from ajax
		$.ajax({
			url: "<?php echo site_url('tutor/teacher/ajax_edit/')?>/" + id,
			type: "GET",
			dataType: "JSON",
			success: function (data) {

				$('[name="IdUser"]').val(data.id);
				$('[name="UserName"]').val(data.username);
				$('[name="LastName"]').val(data.lastname);
				$('[name="FirstName"]').val(data.firstname);
				$('[name="Email"]').val(data.email);
				$('[name="Email_Personal"]').val(data.email_person);
				$('[name="Phone"]').val(data.mobile_number);
				$('[name="Enabled"]').val(data.status_delete_yn);
				$('[name="Permission"]').val(data.role_name);
				$('#modal_form').modal('show'); // show bootstrap modal when complete loaded
				$('.modal-title').text('Sửa thông tin giảng viên'); // Set title to Bootstrap modal title

			},
			error: function (jqXHR, textStatus, errorThrown) {
				alert('Error get data from ajax');
			}
		});
	}

	function reload_table() {
		location.reload();
	}

	function save() {
		$('#btnSave').text('saving...'); //change button text
		$('#btnSave').attr('disabled', true); //set button disable
		var url;

		if (save_method == 'add') {
			url = "<?php echo site_url('tutor/teacher/ajax_add')?>";
		} else {
			url = "<?php echo site_url('tutor/teacher/ajax_update')?>";
		}

		// ajax adding data to database
		$.ajax({
			url: url,
			type: "POST",
			data: $('#form').serialize(),
			dataType: "JSON",
			success: function (data) {

				if (data.status) //if success close modal and reload ajax table
				{
					$('#modal_form').modal('hide');
					reload_table();
				}
				else {
					for (var i = 0; i < data.inputerror.length; i++) {

						$('[name="' + data.inputerror[i] + '"]').parent().parent().addClass('has-error'); //select parent twice to select div form-group class and add has-error class
						$('[name="' + data.inputerror[i] + '"]').next().text(data.error_string[i]); //select span help-block class set text error string
					}
				}
				$('#btnSave').text('Save'); //change button text
				$('#btnSave').attr('disabled', false); //set button enable


			},
			error: function (jqXHR, textStatus, errorThrown) {
				if (save_method == 'add') {
					error = "Thêm mới không thành công";
				} else {
					error = "Cập nhật không thành công";
				}
				alert(error);
				$('#btnSave').text('save'); //change button text
				$('#btnSave').attr('disabled', false); //set button enable

			}
		});
	}

	function lock_teacher(id) {
		// ajax delete data to database
		$.ajax({
			url: "<?php echo site_url('tutor/teacher/ajax_lock')?>/" + id,
			type: "POST",
			dataType: "JSON",
			success: function (data) {
				//if success reload ajax table
				$('#modal_form').modal('hide');
				reload_table();
			},
			error: function (jqXHR, textStatus, errorThrown) {
				alert('Error deleting data');
			}
		});
	}
	function delete_confirm(id) {
		$('#form2')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('[name="IdUser"]').val(id);
		$('.help-block').empty(); // clear error string
		$('#modal_form_confirm').modal('show'); // show bootstrap modal
		$('.modal-title').text('Xác nhận xoá tài khoản'); // Set Title to Bootstrap modal title
	}
	function delete_teacher() {
		$('#btnDelete').text('Deleting...'); //change button text
		$('#btnDelete').attr('disabled', true); //set button disable

		// ajax adding data to database
		$.ajax({
			url: "<?php echo site_url('tutor/teacher/ajax_delete')?>",
			type: "POST",
			data: $('#form2').serialize(),
			dataType: "JSON",
			success: function (data) {

				if (data.status) //if success close modal and reload ajax table
				{
					$('#modal_form_confirm').modal('hide');
					reload_table();
				}
				else {
					alert("Xoá không thành công");
				}
				$('#btnDelete').text('Ok'); //change button text
				$('#btnDelete').attr('disabled', false); //set button enable


			},
			error: function (jqXHR, textStatus, errorThrown) {
				alert("Xoá không thành công");
				$('#btnDelete').text('Ok'); //change button text
				$('#btnDelete').attr('disabled', false); //set button enable

			}
		});
	}

</script>
<!-- Bootstrap modal -->
<div class="modal fade" id="modal_form" role="dialog">
	<div class="modal-dialog modal-full">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
						aria-hidden="true">&times;</span></button>
				<h3 class="modal-title">Person Form</h3>
			</div>
			<div class="modal-body form">
				<form action="#" id="form" class="form-horizontal">
					<input type="hidden" value="" name="IdUser"/>

					<div class="form-body">
						<div class="panel-body">
							<div class="row">
								<div class="col-md-6">
									<fieldset>

										<div class="form-group">
											<label class="control-label col-md-3">Tên đăng nhập <span
													class="text-danger">(*)</span></label>

											<div class="col-md-9">
												<input name="UserName" placeholder="Tên đăng nhập"
													   class="form-control"
													   type="text">
												<span class="help-block"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-3">Họ và đệm <span class="text-danger">(*)</span></label>

											<div class="col-md-9">
												<input name="LastName" placeholder="Họ và đệm"
													   class="form-control"
													   type="text">
												<span class="help-block"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-3">Tên <span
													class="text-danger">(*)</span></label>

											<div class="col-md-9">
												<input name="FirstName" placeholder="Tên riêng"
													   class="form-control"
													   type="text">
												<span class="help-block"></span>
											</div>
										</div>

										<div class="form-group">
											<label class="control-label col-md-3">Loại tài khoản <span
													class="text-danger">(*)</span></label>

											<div class="col-md-9">
												<select name="Permission" class="form-control">
													<option value="GVCM">Giảng viên chuyên môn</option>
													<option value="GVHD">Giảng viên hướng dẫn</option>
												</select>
												<span class="help-block"></span>
											</div>
										</div>
									</fieldset>
								</div>
								<div class="col-md-6">
									<fieldset>
										<div class="form-group">
											<label class="control-label col-md-3">Email <span
													class="text-danger">(*)</span></label>

											<div class="col-md-9">
												<input type="email" name="Email" placeholder="Email"
													   class="form-control">
												<span class="help-block"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-3">Email cá nhân <span
													class="text-danger">(*)</span></label>

											<div class="col-md-9">
												<input type="email" name="Email_Personal" placeholder="Email Personal"
													   class="form-control">
												<span class="help-block"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-3">Điện thoại <span class="text-danger">(*)</span></label>

											<div class="col-md-9">
												<input type="text" name="Phone" placeholder="Điện thoại"
													   class="form-control" maxlength="15">
												<span class="help-block"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-3">Trạng thái</label>

											<div class="col-md-9">
												<select name="Enabled" class="form-control">
													<option value="N">Mở</option>
													<option value="Y">Khoá</option>
												</select>

												<span class="help-block"></span>
											</div>
										</div>
									</fieldset>
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<div align="left">
					<span class="label label-danger">Chú ý</span> <span class="text-danger">(*)</span> là thông tin bắt
					buộc điền đầy đủ, không được bỏ trống.
				</div>
				<button type="button" id="btnSave" onclick="save()" class="btn btn-primary"><i
						class="glyphicon glyphicon-floppy-disk"></i> Save
				</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal"><i
						class="glyphicon glyphicon-remove"></i> Cancel
				</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<!-- Inline form -->
<div class="modal fade" id="modal_form_confirm" title="Inline form">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
						aria-hidden="true">&times;</span></button>
				<h3 class="modal-title">Person Form</h3>
			</div>
			<div class="modal-body form">
				<form action="#" id="form2" class="form-horizontal">
					<input type="hidden" value="" name="IdUser"/>

					<div class="form-body">
						<div class="form-group">
							<label class="control-label col-md-9"><h5>Bạn có chắc chắn muốn xoá tài khoản này?</h5>
							</label>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" id="btnDelete" onclick="delete_teacher()" class="btn btn-primary"><i
						class="icon-checkmark4"></i> Ok
				</button>
				<button type="button" class="btn btn-danger" data-dismiss="modal"><i
						class="glyphicon glyphicon-remove"></i> Cancel
				</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
</div>
<!-- /inline form -->
