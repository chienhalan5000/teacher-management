<?php 
    Class Report extends CI_controller {
        public function __construct() {
			parent::__construct();
			$this->load->model('log_email_sms_m');
		}
		
		public function index()
		{
			check_access();
			$this->load->helper('url');
			$title['title'] = 'Báo cáo';
			$active['active_bao_cao'] = 'class="active"';
			$form = '';
			if($this->input->post('find') == "find") {
				$status = true;
				$from_date = $this->input->post('from_date');
				$to_date = $this->input->post('to_date');
				if($from_date == '') {
					$form['err_from_date'] = "Bạn chưa chọn ngày bắt đầu";
					$status = false;
				}
				if($to_date == '') {
					$form['err_to_date'] = "Bạn chưa chọn ngày kết thúc";
					$status = false;
				}
				if($to_date < $from_date) {
					$form['err'] = "Ngày kết thúc phải lớn hơn ngày bắt đầu";
					$status = false;
				}
				if($status == true){
					$form['from_date'] = $from_date;
					$form['to_date'] = $to_date;
					$form['tu_khoa'] = $this->input->post('tu_khoa');
					$form['list_log_email_sms'] = $this->log_email_sms_m->get_data($this->input->post('typeTemplate'), $from_date, $to_date, $this->input->post('tu_khoa'));

				}
			}

			$this->load->view('header', $title);
			$this->load->view('top');
			$this->load->view('menu_left', $active);
			$this->load->view('report_view', $form);
			$this->load->view('footer');
		}

		public function detail($id)
		{
			check_access();
			$data = $this->log_email_sms_m->get_by_id($id);
			echo $data->noi_dung;
		}

    }
?>