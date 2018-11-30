<?php
	defined('BASEPATH') OR exit('No direct script access allowed');

	class MY_Controller extends CI_Controller
	{
		public function __construct()
		{

			parent::__construct();
			/**
			 * Check quyen truy cap course
			 *
			 * @return mixed
			 */
		}

		public
		function check_access()
		{
			if (!isset($_SESSION)) {
				session_start();
			}

			$user_data = $this->session->userdata('user_data');
			if (empty($user_data)) {
//			echo "Bạn cần đăng nhập lại hệ thống. <a href='http://" . $_SERVER['HTTP_HOST'] . "/kpigvhd/login' >Click để đăng nhập</a>.";
				redirect(base_url() . 'login');
				exit;
			} else {
				$user_role_id = $user_data['role_id'];
				if ($user_role_id > 2) {
					$link = substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], '/') + 1);
					if ($link != 'ketrapha_report') {
						echo 'Bạn không được phép truy cập vào đường dẫn này!';
						exit();
					}
				}

				return $user_data;
			}
		}
	}

?>