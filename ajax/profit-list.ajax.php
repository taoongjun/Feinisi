<?php 
header( 'Content-type:text/html; charset=utf-8' );
include( '../config.php' );   //配置信息页面
  $month = $_POST['month'];
if( $month )
{
  $month = ltrim( $month, '0' );
  $month = $month>9?$month:'0'.$month;//月份，不足两位的补0
  $no_profit_month = date( 'Y-'.$month );//查询没有利润的月份
  $no_profit_login = $dbLocal->get_var( "SELECT LOGINS FROM MT4_NEW_NOPROFIT WHERE `PERIOD`='{$no_profit_month}'" );
  if( $no_profit_login )
  {
    $no_profit_logins = unserialize( $no_profit_login );//反序列化
  }else
  {
    $no_profit_logins = '';
  }
  echo $no_profit_logins;
}
//更改团队的交易信息
else if( $_POST['searchLogin'] )
{
  $searchLogin = $_POST['searchLogin'];
  $period = $_POST['period'];
  if( $mem )
  {
    $mem->delete( 'teamTranAndPro_'.$searchLogin.'_'.$period );
  }
  echo 1;
}
