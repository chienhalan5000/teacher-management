<?php

/**
 * Created by PhpStorm.
 * User: danglx
 * Email: lexuandang89@gmail.com
 * Date: 3/23/17
 * Time: 12:59 AM
 */
class osp100_m extends MY_Model
{
	var $table = 'osp100';
	/**
	 * Kiem tra forum ID da co hay chua
	 * @param $id_forum
	 *
	 * @return bool
	 */
	public function isset_forumid($id_forum)
	{
		$this->db->select('count(1) count');
		$this->db->from($this->table);
		$this->db->where('id_forum <> 0');
		$this->db->where('id_forum', $id_forum);
		$query = $this->db->get();
		$result = $query->row();
		if ($result->count > 0){
			return true;
		}
		return false;
	}

	//Lấy danh sách thông tin gv trong bảng osp100 và teachers
	public function get_unique_gv_startcourse($username_type, $date_start) {
		$this->db->distinct();
		$this->db->select($this->table . '.' . $username_type . ', teachers.id, teachers.username, teachers.lastname, teachers.firstname, teachers.email, teachers.email_person, teachers.mobile_number, teachers.role_name');
		$this->db->from($this->table);
		$this->db->join('teachers', 'teachers.username = osp100.' . $username_type);
		$this->db->where('osp100.ngay_bat_dau', $date_start);
		$query = $this->db->get();
		return $query->result();
	}

	//Lấy danh sách thông tin gv để hiển thị lên màn hình
	public function get_unique_gv_semester($username_type, $plan_id) {
		$this->db->distinct();
		$this->db->select($this->table . '.' . $username_type . ' as username');
		$this->db->from($this->table);
		$this->db->where('plan_id', $plan_id);
		$query = $this->db->get();
		return $query->result();
	}

	//lấy danh sách thông tin gv có status_mail là success || err để gửi
	public function get_gv_semester($username_type, $plan_id) {
		$this->db->distinct();
		$this->db->select($username_type . ' as username, teachers.id, teachers.firstname, teachers.lastname, teachers.email, teachers.email_person, teachers.role_name');
		$this->db->from($this->table);
		$this->db->join('teachers', 'teachers.username = '. $username_type);
		$this->db->where('plan_id', $plan_id);
		if($username_type == 'username_gvcm') {
			$where = "(`status_mail_gvcm` = 'N' OR `status_mail_gvcm` = 'E')";
			$this->db->where($where);
		} else {
			$where = "(`status_mail_gvhd` = 'N' OR `status_mail_gvhd` = 'E')";
			$this->db->where($where);
		}
		$query = $this->db->get();
		return $query->result();
	}

	//lấy danh sách thông tin trong osp100 của 1 username
	public function get_info_gv_osp100_semester($username, $id_semester) {
		$where = '(`username_gvcm` = ' . "'" . $username . "'" . ' OR `username_gvhd` = ' . "'" . $username . "'" .')';
		$this->db->from($this->table);
		$this->db->where($where);
		$this->db->where('plan_id', $id_semester);
		$this->db->where('status', 'Y');
		$query = $this->db->get();
		return $query->result();
	}

	//update status_mail cho username gv
	public function update_status_mail($role_name, $username, $status, $plan_id) {
		if($role_name == 'GVCM') {
			$this->db->set('status_mail_gvcm', $status);
			$this->db->where('username_gvcm', $username);
		} else {
			$this->db->set('status_mail_gvhd', $status);
			$this->db->where('username_gvhd', $username);
		}
		$this->db->where('plan_id', $plan_id);
		$this->db->update($this->table);
	}

	//lấy số lượng mail gửi đi success || err trong 1 quý
	public function get_success_or_err($plan_id, $username_type, $status) {
		$this->db->select('count(distinct(' . $username_type . ')) as so_luong_gv');
		$this->db->from($this->table);
		$this->db->where('plan_id', $plan_id);
		$username_type == 'username_gvcm' ? $this->db->where('status_mail_gvcm', $status) : $this->db->where('status_mail_gvhd', $status);
		$query = $this->db->get();
		$result = $query->row();
		
		return $result->so_luong_gv;
	}

	//lấy chi tiết thông tin giảng viên để hiển thị lên màn hình
	public function get_detail_gv($username_gv, $username_type, $plan_id) {
		if($username_type == 'username_gvcm') {
			$this->db->select('count(*) as so_course, teachers.firstname, teachers.lastname, teachers.username, teachers.email, osp100.status_mail_gvcm as status_mail');
		} else {
			$this->db->select('count(*) as so_course, teachers.firstname, teachers.lastname, teachers.username, teachers.email, osp100.status_mail_gvhd as status_mail');
		}
		$this->db->from($this->table);
		$this->db->join('teachers','teachers.username = '. $username_type);
		$this->db->where('osp100.plan_id', $plan_id);
		$this->db->group_by($username_type);
		$this->db->having('osp100.' . $username_type .' = ' . "'" . $username_gv . "'");
		$query = $this->db->get();
		return $query->result();
	}
	
	public function get_list_gv_osp100_startcourse($username_type, $username, $date_start) {
		$this->db->from($this->table);
		$this->db->where('ngay_bat_dau', $date_start);
		$this->db->where('status', 'Y');
		$this->db->where($username_type, $username);
		$query = $this->db->get();
		return $query->result();
	}

	public function get_ten_lop($ten_course) {
		$this->db->from($this->table);
		$this->db->where('ten_course', $ten_course);
		$query = $this->db->get();
		$result = $query->row();
		if($result) {
			return $result;
		}
		return null;
	}
}