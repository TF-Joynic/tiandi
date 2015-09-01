<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Olclass extends CI_Controller {
	function __construct() {
		parent::__construct();

		$this->load->model('site_model');
		$this->load->model('slide_model');
		$this->load->model('guide_model');
		$this->load->model('course_model');
		$this->load->model('course_class_model');
	}

	public function index() {
		$userdata = $this->user_model->check_login();
		$userdata['guide_list'] = $this->guide_model->get_list();
		$userdata['type_name'] = $this->input->get('type');
		if(isset($_GET['type'])){
			switch ($userdata['type_name']) {
				case 'u3d':$type = 0;break;
				case 'Swift':$type = 1;break;
				case 'Web':$type = 2;break;
				case 'Cocos2d-x':$type = 3;break;
				case 'Android':$type = 4;break;
				default:$type = 0;$userdata['type_name'] = "u3d";break;
			}
			$userdata['types'] = $type;
		}else{
			$userdata['types'] = 0;
			$userdata['type_name'] = "u3d";
		}
		$steup_data = $this->course_model->get(array("type" => $userdata['types']));
		$userdata['class'] = $this->course_step_model->get_list(array("course_id" => $userdata['types']) , 0 , 8);
		$userdata['description'] = $steup_data['description'];
		$userdata['site'] = $this->course_model->get_site_by_json($steup_data['site']);
		$userdata['slide_list'] = $this->slide_model->get_list(1);
		$userdata['schedule_course'] = $this->site_model->get_content('001');
		$userdata['schedule_date'] = $this->site_model->get_content('002');

		$this->parser->parse('pages/olClass.php', $userdata);
	}

}
