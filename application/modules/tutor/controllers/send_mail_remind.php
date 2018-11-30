<?php 
    class Send_mail_remind extends CI_controller {
        public function __construct()
		{
			parent::__construct();
			$this->load->model('teachers_m');
			$this->load->model('setting_mail_m');
            $this->load->model("send_mail_tmp_m");
			$this->load->model("templates_m");
			$this->load->model('log_email_sms_m');
			$this->load->model('login/users_m');
			$this->load->library("upload");
			include APPPATH . 'third_party/mail/mail.php';
		}

		/**
		 * Lấy danh sách giảng viên
		 */
		public function index()
		{
			check_access();
			$this->load->helper('url');
			$input          = array();
			$input['where'] = array('loai_mau' => 'email', 'enable_yn' => 'Y');
			$form['list_template'] = $this->templates_m->get_list($input);
			$title['title'] = 'Import danh sách GV';
			$active['active_send_mail'] = 'class="active"';
			$config['upload_path'] = "upload";
			$config['allowed_types'] = "*";
			
			if ($this->input->post('save_file') == 'upload') {
				//Xoa du lieu truoc khi import
				$this->send_mail_tmp_m->delete_all();
				$this->upload->initialize($config);

				if ($this->upload->do_upload("upFile") == TRUE) {
            		//tien hanh import_request_variables
					if (isset($_FILES["upFile"])) {
						if ($_FILES["upFile"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
							$this->load->library("excel");
							$email_template_data = $this->excel->read_file_email_template($_FILES["upFile"]["tmp_name"]);
							foreach ($email_template_data as $key => $email_data) {
								$data = array(
									'username_giang_vien'        	=> trim($email_data['username_giang_vien']),
									'ten_chung_tu'        	 		=> trim($email_data['ten_chung_tu']),
									'thoi_gian_den'    		 		=> trim($email_data['thoi_gian_den']),
									'deadline'        	 			=> trim($email_data['deadline']),
									'username_tro_giang'        	=> trim($email_data['username_tro_giang']),
								);
								$this->send_mail_tmp_m->insert($data);

							}
						}
					} else {
						echo '<script>alert("File không đúng định dạng cho phép")</script>';
					}
				} else {
					exit('Upload file gặp lỗi');
                }
                
                $list_email_template_tmp = $this->send_mail_tmp_m->get_list();

				$err_data = array();
				foreach ($list_email_template_tmp as $email_data) {
					$status = TRUE;
					if ($email_data->username_giang_vien == '') {
						$status = FALSE;
						array_push($err_data, 'Username giảng viên không được bỏ trống<br>');
					} else if($this->teachers_m->isset_data_gv($email_data->username_giang_vien) == null) {
                        $status = FALSE;
						array_push($err_data, 'Tên user giảng viên không tồn tại<br>');
					}
					if ($email_data->username_tro_giang == '') {
						$status = FALSE;
						array_push($err_data, 'Username trợ giảng không được bỏ trống<br>');
					} else if($this->users_m->isset_data_gv($email_data->username_tro_giang) == null) {
                        $status = FALSE;
						array_push($err_data, 'Tên user trợ giảng không tồn tại<br>');
                    }
					if ($email_data->ten_chung_tu == '') {
						$status = FALSE;
						array_push($err_data, 'Tên chứng từ không được bỏ trống<br>');
					}
					if ($email_data->thoi_gian_den == '') {
						$status = FALSE;
						array_push($err_data, 'Thời gian đến ký không được bỏ trống<br>');
					}
					//Check thoi gian den
					if ($email_data->deadline == '') {
						$status = FALSE;
						array_push($err_data, 'Deadline không được bỏ trống<br>');
					} else {
						//Check dinh dang dd/mm/yyyy
						if (strpos($email_data->deadline, "/")) {
							$time = explode("/", $email_data->deadline);
							$nam = $time[2];
							$thang = $time[1];
							$ngay = $time[0];
							$status_time_active = TRUE;
							$nam_err = '';
							$thang_err = '';
							$ngay_err = '';
							if (strlen($nam) != 4) {
								$status_time_active = FALSE;
								$nam_err = 'sai năm ';
							}
							if (strlen($thang) > 2 || $thang > 12) {
								$status_time_active = FALSE;
								$thang_err = 'sai tháng ';
							}
							if (strlen($ngay) > 2 || $ngay > 31) {
								$status_time_active = FALSE;
								$ngay_err = 'sai ngày ';
							}
							if (!$status_time_active) {
								$status = FALSE;
								array_push($err_data, 'Deadline không hợp lệ: ' . $nam_err . $thang_err . $ngay_err . '<br>');
							}
						} else {
							$status = FALSE;
							array_push($err_data, 'Deadline không hợp lệ<br>');
						}
					}
					if ($status == FALSE) {
						$status_yn = 'N';
					} else {
                        $status_yn = 'Y';
					}
					$err_data = implode(' ', $err_data);
					$this->send_mail_tmp_m->update($email_data->id, array('ghi_chu' => $err_data, 'status' => $status_yn));
					$err_data = array();
				}
			}
			
            if($this->input->post('send_mail') == "send_mail") {
				$username = check_access();
				if($this->input->post('id_template') == '') {
					$form['err'] = "Bạn chưa chọn mẫu email";
				} else {
					$template = $this->templates_m->get_by_id($this->input->post('id_template'));	
					$list_unique = $this->send_mail_tmp_m->get_district_username(); //lấy danh sách các giảng viên cần gửi mail
					foreach($list_unique as $unique_teacher) {
						$list_voucher = $this->send_mail_tmp_m->get_info_teacher($unique_teacher->username_giang_vien); //lấy danh sách giảng viên có username được truyền vào mà status = 'Y'
						if($list_voucher != null) {
							$cc_emails = array();
							$teacher = $this->teachers_m->get_info_by_username($unique_teacher->username_giang_vien); //lấy thông tin giảng viên
							$content  = $template->noi_dung2;
							$subject = $template->tieu_de;
							$data = array(
								'teacher_id' 	=> $teacher->id,
								'msg_type' 		=> 'email',
								'receiver' 		=> $teacher->email,
								'created_by' 	=> $username['user_id'],
							);
							if(strpos($content, "<!--ten_gv-->") && strpos($content, "<!--end_ten_gv-->")) {
								$content = preg_replace("/<!--ten_gv-->([^\!]*)<!--end_ten_gv-->/", "<!--ten_gv-->" . $teacher->lastname . ' ' . $teacher->firstname . "<!--end_ten_gv-->", $content);
							}
							
							if($teacher->email_person != '' || $teacher->email_person != null) {
								$cc_emails[$teacher->email_person] = $teacher->lastname . ' ' . $teacher->firstname;
							}

							$style_th = ' style="border: 1px solid black;padding: 8px;color: #DBAC69;background-color: #082346;border-color: #082346;"';
							$style_td = ' style="border: 1px solid black;padding: 8px;"';

							$table_content = '<table style="border: 1px solid black; border-collapse: collapse;">';
							$table_content .= '<tr>';
							$table_content .= '<th ' . $style_th . '>STT</th>';
							$table_content .= '<th ' . $style_th . '>Tên giảng viên</th>';
							$table_content .= '<th ' . $style_th . '>Chứng từ cần ký</th>';
							$table_content .= '<th ' . $style_th . '>Thời gian ký</th>';
							$table_content .= '<th ' . $style_th . '>Deadline</th>';
							$table_content .= '<th ' . $style_th . '>Tên trợ giảng</th>';
							$table_content .= '<th ' . $style_th . '>Số điện thoại trợ giảng</th>';
							$table_content .= '</tr>';
							
							$stt = 1;
							foreach($list_voucher as $voucher_arr) {
								$tro_giang_info = $this->users_m->get_info_by_username($voucher_arr->username_tro_giang);
								if($tro_giang_info->email != '' || $tro_giang_info->email != null)
								$cc_emails[$tro_giang_info->email] = $tro_giang_info->lastname . ' ' . $tro_giang_info->firstname;
								$table_content .= '<tr>';
								$table_content .= '<td ' . $style_td . '>' . $stt . '</td>';
								$table_content .= '<td ' . $style_td . '>' . $teacher->lastname . ' ' . $teacher->firstname . '</td>';
								$table_content .= '<td ' . $style_td . '>' . $voucher_arr->ten_chung_tu . '</td>';
								$table_content .= '<td ' . $style_td . '>' . $voucher_arr->thoi_gian_den . '</td>';
								$table_content .= '<td ' . $style_td . '>' . $voucher_arr->deadline . '</td>';
								$table_content .= '<td ' . $style_td . '>' . $tro_giang_info->lastname . ' ' . $tro_giang_info->firstname . '</td>';
								$table_content .= '<td ' . $style_td . '>' . $tro_giang_info->mobile_number . '</td>';
								$table_content .= '</tr>';
								$stt++;
							}
							$table_content .= '</table>';
							if (strpos($content, "<!--table_ds_course-->") && strpos($content, "<!--end_table_ds_course-->")) {
								$content = preg_replace("/<!--table_ds_course-->([^\!]*)<!--end_table_ds_course-->/", "<!--table_ds_course-->" . $table_content . "<!--end_table_ds_course-->", $content);
							}
							if(strpos($content, "<!--ngay_nhan_chung_tu-->") && strpos($content, "<!--end_ngay_nhan_chung_tu-->")) {
								$content = preg_replace("/<!--ngay_nhan_chung_tu-->([^\!]*)<!--end_ngay_nhan_chung_tu-->/", date('d-m-Y', strtotime('+10 day', time())), $content);
							}
							$content='<html>'.$content.'</html>';

							if(strpos($subject, "<!--ngay_bat_dau_gui-->") && strpos($subject, "<!--end_ngay_bat_dau_gui-->")) {
								$subject = preg_replace("/<!--ngay_bat_dau_gui-->([^\!]*)<!--end_ngay_bat_dau_gui-->/", date('d-m-Y', time()), $subject);
							}
							if(strpos($subject, "<!--ngay_nhan_chung_tu-->") && strpos($subject, "<!--end_ngay_nhan_chung_tu-->")) {
								$subject = preg_replace("/<!--ngay_nhan_chung_tu-->([^\!]*)<!--end_ngay_nhan_chung_tu-->/", date('d-m-Y', strtotime('+10 day', time())), $subject);
							}
							//Email của người tạo lịch gửi hay QLHT
							$mail = $this->setting_mail_m->get_by_id(1);
							$from_email = array('email' => $mail->email, 'name' => $mail->name, 'password' => $mail->password);
							$to_emails = array($teacher->email => $teacher->lastname . ' ' . $teacher->firstname);
							// $to_emails = array('sonnt@topica.edu.vn' => $teacher->lastname . ' ' . $teacher->firstname);
							$result_email = send_mail($content, $subject, $from_email, $to_emails, $cc_emails, 0);
							if ($result_email == TRUE) {
								list($msec, $sec) = explode(" ", microtime());
								$data['tieu_de'] = $subject;
								$data['noi_dung'] = $content;
								$data['trang_thai'] = 'success';
								$data['msg_code'] = 'tmp' . $msec . ' ' . time();
								$form['mail_res'] = 'mail sent';
							} else {
								list($msec, $sec) = explode(" ", microtime());
								$data['tieu_de'] = $subject;
								$data['noi_dung'] = $content;
								$data['trang_thai'] = 'error';
								$data['msg_code'] = 'tmp' . $msec . ' ' . time();
								$data['ghi_chu'] = 'Gặp lỗi khi gửi mail';
								$form['mail_res'] = 'Sent mail ERROR';
							}
							$insert = $this->log_email_sms_m->insert($data);
						}
					}
				}
			}
			
			if($this->input->post('delete_data') == "delete_data") {
				$this->send_mail_tmp_m->delete_all();
			}
            
            $form['list_email_template'] = $this->send_mail_tmp_m->get_list();

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('send_mail_remind_view', $form);
			$this->load->view('footer');
		}
	
    }
?>