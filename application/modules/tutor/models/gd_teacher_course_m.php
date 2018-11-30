<?php

/**
 * Created by PhpStorm.
 * User: danglx
 * Email: lexuandang89@gmail.com
 * Date: 3/23/17
 * Time: 12:59 AM
 */
class Gd_teacher_course_m extends MY_Model
{
	var $table = 'gd_teacher_course';

	/**
	 * Lay danh sach giang vien chua post bai CM
	 * @return mixed
	 */
	public function get_teacher_cm()
	{
		$this->db->distinct();
		$this->db->select($this->table.'.teacher_id,teachers.username,teachers.lastname,teachers.firstname,teachers.email,teachers.email_person,teachers.mobile_number,teachers.role_name');
		$this->db->from($this->table);
		$this->db->join('teachers', 'teachers.id = ' . $this->table . '.teacher_id');
		$this->db->where($this->table . '.post_cm', 0);
		$this->db->where($this->table . '.action', 'cm');
		$this->db->where($this->table . '.sent_yn', 'N');
		$this->db->where('DATE('.$this->table . '.created_at)=DATE(NOW())');
		$query       = $this->db->get();
		$teacher_Arr = $query->result();

		return $teacher_Arr;
	}

	/**
	 * Lay danh sach cac course GV chua post CM
	 * @param int $teacherid
	 *
	 * @return mixed
	 */
	public function get_post_cm_by_teacher($teacherid=0)
	{
		$this->db->select('g.id gdid,
							c.system,
							t.username,
							m.ma_mon,
							m.ten_mon,
							m.link_document,
							c.course_name,
							c.id_course_lms,
							c.start_date_course,
							c.end_date_course,
							c.exam_date_course,
							g.teacher_id,
							g.gvcm_fullname,
							g.gvhd_fullname,
							g.tro_giang_id,
							g.created_at');
		$this->db->from($this->table.' g');
		$this->db->join('teachers t', 't.id = g.teacher_id');
		$this->db->join('dm_course c', 'c.id = g.dm_course_id');
		$this->db->join('dm_mon m', 'm.ma_mon = c.ma_mon');
		$this->db->where('g.teacher_id',$teacherid);
		$this->db->where('g.post_cm', 0);
		$this->db->where('g.action', 'cm');
		$this->db->where('g.sent_yn', 'N');
		$this->db->where('DATE(g.created_at)=DATE(NOW())');
		$query       = $this->db->get();
		$teacher_Arr = $query->result();

		return $teacher_Arr;
	}

	/**
	 * Lay danh sach GV chua post du dinh muc tuan
	 * @return mixed
	 */
	public function get_teacher_tlm($teacher_type='GVCM')
	{
		$this->db->distinct();
		$this->db->select($this->table.'.teacher_id,teachers.username,teachers.lastname,teachers.firstname,teachers.email,teachers.email_person,teachers.mobile_number,teachers.role_name');
		$this->db->from($this->table);
		$this->db->join('teachers', 'teachers.id = ' . $this->table . '.teacher_id');
		$this->db->where('teachers.role_name', $teacher_type);
		$this->db->where($this->table . '.action', 'tlm');
		$this->db->where($this->table . '.sent_yn', 'N');
		$this->db->where('DATE('.$this->table . '.created_at)=DATE(NOW())');
		$query       = $this->db->get();
		$teacher_Arr = $query->result();

		return $teacher_Arr;
	}

	/**
	 * Lay danh sach cac course GV chua post CM
	 * @param int $teacherid
	 *
	 * @return mixed
	 */
	public function get_post_tlm_by_teacher($teacherid=0,$teacher_type='GVCM')
	{
		$this->db->select('g.id gdid,
							c.system,
							t.username,
							m.ma_mon,
							m.ten_mon,
							m.link_document,
							c.course_name,
							c.id_course_lms,
							c.start_date_course,
							c.end_date_course,
							c.exam_date_course,
							g.teacher_id,
							g.gvcm_fullname,
							g.gvhd_fullname,
							g.tro_giang_id,
							g.post_tlm,
							g.total_post,
							g.tim,
							g.week,
							g.week_start,
							g.week_end,
							g.created_at');
		$this->db->from($this->table.' g');
		$this->db->join('teachers t', 't.id = g.teacher_id');
		$this->db->join('dm_course c', 'c.id = g.dm_course_id');
		$this->db->join('dm_mon m', 'm.ma_mon = c.ma_mon');
		$this->db->where('g.teacher_id',$teacherid);
		$this->db->where('g.action', 'tlm');
		$this->db->where('g.sent_yn', 'N');

		if($teacher_type!='GVCM'){
			$this->db->where('g.post_tlm <= 2');
		}else{
			$this->db->where('g.post_tlm', 0);
		}

		$this->db->where('DATE(g.created_at)=DATE(NOW())');
		$query       = $this->db->get();
		$teacher_Arr = $query->result();

		return $teacher_Arr;
	}

}