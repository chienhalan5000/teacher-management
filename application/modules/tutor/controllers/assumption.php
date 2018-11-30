<?php
	/**
	 * Created by PhpStorm.
	 * User: danglx
	 * Email: lexuandang89@gmail.com
	 * Date: 3/23/17
	 * Time: 12:59 AM
	 */
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Assumption extends CI_controller
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->model('assumption_m');
		}

		/**
		 * Lấy danh sách
		 */
		public function index()
		{
			check_access();
			$this->load->helper('url');
			$form['list_assumption'] = $this->assumption_m->get_all();
			$title['title'] = SYSTEM_NAME;
			$active['active_assumption'] = 'class="active"';

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('assumption_view', $form);
			$this->load->view('footer');
		}

		/**
		 * Them mới
		 */
		public function ajax_add()
		{
			check_access();
			$this->_validate();

			$data = array(
				'categories_id' => $this->input->post('categories_id'),
				'code'          => $this->input->post('Code'),
				'name'          => $this->input->post('Name'),
				'description'   => $this->input->post('Description'),
				'sort'          => $this->input->post('Sort'),
				'enable_yn'     => $this->input->post('Enabled'),
				'created_at'     => date('Y-m-d H:i:s',time()),
				'updated_at'     => date('Y-m-d H:i:s',time())
			);

			$insert = $this->assumption_m->insert($data);
			echo json_encode(array("status" => TRUE));
		}

		public function ajax_delete()
		{
			check_access();
			$Id = $this->input->post('id_del');

			$this->assumption_m->delete_by_id($Id);
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
			$data = $this->assumption_m->get_by_id($id);
			echo json_encode($data);
		}

		/**
		 * Cap nhat thong tin
		 */
		public function ajax_update()
		{
			check_access();
			$this->_validate();
			$data = array(
				'categories_id' => $this->input->post('categories_id'),
				'code'          => $this->input->post('Code'),
				'name'          => $this->input->post('Name'),
				'description'   => $this->input->post('Description'),
				'sort'          => $this->input->post('Sort'),
				'enable_yn'     => $this->input->post('Enabled'),
				'updated_at'     => date('Y-m-d H:i:s',time())
			);

			$this->assumption_m->update($this->input->post('id'), $data);
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
			$code = $this->input->post('Code');
			if (!preg_match("/^[0-9a-zA-Z_\/]*$/", $code) || $code == '') {
				$data['inputerror'][] = 'Code';
				$data['error_string'][] = 'Tên rút gọn không hợp lệ. Chỉ được phép dùng các ký tự chữ và số.';
				$data['status'] = FALSE;
			}

			if ($this->input->post('Name') == '') {
				$data['inputerror'][] = 'Name';
				$data['error_string'][] = 'Bạn phải nhập tên đầy đủ';
				$data['status'] = FALSE;
			}
			if ($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
		}
	}

?>