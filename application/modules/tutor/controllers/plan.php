<?php 
    Class Plan extends CI_controller {
        public function __construct() {
			parent::__construct();
			$this->load->model('plans_m');
        }
        
        /**
		 * Lấy danh sách kế hoạch
		 */
		public function index()
		{
			check_access();
			$this->load->helper('url');
			$form['list_plan'] = $this->plans_m->get_list();

			$title['title'] = 'Danh sách kế hoạch';
			$active['active_plan'] = 'class="active"';

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('plan_view', $form);
			$this->load->view('footer');
		}

		public function ajax_edit($id)
		{
			check_access();
			$data = $this->plans_m->get_by_id($id);
			echo json_encode($data);
		}

		/**
		 * Thêm mới tài khoản
		 */
		public function ajax_add()
		{
			$username = check_access();
			$this->_validate();
			$data = array(
				'ma_dot_hoc'      	=> $this->input->post('codePlan'),
				'ten_dot_hoc'       => $this->input->post('namePlan'),
				'ghi_chu'        	=> $this->input->post('ghi_chu'),
				'created_by'		=> $username['user_id'],
				'created_at'       	=> date('Y-m-d H:i:s', time()),

			);

			$insert = $this->plans_m->insert($data);
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * Cập nhật thông tin kế hoạch
		 */
		public function ajax_update()
		{
			$username = check_access();
			$this->_validate();
			$data = array(
				'ma_dot_hoc'      	=> $this->input->post('codePlan'),
				'ten_dot_hoc'       => $this->input->post('namePlan'),
				'ghi_chu'        	=> $this->input->post('ghi_chu'),
				'updated_by'		=> $username['user_id'],
				'updated_at'       	=> date('Y-m-d H:i:s', time())
			);
			$this->plans_m->update($this->input->post('IdPlan'), $data);
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * Xoá kế hoạch
		 *
		 * @param $id
		 */
		public function ajax_delete()
		{
			check_access();
			$IdPlan = $this->input->post('IdPlan');

			$this->plans_m->delete_by_id($IdPlan);
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
			// $namePlan = $this->input->post('namePlan');
			if ($this->input->post('namePlan') == '') {
				$data['inputerror'][] = 'namePlan';
				$data['error_string'][] = 'Bạn phải nhập tên đợt học';
				$data['status'] = FALSE;
			}
			$code = $this->input->post('codePlan');
			if (!preg_match("/^[0-9a-zA-Z_]*$/", $code) || $code == '') {
				$data['inputerror'][] = 'codePlan';
				$data['error_string'][] = 'Mã đợt học không hợp lệ. Mã đợt học chỉ chứa các kí tự từ 0-9, a-z, A-Z và kí tự "_"';
				$data['status'] = FALSE;
			}

			if ($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
		}
    }
?>