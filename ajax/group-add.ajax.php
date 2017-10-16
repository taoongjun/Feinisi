<?php 
  header( 'Content-type:text/html; charset=utf-8' );
  include( '../config.php' );   //配置信息页面
  $checkResult = $dbLocal->get_var("select GROUP_ID FROM MT4_NEW_GROUP WHERE `GROUP`='{$_POST['GROUP']}'");
  if( $checkResult )
  {
    echo 0;
  }else
  {
    echo 1;
  }