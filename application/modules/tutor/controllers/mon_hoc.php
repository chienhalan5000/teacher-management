<?php
/**
 * Created by PhpStorm.
 * User: danglx
 * Email: lexuandang89@gmail.com
 * Date: 3/23/17
 * Time: 12:59 AM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class mon_hoc extends CI_controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('dm_mon_m');
		$this->load->helper('url');
	}

	/**
	 * Lấy danh sách
	 */
	public function index()
	{
		check_access();
		$form['list_mon_hoc'] = $this->dm_mon_m->get_list();

		$title['title'] = 'Danh mục môn học';
		$active['active_mon_hoc'] = 'class="active"';

		$this->load->view('header', $title);
		$this->load->view('top');
		$this->load->view('menu_left',$active);
		$this->load->view('mon_hoc_view', $form);
		$this->load->view('footer');
	}


	/**
	 * Chỉnh sửa
	 * @param $id
	 */
	public function ajax_edit($id)
	{
		check_access();
		$data = $this->dm_mon_m->get_by_id($id);
		echo json_encode($data);
	}

	/**
	 * Cap nhat thong tin
	 */
	public function ajax_update()
	{
		$user_data = check_access();
		$this->_validate();

		$data = array(
			'ma_mon'     => $this->input->post('ma_mon'),
			'ten_mon'    => $this->input->post('ten_mon'),
			'so_tin_chi'    => $this->input->post('so_tin_chi'),
			'link_document'    => $this->input->post('link_document'),
			'updated_by'    => $user_data['user_id'],
			'updated_at'    => date('Y-m-d H:i:s',time()),
			'ghi_chu'     => $this->input->post('ghi_chu')
		);


		$this->dm_mon_m->update($this->input->post('id_mon_hoc'), $data);
		echo json_encode(array("status" => TRUE));
	}
	public function ajax_delete()
	{
		check_access();
		$Id = $this->input->post('id_mon_hoc');

		$this->dm_mon_m->delete_by_id($Id);
		echo json_encode(array("status" => TRUE));
	}
	/**
	 * Validate input data
	 *
	 * @param string $action
	 */
	private function _validate()
	{
		$data = array();
		$data['error_string'] = array();
		$data['inputerror'] = array();
		$data['status'] = TRUE;
		$code = $this->input->post('ma_mon');
		if (!preg_match("/^[0-9a-zA-Z_]*$/", $code) || $code == '') {
			$data['inputerror'][] = 'ma_mon';
			$data['error_string'][] = 'Tên rút gọn không hợp lệ. Chỉ được phép dùng các ký tự chữ và số.';
			$data['status'] = FALSE;
		}else{
			$isset_mon_hoc= $this->dm_mon_m->get_by_ma_mon($code);
			if ($isset_mon_hoc && $isset_mon_hoc->id != $this->input->post('id_mon_hoc')) {
				$data['inputerror'][] = 'ma_mon';
				$data['error_string'][] = 'Mã môn đã tồn tại.';
				$data['status'] = FALSE;
			}
		}

		if ($this->input->post('ten_mon') == '') {
			$data['inputerror'][] = 'ten_mon';
			$data['error_string'][] = 'Bạn phải nhập tên đầy đủ';
			$data['status'] = FALSE;
		}
		if ($this->input->post('link_document') == '') {
			$data['inputerror'][] = 'link_document';
			$data['error_string'][] = 'Bạn phải nhập link tài liệu môn học';
			$data['status'] = FALSE;
		}

		if ($data['status'] === FALSE) {
			echo json_encode($data);
			exit();
		}
	}

}

?>