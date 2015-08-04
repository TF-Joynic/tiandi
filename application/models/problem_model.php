<?php if (!defined('BASEPATH')) exit('No direct script access allowed');include_once(APPPATH . 'models/base_model.php');class problem_model extends base_model {	public function __construct() {		parent::__construct();		$this->load->model('tag_model');		$this->table_name = 'problem';	}	// pid, uid	public function request($params) {		extract($params);		$this->db->where(array(			'id' => $pid,			'type' => 0		))->update($this->table_name, array(			'answer_id' => $uid,			'type' => 1		));		return $this->db->affected_rows() > 0;	}	public function done($pid) {		$this->db->where(array(			'id' => $pid,			'type' => 1		))->update($this->table_name, array(			'type' => 2		));		return $this->db->affected_rows() > 0;	}	public function close($pid) {		$this->db->where(array(			'id' => $pid,			'type' => 2		))->update($this->table_name, array(			'type' => 3		));		return $this->db->affected_rows() > 0;	}	public function get_list_by_id($id) {		$list = parent::get(array("id" => $id));		foreach($list as $item) {			@$item['tags'] = $this->tag_model->get_list_by_json($item['json']);		}		$list = count($list) <= 0 ? false : $list;		return $list;	}	public function get_list_count(){		return $this->db->count_all_results($this->table_name);	}	public function get_list_by_time($page = 0, $count = 20) {		$list = parent::get_list(array(), $page, $count);		$index = 0;		foreach($list as $item) {			$list[$index]['ctime'] = date("H:i:s",strtotime($item['ctime']));			@$list[$index]['tags'] = $this->tag_model->get_list_by_json($item['json']);			$index ++;		}		return $list;	}	public function get_list_by_hot($page = 0, $count = 20) {		$list = $this->db->order_by('hot', 'DESC')->limit($count, $page * $count)->get($this->table_name)->result_array();		$index = 0;		foreach($list as $item) {			$list[$index]['ctime'] = date("H:i:s",strtotime($item['ctime']));			@$list[$index]['tags'] = $this->tag_model->get_list_by_json($item['json']);			$index ++;		}		return $list;	}	public function follow($pid) {		$this->db->where('id', $pid)->update($this->table_name, array(			'follow_count' => 'follow_count + 1',			'hot' => 'hot + 3'		));	}	public function unfollow($pid) {		$this->db->where('id', $pid)->update($this->table_name, array(			'follow_count' => 'follow_count - 1',			'hot' => 'hot - 3'		));	}	public function collect($pid) {		$this->db->where('id', $pid)->update($this->table_name, array(			'collect_count' => 'collect_count + 1',			'hot' => 'hot + 3'		));	}	public function uncollect() {		$this->db->where('id', $pid)->update($this->table_name, array(			'collect_count' => 'collect_count - 1',			'hot' => 'hot - 3'		));	}	public function get_problem($pid){		$data = $this->db->select(array("up_count" , "hot" ,"up_users"))->get_where($this->table_name , array("id" => $pid) , 0,1)->result_array();		return $data;	}	public function up($array) {		$this->db->where('id', $array['id'])->update($this->table_name, array(			'up_count' => $array["up_count"],			'hot' =>  $array["hot"],			'up_users' =>   $array["up_users"]		));		return $this->db->affected_rows() > 0;	}	public function down($pid) {		$is_down = false;		$data = $this->db->select(array("up_count" , "hot" ,"up_users"))->get_where($this->table_name , array("id" => $pid) , 0,1)->result_array();		$up_users = json_decode($data[0]['up_users']);		$temp = array();		foreach ($up_users as $key => $value) {			if($value->id != $this->me['id']){				array_push($temp, array("id"=>$value->id));			}else{				$is_down = true;			}		}		$up_users = $temp;		if($is_down == false){ return false;}		$this->db->where('id', $pid)->update($this->table_name, array(			'up_count' => $data[0]['up_count'] - 1,			'hot' => $data[0]['hot'] - 5,			'up_users ' =>  json_encode($up_users)		));		return $this->db->affected_rows() > 0;	}}