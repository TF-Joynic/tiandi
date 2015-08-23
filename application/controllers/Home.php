<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model("user_model");
		$this->load->model("problem_model");
		$this->load->model("tag_model");
		$this->me = $this->user_model->check_login();
	}

	public function index(){
		$id = !isset($_GET['uid']) ? false : $this->input->get("uid");
		if($id == false) show_404();

		$user_data = $this->user_model->get_user($id);
		if($user_data == false) show_404();


		// 决定给用户展示什么页面
		$user_type = $user_data['type'] == 2 ? 0 : $user_data['type'];
		if($user_type > 2 || $user_type < 0) show_404();
		$user_type = $user_type == 1 && !isset($this->me['id']) ? 2 : $user_type;


		// 构造数据准备传递
		$push_data = $this->me;
		$push_data['user'] = $user_data;
		$push_data['love'] = isset($_GET['love']) ? true : false;
		$push_data['owner'] = isset($_GET['owner']) ? true : false;
		$push_data['follow_type'] = false;
		$push_data["page"] = !isset($_GET['page']) ? "1" : $this->input->get("page");

		if($user_type == 0){
			if($push_data['love']){
				$push_data['follow_type'] = true;
				$problem_list = array();
				foreach (json_decode($push_data['user']['follow_users']) as $key => $value) {
					array_push($problem_list , $this->user_model->get(array("id" => $value[0])));
				}
				$owner_list_count = count(json_decode($push_data['user']['follow_users']));
				$push_data['hot'] = "&love=love";
			}
			if($push_data['owner']){
				$problem_list = $this->problem_model->handle_tag($this->problem_model->get_list(array("owner_id" => $id) , $push_data['page'] - 1 , 20));
				$owner_list_count = $this->problem_model->get_count(array("owner_id" => $id));
				$push_data['hot'] = "&owner=owner";
			}
			if(!$push_data['love'] && !$push_data['owner'] ){
				$problem_list = $this->problem_model->get_collect($push_data['user']['collect_problems']);
				$owner_list_count = count($push_data['user']['collect_problems']);
				$push_data['hot'] = "";
			}
			$push_data['problem_list'] = $problem_list;
			$push_data['owner_list_count'] = $owner_list_count;
		}
		if($user_type == 1){
			$push_data["recommend_list"] = $this->problem_model->get_list_by_hot(0, 5 , "random","hot",false);
			$push_data["hot_type"] = isset($_GET['ok']) ? true : false;
			if(!$push_data["hot_type"]){
				$push_data["news_problem"] = $this->problem_model->get_list_by_time($push_data["page"]-1, 5);
				$push_data["problem_list_count"] = $this->problem_model->get_list_count();
			}else{
				$push_data["news_problem"] = $this->problem_model->get_answer($uid , $push_data["page"] , 5);
				$push_data["problem_list_count"] = $this->problem_model->answer_count($uid);
			}
		}
		if($user_type == 2){
			$push_data["answer"] = $this->problem_model->get_answer($id , $push_data["page"] , 10);
			$push_data["answer_count"] = $this->problem_model->answer_count($id);
		}



		switch ($user_type) {
			case 0:$file_name = "studentHome.php";break;
			case 1:$file_name = "god/home.php";break;
			case 2:$file_name = "god/show.php";break;
		}
		$this->load->view("miaoda/" . $file_name , $push_data);
	}

}
