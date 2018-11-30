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
				<li class = "active">Gửi mail theo đợt học</li>
			</ul>
		</div>
	</div>
	<!-- /page header -->

	<!-- Content area -->
	<div class = "content">
		<!-- Basic responsive configuration -->
		<div class = "panel panel-flat">

			<div class = "panel-body">

				<form action = "<?php echo site_url('tutor/plan_mail/startcourse') ?>" id = "form" method = "post"
				      class = "form-horizontal" enctype = "multipart/form-data">

                    <div class = "form-group <?php if(isset($err)) echo "has-error"; ?>">
                        <label class = "control-label col-md-2">Chọn ngày bắt đầu <span
                                class = "text-danger">(*)</span></label>

                        <div class = "col-md-2">
                            <input type = "date" data-date-format = "YYYY-mm-dd"
                                    <?php if(isset($date)) echo "value=" . $date; ?>
                                    name = "date_start"
                                    class = "form-control daterange-single">
                            <span class = "help-block"><?php if(isset($err)) echo $err; ?></span>
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
				<table class = "table datatable-responsive">
					<thead>
					<tr>
						<th>STT</th>
						<th>Tên môn</th>
						<th>Mã môn</th>
						<th>Ngày bắt đầu</th>
						<th>Ngày kết thúc</th>
						<th>Ngày thi</th>
						<th>Username GVCM</th>
						<th>Username GVHD</th>
						<th>Username trợ giảng</th>
					</tr>
					</thead>
					<tbody>
					<?php
						$stt = 1;
						foreach ($list_osp100 as $osp100) {
							echo '<tr>';
							echo '<td>' . $stt . '</td>';
							echo '<td>' . $osp100->ten_mon . '</td>';
							echo '<td>' . $osp100->ma_mon . '</td>';
							echo '<td>' . $osp100->ngay_bat_dau . '</td>';
							echo '<td>' . $osp100->ngay_ket_thuc . '</td>';
							echo '<td>' . $osp100->ngay_thi_chinh . '</td>';
							echo '<td>' . $osp100->username_gvcm . '</td>';
							echo '<td>' . $osp100->username_gvhd . '</td>';
							echo '<td>' . $osp100->username_tg . '</td>';
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