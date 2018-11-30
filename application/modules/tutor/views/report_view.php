<!-- Main content -->
<div class="content-wrapper">

<!-- Page header -->
<div class="page-header page-header-default">
	<div class="breadcrumb-line">
		<ul class="breadcrumb">
			<li><a href="<?php echo base_url() ?>"><i class="icon-home2 position-left"></i> Trang chủ</a></li>
			<li class="active">Báo cáo</li>
		</ul>
	</div>
</div>
<!-- /page header -->


<!-- Content area -->
<div class="content">
<!-- Basic responsive configuration -->
<div class="panel panel-flat">
	<div class="panel-body">
        <form action = "<?php echo site_url('tutor/report') ?>" id = "form" method = "post"
                        class = "form-horizontal" enctype = "multipart/form-data">
			<div class = "form-group">
				<label class = "control-label col-md-1 <?php if(isset($err_from_date)) echo "text-danger"; ?>">Từ ngày <span
						class = "text-danger">(*)</span></label>

				<div class = "col-md-2">
					<input type = "date" data-date-format = "YYYY-mm-dd"
							<?php if(isset($from_date)) echo "value=" . $from_date; ?>
							name = "from_date"
							class = "form-control daterange-single">
					<span class = "help-block text-danger"><?php if(isset($err_from_date)) echo $err_from_date; ?></span>
				</div>
				
				<label class = "control-label col-md-1 <?php if(isset($err_to_date)) echo "text-danger"; ?>">Đến ngày <span
						class = "text-danger">(*)</span></label>

				<div class = "col-md-2">
					<input type = "date" data-date-format = "YYYY-mm-dd"
							<?php if(isset($to_date)) echo "value=" . $to_date; ?>
							name = "to_date"
							class = "form-control daterange-single">
					<span class = "help-block text-danger"><?php if(isset($err_to_date)) echo $err_to_date;
													 else if(isset($err)) echo $err;?></span>
				</div>
			</div>
			<div class="form-group">
				<label class="control-label col-md-1">Loại mẫu</label>
				<div class="col-md-2">
					<select name="typeTemplate" class="form-control">
						<option value="email">Email</option>
						<option value="sms">SMS</option>
					</select>
					<span class="help-block"></span>
				</div>

				<label class="control-label col-md-1">Từ khóa</label>
				<div class="col-md-2">
					<input name="tu_khoa" class="form-control" type="text" placeholder="Từ khóa" <?php if(isset($tu_khoa)) echo "value=" . $tu_khoa;?>>
				</div>
			</div>
			<div class = "form-group">
				<button type="submit" id="btnSave" name="find" value="find" class="btn btn-primary" style="margin-left:10px"><i
						class="glyphicon glyphicon-search"></i> Find
				</button>
				<button class = "btn btn-default btn-xs" data-action = "reload"><i
						class = "glyphicon glyphicon-refresh"></i> Tải lại trang
				</button>
			</div>
        </form>
	</div>

	<table class="table datatable-responsive">
		<thead>
			<tr>
				<th>STT</th>
				<th>Người gửi</th>
				<th>Người nhận</th>
				<th>Loại mẫu</th>
				<th>Tiêu đề</th>
				<th>Ngày gửi</th>
				<th>Trạng thái</th>
				<th>Ghi chú</th>
				<th class="text-center">Nội dung</th>
			</tr>
		</thead>
		<tbody>
		
		<?php
			if(isset($list_log_email_sms)) {
				$stt = 1;
				foreach ($list_log_email_sms as $list) {
					echo '<tr>';
					echo '<td>' . $stt . '</td>';
					echo '<td>' . get_username_by_id($list->created_by) . '</td>';
					echo '<td>' . $list->receiver . '</td>';
					echo '<td>' . $list->msg_type . '</td>';
					echo '<td>' . $list->tieu_de . '</td>';
					echo '<td>' . $list->created_at . '</td>';
					echo '<td>' . $list->trang_thai . '</td>';
					echo '<td>' . $list->ghi_chu . '</td>';
					echo '<td class="text-center" style="width: 200px">
					<a title="Chi tiết nội dung" href="'.base_url('tutor/report/detail/').'/'.$list->id.'" target="_blank"><i class="icon-grid3"></i></a>';
					echo '</tr>';
					$stt++;
				}
			}
		?>
		</tbody>
	</table>
</div>
<!-- /basic responsive configuration -->
