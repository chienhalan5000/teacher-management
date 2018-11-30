<?php

	Class Template extends CI_controller
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->model('templates_m');
		}

		public function index()
		{
			check_access();
			$this->load->helper('url');
			$form['list_template']     = $this->templates_m->get_list();
			$title['title']            = 'Danh sách mẫu email';
			$active['active_template'] = 'class="active"';

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('template_view', $form);
			$this->load->view('footer');
		}

		/**
		 * Lấy thông tin mẫu mail
		 *
		 * @param $id
		 */
		public function ajax_edit($id)
		{
			check_access();
			$data = $this->templates_m->get_by_id($id);
			echo json_encode($data);
		}

		/**
		 * Thêm mới mẫu email
		 */
		public function ajax_add()
		{
			$username = check_access();
			$this->_validate();
			$content2 = $this->input->post('editor_noidung');
			$content2 = str_replace("&lt;!--", "<!--", $content2);
			$content2 = str_replace("--&gt;", "-->", $content2);
			$data = array(
				'ten_mau'    => $this->input->post('nameTemplate'),
				'ma_mau'     => $this->input->post('codeTemplate'),
				'loai_mau'   => $this->input->post('typeTemplate'),
				'tieu_de'    => $this->input->post('title'),
				'noi_dung'   => $this->input->post('editor_noidung'),
				'noi_dung2'   => $content2,
				'created_by' => $username['user_id'],
				'created_at' => date('Y-m-d H:i:s', time()),
				'enable_yn'  => $this->input->post('Enabled')
			);

			$insert = $this->templates_m->insert($data);
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * Cập nhật thông tin mẫu
		 */
		public function ajax_update()
		{
			$username = check_access();
			$this->_validate();
			$content2 = $this->input->post('editor_noidung');
			$content2 = str_replace("&lt;!--", "<!--", $content2);
			$content2 = str_replace("--&gt;", "-->", $content2);
			$data = array(
				'ten_mau'    => $this->input->post('nameTemplate'),
				'ma_mau'     => $this->input->post('codeTemplate'),
				'loai_mau'   => $this->input->post('typeTemplate'),
				'tieu_de'    => $this->input->post('title'),
				'noi_dung'   => $this->input->post('editor_noidung'),
				'noi_dung2'   => $content2,
				'updated_by' => $username['user_id'],
				'updated_at' => date('Y-m-d H:i:s', time()),
				'enable_yn'  => $this->input->post('Enabled')
			);
			$this->templates_m->update($this->input->post('IdTemplate'), $data);
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * Bật/tắt mẫu
		 */
		public function ajax_lock($id)
		{
			check_access();
			$data = $this->templates_m->get_by_id($id);

			if ($data->enable_yn == 'N') {
				$status = 'Y';
			} else {
				$status = 'N';
			}

			$this->templates_m->update($id, array('enable_yn' => $status));
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * Xoá mẫu
		 *
		 * @param $id
		 */
		public function ajax_delete()
		{
			check_access();
			$IdTemplate = $this->input->post('IdTemplate');

			$this->templates_m->delete_by_id($IdTemplate);
			echo json_encode(array("status" => TRUE));
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

			if ($this->input->post('nameTemplate') == '') {
				$data['inputerror'][]   = 'nameTemplate';
				$data['error_string'][] = 'Bạn phải nhập tên mẫu';
				$data['status']         = FALSE;
			}

			$code = $this->input->post('codeTemplate');
			if (!preg_match("/^[0-9a-zA-Z_]*$/", $code) || $code == '') {
				$data['inputerror'][]   = 'codeTemplate';
				$data['error_string'][] = 'Mã mẫu không hợp lệ. Mã mẫu chỉ chứa các kí tự từ 0-9, a-z, A-Z và kí tự "_"';
				$data['status']         = FALSE;
			}

			if ($this->input->post('title') == '') {
				$data['inputerror'][]   = 'title';
				$data['error_string'][] = 'Bạn phải nhập tên tiêu đề';
				$data['status']         = FALSE;
			}

			if ($this->input->post('editor_noidung') == '') {
				$data['inputerror'][]   = 'content_hide';
				$data['error_string'][] = 'Bạn phải nhập nội dung';
				$data['status']         = FALSE;
			}

			if ($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
		}

		public function detail($id)
		{
			check_access();
			$data = $this->templates_m->get_by_id($id);
			echo $data->noi_dung2;
		}
	}

?>