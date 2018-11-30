<?php
	/**
	 * Created by PhpStorm.
	 * User: computer
	 * Date: 3/25/17
	 * Time: 10:18 PM
	 */

	/**
	 * @name  : Chuyển tiếng việt có dấu thành không dấu
	 *
	 * @param : $str
	 *
	 * @return: $str
	 * @author: Lê Xuân Đăng (danglx@topica.edu.vn)
	 */
	function unicode_str_filter($str)
	{
		$unicode = array(
			'a' => 'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
			'd' => 'đ',
			'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
			'i' => 'í|ì|ỉ|ĩ|ị',
			'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
			'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
			'y' => 'ý|ỳ|ỷ|ỹ|ỵ',
			'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
			'D' => 'Đ',
			'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
			'I' => 'Í|Ì|Ỉ|Ĩ|Ị',
			'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
			'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
			'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
		);
		foreach ($unicode as $nonUnicode => $uni) {
			$str = preg_replace("/($uni)/i", $nonUnicode, $str);
		}

		return $str;
	}

	/**
	 * Check quyen truy cap course
	 *
	 * @return mixed
	 */
	function check_access()
	{
		$ci =& get_instance();
		if (!isset($_SESSION)) {
			session_start();
		}

		$user_data = $ci->session->userdata('user_data');
		if (empty($user_data)) {
			redirect(base_url() . 'login');
			exit;
		} else {
			$user_role_id = $user_data['role_id'];
			if ($user_role_id > 2) {
				$link = substr($_SERVER['REQUEST_URI'], strrpos($_SERVER['REQUEST_URI'], '/') + 1);
				if ($link !='ketrapha_report'){
					echo 'Bạn không được phép truy cập vào đường dẫn này!';
					exit();
				}
			}

			return $user_data;
		}
	}

//Function lấy ngày bắt đầu tuần
	function f_get_WeekStartDate()
	{
		if (date('w') == 1) {
			$start_date = strtotime("last Monday") + (7 * 86400);
		} else {
			$start_date = strtotime("last Monday");
		}

		return $start_date;
	}

//Function lấy ngày kết thúc tuần
	function f_get_WeekEndDate()
	{
//	if (date('w') == 0) {
//		$end_date = strtotime("last Friday") + (7 * 86400);
//	} else {
//		$end_date = strtotime("last Friday")+ (7 * 86400);
//	}
		if (date('w') == 0) {
			$end_date = strtotime("next Sunday") - (7 * 86400);
		} else {
			$end_date = strtotime("next Sunday");
		}

		return $end_date;
	}

	/**
	 * Lay username cua nguoi dung
	 *
	 * @param $user_id
	 *
	 * @return string
	 */
	function get_username_by_id($user_id = 0)
	{
		$kq='';
		if ($user_id != 0 && $user_id != '') {
			$sql    = "SELECT
				username
			FROM
				users
			WHERE
				id = " . $user_id;
			$ci     =& get_instance();
			$query  = $ci->db->query($sql);
			$result = $query->row();
			if ($result) {
				$kq =  $result->username;
			}
		}

		return $kq;
	}

	/**
	 * Lay thong tin chi tiet nguoi dung
	 *
	 * @param $user_id
	 *
	 * @return string
	 */
	function get_user_info_by_id($user_id)
	{
		$sql    = "SELECT
				username,lastname, firstname,email,mobile_number
			FROM
				users
			WHERE
				id = " . $user_id;
		$ci     =& get_instance();
		$query  = $ci->db->query($sql);
		$result = $query->row();
		if ($result) {
			return $result;
		}

		return null;
	}

	/**
	 * Lay userid
	 *
	 * @param $username
	 *
	 * @return null
	 */
	function get_id_by_username($username)
	{
		$sql    = "SELECT
				id
			FROM
				users
			WHERE
				username = '" . $username . "'";
		$ci     =& get_instance();
		$query  = $ci->db->query($sql);
		$result = $query->row();
		if ($result) {
			return $result->id;
		}

		return NULL;
	}

	/**
	 * Lay ten tu dien
	 *
	 * @param $Id
	 *
	 * @return string
	 */
	function get_dictionary_by_id($Id)
	{
		$sql    = "SELECT
				code,name
			FROM
				dictionaries
			WHERE
				id = " . $Id;
		$ci     =& get_instance();
		$query  = $ci->db->query($sql);
		$result = $query->row();
		if ($result) {
			return $result;
		}

		return '';
	}

	/**
	 * Lay ten mau
	 *
	 * @param $Id
	 *
	 * @return string
	 */
	function get_name_template_by_id($Id)
	{
		$sql    = "SELECT
				ten_mau
			FROM
				template
			WHERE
				id = " . $Id;
		$ci     =& get_instance();
		$query  = $ci->db->query($sql);
		$result = $query->row();
		if ($result) {
			return $result->ten_mau;
		}

		return '';
	}

	/**
	 * Lay thong tin nguoi dung
	 *
	 * @param $user_id
	 *
	 * @return string
	 */
	function get_fullname_gv_by_username($username)
	{
		$sql    = "SELECT
				lastname, firstname
			FROM
				teachers
			WHERE
				username = " . $username;
		$ci     =& get_instance();
		$query  = $ci->db->query($sql);
		$result = $query->row();
		if ($result) {
			return $result->lastname . ' ' . $result->firstname;
		}

		return '';
	}

	/**
	 * Lay ra chi tiet mau cho module
	 * @param string $module_code
	 *
	 * @return null
	 */
	function get_template_by_module($module_code = '')
	{
		$sql    = "SELECT
						t.tieu_de,
						t.noi_dung2 content_mail,
						g.ngay_gui,
						g.gio,
						g.phut,
						d. CODE
					FROM
						gd_module_template g
					INNER JOIN template t ON t.id = g.id_template
					INNER JOIN dictionaries d ON d.id = g.id_module
					WHERE
						d.code = '".$module_code."'";
		$ci     =& get_instance();
		$query  = $ci->db->query($sql);
		$result = $query->row();
		if ($result) {
			return $result;
		}

		return NULL;
	}

	/**
	 * Lay danh sach GV chua post bai chao mung
	 * @return null
	 */
	function get_teacher_post_cm($action='')
	{
		$sql_time ="AND DATE_FORMAT(g.created_at,'%Y-%m-%d %H:%i') = '".date('Y-m-d H:i',time())."'";
		if($action!='' && $action=='export'){
			$sql_time ="AND DATE_FORMAT(g.created_at,'%Y-%m-%d %H') = '".date('Y-m-d H',time())."'";
		}
		$sql    = "SELECT
						CONCAT_WS(' ', t.lastname, t.firstname) ten_gv,
						t.role_name loai_gv,
						t.mobile_number,
						c.system,
						c.course_name,
						c.start_date_course,
						u.username tro_giang
					FROM
						gd_teacher_course g
					INNER JOIN teachers t ON t.id = g.teacher_id
					INNER JOIN users u ON u.id = g.tro_giang_id
					INNER JOIN dm_course c ON c.id = g.dm_course_id
					WHERE
						g.action = 'cm'
					AND post_cm = 0
					$sql_time
					ORDER BY
						g.teacher_id";
		$ci     =& get_instance();
		$query  = $ci->db->query($sql);
		$result = $query->result();
		if ($result) {
			return $result;
		}

		return NULL;
	}

	/**
	 * Lay danh sach GV chua post du bai dinh muc tuan
	 * @return null
	 */
	function get_teacher_post_tlm($action='')
	{
		$sql_time ="AND DATE_FORMAT(g.created_at,'%Y-%m-%d %H:%i') = '".date('Y-m-d H:i',time())."'";
		if($action!='' && $action=='export'){
			$sql_time ="AND DATE_FORMAT(g.created_at,'%Y-%m-%d %H') = '".date('Y-m-d H',time())."'";
		}
		$sql    = "SELECT
						c.system,
						CONCAT_WS(' ', t.lastname, t.firstname) ten_gv,
						t.role_name loai_gv,
						t.mobile_number,
						c.course_name,
						c.start_date_course,
						c.end_date_course,
						c.exam_date_course,
						g.post_tlm,
						g.total_post,
						u.username tro_giang
					FROM
						gd_teacher_course g
					INNER JOIN teachers t ON t.id = g.teacher_id
					INNER JOIN users u ON u.id = g.tro_giang_id
					INNER JOIN dm_course c ON c.id = g.dm_course_id
					WHERE
						g.action = 'tlm'
					AND IF(t.role_name='GVCM',post_tlm = 0,post_tlm <= 2)
					$sql_time
					ORDER BY
						g.teacher_id";
		$ci     =& get_instance();
		$query  = $ci->db->query($sql);
		$result = $query->result();
		if ($result) {
			return $result;
		}

		return NULL;
	}

?>