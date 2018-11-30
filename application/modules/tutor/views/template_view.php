<!-- Main content -->
<div class="content-wrapper">

<!-- Page header -->
<div class="page-header page-header-default">
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo base_url() ?>"><i class="icon-home2 position-left"></i> Trang chủ</a></li>
			<li class="active">Quản lý mẫu email và SMS</li>
			<li class="active">Danh sách mẫu</li>
		</ul>
	</div>
</div>
<!-- /page header -->


<!-- Content area -->
<div class="content">
<!-- Basic responsive configuration -->
<div class="panel panel-flat">
	<div class="panel-body">
		<button style="margin-left: 10px" class="btn btn-success btn-xs" onclick="add_template()"><i
				class="icon-user-plus"></i> Thêm mẫu
		</button>
		<button class="btn btn-default btn-xs" data-action="reload"><i
				class="glyphicon glyphicon-refresh"></i> Tải lại trang
		</button>
	</div>

	<table class="table datatable-responsive">
		<thead>
		<tr>
			<th>STT</th>
			<th>Mã mẫu</th>
			<th>Loại mẫu</th>
			<th>Tên mẫu</th>
			<th>Tiêu đề</th>
			<th class="text-center">Trạng thái</th>
			<th class="text-center">Actions</th>
		</tr>
		</thead>
		<tbody>
		<?php
			$stt = 1;
			foreach ($list_template as $template) {
				$enabled = $template->enable_yn;
				if ($enabled == 'Y') {
					$enabled = '<i class="icon-unlocked"></i>';
				} else {
					$enabled = '<i class=" icon-lock"></i>';
				}
				/*$avatar = $admin_account->Avatar;
				if ($avatar != '') {
					$avatar = '<img src = "' . base_url($avatar) . '" class = "img-circle img-sm" alt = "">';
				}*/
				echo '<tr>';
				echo '<td>' . $stt . '</td>';
				echo '<td>' . $template->ma_mau . '</td>';
				echo '<td>' . $template->loai_mau . '</td>';
				echo '<td>' . $template->ten_mau . '</td>';
				echo '<td>' . $template->tieu_de . '</td>';
//				echo '<td>' . $template->noi_dung . '</td>';
				echo '<td class="text-center">' . $enabled . '</td>';
				echo '<td class="text-center" style="width: 200px">
						<a title="Chi tiết nội dung" href="'.base_url('tutor/template/detail/').'/'.$template->id.'" target="_blank"><i class="icon-grid3"></i></a> &nbsp;
						<a title="Sửa mẫu" class="text-center" onclick="edit_template(' . "'" . $template->id . "'" . ')"><i class="glyphicon glyphicon-pencil"></i></a> &nbsp;
						<a title="Bật/tắt mẫu" class="text-center" onclick="lock_template(' . "'" . $template->id . "'" . ')"><i class="glyphicon glyphicon-lock"></i></a> &nbsp;
						<a title="Xóa mẫu" class="text-center" onclick="delete_template_confirm(' . "'" . $template->id . "'" . ')"><i class="icon-trash"></i></a></td>';
				// echo '<td>' . $template->noi_dung . '</td>';
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

	//Them template moi
	function add_template() {
		save_method = 'add';
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string
		$('#modal_form').modal('show'); // show bootstrap modal
		$('.modal-title').text('Thêm mẫu email'); // Set Title to Bootstrap modal title
	}

	function edit_template(id) {
		save_method = 'update';
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string

		//Ajax Load data from ajax
		$.ajax({
			url: "<?php echo site_url('tutor/template/ajax_edit/')?>/" + id,
			type: "GET",
			dataType: "JSON",
			success: function (data) {

				$('[name="IdTemplate"]').val(data.id);
				$('[name="nameTemplate"]').val(data.ten_mau);
				$('[name="typeTemplate"]').val(data.loai_mau);
				$('[name="codeTemplate"]').val(data.ma_mau);
				$('[name="title"]').val(data.tieu_de);
				CKEDITOR.instances['editor_noidung'].setData(data.noi_dung);
				$('[name="Enabled"]').val(data.enable_yn);
				$('#modal_form').modal('show'); // show bootstrap modal when complete loaded
				$('.modal-title').text('Sửa thông tin mẫu'); // Set title to Bootstrap modal title

			},
			error: function (jqXHR, textStatus, errorThrown) {
				alert('Error get data from ajax');
			}
		});
	}

	function CKupdate(){
  		for ( instance in CKEDITOR.instances )
  		CKEDITOR.instances[instance].updateElement();
 	}

	function reload_table() {
		location.reload();
	}

	function save() {
		$('#btnSave').text('saving...'); //change button text
		$('#btnSave').attr('disabled', true); //set button disable
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string
		var url;

		if (save_method == 'add') {
			url = "<?php echo site_url('tutor/template/ajax_add')?>";
		} else {
			url = "<?php echo site_url('tutor/template/ajax_update')?>";
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

	function lock_template(id) {
		// ajax delete data to database
		$.ajax({
			url: "<?php echo site_url('tutor/template/ajax_lock')?>/" + id,
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

	function delete_template_confirm(id) {
		$('#form2')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('[name="IdTemplate"]').val(id);
		$('.help-block').empty(); // clear error string
		$('#modal_form_confirm').modal('show'); // show bootstrap modal
		$('.modal-title').text('Xác nhận xoá biểu mẫu'); // Set Title to Bootstrap modal title
	}
	function delete_template() {
		$('#btnDelete').text('Deleting...'); //change button text
		$('#btnDelete').attr('disabled', true); //set button disable

		// ajax adding data to database
		$.ajax({
			url: "<?php echo site_url('tutor/template/ajax_delete')?>",
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
					<input type="hidden" value="" name="IdTemplate"/>

					<div class="form-body">
						<div class="panel-body">
							<div class="form-group">
								<label class="control-label col-md-3">Mã mẫu <span
										class="text-danger">(*)</span></label>

								<div class="col-md-6">
									<input name="codeTemplate" placeholder="Mã mẫu"
											class="form-control"
											type="text">
									<span class="help-block"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-3">Loại mẫu</label>
								<div class="col-md-6">
									<select name="typeTemplate" class="form-control">
										<option value="email">Email</option>
										<option value="sms">SMS</option>
									</select>

									<span class="help-block"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-3">Tên mẫu <span
										class="text-danger">(*)</span></label>

								<div class="col-md-6">
									<input name="nameTemplate" placeholder="Tên mẫu"
											class="form-control"
											type="text">
									<span class="help-block"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-3">Tiêu đề <span class="text-danger">(*)</span></label>

								<div class="col-md-6">
									<input name="title" placeholder="Tiêu đề"
											class="form-control"
											type="text">
									<span class="help-block"></span>
								</div>
							</div>
							<div class="form-group">
								<label class="control-label col-md-3">Trạng thái</label>

								<div class="col-md-6">
									<select name="Enabled" class="form-control">
										<option value="Y">Bật</option>
										<option value="N">Tắt</option>
									</select>
									<input type="hidden" name="">
								</div>
							</div>
							<div class="form-group">
								
									<input type="hidden" name="">
							</div>
							<div class = "form-group">
								<label class = "control-label col-md-3">Nội dung <span class="text-danger">(*)</span></label>
								<div class="col-lg-12">
									<textarea name="editor_noidung"  class = "ckeditor" rows="50" cols="160"></textarea>
									<input type="hidden" name="content_hide">
								</div>
								<span class = "help-block"></span>
							</div>
							<div class="modal-footer">
								<div align="left">
									<span class="label label-danger">Chú ý</span> <span class="text-danger">(*)</span> là thông tin bắt
									buộc điền đầy đủ, không được bỏ trống.
								</div>
								<button type="button" id="btnSave" onclick="CKupdate();save()" class="btn btn-primary"><i
										class="glyphicon glyphicon-floppy-disk"></i> Save
								</button>
								<button type="button" class="btn btn-danger" data-dismiss="modal"><i
										class="glyphicon glyphicon-remove"></i> Cancel
								</button>
							</div>
						</div>
					</div>
				</form>
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
					<input type="hidden" value="" name="IdTemplate"/>

					<div class="form-body">
						<div class="form-group">
							<label class="control-label col-md-9"><h5>Bạn có chắc chắn muốn xoá biểu mẫu này?</h5>
							</label>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" id="btnDelete" onclick="delete_template()" class="btn btn-primary"><i
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
