<!-- Page container -->
<div class="page-container">

	<!-- Page content -->
	<div class="page-content">

		<!-- Main sidebar -->
		<div class="sidebar sidebar-main sidebar-fixed">
			<div class="sidebar-content">
				<!-- User menu -->
				<?php if ($this->session->userdata('user_data')) { ?>
					<div class="sidebar-user">
						<div class="category-content">
							<div class="media">
								<a href="#" class="media-left">
									<?php if ($this->session->userdata('user_data')['avatar']) : ?>
										<img src="<?php echo $this->session->userdata('user_data')['avatar']; ?>"
											 class="img-circle img-sm" alt="">
									<?php else: ?>
										<img src="<?php echo base_url('upload/avatar/avatar.png') ?>"
											 class="img-circle img-sm" alt="">
									<?php endif; ?>
								</a>

								<div class="media-body">
								<span class="media-heading text-semibold">
					<?php
						echo $this->session->userdata('FullName');
					?>
				</span>

									<div class="text-size-mini text-muted">
										<i class="icon-pin text-size-small"></i> &nbsp; HN
									</div>
								</div>

								<div class="media-right media-middle">
									<ul class="icons-list">
										<li>
											<a href="#"><i class="icon-user-plus"></i></a>
										</li>
									</ul>
								</div>
							</div>
						</div>
					</div>
				<?php } ?>
				<!-- /user menu -->

				<!-- Main navigation -->
				<div class="sidebar-category sidebar-category-visible">
					<div class="category-content no-padding">
						<ul class="navigation navigation-main navigation-accordion">
							<!-- Main -->
							<?php
								if ($this->session->userdata('user_data')['role_id'] < 3) {
									//Khong cho GV xem
									?>
									<li <?php if (isset($active_home)) echo $active_home; ?>><a
											href="<?php echo base_url('tutor/dashboard') ?>"><i
												class="glyphicon glyphicon-home"></i> <span>Dashboard</span></a>
									</li>
									<li><a href="#"><i class="glyphicon glyphicon-cog"></i>
											<span>Quản trị hệ thống</span></a>
										<ul>
											<li <?php if (isset($active_account)) echo $active_account; ?>><a
													href="<?php echo base_url('tutor/account') ?>"><i
														class="icon-users"></i> Quản lý người dùng</a></li>
											<li <?php if (isset($active_assumption_group)) echo $active_assumption_group; ?>>
												<a
													href="<?php echo base_url('tutor/assumption_group') ?>"><i
														class="glyphicon glyphicon-book"></i> Từ điển hệ thống</a>
											</li>
											<li <?php if (isset($active_setting_mail)) echo $active_setting_mail; ?>>
												<a
													href="<?php echo base_url('tutor/setting_mail') ?>"><i
														class="glyphicon glyphicon-envelope"></i> Setting mail gửi
																								  đi</a>
											</li>
										</ul>
									</li>
									<li <?php if (isset($active_mon_hoc)) echo $active_mon_hoc; ?>><a
											href="<?php echo base_url('tutor/mon_hoc') ?>"><i
												class="glyphicon glyphicon-tasks"></i> <span>Tài liệu môn học</span></a>
									</li>
									<li><a href="#"><i class="glyphicon glyphicon-tasks"></i>
											<span>Quản lý giảng viên</span></a>
										<ul>
											<li <?php if (isset($active_syn)) echo $active_syn; ?>><a
													href="<?php echo base_url('tutor/teacher') ?>"><i
														class="icon-users"></i> Danh sách giảng viên</a>
											</li>
											<li <?php if (isset($active_import)) echo $active_import; ?>><a
													href="<?php echo base_url('tutor/teacher/import') ?>"><i
														class="glyphicon glyphicon-import"></i> Import danh sách GV</a>
											</li>
										</ul>
									</li>

									<li><a href="#"><i class="glyphicon glyphicon-tasks"></i>
											<span>Quản lý kế hoạch học</span></a>
										<ul>
											<li <?php if (isset($active_plan)) echo $active_plan; ?>><a
													href="<?php echo base_url('tutor/plan') ?>"><i
														class="glyphicon glyphicon-tasks"></i> Danh sách kế hoạch</a>
											</li>
											<li <?php if (isset($active_osp)) echo $active_osp; ?>><a
													href="<?php echo base_url('tutor/osp') ?>"><i
														class="glyphicon glyphicon-tasks"></i> Danh sách OSP100</a>
											</li>
											<li <?php if (isset($active_osp_import)) echo $active_osp_import; ?>>
												<a
													href="<?php echo base_url('tutor/osp/import') ?>"><i
														class="glyphicon glyphicon-import"></i> Import danh sách OSP100</a>
											</li>
										</ul>
									</li>
									<li><a href="#"><i class="glyphicon glyphicon-envelope"></i>
											<span>Mail kế hoạch giảng dạy</span></a>
										<ul>
											<li <?php if (isset($active_semester)) echo $active_semester; ?>><a
													href="<?php echo base_url('tutor/plan_mail/semester') ?>"><i
														class="glyphicon glyphicon-send"></i> Gửi mail theo quý</a>
											</li>
											<li <?php if (isset($active_startcourse)) echo $active_startcourse; ?>><a
													href="<?php echo base_url('tutor//plan_mail/startcourse') ?>"><i
														class="glyphicon glyphicon-send"></i> Gửi mail theo đợt học</a>
											</li>
										</ul>
									</li>
								<?php
								}
								//Khong cho GV xem
							?>

							<li><a href="#"><i class="glyphicon glyphicon-envelope"></i>
									<span>Quản lý mẫu email và SMS</span></a>
								<ul>
									<li <?php if (isset($active_template)) echo $active_template; ?>><a
											href="<?php echo base_url('tutor/template') ?>"><i
												class="glyphicon glyphicon-tasks"></i> Danh sách mẫu</a>
									</li>
									<li <?php if (isset($active_task)) echo $active_task; ?>><a
											href="<?php echo base_url('tutor/task') ?>"><i
												class="glyphicon glyphicon-tasks"></i> Danh sách chức năng auto</a>
									</li>
									<li <?php if (isset($active_send_mail)) echo $active_send_mail; ?>><a
											href="<?php echo base_url('tutor/send_mail_remind') ?>"><i
												class="glyphicon glyphicon-tasks"></i> Mail nhắc GV ký chứng từ</a>
									</li>
								</ul>
							</li>
							<li <?php if (isset($active_bao_cao)) echo $active_bao_cao; ?>><a
									href="<?php echo base_url('tutor/report') ?>"><i
										class="glyphicon glyphicon-file"></i> <span>Báo cáo</span></a>
							</li>
							<?php
								$email_cm = get_template_by_module('M003');
								if ($email_cm) {
									$day_sent = $email_cm->ngay_gui;
									$day_now  = date('N') + 1;
									//Neu trung nhau cho chay tiep
									if ($day_sent != $day_now) {
										?>
										<li <?php if (isset($active_report_cm)) echo $active_report_cm; ?>><a
												href="<?php echo base_url('tutor/notification/check_post_cm') ?>"><i
													class="glyphicon glyphicon-file"></i> <span>Kiểm tra giảng viên chưa post chào mừng</span></a>
										</li>
									<?php
									}
								}

								$email_kpi = get_template_by_module('M004');
								if ($email_kpi) {
									$day_sent = $email_kpi->ngay_gui;
									$day_now  = date('N') + 1;
									//Neu trung nhau cho chay tiep
									if ($day_sent != $day_now) {
										?>
										<li <?php if (isset($active_report_tlm)) echo $active_report_tlm; ?>><a
												href="<?php echo base_url('tutor/notification/check_post_tlm') ?>"><i
													class="glyphicon glyphicon-file"></i> <span>Kiểm tra giảng viên chưa post đủ định mức</span></a>
										</li>
									<?php
									}
								}

							?>
							<!-- /main -->

						</ul>
					</div>
				</div>
				<!-- /main navigation -->

			</div>
		</div>
		<!-- /main sidebar -->
