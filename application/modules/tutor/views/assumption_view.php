<!-- Main content -->
<div class="content-wrapper">

<!-- Page header -->
<div class="page-header page-header-default">
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo base_url() ?>"><i class="icon-home2 position-left"></i> Trang chủ</a></li>
			<li><a href="<?php echo base_url('tutor/assumption_group') ?>">Từ điển hệ thống</a></li>
			<li class="active"> <?php echo $assumption_group->name; ?></li>
		</ul>
	</div>
</div>
<!-- /page header -->


<!-- Content area -->
<div class="content">
<!-- Basic responsive configuration -->
<div class="panel panel-flat">
	<div class="panel-body">
		<button style="margin-left: 10px" class="btn btn-success btn-xs" onclick="add_assumption()"><i
				class="icon-user-plus"></i> Thêm từ điển
		</button>
		<button class="btn btn-default btn-xs" data-action="reload"><i
				class="glyphicon glyphicon-refresh"></i> Tải lại trang
		</button>
	</div>

	<table class="table datatable-responsive">
		<thead>
		<tr>
			<th>STT</th>
			<th>Code</th>
			<th>Tên</th>
			<th>Mô tả</th>
			<th>Order</th>
			<th>Trạng thái</th>
			<th class="text-center">Actions</th>
		</tr>
		</thead>
		<tbody>
		<?php
			$stt = 1;
			foreach ($list_assumption as $assumption) {
				$enabled = $assumption->enable_yn;
				if ($enabled == 'Y') {
					$enabled = '<i class="icon-unlocked"></i>';
				} else {
					$enabled = '<i class=" icon-lock"></i>';
				}
				echo '<tr>';
				echo '<td>' . $stt . '</td>';
				echo '<td>' . $assumption->code . '</td>';
				echo '<td>' . $assumption->name . '</td>';
				echo '<td>' . $assumption->description . '</td>';
				echo '<td>' . $assumption->sort . '</td>';
				echo '<td>' . $enabled . '</td>';
				echo '<td class="text-center" style="width: 200px">
			<a title="Sửa thông tin" onclick="edit_assumption(' . "'" . $assumption->id . "'" . ')"><i class="glyphicon glyphicon-pencil"></i></a> &nbsp;
			<a title="Xoá từ điển" onclick="delete_assumption_confirm(' . "'" . $assumption->id . "'" . ')"><i class="icon-trash"></i></a></td>';
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

	//Them assumption moi
	function add_assumption() {
		save_method = 'add';
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string
		$('#modal_form').modal('show'); // show bootstrap modal
		$('.modal-title').text('Thêm từ điển'); // Set Title to Bootstrap modal title
	}

	function edit_assumption(id) {
		save_method = 'update';
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string

		//Ajax Load data from ajax
		$.ajax({
			url: "<?php echo site_url('tutor/assumption/ajax_edit/')?>/" + id,
			type: "GET",
			dataType: "JSON",
			success: function (data) {

				$('[name="id"]').val(data.id);
				$('[name="Code"]').val(data.code);
				$('[name="Name"]').val(data.name);
				$('[name="Description"]').val(data.description);
				$('[name="Sort"]').val(data.sort);
				$('[name="Enabled"]').val(data.enable_yn);
				$('#modal_form').modal('show'); // show bootstrap modal when complete loaded
				$('.modal-title').text('Sửa thông tin từ điển'); // Set title to Bootstrap modal title

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
			url = "<?php echo site_url('tutor/assumption/ajax_add')?>";
		} else {
			url = "<?php echo site_url('tutor/assumption/ajax_update')?>";
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

	function delete_assumption_confirm(id) {
		$('#form2')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('[name="id_del"]').val(id);
		$('.help-block').empty(); // clear error string
		$('#modal_form_2').modal('show'); // show bootstrap modal
		$('.modal-title').text('Xác nhận xoá từ điển'); // Set Title to Bootstrap modal title
	}
	function delete_assumption() {
		$('#btnDelete').text('Deleting...'); //change button text
		$('#btnDelete').attr('disabled', true); //set button disable

		// ajax adding data to database
		$.ajax({
			url: "<?php echo site_url('tutor/assumption/ajax_delete')?>",
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
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
						aria-hidden="true">&times;</span></button>
				<h3 class="modal-title">Person Form</h3>
			</div>
			<div class="modal-body form">
				<form action="#" id="form" class="form-horizontal">
					<input type="hidden" value="" name="id"/>
					<input type="hidden" value="<?php echo $assumption_group->id ?>" name="categories_id"/>

					<div class="form-body">
						<div class="form-group">
							<label class="control-label col-md-3">Code</label>

							<div class="col-md-9">
								<input name="Code" placeholder="Tên viết tắt" class="form-control"
									   type="text">
								<span class="help-block"></span>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3">Tên</label>

							<div class="col-md-9">
								<input name="Name" placeholder="Tên đầy đủ" class="form-control"
									   type="text">
								<span class="help-block"></span>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3">Mô tả</label>

							<div class="col-md-9">
								<textarea name="Description" placeholder="Mô tả chi tiết"
										  class="form-control"></textarea>
								<span class="help-block"></span>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3">Thứ tự hiển thị</label>

							<div class="col-md-9">
								<input type="number" name="Sort" placeholder="Thứ tự hiển thị trên danh sách"
									   class="form-control">
								<span class="help-block"></span>
							</div>
						</div>
						<div class="form-group">
							<label class="control-label col-md-3">Trạng thái sử dụng</label>

							<div class="col-md-9">
								<select name="Enabled" class="form-control">
									<option value="Y">Mở</option>
									<option value="N">Khoá</option>
								</select>

								<span class="help-block"></span>
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
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
<div class="modal fade" id="modal_form_2" title="Inline form">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
						aria-hidden="true">&times;</span></button>
				<h3 class="modal-title">Person Form</h3>
			</div>
			<div class="modal-body form">
				<form action="#" id="form2" class="form-horizontal">
					<input type="hidden" value="" name="id_del"/>

					<div class="form-body">
						<div class="form-group">
							<label class="control-label col-md-9"><h5>Bạn có chắc chắn muốn xoá từ điển này?</h5>
							</label>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" id="btnDelete" onclick="delete_assumption()" class="btn btn-primary"><i
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
