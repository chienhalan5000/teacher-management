<?php
	/**
	 * Created by PhpStorm.
	 * User: danglx
	 * Email: lexuandang89@gmail.com
	 * Date: 3/23/17
	 * Time: 12:59 AM
	 */
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Task extends CI_controller
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->model('gd_module_template_m');
			$this->load->model('templates_m');
			$this->load->model('dictionaries_m');
			$this->load->model('assumption_m');
		}

		/**
		 * Lấy danh sách
		 */
		public function index()
		{
			check_access();
			$this->load->helper('url');
			$form['list_task'] = $this->gd_module_template_m->get_list();
			$input          = array();
			$input['where'] = array('enable_yn' => 'Y');
			$form['list_template'] = $this->templates_m->get_list($input);
			$input['where'] = array('categories_id' => 5, 'enable_yn' => 'Y');
			$form['list_module'] = $this->assumption_m->get_list($input);
//			$form['list_template'] = $this->templates_m->get_list_email();
			$title['title'] = 'Danh sách chức năng auto';
			$active['active_task'] = 'class="active"';

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('task_view', $form);
			$this->load->view('footer');
		}

		/**
		 * Them mới
		 */
		public function ajax_add()
		{
			$username = check_access();
			$this->_validate("add");

			$data = array(
				'id_module'        	=> $this->input->post('idModule'),
				'id_template'       => $this->input->post('idTemplate'),
				'ngay_gui'			=> $this->input->post('dateOfWeek'),
				'gio'				=> $this->input->post('hours'),
				'phut'				=> $this->input->post('minutes'),
				'created_by'		=> $username['user_id'],
				'created_at'       	=> date('Y-m-d H:i:s', time()),
			);

			$insert = $this->gd_module_template_m->insert($data);
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
			$data = $this->gd_module_template_m->get_by_id($id);
			echo json_encode($data);
		}

		/**
		 * Cap nhat thong tin
		 */
		public function ajax_update()
		{
			$username = check_access();
			$this->_validate();

			$data = array(
				// 'id_module'        	=> $this->input->post('idModule'),
				'id_template'       => $this->input->post('idTemplate'),
				'ngay_gui'			=> $this->input->post('dateOfWeek'),
				'updated_by'		=> $username['user_id'],
				'updated_at'       	=> date('Y-m-d H:i:s', time()),
			);

			$this->gd_module_template_m->update($this->input->post('idTask'), $data);
			echo json_encode(array("status" => TRUE));
		}

		public function ajax_delete()
		{
			check_access();
			$Id = $this->input->post('idTask');

			$this->gd_module_template_m->delete_by_id($Id);
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * Validate input data
		 *
		 * @param string $action
		 */
		private function _validate($method = '')
		{
			$data                 = array();
			$data['error_string'] = array();
			$data['inputerror']   = array();
			$data['status']       = TRUE;
			if ($this->input->post('idTemplate') == '') {
				$data['inputerror'][]   = 'ten_mau_hide';
				$data['error_string'][] = 'Bạn chưa chọn tên mẫu';
				$data['status']         = FALSE;
			}

			if($method == "add") {
				if ($this->input->post('hours') == '') {
					$data['inputerror'][]   = 'ten_gio_hide';
					$data['error_string'][] = 'Bạn chưa chọn giờ';
					$data['status']         = FALSE;
				}

				if ($this->input->post('minutes') == '') {
					$data['inputerror'][]   = 'ten_phut_hide';
					$data['error_string'][] = 'Bạn chưa chọn phút';
					$data['status']         = FALSE;
				}

				if ($this->input->post('idModule') == '') {
					$data['inputerror'][]   = 'ten_module_hide';
					$data['error_string'][] = 'Bạn chưa chọn tên module';
					$data['status']         = FALSE;
				}
			}
			
			if ($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
		}

	}

?>