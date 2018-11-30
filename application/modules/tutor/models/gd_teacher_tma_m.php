<?php

/**
 * Created by PhpStorm.
 * User: danglx
 * Email: lexuandang89@gmail.com
 * Date: 3/23/17
 * Time: 12:59 AM
 */
class Gd_teacher_tma_m extends MY_Model
{
	var $table = 'gd_teacher_tma';

	/**
	 * Lay danh sach giang vien chua cham bai tap TMA
	 * @return mixed
	 */
	public function get_teacher_tma()
	{
		$this->db->select('teachers.id teacher_id,teachers.username,teachers.lastname,teachers.firstname,teachers.email,teachers.email_person,teachers.mobile_number,teachers.role_name');
		$this->db->from('teachers');
		$this->db->where('id in (SELECT teacher_id FROM gd_teacher_tma WHERE DATE(created_at) = DATE(NOW()) AND status_delete_yn="N")');
		$query       = $this->db->get();
		$teacher_Arr = $query->result();

		return $teacher_Arr;
	}

	/**
	 * Lay danh sach cac bai tap GV chua cham
	 * @param int $teacherid
	 *
	 * @return mixed
	 */
	public function get_tma_by_teacher($teacherid=0)
	{
		$this->db->from($this->table);
		$this->db->where('teacher_id',$teacherid);
//		$this->db->where('sent_yn', 'N');
		$this->db->where('DATE(created_at)=DATE(NOW())');
		$query       = $this->db->get();
		$teacher_Arr = $query->result();

		return $teacher_Arr;
	}

}