<!-- Main content -->
<div class="content-wrapper">

<!-- Page header -->
<div class="page-header page-header-default">
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo base_url() ?>"><i class="icon-home2 position-left"></i> Trang chủ</a></li>
			<li class="active">Quản lý kế hoạch học</li>
			<li class="active">Danh sách kế hoạch</li>
		</ul>
	</div>
</div>
<!-- /page header -->


<!-- Content area -->
<div class="content">
<!-- Basic responsive configuration -->
<div class="panel panel-flat">
	<div class="panel-body">
		<button style="margin-left: 10px" class="btn btn-success btn-xs" onclick="add_plan()"><i
				class="icon-user-plus"></i> Thêm kế hoạch
		</button>
		<button class="btn btn-default btn-xs" data-action="reload"><i
				class="glyphicon glyphicon-refresh"></i> Tải lại trang
		</button>
	</div>

	<table class="table datatable-responsive">
		<thead>
			<tr>
				<th>STT</th>
				<th>Mã đợt học</th>
				<th>Tên đợt học</th>
				<th>Ghi chú</th>
				<th>Người tạo</th>
				<th>Thời gian tạo</th>
				<th class="text-center">Actions</th>
			</tr>
		</thead>
		<tbody>
		
		<?php
			$stt = 1;
			foreach ($list_plan as $plan) {
				echo '<tr>';
				echo '<td>' . $stt . '</td>';
				echo '<td>' . $plan->ma_dot_hoc . '</td>';
				echo '<td>' . $plan->ten_dot_hoc . '</td>';
				echo '<td>' . $plan->ghi_chu . '</td>';
				echo '<td>' . get_username_by_id($plan->created_by) . '</td>';
				echo '<td style="width:160px;">' . $plan->created_at . '</td>';
				echo '<td class="text-center" style="width: 200px"><a title="Sửa kế hoạch" onclick="edit_plan(' . "'" . $plan->id . "'" . ')"><i class="glyphicon glyphicon-pencil"></i></a> &nbsp;
						<a title="Xoá kế hoạch" onclick="delete_plan_confirm(' . "'" . $plan->id . "'" . ')"><i class="icon-trash"></i></a></td>';
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
	function add_plan() {
		save_method = 'add';
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string
		$('#modal_form').modal('show'); // show bootstrap modal
		$('.modal-title').text('Thêm đợt học'); // Set Title to Bootstrap modal title
	}

	function edit_plan(id) {
		save_method = 'update';
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string

		//Ajax Load data from ajax
		$.ajax({
			url: "<?php echo site_url('tutor/plan/ajax_edit/')?>/" + id,
			type: "GET",
			dataType: "JSON",
			success: function (data) {
                $('[name="IdPlan"]').val(data.id);
				$('[name="codePlan"]').val(data.ma_dot_hoc);
				$('[name="namePlan"]').val(data.ten_dot_hoc);
				$('[name="ghi_chu"]').val(data.ghi_chu);
				$('#modal_form').modal('show'); // show bootstrap modal when complete loaded
				$('.modal-title').text('Sửa thông tin tài khoản'); // Set title to Bootstrap modal title

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
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string
		var url;

		if (save_method == 'add') {
			url = "<?php echo site_url('tutor/plan/ajax_add')?>";
		} else {
			url = "<?php echo site_url('tutor/plan/ajax_update')?>";
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

	

	function delete_plan_confirm(id) {
		$('#form2')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('[name="IdPlan"]').val(id);
		$('.help-block').empty(); // clear error string
		$('#modal_form_confirm').modal('show'); // show bootstrap modal
		$('.modal-title').text('Xác nhận xoá đợt học'); // Set Title to Bootstrap modal title
	}
    
	function delete_plan(id) {
		$('#btnDelete').text('Deleting...'); //change button text
		$('#btnDelete').attr('disabled', true); //set button disable

		// ajax adding data to database
		$.ajax({
			url: "<?php echo site_url('tutor/plan/ajax_delete')?>",
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
					<input type="hidden" value="" name="IdPlan"/>

					<div class="form-body">
						<div class="panel-body">
                            <div class="col-md-10">
                                <fieldset>
									<div class="form-group">
                                        <label class="control-label col-md-5">Mã đợt học <span
                                                class="text-danger">(*)</span></label>

                                        <div class="col-md-7">
                                            <input name="codePlan" placeholder="Mã đợt học"
                                                    class="form-control"
                                                    type="text">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-5">Tên đợt học <span
                                                class="text-danger">(*)</span></label>

                                        <div class="col-md-7">
                                            <input name="namePlan" placeholder="Tên đợt học"
                                                    class="form-control"
                                                    type="text">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-5">Ghi chú </label>

                                        <div class="col-md-7">
                                            <input type="text" name="ghi_chu"
                                                    placeholder="Ghi chú"
                                                    class="form-control" maxlength="15">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                </fieldset>
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
					<input type="hidden" value="" name="IdPlan"/>

					<div class="form-body">
						<div class="form-group">
							<label class="control-label col-md-9"><h5>Bạn có chắc chắn muốn xoá kế hoạch này?</h5>
							</label>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" id="btnDelete" onclick="delete_plan()" class="btn btn-primary"><i
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
