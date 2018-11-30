<!-- Main content -->
<div class = "content-wrapper">

	<!-- Page header -->
	<div class = "page-header page-header-default">
		<div class = "breadcrumb-line">
			<ul class = "breadcrumb">
				<li><a href = "<?php echo base_url() ?>"><i class = "icon-home2 position-left"></i> Trang chủ</a></li>
				<li>Quản lý giảng viên</li>
				<li class = "active">Import danh sách GV</li>
			</ul>
		</div>
	</div>
	<!-- /page header -->

	<!-- Content area -->
	<div class = "content">
		<!-- Basic responsive configuration -->
		<div class = "panel panel-flat">

			<div class = "panel-body">

				<form action = "<?php echo site_url('tutor/teacher/import') ?>" id = "form" method = "post"
				      class = "form-horizontal" enctype = "multipart/form-data">
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
							<button type = "submit" name = "kiem_tra" value = "kiem_tra"
							        class = "btn btn-success btn-xs"
							<i
								class = "glyphicon glyphicon-retweet"></i> Bước 3. Kiểm tra dữ liệu</button>
							<button type = "submit" name = "import" value = "import"
							        class = "btn btn-primary btn-xs"><i
									class = "glyphicon glyphicon-import"></i> Bước 4. Import thông tin giảng viên
							</button>
							<a href = "<?php echo site_url('tutor/teacher/export') ?>"><button type = "button" name = "export" value = "export"
							        class = "btn btn-success btn-xs"><i
									class = "glyphicon glyphicon-export"></i> Export excel thông tin giảng viên
							</button></a>
							<button type = "submit" name = "delete_data" value = "delete_data"
									class = "btn btn-default btn-xs"><i
									class = "glyphicon glyphicon-remove"></i> Xóa dữ liệu cũ
							</button>
						</div>
						<div>Tải file import mẫu <a href="<?php echo site_url('upload/Template_import_teachers.xlsx') ?>">tại đây</a></div>
					</div>
				</form>
				<?php
					if ($list_DSGV_tmp != null) {
					echo isset($total_number)? 'Tổng số: '.$total_number.'<br>': '';
					echo isset($insert_number)? 'Thành công: '.$insert_number.'<br>': '';
					echo isset($err_number)? 'Không thành công: '.$err_number.'<br>': '';
				?>
				<br>
				<br>
				<br>
				<table class = "table datatable-responsive">
					<thead>
					<tr>
						<th>STT</th>
						<th>Ghi chú</th>
						<th>Tên tài khoản</th>
						<th>Họ và tên</th>
						<th>Email</th>
						<th>Email cá nhân</th>
						<th>Số điện thoại</th>
						<th>Loại giảng viên</th>
					</tr>
					</thead>
					<tbody>
					<?php
						$stt = 1;
						foreach ($list_DSGV_tmp as $DSGV) {
							if ($DSGV->status == 'N') {
								$class_tr = 'danger';
								$style_ghi_chu = 'label label-danger';
								
							} else {
								$class_tr = '';
								$style_ghi_chu = 'label label-success';
							}
							echo '<tr class="' . $class_tr . '">';
							echo '<td>' . $stt . '</td>';
							echo '<td><span class="' . $style_ghi_chu . '">' . $DSGV->ghi_chu . '</span></td>';
							echo '<td>' . $DSGV->username .'</td>';
							echo '<td>' . $DSGV->lastname . ' '. $DSGV->firstname .  '</td>';
							echo '<td>' . $DSGV->email . '</td>';
							echo '<td>' . $DSGV->email_person . '</td>';
							echo '<td>' . $DSGV->mobile_number . '</td>';
							echo '<td>' . $DSGV->role_name . '</td>';
							echo '</tr>';
							$stt++;
						}
					}
					?>
					</tbody>
				</table>
			</div>

		</div>
		<!-- /basic responsive configuration -->
