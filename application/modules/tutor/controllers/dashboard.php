<?php
/**
 * Created by PhpStorm.
 * User: danglx
 * Email: lexuandang89@gmail.com
 * Date: 3/23/17
 * Time: 12:59 AM
 */
defined('BASEPATH') OR exit('No direct script access allowed');

class dashboard extends CI_controller
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Lấy danh sách tài khoản giảng viên
	 */
	public function index()
	{
		check_access();
		$this->load->helper('url');
		$title['title'] = SYSTEM_NAME;
		$active['active_home'] = 'class="active"';

		$this->load->view('header', $title);
		$this->load->view('top');
		$this->load->view('menu_left', $active);
		$this->load->view('dashboard_view');
		$this->load->view('footer');
	}

}

?>