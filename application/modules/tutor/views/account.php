<?php
	/**
	 * Created by PhpStorm.
	 * User: danglx
	 * Email: lexuandang89@gmail.com
	 * Date: 3/23/17
	 * Time: 12:59 AM
	 */
	defined('BASEPATH') OR exit('No direct script access allowed');

	class Account extends CI_controller
	{
		var $md5_salt = '1@ANmC^%^wrFO';

		public function __construct()
		{
			parent::__construct();
			$this->load->model('login/users_m', 'users_m');
			$this->load->model('dictionaries_m');
		}

		/**
		 * Lấy danh sách tài khoản người dùng
		 */
		public function index()
		{
			check_access();
			$this->load->helper('url');
			$form['list_acc'] = $this->users_m->get_list();

			$form['permissionList'] = $this->dictionaries_m->get_by_categories_code('role');
			$title['title'] = SYSTEM_NAME;
			$active['active_account'] = 'class="active"';

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('account_view', $form);
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
			$data = $this->users_m->get_by_id($id);
			echo json_encode($data);
		}

		/**
		 * Thêm mới tài khoản
		 */
		public function ajax_add()
		{
			check_access();
			$this->_validate('add');
			$password = md5(md5($this->md5_salt . sha1($this->input->post("Password") . $this->md5_salt)));
			$data = array(
				'username'         => $this->input->post('UserName'),
				'firstname'        => $this->input->post('FirstName'),
				'lastname'         => $this->input->post('LastName'),
				'email'            => $this->input->post('Email'),
				'email_person'     => $this->input->post('Email_Personal'),
				'mobile_number'    => $this->input->post('Phone'),
				'status_delete_yn' => $this->input->post('Enabled'),
				'role_id'          => $this->input->post('Permission'),
//			'UpdatedBy'        => $this->session->userdata('IdUser'),
				'password'         => $password,
				'created_at'       => date('Y-m-d H:i:s', time()),
				'updated_at'       => date('Y-m-d H:i:s', time())
			);

			$insert = $this->users_m->insert($data);
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
				'role_id'          => $this->input->post('Permission'),
//			'UpdatedBy'        => $this->session->userdata('IdUser'),
				'updated_at'       => date('Y-m-d H:i:s', time())
			);
			if ($this->input->post('Password') != '') {
				$password = md5(md5($this->md5_salt . sha1($this->input->post("Password") . $this->md5_salt)));
				$data['password'] = $password;
			}
			$this->users_m->update($this->input->post('IdUser'), $data);
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * Khoá mở tài khoản người dùng
		 */
		public function ajax_lock($id)
		{
			check_access();
			$data = $this->users_m->get_by_id($id);

			if ($data->status_delete_yn == 'n') {
				$delete_yn = 'y';
			} else {
				$delete_yn = 'n';
			}

			$this->users_m->update($id, array('status_delete_yn' => $delete_yn));
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

			$this->users_m->delete_by_id($IdUser);
			echo json_encode(array("status" => TRUE));
		}

		/**
		 * Validate input data
		 *
		 * @param string $action
		 */
		private function _validate($action = '')
		{
			$data = array();
			$data['error_string'] = array();
			$data['inputerror'] = array();
			$data['status'] = TRUE;
			$username = $this->input->post('UserName');
			if (!preg_match("/^[0-9a-zA-Z_.]*$/", $username) || $username == '') {
				$data['inputerror'][] = 'UserName';
				$data['error_string'][] = 'Tên đăng nhập không hợp lệ';
				$data['status'] = FALSE;
			}

			if ($this->input->post('LastName') == '') {
				$data['inputerror'][] = 'LastName';
				$data['error_string'][] = 'Bạn phải nhập họ và đệm';
				$data['status'] = FALSE;
			}

			if ($this->input->post('FirstName') == '') {
				$data['inputerror'][] = 'FirstName';
				$data['error_string'][] = 'Bạn phải nhập tên riêng cho người dùng';
				$data['status'] = FALSE;
			}


			if ($this->input->post('Permission') == '') {
				$data['inputerror'][] = 'Permission';
				$data['error_string'][] = 'Loại tài khoản không hợp lệ';
				$data['status'] = FALSE;
			}
			if (!filter_var($this->input->post('Email'), FILTER_VALIDATE_EMAIL)) {
				$data['inputerror'][] = 'Email';
				$data['error_string'][] = 'Email không hợp lệ';
				$data['status'] = FALSE;
			}

			if (!preg_match('/^[0-9]+$/', $this->input->post('Phone'))) {
				$data['inputerror'][] = 'Phone';
				$data['error_string'][] = 'Số điện thoại không hợp lệ';
				$data['status'] = FALSE;
			}
			if ($action == 'add') {
				$pass = $this->input->post('Password');
				$repass = $this->input->post('RePassword');
				if ($pass == '') {
					$data['inputerror'][] = 'Password';
					$data['error_string'][] = 'Bạn phải nhập mật khẩu đăng nhập';
					$data['status'] = FALSE;
				}
				if ($repass == '' || $repass != $pass) {
					$data['inputerror'][] = 'RePassword';
					$data['error_string'][] = 'Bạn nhập mật khẩu chưa khớp';
					$data['status'] = FALSE;
				}
			} else {
				$pass = $this->input->post('Password');
				$repass = $this->input->post('RePassword');
				if ($pass == '' && $repass != '') {
					$data['inputerror'][] = 'Password';
					$data['error_string'][] = 'Bạn phải nhập mật khẩu đăng nhập';
					$data['status'] = FALSE;
				}
				if ($repass != $pass) {
					$data['inputerror'][] = 'RePassword';
					$data['error_string'][] = 'Bạn nhập mật khẩu chưa khớp';
					$data['status'] = FALSE;
				}
			}
			if ($data['status'] === FALSE) {
				echo json_encode($data);
				exit();
			}
		}

	}

?>