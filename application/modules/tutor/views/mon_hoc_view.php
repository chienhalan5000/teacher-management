<!-- Main content -->
<div class="content-wrapper">

<!-- Page header -->
<div class="page-header page-header-default">
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo base_url() ?>"><i class="icon-home2 position-left"></i> Trang chủ</a></li>
			<li class="active">Quản lý môn học</li>
		</ul>
	</div>
</div>
<!-- /page header -->


<!-- Content area -->
<div class="content">
<!-- Basic responsive configuration -->
<div class="panel panel-flat">
	<table class="table datatable-responsive">
		<thead>
		<tr>
			<th>STT</th>
			<th>Tên môn</th>
			<th>Mã môn</th>
			<th>Số tín chỉ</th>
			<th>Link tài liệu</th>
			<th>Ghi chú</th>
			<th class="text-center" style="width: 200px">Actions</th>
		</tr>
		</thead>
		<tbody>
		<?php
			$stt = 1;
			foreach ($list_mon_hoc as $mon_hoc) {
				echo '<tr>';
				echo '<td>' . $stt . '</td>';
				echo '<td>' . $mon_hoc->ten_mon . '</td>';
				echo '<td>' . $mon_hoc->ma_mon . '</td>';
				echo '<td>' . $mon_hoc->so_tin_chi . '</td>';
				echo '<td>' . $mon_hoc->link_document . '</td>';
				echo '<td>' . $mon_hoc->ghi_chu . '</td>';
				echo '<td class="text-center" style="width: 200px"><a title="Sửa môn học" onclick="edit_mon_hoc(' . "'" . $mon_hoc->id . "'" . ')"><i class="glyphicon glyphicon-pencil"></i></a> &nbsp;
			<a title="Xoá môn học" onclick="delete_mon_hoc_confirm(' . "'" . $mon_hoc->id . "'" . ')"><i class="icon-trash"></i></a></td>';
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


	function edit_mon_hoc(id_mon_hoc) {
		save_method = 'update';
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string

		//Ajax Load data from ajax
		$.ajax({
			url: "<?php echo site_url('tutor/mon_hoc/ajax_edit/')?>/" + id_mon_hoc,
			type: "GET",
			dataType: "JSON",
			success: function (data) {

				$('[name="id_mon_hoc"]').val(data.id);
				$('[name="ma_mon"]').val(data.ma_mon);
				$('[name="ten_mon"]').val(data.ten_mon);
				$('[name="so_tin_chi"]').val(data.so_tin_chi);
				$('[name="link_document"]').val(data.link_document);
				$('[name="ghi_chu"]').val(data.ghi_chu);
				$('#modal_form').modal('show'); // show bootstrap modal when complete loaded
				$('.modal-title').text('Sửa thông tin môn học'); // Set title to Bootstrap modal title

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
			url = "<?php echo site_url('tutor/mon_hoc/ajax_add')?>";
		} else {
			url = "<?php echo site_url('tutor/mon_hoc/ajax_update')?>";
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

	function delete_mon_hoc_confirm(id) {
		$('#form2')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('[name="id_mon_hoc"]').val(id);
		$('.help-block').empty(); // clear error string
		$('#modal_form_confirm').modal('show'); // show bootstrap modal
		$('.modal-title').text('Xác nhận xoá môn học'); // Set Title to Bootstrap modal title
	}
	function delete_mon_hoc() {
		$('#btnDelete').text('Deleting...'); //change button text
		$('#btnDelete').attr('disabled', true); //set button disable

		// ajax adding data to database
		$.ajax({
			url: "<?php echo site_url('tutor/mon_hoc/ajax_delete')?>",
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
					<input type="hidden" value="" name="id_mon_hoc"/>

					<div class="form-body">
						<div class="panel-body">
							<div class="row">
										<div class="form-group">
											<label class="control-label col-md-3">Mã môn <span
													class="text-danger">(*)</span></label>

											<div class="col-md-9">
												<input name="ma_mon" placeholder="Mã môn học" class="form-control"
													   type="text">
												<span class="help-block"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-3">Tên môn <span
													class="text-danger">(*)</span></label>

											<div class="col-md-9">
												<input name="ten_mon" placeholder="Tên môn học" class="form-control"
													   type="text">
												<span class="help-block"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-3">Số tín chỉ <span class="text-danger">(*)</span></label>

											<div class="col-md-9">
												<input name="so_tin_chi" placeholder="Số tín chỉ" class="form-control"
													   type="text">
												<span class="help-block"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-3">Link tài liệu <span
													class="text-danger">(*)</span></label>

											<div class="col-md-9">
												<input name="link_document" placeholder="Nhập link tài liệu môn học"
													   class="form-control"
													   type="text">
												<span class="help-block"></span>
											</div>
										</div>
										<div class="form-group">
											<label class="control-label col-md-3">Ghi chú</label>

											<div class="col-md-9">
												<input type="text" name="Note1" placeholder="Nhập ghi chú nếu có"
													   class="form-control">
												<span class="help-block"></span>
											</div>
										</div>
							</div>
				</form>
			</div>
			<div class="modal-footer">
				<div align="left">
					<span class="label label-danger">Chú ý</span> <span class="text-danger">(*)</span> là thông tin bắt
																									   buộc điền đầy đủ,
																									   không được bỏ
																									   trống.
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
</div>
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
					<input type="hidden" value="" name="id_mon_hoc"/>

					<div class="form-body">
						<div class="form-group">
							<label class="control-label col-md-9"><h5>Bạn có chắc chắn muốn môn học này?</h5>
							</label>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" id="btnDelete" onclick="delete_mon_hoc()"
						class="btn btn-primary"><i
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
