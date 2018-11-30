<?php
    class Plan_mail extends CI_controller {
        public function __construct() {
            parent::__construct();
            $this->load->model("log_email_sms_m");
            $this->load->model("plans_m");
            $this->load->model("setting_mail_m");
            $this->load->model("teachers_m");
            $this->load->model("osp100_m");
            $this->load->model("templates_m");
            $this->load->model("login/users_m");
            $this->load->library("upload");
			include APPPATH . 'third_party/mail/mail.php';
        }

        /**
         * Gui mail theo quy
         * Author: chiennn2
         * Date: 6/4/2018
         */
        public function semester() {
            check_access();
			$this->load->helper('url');
			$title['title'] = 'Gửi mail theo quý';
            $active['active_semester'] = 'class="active"';
            //lấy danh sách quý
            $form['list_semester'] = $this->plans_m->get_list();
            $form['list_osp100'] = array();
            $form['show_total_success_err'] = false;
            if($this->input->post('loc_ket_qua') == "loc_ket_qua") {
                if($this->input->post('id_semester') == '') {
                    $form['err'] = "Bạn chưa chọn quý";
                } else {
                    //lấy danh sách giảng viên cần gửi mail theo quý
                    $list_district_gvcm = $this->osp100_m->get_unique_gv_semester("username_gvcm", $this->input->post('id_semester'));
                    foreach($list_district_gvcm as $key) {
                        $form['list_osp100'][] = $this->osp100_m->get_detail_gv($key->username, "username_gvcm", $this->input->post('id_semester'));
                    }
                    $list_district_gvhd = $this->osp100_m->get_unique_gv_semester("username_gvhd", $this->input->post('id_semester'));
                    foreach($list_district_gvhd as $key) {
                        if($key->username != '')
                            $form['list_osp100'][] = $this->osp100_m->get_detail_gv($key->username, "username_gvhd", $this->input->post('id_semester'));
                    }

                    // lấy danh sách kế hoạch
                    $form['plan'] = $this->plans_m->get_by_id($this->input->post('id_semester'));
                    $form['show_total_success_err'] = true;
                }
            }

            if($this->input->post('send') == 'send') {
                $username = check_access();
                $list_gv = array();
                // lấy danh sách giảng viên hướng dẫn cần gửi mail
                $list_unique_gvhd = $this->osp100_m->get_gv_semester("username_gvhd", $this->input->post('id_semester'));
                foreach($list_unique_gvhd as $key => $info_gv) {
                    $list_gv[] = $info_gv;
                }
                // lấy danh sách giảng viên chuyên môn cần gửi mail
                $list_unique_gvcm = $this->osp100_m->get_gv_semester("username_gvcm", $this->input->post('id_semester'));
                foreach($list_unique_gvcm as $key => $info_gv) {
                    $list_gv[] = $info_gv;
                }
                $template = get_template_by_module('M001');
                $subject = $template->tieu_de;
                $plan = $this->plans_m->get_by_id($this->input->post('id_semester'));
                if(strpos($subject, "<!--ten_quy-->") && strpos($subject, "<!--end_ten_quy-->")) {
                    $subject = preg_replace("/<!--ten_quy-->([^\!]*)<!--end_ten_quy-->/", $plan->ten_dot_hoc, $subject);
                }

                $count = 0;
                foreach($list_gv as $gv_info) {
                    $style_th = ' style="border: 1px solid black;padding: 8px;color: #DBAC69;background-color: #082346;border-color: #082346;"';
                    $style_td = ' style="border: 1px solid black;padding: 8px;"';

                    $table_content = '<table style="border: 1px solid black; border-collapse: collapse;">';
                    $table_content .= '<tr>';
                    $table_content .= '<th ' . $style_th . '>STT</th>';
                    $table_content .= '<th ' . $style_th . '>Mã trường</th>';
                    $table_content .= '<th ' . $style_th . '>Tên môn</th>';
                    $table_content .= '<th ' . $style_th . '>Mã môn</th>';
                    $table_content .= '<th ' . $style_th . '>Lớp học</th>';
                    $table_content .= '<th ' . $style_th . '>Ngày bắt đầu</th>';
                    $table_content .= '<th ' . $style_th . '>Ngày kết thúc</th>';
                    $table_content .= '<th ' . $style_th . '>Ngày thi</th>';
                    $table_content .= '<th ' . $style_th . '>Tên GVCM</th>';
                    $table_content .= '<th ' . $style_th . '>Tên GVHD</th>';
                    $table_content .= '<th ' . $style_th . '>Trợ giảng phụ trách</th>';
                    $table_content .= '</tr>';
                    if($count >= $this->input->post('so_luong')) {
                        break;
                    } else {
                        $content = $template->content_mail;
                        if($gv_info->email == '') {
                            $data = array(
                                'tieu_de'       => $subject,
                                'teacher_id' 	=> $gv_info->id,
                                'msg_type' 		=> 'email',
                                'receiver' 		=> '',
                                'created_by' 	=> $username['user_id'],
                                'trang_thai'    => 'error',
                                'ghi_chu'       => 'Email giảng viên không hợp lệ'
                            );
                        } else {
                            $cc_emails = array();
                            $list_gv_osp100 = $this->osp100_m->get_info_gv_osp100_semester($gv_info->username, $this->input->post('id_semester'));
                            $data = array(
                                'tieu_de'       => $subject,
                                'teacher_id' 	=> $gv_info->id,
                                'msg_type' 		=> 'email',
                                'receiver' 		=> $gv_info->email,
                                'created_by' 	=> $username['user_id'],
                            );

                            if($gv_info->email_person != '' || $gv_info->email_person != null) {
                                $cc_emails[$gv_info->email_person] = $gv_info->lastname . ' ' . $gv_info->firstname;
                            }
                            
                            $stt = 1;
                            foreach($list_gv_osp100 as $gv_osp100) {
                                $gvhd_info = $this->teachers_m->get_info_by_username($gv_osp100->username_gvhd);
                                if($gvhd_info != null)
                                    $gvhd = $gvhd_info->lastname . ' ' . $gvhd_info->firstname . '<br>Email: ' . $gvhd_info->email . '<br>SĐT: ' . $gvhd_info->mobile_number;
                                else $gvhd = '';
                                $gvcm_info = $this->teachers_m->get_info_by_username($gv_osp100->username_gvcm);
                                $gvcm = $gvcm_info->lastname . ' ' . $gvcm_info->firstname . '<br>Email: ' . $gvcm_info->email . '<br>SĐT: ' . $gvcm_info->mobile_number;
                                $gvtg_info = $this->users_m->get_info_by_username($gv_osp100->username_tg);
                                $gvtg = $gvtg_info->lastname . ' ' . $gvtg_info->firstname . '<br>Email: ' . $gvtg_info->email . '<br>SĐT: ' . $gvtg_info->mobile_number;
                                $gvtg_fullname = $gvtg_info->lastname . ' ' . $gvtg_info->firstname;
                                if (!in_array($gvtg_fullname, $cc_emails)) {
                                    $cc_emails[$gvtg_info->email] = $gvtg_fullname;
                                }

                                if($gv_info->role_name == 'GVHD') {
                                    $table_content .= '<tr>';
                                    $table_content .= '<td ' . $style_td . '>' . $stt . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gv_osp100->system . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gv_osp100->ten_mon . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gv_osp100->ma_mon . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gv_osp100->ten_course . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . date("d/m/Y", strtotime($gv_osp100->ngay_bat_dau)) . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . date("d/m/Y", strtotime($gv_osp100->ngay_ket_thuc)) . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . date("d/m/Y", strtotime($gv_osp100->ngay_thi_chinh)) . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gvcm . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gvhd_info->lastname . ' ' . $gvhd_info->firstname . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gvtg . '</td>';
                                    $table_content .= '</tr>';
                                    $stt++; 
                                } else {
                                    $table_content .= '<tr>';
                                    $table_content .= '<td ' . $style_td . '>' . $stt . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gv_osp100->system . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gv_osp100->ten_mon . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gv_osp100->ma_mon . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gv_osp100->ten_course . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . date("d/m/Y", strtotime($gv_osp100->ngay_bat_dau)) . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . date("d/m/Y", strtotime($gv_osp100->ngay_ket_thuc)) . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . date("d/m/Y", strtotime($gv_osp100->ngay_thi_chinh)) . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gvcm_info->lastname . ' ' . $gvcm_info->firstname . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gvhd . '</td>';
                                    $table_content .= '<td ' . $style_td . '>' . $gvtg . '</td>';
                                    $table_content .= '</tr>';
                                    $stt++;
                                }
                            }

                            $table_content .= '</table>';
                            if (strpos($content, "<!--table_ds_course-->") && strpos($content, "<!--end_table_ds_course-->")) {
                                $content = preg_replace("/<!--table_ds_course-->([^\!]*)<!--end_table_ds_course-->/", $table_content, $content);
                            }
                            if(strpos($content, "<!--deadline_xac_nhan-->") && strpos($content, "<!--end_deadline_xac_nhan-->")) {
                                $content = preg_replace("/<!--deadline_xac_nhan-->([^\!]*)<!--end_deadline_xac_nhan-->/", date('d/m/Y', strtotime('+5 day', time())), $content);
                            }
                            if(strpos($content, "<!--ten_gv-->") && strpos($content, "<!--end_ten_gv-->")) {
                                $content = preg_replace("/<!--ten_gv-->([^\!]*)<!--end_ten_gv-->/", $gv_info->lastname . ' ' . $gv_info->firstname, $content);
                            }
                            $content='<html>'.$content.'</html>';
                            $data['noi_dung'] = $content;

                            //Email của người tạo lịch gửi hay QLHT
                            $mail = $this->setting_mail_m->get_by_id(1);
                            $from_email = array('email' => $mail->email, 'name' => $mail->name, 'password' => $mail->password);
                            $to_emails = array($gv_info->email => $gv_info->lastname . ' ' . $gv_info->firstname);
                            // $to_emails = array('chienkakashi@gmail.com' => $gv_info->lastname . ' ' . $gv_info->firstname);
                            $result_email = send_mail($content, $subject, $from_email, $to_emails, $cc_emails, 0);
                            list($msec, $sec)   = explode(" ", microtime());
                            $data['msg_code']   = 'TMQ' . $msec . ' ' . time();
                            if ($result_email == TRUE) {
                                $data['trang_thai'] = 'success';
                                $update = $this->osp100_m->update_status_mail($gv_info->role_name, $gv_info->username, 'Y', $this->input->post('id_semester'));
                            } else {
                                $data['trang_thai'] = 'error';
                                $data['ghi_chu']    = 'Gặp lỗi khi gửi mail';
                                $update = $this->osp100_m->update_status_mail($gv_info->role_name, $gv_info->username, 'E', $this->input->post('id_semester'));
                            }
                            $insert = $this->log_email_sms_m->insert($data);
                            $count++;
                        }
                    }
                }
            }
            $form['total'] = count($form['list_osp100']);
            $form['success'] = $this->osp100_m->get_success_or_err($this->input->post('id_semester'), "username_gvcm", 'Y') + $this->osp100_m->get_success_or_err($this->input->post('id_semester'), "username_gvhd", 'Y');
            $form['error'] = $this->osp100_m->get_success_or_err($this->input->post('id_semester'), "username_gvcm", 'E') + $this->osp100_m->get_success_or_err($this->input->post('id_semester'), "username_gvhd", 'E');

            $this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('mail_plan_semester_view', $form);
			$this->load->view('footer');
        }

        /**
         * Gui mail theo dot
         * Author: chiennn2
         * Date: 14/3/2018
         */
        public function startcourse() {
            check_access();
			$this->load->helper('url');
			$title['title'] = 'Gửi mail theo đợt học';
            $active['active_startcourse'] = 'class="active"';
            $form['list_semester'] = $this->plans_m->get_list();
            $form['list_osp100'] = array();

            if($this->input->post('loc_ket_qua') == "loc_ket_qua") {
                if($this->input->post('date_start') == '') {
                    $form['err'] = "Bạn chưa chọn ngày bắt đầu";
                } else {
                    $input          = array();
                    $input['where'] = array('ngay_bat_dau' => $this->input->post('date_start'), 'status' => 'Y');
                    $form['list_osp100'] = $this->osp100_m->get_list($input);
                    $form['date'] = $this->input->post('date_start');
                }
            }

            if($this->input->post('send') == "send") {
                $username = check_access();
                $list_unique_gvhd = $this->osp100_m->get_unique_gv_startcourse("username_gvhd", $this->input->post('date_start')); // lấy danh sách giảng viên hướng dẫn cần gửi mail
                $list_unique_gvcm = $this->osp100_m->get_unique_gv_startcourse("username_gvcm", $this->input->post('date_start')); // lấy danh sách giảng viên chuyên môn cần gửi mail
                $template = get_template_by_module('M002');
                $subject = $template->tieu_de;
                if(strpos($subject, "<!--ngay_bat_dau_course-->") && strpos($subject, "<!--end_ngay_bat_dau_course-->")) {
                    $subject = preg_replace("/<!--ngay_bat_dau_course-->([^\!]*)<!--end_ngay_bat_dau_course-->/", date("d/m/Y", strtotime($this->input->post('date_start'))), $subject);
                }
                $content = $template->content_mail;

                $style_th = ' style="border: 1px solid black;padding: 8px;color: #DBAC69;background-color: #082346;border-color: #082346;"';
                $style_td = ' style="border: 1px solid black;padding: 8px;"';

                $table_content = '<table style="border: 1px solid black; border-collapse: collapse;">';
                $table_content .= '<tr>';
                $table_content .= '<th ' . $style_th . '>STT</th>';
                $table_content .= '<th ' . $style_th . '>Mã trường</th>';
                $table_content .= '<th ' . $style_th . '>Tên môn</th>';
                $table_content .= '<th ' . $style_th . '>Mã môn</th>';
                $table_content .= '<th ' . $style_th . '>Lớp học</th>';
                $table_content .= '<th ' . $style_th . '>Ngày bắt đầu</th>';
                $table_content .= '<th ' . $style_th . '>Ngày kết thúc</th>';
                $table_content .= '<th ' . $style_th . '>Ngày thi</th>';
                $table_content .= '<th ' . $style_th . '>Tên GVCM</th>';
                $table_content .= '<th ' . $style_th . '>Tên GVHD</th>';
                $table_content .= '<th ' . $style_th . '>Trợ giảng phụ trách</th>';
                $table_content .= '</tr>';

                // gửi mail cho gvhd
                foreach($list_unique_gvhd as $gvhd_info) {
                    $content_gvhd = $content;
                    if($gvhd_info->email != '') {
                        $cc_emails = array();
                        $list_gvhd_osp100 = $this->osp100_m->get_list_gv_osp100_startcourse("username_gvhd", $gvhd_info->username, $this->input->post('date_start'));
                        $data = array(
                            'tieu_de'       => $subject,
                            'teacher_id' 	=> $gvhd_info->id,
                            'msg_type' 		=> 'email',
                            'receiver' 		=> $gvhd_info->email,
                            'created_by' 	=> $username['user_id'],
                        );

                        if($gvhd_info->email_person != '' || $gvhd_info->email_person != null) {
                            $cc_emails[$gvhd_info->email_person] = $gvhd_info->lastname . ' ' . $gvhd_info->firstname;
                        }

                        $table_content_gvhd = $table_content;
                        $stt = 1;
                        foreach($list_gvhd_osp100 as $gvhd_osp100) {
                            $gvcm_info = $this->teachers_m->get_info_by_username($gvhd_osp100->username_gvcm);
                            $gvcm = $gvcm_info->lastname . ' ' . $gvcm_info->firstname . '<br>Email: ' . $gvcm_info->email . '<br>SĐT: ' . $gvcm_info->mobile_number;
                            $gvtg_info = $this->users_m->get_info_by_username($gvhd_osp100->username_tg);
                            $gvtg = $gvtg_info->lastname . ' ' . $gvtg_info->firstname . '<br>Email: ' . $gvtg_info->email . '<br>SĐT: ' . $gvtg_info->mobile_number;
                            $gvtg_fullname = $gvtg_info->lastname . ' ' . $gvtg_info->firstname;
                            if (!in_array($gvtg_fullname, $cc_emails)) {
                                $cc_emails[$gvtg_info->email] = $gvtg_fullname;
                            }

                            $table_content_gvhd .= '<tr>';
                            $table_content_gvhd .= '<td ' . $style_td . '>' . $stt . '</td>';
                            $table_content_gvhd .= '<td ' . $style_td . '>' . $gvhd_osp100->system . '</td>';
                            $table_content_gvhd .= '<td ' . $style_td . '>' . $gvhd_osp100->ten_mon . '</td>';
                            $table_content_gvhd .= '<td ' . $style_td . '>' . $gvhd_osp100->ma_mon . '</td>';
                            $table_content_gvhd .= '<td ' . $style_td . '>' . $gvhd_osp100->ten_course . '</td>';
                            $table_content_gvhd .= '<td ' . $style_td . '>' . date("d/m/Y", strtotime($gvhd_osp100->ngay_bat_dau)) . '</td>';
                            $table_content_gvhd .= '<td ' . $style_td . '>' . date("d/m/Y", strtotime($gvhd_osp100->ngay_ket_thuc)) . '</td>';
                            $table_content_gvhd .= '<td ' . $style_td . '>' . date("d/m/Y", strtotime($gvhd_osp100->ngay_thi_chinh)) . '</td>';
                            $table_content_gvhd .= '<td ' . $style_td . '>' . $gvcm . '</td>';
                            $table_content_gvhd .= '<td ' . $style_td . '>' . $gvhd_info->lastname . ' ' . $gvhd_info->firstname . '</td>';
                            $table_content_gvhd .= '<td ' . $style_td . '>' . $gvtg . '</td>';
                            $table_content_gvhd .= '</tr>';
                            $stt++;
                        }

                        $table_content_gvhd .= '</table>';
                        if (strpos($content_gvhd, "<!--table_ds_course-->") && strpos($content_gvhd, "<!--end_table_ds_course-->")) {
                            $content_gvhd = preg_replace("/<!--table_ds_course-->([^\!]*)<!--end_table_ds_course-->/", $table_content_gvhd, $content_gvhd);
                        }
                        if(strpos($content_gvhd, "<!--deadline_xac_nhan-->") && strpos($content_gvhd, "<!--end_deadline_xac_nhan-->")) {
                            $content_gvhd = preg_replace("/<!--deadline_xac_nhan-->([^\!]*)<!--end_deadline_xac_nhan-->/", date('d/m/Y', strtotime('+5 day', time())), $content_gvhd);
                        }
                        if(strpos($content_gvhd, "<!--ten_gv-->") && strpos($content_gvhd, "<!--end_ten_gv-->")) {
                            $content_gvhd = preg_replace("/<!--ten_gv-->([^\!]*)<!--end_ten_gv-->/", $gvhd_info->lastname . ' ' . $gvhd_info->firstname, $content_gvhd);
                        }
                        $content_gvhd='<html>'.$content_gvhd.'</html>';
                        $data['noi_dung'] = $content_gvhd;

                        //Email của người tạo lịch gửi hay QLHT
                        $mail = $this->setting_mail_m->get_by_id(1);
                        $from_email = array('email' => $mail->email, 'name' => $mail->name, 'password' => $mail->password);
                        $to_emails = array($gvhd_info->email => $gvhd_info->lastname . ' ' . $gvhd_info->firstname);
                        // $to_emails = array('sonnt@topica.edu.vn' => $gvhd_info->lastname . ' ' . $gvhd_info->firstname);
                        $result_email = send_mail($content_gvhd, $subject, $from_email, $to_emails, $cc_emails, 0);
                        list($msec, $sec)   = explode(" ", microtime());
                        $data['msg_code']   = 'TMQ' . $msec . ' ' . time();
                        if ($result_email == TRUE) {
                            $data['trang_thai'] = 'success';
                            $form['mail_res']   = 'mail sent';
                        } else {
                            $data['trang_thai'] = 'error';
                            $data['ghi_chu']    = 'Gặp lỗi khi gửi mail';
                            $form['mail_res']   = 'Sent mail ERROR';
                        }
                        $insert = $this->log_email_sms_m->insert($data);
                    }
                }

                // gửi mail cho gvcm
                foreach($list_unique_gvcm as $gvcm_info) {
                    $content_gvcm = $content;
                    if($gvcm_info->email != '') {
                        $cc_emails = array();
                        $list_gvcm_osp100 = $this->osp100_m->get_list_gv_osp100_startcourse("username_gvcm", $gvcm_info->username, $this->input->post('date_start'));
                        $data = array(
                            'tieu_de'       => $subject,
                            'teacher_id' 	=> $gvcm_info->id,
                            'msg_type' 		=> 'email',
                            'receiver' 		=> $gvcm_info->email,
                            'created_by' 	=> $username['user_id'],
                        );
                        
                        $table_content_gvcm = $table_content;
                        $stt = 1;
                        foreach($list_gvcm_osp100 as $gvcm_osp100) {
                            $gvhd_info = $this->teachers_m->get_info_by_username($gvcm_osp100->username_gvhd);
                            if($gvhd_info != null)
                                $gvhd = $gvhd_info->lastname . ' ' . $gvhd_info->firstname . '<br>Email: ' . $gvhd_info->email . '<br>SĐT: ' . $gvhd_info->mobile_number;
                            else $gvhd = '';
                            $gvtg_info = $this->users_m->get_info_by_username($gvcm_osp100->username_tg);
                            $gvtg = $gvtg_info->lastname . ' ' . $gvtg_info->firstname . '<br>Email: ' . $gvtg_info->email . '<br>SĐT: ' . $gvtg_info->mobile_number;
                            $gvtg_fullname = $gvtg_info->lastname . ' ' . $gvtg_info->firstname;
                            if (!in_array($gvtg_fullname, $cc_emails)) {
                                $cc_emails[$gvtg_info->email] = $gvtg_fullname;
                            }

                            $table_content_gvcm .= '<tr>';
                            $table_content_gvcm .= '<td ' . $style_td . '>' . $stt . '</td>';
                            $table_content_gvcm .= '<td ' . $style_td . '>' . $gvcm_osp100->system . '</td>';
                            $table_content_gvcm .= '<td ' . $style_td . '>' . $gvcm_osp100->ten_mon . '</td>';
                            $table_content_gvcm .= '<td ' . $style_td . '>' . $gvcm_osp100->ma_mon . '</td>';
                            $table_content_gvcm .= '<td ' . $style_td . '>' . $gvcm_osp100->ten_course . '</td>';
                            $table_content_gvcm .= '<td ' . $style_td . '>' . date("d/m/Y", strtotime($gvcm_osp100->ngay_bat_dau)) . '</td>';
                            $table_content_gvcm .= '<td ' . $style_td . '>' . date("d/m/Y", strtotime($gvcm_osp100->ngay_ket_thuc)) . '</td>';
                            $table_content_gvcm .= '<td ' . $style_td . '>' . date("d/m/Y", strtotime($gvcm_osp100->ngay_thi_chinh)) . '</td>';
                            $table_content_gvcm .= '<td ' . $style_td . '>' . $gvcm_info->lastname . ' ' . $gvcm_info->firstname . '</td>';
                            $table_content_gvcm .= '<td ' . $style_td . '>' . $gvhd . '</td>';
                            $table_content_gvcm .= '<td ' . $style_td . '>' . $gvtg . '</td>';
                            $table_content_gvcm .= '</tr>';
                            $stt++;
                        }

                        $table_content_gvcm .= '</table>';
                        if (strpos($content_gvcm, "<!--table_ds_course-->") && strpos($content_gvcm, "<!--end_table_ds_course-->")) {
                            $content_gvcm = preg_replace("/<!--table_ds_course-->([^\!]*)<!--end_table_ds_course-->/", $table_content_gvcm, $content_gvcm);
                        }
                        if(strpos($content_gvcm, "<!--deadline_xac_nhan-->") && strpos($content_gvcm, "<!--end_deadline_xac_nhan-->")) {
                            $content_gvcm = preg_replace("/<!--deadline_xac_nhan-->([^\!]*)<!--end_deadline_xac_nhan-->/", date('d/m/Y', strtotime('+5 day', time())), $content_gvcm);
                        }
                        if(strpos($content_gvcm, "<!--ten_gv-->") && strpos($content_gvcm, "<!--end_ten_gv-->")) {
                            $content_gvcm = preg_replace("/<!--ten_gv-->([^\!]*)<!--end_ten_gv-->/", $gvcm_info->lastname . ' ' . $gvcm_info->firstname, $content_gvcm);
                        }
                        $content_gvcm='<html>'.$content_gvcm.'</html>';
                        $data['noi_dung'] = $content_gvcm;

                        //Email của người tạo lịch gửi hay QLHT
                        $mail = $this->setting_mail_m->get_by_id(1);
                        $from_email = array('email' => $mail->email, 'name' => $mail->name, 'password' => $mail->password);
                        $to_emails = array($gvcm_info->email => $gvcm_info->lastname . ' ' . $gvcm_info->firstname);
                        // $to_emails = array('sonnt@topica.edu.vn' => $gvcm_info->lastname . ' ' . $gvcm_info->firstname);
                        $result_email = send_mail($content_gvcm, $subject, $from_email, $to_emails, $cc_emails, 0);
                        list($msec, $sec)   = explode(" ", microtime());
                        $data['msg_code']   = 'TMQ' . $msec . ' ' . time();
                        if ($result_email == TRUE) {
                            $data['trang_thai'] = 'success';
                            $form['mail_res']   = 'mail sent';
                        } else {
                            $data['trang_thai'] = 'error';
                            $data['ghi_chu']    = 'Gặp lỗi khi gửi mail';
                            $form['mail_res']   = 'Sent mail ERROR';
                        }
                        $insert = $this->log_email_sms_m->insert($data);
                    }
                }
            }

            $this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('mail_plan_startcourse_view', $form);
			$this->load->view('footer');
        }
    }
?>