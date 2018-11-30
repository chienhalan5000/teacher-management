<?php
    class Setting_mail extends CI_controller {
        public function __construct() {
            parent::__construct();
            $this->load->model('setting_mail_m');
        }

        public function index() {
            check_access();
			$this->load->helper('url');
			$form['mail_sent']    = $this->setting_mail_m->get_by_id(1);
			$title['title']           = 'Setting mail';
			$active['active_setting_mail'] = 'class="active"';

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('setting_mail_view', $form);
			$this->load->view('footer');
        }

        /**
		 * Lấy thông tin mail
		 *
		 * @param $id
		 */
		public function ajax_edit($id)
		{
			check_access();
			$data = $this->setting_mail_m->get_by_id(1);
			echo json_encode($data);
        }
        
        /**
		 * Cập nhật thông tin mail
		 */
		public function ajax_update()
		{
			$username = check_access();
			$this->_validate();
			$data = array(
				'name'          => $this->input->post('name'),
				'email'         => $this->input->post('email'),
				'password'      => $this->input->post('password'),
				'updated_by'    => $username['user_id'],
				'updated_at'    => date('Y-m-d H:i:s', time())
			);
			$this->setting_mail_m->update(1, $data);
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
			if ($this->input->post('name') == '') {
				$data['inputerror'][] = 'name';
				$data['error_string'][] = 'Bạn phải nhập tên mail';
				$data['status'] = FALSE;
			}
			if ($this->input->post('email') == '' || !filter_var($this->input->post('email'), FILTER_VALIDATE_EMAIL)) {
                $data['inputerror'][] = 'email';
				$data['error_string'][] = 'Email không hợp lệ';
				$data['status'] = FALSE;
            }
            $password = $this->input->post('password');
            if (!preg_match("/^[0-9a-zA-Z_]*$/", $password) || $password == '') {
				$data['inputerror'][]   = 'codeTemplate';
				$data['error_string'][] = 'Mật khẩu không hợp lệ. Mật khẩu chỉ chứa các kí tự từ 0-9, a-z, A-Z và kí tự "_"';
				$data['status']         = FALSE;
			}

			if ($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
		}
    }
?>