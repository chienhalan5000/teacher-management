<!-- Main content -->
<div class="content-wrapper">

	<!-- Page header -->
	<div class="page-header page-header-default">
		<div class="breadcrumb-line">
			<ul class="breadcrumb">
				<li><a href="<?php echo base_url() ?>"><i class="icon-home2 position-left"></i> Trang chủ</a></li>
				<li class="active">Kiểm tra giảng viên chưa post đủ bài định mức tuần</li>
			</ul>
		</div>
	</div>
	<!-- /page header -->

	<!-- Content area -->
	<div class="content">
		<!-- Basic responsive configuration -->
		<div class="panel panel-flat">

			<div class="panel-body">

				<form action="<?php echo site_url('tutor/notification/check_post_tlm') ?>" id="form" method="post"
					  class="form-horizontal" enctype="multipart/form-data">

					<div class="row">
						<div class="form-group">
							<button style="margin-left: 10px" type="submit" name="get_data" value="get_data"
									class="btn btn-primary btn-xs"><i
									class="glyphicon glyphicon-import"></i> Lọc dữ liệu
							</button>

								<button type="submit" name="export" value="export"
										class="btn btn-success btn-xs"><i
										class="glyphicon glyphicon-export"></i> Export excel
								</button>
						</div>
					</div>
				</form>
			</div>
			<table class="table datatable-responsive">
				<thead>
				<tr>
					<th>STT</th>
					<th>Mã trường</th>
					<th>Tên giảng viên</th>
					<th>Loại giảng viên</th>
					<th>Số điện thoại</th>
					<th>Tên course</th>
					<th>Ngày bắt đầu</th>
					<th>Ngày kết thúc</th>
					<th>Ngày thi</th>
					<th>Số bài đã post trong tuần</th>
					<th>Tổng số bài post</th>
					<th>Trợ giảng</th>
				</tr>
				</thead>
				<tbody>
				<?php
					if (isset($list_course)) {
						$stt = 1;
						foreach ($list_course as $course) {
							echo '<tr>';
							echo '<td>' . $stt . '</td>';
							echo '<td>' . $course->system . '</td>';
							echo '<td>' . $course->ten_gv . '</td>';
							echo '<td>' . $course->loai_gv . '</td>';
							echo '<td>' . $course->mobile_number . '</td>';
							echo '<td>' . $course->course_name . '</td>';
							echo '<td>' . date('d/m/Y',$course->start_date_course) . '</td>';
							echo '<td>' . date('d/m/Y',$course->end_date_course) . '</td>';
							echo '<td>' . date('d/m/Y',$course->exam_date_course) . '</td>';
							echo '<td>' . $course->post_tlm . '</td>';
							echo '<td>' . $course->total_post . '</td>';
							echo '<td>' . $course->tro_giang . '</td>';
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
