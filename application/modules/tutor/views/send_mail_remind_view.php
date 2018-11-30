<div id="overlay">
	<div id="text">Đang trong quá trình gửi mail <img src="<?php echo site_url('assets/images/loading.gif')?>" style="background-color: transparent" alt="loading" height="40" width="40"></div>
</div>

<!-- Main content -->
<div class = "content-wrapper">

	<!-- Page header -->
	<div class = "page-header page-header-default">
		<div class = "breadcrumb-line">
			<ul class = "breadcrumb">
				<li><a href = "<?php echo base_url() ?>"><i class = "icon-home2 position-left"></i> Trang chủ</a></li>
				<li>Quản lý mẫu email và SMS</li>
				<li class = "active">Email nhắc giảng viên ký chứng từ</li>
			</ul>
		</div>
	</div>
	<!-- /page header -->

	<!-- Content area -->
	<div class = "content">
		<!-- Basic responsive configuration -->
		<div class = "panel panel-flat">

			<div class = "panel-body">

				<form action = "<?php echo site_url('tutor/send_mail_remind') ?>" id = "form" method = "post"
				      class = "form-horizontal" enctype = "multipart/form-data">

					<div class = "row">
						<div class = "col-md-4 ">
							<label>Chọn mẫu email <span class = "text-danger">(*)</span></label>
							<div class = "form-group <?php if(isset($err)) echo "has-error"; ?>">
								<select name = "id_template" class = "select-size-sm">
									<option value = "">--Chọn mẫu email--</option>
									<?php
										foreach ($list_template as $template) {
											echo '<option value = "' . $template->id . '">' . $template->ten_mau . '</option>';
										}?>
								</select>
								<span class="help-block"><?php if(isset($err)) echo $err; ?></span>
							</div>
						</div>
					</div>
					<div class = "row">
						<div class = "col-md-4">
							<label>Bước 1: Chọn file</label>

							<div class = "form-group">
								<input type = "file" name = "upFile" class = "file-input" data-show-preview = "false"
								       data-show-upload = "false">
								<span class = "help-block"></span>
							</div>
						</div>
					</div>
					<div class = "row">
						<div class = "form-group">

							<button type = "submit" name = "save_file" value = "upload"
									style = "margin-left: 10px" 
							        class = "btn btn-primary btn-xs"><i
									class = "glyphicon glyphicon-upload"></i> Bước 2. Upload file
							</button>
							<button type = "submit" onclick="on()" name = "send_mail" value = "send_mail"
							        class = "btn btn-primary btn-xs"><i
									class = "glyphicon glyphicon-send"></i> Bước 4. Gửi email
							</button>
							<button type = "submit" name = "delete_data" value = "delete_data"
									class = "btn btn-default btn-xs"><i
									class = "glyphicon glyphicon-remove"></i> Xóa dữ liệu cũ
							</button>
						</div>
					</div>
					<div>Tải file import mẫu <a href="<?php echo site_url('upload/Template_vouchers.xlsx') ?>">tại đây</a></div>
				</form>
				<table class = "table datatable-responsive">
					<thead>
					<tr>
						<th>STT</th>
						<th>Username giảng viên</th>
						<th>Tên chứng từ</th>
						<th>Thời gian đến</th>
						<th>Deadline</th>
						<th>Username trợ giảng</th>
						<th>Ghi chú</th>
					</tr>
					</thead>
					<tbody>
					<?php
						// if(isset($sent_mail)) {
						// 	echo '<script> alert('. $sent_mail .')</script>';
						// }
						$this->load->model("teachers_m");
						$stt = 1;
						foreach ($list_email_template as $mail) {
							if ($mail->status == 'N') {
								$style = 'label label-danger';
								$class_tr = 'danger';
								$style_ghi_chu = 'label label-danger';
							} else {
								$style = 'label label-success';
								$class_tr = '';
								$style_ghi_chu = 'label label-success';
							}
							echo '<tr class="' . $class_tr . '">';
							echo '<td>' . $stt . '</td>';
							echo '<td>' . $mail->username_giang_vien . '</td>';
							echo '<td>' . $mail->ten_chung_tu . '</td>';
							echo '<td>' . $mail->thoi_gian_den . '</td>';
							echo '<td>' . $mail->deadline . '</td>';
							echo '<td>' . $mail->username_tro_giang . '</td>';
							echo '<td><span class="' . $style_ghi_chu . '">' . $mail->ghi_chu . '</span></td>';
							echo '</tr>';
							$stt++;
						}
					?>
					</tbody>
				</table>
			</div>

		</div>
		<!-- /basic responsive configuration -->


<script type="text/javascript">
	function on() {
		document.getElementById("overlay").style.display = "block";
	}

</script>