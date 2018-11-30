<?php

/**
 * Created by PhpStorm.
 * User: danglx
 * Email: lexuandang89@gmail.com
 * Date: 3/23/17
 * Time: 12:59 AM
 */
class Log_email_sms_m extends MY_Model
{
	var $table = 'log_email_sms';

	public function get_data($msg_type = '', $from_date, $to_date, $key = '') {
		$this->db->from($this->table);
		$this->db->where('msg_type', $msg_type);
		$this->db->where('DATE(created_at) >=', $from_date);
		$this->db->where('DATE(created_at) <=', $to_date);
		$this->db->where('noi_dung LIKE', '%' . $key . '%');
		$query = $this->db->get();
		return $query->result();
	}
}