<?php
  header('Content-type:text/html; charset=utf-8');
  include('../config.php');                                         //配置信息页面
  /*ajax动态传递的值*/
  $topLogin = $_POST['LOGIN'];                                     //所查询的账号
  $startDate = $_POST['startDate'];                                   //开始的日期
  $endDate = $_POST['endDate'];                                       //结束的日期，
  $startTime = date('Y-m-d 00:00:00', strtotime($startDate));   //查询的开始时间
  $endTime = date('Y-m-d 23:59:59', strtotime($endDate));       //查询结束的时间
  $todayEndTime = date('Y-m-d 23:59:59');                        //今天的结束时间;
                                                                     //注意：ajax的后台尽量不要使用session和cookie
  /*取出所有的用户的账号和用户的上级账号*/
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
  //获取所有下级
  global $resultlll;
  $resultlll = null;
  $adminLogins = queryLowerLogin($allUserInfo, $topLogin);//查询出所有和登录者有关的账号(并不包括其自己)

  if ($adminLogins)
  {
    array_unshift($adminLogins, $topLogin);               //将用户自己的登录账号插入数组头部
  }
  else
  {
    $adminLogins = array($topLogin);
  }  

  $adminUserInfo = array();            //所有相关的用户的信息
  $firLowerLogins = array();           //直接下级
  foreach ($allUserInfo as $key => $value) 
  {
    if (in_array($value['login'], $adminLogins))
    {
      $adminUserInfo[$value['login']]['name'] = $value['name'];
      $adminUserInfo[$value['login']]['login'] = $value['login'];
      $adminUserInfo[$value['login']]['agent_account'] = $value['agent_account'];
    }
    if ($value['agent_account'] == $topLogin)
    {
      $firLowerLogins[] = $value['login'];
    }
  }
  unset($allUserInfo);
  //获取每个人的交易额
  //获取权限内每个人的交易额，人数少用单表查询（不同的下级采用不同的查询方式，具体人数待优化）
  if ($mem)
  {
    $everyTradeInfo = $mem->get('everyTradeInfo_'.$startTime.'_'.$endTime.'_'.$topLogin);
    if (!$everyTradeInfo)
    {
      $everyTradeInfo = $mem->get('everyTradeInfo_'.$startTime.'_'.$endTime.'_'.$_SESSION['login']);
    }
  }
  if (!$everyTradeInfo)
  {
    if (count($adminLogins)<500)
    {
      foreach ($adminLogins as $value) 
      {
        $adminLoginsV.= $value.',';
      }
      $adminLoginsV = trim($adminLoginsV, ',');
      $everyTradeInfo = $dbNRemote->get_results("SELECT login AS LOGIN,
            COUNT(id) AS TRADE_QUAN,
            SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS TRADE_QUAN2,
            SUM(`money`) AS TRANSACTION,
            SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TRANSACTION2,
            SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS PROFIT FROM mt4_binary_option_history WHERE login IN($adminLoginsV) AND update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1 GROUP BY login", ARRAY_A);
    }
    //人数多时，牺牲性能全部查询出来
    else
    {
      //获取每个人的交易数据
      $everyTradeInfo = $dbNRemote->get_results("SELECT login AS LOGIN,
            COUNT(id) AS TRADE_QUAN,
            SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS TRADE_QUAN2,
            SUM(`money`) AS TRANSACTION,
            SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TRANSACTION2,
            SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS PROFIT FROM mt4_binary_option_history WHERE update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1 GROUP BY login",ARRAY_A);
    }
    if ($mem)
    {
      if ($endTime == $todayEndTime)
      {
        $mem->set('everyTradeInfo_'.$startTime.'_'.$endTime.'_'.$topLogin, $everyTradeInfo, 0 ,10);
      }else
      {
        $mem->set('everyTradeInfo_'.$startTime.'_'.$endTime.'_'.$topLogin, $everyTradeInfo, 0 ,3600);//过去的时间，缓存设置成一小时
      }
    }
  }
  //获取所有的代理的返点比率
  $agentsRatios = getAgentRatios($adminLogins);                   //获取权限内所有代理的返点比率 

  if ($agentsRatios)                                               //说明存在代理
  {
    $agentLogins = array();//所有代理的账号组成的一维数组
    $firLowerTeamLogins = array();//第一级下级代理的团队账号组成的二位数组
    
    foreach ($agentsRatios as $key => $value) 
    {
      $agentLogins[] = $key;                                        //所有的代理机构代码
      if ($adminUserInfo[$key]['agent_account'] == $topLogin)       //说明是直接下级
      {
        global $resultlll;
        $resultlll = null;
        $firLowerTeamLogins[$key] = queryLowerLogin($adminUserInfo, $key); //直接下级的团队账号组成的二维数组
      }
    }
    
    if ($everyTradeInfo)
    {
      $everyTradeInfo2 = array();//把键名变成login账号
      $firstLowerLogins = array();//第一级下级的账号
      //循环每一个人的交易额
      foreach ($everyTradeInfo as $everyTradeInfoV) 
      {  
        $everyTradeInfo2[$everyTradeInfoV['LOGIN']] = $everyTradeInfoV;

        //所有代理的直接下级的交易额之和
        //上级帐号
        $upLogin = $adminUserInfo[$everyTradeInfoV['LOGIN']]['agent_account'];
        if ($upLogin)//必须存在该上级，即符合条件
        {
          $everAgentFirLowTran[$adminUserInfo[$everyTradeInfoV['LOGIN']]['agent_account']] += $everyTradeInfoV['TRANSACTION2'];
        }
      }

      $pathArr = getPathArr($agentLogins, $adminUserInfo);          //路径信息
      $currentCommission = getCurrentCommission($pathArr, $agentsRatios, $everAgentFirLowTran, $currentRatio);//计算出所有代理的实时返佣
    }
  }
  unset($everyTradeInfo);
  unset($pathArr);
  unset($agentsRatios);
  unset($everAgentFirLowTran);
  //第一级下级的团队交易额
  $firLowerTeamLoginsTran = array();

  $return = array();//返回的结果值
  //计算第一级下级的代理的交易
  if ($firLowerLogins)
  {
    foreach ($firLowerLogins as $firLowerLogin) 
    {
      $return[$firLowerLogin]['LOGIN'] = $firLowerLogin;//账号
      $return[$firLowerLogin]['NAME'] = $adminUserInfo[$firLowerLogin]['name'];//姓名
      $return[$firLowerLogin]['TRADE_QUAN'] = $everyTradeInfo2[$firLowerLogin]['TRADE_QUAN']?$everyTradeInfo2[$firLowerLogin]['TRADE_QUAN']:0;//本人去平前的交易量 
      $return[$firLowerLogin]['TRADE_QUAN2'] = $everyTradeInfo2[$firLowerLogin]['TRADE_QUAN2']?$everyTradeInfo2[$firLowerLogin]['TRADE_QUAN2']:0;//本人去平后的交易量    
      $return[$firLowerLogin]['TRANSACTION'] = $everyTradeInfo2[$firLowerLogin]['TRANSACTION']?$everyTradeInfo2[$firLowerLogin]['TRANSACTION']:0;//本人去平前的交易金额
      $return[$firLowerLogin]['TRANSACTION2'] = $everyTradeInfo2[$firLowerLogin]['TRANSACTION2']?$everyTradeInfo2[$firLowerLogin]['TRANSACTION2']:0;//本人去平后的交易金额
      $return[$firLowerLogin]['PROFIT'] = $everyTradeInfo2[$firLowerLogin]['PROFIT']?$everyTradeInfo2[$firLowerLogin]['PROFIT']:0;//本人去平后的交易金额//本人的盈亏之和
      //直属下级团队交易
      //该直属下级是代理
      if (array_key_exists($firLowerLogin, $firLowerTeamLogins))
      {
        if ($firLowerTeamLogins[$firLowerLogin])
        {
          foreach ($firLowerTeamLogins[$firLowerLogin] as $kkk) 
          {
            $return[$firLowerLogin]['TEAMTRAN'] += $everyTradeInfo2[$kkk]['TRANSACTION2'];  //团队交易额
          }
        }
        else
        {
          $return[$firLowerLogin]['TEAMTRAN'] = 0;   //团队交易额
        }
        $return[$firLowerLogin]['COM'] = $currentCommission[$firLowerLogin]['COM']?$currentCommission[$firLowerLogin]['COM']:0;//佣金
        $return[$firLowerLogin]['COMTRAN'] = $currentCommission[$firLowerLogin]['TRAN'];//返佣交易额
      //该直属下级是一个交易者
      }
      else
      {
        $return[$firLowerLogin]['TEAMTRAN'] = '/';//团队交易额
        $return[$firLowerLogin]['COMTRAN'] = '/'; //返佣交易额
        $return[$firLowerLogin]['COM'] = '/';     //及时返佣
      }
    }
  }
  unset($adminUserInfo);
  unset($currentCommission);
  unset($everyTradeInfo2);
  $groups_json = json_encode($return);
  unset($return);
  echo $groups_json;
  unset($groups_json);