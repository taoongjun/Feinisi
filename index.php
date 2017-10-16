<?php 
session_start();
header('Content-type:text/html; charset=utf-8');
include( 'config.php' );   //配置信息页面
include( "action.php" );//动作控制页面?>
<!DOCTYPE html>
<html lang="zh-CN" style="font-size: 62.5%;">
<head>
  <link rel="shortcut icon" href="/favicon.png">
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <meta name="renderer" content="webkit">
  <!-- js -->
  <script src="<?=$url?>lib/js/jquery.min.js"></script>
  <script src="<?=$url?>lib/js/bootstrap.min.js"></script>
  <script src="<?=$url?>lib/js/function.js" type="text/javascript" charset="utf-8"></script>  
  <!-- css -->
  <link rel="stylesheet" href="<?=$url?>lib/css/bootstrap.min.css"> 
  <!-- 自定义的css设计 -->
  <link rel="stylesheet" href="<?=$url?>/lib/css/index.css">
  <style>
    body { position: relative; }
    #login { padding-top: 30px; }
  </style>
  <title><?=$titleName?$titleName:''?></title>
</head>
<body>
<?php
//跳转的页面。提示错误和成功信息
if ($_GET['action'] && $_GET['rst']) {
  include 'success.php';

 //session值不完善,没有动作的话，就进行登录
} elseif((!$_SESSION['login'] || !$_SESSION['password']) && $_GET['action']!='obtain-password') { 
  include('login.php');

//session值不完善，又有动作，获取密码
} elseif ((!$_SESSION['login'] || !$_SESSION['password']) && $_GET['action']=='obtain-password') {
  include('obtain-password.php');

//登录之后
//session值完善，根据action进行页面包含
} elseif ($_SESSION['login'] && $_SESSION['password']) {
  //菜单栏
  include('menu.php');
  //一、首页
  ////默认进入首页
  if (!$_GET['action'] || $_GET['action'] == 'obtain-password') {
    include('index-list.php');

  //二、用户管理
  } elseif ($_GET['action'] == 'user-managenent') {
    //分组列表
    if ( $_GET['do'] == 'group-list' ) {
      include("group-list.php");

    //用户列表
    } elseif ($_GET['do'] == 'user-list') {
      include("user-list.php");

    //添加分组
    } elseif ($_GET['do'] == 'group-add') {
      include("group-add.php");
    }

  //交易报表
  } elseif ($_GET['action'] == 'trade-list') {
    include("trade-list.php");

  //交易详情表
  } elseif ($_GET['action'] == 'trade-detail') { 
    include("trade-detail.php");

  //佣金报表
  } elseif ($_GET['action'] == 'commission-list') {
    include('commission-list.php');

  //查询佣金明细
  } elseif ($_GET['action'] == 'commission-detail') {
    include('commission-detail.php');

  //管理员登录才能看到
  } elseif ($_SESSION['login'] == 'admin' && $_GET['action'] == 'profit-list') {
    include('profit-list.php');

  //生成树状列表
  } elseif ($_SESSION['login'] == 'admin' && $_GET['action'] == 'tree-map') {
    include('tree-map.php');
  } 
}
?>
</body>
</html>