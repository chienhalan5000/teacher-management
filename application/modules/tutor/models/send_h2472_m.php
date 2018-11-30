<?php

	/**
	 * Created by PhpStorm.
	 * User: danglx
	 * Email: lexuandang89@gmail.com
	 * Date: 3/23/17
	 * Time: 12:59 AM
	 */
	class Send_h2472_m extends MY_Model
	{
		var $table = 'send_h2472';

		/**
		 * Lay danh sach GV can tra loi H2472
		 * @return mixed
		 */
		public function get_teacher_h2472()
		{
			$this->db->select('teachers.id teacher_id,teachers.username,teachers.lastname,teachers.firstname,teachers.email,teachers.email_person,teachers.mobile_number,teachers.role_name');
			$this->db->from('teachers');
			$this->db->where('username in (SELECT h.teacher_username FROM send_h2472 h WHERE DATE(h.created_at) = DATE(NOW()) AND sent_yn="N")');
			$query       = $this->db->get();
			$teacher_Arr = $query->result();

			return $teacher_Arr;
		}

		/**
		 * Lay DS cau hoi H2472 theo username GVs
		 * @param string $username_gv
		 *
		 * @return mixed
		 */
		public function get_h2472_by_teacher($username_gv='')
		{
			$this->db->select('id gdid,
								system,
								tro_giang_id,
								id gdid,
								thread_id,
								answer_id,
								thread_name,
								id_course_lms,
								course_name,
								thoi_gian_hoi,
								student_name,
								delay,
								userid_teacher_lms,
								teacher_username');
			$this->db->from($this->table);
			$this->db->where('teacher_username', $username_gv);
			$this->db->where('sent_yn', 'N');
			$this->db->where('DATE(created_at) = DATE(NOW())');

			$query       = $this->db->get();
			$teacher_Arr = $query->result();

			return $teacher_Arr;
		}
	}