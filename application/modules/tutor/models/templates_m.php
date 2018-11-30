<?php 
    class Templates_m extends MY_Model {
        var $table = 'template';

        public function isExist($codeTemplate) {
            $list_template = $this->get_list();
            foreach($list_template as $template) {
                if($codeTemplate == $template->ma_mau) {
                    return true;
                }
            }
            return false;
        }

        public function get_list_email() {
            $this->db->from($this->table);
            $this->db->where('loai_mau', 'email');
            $this->db->where('enable_yn', 'Y');
            $query  = $this->db->get();
            if ($query)
                return $query->result();
            else return NULL;
        }

        public function get_template_by_code_template($code_template) {
            $this->db->from($this->table);
            $this->db->where('ma_mau', $code_template);
            $this->db->where('enable_yn', 'Y');
            $query  = $this->db->get();
            if ($query)
                return $query->row();
            else return '';
        }
    }
?>