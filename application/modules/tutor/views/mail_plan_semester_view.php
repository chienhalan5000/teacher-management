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
				<li>Mail kế hoạch giảng dạy</li>
				<li class = "active">Gửi mail theo quý</li>
			</ul>
		</div>
	</div>
	<!-- /page header -->

	<!-- Content area -->
	<div class = "content">
		<!-- Basic responsive configuration -->
		<div class = "panel panel-flat">

			<div class = "panel-body">

				<form action = "<?php echo site_url('tutor/plan_mail/semester') ?>" id = "form" method = "post"
				      class = "form-horizontal" enctype = "multipart/form-data">

                    <div class="form-group <?php if(isset($err)) echo "has-error"; ?>" >
                        <label class="control-label col-md-2">Chọn quý <span class = "text-danger">(*)</span></label>
                        <div class="col-md-2">
                            <select name = "id_semester" class = "select-size-sm">
								<?php
									if(isset($plan)) {
										echo '<option value = "' . $plan->id . '">' . $plan->ten_dot_hoc . '</option>';
									} else {
										echo '<option value = "">--Chọn quý--</option>';
									}
                                    foreach ($list_semester as $semester) {
										if($semester->id == $plan->id)
											continue;
										else
                                        	echo '<option value = "' . $semester->id . '">' . $semester->ten_dot_hoc . '</option>';
									}
								?>
                            </select>
                            <span class="help-block"><?php if(isset($err)) echo $err; ?></span>
                        </div>
                    </div>
					<div class="form-group" >
                        <label class="control-label col-md-2">Chọn số lượng</label>
                        <div class="col-md-2">
                            <select name = "so_luong" class = "form-control">
								<option value = "50">50</option>
								<option value = "100">100</option>
								<option value = "150">150</option>
                            </select>
                        </div>
                    </div>
                    <div class = "form-group">
                        <button type = "submit" name = "loc_ket_qua" value = "loc_ket_qua"
                            style = "margin-left: 10px" 
                            class = "btn btn-primary btn-xs"><i
                            class = "glyphicon glyphicon-list-alt"></i> Lấy danh sách
                        </button>
						<?php
							if(isset($list_osp100) && $list_osp100 != null) {
								echo '<button type="submit" onclick="on()" id="btnSave" name="send" value="send" class="btn btn-primary btn-xs"><i
										class="glyphicon glyphicon-send"></i> Send
									  </button>';
							}
						?>
                    </div>
				</form>
				<?php
					if($show_total_success_err) {
						echo isset($total)? 'Tổng số: '.$total.'<br>': '';
						echo isset($success)? 'Đã gửi: '.$success.'<br>': '';
						echo isset($error)? 'Bị lỗi: '.$error.'<br>': '';
					}
				?>
				<br>
				<br>
				<br>
				<table class = "table datatable-responsive">
					<thead>
					<tr>
						<th>STT</th>
						<th>Tên giảng viên</th>
						<th>Username</th>
						<th>Email</th>
						<th>Số khóa học</th>
						<th>Trạng thái gửi mail</th>
					</tr>
					</thead>
					<tbody>
					<?php
						$stt = 1;
						foreach ($list_osp100 as $key => $osp100) {
							if ($osp100[0]->status_mail == 'N') {
								$style = 'label label-danger';
								$status_mail = 'chưa gửi';
							} else if($osp100[0]->status_mail == 'Y') {
								$style = 'label label-success';
								$status_mail = 'đã gửi';
							} else {
								$style = 'label label-warning';
							$status_mail = 'bị lỗi';
							}
							echo '<tr>';
							echo '<td>' . $stt . '</td>';
							echo '<td>' . $osp100[0]->lastname . ' ' . $osp100[0]->firstname . '</td>';
							echo '<td>' . $osp100[0]->username . '</td>';
							echo '<td>' . $osp100[0]->email . '</td>';
							echo '<td>' . $osp100[0]->so_course . '</td>';
							echo '<td><span class="' . $style . '">' . $status_mail . '</span></td>';
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