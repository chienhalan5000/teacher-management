<?php

/**
 * Created by PhpStorm.
 * User: danglx
 * Email: lexuandang89@gmail.com
 * Date: 3/23/17
 * Time: 12:59 AM
 */
class Dictionaries_m extends MY_Model
{
	var $table = 'dictionaries';
	/**
	 * Lay toan bo danh sach
	 * @return mixed
	 */
	function get_all()
	{
		$this->db->from($this->table);
		$query = $this->db->get();

		return $query->result();
	}

	public function get_by_categories_code($ma_loai = '')
	{
		$this->db->select($this->table . '.id,' . $this->table . '.name,'. $this->table . '.code');
		$this->db->from($this->table);
		$this->db->join('categories', 'categories.id = ' . $this->table . '.categories_id');
		$this->db->where('categories.code', $ma_loai);
		$query = $this->db->get();
		$tu_dien_Arr = $query->result();

		return $tu_dien_Arr;
	}

	/**
	 * Lay thong tin
	 *
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function get_by_id($id)
	{
		$this->db->from($this->table);
		$this->db->where('id', $id);
		$query = $this->db->get();

		return $query->row();
	}

	/**
	 * Chi tiet tu dien
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public function get_by_IdAssumptionGroup($IdAssumptionGroup)
	{
		$this->db->from($this->table);
		$this->db->where('IdAssumptionGroup', $IdAssumptionGroup);
		$query = $this->db->get();

		return $query->result();
	}

	public function update($where, $data)
	{
		$this->db->update($this->table, $data, $where);

		return $this->db->affected_rows();
	}

	public function insert($data)
	{
		$this->db->insert($this->table, $data);

		return $this->db->insert_id();
	}

	public function delete_by_id($id)
	{
		$this->db->where('id', $id);
		$this->db->delete($this->table);
	}
}