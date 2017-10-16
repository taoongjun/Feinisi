<?php 
  header( 'Content-type:text/html; charset=utf-8' );
  include( '../config.php' );   //配置信息页面

  //删除用户
  if( $_POST['loginForDel'] )
  {
    $deleteUserRst = $dbLocal->query("DELETE FROM MT4_NEW_USERS WHERE LOGIN = {$_POST['loginForDel']}");
    if( $deleteUserRst )
    {
      echo 1;
    }else
    {
      echo 0;
    }
  }
  
  //修改用户分组
  else if( $_POST['loginForAlter'] )
  {
    $groups = $dbLocal->get_results( "SELECT a.`GROUP_ID`,a.`GROUP` FROM `MT4_NEW_GROUP` AS a LEFT JOIN `MT4_NEW_USERS` as b ON a.`GROUP_ID`=b.`GROUP_ID` WHERE b.`LOGIN` = '{$_POST['loginForAlter']}'",ARRAY_N );
    $groups_json = json_encode($groups);
    echo $groups_json;
    unset($groups);//及时释放内存
  }