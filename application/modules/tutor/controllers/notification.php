<?php
	/**
	 * Created by PhpStorm.
	 * User: danglx
	 * Email: lexuandang89@gmail.com
	 * Date: 3/23/17
	 * Time: 12:59 AM
	 */
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Notification extends CI_controller
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->model('assumption_m');
			$this->load->model('dm_course_m');
			$this->load->model('dm_mon_m');
			$this->load->model('teachers_m');
			$this->load->model('login/users_m');
			$this->load->model('gd_teacher_course_m');
			$this->load->model('gd_teacher_tma_m');
			$this->load->model('send_h2472_m');
			$this->load->model('log_email_sms_m');
			$this->load->model('templates_m');
			$this->load->model('setting_mail_m');

			include APPPATH . 'third_party/mail/mail.php';
		}

		/**
		 * Lấy dữ liệu course từ các trường
		 *
		 * @param string $action
		 * @param int    $number_of_day
		 * Trang thai test: Ok
		 */
		public function get_data($action = '', $number_of_day = 0)
		{
			//autotutor/tutor/notification/get_data/cm/3: lay thong tin cac course sap bat dau truoc 3 ngay
			//autotutor/tutor/notification/get_data/tlm: lay thong tin cac course dang hoc o tuan 2-7
			$input          = array();
			$input['where'] = array('categories_id' => 4, 'enable_yn' => 'Y');
			//Lay danh sach cac truong
			$list_school = $this->assumption_m->get_list($input);
			if ($list_school) {
				foreach ($list_school as $school) {
					$code_lms = strtolower($school->code);
//					if ($school->code != 'HOU' && $action != '') {

						$curl = curl_init();
						$url  = "http://elearning.$code_lms.topica.vn/api/auto_tutors/forum.php?action=$action&day=$number_of_day";

						curl_setopt_array($curl, array(
							CURLOPT_URL            => $url,
							CURLOPT_RETURNTRANSFER => TRUE,
							CURLOPT_ENCODING       => "",
							CURLOPT_MAXREDIRS      => 10,
							CURLOPT_TIMEOUT        => 60,
							CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
							CURLOPT_CUSTOMREQUEST  => "GET",
							CURLOPT_HTTPHEADER     => array(
								"authorization: Basic YXV0b190dXRvcjp0b3BpY2FAMTIzIyM=",
								"cache-control: no-cache",
							),
						));

						$response = curl_exec($curl);
						$err      = curl_error($curl);

						curl_close($curl);

						if ($err) {
							echo "cURL Error #:" . $err;
						} else {
							$response = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response);
							$data     = json_decode($response);
							if ($data->status == 200) {
								$stt = 1;
								foreach ($data->courses as $course) {
									//Neu ma mon khac Null
									if ($course->ma_mon != '') {
										//Kiem tra mon da co hay chua
										$mon_info = $this->dm_mon_m->get_info_rule(array('ma_mon' => $course->ma_mon));
										if (!$mon_info) {
											//Them thong tin course
											$data = array(
												'ma_mon'     => $course->ma_mon,
												'ten_mon'    => $course->ten_mon,
												'created_by' => 1,
												'created_at' => date('Y-m-d H:i:s', time())
											);

											$dm_mon_id = $this->dm_mon_m->insert($data);


										}
										//Kiem tra course da co hay chua
										$input_course = array('system' => $school->code, 'id_course_lms' => $course->id);
										$course_info  = $this->dm_course_m->get_info_rule($input_course);
										if (!$course_info) {
											//Them thong tin course
											$data = array(
												'system'            => $school->code,
												'id_course_lms'     => $course->id,
												'course_name'       => $course->ten_course,
												'start_date_course' => $course->ngay_bat_dau_course,
												'end_date_course'   => $course->ngay_ket_thuc_course,
												'exam_date_course'  => $course->ngay_thi,
												'forumid'           => $course->forumid,
												'ma_mon'            => $course->ma_mon,
												'created_at'        => date('Y-m-d H:i:s', time())
											);

											$dm_course_id = $this->dm_course_m->insert($data);

										} else {
											//Lay thong tin dm_course_id
											$dm_course_id = $course_info->id;
										}

										if ($dm_course_id) {
											$ghi_chu = '';
											if ($action == 'cm') {
												$ghi_chu = 'Course sắp bắt đầu sau ' . $number_of_day . ' ngày';
											}
											$gvcm      = $course->gvcm;
											$gvhd      = $course->gvhd;
											$tro_giang = $course->tro_giang;
//Kiem tra thong tin tro giang
											$tro_giang_id = 0;
											if ($tro_giang != NULL) {
												$tro_giang_fullname = $tro_giang->lastname . ' ' . $tro_giang->firstname;
												//Kiem tra user tro giang da co hay chua
												$tro_giang_info = $this->users_m->get_info_rule(array('username' => $tro_giang->username));
												if (!$tro_giang_info) {
													//Them thong tin tro giang
													$data = array(
														'username'      => $tro_giang->username,
														'firstname'     => $tro_giang->firstname,
														'lastname'      => $tro_giang->lastname,
														'email'         => $tro_giang->email,
														'email_person'  => $tro_giang->email_canhan,
														'mobile_number' => $tro_giang->topica_dienthoai,
														'role_id'       => 2,
														'created_by'    => 404,
														'created_at'    => date('Y-m-d H:i:s', time())
													);

													$tro_giang_id = $this->users_m->insert($data);

												} else {
													$tro_giang_id = $tro_giang_info->id;
												}
											}

											$gvcm_fullname = '';
											if ($gvcm != NULL) {
												$gvcm_fullname = $gvcm->lastname . ' ' . $gvcm->firstname;
											}

											$gvhd_fullname = '';
											if ($gvhd != NULL) {
												$gvhd_fullname = $gvhd->lastname . ' ' . $gvhd->firstname;
											}
											//Check GVCM
											if ($gvcm != NULL) {
												//Kiem tra user GVCM da co hay chua
												$gvcm_info = $this->teachers_m->get_info_rule(array('username' => $gvcm->username));
												if (!$gvcm_info) {
													//Them thong tin gvcm
													$data = array(
														'username'      => $gvcm->username,
														'firstname'     => $gvcm->firstname,
														'lastname'      => $gvcm->lastname,
														'email'         => $gvcm->email,
														'email_person'  => $gvcm->email_canhan,
														'mobile_number' => $gvcm->topica_dienthoai,
														'role_name'     => 'GVCM',
														'created_by'    => 404,
														'created_at'    => date('Y-m-d H:i:s', time())
													);

													$gvcm_id = $this->teachers_m->insert($data);

												} else {
													//Lay thong tin dm_course_id
													$gvcm_id = $gvcm_info->id;
												}
												if ($gvcm_id) {
													//Them du lieu hoat dong GVCM post bai trong course
													$data = array(
														'teacher_id'    => $gvcm_id,
														'tro_giang_id'  => $tro_giang_id,
														'dm_course_id'  => $dm_course_id,
														'gvcm_fullname' => $gvcm_fullname,
														'gvhd_fullname' => $gvhd_fullname,
														'post_cm'       => $gvcm->post_cm,
														'post_tlm'      => $gvcm->post_forum,
														'total_post'    => $gvcm->total_post_forum,
														'tim'           => $gvcm->tim,
														'week'          => $course->week,
														'week_start'    => $course->week_start,
														'week_end'      => $course->week_end,
														'action'        => $action,
														'ghi_chu'       => $ghi_chu,
														'created_at'    => date('Y-m-d H:i:s', time())
													);
													if ($action == 'cm' && $gvcm->post_cm > 0) {
														//Khong them vao DB

													} else {
														$this->gd_teacher_course_m->insert($data);
													}
												}
											}
											//End check GVCM

											//Check GVHD
											if ($gvhd != NULL) {
												//Kiem tra user GVHD da co hay chua
												$gvhd_info = $this->teachers_m->get_info_rule(array('username' => $gvhd->username));
												if (!$gvhd_info) {
													//Them thong tin gvhd
													$data = array(
														'username'      => $gvhd->username,
														'firstname'     => $gvhd->firstname,
														'lastname'      => $gvhd->lastname,
														'email'         => $gvhd->email,
														'email_person'  => $gvhd->email_canhan,
														'mobile_number' => $gvhd->topica_dienthoai,
														'role_name'     => 'GVHD',
														'created_by'    => 404,
														'created_at'    => date('Y-m-d H:i:s', time())
													);

													$gvhd_id = $this->teachers_m->insert($data);

												} else {
													//Lay thong tin dm_course_id
													$gvhd_id = $gvhd_info->id;
												}
												if ($gvhd_id) {
													//Them du lieu hoat dong GVHD post bai trong course
													$data = array(
														'teacher_id'    => $gvhd_id,
														'tro_giang_id'  => $tro_giang_id,
														'dm_course_id'  => $dm_course_id,
														'gvcm_fullname' => $gvcm_fullname,
														'gvhd_fullname' => $gvhd_fullname,
														'post_cm'       => $gvhd->post_cm,
														'post_tlm'      => $gvhd->post_forum,
														'total_post'    => $gvhd->total_post_forum,
														'tim'           => $gvhd->tim,
														'week'          => $course->week,
														'week_start'    => $course->week_start,
														'week_end'      => $course->week_end,
														'action'        => $action,
														'ghi_chu'       => $ghi_chu,
														'created_at'    => date('Y-m-d H:i:s', time())
													);


													if ($action == 'cm' && $gvhd->post_cm > 0) {
														//Khong them vao DB

													} else {
														$this->gd_teacher_course_m->insert($data);
													}
												}
											}
											//End check GVHD
										}

										$stt++;
									}
								}
							}
						}
//					}
				}
			}
		}

		/**
		 * M003 Gui mail thong bao post bai chao mung
		 * Author: danglx@topica.edu.vn
		 * Date: 2018-03-14
		 *
		 */
		public function send_email_post_cm()
		{
			//Lay mau email M003
			$email_template = get_template_by_module('M003');
			if ($email_template) {
				//Lay ngay trong tuan duoc setting cho module so sanh voi ngay hien tai
				$day_sent = $email_template->ngay_gui;
				$day_now  = date('N') + 1;
				//Neu trung nhau cho chay tiep
				if ($day_sent == $day_now) {
					//Lay so ngay sap bat dau course (8 la chu nhat trong tuan)
					$day_open_course = 8 - $day_now;

					//Lay thong tin course $day_open_course ngay nua la bat dau va check gv post bai CM
					$this->get_data('cm', $day_open_course);

					//Kiem tra ket qua course vưa lay co GV can nhac post bai CM hay khong
					$teacher_list = $this->gd_teacher_course_m->get_teacher_cm();
					$stt          = 1;
					$sent_success = 0;
					$sent_error   = 0;
					if ($teacher_list) {
						//Danh sach GV
						foreach ($teacher_list as $teacher) {

							$content = $email_template->content_mail;
							$subject = $email_template->tieu_de;
							//Lay danh sach cac course theo GV can nhac nho
							$course_list_of_teacher = $this->gd_teacher_course_m->get_post_cm_by_teacher($teacher->teacher_id);
							//set data log
							$data_log = array(
								'teacher_id' => $teacher->teacher_id,
								'receiver'   => $teacher->email,
								'msg_type'   => 'email',
								'msg_code'   => 'TMQ' . time(),
								'created_by' => 404,
								'created_at' => date('Y-m-d H:i:s', time())
							);

							//Check email
							if ($teacher->email != '') {
								if ($course_list_of_teacher) {
									$start_course = '';
									$fullname_gv  = $teacher->lastname . ' ' . $teacher->firstname;

									$style_th = ' style="border: 1px solid black;padding: 8px;color: #DBAC69;background-color: #082346;border-color: #082346;"';
									$style_td = ' style="border: 1px solid black;padding: 8px;"';

									$table_content = '<table style="border: 1px solid black; border-collapse: collapse;">';
									$table_content .= '<tr>';
									$table_content .= '<th ' . $style_th . '>Trường</th>';
									$table_content .= '<th ' . $style_th . '>Tên môn</th>';
									$table_content .= '<th ' . $style_th . '>Mã môn</th>';
									$table_content .= '<th ' . $style_th . '>Tên lớp học</th>';
									$table_content .= '<th ' . $style_th . '>Ngày bắt đầu KH</th>';
									$table_content .= '<th ' . $style_th . '>Ngày kết thúc KH</th>';
									$table_content .= '<th ' . $style_th . '>Ngày thi</th>';
									$table_content .= '<th ' . $style_th . '>Tên GVCM</th>';
									$table_content .= '<th ' . $style_th . '>Tên GVHD</th>';
									$table_content .= '<th ' . $style_th . '>Link tài liệu môn học</th>';
									$table_content .= '<th ' . $style_th . '>Trợ giảng phụ trách</th>';
									$table_content .= '</tr>';
									$stt_course          = 1;
									$arr_email_tro_giang = array();
									foreach ($course_list_of_teacher as $course) {
										//Lay thong tin tro giang
										$tro_giang_info = get_user_info_by_id($course->tro_giang_id);
										if ($tro_giang_info != NULL) {
											$tro_giang_fullname      = $tro_giang_info->lastname . ' ' . $tro_giang_info->firstname;
											$tro_giang_fullname_info = $tro_giang_fullname . '<br>Email: ' . $tro_giang_info->email . '<br>SĐT: ' . $tro_giang_info->mobile_number;
											//add vao $arr_email_tro_giang
											if (!in_array($tro_giang_fullname, $arr_email_tro_giang)) {
												$arr_email_tro_giang[$tro_giang_info->email] = $tro_giang_fullname;
											}
										} else {
											$tro_giang_fullname_info = '';
										}
										$table_content .= '<tr>';
										$table_content .= '<tr><td ' . $style_td . '>' . $course->system . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $course->ten_mon . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $course->ma_mon . '</td>';
										$table_content .= '<td ' . $style_td . '><a href="http://elearning.' . strtolower($course->system) . '.topica.vn/course/view.php?id=' . $course->id_course_lms . '">' . $course->course_name . '</a></td>';
										$table_content .= '<td ' . $style_td . '>' . date('d/m/Y', $course->start_date_course) . '</td>';
										$table_content .= '<td ' . $style_td . '>' . date('d/m/Y', $course->end_date_course) . '</td>';
										$table_content .= '<td ' . $style_td . '>' . date('d/m/Y', $course->exam_date_course) . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $course->gvcm_fullname . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $course->gvhd_fullname . '</td>';
										$link_document = '';
										if ($course->link_document != '') {
											$link_document = '<a href="' . $course->link_document . '">Xem tài liệu tại đây</a>';
										}
										$table_content .= '<td ' . $style_td . '>' . $link_document . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $tro_giang_fullname_info . '</td>';
										$table_content .= '</tr>';
										if ($stt_course == 1) {
											$start_course = $course->start_date_course;
										}
										$stt_course++;
										//update giao dich course da gui
										$this->gd_teacher_course_m->update($course->gdid, array('sent_yn' => 'Y', 'updated_at' => date('Y-m-d H:i:s', time())));
									}

									$table_content .= '</table>';

									if (strpos($content, "<!--ten_gv-->") && strpos($content, "<!--end_ten_gv-->")) {
										$content = preg_replace("/<!--ten_gv-->([^\!]*)<!--end_ten_gv-->/", $fullname_gv, $content);
									}

									//Thay deadline post bai CM

									$deadline_post_cm = date('d/m/Y', strtotime('-3 day', $start_course));
//Thay ten GV trong content_mail
									if (strpos($content, "<!--deadline_post_bai_chaomung-->") && strpos($content, "<!--end_deadline_post_bai_chaomung-->")) {
										$content = preg_replace("/<!--deadline_post_bai_chaomung-->([^\!]*)<!--end_deadline_post_bai_chaomung-->/", $deadline_post_cm, $content);
									}
									//Thay bang danh sach course trong content_mail
									if (strpos($content, "<!--table_ds_course-->") && strpos($content, "<!--end_table_ds_course-->")) {
										$content = preg_replace("/<!--table_ds_course-->([^\!]*)<!--end_table_ds_course-->/", $table_content, $content);
									}


									$content = '<html>' . $content . '</html>';

									//Tieu de mail
									//Thay ngay bat course
									if (strpos($subject, "<!--ngay_bat_dau_course-->") && strpos($subject, "<!--end_ngay_bat_dau_course-->")) {
										$subject = preg_replace("/<!--ngay_bat_dau_course-->([^\!]*)<!--end_ngay_bat_dau_course-->/", date('d/m/Y', $start_course), $subject);
									}

									//Email của người gửi
//								$from_email = array('email' => 'trungtamgiangvien@topica.edu.vn', 'name' => 'Trung tâm Quản trị và Phát triển Giảng viên');

									$mail       = $this->setting_mail_m->get_by_id(1);
									$from_email = array('email' => $mail->email, 'name' => $mail->name, 'password' => $mail->password);


//									$to_emails = array('sonnt@topica.edu.vn' => 'Nguyễn Thị Son');
									$to_emails = array($teacher->email => $fullname_gv);
//Gui toi email ca nhan cua GV
									if ($teacher->email_person != '') {
										$arr_email_tro_giang[$teacher->email_person] = $fullname_gv;
									}
//									$cc_emails = '';
									$cc_emails = $arr_email_tro_giang;

									//Gui email nhac nho
									$result_email = send_mail($content, $subject, $from_email, $to_emails, $cc_emails, 0);

									$data_log['tieu_de']  = $subject;
									$data_log['noi_dung'] = $content;

									if ($result_email == TRUE) {
										//data log gui thanh cong
										$data_log['trang_thai'] = 'success';
										$sent_success++;
									} else {
										//log gui loi
										$data_log['trang_thai'] = 'error';
										$data_log['ghi_chu']    = 'Gửi mail gặp lỗi';
										$sent_error++;
									}
								}

							} else {
								//Ghi log loi email khong co
								$data_log['noi_dung']   = '';
								$data_log['trang_thai'] = 'error';
								$data_log['ghi_chu']    = 'GV không có email';
								$sent_error++;
							}
							//Luu log gui email
							$this->log_email_sms_m->insert($data_log);
							$stt++;
						}

					}
					echo 'Sent ' . $sent_success . ' email success and  ' . $sent_error . ' email error to Teacher.';
				} else {
					echo 'Ngày gửi không khớp với setting';
				}
			} else {
				echo 'Module chưa được setting mẫu gửi email';
			}
		}

		/**
		 * S009 Gui SMS thong bao post bai chao mung
		 * Cho chay luc 10h30 hang ngay roi so sanh ngay gui trong tuan de lay ra so ngay sap mo course $day_open_course
		 * Author: danglx@topica.edu.vn
		 * Date: 2018-03-14
		 */
		public function send_sms_post_cm()
		{
			//Lay mau email S009
			$sms_template = get_template_by_module('S009');
			if ($sms_template) {
				//Lay ngay trong tuan duoc setting cho module so sanh voi ngay hien tai
				$day_sent = $sms_template->ngay_gui;
				$day_now  = date('N') + 1;
				//Neu trung nhau cho chay tiep
				if ($day_sent == $day_now) {
					//Lay so ngay sap bat dau course (8 la chu nhat trong tuan)
					$day_open_course = 8 - $day_now;
					//Lay thong tin course $day_open_course ngay nua la bat dau va check gv post bai CM
					$this->get_data('cm', $day_open_course);

					//Kiem tra ket qua course vưa lay co GV can nhac post bai CM hay khong
					$teacher_list = $this->gd_teacher_course_m->get_teacher_cm();
					$stt          = 1;
					$sent_success = 0;
					$sent_error   = 0;
					if ($teacher_list) {
						foreach ($teacher_list as $teacher) {
							$content = $sms_template->content_mail;
							//Lay danh sach cac course theo GV can nhac nho
							$course_list_of_teacher = $this->gd_teacher_course_m->get_post_cm_by_teacher($teacher->teacher_id);
							//set data log
							$msg_code = 'TMQ' . time();
							$data_log = array(
								'teacher_id' => $teacher->teacher_id,
								'receiver'   => $teacher->mobile_number,
								'msg_type'   => 'sms',
								'msg_code'   => $msg_code,
								'created_by' => 404,
								'created_at' => date('Y-m-d H:i:s', time())
							);

							//Check so dien thoai
							if ($teacher->mobile_number != '') {
								if ($course_list_of_teacher) {
									$start_course = '';

									$stt_course = 0;
									$lms        = array();
									foreach ($course_list_of_teacher as $course) {
										if (!in_array($course->system, $lms)) {
											array_push($lms, $course->system);
										}
										$stt_course++;
										if ($stt_course == 1) {
											$start_course = $course->start_date_course;
										}
										//update giao dich course da gui
										$this->gd_teacher_course_m->update($course->gdid, array('sent_yn' => 'Y', 'updated_at' => date('Y-m-d H:i:s', time())));
									}
									$deadline_post_cm = date('d/m/Y', strtotime('-3 day', $start_course));

									$lms_name = implode(',', $lms);

//Thay so course chua post
									if (strpos($content, "<!--so_course-->") && strpos($content, "<!--end_so_course-->")) {
										$content = preg_replace("/<!--so_course-->([^\!]*)<!--end_so_course-->/", $stt_course, $content);
									}
									//Thay ten LMS chua post
									if (strpos($content, "<!--ten_lms-->") && strpos($content, "<!--end_ten_lms-->")) {
										$content = preg_replace("/<!--ten_lms-->([^\!]*)<!--end_ten_lms-->/", $lms_name, $content);
									}
									//Thay deadline post
									if (strpos($content, "<!--deadline_post_cm-->") && strpos($content, "<!--end_deadline_post_cm-->")) {
										$content = preg_replace("/<!--deadline_post_cm-->([^\!]*)<!--end_deadline_post_cm-->/", $deadline_post_cm, $content);
									}

//Goi API gui SMS
//									$mobile = '0979927158';
									$mobile = $teacher->mobile_number;
									$curl   = curl_init();

									curl_setopt_array($curl, array(
										CURLOPT_URL            => "http://sms.topica.edu.vn/api/send_sms_vmg",
										CURLOPT_RETURNTRANSFER => TRUE,
										CURLOPT_ENCODING       => "",
										CURLOPT_MAXREDIRS      => 10,
										CURLOPT_TIMEOUT        => 30,
										CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
										CURLOPT_CUSTOMREQUEST  => "POST",
										CURLOPT_POSTFIELDS     => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"token\"\r\n\r\ntopica@!1208@\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"dien_thoai\"\r\n\r\n" . $mobile . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"noi_dung\"\r\n\r\n" . $content . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"code\"\r\n\r\n" . $msg_code . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"lms\"\r\n\r\nttm\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"khu_vuc\"\r\n\r\nHN\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"user_lms\"\r\n\r\nTMQ\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"msg_type\"\r\n\r\nTMQ\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"brand\"\r\n\r\nTOPICA\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
										CURLOPT_HTTPHEADER     => array(
											"cache-control: no-cache",
											"content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
											"postman-token: d804aecf-3cd5-36f0-4df6-62b0ae22ff75"
										),
									));
									$response = curl_exec($curl);
									$err      = curl_error($curl);

									curl_close($curl);

									if ($err) {
										echo "cURL Error #:" . $err;
									}
									//End add API
									//data log gui thanh cong
									$data_log['noi_dung']   = $content;
									$data_log['trang_thai'] = 'success';
									$sent_success++;
								}
							} else {
								//Ghi log loi email khong co
								$data_log['noi_dung']   = $teacher->username;
								$data_log['trang_thai'] = 'error';
								$data_log['ghi_chu']    = 'GV không có số điện thoại';
								$sent_error++;
							}
							//Luu log gui email
							$this->log_email_sms_m->insert($data_log);
							$stt++;
						}
					}
					echo 'Sent ' . $sent_success . ' sms success and  ' . $sent_error . ' sms error to Teacher.';
				} else {
					echo 'Ngày gửi không khớp với setting';
				}
			} else {
				echo 'Module chưa được setting mẫu gửi email';
			}
		}

		/**
		 * M004 Gui mail thong bao post bai dinh muc tuan cho GVCM
		 * Cho chay luc 9h30 hang ngay roi so sanh ngay gui trong tuan
		 * Author: danglx@topica.edu.vn
		 * Date: 2018-03-14
		 */
		public function send_email_kpi_gvcm()
		{
			//Lay mau email
			$email_template = get_template_by_module('M004');
			//Neu co template cho module
			if ($email_template) {
				$day_sent = $email_template->ngay_gui;
				$day_now  = date('N') + 1;
				//Neu trung nhau cho chay tiep
				if ($day_sent == $day_now) {
					//Kiem tra gio hien tai
					$run_time = date('G');
					//Chi cho phep chay luc 9h
					if ($run_time >= 8) {
						//Lay thong tin course $day_open_course ngay nua la bat dau va check gv post bai CM
						$this->get_data('tlm');

						//Kiem tra ket qua course vưa lay co GV can nhac post bai CM hay khong
						$teacher_list = $this->gd_teacher_course_m->get_teacher_tlm('GVCM');
						$stt          = 1;
						$sent_success = 0;
						$sent_error   = 0;
						if ($teacher_list) {
							//lay ra DS GV
							foreach ($teacher_list as $teacher) {
								$content = $email_template->content_mail;
								$subject = $email_template->tieu_de;
								//Lay danh sach cac course theo GV can nhac nho
								$course_list_of_teacher = $this->gd_teacher_course_m->get_post_tlm_by_teacher($teacher->teacher_id, $teacher->role_name);
								//Chi lay cac GV co course chua hoan thanh KPI
								if ($course_list_of_teacher) {
									//set data log
									$data_log = array(
										'teacher_id' => $teacher->teacher_id,
										'receiver'   => $teacher->email,
										'msg_type'   => 'email',
										'msg_code'   => 'TMQ' . time(),
										'created_by' => 404,
										'created_at' => date('Y-m-d H:i:s', time())
									);

									//Check email
									if ($teacher->email != '') {
										$start_course = '';
										$fullname     = $teacher->lastname . ' ' . $teacher->firstname;

										$style_th = ' style="border: 1px solid black;padding: 8px;color: #DBAC69;background-color: #082346;border-color: #082346;"';
										$style_td = ' style="border: 1px solid black;padding: 8px;"';

										$table_content = '<table style="border: 1px solid black; border-collapse: collapse;">';
										$table_content .= '<tr>';
										$table_content .= '<th ' . $style_th . '>Trường</th>';
										$table_content .= '<th ' . $style_th . '>Giảng viên</th>';
										$table_content .= '<th ' . $style_th . '>Lớp môn</th>';
										$table_content .= '<th ' . $style_th . '>Ngày bắt đầu KH</th>';
										$table_content .= '<th ' . $style_th . '>Ngày kết thúc KH</th>';
										$table_content .= '<th ' . $style_th . '>Tuần</th>';
										$table_content .= '<th ' . $style_th . '>TIM+</th>';
										$table_content .= '<th ' . $style_th . '>Số bài post</th>';
										$table_content .= '<th ' . $style_th . '>Tổng số bài post đến hiện tại</th>';
										$table_content .= '<th ' . $style_th . '>Trợ giảng hỗ trợ </th>';
										$table_content .= '</tr>';
										$stt_course          = 1;
										$arr_email_tro_giang = array();
										foreach ($course_list_of_teacher as $course) {

											//Lay thong tin tro giang
											$tro_giang_info = get_user_info_by_id($course->tro_giang_id);
//									$tro_giang_fullname_info = '';
											if ($tro_giang_info != NULL) {
												$tro_giang_fullname      = $tro_giang_info->lastname . ' ' . $tro_giang_info->firstname;
												$tro_giang_fullname_info = $tro_giang_fullname . '<br>Email: ' . $tro_giang_info->email . '<br>SĐT: ' . $tro_giang_info->mobile_number;
												//add vao $arr_email_tro_giang
												if (!in_array($tro_giang_fullname, $arr_email_tro_giang)) {
													$arr_email_tro_giang[$tro_giang_info->email] = $tro_giang_fullname;
												}
											} else {
												$tro_giang_fullname_info = '';
											}
											$table_content .= '<tr>';
											$table_content .= '<tr><td ' . $style_td . '>' . $course->system . '</td>';
											$table_content .= '<td ' . $style_td . '>' . $fullname . '</td>';
											$table_content .= '<td ' . $style_td . '><a href="http://elearning.' . strtolower($course->system) . '.topica.vn/course/view.php?id=' . $course->id_course_lms . '">' . $course->course_name . '</a></td>';
											$table_content .= '<td ' . $style_td . '>' . date('d/m/Y', $course->start_date_course) . '</td>';
											$table_content .= '<td ' . $style_td . '>' . date('d/m/Y', $course->end_date_course) . '</td>';
											$table_content .= '<td ' . $style_td . '>' . $course->week . '</td>';
											$table_content .= '<td ' . $style_td . '>' . $course->tim . '</td>';
											$table_content .= '<td ' . $style_td . '>' . $course->post_tlm . '</td>';
											$table_content .= '<td ' . $style_td . '>' . $course->total_post . '</td>';
											$table_content .= '<td ' . $style_td . '>' . $tro_giang_fullname_info . '</td>';
											$table_content .= '</tr>';
											if ($stt_course == 1) {
												$week_start = date('d/m/Y', $course->week_start);
												$week_end   = date('d/m/Y', $course->week_end);
											}
											$stt_course++;
											//update giao dich course da gui
											$this->gd_teacher_course_m->update($course->gdid, array('sent_yn' => 'Y', 'updated_at' => date('Y-m-d H:i:s', time())));
										}

										$table_content .= '</table>';

										//Ten giang vien day du
										$fullname_gv = $teacher->lastname . ' ' . $teacher->firstname;

										//Thay ten GV trong content_mail
										if (strpos($content, "<!--ten_gv-->") && strpos($content, "<!--end_ten_gv-->")) {
											$content = preg_replace("/<!--ten_gv-->([^\!]*)<!--end_ten_gv-->/", $fullname_gv, $content);
										}

										//Thay ngay bat dau tuan
										$week_start = date('d/m/Y', f_get_WeekStartDate());
										if (strpos($content, "<!--ngay_bat_dau_tuan-->") && strpos($content, "<!--end_ngay_bat_dau_tuan-->")) {
											$content = preg_replace("/<!--ngay_bat_dau_tuan-->([^\!]*)<!--end_ngay_bat_dau_tuan-->/", $week_start, $content);
										}

										//Thay ngay ket thuc tuan
										$week_end = date('d/m/Y', f_get_WeekEndDate());
										if (strpos($content, "<!--ngay_ket_thuc_tuan-->") && strpos($content, "<!--end_ngay_ket_thuc_tuan-->")) {
											$content = preg_replace("/<!--ngay_ket_thuc_tuan-->([^\!]*)<!--end_ngay_ket_thuc_tuan-->/", $week_end, $content);
										}
										//Thay bang danh sach bai tap trong content_mail
										if (strpos($content, "<!--table_ds_course-->") && strpos($content, "<!--end_table_ds_course-->")) {
											$content = preg_replace("/<!--table_ds_course-->([^\!]*)<!--end_table_ds_course-->/", $table_content, $content);
										}
										//So ngay con lai cua tuan
//									$date_remain = date_diff(date_create(date('Y-m-d', time())), date_create(date('Y-m-d', $course->week_end)));
//									$day_remain  = $date_remain->d + 1;
										$deadline_post = date('d/m/Y', strtotime('-1 day', f_get_WeekEndDate()));
										if (strpos($content, "<!--ngay_thu_bay-->") && strpos($content, "<!--end_ngay_thu_bay-->")) {
											$content = preg_replace("/<!--ngay_thu_bay-->([^\!]*)<!--end_ngay_thu_bay-->/", $deadline_post, $content);
										}

										$content = '<html>' . $content . '</html>';

										//Tieu de mail
										//Thay ngay bat dau tuan
										if (strpos($subject, "<!--ngay_bat_dau_tuan-->") && strpos($subject, "<!--end_ngay_bat_dau_tuan-->")) {
											$subject = preg_replace("/<!--ngay_bat_dau_tuan-->([^\!]*)<!--end_ngay_bat_dau_tuan-->/", $week_start, $subject);
										}
										//Thay ngay ket thuc tuan
										if (strpos($subject, "<!--ngay_ket_thuc_tuan-->") && strpos($subject, "<!--end_ngay_ket_thuc_tuan-->")) {
											$subject = preg_replace("/<!--ngay_ket_thuc_tuan-->([^\!]*)<!--end_ngay_ket_thuc_tuan-->/", $week_end, $subject);
										}

										//Email của người gửi
//									$from_email = array('email' => 'trungtamgiangvien@topica.edu.vn', 'name' => 'Trung tâm Quản trị và Phát triển Giảng viên');
										$mail       = $this->setting_mail_m->get_by_id(1);
										$from_email = array('email' => $mail->email, 'name' => $mail->name, 'password' => $mail->password);


//										$to_emails = array('sonnt@topica.edu.vn' => 'Nguyễn Thị Son');
										$to_emails = array($teacher->email => $fullname_gv);
										//Gui toi email ca nhan cua GV
										if ($teacher->email_person != '') {
											$arr_email_tro_giang[$teacher->email_person] = $fullname_gv;
										}
//									$cc_emails = '';

										$cc_emails = $arr_email_tro_giang;

										//Gui email nhac nho
										$result_email = send_mail($content, $subject, $from_email, $to_emails, $cc_emails, 0);

										$data_log['tieu_de']  = $subject;
										$data_log['noi_dung'] = $content;

										if ($result_email == TRUE) {
											//data log gui thanh cong
											$data_log['trang_thai'] = 'success';
											$sent_success++;
										} else {
											//log gui loi
											$data_log['trang_thai'] = 'error';
											$data_log['ghi_chu']    = 'Gửi mail gặp lỗi';
											$sent_error++;
										}

									} else {
										//Ghi log loi email khong co
										$data_log['noi_dung']   = '';
										$data_log['trang_thai'] = 'error';
										$data_log['ghi_chu']    = 'GV không có email';
										$sent_error++;
									}
									//Luu log gui email
									$this->log_email_sms_m->insert($data_log);
									$stt++;

								}
							}
						}
						echo 'Sent ' . $sent_success . ' email success and  ' . $sent_error . ' email error to Teacher.';
					} else {
						echo 'Thời gian này không cho chạy hệ thống';
					}
				} else {
					echo 'Ngày gửi không khớp với setting';
				}
			} else {
				echo 'Chua co mau cho chuc nang nay';
			}
		}

		/**
		 * M005 Gui mail thong bao post bai dinh muc tuan cho GVHD
		 * Cho chay luc 9h30 hang ngay roi so sanh ngay gui trong tuan
		 * Author: danglx@topica.edu.vn
		 * Date: 2018-03-14
		 */
		public function send_email_kpi_gvhd()
		{
			//Lay mau email
			$email_template = get_template_by_module('M005');
			//Neu co template cho module
			if ($email_template) {
				$day_sent = $email_template->ngay_gui;
				$day_now  = date('N') + 1;
				//Neu trung nhau cho chay tiep
				if ($day_sent == $day_now) {
					//Kiem tra gio hien tai
					$run_time = date('G');
					//Chi cho phep chay luc 9h
					if ($run_time >= 8) {
						//Lay thong tin course $day_open_course ngay nua la bat dau va check gv post bai CM
//						$this->get_data('tlm');

						//Kiem tra ket qua course vưa lay co GV can nhac post bai CM hay khong
						$teacher_list = $this->gd_teacher_course_m->get_teacher_tlm('GVHD');
						$stt          = 1;
						$sent_success = 0;
						$sent_error   = 0;
						if ($teacher_list) {
							//lay ra DS GV
							foreach ($teacher_list as $teacher) {
								$content = $email_template->content_mail;
								$subject = $email_template->tieu_de;
								//Lay danh sach cac course theo GV can nhac nho
								$course_list_of_teacher = $this->gd_teacher_course_m->get_post_tlm_by_teacher($teacher->teacher_id, $teacher->role_name);
								//Chi lay cac GV co course chua hoan thanh KPI
								if ($course_list_of_teacher) {
									//set data log
									$data_log = array(
										'teacher_id' => $teacher->teacher_id,
										'receiver'   => $teacher->email,
										'msg_type'   => 'email',
										'msg_code'   => 'TMQ' . time(),
										'created_by' => 404,
										'created_at' => date('Y-m-d H:i:s', time())
									);

									//Check email
									if ($teacher->email != '') {
										$start_course = '';
										$fullname     = $teacher->lastname . ' ' . $teacher->firstname;

										$style_th = ' style="border: 1px solid black;padding: 8px;color: #DBAC69;background-color: #082346;border-color: #082346;"';
										$style_td = ' style="border: 1px solid black;padding: 8px;"';

										$table_content = '<table style="border: 1px solid black; border-collapse: collapse;">';
										$table_content .= '<tr>';
										$table_content .= '<th ' . $style_th . '>Trường</th>';
										$table_content .= '<th ' . $style_th . '>Giảng viên</th>';
										$table_content .= '<th ' . $style_th . '>Lớp môn</th>';
										$table_content .= '<th ' . $style_th . '>Ngày bắt đầu KH</th>';
										$table_content .= '<th ' . $style_th . '>Ngày kết thúc KH</th>';
										$table_content .= '<th ' . $style_th . '>Tuần</th>';
										$table_content .= '<th ' . $style_th . '>TIM+</th>';
										$table_content .= '<th ' . $style_th . '>Số bài post</th>';
										$table_content .= '<th ' . $style_th . '>Tổng số bài post đến hiện tại</th>';
										$table_content .= '<th ' . $style_th . '>Trợ giảng hỗ trợ </th>';
										$table_content .= '</tr>';
										$stt_course          = 1;
										$arr_email_tro_giang = array();
										foreach ($course_list_of_teacher as $course) {

											//Lay thong tin tro giang
											$tro_giang_info = get_user_info_by_id($course->tro_giang_id);
//									$tro_giang_fullname_info = '';
											if ($tro_giang_info != NULL) {
												$tro_giang_fullname      = $tro_giang_info->lastname . ' ' . $tro_giang_info->firstname;
												$tro_giang_fullname_info = $tro_giang_fullname . '<br>Email: ' . $tro_giang_info->email . '<br>SĐT: ' . $tro_giang_info->mobile_number;
												//add vao $arr_email_tro_giang
												if (!in_array($tro_giang_fullname, $arr_email_tro_giang)) {
													$arr_email_tro_giang[$tro_giang_info->email] = $tro_giang_fullname;
												}
											} else {
												$tro_giang_fullname_info = '';
											}
											$table_content .= '<tr>';
											$table_content .= '<tr><td ' . $style_td . '>' . $course->system . '</td>';
											$table_content .= '<td ' . $style_td . '>' . $fullname . '</td>';
											$table_content .= '<td ' . $style_td . '><a href="http://elearning.' . strtolower($course->system) . '.topica.vn/course/view.php?id=' . $course->id_course_lms . '">' . $course->course_name . '</a></td>';
											$table_content .= '<td ' . $style_td . '>' . date('d/m/Y', $course->start_date_course) . '</td>';
											$table_content .= '<td ' . $style_td . '>' . date('d/m/Y', $course->end_date_course) . '</td>';
											$table_content .= '<td ' . $style_td . '>' . $course->week . '</td>';
											$table_content .= '<td ' . $style_td . '>' . $course->tim . '</td>';
											$table_content .= '<td ' . $style_td . '>' . $course->post_tlm . '</td>';
											$table_content .= '<td ' . $style_td . '>' . $course->total_post . '</td>';
											$table_content .= '<td ' . $style_td . '>' . $tro_giang_fullname_info . '</td>';
											$table_content .= '</tr>';
											if ($stt_course == 1) {
												$week_start = date('d/m/Y', $course->week_start);
												$week_end   = date('d/m/Y', $course->week_end);
											}
											$stt_course++;
											//update giao dich course da gui
											$this->gd_teacher_course_m->update($course->gdid, array('sent_yn' => 'Y', 'updated_at' => date('Y-m-d H:i:s', time())));
										}

										$table_content .= '</table>';

										//Ten giang vien day du
										$fullname_gv = $teacher->lastname . ' ' . $teacher->firstname;

										//Thay ten GV trong content_mail
										if (strpos($content, "<!--ten_gv-->") && strpos($content, "<!--end_ten_gv-->")) {
											$content = preg_replace("/<!--ten_gv-->([^\!]*)<!--end_ten_gv-->/", $fullname_gv, $content);
										}

										//Thay ngay bat dau tuan
										$week_start = date('d/m/Y', f_get_WeekStartDate());
										if (strpos($content, "<!--ngay_bat_dau_tuan-->") && strpos($content, "<!--end_ngay_bat_dau_tuan-->")) {
											$content = preg_replace("/<!--ngay_bat_dau_tuan-->([^\!]*)<!--end_ngay_bat_dau_tuan-->/", $week_start, $content);
										}

										//Thay ngay ket thuc tuan
										$week_end = date('d/m/Y', f_get_WeekEndDate());
										if (strpos($content, "<!--ngay_ket_thuc_tuan-->") && strpos($content, "<!--end_ngay_ket_thuc_tuan-->")) {
											$content = preg_replace("/<!--ngay_ket_thuc_tuan-->([^\!]*)<!--end_ngay_ket_thuc_tuan-->/", $week_end, $content);
										}
										//Thay bang danh sach bai tap trong content_mail
										if (strpos($content, "<!--table_ds_course-->") && strpos($content, "<!--end_table_ds_course-->")) {
											$content = preg_replace("/<!--table_ds_course-->([^\!]*)<!--end_table_ds_course-->/", $table_content, $content);
										}
										//So ngay con lai cua tuan
//									$date_remain = date_diff(date_create(date('Y-m-d', time())), date_create(date('Y-m-d', $course->week_end)));
//									$day_remain  = $date_remain->d + 1;
										$deadline_post = date('d/m/Y', strtotime('-1 day', f_get_WeekEndDate()));
										if (strpos($content, "<!--ngay_thu_bay-->") && strpos($content, "<!--end_ngay_thu_bay-->")) {
											$content = preg_replace("/<!--ngay_thu_bay-->([^\!]*)<!--end_ngay_thu_bay-->/", $deadline_post, $content);
										}

										$content = '<html>' . $content . '</html>';

										//Tieu de mail
										//Thay ngay bat dau tuan
										if (strpos($subject, "<!--ngay_bat_dau_tuan-->") && strpos($subject, "<!--end_ngay_bat_dau_tuan-->")) {
											$subject = preg_replace("/<!--ngay_bat_dau_tuan-->([^\!]*)<!--end_ngay_bat_dau_tuan-->/", $week_start, $subject);
										}
										//Thay ngay ket thuc tuan
										if (strpos($subject, "<!--ngay_ket_thuc_tuan-->") && strpos($subject, "<!--end_ngay_ket_thuc_tuan-->")) {
											$subject = preg_replace("/<!--ngay_ket_thuc_tuan-->([^\!]*)<!--end_ngay_ket_thuc_tuan-->/", $week_end, $subject);
										}

										//Email của người gửi
//									$from_email = array('email' => 'trungtamgiangvien@topica.edu.vn', 'name' => 'Trung tâm Quản trị và Phát triển Giảng viên');
										$mail       = $this->setting_mail_m->get_by_id(1);
										$from_email = array('email' => $mail->email, 'name' => $mail->name, 'password' => $mail->password);


//										$to_emails = array('sonnt@topica.edu.vn' => 'Nguyễn Thị Son');
										$to_emails = array($teacher->email => $fullname_gv);
//Gui toi email ca nhan cua GV
										if ($teacher->email_person != '') {
											$arr_email_tro_giang[$teacher->email_person] = $fullname_gv;
										}
//									$cc_emails = '';
										$cc_emails = $arr_email_tro_giang;

										//Gui email nhac nho
										$result_email = send_mail($content, $subject, $from_email, $to_emails, $cc_emails, 0);

										$data_log['tieu_de']  = $subject;
										$data_log['noi_dung'] = $content;

										if ($result_email == TRUE) {
											//data log gui thanh cong
											$data_log['trang_thai'] = 'success';
											$sent_success++;
										} else {
											//log gui loi
											$data_log['trang_thai'] = 'error';
											$data_log['ghi_chu']    = 'Gửi mail gặp lỗi';
											$sent_error++;
										}

									} else {
										//Ghi log loi email khong co
										$data_log['noi_dung']   = '';
										$data_log['trang_thai'] = 'error';
										$data_log['ghi_chu']    = 'GV không có email';
										$sent_error++;
									}
									//Luu log gui email
									$this->log_email_sms_m->insert($data_log);
									$stt++;

								}
							}
						}
						echo 'Sent ' . $sent_success . ' email success and  ' . $sent_error . ' email error to Teacher.';
					} else {
						echo 'Thời gian này không cho chạy hệ thống';
					}
				} else {
					echo 'Ngày gửi không khớp với setting';
				}
			} else {
				echo 'Chua co mau cho chuc nang nay';
			}
		}

		/**
		 * Lay du lieu H2472 tu cac truong
		 * Trang thai test: Ok
		 */
		public function get_data_h2472()
		{
			//kiem tra quyen truy cap
			$input          = array();
			$input['where'] = array('categories_id' => 4, 'enable_yn' => 'Y');
			//Lay danh sach cac truong
			$list_school = $this->assumption_m->get_list($input);
			if ($list_school) {
				foreach ($list_school as $school) {
					$code_lms = strtolower($school->code);
					//Rieng HOU1 đang lỗi lấy API nên bỏ qua
//					if ($school->code != 'HOU') {
						$curl = curl_init();
						$url  = "http://elearning.$code_lms.topica.vn/api/auto_tutors/h2472.php";
						curl_setopt_array($curl, array(
							CURLOPT_URL            => $url,
							CURLOPT_RETURNTRANSFER => TRUE,
							CURLOPT_ENCODING       => "",
							CURLOPT_MAXREDIRS      => 10,
							CURLOPT_TIMEOUT        => 60,
							CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
							CURLOPT_CUSTOMREQUEST  => "GET",
							CURLOPT_HTTPHEADER     => array(
								"authorization: Basic YXV0b190dXRvcjp0b3BpY2FAMTIzIyM=",
								"cache-control: no-cache",
							),
						));

						$response = curl_exec($curl);
						$err      = curl_error($curl);
						curl_close($curl);

						if ($err) {
							echo "cURL Error #:" . $err;
						} else {
							$response = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response);
							$data     = json_decode($response);

							if ($data->status == 200) {
								$stt = 1;
								foreach ($data->threads as $thread) {
									$tro_giang = $thread->tro_giang;
									//Kiem tra thong tin tro giang
									$tro_giang_id = 0;
									if ($tro_giang != NULL) {
										//Kiem tra user tro giang da co hay chua
										$tro_giang_info = $this->users_m->get_info_rule(array('username' => $tro_giang->username));
										if (!$tro_giang_info) {
											//Them thong tin tro giang
											$data = array(
												'username'      => $tro_giang->username,
												'firstname'     => $tro_giang->firstname,
												'lastname'      => $tro_giang->lastname,
												'email'         => $tro_giang->email,
												'email_person'  => $tro_giang->email_canhan,
												'mobile_number' => $tro_giang->topica_dienthoai,
												'role_id'       => 2,
												'created_by'    => 404,
												'created_at'    => date('Y-m-d H:i:s', time())
											);

											$tro_giang_id = $this->users_m->insert($data);

										} else {
											$tro_giang_id = $tro_giang_info->id;
										}
									}
									//Them du lieu
									$data = array(
										'system'             => $school->code,
										'tro_giang_id'       => $tro_giang_id,
										'thread_id'          => $thread->thread_id,
										'answer_id'          => $thread->answer_id,
										'thread_name'        => $thread->thread_name,
										'id_course_lms'      => $thread->id_course_lms,
										'course_name'        => $thread->shortname,
										'student_name'       => $thread->student_name,
										'thoi_gian_hoi'      => $thread->thoi_gian_hoi,
										'delay'              => $thread->delay,
										'userid_teacher_lms' => $thread->userid_gv_lms,
										'teacher_username'   => $thread->username_gv,
										'created_at'         => date('Y-m-d H:i:s', time())
									);

									$this->send_h2472_m->insert($data);

									$stt++;
								}
							}
						}
//					}
				}
			} else {
				return 0;
			}
		}

		/**
		 * M007 Gui email thong bao cau hoi H2472 can tra loi
		 * Cho chay co dinh luc 9h va 17h hang ngay
		 * Trang thai test ok
		 */
		public function send_email_h2472()
		{
			//Kiem tra gio hien tai. Chi cho phep tiep tuc neu la 10h hoac 17h
			$run_time = date('G');
//			if ($run_time == 10 || $run_time == 17) {
			if ($run_time >= 8) {
				//Lay tat ca cac cau hoi chua tra loi tu cac he thong LMS
				$this->get_data_h2472();
				//Lay danh sach GV co cau hoi chua tra loi
				$teachers_arr = $this->send_h2472_m->get_teacher_h2472();
				$sent_success = 0;
				$sent_error   = 0;
				if ($teachers_arr != NULL) {
					//Lấy mau email M007
					$email_template = get_template_by_module('M007');

					//Neu co template cho module
					if ($email_template) {
						foreach ($teachers_arr as $teacher) {
							//set data log
							$data_log = array(
								'teacher_id' => $teacher->teacher_id,
								'receiver'   => $teacher->email,
								'msg_type'   => 'email',
								'msg_code'   => 'TMQ' . time(),
								'created_by' => 404,
								'created_at' => date('Y-m-d H:i:s', time())
							);

							if ($teacher->email != '') {
								$h2472_arr = $this->send_h2472_m->get_h2472_by_teacher($teacher->username);
								if ($h2472_arr) {
									//Tao du lieu bang H2472 gui email
									$fullname_gv = $teacher->lastname . ' ' . $teacher->firstname;

									$style_th = ' style="border: 1px solid black;padding: 8px;color: #DBAC69;background-color: #082346;border-color: #082346;"';
									$style_td = ' style="border: 1px solid black;padding: 8px;"';

									$table_content = '<table style="border: 1px solid black; border-collapse: collapse;">';
									$table_content .= '<tr>';
									$table_content .= '<th ' . $style_th . '>Trường</th>';
									$table_content .= '<th ' . $style_th . '>ID chủ đề</th>';
									$table_content .= '<th ' . $style_th . '>Chủ đề</th>';
									$table_content .= '<th ' . $style_th . '>Lớp môn</th>';
									$table_content .= '<th ' . $style_th . '>Người hỏi</th>';
									$table_content .= '<th ' . $style_th . '>Thời gian hỏi</th>';
									$table_content .= '<th ' . $style_th . '>Độ trễ</th>';
									$table_content .= '<th ' . $style_th . '>Người trả lời</th>';
									$table_content .= '<th ' . $style_th . '>Link trả lời nhanh</th>';
									$table_content .= '<th ' . $style_th . '>Trợ giảng hỗ trợ </th>';
									$table_content .= '</tr>';

									$stt_h2472           = 0;
									$arr_email_tro_giang = array();
									foreach ($h2472_arr as $h2472) {
										//Lay thong tin tro giang
										$tro_giang_info          = get_user_info_by_id($h2472->tro_giang_id);
										$tro_giang_fullname_info = '';
										if ($tro_giang_info != NULL) {
											$tro_giang_fullname      = $tro_giang_info->lastname . ' ' . $tro_giang_info->firstname;
											$tro_giang_fullname_info = $tro_giang_fullname . '<br>Email: ' . $tro_giang_info->email . '<br>SĐT: ' . $tro_giang_info->mobile_number;
											//add vao $arr_email_tro_giang
											if (!in_array($tro_giang_fullname, $arr_email_tro_giang)) {
												$arr_email_tro_giang[$tro_giang_info->email] = $tro_giang_fullname;
											}
										}
										$table_content .= '<tr>';
										$table_content .= '<tr><td ' . $style_td . '>' . $h2472->system . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $h2472->thread_id . '</td>';
										$table_content .= '<td ' . $style_td . '><a href="http://elearning.' . strtolower($h2472->system) . '.topica.vn/h2472/?act=answers&do=detail&id=' . $h2472->thread_id . '">' . $h2472->thread_name . '</a></td>';
										$table_content .= '<td ' . $style_td . '><a href="http://elearning.' . strtolower($h2472->system) . '.topica.vn/course/view.php?id=' . $h2472->id_course_lms . '">' . $h2472->course_name . '</a></td>';
										$table_content .= '<td ' . $style_td . '>' . $h2472->student_name . '</td>';
										$table_content .= '<td ' . $style_td . '>' . date('d/m/Y', $h2472->thoi_gian_hoi) . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $h2472->delay . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $fullname_gv . '</td>';
										$table_content .= '<td ' . $style_td . '><a href="http://elearning.' . strtolower($h2472->system) . '.topica.vn/h2472/?act=fastreply&qid=' . base64_encode($h2472->answer_id) . '&assignid=' . base64_encode($h2472->userid_teacher_lms) . '">Link nhanh</a></td>';
										$table_content .= '<td ' . $style_td . '>' . $tro_giang_fullname_info . '</td>';
										$table_content .= '</tr>';
										//update giao dich course da gui
										$this->send_h2472_m->update($h2472->gdid, array('sent_yn' => 'Y', 'updated_at' => date('Y-m-d H:i:s', time())));
										$stt_h2472++;
									}
									$table_content .= '</table>';

									//Noi dung chinh cua email
									$content = $email_template->content_mail;

									//Thay ten GV trong content_mail
									if (strpos($content, "<!--ten_gv-->") && strpos($content, "<!--end_ten_gv-->")) {
										$content = preg_replace("/<!--ten_gv-->([^\!]*)<!--end_ten_gv-->/", $fullname_gv, $content);
									}
									//Thay bang danh sach bai tap trong content_mail
									if (strpos($content, "<!--table_ds_course-->") && strpos($content, "<!--end_table_ds_course-->")) {
										$content = preg_replace("/<!--table_ds_course-->([^\!]*)<!--end_table_ds_course-->/", $table_content, $content);
									}
									$content = '<html>' . $content . '</html>';
									//Tieu de mail
									$subject = $email_template->tieu_de;

									//Email của người gửi
//									$from_email = array('email' => 'trungtamgiangvien@topica.edu.vn', 'name' => 'Trung tâm Quản trị và Phát triển Giảng viên');

									$mail       = $this->setting_mail_m->get_by_id(1);
									$from_email = array('email' => $mail->email, 'name' => $mail->name, 'password' => $mail->password);


//									$to_emails = array('sonnt@topica.edu.vn' => 'Nguyễn Thị Son');
									$to_emails = array($teacher->email => $fullname_gv);
									//Gui toi email ca nhan cua GV
									if ($teacher->email_person != '') {
										$arr_email_tro_giang[$teacher->email_person] = $fullname_gv;
									}
//									$cc_emails = '';
									$cc_emails = $arr_email_tro_giang;

									//Gui email nhac nho
									$result_email = send_mail($content, $subject, $from_email, $to_emails, $cc_emails, 0);

									$data_log['tieu_de']  = $subject;
									$data_log['noi_dung'] = $content;

									if ($result_email == TRUE) {
										//data log gui thanh cong
										$data_log['trang_thai'] = 'success';
										$sent_success++;
									} else {
										//log gui loi
										$data_log['trang_thai'] = 'error';
										$data_log['ghi_chu']    = 'Gửi mail gặp lỗi';
										$sent_error++;
									}
								}
							} else {
								//Ghi log loi email khong co
								$data_log['noi_dung']   = $teacher->email;
								$data_log['trang_thai'] = 'error';
								$data_log['ghi_chu']    = 'GV không có email';
								$sent_error++;
							}
							//Luu log gui email
							$this->log_email_sms_m->insert($data_log);

						}
					} else {
						echo 'Module chưa được setting mẫu gửi email';
					}
				}
				echo 'Sent ' . $sent_success . ' email success and  ' . $sent_error . ' email error to Teacher.';
			} else {
				echo 'Thời gian này không cho chạy hệ thống';
			}
		}

		/**
		 * Lay du lieu GVCM cham bai tap TMA
		 * Trang thai test: OK
		 */
		public function get_data_tma()
		{
			$deadline_tma = date('Y-m-d', strtotime('-1 day', time()));
//			$deadline_tma = '2018-03-21';

			$input          = array();
			$input['where'] = array('categories_id' => 4, 'enable_yn' => 'Y');
			//Lay danh sach cac truong
			$list_school = $this->assumption_m->get_list($input);
			if ($list_school) {
				foreach ($list_school as $school) {
					$code_lms = strtolower($school->code);
//					if ($school->code != 'HOU') {

						$curl = curl_init();
						$url  = "http://elearning.$code_lms.topica.vn/api/auto_tutors/tma.php";

						curl_setopt_array($curl, array(
							CURLOPT_URL            => $url,
							CURLOPT_RETURNTRANSFER => TRUE,
							CURLOPT_ENCODING       => "",
							CURLOPT_MAXREDIRS      => 10,
							CURLOPT_TIMEOUT        => 30,
							CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
							CURLOPT_CUSTOMREQUEST  => "POST",
							CURLOPT_POSTFIELDS     => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"day\"\r\n\r\n" . $deadline_tma . "\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
							CURLOPT_HTTPHEADER     => array(
								"authorization: Basic YXV0b190dXRvcjp0b3BpY2FAMTIzIyM=",
								"cache-control: no-cache",
								"content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
								"postman-token: 3e9bf4c1-b58a-0bf9-c4ba-71cbe503a042"
							),
						));

						$response = curl_exec($curl);
						$err      = curl_error($curl);

						curl_close($curl);

						if ($err) {
							echo "cURL Error #:" . $err;
						} else {
							$response = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $response);
							$data     = json_decode($response);
							if ($data->status == 200) {
								$stt = 1;
								foreach ($data->courses as $course) {
									$ghi_chu   = '';
									$gvcm      = $course->gvcm;
									$tro_giang = $course->tro_giang;
									//Kiem tra thong tin tro giang
									$tro_giang_id = 0;
									if ($tro_giang != NULL) {
										//Kiem tra user tro giang da co hay chua
										$tro_giang_info = $this->users_m->get_info_rule(array('username' => $tro_giang->username));
										if (!$tro_giang_info) {
											//Them thong tin tro giang
											$data = array(
												'username'      => $tro_giang->username,
												'firstname'     => $tro_giang->firstname,
												'lastname'      => $tro_giang->lastname,
												'email'         => $tro_giang->email,
												'email_person'  => $tro_giang->email_canhan,
												'mobile_number' => $tro_giang->topica_dienthoai,
												'role_id'       => 2,
												'created_by'    => 404,
												'created_at'    => date('Y-m-d H:i:s', time())
											);

											$tro_giang_id = $this->users_m->insert($data);

										} else {
											$tro_giang_id = $tro_giang_info->id;
										}
									}

									//Check GVCM
									if ($gvcm != NULL) {
										//Kiem tra user GVCM da co hay chua
										$gvcm_info = $this->teachers_m->get_info_rule(array('username' => $gvcm->username));
										if (!$gvcm_info) {
											//Them thong tin gvcm
											$data = array(
												'username'      => $gvcm->username,
												'firstname'     => $gvcm->firstname,
												'lastname'      => $gvcm->lastname,
												'email'         => $gvcm->email,
												'email_person'  => $gvcm->email_canhan,
												'mobile_number' => $gvcm->topica_dienthoai,
												'role_name'     => 'GVCM',
												'created_by'    => 404,
												'created_at'    => date('Y-m-d H:i:s', time())
											);

											$gvcm_id = $this->teachers_m->insert($data);

										} else {
											//Lay thong tin dm_course_id
											$gvcm_id = $gvcm_info->id;
										}
										if ($gvcm_id) {
											//Them du lieu hoat dong GVCM cham bai TMA trong course
											$data = array(
												'system'        => $school->code,
												'teacher_id'    => $gvcm_id,
												'tro_giang_id'  => $tro_giang_id,
												'id_course_lms' => $course->course_id,
												'ten_course'    => $course->ten_course,
												'loai_bai_tap'  => $course->loai_bai_tap,
												'san_pham_id'   => $course->san_pham_id,
												'bai_tap_id'    => $course->bai_tap_id,
												'ten_san_pham'  => $course->ten_san_pham,
												'deadline'      => $course->deadline,
												'chua_cham'     => $course->chua_cham,
												'da_cham'       => $course->da_cham,
												'created_at'    => date('Y-m-d H:i:s', time())
											);
											$this->gd_teacher_tma_m->insert($data);

										}
									}
									//End check GVCM
									$stt++;
								}
							}
						}
//					}
				}
			}
		}

		/**
		 * M006 gui mai thong bao GVCM cham bai TMA
		 * Chi cho chay co dinh luc 10h ngay thu 2,5,7
		 * Trang thai test: Ok
		 */
		public function send_email_tma()
		{
			//Kiem tra gio hien tai
			$run_time = date('G');
			//Chi cho phep chay luc 10h
			if ($run_time >= 8) {
				//Lay du lieu GV cham BTN, BTKN
				$this->get_data_tma();
				//Lay danh sach GV co bai can cham
				$teachers_arr = $this->gd_teacher_tma_m->get_teacher_tma();

				$sent_success = 0;
				$sent_error   = 0;
				if ($teachers_arr != NULL) {
					$deadline_cham_tma = date('d/m/Y', strtotime('+5 day', time()));
					//Lấy mau email M006
					$email_template = get_template_by_module('M006');
					//Neu co template cho module
					if ($email_template) {
						foreach ($teachers_arr as $teacher) {
							//set data log
							$data_log = array(
								'teacher_id' => $teacher->teacher_id,
								'receiver'   => $teacher->email,
								'msg_type'   => 'email',
								'msg_code'   => 'TMQ' . time(),
								'created_by' => 404,
								'created_at' => date('Y-m-d H:i:s', time())
							);

							if ($teacher->email != '') {
								$tma_arr = $this->gd_teacher_tma_m->get_tma_by_teacher($teacher->teacher_id);

								if ($tma_arr) {
									//Tao du lieu bang tma gui email

									$style_th = ' style="border: 1px solid black;padding: 8px;color: #DBAC69;background-color: #082346;border-color: #082346;"';
									$style_td = ' style="border: 1px solid black;padding: 8px;"';

									$table_content = '<table style="border: 1px solid black; border-collapse: collapse;">';
									$table_content .= '<tr>';
									$table_content .= '<th ' . $style_th . '>STT</th>';
									$table_content .= '<th ' . $style_th . '>Trường</th>';
									$table_content .= '<th ' . $style_th . '>Lớp môn</th>';
									$table_content .= '<th ' . $style_th . '>Loại BT</th>';
									$table_content .= '<th ' . $style_th . '>Tên sản phẩm</th>';
									$table_content .= '<th ' . $style_th . '>Số bài đã chấm</th>';
									$table_content .= '<th ' . $style_th . '>Số bài cần chấm</th>';
									$table_content .= '<th ' . $style_th . '>Deadline chấm</th>';
									$table_content .= '<th ' . $style_th . '>Trợ giảng hỗ trợ</th>';
									$table_content .= '</tr>';

									$stt                 = 1;
									$arr_email_tro_giang = array();
									foreach ($tma_arr as $tma) {
										//Lay thong tin tro giang
										$tro_giang_info          = get_user_info_by_id($tma->tro_giang_id);
										$tro_giang_fullname_info = '';
										if ($tro_giang_info != NULL) {
											$tro_giang_fullname      = $tro_giang_info->lastname . ' ' . $tro_giang_info->firstname;
											$tro_giang_fullname_info = $tro_giang_fullname . '<br>Email: ' . $tro_giang_info->email . '<br>SĐT: ' . $tro_giang_info->mobile_number;
											//add vao $arr_email_tro_giang
											if (!in_array($tro_giang_fullname, $arr_email_tro_giang)) {
												$arr_email_tro_giang[$tro_giang_info->email] = $tro_giang_fullname;
											}
										}

										$table_content .= '<tr>';
										$table_content .= '<td ' . $style_td . '>' . $stt . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $tma->system . '</td>';
										$table_content .= '<td ' . $style_td . '><a href="http://elearning.' . strtolower($tma->system) . '.topica.vn/course/view.php?id=' . $tma->id_course_lms . '">' . $tma->ten_course . '</a></td>';
										$table_content .= '<td ' . $style_td . '>' . $tma->loai_bai_tap . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $tma->ten_san_pham . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $tma->da_cham . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $tma->chua_cham . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $deadline_cham_tma . '</td>';
										$table_content .= '<td ' . $style_td . '>' . $tro_giang_fullname_info . '</td>';
										$table_content .= '</tr>';
										//update giao dich course da gui
										$this->gd_teacher_tma_m->update($tma->id, array('sent_yn' => 'Y', 'updated_at' => date('Y-m-d H:i:s', time())));
										$stt++;
									}
									$table_content .= '</table>';
									//Noi dung chinh cua email
									$content = $email_template->content_mail;
									//Ten giang vien day du
									$fullname_gv = $teacher->lastname . ' ' . $teacher->firstname;
									//Thay ten GV trong content_mail
									if (strpos($content, "<!--ten_gv-->") && strpos($content, "<!--end_ten_gv-->")) {
										$content = preg_replace("/<!--ten_gv-->([^\!]*)<!--end_ten_gv-->/", $fullname_gv, $content);
									}
									//Thay bang danh sach bai tap trong content_mail
									if (strpos($content, "<!--table_ds_course-->") && strpos($content, "<!--end_table_ds_course-->")) {
										$content = preg_replace("/<!--table_ds_course-->([^\!]*)<!--end_table_ds_course-->/", $table_content, $content);
									}
									$content = '<html>' . $content . '</html>';
									//Tieu de mail
									$subject = $email_template->tieu_de;
									//Thay ngay bat dau tuan
									if (strpos($subject, "<!--ngay_bat_dau_tuan-->") && strpos($subject, "<!--end_ngay_bat_dau_tuan-->")) {
										$week_start = date('d/m/Y', f_get_WeekStartDate());
										$subject    = preg_replace("/<!--ngay_bat_dau_tuan-->([^\!]*)<!--end_ngay_bat_dau_tuan-->/", $week_start, $subject);
									}
									//Thay ngay ket thuc tuan
									if (strpos($subject, "<!--ngay_ket_thuc_tuan-->") && strpos($subject, "<!--end_ngay_ket_thuc_tuan-->")) {
										$week_end = date('d/m/Y', f_get_WeekEndDate());
										$subject  = preg_replace("/<!--ngay_ket_thuc_tuan-->([^\!]*)<!--end_ngay_ket_thuc_tuan-->/", $week_end, $subject);
									}

									//Email của người gửi
//									$from_email = array('email' => 'trungtamgiangvien@topica.edu.vn', 'name' => 'Trung tâm Quản trị và Phát triển Giảng viên');
									$mail       = $this->setting_mail_m->get_by_id(1);
									$from_email = array('email' => $mail->email, 'name' => $mail->name, 'password' => $mail->password);


//									$to_emails = array('sonnt@topica.edu.vn' => 'Nguyễn Thị Son');
									$to_emails = array($teacher->email => $fullname_gv);
//Gui toi email ca nhan cua GV
									if ($teacher->email_person != '') {
										$arr_email_tro_giang[$teacher->email_person] = $fullname_gv;
									}
//									$cc_emails = '';
									$cc_emails = $arr_email_tro_giang;
									//Gui email nhac nho
									$result_email = send_mail($content, $subject, $from_email, $to_emails, $cc_emails, 0);

									$data_log['tieu_de']  = $subject;
									$data_log['noi_dung'] = $content;

									if ($result_email == TRUE) {
										//data log gui thanh cong
										$data_log['trang_thai'] = 'success';
										$sent_success++;
									} else {
										//log gui loi
										$data_log['trang_thai'] = 'error';
										$data_log['ghi_chu']    = 'Gửi mail gặp lỗi';
										$sent_error++;
									}
								}
							} else {
								//Ghi log loi email khong co
								$data_log['noi_dung']   = '';
								$data_log['trang_thai'] = 'error';
								$data_log['ghi_chu']    = 'GV không có email';
								$sent_error++;
							}
							//Luu log gui email
							$this->log_email_sms_m->insert($data_log);

						}

					} else {
						echo 'Module chưa được setting mẫu gửi email';
					}
				}
				echo 'Sent ' . $sent_success . ' email success and  ' . $sent_error . ' email error to Teacher.';
			} else {
				echo 'Thời gian này không cho chạy hệ thống';
			}
		}

		/**
		 * Check GV chua post bai chao mung
		 */
		public function check_post_cm()
		{
			$user_data = check_access();

			$title['title']             = 'Kiểm tra giảng viên chưa post bài chào mừng';
			$active['active_report_cm'] = 'class="active"';
			$form                       = array();
			if ($this->input->post('get_data') == "get_data") {
				$day_now = date('N') + 1;
				//Lay so ngay sap bat dau course (8 la chu nhat trong tuan)
				$day_open_course = 8 - $day_now;

				//Lay thong tin course $day_open_course ngay nua la bat dau va check gv post bai CM
				$this->get_data('cm', $day_open_course);
			}

			$form['list_course'] = get_teacher_post_cm();
			//end loc du lieu

			if ($this->input->post('export') == "export") {
				$list_course = get_teacher_post_cm('export');

				if ($list_course == NULL) {
					//Thong bao
					echo "<script>alert('Bạn vui lòng chọn lại lọc dữ liệu');</script>";
				} else {
					$this->load->library("excel");
					$objPHPExcel = new Excel();

					$objPHPExcel->getActiveSheet()->setCellValue("E3", "DANH SÁCH GIẢNG VIÊN CHƯA POST BÀI CHAO MỪNG")
						->getStyle("E3")->getFont()->setBold(TRUE)->setName("Times New Romans")->setSize(11);
					$objPHPExcel->getActiveSheet()->mergeCells('E3:I3');

					$objPHPExcel->getActiveSheet()->setCellValue("B4", "Ngày xuất: " . date('d/m/Y H:i:s', time()))
						->getStyle("B4")->getFont()->setName("Times New Romans")->setSize(11);
					$objPHPExcel->getActiveSheet()->mergeCells('B4:C4');

					//Tieu de danh sach du lieu
					$headings = array('STT', 'Tên giảng viên', 'Loại GV', 'Số điện thoại', 'Mã trường', 'Tên course', 'Ngày bắt đầu online', 'Trợ giảng phụ trách');
					//Set title sheet
					$objPHPExcel->getActiveSheet()->setTitle('Danh sách giảng viên');

					//set header row height and start header
					$objPHPExcel->getActiveSheet()->getRowDimension('6')->setRowHeight(45);

					//set column widths
					$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
					$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
					$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
					$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
					$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
					$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
					$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);

					$styleArrayContent     = array(
						'font'      => array(
							'bold'  => FALSE,
							'color' => array('rgb' => '000000'),
							'size'  => 11,
							'name'  => 'Times New Roman'
						),
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
						),
						'borders'   => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN
							)
						)
					);
					$styleArrayContentLeft = array(
						'font'      => array(
							'bold'  => FALSE,
							'color' => array('rgb' => '000000'),
							'size'  => 11,
							'name'  => 'Times New Roman'
						),
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
							'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
						),
						'borders'   => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN
							)
						)
					);
//			Style header
					$styleArrayTitle = array(
						'font'      => array(
							'bold' => TRUE,
							'size' => 11,
							'name' => 'Times New Roman',
						),
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
						),
						'borders'   => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN
							)
						)
					);
					$objPHPExcel->getActiveSheet()->getStyle("A6:H6")->applyFromArray($styleArrayTitle);

//Set background header

					$objPHPExcel->getActiveSheet()->getStyle('A6:H6')->getFill()->applyFromArray(
						array(
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
						)
					);
//Header bat dau tu dong thu 7
					$rowNumber = 6;
					$col       = 'A';
					foreach ($headings as $heading) {
						$objPHPExcel->getActiveSheet()->setCellValue($col . $rowNumber, $heading);
						$objPHPExcel->getActiveSheet()->getStyle($col . $rowNumber)->getAlignment()->setWrapText(TRUE);
						$col++;
					}
//Lay danh sach OSP trong bang tmp

					// Loop through the result set
					$rowCount = 7; //Row start data export
					$stt      = 1;
					foreach ($list_course as $course) {
						$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $stt)
							->getStyle('A' . $rowCount)->applyFromArray($styleArrayContent);

						$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $course->ten_gv)
							->getStyle('B' . $rowCount)->applyFromArray($styleArrayContentLeft);;
						$objPHPExcel->getActiveSheet()->getStyle('B' . $rowCount)->getAlignment()->setWrapText(TRUE);

						$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $course->loai_gv)
							->getStyle('C' . $rowCount)->applyFromArray($styleArrayContentLeft);;
						$objPHPExcel->getActiveSheet()->getStyle('C' . $rowCount)->getAlignment()->setWrapText(TRUE);

						$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $course->mobile_number)
							->getStyle('D' . $rowCount)->applyFromArray($styleArrayContent);;

						$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $course->system)
							->getStyle('E' . $rowCount)->applyFromArray($styleArrayContent);

						$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $course->course_name)
							->getStyle('F' . $rowCount)->applyFromArray($styleArrayContentLeft);

						$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, date('d/m/Y', $course->start_date_course))
							->getStyle('G' . $rowCount)->applyFromArray($styleArrayContent);

						$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $course->tro_giang)
							->getStyle('H' . $rowCount)->applyFromArray($styleArrayContent);

						$stt++;
						$rowCount++;
					}

					//Paper size
					$objPHPExcel->getActiveSheet()
						->getPageSetup()
						->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
//			->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
					$objPHPExcel->getActiveSheet()
						->getPageSetup()
						->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

					//Page margins
					$objPHPExcel->getActiveSheet()
						->getPageMargins()->setTop(1);
					$objPHPExcel->getActiveSheet()
						->getPageMargins()->setRight(0.5);
					$objPHPExcel->getActiveSheet()
						->getPageMargins()->setLeft(0.5);
					$objPHPExcel->getActiveSheet()
						->getPageMargins()->setBottom(1);

					//Header and footer
					$objPHPExcel->getActiveSheet()
						->getHeaderFooter()
						->setOddHeader('&C&HPlease treat this document as confidential!');
					$objPHPExcel->getActiveSheet()
						->getHeaderFooter()
						->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle());

					// Save as an Excel BIFF (xls) file
					$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="Danh sach GV' . time() . '.xlsx"');
					header('Cache-Control: max-age=0');

					$objWriter->save('php://output');
				}
			}

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('check_post_cm_view', $form);
			$this->load->view('footer');
		}

		/**
		 * Check GV chua post du bai dinh muc
		 */
		public function check_post_tlm()
		{
			$user_data = check_access();

			$title['title']              = 'Kiểm tra giảng viên chưa post đủ bài định mức tuần';
			$active['active_report_tlm'] = 'class="active"';
			$form                        = array();
			if ($this->input->post('get_data') == "get_data") {
				//Lay thong tin course $day_open_course ngay nua la bat dau va check gv post bai CM
				$this->get_data('tlm');

			}

			$form['list_course'] = get_teacher_post_tlm();
			//end loc du lieu

			if ($this->input->post('export') == "export") {
				$list_course = get_teacher_post_tlm('export');

				if ($list_course == NULL) {
					//Thong bao
					echo "<script>alert('Bạn vui lòng chọn lại lọc dữ liệu');</script>";
				} else {
					$this->load->library("excel");
					$objPHPExcel = new Excel();

					$objPHPExcel->getActiveSheet()->setCellValue("E3", "DANH SÁCH GIẢNG VIÊN CHƯA POST ĐỦ BÀI ĐỊNH MỨC TUẦN")
						->getStyle("E3")->getFont()->setBold(TRUE)->setName("Times New Romans")->setSize(11);
					$objPHPExcel->getActiveSheet()->mergeCells('E3:I3');

					$objPHPExcel->getActiveSheet()->setCellValue("B4", "Ngày xuất: " . date('d/m/Y H:i:s', time()))
						->getStyle("B4")->getFont()->setName("Times New Romans")->setSize(11);
					$objPHPExcel->getActiveSheet()->mergeCells('B4:C4');

					//Tieu de danh sach du lieu
					$headings = array('STT', 'Mã trường', 'Tên giảng viên', 'Loại GV', 'Số điện thoại', 'Tên course', 'Ngày bắt đầu', 'Ngày kết thúc', 'Ngày thi', 'Số bài đã post trong tuần', 'Tổng số bài post', 'Trợ giảng phụ trách');
					//Set title sheet
					$objPHPExcel->getActiveSheet()->setTitle('Danh sách giảng viên');

					//set header row height and start header
					$objPHPExcel->getActiveSheet()->getRowDimension('6')->setRowHeight(45);

					//set column widths
					$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
					$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
					$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
					$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
					$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
					$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
					$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
					$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
					$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
					$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
					$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
					$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);

					$styleArrayContent     = array(
						'font'      => array(
							'bold'  => FALSE,
							'color' => array('rgb' => '000000'),
							'size'  => 11,
							'name'  => 'Times New Roman'
						),
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
						),
						'borders'   => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN
							)
						)
					);
					$styleArrayContentLeft = array(
						'font'      => array(
							'bold'  => FALSE,
							'color' => array('rgb' => '000000'),
							'size'  => 11,
							'name'  => 'Times New Roman'
						),
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
							'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
						),
						'borders'   => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN
							)
						)
					);
//			Style header
					$styleArrayTitle = array(
						'font'      => array(
							'bold' => TRUE,
							'size' => 11,
							'name' => 'Times New Roman',
						),
						'alignment' => array(
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
						),
						'borders'   => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN
							)
						)
					);
					$objPHPExcel->getActiveSheet()->getStyle("A6:L6")->applyFromArray($styleArrayTitle);

//Set background header

					$objPHPExcel->getActiveSheet()->getStyle('A6:L6')->getFill()->applyFromArray(
						array(
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
						)
					);
//Header bat dau tu dong thu 7
					$rowNumber = 6;
					$col       = 'A';
					foreach ($headings as $heading) {
						$objPHPExcel->getActiveSheet()->setCellValue($col . $rowNumber, $heading);
						$objPHPExcel->getActiveSheet()->getStyle($col . $rowNumber)->getAlignment()->setWrapText(TRUE);
						$col++;
					}
//Lay danh sach OSP trong bang tmp

					// Loop through the result set
					$rowCount = 7; //Row start data export
					$stt      = 1;
					foreach ($list_course as $course) {
						$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $stt)
							->getStyle('A' . $rowCount)->applyFromArray($styleArrayContent);

						$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $course->system)
							->getStyle('B' . $rowCount)->applyFromArray($styleArrayContentLeft);;
						$objPHPExcel->getActiveSheet()->getStyle('B' . $rowCount)->getAlignment()->setWrapText(TRUE);

						$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $course->ten_gv)
							->getStyle('C' . $rowCount)->applyFromArray($styleArrayContentLeft);;
						$objPHPExcel->getActiveSheet()->getStyle('C' . $rowCount)->getAlignment()->setWrapText(TRUE);

						$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $course->loai_gv)
							->getStyle('D' . $rowCount)->applyFromArray($styleArrayContent);;

						$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $course->mobile_number)
							->getStyle('E' . $rowCount)->applyFromArray($styleArrayContent);

						$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $course->course_name)
							->getStyle('F' . $rowCount)->applyFromArray($styleArrayContentLeft);

						$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, date('d/m/Y', $course->start_date_course))
							->getStyle('G' . $rowCount)->applyFromArray($styleArrayContent);

						$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, date('d/m/Y', $course->end_date_course))
							->getStyle('H' . $rowCount)->applyFromArray($styleArrayContent);
						$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, date('d/m/Y', $course->exam_date_course))
							->getStyle('I' . $rowCount)->applyFromArray($styleArrayContent);
						$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $course->post_tlm)
							->getStyle('J' . $rowCount)->applyFromArray($styleArrayContent);
						$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $course->total_post)
							->getStyle('K' . $rowCount)->applyFromArray($styleArrayContent);
						$objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $course->tro_giang)
							->getStyle('L' . $rowCount)->applyFromArray($styleArrayContent);

						$stt++;
						$rowCount++;
					}

					//Paper size
					$objPHPExcel->getActiveSheet()
						->getPageSetup()
						->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
//			->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
					$objPHPExcel->getActiveSheet()
						->getPageSetup()
						->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

					//Page margins
					$objPHPExcel->getActiveSheet()
						->getPageMargins()->setTop(1);
					$objPHPExcel->getActiveSheet()
						->getPageMargins()->setRight(0.5);
					$objPHPExcel->getActiveSheet()
						->getPageMargins()->setLeft(0.5);
					$objPHPExcel->getActiveSheet()
						->getPageMargins()->setBottom(1);

					//Header and footer
					$objPHPExcel->getActiveSheet()
						->getHeaderFooter()
						->setOddHeader('&C&HPlease treat this document as confidential!');
					$objPHPExcel->getActiveSheet()
						->getHeaderFooter()
						->setOddFooter('&L&B' . $objPHPExcel->getProperties()->getTitle());

					// Save as an Excel BIFF (xls) file
					$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
					header('Content-Type: application/vnd.ms-excel');
					header('Content-Disposition: attachment;filename="Danh sach GV TLM' . time() . '.xlsx"');
					header('Cache-Control: max-age=0');

					$objWriter->save('php://output');
				}
			}

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('check_post_tlm_view', $form);
			$this->load->view('footer');
		}
	}

?>