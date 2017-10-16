<?php 
  header( 'Content-type:text/html; charset=utf-8' );
  include( '../config.php' );   //配置信息页面
  $period = $_POST['period'];
  $topLogin = $_POST['topLogin'];
  $sessionLogin = $_POST['sessionLogin'];
  if( $period )
  {
  	$a = $dbLocal->query("DELETE FROM MT4_NEW_COM WHERE PERIOD = '{$period}'");
    if( $mem )
    {
      $mem->delete( 'allUserInfo_'.$period );
      $mem->delete( 'everAgentTeamFirTran_'.$topLogin.'_'.$period );
      $mem->delete( 'relativeAgentsInfo_'.$sessionLogin.'_'.$period );
      $mem->delete( 'allBusiness_'.$period );
      $mem->delete( 'commissionDetail_'.$period );
      $mem->delete( 'teamTranAndPro_'.$topLogin.'_'.$period );
      $mem->delete( 'adminAgentLogins_'.$period );
    }
    echo 1;
  }
  