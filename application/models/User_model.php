<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');include_once(APPPATH . 'models/Base_model.php');class User_model extends Base_model {	public function __construct() {		parent::__construct();		$this->table_name = 'user';		$this->id_name = 'uid';		$this->load->model("tag_model");		$this->load->model("news_model");		$this->me = $this->check_login();	}	/**	* 获得大神列表以关注人数排序	* @param	* @param	* @param	* @return	*/	public function get_god_list($params, $page, $count){		$this->db->order_by('follower_count', 'DESC');		$page = $page < 0 ? 0 : $page;		return $this->db->where($params)->limit($count, $page * $count)->get($this->table_name)->result_array();	}	/**	 * 增加金币或银币	 * @param  formUser	 * @param  number	 * @param  type true为增加false为减少	 * @param  gold_coin/silver_coin 金币与银币	 * @return bool	 */	public function coin($formUser , $number , $type = true , $coin = "silver_coin"){		if($type == false){			$coin_data = $this->db->select(array($coin))->get_where($this->table_name , array("id" => $formUser))->row_array();			if($coin_data[$coin] - $number < 0)return false;		}		$type = $type ? "+" : "-";		$this->db->query("update `" . $this->table_name ."` SET		`{$coin}` = `{$coin}` {$type} {$number}		WHERE `id` = {$formUser}");		// $coin_type = $coin == "silver_coin" ? "银币" : "金币";		// $up_down = $type ? "增加" : "减少";		// $this->news_model->add_noews($formUser , "您的{$coin_type}{$up_down}了{$number}个！");		return $this->db->affected_rows() > 0;	}	/**	 * Integral 增加或减少用户积分	 * @param [type]  $formUser [description]	 * @param [type]  $number   [description]	 * @param boolean $type     [description]	 * @return bool     [description]	 */	public function Integral($formUser , $number , $type = true , $coin = "silver_coin"){		$type = $type ? "+" : "-";		$this->db->query("update `" . $this->table_name ."` SET		`Integral` = `Integral` {$type} {$number}		WHERE `id` = {$formUser}");		return $this->db->affected_rows() > 0;	}	/**	 * [add_chou 添加一个众筹]	 * @param [type] $problem_id [description]	 * @return bool     [description]	 */	public function add_chou($problem_id){		$userData = json_decode($this->me['chou']);		array_push($userData,$problem_id);		return $this->edit($this->me['id'],array(			"chou" => json_encode($userData)		));	}	/**	 * 获得用户资料（不处理各项json）	 * @param  [type] $uid [description]	 * @return [type]      [description]	 */	public function get_user($uid){		$get_user = $this->get(array("id" => $uid));		if(!isset($get_user['avatar'])) return false;		if($get_user['avatar'] == ""){			$get_user['avatar'] = "static/image/default.jpg";		}		$get_user['skilled_tags'] = $this->tag_model->get_list_by_json($get_user['skilled_tags'] , "id");		return count($get_user) <= 0 ? false : $get_user;	}	public function get_user_list($params){		$data = $this->db->query("select * from `".$this->table_name."` where `id`='".$params['id']."' and `type`=0 or `type`=2")->row_array();		if(!isset($data[0])){			return array($data);		}else{			return $data;		}	}	public function login($username, $pwd) {		$user = $this->db->select('id, pwd, salt, type')->where('email', $username)->get($this->table_name)->row_array();		if(count($user) <= 0){			return false;		}		if (empty($user) || $user['pwd'] !== md5($pwd . $user['salt'])){			return false;		}		$this->session->set_userdata($this->id_name, $user['id']);		return array(			'type' => $user['type'],			'id' => $user['id']		);	}	public function logout() {		$this->session->unset_userdata($this->id_name);		return true;	}	public function check_login() {		$id = $this->session->userdata($this->id_name);		if (!isset($id)) return false;		$userdata = $this->get(array(			"id" => $id		));		if($userdata["avatar"] == NULL){			$userdata["avatar"] = "static/image/default.jpg";		}		$this->load->model("news_model");		$userdata['news_nuw'] = $this->news_model->get_count(array("to" => $id , "type" => 0));		return $userdata;	}	public function get_user_data($id){		$userdata = parent::get(array("id"=>$id ));		$userdata["skilled_tags"] = $this->tag_model->get_list_by_json($userdata['skilled_tags']);		if(count($userdata) <=0 )return false;		if(@$userdata["avatar"] == NULL){			@$userdata["avatar"] = "static/image/default.jpg";		}		return $userdata;	}	// email, nickname, pwd	public function create($params) {		extract($params);		if($this->is_exist(array('email' => $email))) return '该邮箱已被使用';		if($this->is_exist(array('nickname' => $nickname))) return '该昵称已被使用';		$salt = substr(uniqid(rand()), -10);		$create_id = parent::create(array(			'nickname' => $nickname,			'email' => $email,			'salt' => $salt,			'pwd' => md5($pwd . $salt)		));		$this->login($email, $pwd);    		$this->user_model->coin($create_id , 500);		$this->news_model->add_news($create_id,"欢迎您注册天地培训为表示感谢赠送您500银币，请注意查收！");		return true;	}	public function updata_pic($id){		$this->edit($id , array(			"avatar" => "./static/uploads/".$id.".jpg",		));		return true;	}	public function follow_problem($pid) {		$collect = json_decode($this->me['follow_problems']);		array_push($collect ,array("t" => $pid));		$collect = json_encode($collect);		$this->db->where('id' , $this->me['id'])->update($this->table_name , array(			"follow_problems" => $collect		));		return $this->db->affected_rows() > 0;	}	public function unfollow_problem($pid) {		$collect = $this->get_problem_json($pid , "follow_problems");		$this->db->where('id' , $this->me['id'])->update($this->table_name , array(			"follow_problems" => $collect		));		return $this->db->affected_rows() > 0;	}	// 添加收藏	public function collect_problem($pid) {		if($this->is_problem($pid) == true)return false;		$collect = json_decode($this->me['collect_problems']);		array_push($collect ,array("t" => $pid));		$collect = json_encode($collect);		$this->db->where('id' , $this->me['id'])->update($this->table_name , array(			"collect_problems" => $collect		));		return $this->db->affected_rows() > 0;	}	public function uncollect_problem($pid) {		if($this->is_problem($pid) == false)return false;		$collect = $this->get_problem_json($pid);		$this->db->where('id' , $this->me['id'])->update($this->table_name , array(			"collect_problems" => $collect		));		return $this->db->affected_rows() > 0;	}	public function get_problem_json($pid , $type = 'collect_problems' ) {		$temp_collect = array();		$collect = json_decode($this->me[$type]);		foreach ($collect as $key => $value) {			if($value->t != $pid){				array_push($temp_collect, array("t" => $value->t ));			}		}		$temp_collect = json_encode($temp_collect);		return $temp_collect == "" ? "[]" : $temp_collect;	}	public function is_problem($pid ,$type = 'collect_problems') {		$temp_collect = array();		if(!isset($this->me[$type]))return false;		$collect = json_decode($this->me[$type]);		foreach ($collect as $key => $value) {			if($value->t == $pid){				return true;			}		}		return false;	}}