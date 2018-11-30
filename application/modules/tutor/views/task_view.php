<!-- Main content -->
<div class = "content-wrapper">

<!-- Page header -->
<div class = "page-header page-header-default">
	<div class = "breadcrumb-line">
		<ul class = "breadcrumb">
			<li><a href = "<?php echo base_url() ?>"><i class = "icon-home2 position-left"></i> Trang chủ</a></li>
			<li class = "active">Quản lý mẫu email và SMS</li>
			<li class = "active">Danh sách chức năng auto</li>
		</ul>
	</div>
</div>
<!-- /page header -->


<!-- Content area -->
<div class = "content">
<!-- Basic responsive configuration -->
<div class = "panel panel-flat">
	<div class = "panel-body">
		<button style = "margin-left: 10px" class = "btn btn-success btn-xs" onclick = "add_task()"><i
				class = "glyphicon glyphicon-plus-sign"></i> Thêm chức năng
		</button>
		<button class = "btn btn-default btn-xs" data-action = "reload"><i
				class = "glyphicon glyphicon-refresh"></i> Tải lại trang
		</button>
	</div>

	<table class = "table datatable-responsive">
		<thead>
		<tr>
			<th>STT</th>
			<th>Tên module</th>
			<th>Tên mẫu</th>
			<th>Ngày gửi trong tuần</th>
			<th>Thời gian gửi</th>
			<th class="text-center">Actions</th>
		</tr>
		</thead>
		<tbody>
		<?php
			$stt = 1;
			foreach ($list_task as $task) {
				$module = get_dictionary_by_id($task->id_module);
				echo '<tr>';
				echo '<td>' . $stt . '</td>';
				echo '<td>' . $module->code . '-' . $module->name . '</td>';
				echo '<td>' . get_name_template_by_id($task->id_template) . '</td>';
				if($task->ngay_gui == 8)
					echo '<td>Chủ nhật</td>';
				else
					echo '<td>Thứ ' . $task->ngay_gui . '</td>';
				echo '<td>' . $task->gio . ':'. $task->phut . ':00' . '</td>';
				echo '<td class="text-center" style="width: 200px"><a title="Sửa chức năng" onclick="edit_task(' . "'" . $task->id . "'" . ')"><i class="glyphicon glyphicon-pencil"></i></a> &nbsp;
				<a title="Xóa chức năng" onclick="delete_task_confirm(' . "'" . $task->id . "'" . ')"><i class="icon-trash"></i></a></td>';
				echo '</tr>';
				$stt++;
			}
		?>
		</tbody>
	</table>
</div>
<!-- /basic responsive configuration -->

<script type = "text/javascript">

	var save_method; //for save method string
	var table;

	//Them osp moi
	function add_task() {
		save_method = 'add';
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string
		$('#modal_form').modal('show'); // show bootstrap modal
		$('.modal-title').text('Thêm chức năng'); // Set Title to Bootstrap modal title
		$('[name="hours"]').attr('disabled', false);
		$('[name="minutes"]').attr('disabled', false);
		$('[name="idModule"]').attr('disabled', false);
		$('[name="idModule"]').attr('disabled', false);
	}

	function edit_task(id) {
		save_method = 'update';
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string
		$('[name="hours"]').attr('disabled', true);
		$('[name="minutes"]').attr('disabled', true);
		$('[name="idModule"]').attr('disabled', true);
		$('[name="idModule"]').attr('disabled', true);

		//Ajax Load data from ajax
		$.ajax({
			url: "<?php echo site_url('tutor/task/ajax_edit/')?>/" + id,
			type: "GET",
			dataType: "JSON",
			success: function (data) {

				$('[name="idTask"]').val(data.id);
				$('[name="idTemplate"]').select2();
				$('[name="idTemplate"]').val(data.id_template).trigger("change");
				$('[name="idModule"]').select2();
				$('[name="idModule"]').val(data.id_module).trigger("change");
				$('[name="dateOfWeek"]').select2();
				$('[name="dateOfWeek"]').val(data.ngay_gui).trigger("change");
				$('[name="hours"]').select2();
				$('[name="hours"]').val(data.gio).trigger("change");
				$('[name="minutes"]').select2();
				$('[name="minutes"]').val(data.phut).trigger("change");
				$('#modal_form').modal('show'); // show bootstrap modal when complete loaded
				$('.modal-title').text('Sửa thông tin chức năng'); // Set title to Bootstrap modal title

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
			url = "<?php echo site_url('tutor/task/ajax_add')?>";
		} else {
			url = "<?php echo site_url('tutor/task/ajax_update')?>";
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
				$('#btnSave').text('Save'); //change button text
				$('#btnSave').attr('disabled', false); //set button enable

			}
		});
	}

	function delete_task_confirm(id) {
		$('#form2')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('[name="idTask"]').val(id);
		$('.help-block').empty(); // clear error string
		$('#modal_form_confirm').modal('show'); // show bootstrap modal
		$('.modal-title').text('Xác nhận xoá chức năng'); // Set Title to Bootstrap modal title
	}
	function delete_task(id) {
		$('#btnDelete').text('Deleting...'); //change button text
		$('#btnDelete').attr('disabled', true); //set button disable

		// ajax adding data to database
		$.ajax({
			url: "<?php echo site_url('tutor/task/ajax_delete')?>",
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
<div class = "modal fade" id = "modal_form" role = "dialog">
	<div class = "modal-dialog">
		<div class = "modal-content">
			<div class = "modal-header">
				<button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close"><span
						aria-hidden = "true">&times;</span></button>
				<h3 class = "modal-title">Person Form</h3>
			</div>
			<div class = "modal-body form">
				<form action = "#" id = "form" class = "form-horizontal">
					<input type = "hidden" value = "" name = "idTask"/>

					<div class = "form-body">
						<div class = "panel-body">
							<div class = "col-md-12">
								<fieldset>
									<div class = "form-group">
										<label class = "control-label col-md-4">Tên module <span class = "text-danger">(*)</span></label>
										<div class = "col-md-8">
											<select name = "idModule" class = "select-size-sm">
												<option value = "">--Chọn tên module--</option>
												<?php
												foreach ($list_module as $module) {
													echo '<option value = "' . $module->id . '">' . $module->name . '</option>';
												}?>
											</select>
											<input type = "hidden" name = "ten_module_hide">
											<span class = "help-block"></span>
										</div>
									</div>
									<div class = "form-group">
										<label class = "control-label col-md-4">Tên mẫu <span class = "text-danger">(*)</span></label>
										<div class = "col-md-8">
											<select name = "idTemplate" class = "select-size-sm">
												<option value = "">--Chọn tên mẫu--</option>
												<?php
												foreach ($list_template as $template) {
													echo '<option value = "' . $template->id . '">' .$template->ten_mau . '</option>';
												}?>
											</select>
											<input type = "hidden" name = "ten_mau_hide">
											<span class = "help-block"></span>
										</div>
									</div>
									<div class = "form-group">
										<label class = "control-label col-md-4">Ngày gửi trong tuần</label>
										<div class = "col-md-8">
											<select name = "dateOfWeek" class = "form-control">
												<?php 
													for($i = 2; $i <= 7; $i++) {
														echo '<option value = ' . $i . '> Thứ ' . $i . '</option>';
													}
												?>
												<option value = "8">Chủ Nhật</option>
											</select>
											<!-- <span class = "help-block"></span> -->
											<input type = "hidden" name = "ten_ngay_hide">
										</div>
									</div>
									<div class = "form-group">
										<label class = "control-label col-md-4">Giờ <span class = "text-danger">(*)</span></label>
										<div class = "col-md-8">
											<select name = "hours" class = "form-control">
												<option value = "">--Chọn giờ--</option>
												<?php 
													for($i = 0; $i <= 23; $i++) {
														if($i <= 9)
															echo '<option value = 0' . $i . '>' . $i . '</option>';
														else
															echo '<option value = ' . $i . '>' . $i . '</option>';
													}
												?>
											</select>
											<input type = "hidden" name = "ten_gio_hide">
											<span class = "help-block"></span>
										</div>
									</div>
									<div class = "form-group">
										<label class = "control-label col-md-4">Phút <span class = "text-danger">(*)</span></label>
										<div class = "col-md-8">
											<select name = "minutes" class = "form-control">
												<option value = "">--Chọn phút--</option>
												<?php 
													for($i = 0; $i <= 59; $i++) {
														if($i <= 9)
															echo '<option value = 0' . $i . '>' . $i . '</option>';
														else
															echo '<option value = ' . $i . '>' . $i . '</option>';
													}
												?>
											</select>
											<input type = "hidden" name = "ten_phut_hide">
											<span class = "help-block"></span>
										</div>
									</div>
								</fieldset>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class = "modal-footer">
				<div align = "left">
					<span class = "label label-danger">Chú ý</span> <span class = "text-danger">(*)</span> là thông tin bắt
					buộc điền đầy đủ, không được bỏ trống.
				</div>
				<button type = "button" id = "btnSave" onclick = "save()" class = "btn btn-primary"><i
						class = "glyphicon glyphicon-floppy-disk"></i> Save
				</button>
				<button type = "button" class = "btn btn-danger" data-dismiss = "modal"><i
						class = "glyphicon glyphicon-remove"></i> Cancel
				</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
</div>

<!-- Inline form -->
<div class = "modal fade" id = "modal_form_confirm" title = "Inline form">
	<div class = "modal-dialog">
		<div class = "modal-content">
			<div class = "modal-header">
				<button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close"><span
						aria-hidden = "true">&times;</span></button>
				<h3 class = "modal-title">Person Form</h3>
			</div>
			<div class = "modal-body form">
				<form action = "#" id = "form2" class = "form-horizontal">
					<input type = "hidden" value = "" name = "idTask"/>

					<div class = "form-body">
						<div class = "form-group">
							<label class = "control-label col-md-9"><h5>Bạn có chắc chắn muốn xóa chức năng này?</h5>
							</label>
						</div>
					</div>
				</form>
			</div>
			<div class = "modal-footer">
				<button type = "button" id = "btnDelete" onclick = "delete_task()"
				        class = "btn btn-primary"><i
						class = "icon-checkmark4"></i> Ok
				</button>
				<button type = "button" class = "btn btn-danger" data-dismiss = "modal"><i
						class = "glyphicon glyphicon-remove"></i> Cancel
				</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
</div>
<!-- /inline form -->