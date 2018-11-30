<!-- Main content -->
<div class = "content-wrapper">

<!-- Page header -->
<div class = "page-header page-header-default">
	<div class = "breadcrumb-line">
		<ul class = "breadcrumb">
			<li><a href = "<?php echo base_url() ?>"><i class = "icon-home2 position-left"></i> Trang chủ</a></li>
			<li class = "active">Quản trị hệ thống</li>
			<li class = "active">Setting mail gửi đi</li>
		</ul>
	</div>
</div>
<!-- /page header -->


<!-- Content area -->
<div class = "content">
<!-- Basic responsive configuration -->
<div class = "panel panel-flat">
	<div class = "panel-body">
		<button style = "margin-left: 10px" class = "btn btn-success btn-xs" onclick = "edit_task(1)"><i
				class = "glyphicon glyphicon-cog"></i> Sửa
		</button>
		<button class = "btn btn-default btn-xs" data-action = "reload"><i
				class = "glyphicon glyphicon-refresh"></i> Tải lại trang
		</button>
	</div>

	<table class = "table datatable-responsive">
		<thead>
		<tr>
			<th>STT</th>
			<th>Tên</th>
			<th>Email</th>
            <th>Password</th>
		</tr>
		</thead>
		<tbody>
		<?php
            echo '<tr>';
            echo '<td>1</td>';
            echo '<td>' . $mail_sent->name . '</td>';
            echo '<td>' . $mail_sent->email . '</td>';
            echo '<td>' . $mail_sent->password . '</td>';
            echo '</tr>';
		?>
		</tbody>
	</table>
</div>
<!-- /basic responsive configuration -->

<script type = "text/javascript">

	function edit_task(id) {
		$('#form')[0].reset(); // reset form on modals
		$('.form-group').removeClass('has-error'); // clear error class
		$('.help-block').empty(); // clear error string

		//Ajax Load data from ajax
		$.ajax({
			url: "<?php echo site_url('tutor/setting_mail/ajax_edit/')?>/" + id,
			type: "GET",
			dataType: "JSON",
			success: function (data) {
				$('[name="idTask"]').val(data.id);
				$('[name="name"]').val(data.name);
				$('[name="email"]').val(data.email);
				$('[name="password"]').val(data.password);
				$('#modal_form').modal('show'); // show bootstrap modal when complete loaded
				$('.modal-title').text('Sửa thông tin mail gửi'); // Set title to Bootstrap modal title

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
		var url = "<?php echo site_url('tutor/setting_mail/ajax_update')?>";
		

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
					<input type="hidden" value="" name="idTask"/>

					<div class="form-body">
						<div class="panel-body">
                            <div class="col-md-10">
                                <fieldset>
									<div class="form-group">
                                        <label class="control-label col-md-5">Tên mail <span
                                                class="text-danger">(*)</span></label>

                                        <div class="col-md-7">
                                            <input name="name" placeholder="Tên mail"
                                                    class="form-control"
                                                    type="text">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-5">Email <span
                                                class="text-danger">(*)</span></label>

                                        <div class="col-md-7">
                                            <input name="email" placeholder="Email"
                                                    class="form-control"
                                                    type="text">
                                            <span class="help-block"></span>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-5">Password <span
                                                class="text-danger">(*)</span></label>

                                        <div class="col-md-7">
                                            <input name="password" placeholder="Password"
                                                    class="form-control"
                                                    type="text">
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