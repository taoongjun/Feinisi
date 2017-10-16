<?php 
$action = $_GET['action'];//获取动作,没有动作时，是登录
$do = $_GET['do'];//对$action进行补充
//佣金列表
if ($action == 'commission-list') {
    //当前所处的月份
    $nowMonth = date('n');
    //手动查询
    if ($_POST['month']) {
        $month = $_POST['month'];
        $searchAgent = $_POST['search_login1'] ? $_POST['search_login1'] : '';//手动查询的代理，若不查询则默认查询所有用户
    //没有进行手工查询
    } else if (!$_POST) {
        //url地址传递了月份
        //只传递了月份信息（则get值为纯数字）
        if (is_numeric($_GET['do'])) {
            $month = $_GET['do'];
            if ($month >= $nowMonth) {
                $month = $nowMonth - 1;
            }
            $searchAgent = '';
        //url地址有月份，但不是数字类型(如果没有月份，此处也会成立,即下载默认月份的报表)
        } else if (!is_numeric($_GET['do']) && $_GET['do']) {
            $downloadInfo = explode('M', $_GET['do']);
            $download = $downloadInfo[0];//此处表示下载列表
            $month = $downloadInfo[1] ? $downloadInfo[1] : date('n') - 1;//查询的月份（默认是上个月）
            //防止地址栏非法输入月份
            if ($month >= $nowMonth) {
                $month = $nowMonth - 1;
            }
            $searchAgent = $downloadInfo[2] ? $downloadInfo[2] : '';//查询的账号
        } else if (!$_GET['do']) {
            $month = date('n') - 1;
        }
    }
    //获取本月最后一天
    //月份之前注册的用户将不予以查询和计算
    $month = $month > 9 ? $month : '0' . ltrim($month, '0');//月份，不足两位的补0
    $period = date('Y-' . $month);//格式2016-06
    $monthFirDay = date('Y-' . $month . '-01');//查询月份的第一天
    $endRegTime = date('Y-m-d 23:59:59', strtotime("$monthFirDay +1 month -1 day"));//最后的注册时间
    if ($mem) {
        $allUserInfo = $mem->get('allUserInfo_' . $period);//memcache优先获取，提升网页速度，避免服务器崩溃
    }
    if (!$allUserInfo) {
        $allUserInfo = $dbNRemote->get_results("SELECT login,agent_account,name FROM mt4_account", ARRAY_A);
        if ($mem) {
            $mem->set('allUserInfo_' . $period, $allUserInfo, 0, 60);//此处设成，过去的时间，永久有效，据说是30天以后会失效
        }
    }
    //$validGroupStr在config.php中配置
    $loginLogin = $_SESSION['login'] == 'admin' ? 0 : $_SESSION['login'];//管理员的$_SESSION['login']=='admin'
    //如果不进行手动查询，则查询的是默认登录者
    $topLogin = $_POST['search_login1'] ? $_POST['search_login1'] : $loginLogin;
    $relativeUserInfo = array();//页面中用于显示的信息
    $relativeLogins = array();//权限内相关的账号
    $adminUserInfo = array();//与当前计算相关的账号
    $adminLogins = array();// 与当前计算相关的用户信息
    $mustBeAgeLogins = array();//必须设置成代理的账号
    //管理员登录
    if ($_SESSION['login'] == 'admin') {
        //不进行查询
        if ($topLogin == 0) {
            foreach ($allUserInfo AS $value) {
                $relativeUserInfo[$value['login']] = $value;
                $relativeLogins[]                  = $value['login'];
                $adminUserInfo[$value['login']]    = $value;
                $adminLogins[]                     = $value['login'];
                if ($value['agent_account']) {
                    $mustBeAgeLogins[] = $value['agent_account'];
                }
            }
        //进行查询
        } else {
            global $resultlll;
            $resultlll = null;
            $adminLogins = queryLowerLogin($allUserInfo, $topLogin);//查询出所有和登录者有关的账号(并不包括其自己)
            if ($adminLogins) {
                array_unshift($adminLogins, $topLogin);
            } else {
                $adminLogins = array($topLogin);
            }
            foreach ($allUserInfo as $key => $value) {
                $relativeUserInfo[$value['login']] = $value;
                $relativeLogins[] = $value['login'];
                if (in_array($value['login'], $adminLogins)) {
                    $adminUserInfo[$value['login']] = $value;
                }
                if ($value['agent_account']) {
                    $mustBeAgeLogins[] = $value['agent_account'];
                }
            }
        }
    //代理登录
    } else if ($_SESSION['login'] != 'admin') {
        //不进行查询
        if ($topLogin == $_SESSION['login']) {
            global $resultlll;
            $resultlll = null;
            $adminLogins = queryLowerLogin($allUserInfo, $_SESSION['login']);//查询出所有和登录者有关的账号(并不包括其自己)
            if ($adminLogins) {
                array_unshift($adminLogins, $_SESSION['login']);
            } else {
                $adminLogins = array($_SESSION['login']);
            }
            $relativeLogins = $adminLogins;
            foreach ($allUserInfo as $key => $value) {
                if (in_array($value['login'], $adminLogins)) {
                    $relativeUserInfo[$value['login']] = $value;
                    $adminUserInfo[$value['login']] = $value;
                }
                if ($value['agent_account']) {
                    $mustBeAgeLogins[] = $value['agent_account'];
                }
            }
        //进行查询
        } else {
            global $resultlll;
            $resultlll = null;
            $adminLogins = queryLowerLogin($allUserInfo, $topLogin);//查询出所有和登录者有关的账号(并不包括其自己)
            if ($adminLogins) {
                array_unshift($adminLogins, $topLogin);
            } else {
                $adminLogins = array($topLogin);
            }
            global $resultlll;
            $resultlll = null;
            $relativeLogins = queryLowerLogin($allUserInfo, $_SESSION['login']);//查询出所有和登录者有关的账号(并不包括其自己)
            if ($relativeLogins) {
                array_unshift($relativeLogins, $_SESSION['login']);
            } else {
                $relativeLogins = array($_SESSION['login']);
            }
            foreach ($allUserInfo as $key => $value) {
                if (in_array($value['login'], $relativeLogins)) {
                    $relativeUserInfo[$value['login']] = $value;
                }
                if (in_array($value['login'], $adminLogins)) {
                    $adminUserInfo[$value['login']] = $value;
                }
                if ($value['agent_account']) {
                    $mustBeAgeLogins[] = $value['agent_account'];
                }
            }
        }
    }
    /*unset($allUserInfo);*/
    //对所有权限内的用户信息按照数组姓名进行排序
    $relativeNames = array_map('end', $relativeUserInfo);//取出姓名
    array_multisort($relativeNames, SORT_ASC, $relativeUserInfo);//根据姓名进行排序
    unset($relativeNames);
    //缓存中获取每个人的交易额等数据
    if ($mem) {
        $everAgentTeamFirTran = $mem->get('everAgentTeamFirTran_' . $topLogin . '_' . $period);
    }
    if (!$everAgentTeamFirTran) {
        //获取每个人代理的直接下级和所在团队的交易额（ 老函数getEverTeamFirTransaction，不具备存储功能 ）
        $everAgentTeamFirTran = getEverTeamFirTransaction2($adminLogins, $month, $adminUserInfo);
        if ($mem) {
            $mem->set('everAgentTeamFirTran_' . $topLogin . '_' . $period, $everAgentTeamFirTran, 0 ,0);
        }
    }
    //求出所有与之相关的用户中是代理商的信息
    if ($mem) {
        $relativeAgentsInfo = $mem->get('relativeAgentsInfo_' . $_SESSION['login'] . '_' . $period);
    }
    if (!$relativeAgentsInfo) {
        $relativeAgentsInfo = getAndStoreAgentLoginsInfo($relativeLogins, $month);
        if ($mem) {
            $mem->set('relativeAgentsInfo_' . $_SESSION['login'] . '_' . $period, $relativeAgentsInfo, 0, 0);
        }
    }
    $relativeAgentLogins = array_keys($relativeAgentsInfo);//登录用户权限内的所有的代理账号组成的一维数组
    $adminAgentsInfo = array();//本次需要计算的所有的代理完整信息
    $adminAgentLogins = array();//本次需要计算的所有的代理的账号*/
    //根据权限内的代理账号获取本次计算的代理账号
    if ($topLogin == 0 || $topLogin == $_SESSION['login']) {
        $adminAgentsInfo = $relativeAgentsInfo;
        $adminAgentLogins = $relativeAgentLogins;
    } else {
        foreach ($adminLogins as $login) {
            if ($relativeAgentsInfo[$login]) {
                $adminAgentsInfo[$login] = $relativeAgentsInfo[$login];
                $adminAgentLogins[] = $login;
            }
        }
    }
    $mustBeAgeLogins = array_unique($mustBeAgeLogins);//所有必须设置成代理的账号
    $relMustBeAgentLogins = array_intersect($mustBeAgeLogins, $relativeLogins);//权限内必须设置成代理的账号
    unset($mustBeAgeLogins);
    $mustAgeButTrade = array_diff($relMustBeAgentLogins, $relativeAgentLogins);//求出必须设置成代理而未设置的账号*/
    //存在代理商才会进行下一步运算
    if ($adminAgentsInfo) {
        //将代理商的等级层次分出排列
        $pathArr = getPathArr($adminAgentLogins, $adminUserInfo);
        //计算出佣金，其中包含多维'dirCom'=>$dirCom, 'gapCom'=>$gapCom,'gapComUp'=>$gapComUp, 'reward'=>$reward,'rewardUp'=>$rewardUp, 'allCom'=>$allCom,'curCom'=>$curCom,'monCom'=>$monCom
        $commission = calculateCommsissionByFirTeam($pathArr, $adminAgentsInfo, $everAgentTeamFirTran);
    }
    //导出excel文件 
   if ($download && $adminAgentLogins) {
        $i = 0;
        foreach ($adminAgentLogins as $agentLoginVs) {
            $newComm[$i][] = $agentLoginVs;//账号
            $newComm[$i][] = $adminUserInfo[$agentLoginVs]['name'];//姓名
            $newComm[$i][] = $everAgentTeamFirTran[$agentLoginVs]['FIR_TRADE_QUAN'] ? $everAgentTeamFirTran[$agentLoginVs]['FIR_TRADE_QUAN'] : 0;//直接下级交易量(总计)
            $newComm[$i][] = $everAgentTeamFirTran[$agentLoginVs]['FIR_TRADE_QUAN2'] ? $everAgentTeamFirTran[$agentLoginVs]['FIR_TRADE_QUAN2'] : 0;//直接下级交易量(去平后)
            $newComm[$i][] = $everAgentTeamFirTran[$agentLoginVs]['FIR_TRANSACTION'];//直接下级交易金额(总计)
            $newComm[$i][] = $everAgentTeamFirTran[$agentLoginVs]['FIR_TRANSACTION2'];//直接下级交易金额(去平后)
            $newComm[$i][] = $everAgentTeamFirTran[$agentLoginVs]['TEAM_TRANSACTION2'];//直接下级交易金额(去平后)
            $newComm[$i][] = $adminAgentsInfo[$agentLoginVs]['GROUP'];//所属代理商分组
            $newComm[$i][] = $adminAgentsInfo[$agentLoginVs]['RATIO'];//分组比率
            $newComm[$i][] = $adminAgentsInfo[$agentLoginVs]['REWARD'];//奖励比率
            $newComm[$i][] = $commission['dirCom'][$agentLoginVs]['dirCom'];//直接佣金
            $newComm[$i][] = $commission['gapCom'][$agentLoginVs]['gapCom'];//下级点差佣金
            $newComm[$i][] = $commission['reward'][$agentLoginVs]['reward'];//下级奖励
            $newComm[$i][] = $commission['allCom'][$agentLoginVs]['allCom'];//佣金合计
            $newComm[$i][] = $commission['monCom'][$agentLoginVs]['monCom'];//月结佣金
            $i++;
        }
        $filename = date('Y').'年'.$month.'月佣金报表';//报表名称
      //导出excel文件 
        $excel = new PHPExcel();
        //Excel表格式
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        //表头数组
        $tableheader = array('账号','姓名','直接下级交易量(总计)','直接下级交易量(去平后)','直接下级交易金额(总计)','直接下级交易金额(去平后)','团队去平交易额','所属代理商分组','分组比率','奖励比率','直接佣金','下级点差佣金','下级奖励','佣金合计','月结佣金');
        //填充表头信息
        for ($i = 0;$i < count($tableheader);$i++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1","$tableheader[$i]");
        }    
        $i = 2;
        //填充表格信息
        foreach ($newComm as $key => $value) {
            foreach ($value as $k => $v) {
                $excel->getActiveSheet()->setCellValue("$letter[$k]" . ($key + 2), "$v");
            }
            $i++;
        }
        $excel->getActiveSheet()->setCellValue('A' . $i, "备注：金额单位($);交易量单位（条）。");
        $excel->getActiveSheet()->mergeCells("A" . $i . ":" . $letter[count($tableheader) - 1] . $i);
        unset($newComm);
        $write = new PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header("Content-Disposition:attachment;filename='{$filename}.xls'");
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }

//查看利润报表（只有管理员才能看）
} else if ($action == 'profit-list' && $_SESSION['login'] == 'admin') {
    //手动添加运营账号
    if ($_POST['noProfitLogin']) {
        $noProfitLogins = serialize(trim($_POST['noProfitLogin'])); //不计算利润的账号
        $noProfitMonth = $_POST['noProfitMonth'] < 10 ? '0' . ltrim($_POST['noProfitMonth'], '0') : $_POST['noProfitMonth'];//不计算利润的月份
        $noProfitPeriod = date('Y-' . $noProfitMonth);//格式2016-07
        $setNoProfitLogins = $dbLocal->query("INSERT MT4_NEW_NOPROFIT(`PERIOD`,`LOGINS`) VALUES('{$noProfitPeriod}','{$noProfitLogins}') ON DUPLICATE KEY UPDATE `LOGINS`='{$noProfitLogins}'");
        //更新运营账号
        if ($mem) {
            $mem->set('noProfitLogin_' . $noProfitPeriod, $noProfitLogins ,0 ,0);
            //需要删除的变量(运营账号的业务量之和)
            $mem->delete('invalidProfit_' . $noProfitPeriod);
        }
    }
    //不进行手工查询对月份的处理
    if (!$_POST['month']) {
        //url地址传递信息，是下载报表
        if ($_GET['do']) {
            $downloadInfo = explode('M', $_GET['do']);
            $download = $downloadInfo[0];//进行下载(通过url地址传值)
            $month = $downloadInfo[1] ? $downloadInfo[1] : date('n');//下载时如果不传递月份，就默认为当前月。
            $searchLogin = $downloadInfo[2] ? $downloadInfo[2] : '';//查询的目标账号
        //默认当前月
        } else {
            $month = date('m');//date('n')月份小于10时，前面不会补0。但是date('m')会
        }
    //2、查询月份，不查询具体账号
    } else if ($_POST['month'] && $_POST['search_login1'] == '') {
        $month = $_POST['month'];
        $searchLogin = '';
    //3、即查询月份，又查询具体账号
    } else if ($_POST['month'] && $_POST['search_login1'] != '') {
        $month = $_POST['month'];
        $searchLogin = $_POST['search_login1'];
    }
    $month = $month > 9 ? $month : '0' . ltrim($month, '0');//月份，不足两位的补0
    $nowMonth = date('m');//现在所处的月份
    $period = date('Y-' . $month);//所查询的周期，格式2016-06
    $startDate = date('Y-' . $month . '-01');//查询月份的第一天
    if ($month != $nowMonth) {
        $endDate = date('Y-m-d', strtotime("$startDate +1 month -1 day"));//所查询月份的最后一天
    }
    $startTime = date('Y-m-d 00:00:00', strtotime($startDate));//这个月1号的0点
    $endTime = $month == $nowMonth ? date('Y-m-d H:i:s') : date('Y-m-d 23:59:59', strtotime($endDate));
    /*$endTime = date('Y-m-d 23:59:59', strtotime($endDate));//截止今天的最后时间*/
    $profitForList = array();//用于呈现在列表中的信息
    //一、说明是要查询所有的用户
    if ($searchLogin == '') {
        //一１所查询的月份为当月，则数据库中肯定没有进行存储，实时变化的数据不会进行固定存储
        if ($month == $nowMonth) {
            if ($mem) {
                $relativeUserInfo = $mem->get('allUserInfoRealTime');
            }
            if (!$relativeUserInfo) {
                //此处并没有好办法使用缓存
                $relativeUserInfo = $dbNRemote->get_results("SELECT login,agent_account,name FROM mt4_account", ARRAY_A);//$validGroupStr在config.php中配置      //注意：如果有下级的用户没有被设置成代理。那会计算结果会出错
                if ($mem) {
                    $mem->set('allUserInfoRealTime', $relativeUserInfo, 0, 60);//此处设置60秒的会在一定程度上减少数据库的连接
                }
            }
            //所有用户信息变成关联数组
            $relativeAgentsInfoAso = array();
            foreach ($relativeUserInfo as $key => $value) {
                $relativeLogins[] = $value['login'];
                $relativeNames[] = $value['name'];
                $relativeUserInfoAso[$value['login']] = $value;
            }
            array_multisort($relativeNames, SORT_ASC, $relativeUserInfo);//按照姓名对用户的信息进行排序，用在搜索框中
            unset($relativeNames);
            //返回所有用户的利润
            if ($mem) {
                $allBusiness = $mem->get('allBusiness_' . $period);//数据不会存入数据库中
            }  
            if (!$allBusiness) {
                $allBusiness = $dbNRemote->get_results("SELECT login AS LOGIN,
                    COUNT(id) AS TRADE_QUAN,
                    SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS TRADE_QUAN2,
                    SUM(`money`) AS TRANSACTION,
                    SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TRANSACTION2,
                    SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS PROFIT FROM mt4_binary_option_history WHERE update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status = 1 GROUP BY login", ARRAY_A);//初始化利润表（ 没有剔除无效账号 ）  
                if ($mem) {
                    $mem->set('allBusiness_' . $period, $allBusiness, 0, 60);//60秒后失效更新
                }
            }
            
        //一２没有查询本月，1、看memcache是否存储-2、本读数据库是否存储-3、前两个都不满足的情况下进行远程查询
        } else {
            //查看memcache中是否进行存储
            if ($mem) {
                $relativeUserInfo = $mem->get('allUserInfo_' . $period);
            }
            if (!$relativeUserInfo) {
                //与本次计算有关的所有用户
                $relativeUserInfo = $dbNRemote->get_results("SELECT login,agent_account,name FROM mt4_account", ARRAY_A);
                //设置memcache
                if ($mem) {
                    $mem->set('allUserInfo_' . $period, $relativeUserInfo ,0 ,0);//永久有效
                }
            }  
            //所有用户信息变成关联数组
            $relativeUserInfoAso = array();
            foreach ($relativeUserInfo as $key => $value) {
                $relativeLogins[] = $value['login'];
                $relativeNames[] = $value['name'];
                $relativeUserInfoAso[$value['login']] = $value;
            }
            array_multisort($relativeNames, SORT_ASC, $relativeUserInfo);//按姓名进行排序
            unset($relativeNames);
            //使用memcache存储信息，要是memcache没有进行存储
            if ($mem) {
                $allBusiness = $mem->get('allBusiness_' . $period);
            }
            //如果memcache中没有进行缓存，那么就到数据库中进行查询
            if (!$allBusiness) {
                //已经存储的额信息
                $hasStoreInfo = $dbLocal->get_results("SELECT LOGIN,NAME,GENRE,TRADE_QUAN,TRADE_QUAN2,TRANSACTION,TRANSACTION2,PROFIT FROM MT4_NEW_COM WHERE PERIOD='{$period}'", ARRAY_A);
                //已经有部分信息存储在本地
                if ($hasStoreInfo) {
                    //提取出所有的账号和所有的姓名
                    $hasStoreLogins = array_map('reset', $hasStoreInfo);//所有已经存储好的用户的账号
                    $hasNoStoreLogins = array_diff($relativeLogins, $hasStoreLogins);//求出差集
                    //存在差集，则还是不进行任何操作
                    if ($hasNoStoreLogins) {
                        $hasNoStoreLoginVal = implode(',', $hasNoStoreLogins);
                      //返回所有用户的利润
                        $hasNoStoreInfo = $dbNRemote->get_results("SELECT login AS LOGIN,
                            COUNT(id) AS TRADE_QUAN,
                            SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS TRADE_QUAN2,
                            SUM(`money`) AS TRANSACTION,
                            SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TRANSACTION2,
                            SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS PROFIT FROM mt4_binary_option_history WHERE login IN($hasNoStoreLoginVal) AND update_time 
                            BETWEEN '{$startTime}' AND '{$endTime}' and status=1 GROUP BY login", ARRAY_A);//初始化利润表（没有剔除无效账号）
                        //所有返回的数组
                        $allBusiness = array_merge($hasStoreInfo, $hasNoStoreInfo);
                    //不存在差集，说明数据库中已经存储了完整的用户列表
                    } else {
                        $allBusiness = $hasStoreInfo;
                        unset($hasStoreInfo);
                    }
                //没有任何信息存储在本地
                } else {
                    $allBusiness = $dbNRemote->get_results("SELECT login AS LOGIN,
                        COUNT(id) AS TRADE_QUAN,
                        SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS TRADE_QUAN2,
                        SUM(`money`) AS TRANSACTION,
                        SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TRANSACTION2,
                        SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS PROFIT FROM mt4_binary_option_history WHERE update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1 GROUP BY login", ARRAY_A);
                }
                if ($mem) {
                    $mem->set('allBusiness_' . $period, $allBusiness, 0, 0);//永久有效
                }
            }
        }
        //所有不为0的利润
        $getProfit = array();
        //所有业务不为0的账号密码
        $getProfitLogins = array();  
        //累计的资金流量
        $allTradeTimes = 0;//累计交易次数（所有） 
        $allTradeTimes2 = 0;//累计交易次数 （去平）
        $allTransaction = 0;//累计交易金额 （所有）
        $allTransaction2 = 0;//累计交易金额 （去平）
        $totalProfit = 0;//累计盈亏金额
        $profitForList = array();
        $loginsForList = array();
        if ($allBusiness) {
            foreach ($allBusiness as $key => $value) {
                //$simLoginArr 在配置文件中配置，是公司自己人员的模拟操作数据
                if (in_array($value['LOGIN'], $relativeLogins) && !in_array($value['LOGIN'], $simLoginArr)) {
                    if ($value['TRADE_QUAN'] > 0) {
                        $businessForList[$value['LOGIN']] = $value;
                        $businessForList[$value['LOGIN']]['NAME'] = $relativeUserInfoAso[$value['LOGIN']]['name'];
                        $loginsForList[] = $value['LOGIN'];
                    }
                    $allTradeTimes += $value['TRADE_QUAN'];//累计交易次数（所有） 
                    $allTradeTimes2 += $value['TRADE_QUAN2'];//累计交易次数 （去平）
                    $allTransaction += $value['TRANSACTION'];//累计交易金额 （所有）
                    $allTransaction2 += $value['TRANSACTION2'];//累计交易金额 （去平）
                    $totalProfit += $value['PROFIT'];//所有累计的利润
                    $getProfit[$value['LOGIN']] = $value;//展示在页面中的利润报表
                    $getProfitLogins[] = $value['LOGIN'];//所有本次展示的用户的账号
                }
            }
        }
        
        $profitForList = $getProfit; //列出所有的利润表等于默认的所有利润表
        $loginsForList = $getProfitLogins;//所有列出的账号

    //二、根据指定的登录账号进行查询
    } else if ($searchLogin != '') {
        //所查询的月份是当前的月份，未进行信息储存
        if ($month == $nowMonth) {
            if ($mem) {
                $relativeUserInfo = $mem->get('allUserInfo_' . $period);
            } 
            if (!$relativeUserInfo) {
                $relativeUserInfo = $dbNRemote->get_results("SELECT login,agent_account,name FROM mt4_account", ARRAY_A);
                $relativeLogins = array_map('reset', $relativeUserInfo);
                $relativeUserInfoAso = array_combine($relativeLogins, $relativeUserInfo);//把键名变成帐号
                if ($mem) {
                    $mem->set('allUserInfo_' . $period, $relativeUserInfo, 0, 60);//60秒失效更新
                }
            }
            if ($mem) {
                $businessForList = $mem->get('businessForList_' . $searchLogin . '_' . $period);
            }
            if (!$businessForList) {
                $businessForList1 = $dbNRemote->get_row("SELECT
                    COUNT(id) AS TRADE_QUAN,
                    SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS TRADE_QUAN2,
                    SUM(money) AS TRANSACTION,
                    SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TRANSACTION2,
                    SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS PROFIT 
                    FROM mt4_binary_option_history WHERE login={$searchLogin} AND update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1", ARRAY_A);
                $businessForList1['LOGIN'] = $searchLogin;
                $businessForList1['NAME'] = $relativeUserInfoAso[$searchLogin]['name'];
                $businessForList[] = $businessForList1;
                if ($mem) {
                    $mem->set('businessForList_' . $searchLogin . '_' . $period, $businessForList, 0, 60);//60秒一更新 
                }
            }
        //所查询的月份和当前月份不同
        } else {
            if ($mem) {
                $relativeUserInfo = $mem->get('allUserInfo_' . $period);
            }
            if (!$relativeUserInfo) {
                $relativeUserInfo = $dbNRemote->get_results("SELECT login,agent_account,name FROM mt4_account", ARRAY_A);
                if ($mem) {
                    $mem->set('allUserInfo_' . $period, $relativeUserInfo, 0, 0);
                }
            }
            $relativeLogins = array_map('reset', $relativeUserInfo);
            $relativeUserInfoAso = array_combine($relativeLogins, $relativeUserInfo);//把帐号当作键名
            $relativeNames = array_map('end', $relativeUserInfo);
            array_multisort($relativeNames, SORT_ASC, $relativeUserInfo);//进行排序
            unset($relativeNames);
            //缓存查询
            if ($mem) {
                $businessForList = $mem->get('businessForList_' . $searchLogin . '_' . $period);
            }
            if (!$businessForList) {
                //本地查询
                $businessForList = $dbLocal->get_results("SELECT LOGIN,NAME,GENRE,TRADE_QUAN,TRADE_QUAN2,TRANSACTION,TRANSACTION2,PROFIT FROM MT4_NEW_COM WHERE LOGIN={$searchLogin} AND PERIOD='{$period}'", ARRAY_A);
                //远程查询
                if (!$businessForList) {

                    $businessForList1 = $dbNRemote->get_row("SELECT
                        COUNT(id) AS TRADE_QUAN,
                        SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS TRADE_QUAN2,
                        SUM(money) AS TRANSACTION,
                        SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TRANSACTION2,
                        SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS PROFIT 
                        FROM mt4_binary_option_history WHERE login={$searchLogin} AND update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1", ARRAY_A);
                    $businessForList1['LOGIN'] = $searchLogin;
                    $businessForList1['NAME'] = $relativeUserInfoAso[$searchLogin]['name'];
                    $businessForList[] = $businessForList1;
                }
                if ($mem && $businessForList) {
                    $mem->set('businessForList_' . $searchLogin . '_' . $period, $businessForList ,0 ,0);//设置成永久有效
                }
            }
        }
        //缓存查询
        //计算团队交易额(团队的账号,不包括自己)
        global $resultlll;
        $resultlll = null;
        //查询所有的账号
        $teamLogins = queryLowerLogin($relativeUserInfo, $searchLogin);
          //存在团队
        if ($teamLogins) {
            $teamLoginsValue = implode(',', $teamLogins);
            if ($month == $nowMonth) {
                if ($mem) {
                    $teamTranAndPro = $mem->get('teamTranAndPro_' . $searchLogin . '_' . $period);
                }
                if (!$teamTranAndPro) {
                    $teamTranAndPro = $dbNRemote->get_row("SELECT 
                        SUM(`money`) AS TEAMTRANSACTION,
                        SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TEAMTRANSACTION2,
                        SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS TEAMPROFIT FROM mt4_binary_option_history WHERE login IN($teamLoginsValue) AND update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1", ARRAY_A);
                    if ($mem) {
                        $mem->set('teamTranAndPro_' . $searchLogin . '_' . $period, $teamTranAndPro, 0, 60);//实时数据60秒更新
                    }
                }
            //非本月
            } else {
                if ($mem) {
                    $teamTranAndPro = $mem->get('teamTranAndPro_' . $searchLogin . '_' . $period);
                }
                if (!$teamTranAndPro) {
                    $teamTranAndPro1 = $dbLocal->get_row("SELECT 
                        SUM(TRANSACTION) AS TEAMTRANSACTION,
                        SUM(TRANSACTION2) AS TEAMTRANSACTION2,
                        SUM(PROFIT) AS TEAMPROFIT,
                        COUNT(DISTINCT LOGIN) AS USERS_QUAN FROM MT4_NEW_COM WHERE LOGIN IN($teamLoginsValue) AND PERIOD='{$period}'", ARRAY_A);

                    if ($teamTranAndPro1['USERS_QUAN'] == count($teamLogins)) {
                        $teamTranAndPro = $teamTranAndPro1;
                    } else {
                        $teamTranAndPro = $dbNRemote->get_row("SELECT 
                            SUM(`money`) AS TEAMTRANSACTION,
                            SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TEAMTRANSACTION2,
                            SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS TEAMPROFIT FROM mt4_binary_option_history WHERE login IN($teamLoginsValue) AND update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1", ARRAY_A);
                    }
                    if ($mem) {
                        $mem->set('teamTranAndPro_'.$searchLogin.'_'.$period , $teamTranAndPro, 0, 0);//过去的
                    }
                }
            }
          //暂时不存在团队
        } else {
            $teamTranAndPro = array('TEAMTRANSACTION'=>0, 'TEAMPROFIT'=>0);
        }
    }
    //获取本月的代理帐号
    if ($month == $nowMonth) {
        //获取代理账号
        //查询单个用户
        if ($searchLogin) {
            $adminAgentLogins = getAgentLogins(array($searchLogin));
        //批量查询
        } else {
            $adminAgentLogins = getAgentLogins($relativeLogins);//$adminLogins在配置文件中获取，是登录者所能查看的所有用户,获取所有的代理
        } 
    //获取以往月份的代理帐号
    } else {
        //先查询memcache看是否有缓存
        if ($mem) {
            $adminAgentLogins = $mem->get('adminAgentLogins_' . $period);//如果user-list中进行过调整，就删除此缓存变量
        }
        //如果没有获得缓存代理帐号
        if (!$adminAgentLogins) {
            if ($searchLogin) {
                $adminAgentLogins = getAgentLogins(array($searchLogin));
            //管理员查询所有用户
            } else {
                $adminAgentLogins = getAgentLogins($relativeLogins);//是登录者所能查看的所有用户,获取所有的代理
                if ($mem) {
                    $mem->set('adminAgentLogins_' . $period, $adminAgentLogins, 0 ,0);
                }
            } 
        //缓存中存在代理信息帐号
        } else {
            if ($searchLogin) {
                $adminAgentLogins = in_array($searchLogin, $adminAgentLogins) ? array($searchLogin) : array();
            } else {
                $adminAgentLogins = $adminAgentLogins;
            }
        }
    }
    //查询运营业务量
    if ($mem) {
        $no_profit_login = $mem->get('noProfitLogin_' . $period);
    }
    if (!$no_profit_login) {
        $no_profit_login = $dbLocal->get_var("SELECT LOGINS FROM MT4_NEW_NOPROFIT WHERE `PERIOD`='{$period}'");
    }
    $no_profit_login = trim(unserialize($no_profit_login));//反序列化
    if ($no_profit_login) {
        preg_match_all("/[^\s]+/s" ,$no_profit_login ,$no_profit_loginArr);//PREG_PATTERN_ORDER和PREG_SET_ORDER参数，前一个参数是默认的，第0个元素是匹配到的所有的结果(多维数组)，第一个元素是第一次匹配．
        //PREG_SET_ORDER参数，没有如上的匹配出的所有元素组成的数组

        $noProfitLogins = $no_profit_loginArr[0];
    }
    if ($noProfitLogins) {
        $invalidLoginStr = implode(',', $noProfitLogins);
        //查询本月的无效利润
        if ($month == $nowMonth) {
            if ($mem) {
                $invalidProfit = $mem->get('invalidProfit_'.$period);
            }
            if (!$invalidProfit) {
                $invalidProfit = $dbNRemote->get_var("SELECT SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) FROM mt4_binary_option_history WHERE login IN($invalidLoginStr) AND update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1");//无效的利润数组
                if ($mem) {
                    $mem->set('invalidProfit_' . $period, $invalidProfit, 0, 60);//60秒，缓解数据库压力
                }
            }
        } else {
            if ($mem) {
                $invalidProfit = $mem->get('invalidProfit_' . $period);
            }
            if (!$invalidProfit) {
                $invalidProfitArr = $dbLocal->get_row("SELECT SUM(PROFIT) AS PROFIT,COUNT(DISTINCT LOGIN) AS USERS_QUAN FROM MT4_NEW_COM WHERE LOGIN IN($invalidLoginStr) AND PERIOD = '{$period}'",ARRAY_A);
                if ($invalidProfitArr['USERS_QUAN'] == count($noProfitLogins)) {
                    $invalidProfit = $invalidProfitArr['PROFIT'];
                } else {
                    $invalidProfit = $dbNRemote->get_var("SELECT SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) FROM mt4_binary_option_history WHERE login IN($invalidLoginStr) AND update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1");//无效的利润数组
                }
                if ($mem) {
                    $mem->set('invalidProfit_' . $period, $invalidProfit, 0, 0);//过去的设置成永久有效
                }
            }
        }
    }
    //公司的利润
    $companyProfit = -($totalProfit - $invalidProfit);
    //导出报表的操作
    //下载利润报表
    //不存在业务报表时，则不予以下载
    if ($download && $businessForList) {
        require_once 'lib/PHPExcel/PHPExcel.php';  
        $objPHPExcel = new PHPExcel();  
        foreach ($businessForList as $key => $businessForListV) {
            $newProfit[$key][] = $businessForListV['LOGIN'];
            $newProfit[$key][] = $businessForListV['NAME'];
            $newProfit[$key][] = in_array($businessForListV['LOGIN'], $adminAgentLogins) ? $agentName : $tradeName;
            $newProfit[$key][] = $businessForListV['TRADE_QUAN'];
            $newProfit[$key][] = $businessForListV['TRADE_QUAN2'];
            $newProfit[$key][] = $businessForListV['TRANSACTION'];
            $newProfit[$key][] = $businessForListV['TRANSACTION2'];
            $newProfit[$key][] = $businessForListV['PROFIT'];
        }
        $filename = date('Y').'年'.$month.'月业务报表';//报表名称
        //导出excel文件 
        $excel = new PHPExcel();
        //Excel表格式
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        //表头数组
        $tableheader = array('账号','姓名','用户身份','交易次数(总计)','交易次数(去平后)','累计资金流量(总计)','累计资金流量(去平后)','累计盈亏金额');    
        for ($i = 0;$i < count($tableheader);$i++) {    
            $excel->getActiveSheet()->setCellValue($letter[$i] . '1', $tableheader[$i]);
        }    
        $i = 2;
        $j = 0;
        // //填充表格信息
        foreach ($newProfit as $key => $value) {
            foreach ($value as $k => $v) {
                $excel->getActiveSheet()->setCellValue("$letter[$k]" . ($j + 2), "$v");
            }
            $i++;
            $j++;
        }
        $excel->getActiveSheet()->setCellValue('A' . $i, "备注：金额单位($)。");
        $excel->getActiveSheet()->mergeCells("A" . $i . ":" . $letter[count($tableheader)-1] . $i);
        $write = new PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header("Content-Disposition:attachment;filename='{$filename}.xls'");
        header("Content-Transfer-Encoding:binary");
        $write->save('php://output');
    }
//退出登录的操作
} else if ($_GET['action'] == 'logout') {
    session_destroy();
    setcookie ('login', "", time() - 3600);
    header("Location:{$erpurl}");
}
