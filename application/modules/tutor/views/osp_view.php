<!-- Main content -->
<div class = "content-wrapper">

<!-- Page header -->
<div class = "page-header page-header-default">
	<div class = "breadcrumb-line">
		<ul class = "breadcrumb">
			<li><a href = "<?php echo base_url() ?>"><i class = "icon-home2 position-left"></i> Trang chủ</a></li>
			<li class = "active">Quản lý danh mục OSP100</li>
		</ul>
	</div>
</div>
<!-- /page header -->


<!-- Content area -->
<div class = "content">
<!-- Basic responsive configuration -->
<div class = "panel panel-flat">
	<div class = "panel-body">
		<button style = "margin-left: 10px" class = "btn btn-success btn-xs" onclick = "add_osp()"><i
				class = "glyphicon glyphicon-plus-sign"></i> Thêm OSP
		</button>
		<button class = "btn btn-default btn-xs" data-action = "reload"><i
				class = "glyphicon glyphicon-refresh"></i> Tải lại trang
		</button>
	</div>

	<table class = "table datatable-responsive">
		<thead>
		<tr>
			<th>STT</th>
			<th class = "text-center" style = "width: 250px">Actions</th>
			<th>Tên đợt học</th>
			<th>Mã trường</th>
			<th>Tên môn</th>
			<th>Mã môn</th>
			<th>Lớp môn</th>
			<th>Ngày bắt đầu KH</th>
			<th>Ngày kết thúc online</th>
<!--			<th>Ngày thi (Luôn để format Text)</th>-->
			<th>Ngày thi cuối</th>
			<th>Số lượng SV</th>
<!--			<th>ID diễn đàn</th>-->
			<th>Trợ giảng</th>
			<th>Acc GVCM</th>
			<th>Acc GVHD</th>
			<th>Ghi chú</th>
		</tr>
		</thead>
		<tbody>
		<?php
		$stt = 1;
		if (isset($list_osp100)) {
			foreach ($list_osp100 as $osp) {
				echo '<tr>';
				echo '<td>' . $stt . '</td>';
				echo '<td class="text-center" style="width: 200px">';
				echo '<a title="Sửa OSP" onclick="edit_osp(' . "'" . $osp->id . "'" . ')"><i class="glyphicon glyphicon-pencil"></i></a> &nbsp;';
				echo '<a title="Xoá OSP" onclick="delete_osp_confirm(' . "'" . $osp->id . "'" . ')"><i class="icon-trash"></i></a>';

				echo '</td>';
				echo '<td>' . $this->plans_m->get_ten_dot_hoc_by_id($osp->plan_id) . '</td>';
				echo '<td>' . $osp->system . '</td>';
				echo '<td>' . $osp->ten_mon . '</td>';
				echo '<td>' . $osp->ma_mon . '</td>';
				echo '<td>' . $osp->ten_course . '</td>';
				echo '<td>' . $osp->ngay_bat_dau . '</td>';
				echo '<td>' . $osp->ngay_ket_thuc . '</td>';
//				echo '<td>' . $osp->ngay_thi . '</td>';
				echo '<td>' . $osp->ngay_thi_chinh . '</td>';
				echo '<td>' . $osp->so_luong_sv . '</td>';
//				echo '<td>' . $osp->id_forum . '</td>';
				echo '<td>' . $osp->username_tg . '</td>';
				echo '<td>' . $osp->username_gvcm . '</td>';
				echo '<td>' . $osp->username_gvhd . '</td>';
				echo '<td>' . $osp->ghi_chu . '</td>';
				echo '</tr>';
				$stt++;
			}
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
	function add_osp() {
		save_method = 'add';
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string
		$('#modal_form').modal('show'); // show bootstrap modal
		$('.modal-title').text('Thêm OSP'); // Set Title to Bootstrap modal title
	}

	function edit_osp(id) {
		save_method = 'update';
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string

		//Ajax Load data from ajax
		$.ajax({
			url: "<?php echo site_url('tutor/osp/ajax_edit/')?>/" + id,
			type: "GET",
			dataType: "JSON",
			success: function (data) {

				$('[name="id_osp"]').val(data.id);
				$('[name="dot_hoc"]').select2();
				$('[name="dot_hoc"]').val(data.plan_id).trigger("change");
				$('[name="system"]').select2();
				$('[name="system"]').val(data.system).trigger("change");
				$('[name="ma_mon_hoc"]').val(data.ma_mon);
				$('[name="ten_mon_hoc"]').val(data.ten_mon);
				$('[name="ten_lop"]').val(data.ten_lop);
				$('[name="ten_course"]').val(data.ten_course);
				$('[name="ngay_bat_dau"]').val(data.ngay_bat_dau);
				$('[name="ngay_ket_thuc"]').val(data.ngay_ket_thuc);
				$('[name="ngay_thi"]').val(data.ngay_thi);
				$('[name="ngay_thi_chinh"]').val(data.ngay_thi_chinh);
				$('[name="so_luong_sv"]').val(data.so_luong_sv);
				$('[name="id_forum"]').val(data.id_forum);
				$('[name="username_povh"]').val(data.username_povh);
				$('[name="username_tg"]').val(data.username_tg);
				$('[name="username_gvcm"]').val(data.username_gvcm);
				$('[name="username_gvhd"]').val(data.username_gvhd);
				$('[name="ghi_chu"]').val(data.ghi_chu);
				$('#modal_form').modal('show'); // show bootstrap modal when complete loaded
				$('.modal-title').text('Sửa thông tin OSP'); // Set title to Bootstrap modal title

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
			url = "<?php echo site_url('tutor/osp/ajax_add')?>";
		} else {
			url = "<?php echo site_url('tutor/osp/ajax_update')?>";
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

	function delete_osp_confirm(id) {
		$('#form2')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('[name="id_osp"]').val(id);
		$('.help-block').empty(); // clear error string
		$('#modal_form_confirm').modal('show'); // show bootstrap modal
		$('.modal-title').text('Xác nhận xoá OSP'); // Set Title to Bootstrap modal title
	}
	function delete_osp(id) {
		$('#btnDelete').text('Deleting...'); //change button text
		$('#btnDelete').attr('disabled', true); //set button disable

		// ajax adding data to database
		$.ajax({
			url: "<?php echo site_url('tutor/osp/ajax_delete')?>",
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
<div class = "modal fade" id = "modal_form" role = "dialog">
	<div class = "modal-dialog modal-full">
		<div class = "modal-content">
			<div class = "modal-header">
				<button type = "button" class = "close" data-dismiss = "modal" aria-label = "Close"><span
						aria-hidden = "true">&times;</span></button>
				<h3 class = "modal-title">Person Form</h3>
			</div>
			<div class = "modal-body form">
				<form action = "#" id = "form" class = "form-horizontal">
					<input type = "hidden" value = "" name = "id_osp"/>

					<div class = "form-body">
						<div class = "panel-body">
							<div class = "row">
								<div class = "col-md-6">
									<fieldset>
										<div class = "form-group">
											<label class = "control-label col-md-3">Đợt học <span class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<select name = "dot_hoc" class = "select-size-sm">
													<option value = "">--Chọn đợt học--</option>
													<?php
													foreach ($list_plan as $plan) {
														echo '<option value = "' . $plan->id . '">' . $plan->ten_dot_hoc . '</option>';
													}?>
												</select>
												<input type = "hidden" name = "dot_hoc_hide">
												<span class = "help-block"></span>
											</div>
										</div>
										<div class = "form-group">
											<label class = "control-label col-md-3">Trường <span class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<select name = "system" class = "select-size-sm">
													<option value = "">--Chọn trường--</option>
													<?php
													foreach ($list_system as $system) {
														echo '<option value = "' . $system->code . '">' . $system->name . '</option>';
													}?>
												</select>
												<input type = "hidden" name = "system_hide">
												<span class = "help-block"></span>
											</div>
										</div>
										<div class = "form-group">
											<label class = "control-label col-md-3">Mã môn học <span class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<input name = "ma_mon_hoc" placeholder = "Mã môn học"
													   class = "form-control"
													   type = "text">
												<span class = "help-block"></span>
											</div>
										</div>
										<div class = "form-group">
											<label class = "control-label col-md-3">Môn học <span class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<input name = "ten_mon_hoc" placeholder = "Tên môn học"
													   class = "form-control"
													   type = "text">
												<span class = "help-block"></span>
											</div>
										</div>
										<div class = "form-group">
											<label class = "control-label col-md-3">Tên lớp <span class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<input name = "ten_lop" placeholder = "Tên lớp quản lý"
												       class = "form-control"
												       type = "text">
												<span class = "help-block"></span>
											</div>
										</div>
										<div class = "form-group">
											<label class = "control-label col-md-3">Lớp môn <span class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<input name = "ten_course" placeholder = "Tên lớp môn"
												       class = "form-control"
												       type = "text">
												<span class = "help-block"></span>
											</div>
										</div>
										<div class = "form-group">
											<label class = "control-label col-md-3">Ngày bắt đầu <span
													class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<input type = "date" data-date-format = "YYYY-mm-dd"
												       name = "ngay_bat_dau"
												       class = "form-control daterange-single">
												<span class = "help-block"></span>
											</div>
										</div>
										<div class = "form-group">
											<label class = "control-label col-md-3">Ngày kết thúc <span
													class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<input type = "date" data-date-format = "YYYY-mm-dd"
												       name = "ngay_ket_thuc"
												       class = "form-control daterange-single">
												<span class = "help-block"></span>
											</div>
										</div>
									</fieldset>
								</div>
								<div class = "col-md-6">
									<fieldset>
										<div class = "form-group">
											<label class = "control-label col-md-3">Ngày thi <span class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<input type = "date" data-date-format = "YYYY-mm-dd" name = "ngay_thi"
												       class = "form-control daterange-single">
												<span class = "help-block"></span>
											</div>
										</div>

										<div class = "form-group">
											<label class = "control-label col-md-3">Ngày thi cuối cùng <span class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<input type = "date" data-date-format = "YYYY-mm-dd" name = "ngay_thi_chinh"
												       class = "form-control daterange-single">
												<span class = "help-block"></span>
											</div>
										</div>

										<div class = "form-group">
											<label class = "control-label col-md-3">Số lượng SV</label>

											<div class = "col-md-9">
												<input type = "number" name = "so_luong_sv"
												       placeholder = "Số lượng sinh viên" class = "form-control">
												<span class = "help-block"></span>
											</div>
										</div>
										<div class = "form-group">
											<label class = "control-label col-md-3">ID diễn đàn <span
													class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<input type = "number" name = "id_forum"
												       placeholder = "ID diễn đàn lớp môn" class =
												"form-control">
												<span class = "help-block"></span>
											</div>
										</div>
										<div class = "form-group">
											<label class = "control-label col-md-3">POVH <span
													class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<input type = "text" name = "username_povh"
												       placeholder = "username của POVH" class =
												"form-control">
												<span class = "help-block"></span>
											</div>
										</div>
										<div class = "form-group">
											<label class = "control-label col-md-3">Trợ giảng <span
													class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<input type = "text" name = "username_tg"
												       placeholder = "username của Trợ giảng" class =
												"form-control">
												<span class = "help-block"></span>
											</div>
										</div>
										<div class = "form-group">
											<label class = "control-label col-md-3">GVCM <span
													class = "text-danger">(*)</span></label>

											<div class = "col-md-9">
												<input type = "text" name = "username_gvcm"
												       placeholder = "username của GVCM" class =
												"form-control">
												<span class = "help-block"></span>
											</div>
										</div>
										<div class = "form-group">
											<label class = "control-label col-md-3">GVHD</label>

											<div class = "col-md-9">
												<input type = "text" name = "username_gvhd"
												       placeholder = "username của GVHD" class =
												"form-control">
												<span class = "help-block"></span>
											</div>
										</div>
										<div class = "form-group">
											<label class = "control-label col-md-3">Ghi chú</label>

											<div class = "col-md-9">
												<input type = "text" name = "ghi_chu"
												       placeholder = "Nhập ghi chú nếu có"
												       class = "form-control">
												<span class = "help-block"></span>
											</div>
										</div>
									</fieldset>
								</div>
							</div>
				</form>
			</div>
			<div class = "modal-footer">
				<div align = "left">
					<span class = "label label-danger">Chú ý</span> <span class = "text-danger">(*)</span> là thông tin
					                                                                                       bắt buộc điền
					                                                                                       đầy đủ, không
					                                                                                       được bỏ
					                                                                                       trống. Ngày thi cuối cùng dùng để làm dữ liệu đóng course tự động.
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
</div>
<!-- /.modal-dialog -->
</div>
<!-- /.modal -->

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
					<input type = "hidden" value = "" name = "id_osp"/>

					<div class = "form-body">
						<div class = "form-group">
							<label class = "control-label col-md-9"><h5>Bạn có chắc chắn muốn OSP này?</h5>
							</label>
						</div>
					</div>
				</form>
			</div>
			<div class = "modal-footer">
				<button type = "button" id = "btnDelete" onclick = "delete_osp()"
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
