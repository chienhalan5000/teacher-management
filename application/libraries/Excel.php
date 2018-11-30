<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');
/**
 *
 */
require_once APPPATH . "/third_party/PHPExcel.php";

class Excel extends PHPExcel
{

	public function __construct()
	{
		parent::__construct();
	}

	public function read_file_trapha($excel_path)
	{
		$data_return = array();
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		$objReader->setReadDataOnly(TRUE);

		$objPHPExcel = $objReader->load($excel_path);
		if ($objPHPExcel) {
			$objWorksheet = $objPHPExcel->getActiveSheet();

			$highestRow = $objWorksheet->getHighestRow(); // e.g. 10
			$highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'

			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5

			$need_colum = array(
				"stt" => 0, //stt
				"tieu_chi" => 1, //ten mon
				"username" => 2, //ma mon
				"so_lan" => 3,
				"thoi_gian" => 4,
				"noi_dung" => 5
			);
			for ($row = 2; $row <= $highestRow; ++$row) {
				$data_row = array();
				if (intval($objWorksheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()) && $objWorksheet->getCellByColumnAndRow(1, $row)->getCalculatedValue() != "") {
					foreach ($need_colum as $col_name => $col) {
						$data_row[$col_name] = $objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
					}
					$data_return [] = $data_row;
				}
			}
		}

		return $data_return;
	}


	public function read_file_osp($excel_path)
	{
		$data_return = array();
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		$objReader->setReadDataOnly(TRUE);

		$objPHPExcel = $objReader->load($excel_path);
		if ($objPHPExcel) {
			$objWorksheet = $objPHPExcel->getActiveSheet();

			$highestRow = $objWorksheet->getHighestRow(); // e.g. 10
			$highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'

			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5

			$need_colum = array(
				"stt" => 0, //stt
				"system" => 1, //stt
				"ten_mon" => 2, //ten mon
				"ma_mon" => 3, //ma mon
				"ten_course" => 4,
				"ngay_bat_dau" => 5,
				"ngay_ket_thuc" => 6,
				"ngay_thi_chinh" => 7,
				"so_luong_sv" => 8,
				"username_tg" => 9,
				"ten_gvcm" => 10,
				"username_gvcm" => 11,
				"ten_gvhd" => 12,
				"username_gvhd" => 13
			);
			for ($row = 3; $row <= $highestRow; ++$row) {
				$data_row = array();
				if (intval($objWorksheet->getCellByColumnAndRow(0, $row)->getCalculatedValue()) && $objWorksheet->getCellByColumnAndRow(1, $row)->getCalculatedValue() != "") {
					foreach ($need_colum as $col_name => $col) {
						$data_row[$col_name] = $objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
					}
					$data_return [] = $data_row;
				}
			}
		}

		return $data_return;
	}

	public function read_file_DSGV($excel_path)
	{
		$data_return = array();
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		$objReader->setReadDataOnly(TRUE);

		$objPHPExcel = $objReader->load($excel_path);
		if ($objPHPExcel) {
			$objWorksheet = $objPHPExcel->getActiveSheet();

			$highestRow = $objWorksheet->getHighestRow(); // e.g. 10
			$highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'

			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5

			$need_colum = array(
				"stt" => 0, //stt
				"username" => 1, //ten mon
				"lastname" => 2, //ma mon
				"firstname" => 3,
				"email" => 4,
				"email_person" => 5,
				"mobile_number" => 6,
				"role_name" => 7,
			);
			for ($row = 2; $row <= $highestRow; ++$row) {
				$data_row = array();
				if (intval($objWorksheet->getCellByColumnAndRow(0, $row)->getCalculatedValue())) {
					foreach ($need_colum as $col_name => $col) {
						$data_row[$col_name] = $objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
					}
					$data_return [] = $data_row;
				}
			}
		}

		return $data_return;
	}

	public function read_file_email_template($excel_path)
	{
		$data_return = array();
		$objReader = PHPExcel_IOFactory::createReader('Excel2007');
		$objReader->setReadDataOnly(TRUE);

		$objPHPExcel = $objReader->load($excel_path);
		if ($objPHPExcel) {
			$objWorksheet = $objPHPExcel->getActiveSheet();

			$highestRow = $objWorksheet->getHighestRow(); // e.g. 10
			$highestColumn = $objWorksheet->getHighestColumn(); // e.g 'F'

			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); // e.g. 5

			$need_colum = array(
				"stt" => 0, 
				"username_giang_vien" => 1, 
				"ten_chung_tu" => 2,
				"thoi_gian_den" => 3,
				"deadline" => 4,
				"username_tro_giang" => 5,
			);
			for ($row = 3; $row <= $highestRow; ++$row) {
				$data_row = array();
				if (intval($objWorksheet->getCellByColumnAndRow(0, $row)->getCalculatedValue())) {
					foreach ($need_colum as $col_name => $col) {
						$data_row[$col_name] = $objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
					}
					$data_return [] = $data_row;
				}
			}
		}

		return $data_return;
	}
}
