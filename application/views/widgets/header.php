<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php if($this->headTitle){echo $this->headTitle;}else{echo '天地君道培训官网-国内最专业的游戏开发在线教育平台';}?></title>
<base href="<?= base_url() ?>">
<link rel="Shortcut Icon" href="favicon.ico">
<link rel="stylesheet" href="static/css/global.css">
<link rel="stylesheet" href="static/lib/fa/css/font-awesome.min.css">
    <link href="static/login/css/style.css" rel="stylesheet" type="text/css" />
<meta name="keywords" content="<?php if($this->headKeyWords){echo $this->headKeyWords;}else{echo '天地君道培训,在线教育,游戏开发培训,VR游戏开发,AR游戏开发,手机游戏开发培训,游戏编程培训,unity5,cocos2dx,android,ios,flash,java,html5';}?>">
<meta name="description" content="<?php if($this->headDesc){echo $this->headDesc;}else{echo '天地君道培训是国内最专业的游戏开发在线教育平台。天地君道培训提供了丰富的适用于零基础学习游戏开发及IT职业技能的在线直播课程。课程内容涵盖多个热门技术方向，例如Unity3D、Cocos2dx、Android、iOS、HTML5等。天地君道培训同时推出的秒答、在线课堂、学习印记和精英汇让编程学习更轻松，学完就业有保障。用编程实现梦想！';}?>">
<meta property="qc:admins" content="2771270563641164105105663757" />
<meta property="wb:webmaster" content="70045023058016ff" />
<meta name="baidu-site-verification" content="IbjMD7HW0P" />
    <?php
    switch($this->agent->browser()) {
        case 'Opera':
        case 'Chrome':
        case 'Firefox':
        case 'Safari':
            echo '<script src="static/lib/jquery/jquery-2.1.4.min.js"></script>';
            break;
        default:
            echo '<script src="static/lib/jquery/jquery-1.11.3.min.js"></script>';
            break;
    }
    ?>
    <script src="/static/lib/ajaxupload.js" type="text/javascript"></script>
    <script src="http://qzonestyle.gtimg.cn/qzone/openapi/qc_loader.js" data-appid="101242237" data-redirecturi="http://www.91miaoda.com/qq_cb" charset="utf-8"></script>
    <script src="http://tjs.sjs.sinajs.cn/open/api/js/wb.js?appkey=665902895" type="text/javascript" charset="utf-8"></script>
    <script src="http://res.wx.qq.com/connect/zh_CN/htmledition/js/wxLogin.js"></script>