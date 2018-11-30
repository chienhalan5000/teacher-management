<?php 
    class Plans_m extends MY_Model {
        var $table = 'plans';

        public function isExist($codePlan) {
            $list_plan = $this->get_list();
            foreach($list_plan as $plan) {
                if($codePlan == $plan->ma_dot_hoc) {
                    return true;
                }
            }
            return false;
        }

        public function get_ten_dot_hoc_by_id($id) {
            $this->db->select('ten_dot_hoc');
            $this->db->from($this->table);
            $this->db->where('id', $id);
            $query = $this->db->get();
            $result = $query->row();

            return $result->ten_dot_hoc;
        }
    }
?>