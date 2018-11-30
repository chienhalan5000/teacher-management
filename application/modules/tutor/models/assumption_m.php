<?php

	/**
	 * Created by PhpStorm.
	 * User: danglx
	 * Email: lexuandang89@gmail.com
	 * Date: 3/23/17
	 * Time: 12:59 AM
	 */
	class Assumption_m extends MY_Model
	{
		var $table = 'dictionaries';

		public function get_by_categories_code($ma_loai = '')
		{
			$this->db->select($this->table . '.id,' . $this->table . '.name,' . $this->table . '.code');
			$this->db->from($this->table);
			$this->db->join('categories', 'categories.id = ' . $this->table . '.categories_id');
			$this->db->where('categories.code', $ma_loai);
			$this->db->where($this->table . '.enable_yn', 'Y');
			$this->db->order_by($this->table . '.sort', 'ASC');
			$query       = $this->db->get();
			$tu_dien_Arr = $query->result();

			return $tu_dien_Arr;
		}

		public function get_by_code($ma_tu_dien)
		{
			$this->db->from($this->table);
			$this->db->where('code', $ma_tu_dien);
			$query = $this->db->get();

			return $query->row();
		}

	}