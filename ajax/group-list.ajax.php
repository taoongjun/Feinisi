<?php
  header( 'Content-type:text/html; charset=utf-8' );
  include( '../config.php' );   //配置信息页面
  if( $_POST['GROUP_ID'] )
  {
    $groupInfo = $dbLocal->get_results("SELECT `GROUP_ID`,`GROUP`,`GENRE`,`RATIO`,`REWARD` FROM MT4_NEW_GROUP WHERE GROUP_ID = {$_POST['GROUP_ID']}",ARRAY_A);
    $groupInfo_json = json_encode($groupInfo);
    echo $groupInfo_json;
    unset($groupInfo);
  }
  //删除分组
  else if( $_POST['groupIdforDelete'] )
  {
    $queryUserByGroup = $dbLocal->get_col("SELECT LOGIN FROM MT4_NEW_USERS WHERE GROUP_ID={$_POST['groupIdforDelete']}");//所有该分组下的用户必须立即删除
    if( $queryUserByGroup )//若用户列表中存在采用相应分组的用户
    {
      $deleteUseRst = $dbLocal->query("DELETE FROM MT4_NEW_USERS WHERE GROUP_ID={$_POST['groupIdforDelete']}");
      if( $deleteUseRst )
      {
        $deleteRst = $dbLocal->query("DELETE FROM MT4_NEW_GROUP WHERE GROUP_ID={$_POST['groupIdforDelete']}");
      }
    }else
    {
      $deleteRst = $dbLocal->query("DELETE FROM MT4_NEW_GROUP WHERE GROUP_ID={$_POST['groupIdforDelete']}");
    }
    echo $deleteRst;
  }
