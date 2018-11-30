<?php
	/**
	 * Created by PhpStorm.
	 * User: danglx
	 * Email: lexuandang89@gmail.com
	 * Date: 3/23/17
	 * Time: 12:59 AM
	 */
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Teacher extends CI_controller
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->model('teachers_m');
			$this->load->library("upload");
			$this->load->model("import_dsgv_tmp_m");
		}

		/**
		 * Lấy danh sách giảng viên
		 */
		public function index()
		{
			check_access();
			$this->load->helper('url');
			$form['teachers_list']    = $this->teachers_m->get_list();
			$title['title']           = SYSTEM_NAME;
			$active['active_syn'] = 'class="active"';

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('teacher_view', $form);
			$this->load->view('footer');
		}

		/**
		 * Lấy thông tin tài khoản
		 *
		 * @param $id
		 */
		public function ajax_edit($id)
		{
			check_access();
			$data = $this->teachers_m->get_by_id($id);
			echo json_encode($data);
		}

		/**
		 * Thêm mới tài khoản
		 */
		public function ajax_add()
		{
			check_access();
			$this->_validate('add');
			$data     = array(
				'username'         => $this->input->post('UserName'),
				'firstname'        => $this->input->post('FirstName'),
				'lastname'         => $this->input->post('LastName'),
				'email'            => $this->input->post('Email'),
				'email_person'     => $this->input->post('Email_Personal'),
				'mobile_number'    => $this->input->post('Phone'),
				'status_delete_yn' => $this->input->post('Enabled'),
				'role_name'        => $this->input->post('Permission'),
				'created_by'        => $this->session->userdata('user_data')['user_id'],
				'updated_by'        => $this->session->userdata('user_data')['user_id'],
				'created_at'       => date('Y-m-d H:i:s', time()),
				'updated_at'       => date('Y-m-d H:i:s', time())
			);

			$insert = $this->teachers_m->insert($data);
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * Cập nhật thông tin tài khoản
		 */
		public function ajax_update()
		{
			check_access();
			$this->_validate();
			$data = array(
				'username'         => $this->input->post('UserName'),
				'firstname'        => $this->input->post('FirstName'),
				'lastname'         => $this->input->post('LastName'),
				'email'            => $this->input->post('Email'),
				'email_person'     => $this->input->post('Email_Personal'),
				'mobile_number'    => $this->input->post('Phone'),
				'status_delete_yn' => $this->input->post('Enabled'),
				'role_name'        => $this->input->post('Permission'),
				'updated_by'        => $this->session->userdata('user_data')['user_id'],
				'updated_at'       => date('Y-m-d H:i:s', time())
			);
			$this->teachers_m->update($this->input->post('IdUser'), $data);
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * Khoá mở tài khoản người dùng
		 */
		public function ajax_lock($id)
		{
			check_access();
			$data = $this->teachers_m->get_by_id($id);

			if ($data->status_delete_yn == 'n') {
				$delete_yn = 'y';
			} else {
				$delete_yn = 'n';
			}

			$this->teachers_m->update($id, array('status_delete_yn' => $delete_yn));
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * Xoá tài khoản người dùng
		 *
		 * @param $id
		 */
		public function ajax_delete()
		{
			check_access();
			$IdUser = $this->input->post('IdUser');

			$this->teachers_m->delete_by_id($IdUser);
			echo json_encode(array("status" => TRUE));
		}

		public function import() {
			check_access();
			$title['title'] = 'Import danh sách GV';
			$active['active_import'] = 'class="active"';
			$form = array();
			$config['upload_path'] = "upload";
			$config['allowed_types'] = "*";
			//import file
			
			if ($this->input->post('save_file') == 'upload') {
				//Xoa du lieu truoc khi import
				$this->import_dsgv_tmp_m->delete_all();
				$this->upload->initialize($config);

				if ($this->upload->do_upload("upFile") == TRUE) {
            		//tien hanh import_request_variables
					if (isset($_FILES["upFile"])) {
						if ($_FILES["upFile"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
							$this->load->library("excel");
							$DSGV_data = $this->excel->read_file_DSGV($_FILES["upFile"]["tmp_name"]);
							foreach ($DSGV_data as $key => $DSGV) {
								$data = array(
									'username'        	 => trim($DSGV['username']),
									'firstname'        	 => trim($DSGV['firstname']),
									'lastname'    		 => trim($DSGV['lastname']),
									'email'    		 	 => trim($DSGV['email']),
									'email_person'     	 => trim($DSGV['email_person']),
									'mobile_number'  	 => trim($DSGV['mobile_number']),
									'role_name'    		 => trim($DSGV['role_name']),
								);
								$this->import_dsgv_tmp_m->insert($data);

							}
						}
					} else {
						echo '<script>alert("File không đúng định dạng cho phép")</script>';
					}
				} else {
					exit('Upload file gặp lỗi');
				}
			}
			
			if ($this->input->post('kiem_tra') == 'kiem_tra') {
				$list_DSGV_tmp = $this->import_dsgv_tmp_m->get_list();

				$err_DSGV = array();
				foreach ($list_DSGV_tmp as $DSGV) {
					$status = TRUE;
					//Check username
					if ($DSGV->username == '') {
						$status = FALSE;
						array_push($err_DSGV, 'username không được bỏ trống<br>');
					}
					//Check surname
					if ($DSGV->firstname == '') {
						$status = FALSE;
						array_push($err_DSGV, 'Họ và tên đệm không được bỏ trống<br>');
					}
					//Check lastname
					if ($DSGV->lastname == '') {
						$status = FALSE;
						array_push($err_DSGV, 'Tên không được bỏ trống<br>');
					}
					//Check email
					if ($DSGV->email == '' || !filter_var($DSGV->email, FILTER_VALIDATE_EMAIL)) {
						$status = FALSE;
						array_push($err_DSGV, 'Email không hợp lệ<br>');
					}
					//Check email_person
					if ($DSGV->email_person == '' || !filter_var($DSGV->email_person, FILTER_VALIDATE_EMAIL)) {
						$status = FALSE;
						array_push($err_DSGV, 'Email cá nhân không hợp lệ<br>');
					}
					//Check so dien thoai
					if ($DSGV->mobile_number == '' || !preg_match('/^[0-9]+$/', $DSGV->mobile_number)) {
						$status = FALSE;
						array_push($err_DSGV, 'Số điện thoại không hợp lệ<br>');
					}
					//Check quyen giang vien
					if ($DSGV->role_name == '') {
						$status = FALSE;
						array_push($err_DSGV, 'Quyền giảng viên không được bỏ trống<br>');
					} else if ($DSGV->role_name != 'GVHD' && $DSGV->role_name != 'GVCM') {
						$status = FALSE;
						array_push($err_DSGV, 'Quyền giảng viên không hợp lệ<br>');
					}
					if ($status == FALSE) {
						$status_yn = 'N';
					} else {
						$check_DSGV = $this->teachers_m->isset_data_gv($DSGV->username);
						if($check_DSGV == null){
							$status_yn = 'Y';
						} else {
							array_push($err_DSGV, 'Kết quả này đã được ghi nhận trên hệ thống<br>');
							$status_yn = 'N';
						}

					}
					$err_DSGV = implode(' ', $err_DSGV);
					$this->import_dsgv_tmp_m->update($DSGV->id, array('ghi_chu' => $err_DSGV, 'status' => $status_yn));
					$err_DSGV = array();
				}

			}

			if ($this->input->post('import') == 'import') {
				$list_DSGV = $this->import_dsgv_tmp_m->get_list();
				$insert_number = 0;
				foreach ($list_DSGV as $DSGV) {
					if ($DSGV->status == 'Y') {
						$data = array(
							'username'         => $DSGV->username,
							'firstname'        => $DSGV->firstname,
							'lastname'         => $DSGV->lastname,
							'email'            => $DSGV->email,
							'email_person'     => $DSGV->email_person,
							'mobile_number'    => $DSGV->mobile_number,
							'role_name'        => $DSGV->role_name,
							'created_by'       => $this->session->userdata('user_data')['user_id'],
							'created_at'       => date('Y-m-d H:i:s', time())
						);

						$insert = $this->teachers_m->insert($data);
						if ($insert) {
							$insert_number++;
						}
					}
				}

				$form['total_number']  = count($list_DSGV);
				$form['insert_number'] = $insert_number;
				$form['err_number']    = $form['total_number'] - $insert_number;
			}

			if($this->input->post('delete_data') == "delete_data") {
				$this->import_dsgv_tmp_m->delete_all();
			}

			$form['list_DSGV_tmp'] = $this->import_dsgv_tmp_m->get_list();
			//end import

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('import_DSGV_view', $form);
			$this->load->view('footer');
		}

		public function export() {
			check_access();
			$list_DSGV = $this->import_dsgv_tmp_m->get_list();
			$this->load->library("excel");
			$objPHPExcel = new Excel();

			$objPHPExcel->getActiveSheet()->setCellValue("E3", "DANH SÁCH GIẢNG VIÊN")
				->getStyle("E3")->getFont()->setBold(TRUE)->setName("Times New Romans")->setSize(11);
			$objPHPExcel->getActiveSheet()->mergeCells('E3:I3');

			$objPHPExcel->getActiveSheet()->setCellValue("B4", "Ngày xuất: " . date('d/m/Y H:i:s', time()))
				->getStyle("B4")->getFont()->setName("Times New Romans")->setSize(11);
			$objPHPExcel->getActiveSheet()->mergeCells('B4:C4');

			//Tieu de danh sach du lieu
			$headings = array('STT', 'Tên tài khoản', 'Họ và đệm', 'Tên', 'Email', 'Email cá nhân', 'Số điện thoại', 'Loại giảng viên');
			//Set title sheet
			$objPHPExcel->getActiveSheet()->setTitle('Danh sách đơn đăng ký');

			//set header row height and start header
			$objPHPExcel->getActiveSheet()->getRowDimension('6')->setRowHeight(45);

			//set column widths
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
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
			foreach ($list_DSGV as $DSGV) {
				$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $stt)
					->getStyle('A' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $DSGV->username)
					->getStyle('B' . $rowCount)->applyFromArray($styleArrayContentLeft);;
				$objPHPExcel->getActiveSheet()->getStyle('B' . $rowCount)->getAlignment()->setWrapText(TRUE);

				$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $DSGV->firstname)
					->getStyle('C' . $rowCount)->applyFromArray($styleArrayContent);;

				$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $DSGV->lastname)
					->getStyle('D' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $DSGV->email)
					->getStyle('E' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $DSGV->email_person)
					->getStyle('F' . $rowCount)->applyFromArray($styleArrayContentLeft);
				$objPHPExcel->getActiveSheet()->getStyle('F' . $rowCount)->getAlignment()->setWrapText(TRUE);

				$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $DSGV->mobile_number)
					->getStyle('G' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $DSGV->role_name)
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
			header('Content-Disposition: attachment;filename="Danh sach GV_' . time() . '.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter->save('php://output');
		}
		/**
		 * Validate input data
		 *
		 * @param string $action
		 */
		private function _validate($action = '')
		{
			$data                 = array();
			$data['error_string'] = array();
			$data['inputerror']   = array();
			$data['status']       = TRUE;
			$username             = $this->input->post('UserName');
			if (!preg_match("/^[0-9a-zA-Z_.]*$/", $username) || $username == '') {
				$data['inputerror'][]   = 'UserName';
				$data['error_string'][] = 'Tên đăng nhập không hợp lệ';
				$data['status']         = FALSE;
			}
			
			if($action == 'add' && $this->teachers_m->isset_data_gv($username)) {
				$data['inputerror'][]   = 'UserName';
				$data['error_string'][] = 'Tên đăng nhập đã tồn tại';
				$data['status']         = FALSE;
			}

			if ($this->input->post('LastName') == '') {
				$data['inputerror'][]   = 'LastName';
				$data['error_string'][] = 'Bạn phải nhập họ và đệm';
				$data['status']         = FALSE;
			}

			if ($this->input->post('FirstName') == '') {
				$data['inputerror'][]   = 'FirstName';
				$data['error_string'][] = 'Bạn phải nhập tên riêng cho người dùng';
				$data['status']         = FALSE;
			}


			if ($this->input->post('Permission') == '') {
				$data['inputerror'][]   = 'Permission';
				$data['error_string'][] = 'Loại tài khoản không hợp lệ';
				$data['status']         = FALSE;
			}
			if (!filter_var($this->input->post('Email'), FILTER_VALIDATE_EMAIL)) {
				$data['inputerror'][]   = 'Email';
				$data['error_string'][] = 'Email không hợp lệ';
				$data['status']         = FALSE;
			}

			if (!preg_match('/^[0-9]+$/', $this->input->post('Phone'))) {
				$data['inputerror'][]   = 'Phone';
				$data['error_string'][] = 'Số điện thoại không hợp lệ';
				$data['status']         = FALSE;
			}
			if ($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
		}

	}

?>