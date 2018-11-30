<?php
	/**
	 * Created by PhpStorm.
	 * User: danglx
	 * Email: lexuandang89@gmail.com
	 * Date: 3/23/17
	 * Time: 12:59 AM
	 */
	defined('BASEPATH') OR exit('No direct script access allowed');

	class osp extends CI_controller
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->model('osp100_m');
			$this->load->model('login/users_m');
			$this->load->model('dictionaries_m');
			$this->load->model('teachers_m');
			$this->load->model('plans_m');
			$this->load->model('osp100_tmp_m');
			$this->load->library("upload");
		}

		/**
		 * Lấy danh sách
		 */
		public function index()
		{
			check_access();
			$this->load->helper('url');
			$form['list_osp100']  = $this->osp100_m->get_list();
			$form['list_plan']  = $this->plans_m->get_list();
			$input          = array();
			$input['where'] = array('categories_id' => 4, 'enable_yn' => 'Y');
			$form['list_system'] = $this->dictionaries_m->get_list($input);
			$title['title']       = 'Danh mục OSP100';
			$active['active_osp'] = 'class="active"';

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('osp_view', $form);
			$this->load->view('footer');
		}

		/**
		 * Thêm mới
		 */
		public function ajax_add()
		{
			$user_data = check_access();
			$this->_validate();

			$data = array(
				'plan_id'     	 => $this->input->post('dot_hoc'),
				'system'     	 => $this->input->post('system'),
				'ma_mon'     	 => $this->input->post('ma_mon_hoc'),
				'ten_mon'     	 => $this->input->post('ten_mon_hoc'),
				'ten_lop'        => $this->input->post('ten_lop'),
				'ten_course'     => $this->input->post('ten_course'),
				'ngay_bat_dau'   => $this->input->post('ngay_bat_dau'),
				'ngay_ket_thuc'  => $this->input->post('ngay_ket_thuc'),
				'ngay_thi'       => $this->input->post('ngay_thi'),
				'ngay_thi_chinh' => $this->input->post('ngay_thi_chinh'),
				'so_luong_sv'    => $this->input->post('so_luong_sv'),
				'id_forum'       => $this->input->post('id_forum'),
				'username_povh'  => $this->input->post('username_povh'),
				'username_tg'    => $this->input->post('username_tg'),
				'username_gvcm'  => $this->input->post('username_gvcm'),
				'username_gvhd'  => $this->input->post('username_gvhd'),
				'ghi_chu'        => $this->input->post('ghi_chu'),
				'created_by'     => $user_data['user_id'],
				'created_at'     => date('Y-m-d H:i:s', time()),
				'updated_by'     => $user_data['user_id'],
				'updated_at'     => date('Y-m-d H:i:s', time())
			);

			$insert = $this->osp100_m->insert($data);
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * Chỉnh sửa
		 *
		 * @param $id
		 */
		public function ajax_edit($id)
		{
			check_access();
			$data = $this->osp100_m->get_by_id($id);
			echo json_encode($data);
		}

		/**
		 * Cap nhat thong tin
		 */
		public function ajax_update()
		{
			$user_data = check_access();

			$this->_validate();
			date_default_timezone_set('Asia/Ho_Chi_Minh');
			$data = array(
				'plan_id'     => $this->input->post('dot_hoc'),
				'system'     	 => $this->input->post('system'),
				'ma_mon'     => $this->input->post('ma_mon_hoc'),
				'ten_mon'     => $this->input->post('ten_mon_hoc'),
				'ten_lop'        => $this->input->post('ten_lop'),
				'ten_course'     => $this->input->post('ten_course'),
				'ngay_bat_dau'   => $this->input->post('ngay_bat_dau'),
				'ngay_ket_thuc'  => $this->input->post('ngay_ket_thuc'),
				'ngay_thi'       => $this->input->post('ngay_thi'),
				'ngay_thi_chinh' => $this->input->post('ngay_thi_chinh'),
				'so_luong_sv'    => $this->input->post('so_luong_sv'),
				'id_forum'       => $this->input->post('id_forum'),
				'username_povh'  => $this->input->post('username_povh'),
				'username_tg'    => $this->input->post('username_tg'),
				'username_gvcm'  => $this->input->post('username_gvcm'),
				'username_gvhd'  => $this->input->post('username_gvhd'),
				'ghi_chu'        => $this->input->post('ghi_chu'),
				'updated_by'     => $user_data['user_id'],
				'updated_at'     => date('Y-m-d H:i:s', time())
			);

			$this->osp100_m->update($this->input->post('id_osp'), $data);
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * Xoa du lieu OSP
		 */
		public function ajax_delete()
		{
			check_access();
			$Id = $this->input->post('id_osp');

			$this->osp100_m->delete_by_id($Id);
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * import OSP
		 */
		public function import()
		{
			$user_data = check_access();

			$title['title']              = 'Import danh sách OSP';
			$active['active_osp_import'] = 'class="active"';
			$form                        = array();

			$config['upload_path']       = "upload";
			$config['allowed_types']     = "*";
			//import file
			if ($this->input->post('save_file') == 'upload') {
				$this->upload->initialize($config);

				if ($this->upload->do_upload("upFile") == TRUE) {
//tien hanh import_request_variables
					if (isset($_FILES["upFile"])) {
						if ($_FILES["upFile"]["type"] == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
							$this->load->library("excel");
							$osp_data = $this->excel->read_file_osp($_FILES["upFile"]["tmp_name"]);
							//Xoa du lieu truoc khi import
							$this->osp100_tmp_m->delete_all();
							$stt = 0;
							foreach ($osp_data as $key => $osp) {
								$stt++;
								$data = array(
									'system'         => trim($osp['system']),
									'ten_mon'        => trim($osp['ten_mon']),
									'ma_mon'         => trim($osp['ma_mon']),
									'ten_course'     => trim($osp['ten_course']),
									'ngay_bat_dau'   => $osp['ngay_bat_dau'],
									'ngay_ket_thuc'  => $osp['ngay_ket_thuc'],
									'ngay_thi_chinh' => $osp['ngay_thi_chinh'],
									'so_luong_sv'    => $osp['so_luong_sv'],
									'username_tg'    => trim($osp['username_tg']),
									'ten_gvcm'    	 => trim($osp['ten_gvcm']),
									'username_gvcm'  => trim($osp['username_gvcm']),
									'ten_gvhd'    	 => trim($osp['ten_gvhd']),
									'username_gvhd'  => trim($osp['username_gvhd']),
								);
								$this->osp100_tmp_m->insert($data);

							}
						}
					} else {
						echo '<script>alert("File không đúng định dạng cho phép")</script>';
//					redirect(site_url());
					}
				} else {
					exit('Upload file gặp lỗi');
				}
			}
			//Lay danh sach OSP import
			if ($this->input->post('kiem_tra') == 'kiem_tra') {
				$list_osp100_tmp = $this->osp100_tmp_m->get_list();

				$err_mon_hoc = array();
				foreach ($list_osp100_tmp as $osp) {
					$status = TRUE;
					//check username gvcm
					if ($osp->username_gvcm == '') {
						$status = FALSE;
						array_push($err_mon_hoc, 'Acc GVCM không được bỏ trống');
					} else if($this->teachers_m->isset_data_gv($osp->username_gvcm) == null) {
                        $status = FALSE;
						array_push($err_mon_hoc, 'Acc GVCM không tồn tại');
					}
					
					if($osp->username_gvhd != '' && $this->teachers_m->isset_data_gv($osp->username_gvhd) == null) {
						$status = FALSE;
						array_push($err_mon_hoc, 'Acc GVHD không tồn tại');
					}
					//check username tg
					if ($osp->username_tg == '') {
						$status = FALSE;
						array_push($err_mon_hoc, 'Acc trợ giảng không được bỏ trống');
					} else if($this->users_m->isset_data_gv($osp->username_tg) == null) {
                        $status = FALSE;
						array_push($err_mon_hoc, 'Acc trợ giảng không tồn tại');
					}
					//check mon hoc
					if ($osp->ma_mon == '') {
						$status = FALSE;
						array_push($err_mon_hoc, 'Mã môn học không được bỏ trống');
					}
					//check ma tuong
					if ($osp->system == '') {
						$status = FALSE;
						array_push($err_mon_hoc, 'Mã trường học không được bỏ trống');
					}
					//Check ten course null
					if ($osp->ten_course == '') {
						$status = FALSE;
						array_push($err_mon_hoc, 'Tên course không được bỏ trống');
					}

					//check co ton tai tren osp100
					if($this->osp100_m->get_ten_lop($osp->ten_course) != null) {
						$status = FALSE;
						array_push($err_mon_hoc, 'Tên course đã tồn tại trên hệ thống');
					}
					
					//Check ngay bat dau
					if ($osp->ngay_bat_dau == '') {
						$status = FALSE;
						array_push($err_mon_hoc, 'Ngày bắt đầu course không được bỏ trống');
					} else {
						//Check dinh dang yyyy-mm-dd
						if (strpos($osp->ngay_bat_dau, "-")) {
							$ngay_bat_dau        = explode("-", $osp->ngay_bat_dau);
							$nam                 = $ngay_bat_dau[0];
							$thang               = $ngay_bat_dau[1];
							$ngay                = $ngay_bat_dau[2];
							$status_ngay_bat_dau = TRUE;
							$nam_err             = '';
							$thang_err           = '';
							$ngay_err            = '';
							if (strlen($nam) != 4) {
								$status_ngay_bat_dau = FALSE;
								$nam_err             = 'sai năm ';
							}
							if (strlen($thang) > 2 || $thang > 12) {
								$status_ngay_bat_dau = FALSE;
								$thang_err           = 'sai tháng ';
							}
							if (strlen($ngay) > 2 || $ngay > 31) {
								$status_ngay_bat_dau = FALSE;
								$ngay_err            = 'sai ngày ';
							}
							if (!$status_ngay_bat_dau) {
								$status = FALSE;
								array_push($err_mon_hoc, 'Ngày bắt đầu course không hợp lệ ' . $nam_err . $thang_err . $ngay_err);
							}

						} else {
							$status = FALSE;
							array_push($err_mon_hoc, 'Ngày bắt đầu course không hợp lệ');
						}
					}
					//Check ngay ket thuc
					if ($osp->ngay_ket_thuc == '') {
						$status = FALSE;
						array_push($err_mon_hoc, 'Ngày bắt đầu course không được bỏ trống');
					} else {
						//Check dinh dang yyyy-mm-dd
						if (strpos($osp->ngay_ket_thuc, "-")) {
							$ngay_ket_thuc        = explode("-", $osp->ngay_ket_thuc);
							$nam                  = $ngay_ket_thuc[0];
							$thang                = $ngay_ket_thuc[1];
							$ngay                 = $ngay_ket_thuc[2];
							$status_ngay_ket_thuc = TRUE;
							$nam_err              = '';
							$thang_err            = '';
							$ngay_err             = '';
							if (strlen($nam) != 4) {
								$status_ngay_ket_thuc = FALSE;
								$nam_err              = 'sai năm ';
							}
							if (strlen($thang) > 2 || $thang > 12) {
								$status_ngay_ket_thuc = FALSE;
								$thang_err            = 'sai tháng ';
							}
							if (strlen($ngay) > 2 || $ngay > 31) {
								$status_ngay_ket_thuc = FALSE;
								$ngay_err             = 'sai ngày ';
							}
							if (!$status_ngay_ket_thuc) {
								$status = FALSE;
								array_push($err_mon_hoc, 'Ngày kết thúc course không hợp lệ ' . $nam_err . $thang_err . $ngay_err);
							}

						} else {
							$status = FALSE;
							array_push($err_mon_hoc, 'Ngày kết thúc course không hợp lệ');
						}
					}
					//Check so luong SV du kien
					if ($osp->so_luong_sv == '') {
						$status = FALSE;
						array_push($err_mon_hoc, 'Số lượng SV dự kiến không được bỏ trống');
					}

					if ($status == FALSE) {
						$status_yn = 'N';
					} else {
						$status_yn = 'Y';
					}
					$err_mon_hoc = implode('<br>', $err_mon_hoc);
					$this->osp100_tmp_m->update($osp->id, array('ghi_chu' => $err_mon_hoc, 'status' => $status_yn));
					$err_mon_hoc = array();
				}

			}
			if ($this->input->post('import')) {
				if($this->input->post('plan_osp') == '') {
					$form['err'] = "Bạn chưa chọn đợt học";
				} else {
					$list_osp100   = $this->osp100_tmp_m->get_list();
					$insert_number = 0;
					foreach ($list_osp100 as $osp) {
						if ($osp->status == 'Y') {
							$data = array(
								'plan_id'        => $this->input->post('plan_osp'),
								'system'         => $osp->system,
								'ten_mon'        => $osp->ten_mon,
								'ma_mon'         => $osp->ma_mon,
								'ten_course'     => $osp->ten_course,
								'ngay_bat_dau'   => $osp->ngay_bat_dau,
								'ngay_ket_thuc'  => $osp->ngay_ket_thuc,
								'ngay_thi_chinh' => $osp->ngay_thi_chinh,
								'so_luong_sv'    => $osp->so_luong_sv,
								'username_tg'    => $osp->username_tg,
								'username_gvcm'  => $osp->username_gvcm,
								'username_gvhd'  => $osp->username_gvhd,
								'ghi_chu'        => '',
								'created_by'     => $user_data['user_id'],
								'created_at'     => date('Y-m-d H:i:s', time()),
								'updated_by'     => $user_data['user_id'],
								'updated_at'     => date('Y-m-d H:i:s', time())
							);

							$insert = $this->osp100_m->insert($data);
							if ($insert) {
								$insert_number++;
							}
						}
					}

					$form['total_number']  = count($list_osp100);
					$form['insert_number'] = $insert_number;
					$form['err_number']    = $form['total_number'] - $insert_number;
				}
			}

			if($this->input->post('delete_data') == "delete_data") {
				$this->osp100_tmp_m->delete_all();
			}

			$form['list_osp100_tmp'] = $this->osp100_tmp_m->get_list();
			$form['list_plan']  = $this->plans_m->get_list();

			//end import

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('osp_import_view', $form);
			$this->load->view('footer');
		}

		public function export_osp_tmp()
		{
			check_access();
			$list_osp100_tmp = $this->osp100_tmp_m->get_list();
//		var_dump($list_osp100_tmp);
//		exit;
			$this->load->library("excel");
			$objPHPExcel = new Excel();

			$objPHPExcel->getActiveSheet()->setCellValue("E3", "DANH SÁCH OSP100")
				->getStyle("E3")->getFont()->setBold(TRUE)->setName("Times New Romans")->setSize(11);
			$objPHPExcel->getActiveSheet()->mergeCells('E3:I3');

			$objPHPExcel->getActiveSheet()->setCellValue("B4", "Ngày xuất: " . date('d/m/Y H:i:s', time()))
				->getStyle("B4")->getFont()->setName("Times New Romans")->setSize(11);
			$objPHPExcel->getActiveSheet()->mergeCells('B4:C4');

			//Tieu de danh sach du lieu
			$headings = array('STT', 'Mã trường', 'Tên môn', 'Mã môn', 'Số TC', 'Tên lớp', 'Ngày bắt đầu online', 'Ngày kết thúc online', 'Ngày thi', 'SLSV dự kiến', 'Trợ giảng phụ trách', 'GVCM', 'ACC GVCM', 'GVHD', 'ACC GVHD', 'Trạng thái', 'Ghi chú');
			//Set title sheet
			$objPHPExcel->getActiveSheet()->setTitle('Danh sách đơn đăng ký');

			//set header row height and start header
			$objPHPExcel->getActiveSheet()->getRowDimension('6')->setRowHeight(45);

			//set column widths
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(50);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(50);
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(50);

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
			$objPHPExcel->getActiveSheet()->getStyle("A6:Q6")->applyFromArray($styleArrayTitle);

//Set background header

			$objPHPExcel->getActiveSheet()->getStyle('A6:Q6')->getFill()->applyFromArray(
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
			foreach ($list_osp100_tmp as $osp_tmp) {
				$objPHPExcel->getActiveSheet()->SetCellValue('A' . $rowCount, $stt)
					->getStyle('A' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('B' . $rowCount, $osp_tmp->system)
					->getStyle('B' . $rowCount)->applyFromArray($styleArrayContentLeft);;
				$objPHPExcel->getActiveSheet()->getStyle('B' . $rowCount)->getAlignment()->setWrapText(TRUE);

				$objPHPExcel->getActiveSheet()->SetCellValue('C' . $rowCount, $osp_tmp->ten_mon)
					->getStyle('C' . $rowCount)->applyFromArray($styleArrayContentLeft);;
				$objPHPExcel->getActiveSheet()->getStyle('C' . $rowCount)->getAlignment()->setWrapText(TRUE);

				$objPHPExcel->getActiveSheet()->SetCellValue('D' . $rowCount, $osp_tmp->ma_mon)
					->getStyle('D' . $rowCount)->applyFromArray($styleArrayContent);;

				$objPHPExcel->getActiveSheet()->SetCellValue('E' . $rowCount, $osp_tmp->so_tin_chi)
					->getStyle('E' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('F' . $rowCount, $osp_tmp->ten_lop)
					->getStyle('F' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('G' . $rowCount, $osp_tmp->ngay_bat_dau)
					->getStyle('G' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('H' . $rowCount, $osp_tmp->ngay_ket_thuc)
					->getStyle('H' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('I' . $rowCount, $osp_tmp->ngay_thi_chinh)
					->getStyle('I' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('J' . $rowCount, $osp_tmp->so_luong_sv)
					->getStyle('J' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('K' . $rowCount, $osp_tmp->username_tg)
					->getStyle('K' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('L' . $rowCount, $osp_tmp->ten_gvcm)
					->getStyle('L' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('M' . $rowCount, $osp_tmp->username_gvcm)
					->getStyle('M' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('N' . $rowCount, $osp_tmp->ten_gvhd)
					->getStyle('N' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('O' . $rowCount, $osp_tmp->username_gvhd)
					->getStyle('O' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('P' . $rowCount, $osp_tmp->status)
					->getStyle('P' . $rowCount)->applyFromArray($styleArrayContent);

				$objPHPExcel->getActiveSheet()->SetCellValue('Q' . $rowCount, $osp_tmp->ghi_chu)
					->getStyle('Q' . $rowCount)->applyFromArray($styleArrayContentLeft);
				$objPHPExcel->getActiveSheet()->getStyle('Q' . $rowCount)->getAlignment()->setWrapText(TRUE);
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
			header('Content-Disposition: attachment;filename="Danh sach OSP100_' . time() . '.xlsx"');
			header('Cache-Control: max-age=0');

			$objWriter->save('php://output');

		}

		/**
		 * Validate input data
		 *
		 * @param string $action
		 */
		private function _validate()
		{
			$data                 = array();
			$data['error_string'] = array();
			$data['inputerror']   = array();
			$data['status']       = TRUE;
			if ($this->input->post('dot_hoc') == '') {
				$data['inputerror'][]   = 'dot_hoc_hide';
				$data['error_string'][] = 'Bạn chưa chọn đợt học';
				$data['status']         = FALSE;
			}
			if ($this->input->post('system') == '') {
				$data['inputerror'][]   = 'system_hide';
				$data['error_string'][] = 'Bạn chưa chọn trường';
				$data['status']         = FALSE;
			}
			if ($this->input->post('ma_mon_hoc') == '') {
				$data['inputerror'][]   = 'ma_mon_hoc';
				$data['error_string'][] = 'Bạn chưa nhập mã môn học';
				$data['status']         = FALSE;
			}
			if ($this->input->post('ten_mon_hoc') == '') {
				$data['inputerror'][]   = 'ten_mon_hoc';
				$data['error_string'][] = 'Bạn chưa nhập tên môn học';
				$data['status']         = FALSE;
			}

			if ($this->input->post('ten_lop') == '') {
				$data['inputerror'][]   = 'ten_lop';
				$data['error_string'][] = 'Bạn chưa nhập tên lớp';
				$data['status']         = FALSE;
			}

			if ($this->input->post('ten_course') == '') {
				$data['inputerror'][]   = 'ten_course';
				$data['error_string'][] = 'Bạn chưa nhập tên lớp môn';
				$data['status']         = FALSE;
			} else if($this->osp100_m->get_ten_lop($this->input->post('ten_course')) != null) {
				$data['inputerror'][]   = 'ten_course';
				$data['error_string'][] = 'Tên lớp môn đã tồn tại trên hệ thống';
				$data['status']         = FALSE;
			}
			if ($this->input->post('ngay_bat_dau') == '') {
				$data['inputerror'][]   = 'ngay_bat_dau';
				$data['error_string'][] = 'Bạn chưa nhập ngày bắt đầu';
				$data['status']         = FALSE;
			}
			if ($this->input->post('ngay_ket_thuc') == '') {
				$data['inputerror'][]   = 'ngay_ket_thuc';
				$data['error_string'][] = 'Bạn chưa nhập ngày kết thúc';
				$data['status']         = FALSE;
			}
			if ($this->input->post('ngay_thi') == '') {
				$data['inputerror'][]   = 'ngay_thi';
				$data['error_string'][] = 'Bạn chưa nhập ngày thi';
				$data['status']         = FALSE;
			}
			if ($this->input->post('ngay_thi_chinh') == '') {
				$data['inputerror'][]   = 'ngay_thi_chinh';
				$data['error_string'][] = 'Bạn chưa nhập ngày thi cuối cùng';
				$data['status']         = FALSE;
			}
			if ($this->input->post('so_luong_sv') == '') {
				$data['inputerror'][]   = 'so_luong_sv';
				$data['error_string'][] = 'Bạn chưa nhập số lượng sinh viên dự kiến';
				$data['status']         = FALSE;
			}

			if ($this->input->post('id_forum') == '') {
				$data['inputerror'][]   = 'id_forum';
				$data['error_string'][] = 'Bạn chưa nhập ID diễn đàn';
				$data['status']         = FALSE;
			}
			if ($this->input->post('username_povh') == '') {
				$data['inputerror'][]   = 'username_povh';
				$data['error_string'][] = 'Bạn chưa nhập username POVH';
				$data['status']         = FALSE;
			}
			if ($this->input->post('username_tg') == '') {
				$data['inputerror'][]   = 'username_tg';
				$data['error_string'][] = 'Bạn chưa nhập username TG';
				$data['status']         = FALSE;
			} else if($this->users_m->isset_data_gv($this->input->post('username_tg')) == null) {
				$data['inputerror'][]   = 'username_tg';
				$data['error_string'][] = 'Username trợ giảng không tồn tại';
				$data['status']         = FALSE;
			}
			if ($this->input->post('username_gvcm') == '') {
				$data['inputerror'][]   = 'username_gvcm';
				$data['error_string'][] = 'Bạn chưa nhập username GVCM';
				$data['status']         = FALSE;
			} else if($this->teachers_m->isset_data_gv($this->input->post('username_gvcm')) == null) {
				$data['inputerror'][]   = 'username_gvcm';
				$data['error_string'][] = 'Username gvcm không tồn tại';
				$data['status']         = FALSE;
			}
			if($this->input->post('username_gvhd') != '' && $this->teachers_m->isset_data_gv($this->input->post('username_gvhd')) == null) {
				$data['inputerror'][]   = 'username_gvhd';
				$data['error_string'][] = 'Username gvhd không tồn tại';
				$data['status']         = FALSE;
			}

			if ($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
		}

	}

?>