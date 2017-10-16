<?php 
  $todayStart = date('Y-m-d 00:00:00');//今天的开始时间
  $todayEnd = date('Y-m-d 23:59:59');//今天的结束时间
  //昨天的开始时间和结束时间
  $yesterdayStart = date("Y-m-d 00:00:00",strtotime("-1 day"));//昨天的开始时间
  $yesterdayEnd = date("Y-m-d 23:59:59",strtotime("-1 day"));//昨天的结束时间
  //本周的开始和结束时间（截止到今天）
  $weekStart = date('Y-m-d 00:00:00', strtotime("last sunday"));
  //本月的开始和结束时间 (截止到今天)
  $monthStart = date("Y-m-01 00:00:00");
  //管理员登录（需要计算利润，不计算佣金）
  if ($_SESSION['login'] == 'admin')
  {
    //本年月2016-07
    $period = date('Y-m');
    //使用memcache获取不计算利润的账户（ 结果为一个序列化后的字符串，在$action='profit-list'中进行设置 ）
    if ($mem)
    {
      $noProfitLogin = $mem->get('noProfitLogin_' . $period);
    }
    //memcache中没有进行存储
    if (!$noProfitLogin)
    {
      //本月不计算利润的账户
      $noProfitLogin = $dbLocal->get_var("SELECT LOGINS FROM MT4_NEW_NOPROFIT WHERE `PERIOD`='{$period}'");
      if ($mem)
      {
        $mem->set('noProfitLogin_' . $period, $noProfitLogin, 0, 0);
      }
    }
    if ($noProfitLogin)
    {
      $noProfitLogin = unserialize($noProfitLogin);  
      preg_match_all("/[^\s]+/s", $noProfitLogin, $no_profit_loginArr);
      $noProfitLogins = $no_profit_loginArr[0];
    }
    $str = '';//计算利润的账户
    if ($noProfitLogins)
    {
      foreach ($noProfitLogins as $value) 
      {
        $str .= 'AND A.LOGIN<>' . $value . ' ';//因为主表MT4_OPTIONS被命名为A表
      }
    }
    if ($mem)
    {
      $allTradeInfo = $mem->get('allTradeInfoIndex_' . $period);//首页获取所有的信息
    }
    //209开头的都是模拟账号
    if (!$allTradeInfo)
    {
      //今天的交易情况
      $todayTradeInfo = $dbNRemote->get_row("SELECT 
        COUNT(id) AS TODAY_TRADE_QUAN1,
        SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS TODAY_TRADE_QUAN2,
        SUM(`money`) AS TODAY_TRANSACTION1,
        SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TODAY_TRANSACTION2,
        SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS TODAY_PROFIT FROM mt4_binary_option_history
        WHERE update_time BETWEEN '{$todayStart}' AND '{$todayEnd}' AND status=1 AND login NOT LIKE '209%'", ARRAY_A);
      //昨天的交易情况
      $yesdayTradeInfo = $dbNRemote->get_row("SELECT 
        COUNT(id) AS YESTERDAY_TRADE_QUAN1,
        SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS YESTERDAY_TRADE_QUAN2,
        SUM(`money`) AS YESTERDAY_TRANSACTION1,
        SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS YESTERDAY_TRANSACTION2,
        SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS YESTERDAY_PROFIT FROM mt4_binary_option_history
        WHERE update_time BETWEEN '{$yesterdayStart}' AND '{$yesterdayEnd}' AND status=1 AND login NOT LIKE '209%'", ARRAY_A);
      //本周的交易情况
      $weekTradeInfo = $dbNRemote->get_row("SELECT 
        COUNT(id) AS WEEK_TRADE_QUAN1,
        SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS WEEK_TRADE_QUAN2,
        SUM(`money`) AS WEEK_TRANSACTION1,
        SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS WEEK_TRANSACTION2,
        SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS WEEK_PROFIT FROM mt4_binary_option_history
        WHERE update_time BETWEEN '{$weekStart}' AND '{$todayEnd}' AND status=1 AND login NOT LIKE '209%'", ARRAY_A);
      //本月的交易情况
      $monTradeInfo = $dbNRemote->get_row("SELECT 
        COUNT(id) AS MONTH_TRADE_QUAN1,
        SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS MONTH_TRADE_QUAN2,
        SUM(`money`) AS MONTH_TRANSACTION1,
        SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS MONTH_TRANSACTION2,
        SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS MONTH_PROFIT FROM mt4_binary_option_history
        WHERE update_time BETWEEN '{$monthStart}' AND '{$todayEnd}' AND status=1 AND login NOT LIKE '209%'", ARRAY_A);
      $allTradeInfo = array_merge($todayTradeInfo, $yesdayTradeInfo, $weekTradeInfo, $monTradeInfo);
      if ($mem)
      {
        $mem->set('allTradeInfoIndex_' . $period, $allTradeInfo, 0, 60 );//设置60秒以后失效
      }
    }
  }

  //非管理员登录（不计算利润，计算佣金,返佣比率在config页面计算$currentRatio）
  else
  {
    //查询出代理的所有下级
    if ($mem)
    {
      $allUserInfo = $mem->get('allUserInfoRealTime');
    }
    if (!$allUserInfo)
    {
      //此处并没有好办法使用缓存
      $allUserInfo = $dbNRemote->get_results("SELECT login,agent_account,name FROM mt4_account", ARRAY_A);//$validGroupStr在config.php中配置      //注意：如果有下级的用户没有被设置成代理。那会计算结果会出错
      if ($mem)
      {
        $mem->set('allUserInfoRealTime', $allUserInfo, 0, 60);//此处设置60秒的会在一定程度上减少数据库的连接
      }
    }
    global $resultlll;
    $resultlll = null;
    $allLowerLogins = queryLowerLogin($allUserInfo, $_SESSION['login']);
    $loginsStr = implode(',', $allLowerLogins);
    if ($mem)
    {
      $allTradeInfo = $mem->get('allTradeInfoIndex_' . $_SESSION['login'] . '_' . $period);
    }
    if (!$allTradeInfo)
    { 
      //今天的交易情况
      $todayTradeInfo = $dbNRemote->get_row("SELECT 
        COUNT(id) AS TODAY_TRADE_QUAN1,
        SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS TODAY_TRADE_QUAN2,
        SUM(`money`) AS TODAY_TRANSACTION1,
        SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TODAY_TRANSACTION2 FROM mt4_binary_option_history
        WHERE update_time BETWEEN '{$todayStart}' AND '{$todayEnd}' AND status=1 AND login IN($loginsStr)", ARRAY_A);
      //昨天的交易情况
      $yesdayTradeInfo = $dbNRemote->get_row("SELECT 
        COUNT(id) AS YESTERDAY_TRADE_QUAN1,
        SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS YESTERDAY_TRADE_QUAN2,
        SUM(`money`) AS YESTERDAY_TRANSACTION1,
        SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS YESTERDAY_TRANSACTION2 FROM mt4_binary_option_history
        WHERE update_time BETWEEN '{$yesterdayStart}' AND '{$yesterdayEnd}' AND status=1 AND login IN($loginsStr)", ARRAY_A);
      //本周的交易情况
      $weekTradeInfo = $dbNRemote->get_row("SELECT 
        COUNT(id) AS WEEK_TRADE_QUAN1,
        SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS WEEK_TRADE_QUAN2,
        SUM(`money`) AS WEEK_TRANSACTION1,
        SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS WEEK_TRANSACTION2 FROM mt4_binary_option_history
        WHERE update_time BETWEEN '{$weekStart}' AND '{$todayEnd}' AND status=1 AND login IN($loginsStr)", ARRAY_A);
      //本月的交易情况
      $monTradeInfo = $dbNRemote->get_row("SELECT 
        COUNT(id) AS MONTH_TRADE_QUAN1,
        SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS MONTH_TRADE_QUAN2,
        SUM(`money`) AS MONTH_TRANSACTION1,
        SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS MONTH_TRANSACTION2 FROM mt4_binary_option_history
        WHERE update_time BETWEEN '{$monthStart}' AND '{$todayEnd}' AND status=1 AND login IN($loginsStr)", ARRAY_A);
      $allTradeInfo = array_merge($todayTradeInfo, $yesdayTradeInfo, $weekTradeInfo, $monTradeInfo);
      if ($mem)
      {
        $mem->set('allTradeInfoIndex_' . $_SESSION['login'] . '_' . $period, $allTradeInfo, 0, 60);//设置成60秒
      }
    }
    if ($mem)
    {
      $commissions = $mem->get('commissionsIndex_' . $_SESSION['login'] . '_' . $period);
    }
    if (!$commissions)
    {
      //获取直接下级的去平后的累计交易金额之和*.015即为及时返佣,以及直接下级交易量。
      $todayComm = $dbNRemote->get_row("SELECT 
        COUNT(A.id) AS TODAY_TRADE_QUAN1,
        SUM(CASE A.result WHEN 3 THEN 0 ELSE 1 END) AS TODAY_TRADE_QUAN2,
        SUM(A.`money`) AS TODAY_TRANSACTION1,
        SUM(CASE A.result WHEN 3 THEN 0 ELSE A.`money` END) AS TODAY_TRANSACTION2,
        SUM(CASE A.result WHEN 3 THEN 0 ELSE A.`money`*{$currentRatio} END) AS TODAY_COMMISSION FROM mt4_binary_option_history AS A
        LEFT JOIN mt4_account AS B ON A.login = B.login WHERE A.update_time BETWEEN '{$todayStart}' AND '{$todayEnd}' AND A.status=1 AND B.agent_account = {$_SESSION['login']}", ARRAY_A);
      $yesterdayComm = $dbNRemote->get_row("SELECT 
        COUNT(A.id) AS YESTERDAY_TRADE_QUAN1,
        SUM(CASE A.result WHEN 3 THEN 0 ELSE 1 END) AS YESTERDAY_TRADE_QUAN2,
        SUM(A.`money`) AS YESTERDAY_TRANSACTION1,
        SUM(CASE A.result WHEN 3 THEN 0 ELSE A.`money` END) AS YESTERDAY_TRANSACTION2,
        SUM(CASE A.result WHEN 3 THEN 0 ELSE A.`money`*{$currentRatio} END) AS YESTERDAY_COMMISSION FROM mt4_binary_option_history AS A
        LEFT JOIN mt4_account AS B ON A.login = B.login WHERE A.update_time BETWEEN '{$yesterdayStart}' AND '{$yesterdayEnd}' AND A.status=1 AND B.agent_account = {$_SESSION['login']}", ARRAY_A);
      $weekComm = $dbNRemote->get_row("SELECT 
        COUNT(A.id) AS WEEK_TRADE_QUAN1,
        SUM(CASE A.result WHEN 3 THEN 0 ELSE 1 END) AS WEEK_TRADE_QUAN2,
        SUM(A.`money`) AS WEEK_TRANSACTION1,
        SUM(CASE A.result WHEN 3 THEN 0 ELSE A.`money` END) AS WEEK_TRANSACTION2,
        SUM(CASE A.result WHEN 3 THEN 0 ELSE A.`money`*{$currentRatio} END) AS WEEK_COMMISSION FROM mt4_binary_option_history AS A
        LEFT JOIN mt4_account AS B ON A.login = B.login WHERE A.update_time BETWEEN '{$weekStart}' AND '{$todayEnd}' AND A.status=1 AND B.agent_account = {$_SESSION['login']}", ARRAY_A);
      $monthComm = $dbNRemote->get_row("SELECT 
        COUNT(A.id) AS MONTH_TRADE_QUAN1,
        SUM(CASE A.result WHEN 3 THEN 0 ELSE 1 END) AS MONTH_TRADE_QUAN2,
        SUM(A.`money`) AS MONTH_TRANSACTION1,
        SUM(CASE A.result WHEN 3 THEN 0 ELSE A.`money` END) AS MONTH_TRANSACTION2,
        SUM(CASE A.result WHEN 3 THEN 0 ELSE A.`money`*{$currentRatio} END) AS MONTH_COMMISSION FROM mt4_binary_option_history AS A
        LEFT JOIN mt4_account AS B ON A.login = B.login WHERE A.update_time BETWEEN '{$monthStart}' AND '{$todayEnd}' AND A.status=1 AND B.agent_account = {$_SESSION['login']}", ARRAY_A);
       $commissions = array_merge($todayComm, $yesterdayComm, $weekComm, $monthComm);
      if ($mem)
      {
        $mem->set('commissionsIndex_' . $_SESSION['login'] . '_' . $period, $commissions,0 ,60);
      }
    }
  }
 ?>
<div class="content-body">
  <div class="content-title">
      首页
  </div>
  <div class="container" >
    <div class="row">
      <div class="col-sm-6" style="float:left;">
      <div class="panel panel-info">
         <div class="panel-heading">
            <h3 class="panel-title">今日(<?=date('Y-m-d')?>)</h3>
         </div>
         <div class="panel-body">
            <table class="table">
              <tr class="active">
                <td>
                  <span class="label label-warning"><?=$_SESSION['login']=='admin'?'累计':'团队'?>交易量</span>
                </td>
                <td>
                  <strong><?=number_format($allTradeInfo['TODAY_TRADE_QUAN1'])?></strong>笔（总计）
                </td>
                <td>
      　            <strong><?=number_format($allTradeInfo['TODAY_TRADE_QUAN2'])?></strong>笔（去平）
                </td>
              </tr>
              <tr class="active">
                <td>
                  <span class="label label-warning"><?=$_SESSION['login']=='admin'?'累计':'团队'?>交易金额</span>
                </td>
                <td>
                  <strong>$<?=number_format($allTradeInfo['TODAY_TRANSACTION1'], 2)?></strong>（总计）
                </td>
                <td>
                  <strong>$<?=number_format($allTradeInfo['TODAY_TRANSACTION2'], 2)?></strong>（去平）
                </td>
              </tr>
    <?php if ($_SESSION['login'] == 'admin') {?>
              <tr class="active">
                <td>
                  <span class="label label-warning">累计交易利润</span>
                </td>
                <td>
                  <strong>$<?=number_format($allTradeInfo['TODAY_PROFIT'], 2)?></strong>
                </td>
                <td>
                </td>
              </tr>
    <?php } ?>
    <!-- 管理员不查看佣金 -->
    <?php if ($_SESSION['login'] != 'admin') { ?>
              <tr class="active">
                <td>
                  <span class="label label-warning">直接下级交易量</span>
                </td>
                <td>
                  <strong><?= number_format($commissions['TODAY_FIR_TRADE_QUAN1'])?></strong>笔（总计）
                </td>
                <td>
      　            <strong><?= number_format($commissions['TODAY_FIR_TRADE_QUAN2'])?></strong>笔（去平）
                </td>
              </tr>
              <tr class="active">
                <td>
                  <span class="label label-warning">直接下级交易金额</span>
                </td>
                <td>
                  <strong>$<?=number_format($commissions['TODAY_FIR_TRANSACTION1'], 2)?></strong>（总计）
                </td>
                <td>
                  <strong>$<?=number_format($commissions['TODAY_FIR_TRANSACTION2'], 2)?></strong>（去平）
                </td>
              </tr>
                <tr class="active">
                <td>
                  <span class="label label-warning">(直接)实时返佣</span>
                </td>
                <td>
                  <strong>$<?=number_format($commissions['TODAY_COMMISSION'], 2)?></strong>
                </td>
                <td>
                </td>
              </tr>
    <?php }?>
            </table>
         </div>
      </div>
      </div>
      <div class="col-sm-6" style="float:left;">
      <div class="panel panel-info">
         <div class="panel-heading">
            <h3 class="panel-title">昨日(<?=date('Y-m-d',strtotime("-1 day"))?>)</h3>
         </div>
         <div class="panel-body">
            <table class="table">
              <tr class="active">
                <td>
                  <span class="label label-warning"><?=$_SESSION['login']=='admin'?'累计':'团队'?>交易量</span>
                </td>
                <td>
                  <strong><?=number_format($allTradeInfo['YESTERDAY_TRADE_QUAN1'])?></strong>笔（总计）
                </td>
                <td>
      　            <strong><?=number_format($allTradeInfo['YESTERDAY_TRADE_QUAN2'])?></strong>笔（去平）
                </td>
              </tr>
              <tr class="active">
                <td>
                  <span class="label label-warning"><?=$_SESSION['login']=='admin'?'累计':'团队'?>交易金额</span>
                </td>
                <td>
                  <strong>$<?=number_format($allTradeInfo['YESTERDAY_TRANSACTION1'], 2)?></strong>（总计）
                </td>
                <td>
                  <strong>$<?=number_format($allTradeInfo['YESTERDAY_TRANSACTION2'], 2)?></strong>（去平）
                </td>
              </tr>
    <?php if ($_SESSION['login'] == 'admin') {?>
              <tr class="active">
                <td>
                  <span class="label label-warning">累计交易利润</span>
                </td>
                <td>
                  <strong>$<?=number_format($allTradeInfo['YESTERDAY_PROFIT'], 2)?></strong>
                </td>
                <td>
                </td>
              </tr>
    <?php } ?>
    <!-- 管理员不查看佣金 -->
    <?php if ($_SESSION['login'] != 'admin') { ?>
              <tr class="active">
                <td>
                  <span class="label label-warning">直接下级交易量</span>
                </td>
                <td>
                  <strong><?=number_format($commissions['YESTERDAY_FIR_TRADE_QUAN1'])?></strong>笔（总计）
                </td>
                <td>
      　            <strong><?=number_format($commissions['YESTERDAY_FIR_TRADE_QUAN2'])?></strong>笔（去平）
                </td>
              </tr>
              <tr class="active">
                <td>
                  <span class="label label-warning">直接下级交易金额</span>
                </td>
                <td>
                  <strong>$<?=number_format($commissions['YESTERDAY_FIR_TRANSACTION1'], 2)?></strong>（总计）
                </td>
                <td>
                  <strong>$<?=number_format($commissions['YESTERDAY_FIR_TRANSACTION2'], 2)?></strong>（去平）
                </td>
              </tr>
                <tr class="active">
                <td>
                  <span class="label label-warning">(直接)实时返佣</span>
                </td>
                <td>
                  <strong>$<?=number_format($commissions['YESTERDAY_COMMISSION'], 2)?></strong>
                </td>
                <td>
                </td>
              </tr>
    <?php } ?>
            </table>
         </div>
      </div>
      </div>
    </div>
  </div>
　<div class="container" >
    <div class="row">
      <div class="col-sm-6" style="float:left;">
      <div class="panel panel-info">
         <div class="panel-heading">
            <h3 class="panel-title">本周(<?=date('Y-m-d', strtotime("last sunday")), '——今天'?>)</h3>
         </div>
         <div class="panel-body">
            <table class="table">
            <table class="table">
              <tr class="active">
                <td>
                  <span class="label label-warning"><?=$_SESSION['login']=='admin' ? '累计' : '团队'?>交易量</span>
                </td>
                <td>
                  <strong><?=number_format($allTradeInfo['WEEK_TRADE_QUAN1'])?></strong>笔（总计）
                </td>
                <td>
      　            <strong><?=number_format($allTradeInfo['WEEK_TRADE_QUAN2'])?></strong>笔（去平）
                </td>
              </tr>
              <tr class="active">
                <td>
                  <span class="label label-warning"><?=$_SESSION['login']=='admin' ? '累计' : '下级'?>交易金额</span>
                </td>
                <td>
                  <strong>$<?=number_format($allTradeInfo['WEEK_TRANSACTION1'], 2)?></strong>（总计）
                </td>
                <td>
                  <strong>$<?=number_format($allTradeInfo['WEEK_TRANSACTION2'], 2)?></strong>（去平）
                </td>
              </tr>
    <?php if ($_SESSION['login'] == 'admin') {?>
              <tr class="active">
                <td>
                  <span class="label label-warning">累计交易利润</span>
                </td>
                <td>
                  <strong>$<?=number_format($allTradeInfo['WEEK_PROFIT'], 2)?></strong>
                </td>
                <td>
                </td>
              </tr>
    <?php } ?>
    <!-- 管理员不查看佣金 -->
    <?php if ($_SESSION['login'] != 'admin') { ?>
              <tr class="active">
                <td>
                  <span class="label label-warning">直接下级交易量</span>
                </td>
                <td>
                  <strong><?=number_format($commissions['WEEK_FIR_TRADE_QUAN1'])?></strong>笔（总计）
                </td>
                <td>
      　            <strong><?=number_format($commissions['WEEK_FIR_TRADE_QUAN2'])?></strong>笔（去平）
                </td>
              </tr>
              <tr class="active">
                <td>
                  <span class="label label-warning">直接下级交易金额</span>
                </td>
                <td>
                  <strong>$<?=number_format($commissions['WEEK_FIR_TRANSACTION1'], 2)?></strong>（总计）
                </td>
                <td>
                  <strong>$<?=number_format($commissions['WEEK_FIR_TRANSACTION2'], 2)?></strong>（去平）
                </td>
              </tr>
                <tr class="active">
                <td>
                  <span class="label label-warning">(直接)实时返佣</span>
                </td>
                <td>
                  <strong>$<?=number_format($commissions['WEEK_COMMISSION'], 2)?></strong>
                </td>
                <td>
                </td>
              </tr>
    <?php } ?>
            </table>
            </table>
         </div>
      </div>
      </div>
      <div class="col-sm-6" style="float:left;">
      <div class="panel panel-info">
         <div class="panel-heading">
            <h3 class="panel-title">本月(<?=date('Y-m-01'), '——今天'?>)</h3>
         </div>
         <div class="panel-body">
            <table class="table">
            <table class="table">
              <tr class="active">
                <td>
                  <span class="label label-warning"><?=$_SESSION['login']=='admin' ? '累计' : '团队'?>交易量</span>
                </td>
                <td>
                  <strong><?= number_format($allTradeInfo['MONTH_TRADE_QUAN1'])?></strong>笔（总计）
                </td>
                <td>
      　            <strong><?= number_format($allTradeInfo['MONTH_TRADE_QUAN2'])?></strong>笔（去平）
                </td>
              </tr>
              <tr class="active">
                <td>
                  <span class="label label-warning"><?=$_SESSION['login']=='admin' ? '累计' : '团队'?>交易金额</span>
                </td>
                <td>
                  <strong>$<?=number_format($allTradeInfo['MONTH_TRANSACTION1'], 2)?></strong>（总计）
                </td>
                <td>
                  <strong>$<?=number_format($allTradeInfo['MONTH_TRANSACTION2'], 2)?></strong>（去平）
                </td>
              </tr>
    <?php if ($_SESSION['login'] == 'admin') { ?>
              <tr class="active">
                <td>
                  <span class="label label-warning">累计交易利润</span>
                </td>
                <td>
                  <strong>$<?=number_format($allTradeInfo['MONTH_PROFIT'], 2)?></strong>
                </td>
                <td>
                </td>
              </tr>
    <?php } ?>
    <!-- 管理员不查看佣金 -->
    <?php if ($_SESSION['login'] != 'admin') { ?> 
              <tr class="active">
                <td>
                  <span class="label label-warning">直接下级交易量</span>
                </td>
                <td>
                  <strong><?= number_format($commissions['MONTH_FIR_TRADE_QUAN1'])?></strong>笔（总计）
                </td>
                <td>
      　            <strong><?= number_format($commissions['MONTH_FIR_TRADE_QUAN2'])?></strong>笔（去平）
                </td>
              </tr>
              <tr class="active">
                <td>
                  <span class="label label-warning">直接下级交易金额</span>
                </td>
                <td>
                  <strong>$<?=number_format($commissions['MONTH_FIR_TRANSACTION1'], 2)?></strong>（总计）
                </td>
                <td>
                  <strong>$<?=number_format($commissions['MONTH_FIR_TRANSACTION2'], 2)?></strong>（去平）
                </td>
              </tr>
                <tr class="active">
                <td>
                  <span class="label label-warning">(直接)实时返佣</span>
                </td>
                <td>
                  <strong>$<?=number_format($commissions['MONTH_COMMISSION'], 2)?></strong>
                </td>
                <td>
                </td>
              </tr>
    <?php } ?>
            </table>
            </table>
         </div>
      </div>
      </div>
    </div>
  </div>
　</div>
</div>