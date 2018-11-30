<?php 
    class Send_mail_tmp_m extends MY_Model {
        var $table = 'send_mail_tmp';

        public function get_district_username() {
            $this->db->distinct();
            $this->db->select('username_giang_vien');
            $this->db->from($this->table);
            $query = $this->db->get();
            return $query->result();
        }

        public function get_info_teacher($username) {
            $this->db->from($this->table);
            $this->db->where('username_giang_vien', $username);
            $this->db->where('status', 'Y');
            $query = $this->db->get();
            return $query->result();
        }
        
    }
?>