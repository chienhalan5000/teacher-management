<?php

/**
 * Created by PhpStorm.
 * User: danglx
 * Email: lexuandang89@gmail.com
 * Date: 3/23/17
 * Time: 12:59 AM
 */
class Teachers_m extends MY_Model
{
	var $table = 'teachers';

	public function isset_data_gv($username) {
		$this->db->from($this->table);
		$this->db->where('username', $username);
		$query  = $this->db->get();
		$result = $query->row();
		if ($result)
			return $result->id;
		else return NULL;
	}

	public function get_info_by_username($username = '') {
		$this->db->from($this->table);
		$this->db->where('username', $username);
		$query = $this->db->get();
		$result = $query->row();
		if($result != null) 
			return $result;
		return null;
	}

	
}