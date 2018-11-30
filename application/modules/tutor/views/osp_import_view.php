<!-- Main content -->
<div class = "content-wrapper">

	<!-- Page header -->
	<div class = "page-header page-header-default">
		<div class = "breadcrumb-line">
			<ul class = "breadcrumb">
				<li><a href = "<?php echo base_url() ?>"><i class = "icon-home2 position-left"></i> Trang chủ</a></li>
				<li>Quản lý danh mục OSP100</li>
				<li class = "active">Import danh mục OSP100</li>
			</ul>
		</div>
	</div>
	<!-- /page header -->

	<!-- Content area -->
	<div class = "content">
		<!-- Basic responsive configuration -->
		<div class = "panel panel-flat">

			<div class = "panel-body">

				<form action = "<?php echo site_url('tutor/osp/import') ?>" id = "form" method = "post"
				      class = "form-horizontal" enctype = "multipart/form-data">

					<div class = "row">
						<div class = "col-md-3">
							<label>Chọn đợt học <span class="text-danger">(*)</span></label>
							<div class = "form-group <?php if(isset($err)) echo "has-error"; ?>">
								<select name = "plan_osp" class = "select-size-sm">
									<option value = "">--Chọn đợt học--</option>
									<?php
										foreach ($list_plan as $plan) {
											echo '<option value = "' . $plan->id . '">' . $plan->ten_dot_hoc . '</option>';
										}?>
								</select>
								<span class = "help-block"><?php if(isset($err)) echo $err; ?></span>
							</div>
						</div>

						<div class = "col-md-3" style = "margin-left:10px;">
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
									class = "glyphicon glyphicon-import"></i> Bước 4. Import thông tin OSP
							</button>
							<a href = "<?php echo site_url('tutor/osp/export_osp_tmp') ?>"><button type = "button" name = "export" value = "export"
							        class = "btn btn-success btn-xs"><i
									class = "glyphicon glyphicon-export"></i> Export excel thông tin OSP
							</button></a>
							<button type = "submit" name = "delete_data" value = "delete_data"
									class = "btn btn-default btn-xs"><i
									class = "glyphicon glyphicon-remove"></i> Xóa dữ liệu cũ
							</button>
						</div>
					</div>
					<div>Tải file import mẫu <a href="<?php echo site_url('upload/OSP_TVU_18031.xlsx') ?>">tại đây</a></div>
				</form>
				<?php
				if (isset($update_number)) {
					echo '<b>' . $update_number . '</b>';
				}

				if (isset($msg)) {
					echo '<span style="color: red">' . $msg . '</span>';
				}
				if (isset($list_osp100_tmp)) {
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
						<th>Trạng thái</th>
						<th>Ghi chú</th>
						<th>Mã trường</th>
						<th>Tên môn</th>
						<th>Mã môn</th>
						<th>Tên course</th>
						<th>Ngày bắt đầu KH</th>
						<th>Ngày kết thúc online</th>
						<th>Ngày thi</th>
						<th>Số lượng SV</th>
						<th>Trợ giảng</th>
						<th>Tên GVCM</th>
						<th>Acc GVCM</th>
						<th>Tên GVHD</th>
						<th>Acc GVHD</th>
					</tr>
					</thead>
					<tbody>
					<?php
					$stt = 1;
					foreach ($list_osp100_tmp as $osp) {
						if ($osp->status == 'N') {
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
						echo '<td><span class="' . $style . '">' . $osp->status . '</span></td>';
						echo '<td><span class="' . $style_ghi_chu . '">' . $osp->ghi_chu . '</span></td>';
						echo '<td>' . $osp->system . '</td>';
						echo '<td>' . $osp->ten_mon . '</td>';
						echo '<td>' . $osp->ma_mon . '</td>';
						echo '<td>' . $osp->ten_course . '</td>';
						echo '<td>' . $osp->ngay_bat_dau . '</td>';
						echo '<td>' . $osp->ngay_ket_thuc . '</td>';
						echo '<td>' . $osp->ngay_thi_chinh . '</td>';
						echo '<td>' . $osp->so_luong_sv . '</td>';
						echo '<td>' . $osp->username_tg . '</td>';
						echo '<td>' . $osp->ten_gvcm . '</td>';
						echo '<td>' . $osp->username_gvcm . '</td>';
						echo '<td>' . $osp->ten_gvhd . '</td>';
						echo '<td>' . $osp->username_gvhd . '</td>';
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
