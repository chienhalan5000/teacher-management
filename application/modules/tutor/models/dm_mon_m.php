<?php

/**
 * Created by PhpStorm.
 * User: danglx
 * Email: lexuandang89@gmail.com
 * Date: 3/23/17
 * Time: 12:59 AM
 */
class Dm_mon_m extends MY_Model
{
	var $table = 'dm_mon';
	/**
	 * Lay thong tin
	 *
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function get_by_ma_mon($ma_mon)
	{
		$this->db->from($this->table);
		$this->db->where('ma_mon', $ma_mon);
		$query = $this->db->get();

		return $query->row();
	}
}