<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

include_once(APPPATH . 'controllers/api/Base_api.php');

class Problem_api extends Base_api {

    public function __construct() {
        parent::__construct();
        $this->table_name="problem_detail";
        $this->load->model('problem_model');
        $this->load->model('problem_detail_model');
        $this->load->model('problem_comment_model');
        $this->load->model('tag_model');
        $this->load->model('news_model');

        $this->me = $this->user_model->check_login();
    }

    public function get_info() {
        parent::require_login();
        $params = $this->get_params('GET' , array("problem_id"));
        extract($params);

        $problem = $this->problem_model->get(array("id" => $problem_id) , ("type"));
        if (!isset($problem['type'])) {
            parent::finish(false , "您尝试着获取不存在的问题");
        }
        parent::finish(true, '', $problem);
    }

    public function remove() {
        $this->load->model('admin_model');
        $this->me = $this->admin_model->check_login();
        parent::require_login();
        $params = $this->get_params('POST', array('id'));
        extract($params);

        if (!$this->problem_model->is_exist(array(
            'id' => $id
        ))) {
            $this->finish(false, '不存在的 id');
        }

        $problem_data = $this->problem_model->get(array("id" => $id));
        if(isset($problem_data['tags']) && $problem_data['tags'] != "[]") {
            $tags = json_decode($problem_data['tags'] , true);
            foreach (empty($tags) ? array() : $tags as $value) {
                $this->tag_model->edit_tag_problem_count($value['t'] , 1 , "-");
            }
        }

        $this->problem_model->remove($id);
        $this->problem_detail_model->remove_where(array("problem_id" => $id));
        $this->problem_comment_model->remove_where(array("problem_id" => $id));
        $this->finish(true);
    }

    public function online_save(){
        $params = $this->get_params('POST', array('type' , "title" , "tags" , "language" , "problem_id"));
        extract($params);
        $code = $_POST['code'];
        $content = $_POST['content'];

        if($type == "ask"){
            // 提问在线保存处理
            $params['content'] = $content;
            $params['code'] = $code;
            $this->session->problem_temp = $params;
        } else {
            // 回答在线保存处理
            parent::require_login();
            $problem = $this->problem_model->get(array("id"=>$problem_id));
            if($this->me['id'] == $problem['answer_id']){
                $result = array(
                    "content" => $content,
                    "type" => 3,
                    "owner_id" => "-1",
                    "ctime" => time(),
                    "problem_id" => $problem_id,
                    "code" => $code,
                    "language" => $language,
                );
                if($this->problem_detail_model->is_exist(array("problem_id" => $problem_id , "type" => 3))){
                    $this->problem_detail_model->edit_array(array("problem_id" => $problem_id, "type" => 3) , $result);
                }else{
                    $this->problem_detail_model->create($result);
                }
            }else{
                $this->finish(false, "异常");
            }
        }
        $this->finish(true);
    }

    /**
     * [chou 用户众筹]
     * 自己可以众筹自己的问题，每众筹一次获得50积分
     * 没有对每个用户每天众筹多少次做限制
     * @param [1、不存在，2、已经参加国，3、银币不足，4、插入异常会返回扣除的积分]
     */
    public function chou(){
        parent::require_login();$params = $this->get_params('POST', array('problem_id'));extract($params);
        $problem_data = $this->problem_model->get(array("id" => $problem_id));
        if(count($problem_data) <= 0) {
            $this->finish(false, '无法完成您的请求，您要尝试操作的是一个不存在的问题');
        }
        if($this->me['id'] === $problem_data['owner_id']) parent::finish(false , "您不能对自己发起的问题进行众筹");
        $problem_json = json_decode($problem_data['who']);
        foreach ($problem_json as $key => $value) {
            if($value==$this->me['id']){
                $this->finish(false, '您已经参加过该问题的众筹了。');
            }
        }
        $problem_json[] = $this->me['id'];
        $problem_json = json_encode($problem_json);
        if(false === $this->user_model->coin($this->me['id'] , 50 , false)){
            $this->finish(false, '您的银币不足，无法完成众筹');
        }
        if(false === $this->problem_model->edit($problem_id , array("silver_coin" => $problem_data['silver_coin'] + 50,"who" => $problem_json))){
            $this->user_model->coin($this->me['id'] , 50);
            $this->finish(false, '无法众筹！');
        }else{
            $this->user_model->add_chou($problem_id);
            $this->news_model->create(array(
                'target' => $this->me['id'],
                'type' => '300',
                'problem_id' => $problem_id
            ));

            $this->user_model->Integral($this->me['id'] , 50);
            $this->finish(true);
        }
    }

    public function create() {
        parent::require_login();
        $params = $this->get_params('POST', array(
            'coinType',
            'code',
            'tags',
            "language"
        ));
        extract($params);
        $content = $_POST['content'];
        $title = $_POST['title'];

        if($this->problem_model->is_exist(array('title' => $title))) {
            $this->finish(false, '您的问题已经有人问过了，请不要再次提问咯！');
        }

        $language_list = array(
            '0' => 'html',
            '1' => 'php',
            '2' => 'c++',
            '3' => 'javascript',
            '4' => 'java',
            '5' => 'c#',
            '6' => 'unity-3d',
            '7' => 'swift',
            '8' => 'web',
            '9' => 'cocos2d-x',
            '10' => 'android',
            '11' => 'lua',
            '12' => 'css',
            '13' => 'objective-c',
            '14' => '其他',
        );
        $language = $language_list[$language];

        $is_length = parent::is_length(array(
            array("name" => "标题" , "value" => $title, "min" => 6,"max" => 64),
            array("name" => "描述" , "value" => $content, "min" => 12),
        ));

        $_SESSION['first'] = true;
        $_SESSION['problem_temp'] = array('type'=>"", "title"=>"","content"=>"","tags"=>"[]","code"=>"" , "language" => 0 , "problem_id");

        // 判断 tag
        $tagArray = json_decode($tags);
        if(count($tagArray) <= 0) parent::finish(false , "您必须输入一个标签才能发表问题！");

        // 处理硬币需求
        $coinConfig = $coinType == "true" ? array(
            "name" => "金币",
            "type" =>"gold_coin",
            "value" => 1,
        ) : array(
            "name" => "银币",
            "type" =>"silver_coin",
            "value" => 100,
        );
        if(!$this->user_model->coin($this->me['id'] , $coinConfig['value'] , false , $coinConfig['type'])){
            $this->finish(false, '您的' . $coinConfig['name'] . '不足，所以并不能提问问题！');
        }

        // 积分需求
        $this->user_model->Integral($this->me['id'] , 100);
        $_SESSION['first'] = true;

        // 处理标签请求
        $tagTemp = array();
        if (!empty($tagArray)) {
            foreach ($tagArray as &$value) {
                $value = trim($value);
                if (preg_match("/[\'.,:;*?~`!@$%^&=)(<>{}]|\]|\[|\/|\\\|\"|\|/",$value)){
                    parent::finish(false , "标签中不能存在特殊字符，请检查后再提交！");
                }
                if (strlen($value) < 2 || strlen($value) > 20) {
                    parent::finish(false , "标签不能为空且需小于20字符");
                } else if ($this->tag_model->add_tag($this->HTML($value))) {
                    $tagTemp[] = array("t" => $value);
                    $this->tag_model->edit_tag_problem_count($value);
                    $tag_id = $this->tag_model->get(array(
                        'name' => $value
                    ))['id'];
                    $this->tag_model->add_active_user($tag_id, $this->me['id']);
                }
            }
        }

        // 创建题主
        $detail_id = $this->problem_model->create(array(
            'owner_id' => $this->me['id'],
            'title' => htmlspecialchars($title),
            'tags' => json_encode($tagTemp),
            $coinConfig['type'] => $coinConfig['value']
        ));
        if($detail_id == false){parent::finish(false , "服务器异常，请尝试重新提交问题！problem");}

        // 首位答主创建
        $this->load->helper('security');
        if(!$this->problem_detail_model->create(array(
            'owner_id' => $this->me['id'],
            'type' => 0,
            'content' =>$this->HTML(($content)),
            'code' => htmlspecialchars($code),
            'problem_id' => $detail_id,
            'language' => $language
        ))){
            parent::finish(false , "服务器异常，请尝试重新提交问题！detail");
        }

        // 清除在线保存中的问题
        $this->session->unset_userdata('problem_temp');
        $this->finish(true,$detail_id,$detail_id);
    }

    /*
        创建一条评论
        - content
        - problem_id
    */
    public function create_comment() {
        parent::require_login();
        $params = $this->get_params('POST', array('problem_id'));
        extract($params);
        $content = $_POST['content'];

        if (!$this->problem_model->is_exist(array(
            'id' => $problem_id
        ))) {
            $this->finish(false, '不存在的问题');
        }

        $new_comment_id = $this->problem_comment_model->add_comment($this->me['id'], $problem_id, $this->HTML($content));
        $problem = $this->problem_model->get(array(
            'id' => $problem_id
        ));
        $comments = json_decode($problem['comments']);
        $comments[] = $new_comment_id;
        $this->problem_model->edit($problem_id, array(
            'comments' => json_encode($comments)
        ));

        // 评论给用户积分
        ModelFactory::User()->Integral($this->me['id'] , CONSTFILE::USER_ACTION_COMMENT_PROBLEM_INTEGRAL_VALUE ,true,'Integral',CONSTFILE::CHANGE_LOG_COUNT_TYPE_CLICK_COMMENT);
        ModelFactory::User()->coin($this->me['id'] , CONSTFILE::USER_ACTION_COMMENT_PROBLEM_SILVER_COIN_VALUE ,true,'silver_coin',CONSTFILE::CHANGE_LOG_COUNT_TYPE_CLICK_COMMENT);

        // 每次评论可获得一点热度
        $this->problem_model->hot($problem_id , 1 , true);

        // 添加新消息
        if ($problem['owner_id'] != $this->me['id']) {
            $this->news_model->create(array(
                'target' => $problem['owner_id'],
                'type' => '202',
                'problem_id' => $problem_id,
                'from_id' => $this->me['id']
            ));
        }
        if (!empty($problem['answer_id']) && $problem['answer_id'] != $this->me['id']) {
            $this->news_model->create(array(
                'target' => $problem['answer_id'],
                'type' => '202',
                'problem_id' => $problem_id,
                'from_id' => $this->me['id']
            ));
        }
        $this->finish(true);
    }


    /**
     * 创建一个新的回答   [已重构]
     * @param content
     * @param type
     * @param problem_id
     * @param language
     * @return bool
     */
    public function create_detail() {
        parent::require_login();
        $params = $this->get_params('POST', array(
            'type' => true,
            'problem_id' => true,
            'language'
        ));
        extract($params);
        $content = $_POST['content'];
        $code = $_POST["code"];

        switch ($language) {
            case '0':$language = "html";break;
            case '1':$language = "php";break;
            case '2':$language = "c";break;
            case '3':$language = "javascript";break;
            case '4':$language = "java";break;
            default: $language = "php";break;
        }

        parent::is_length(array(
            array("name" => "解答描述" , "value" => $content , "min" => 6)
        ));

        $problem = $this->problem_model->get(array( 'id' => $problem_id));
        if($this->me['id'] !== $problem['answer_id'] && $this->me['type'] !== 1) $this->finish(false, '您并不是大神用户，无法回答问题');
        if(!isset($problem['id'])) $this->finish(false, '该问题不存在，请您回答其他的问题！');
        if($problem['type'] != 1)  $this->finish(false, '问题还没有被认领，无法回答问题！');


        // Problem answer time out
        if($problem["answer_time"] + 1200 < time()){
            $this->problem_model->def($problem_id);
            $this->finish(false, '问题已经过期，无法回答！');
        }

        // Create problem detail
        $this->problem_model->edit($problem_id , array("answer_time" => time()));
        $new_detail_id = $this->problem_detail_model->create(array(
            'owner_id' => $this->me['id'],
            'content' => parent::HTML($content),
            'problem_id' => $problem_id,
            'code' => htmlspecialchars($code),
            'type' => $type,
            'language' => $language
        ));

        // add tag active user
        $tags = json_decode($problem['tags']);
        foreach ($tags as $tag) {
            $tag_id = $this->tag_model->get(array(
                'name' => $tag->t
            ))['id'];
            $this->tag_model->add_active_user($tag_id, $this->me['id'], true);
        }

        //Empty problem temp data
        $this->problem_detail_model->remove_where(array("problem_id" => $problem_id , "type" => 3));

        //Close problem
        $this->problem_model->done($problem_id);



        $is_keep = ModelFactory::Usertask()->is_keep_answer($this->me['id']);
        ModelFactory::Usertask()->answer_sign($this->me['id'],date('Y-m-d'),$is_keep+1);
        $cg_value = 0;
        if ($is_keep+1 >= CONSTFILE::USER_TASK_GOD_ANSWER_QUESTION_VALUE) {
            $god_level = ModelFactory::User()->get_god_level($this->me['id']);
            $cg_value = 0;
            if ($god_level >= 2 && $god_level <= 3) {
                $cg_value = 5;
            }
            if ($god_level <= 7 && $god_level >= 4) {
                $cg_value = 8;
            }
            if ($god_level <= 10 && $god_level >= 8) {
                $cg_value = 15;
            }
            if ($cg_value) {
                try {
                    ModelFactory::Usertask()->begin();
                    $result = ModelFactory::Usertask()->answer_sign($this->me['id'],date('Y-m-d'),1);
                    if ($result) {
                        ModelFactory::User()->coin($this->me['id'],$cg_value,true,'prestige',CONSTFILE::CHANGE_LOG_COUNT_TYPE_ANSWER_30);
                    }

                } catch (Exception $e) {
                    ModelFactory::Usertask()->rollback();
                }
            }
        }
        $this->news_model->create(array(
            'target' => $problem['owner_id'],
            'type' => '201',
            'problem_id' => $problem_id,
            'from_id' => $this->me['id']
        ));
        foreach (json_decode($problem['who']) as $key => $value) {
            $this->news_model->create(array(
                'target' => $value,
                'type' => '301',
                'problem_id' => $problem_id,
                'from_id' => $this->me['id']
            ));
        }

        // 给大神结算问题报酬
//        $max_coin = (100 + count(json_decode($problem['who'])) * 50);
//        ModelFactory::User()->coin($problem['answer_id'] , $max_coin);
        $this->news_model->create(array(
            'target' => $this->me['id'],
            'type' => '402',
            'problem_id' => $problem_id,
            'from_id' => $problem['silver_coin']
        ));

        // 给大神威望
        $prestige = $problem['gold_coin'] +   $problem['silver_coin'] / 100;
        ModelFactory::User()->coin($problem['answer_id'],$prestige,true,'prestige',CONSTFILE::CHANGE_LOG_COUNT_TYPE_ANSWER_PROBLEM);
        if ($problem['gold_coin'] ) {
            ModelFactory::User()->coin($problem['answer_id'],$problem['gold_coin'] ,true,'gold_coin',CONSTFILE::CHANGE_LOG_COUNT_TYPE_ANSWER_PROBLEM);
        }
        if ($problem['silver_coin'] ) {
            ModelFactory::User()->coin($problem['answer_id'],$problem['silver_coin'] ,true,'silver_coin',CONSTFILE::CHANGE_LOG_COUNT_TYPE_ANSWER_PROBLEM);
        }
        if($this->problem_model->close(array(
            'pid' => $problem_id
        )) === false) {
            $this->finish(false, '服务器异常，请尝试重新提交请求！');
        }
        $this->finish(true);
    }


    public function request_problem() {
        parent::require_login();$params = $this->get_params('POST', array('problem_id'));extract($params);
        if ($this->me['type'] != 1) {
            $this->finish(false, '没有权限');
        }
        $this->problem_detail_model->remove_where(array(
            "problem_id" => $problem_id,
            "type" => 3
        ));
        $problem = $this->problem_model->get(array(
            'id' => $problem_id
        ));
        if($problem["owner_id"] == $this->me['id']){
            $this->finish(false, '你不能认领自己发布的问题！');
        }
        if ($this->problem_model->request(array(
            'pid' => $problem_id,
            'uid' => $this->me['id']
        )) === false) {
            $this->finish(false, '您现在不能认领该问题，这个问题已经被人认领了，或者已经完成了回答！');
        }
        $this->news_model->create(array(
            'target' => $problem['owner_id'],
            'type' => '200',
            'problem_id' => $problem_id,
            'from_id' => $this->me['id']
        ));
        $this->finish(true);
    }


    /**
     * 满意某个问题
     * @param [problem_id]
     */
    public function close_problem() {
        parent::require_login();
        $params = $this->get_params('POST', array('problem_id'));
        extract($params);

        $problem = $this->problem_model->get(array('id' => $problem_id));
        if(!isset($problem['owner_id']) || $problem['agree'] == 1) $this->finish(false, '该问题已经不存在，请刷新页面后重试！');
        if($problem['owner_id'] !== $this->me['id']) $this->finish(false, '没有权限！');
        if(!$this->problem_model->edit($problem_id , array("agree" => 1))) {
            parent::finish(false, "服务器异常，请尝试重新提交请求！");
        }

        $this->news_model->create(array(
            'target' => $problem['answer_id'],
            'type' => '400',
            'problem_id' => $problem_id,
            'from_id' => $this->me['id']
        ));

        // Up agree problem
        // $up_users = json_decode($problem['up_users']);
        // array_push($up_users , array("id" => $this->me['id']));
        // $up_users = json_encode($up_users);
        $this->problem_model->edit($problem_id , array(
            //"up_users" => $up_users,
            "hot" => $problem['hot'] + 5,
            //"up_count" => $problem['up_count'] + 1
        ));

        parent::finish(true);
    }

    public function collect_problem() {
        parent::require_login();$params = $this->get_params('POST', array('problem_id'));extract($params);
        if($this->problem_model->collect($problem_id)){
            // 取消关注则减少火力值
            $this->problem_model->hot($problem_id , 3 , true);
            ModelFactory::User()->Integral($this->me['id'] , CONSTFILE::USER_ACTION_COLLECTION_PROBLEM_INTEGRAL_VALUE ,true,CONSTFILE::CHANGE_LOG_COUNT_TYPE_CLICK_COLLECTION);
            ModelFactory::User()->coin($this->me['id'] , CONSTFILE::USER_ACTION_COLLECTION_PROBLEM_SILVER_VALUE ,true,'silver_coin',CONSTFILE::CHANGE_LOG_COUNT_TYPE_CLICK_COLLECTION);
            parent::finish(true);
        }else{
            parent::finish(false , "无法预料到的意外错误，请您稍后再试！");
        }
    }

    public function uncollect_problem() {
        parent::require_login(); $params = $this->get_params('POST', array('problem_id'));extract($params);
        if($this->problem_model->uncollect($problem_id)){
             // 取消关注则减少火力值
            $this->problem_model->hot($problem_id , 3 , false);
           ModelFactory::User()->Integral($this->me['id'] , CONSTFILE::USER_ACTION_COLLECTION_PROBLEM_INTEGRAL_VALUE,false,'Integral',CONSTFILE::CHANGE_LOG_COUNT_TYPE_CLICK_COLLECTION );
            ModelFactory::User()->coin($this->me['id'] , CONSTFILE::USER_ACTION_COLLECTION_PROBLEM_SILVER_VALUE ,false,'silver_coin',CONSTFILE::CHANGE_LOG_COUNT_TYPE_CLICK_COLLECTION);

            parent::finish(true , "");
        }else{
            parent::finish(false , "无法预料到的意外错误，请您稍后再试！");
        }


    }



    public function up_problem() {
        $up_down_type = false;
        $temp = array();
        parent::require_login();$params = $this->get_params('POST', array('problem_id'));extract($params);
        $return_data =    ModelFactory::Problem()->get_problem($problem_id);
        $up_users = json_decode($return_data[0]['up_users']);
        if ($return_data && $return_data[0]['up_count'] >= 20 &&  $return_data[0]['is_prestige'] != 1) {
            $god_level = ModelFactory::User()->get_god_level( $return_data[0]['answer_id']);

            $cg_value = 0;
            if ($god_level >= 1 && $god_level <= 2) {
                $cg_value = 5;
            }
            if ($god_level <= 7 && $god_level >= 3) {
                $cg_value = 8;
            }
            if ($god_level <= 10 && $god_level >= 8) {
                $cg_value = 15;
            }
            if ($cg_value) {

                try {
                    ModelFactory::Usertask()->begin();
                    $r2 = ModelFactory::Problem()->edit($problem_id,['is_prestige'=>1]);
                    if ($r2) {
                        ModelFactory::User()->coin( $return_data[0]['answer_id'],$cg_value,true,'prestige',CONSTFILE::CHANGE_LOG_COUNT_TYPE_ZAN);
                        ModelFactory::Usertask()->commit();
                    }else{
                        ModelFactory::Usertask()->rollback();
                    }
                } catch (Exception $e) {
                    ModelFactory::Usertask()->rollback();
                }
            }
        }

        foreach ($up_users as $key => $value) {
            if($value->id != $this->me['id']){
                array_push($temp, array("id"=>$value->id));
            }else{
                $up_down_type = true;
            }
        }
        $up_users = $temp;
        if($up_down_type == true){//取消点赞
            if($this->problem_model->up(array(
                "id" => $problem_id ,
                "up_count" => $return_data[0]["up_count"] - 1 ,
                "hot" =>$return_data[0]["hot"] - 5,
                "up_users" => json_encode($up_users)
            ))){
                // 积分需求
                ModelFactory::User()->Integral($this->me['id'] , CONSTFILE::USER_ACTION_ZAN_INTEGRAL_VALUE , false,'Integral',CONSTFILE::CHANGE_LOG_COUNT_TYPE_CLICK_ZAN);
                ModelFactory::User()->coin($this->me['id'] , CONSTFILE::USER_ACTION_ZAN_SILVER_VALUE , false,'silver_coin',CONSTFILE::CHANGE_LOG_COUNT_TYPE_CLICK_ZAN);
                $this->finish(true , "","1");
            }else{
                $this->finish(false , "未知的网络原因导致操作失败");
            }
        }else{
            array_push($up_users , array("id"=>$this->me['id']));
            if($this->problem_model->up(array(
                    "id" => $problem_id ,
                    "up_count" => $return_data[0]["up_count"] + 1 ,
                    "hot" =>$return_data[0]["hot"] + 5,
                    "up_users" => json_encode($up_users)
                ))){
                ModelFactory::User()->coin($this->me['id'] , CONSTFILE::USER_ACTION_ZAN_SILVER_VALUE , true,'silver_coin',CONSTFILE::CHANGE_LOG_COUNT_TYPE_CLICK_ZAN);

                ModelFactory::User()->Integral($this->me['id'] ,  CONSTFILE::USER_ACTION_ZAN_INTEGRAL_VALUE ,true,'Integral',CONSTFILE::CHANGE_LOG_COUNT_TYPE_CLICK_ZAN);
                    $this->finish(true , "","0");
                }else{
                    $this->finish(false , "未知的网络原因导致操作失败");
                }
            }
        }



    public function down_problem() {
        parent::require_login();
        $params = $this->get_params('POST', array('problem_id'));
        extract($params);
        if($this->problem_model->down($problem_id)){
            $this->finish(true);
        }else{
            $this->finish(false ,"可以因为某些网络原因导致操作失败，请尝试重试！");
        }
    }


    public function get_new_problems() {
        $params = $this->get_params('GET', array('page'));
        extract($params);

        parent::finish(true, '', $this->problem_model->get_list_by_time($page));
    }

    public function get_hot_problems() {
        $params = $this->get_params('GET', array('page'));
        extract($params);

        parent::finish(true, '', $this->problem_model->get_hot_list($page));
    }

    public function get_fund_problems() {
        $params = $this->get_params('GET', array('page'));
        extract($params);

        parent::finish(true, '', $this->problem_model->get_fund_list($page));
    }

    public function get_god_temp_answer() {
        $params = $this->get_params('GET', array('problem_id'));
        extract($params);

        if (!$this->problem_model->is_exist(array(
            'id' => $problem_id
        ))) {
            parent::finish(false, '问题不存在');
        }

        $res = $this->problem_detail_model->get(array("problem_id" => $problem_id, "type" => 3));
		parent::finish(true, '', $res);
    }

    public function remove_comment() {
        $this->load->model('admin_model');
        $this->me = $this->admin_model->check_login();
        parent::require_login();
        $params = $this->get_params('POST', array('comment_id'));
        extract($params);

        if (!$this->problem_comment_model->is_exist(array(
            'id' => $comment_id
        ))) {
            parent::finish(false, '评论不存在');
        }

        $res = $this->problem_comment_model->remove($comment_id);
		parent::finish(true, '', $res);
    }
}
