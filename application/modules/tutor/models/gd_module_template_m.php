<?php 
    class Gd_module_template_m extends MY_Model {
        var $table = 'gd_module_template';

        public function isExist($id_module) {
            $this->db->from($this->table);
            $this->db->where('id_module', $id_module);
            $query  = $this->db->get();
            $result = $query->row();
            if ($result)
                return $result->id;
            else return NULL;
        }
    }
?>