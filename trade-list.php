<?php 
  //为了提高速度，此处采用60秒的延迟,所有需要延迟信息的都是统一采用此变量
if ($mem) {
    $allUserInfo = $mem->get('allUserInfoRealTime');
}
if (!$allUserInfo) {
    //此处并没有好办法使用缓存
    $allUserInfo = $dbNRemote->get_results("SELECT login,agent_account,name FROM mt4_account", ARRAY_A);//$validGroupStr在config.php中配置//注意：如果有下级的用户没有被设置成代理。那会计算结果会出错
    if ($mem) {
        $mem->set('allUserInfoRealTime', $allUserInfo, 0, 60);//此处设置60秒的会在一定程度上减少数据库的连接
    }
}
$loginLogin = $_SESSION['login']=='admin' ? 0 : $_SESSION['login'];//管理员的$_SESSION['login']=='admin'
$topLogin = $_POST['search_login1'] ? $_POST['search_login1'] : $loginLogin;//如果不进行手动查询，则查询的是默认登录者
$adminUserInfo = array();//所有能够管理的账户的详细信息
$adminLogins = array();//所有管理的账号
$firLowerLogins = array(); //第一级下级的账号

//管理员登录并且不进行任何查询
//管理员登录
if ($_SESSION['login'] == 'admin') {
    $relativeUserInfo = $allUserInfo;
    //不进行查询
    if ($topLogin == 0) {
        foreach($allUserInfo AS $value) {
            $adminLogins[] = $value['login'];
            $adminUserInfo[$value['login']] = $value;
            if ($value['agent_account'] == $topLogin) {
                $firLowerLogins[] = $value['login'];
            }
        }
    //进行查询
    } else {
        global $resultlll;
        $resultlll = null;
        $adminLogins = queryLowerLogin($allUserInfo, $topLogin);//查询出所有和登录者有关的账号(并不包括其自己)
        $topTeamLogins = $adminLogins;//该用户的顶尖团队成员
        if ($adminLogins) {
          array_unshift($adminLogins, $topLogin);
        } else {
          $adminLogins = array($topLogin);
        }
        
        foreach ($allUserInfo as $key => $value) {
            if (in_array($value['login'], $adminLogins)) {
                $adminUserInfo[$value['login']] = $value;
            }
            if ($value['agent_account'] == $topLogin) {
                $firLowerLogins[] = $value['login'];
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
        $topTeamLogins = $adminLogins;
        if ($adminLogins) {
            array_unshift($adminLogins, $_SESSION['login']);
        } else {
            $adminLogins = array($_SESSION['login']);
        }
        foreach ($allUserInfo as $key => $value) {
            if (in_array($value['login'], $adminLogins)) {
                $adminUserInfo[$value['login']] = $value;
                $relativeUserInfo[$value['login']] = $value;
            }
            if ($value['agent_account'] == $topLogin) {
                $firLowerLogins[] = $value['login'];
            }
        }

    } else {
        global $resultlll;
        $resultlll = null;
        $adminLogins = queryLowerLogin($allUserInfo, $topLogin);//查询出所有和登录者有关的账号(并不包括其自己)
        $topTeamLogins = $adminLogins;
        if ($adminLogins) {
            array_unshift($adminLogins, $topLogin);//插入数组头部
        } else {
            $adminLogins = array($topLogin);
        }
        global $resultlll;
        $resultlll = null;
        $relativLogins = queryLowerLogin($allUserInfo, $_SESSION['login']);//查询出所有和登录者有关的账号(并不包括其自己)
        if ($relativLogins) {
          array_unshift($relativLogins, $_SESSION['login']);
        } else {
          $relativLogins = array($_SESSION['login']);
        }
          
        foreach ($allUserInfo as $key => $value)  {
            //与本次计算相关的信息
            if (in_array($value['login'], $adminLogins)) {
                $adminUserInfo[$value['login']] = $value;
            }
            //与登陆者相关的信息
            if (in_array($value['login'], $relativLogins)) {
                $relativeUserInfo[] = $value;
            }
            //第一级下级
            if ($value['agent_account'] == $topLogin) {
                $firLowerLogins[] = $value['login'];
            }
        }
    }
}

$relativNames = array_map('end', $relativeUserInfo);//取出姓名
array_multisort($relativNames,SORT_ASC,$relativeUserInfo);//根据姓名进行排序
unset($relativNames);
$todayEndTime = date('Y-m-d 23:59:59');//今天结束时间

//查询的开始和结束时间
//如果没有传递开始和结束时间，则默认从当月开始查
if (!$_POST['startDate']) {
    $startDate = date('Y-m-01');//本月第一天
    $endDate = date('Y-m-d');//截止到今天
//手动查询，有开始和结束时间
} else {
    $dataRangeLable = $_POST['dataRangeLable'];//设置变量的作用是，让显示页面的下拉菜单停留在相应的位置
    $startDate = $_POST['startDate'];//查询开始的时间
    $endDate = $_POST['endDate'];//查询结束的时间
} //ok
$startTime = date('Y-m-d 00:00:00', strtotime($startDate));   //查询的开始时间
$endTime = date('Y-m-d 23:59:59', strtotime($endDate));       //查询结束的时间
if ($mem) {
    $everyTradeInfo = $mem->get('everyTradeInfo_' . $startTime . '_' . $endTime . '_' . $topLogin);
}
if (!$everyTradeInfo) {
    if (count($adminLogins) < 300) {
        $adminLoginsV = implode(',', $adminLogins);
        $everyTradeInfo = $dbNRemote->get_results("SELECT login AS LOGIN,
            COUNT(id) AS TRADE_QUAN,
            SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS TRADE_QUAN2,
            SUM(`money`) AS TRANSACTION,
            SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TRANSACTION2,
            SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS PROFIT FROM mt4_binary_option_history WHERE login IN($adminLoginsV) AND update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1 GROUP BY login", ARRAY_A);  

    //人数多时，牺牲性能全部查询出来
    } else {
        //获取每个人的交易数据
        $everyTradeInfo = $dbNRemote->get_results("SELECT login AS LOGIN,
            COUNT(id) AS TRADE_QUAN,
            SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS TRADE_QUAN2,
            SUM(`money`) AS TRANSACTION,
            SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TRANSACTION2,
            SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS PROFIT FROM mt4_binary_option_history WHERE update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1 GROUP BY login", ARRAY_A);
    }
    if ($mem) {
        if ($endTime == $todayEndTime) {
            $mem->set('everyTradeInfo_' . $startTime . '_' . $endTime . '_' . $topLogin, $everyTradeInfo, 0, 20);//今天的交易信息需要实时更新
        } else {
            $mem->set('everyTradeInfo_' . $startTime . '_' . $endTime . '_' . $topLogin, $everyTradeInfo, 0, 1800);//往日的交易信息则不需要实时更新？？？？？用户进行了调整怎么办，故而时间不宜太久
        }
    }
}
//获取所有的代理的返点比率
$agentsRatios = getAgentRatios($adminLogins);                 //获取所有代理的返点比率 
//说明存在代理
if ($agentsRatios) {
    $agentLogins = array();//所有代理的账号组成的一维数组
    $firLowerTeamLogins = array();//第一级下级代理的团队账号组成的二位数组
    foreach ($agentsRatios as $key => $value) {
        $agentLogins[] = $key;                                        //所有的代理机构代码
        if ($adminUserInfo[$key]['agent_account'] == $topLogin) {
            global $resultlll;
            $resultlll = null;
            $firLowerTeamLogins[$key] = queryLowerLogin($adminUserInfo,$key); //查询出所有和登录者有关的账号(并不包括其自己)
        }
    }
}

if ($everyTradeInfo) {
    $everyTradeInfo2 = array();//把键名变成login账号
    $firstLowerLogins = array();//第一级下级的账号
    //循环每一个人的交易额
    foreach ($everyTradeInfo as $everyTradeInfoV) {  
        $everyTradeInfo2[$everyTradeInfoV['LOGIN']] = $everyTradeInfoV;
        //所查询用户的团队交易额
        if (in_array($everyTradeInfoV['LOGIN'],$topTeamLogins)) {
            $topTeamTran+=$everyTradeInfoV['TRANSACTION2'];
        }
        //所有代理的直接下级的交易额之和
        $upLogin = $adminUserInfo[$everyTradeInfoV['LOGIN']]['agent_account'];    

        //必须存在该上级，即符合条件
        if ($upLogin) {
            $everAgentFirLowTran[$adminUserInfo[$everyTradeInfoV['LOGIN']]['agent_account']] += $everyTradeInfoV['TRANSACTION2'];
        }
    }
    $pathArr = getPathArr($agentLogins, $adminUserInfo);          //路径信息
    $currentCommission = getCurrentCommission($pathArr, $agentsRatios, $everAgentFirLowTran, $currentRatio);//计算出所有代理的实时返佣
}
unset($everyTradeInfo);
unset($pathArr);
unset($agentsRatios);
unset($everAgentFirLowTran);
//只要不是管理员登录($topLogin = 0)，首行都必须显示顶级的用户信息
  
if ($topLogin) {
    $topInfo = array();
    $topInfo['LOGIN'] = $topLogin;
    $topInfo['NAME'] = $adminUserInfo[$topLogin]['name'];
    $topInfo['TRADE_QUAN'] = $everyTradeInfo2[$topLogin]['TRADE_QUAN'];//本人去平前的交易量 
    $topInfo['TRADE_QUAN2'] = $everyTradeInfo2[$topLogin]['TRADE_QUAN2'];//本人去平后的交易量    
    $topInfo['TRANSACTION'] = $everyTradeInfo2[$topLogin]['TRANSACTION'];//本人去平前的交易金额
    $topInfo['TRANSACTION2'] = $everyTradeInfo2[$topLogin]['TRANSACTION2'];//本人去平后的交易金额
    $topInfo['PROFIT'] = $everyTradeInfo2[$topLogin]['PROFIT'];        //累计盈利
    if (in_array($topLogin, $agentLogins)) {
        $topInfo['TEAMTRAN'] = $topTeamTran;                                  //团队交易额
        $topInfo['COMTRAN'] = $currentCommission[$topLogin]['TRAN'];          //返佣交易额
        $topInfo['COM'] = $currentCommission[$topLogin]['COM'];               //返佣交易额
    }
}
//返回的直属下级结果值
$returnResult = array();
//计算第一级下级的代理的交易
if ($firLowerLogins) {
    foreach ($firLowerLogins as $firLowerLogin) {
        $returnResult[$firLowerLogin]['LOGIN'] = $firLowerLogin;//账号
        $returnResult[$firLowerLogin]['NAME'] = $adminUserInfo[$firLowerLogin]['name'];//姓名
        $returnResult[$firLowerLogin]['TRADE_QUAN'] = $everyTradeInfo2[$firLowerLogin]['TRADE_QUAN'];//本人去平前的交易量 
        $returnResult[$firLowerLogin]['TRADE_QUAN2'] = $everyTradeInfo2[$firLowerLogin]['TRADE_QUAN2'];//本人去平后的交易量    
        $returnResult[$firLowerLogin]['TRANSACTION'] = $everyTradeInfo2[$firLowerLogin]['TRANSACTION'];//本人去平前的交易金额
        $returnResult[$firLowerLogin]['TRANSACTION2'] = $everyTradeInfo2[$firLowerLogin]['TRANSACTION2'];//本人去平后的交易金额
        $returnResult[$firLowerLogin]['PROFIT'] = $everyTradeInfo2[$firLowerLogin]['PROFIT'];//本人去平后的交易金额//本人的盈亏之和
        //直属下级团队交易
        //该直属下级是代理
        if (array_key_exists($firLowerLogin, $firLowerTeamLogins)) {
            if ($firLowerTeamLogins[$firLowerLogin]) {
                foreach ($firLowerTeamLogins[$firLowerLogin] as $kkk)  {
                    $returnResult[$firLowerLogin]['TEAMTRAN'] += $everyTradeInfo2[$kkk]['TRANSACTION2'];
                }
            } else {
                $returnResult[$firLowerLogin]['TEAMTRAN'] = 0;
            }
            $returnResult[$firLowerLogin]['COMTRAN'] = $currentCommission[$firLowerLogin]['TRAN'];
            $returnResult[$firLowerLogin]['COM'] = $currentCommission[$firLowerLogin]['COM'];//本人去平后的交易金额//本人的盈亏之和
        //该直属下级是一个交易者
        } else {
            $returnResult[$firLowerLogin]['TEAMTRAN'] = '';//团队交易额
            $returnResult[$firLowerLogin]['COMTRAN'] = '';//返佣交易额
            $returnResult[$firLowerLogin]['COM'] = '';//本人去平后的交易金额//本人的盈亏之和
        }
    }
    //消除变量，节约内存
    unset($everyTradeInfo2);
    unset($currentCommission);
    unset($firLowerLogins);
}
?>
<div class="content-body">
  <div class="content-title">实时交易统计</div>
  <!-- 区段选择 -->
  <div class="col-lg-12">
    <div class="alert alert-danger" role="alert"> 
    1、本页面分级呈现，如需查看相关用户的直属下级，点击所在行即可;<br>
    <?=$topLogin?'':'2、最顶级用户（没有上级）,产生的交易量不会产生佣金;'?>
      <form action="" method="post" style="margin-top:10px;">
        <div id="search_login">
          <input type="text" class="form-control" id="search_login2" placeholder="账号或姓名" value="<?php if (in_array($topLogin,$adminLogins)){ echo $topLogin; } else{ echo ''; }?>" style="width:103px;"/> 
          <select class="form-control" id="search_login1" name="search_login1" style="margin-left:108px;width:182px;">
              <option value="">--顶端用户--</option>
              <?php foreach ($relativeUserInfo as $key => $relativeUserInfoV) { ?>
                <option value="<?php echo $relativeUserInfoV['login'];?>" <?php if ($topLogin == $relativeUserInfoV['login']){ echo 'selected'; } ?>><?php echo $relativeUserInfoV['name'];?></option>
              <?php } ?>
          </select>
        </div> 
        <input type="hidden" value="<?=$dataRangeLable?>" id="dataRangeLable" name="dataRangeLable">
        <div class="form-inline">
          <div class="form-group">
            <select id="dateRange" name="dateRange" class="form-control">
              <option value="month">本月</option>
              <option value="week">本周</option>
              <option value="lastWeek">上周</option>
              <option value="today">当日</option>
              <option value="yesterday">上日</option>
              <option value="manual">手动选择</option>
            </select>
          </div>
          <div class="input-group">
            <span class="input-group-addon">起始</span>
            <input type="date" class="form-control"  id="startDate"  name="startDate" value="<?=$startDate?>">
            <span class="input-group-addon">结束</span>
            <input type="date" class="form-control"  id="endDate"  name="endDate" value="<?=$endDate?>">
          </div>
          <div class="form-group">
            <button class="btn example-btn" type="submit">查看统计</button>
          </div>
        </div>
      </form>
    </div> 
  </div>
  <div class="col-lg-12">
<table class="table table-hover table-striped">
      <thead>
        <tr style="font-size:1.6rem;">
          <th align="center"><nobr>序号</nobr></th>
          <th align="center"><nobr>账户</nobr></th>
          <th align="center"><nobr>姓名</nobr></th><!-- 用户账号 -->
          <th align="center"><nobr>本人交易量</nobr></th><!-- 用户分组 -->
          <th align="center"><nobr>本人交易金额</nobr></th>
          <th align="center"><nobr>团队(去平后)/返佣交易量</nobr></th>
          <th align="center"><nobr>累计盈利</nobr></th>
          <th align="center"><nobr>产生佣金</nobr></th>
          <th align="center"><nobr>实时返佣</nobr></th>
          <th align="center"><nobr>查看交易详情</nobr></th>
        </tr>
      </thead>
      <tbody id="trade-list">
<!-- 首行显示顶级用户的信息 -->

<?php if ($topLogin){ ?>
        <tr style="background:#F2DEDE;">
          <td>顶级用户</td>
          <td><?=$topInfo['LOGIN']?></td><!-- 账户 -->
          <td>
            <nobr>
              <span class="label label-primary"><?=$topInfo['NAME']?></span>
              <?php if (in_array($topInfo['LOGIN'], $agentLogins)){echo "<span class=\"label label-success\">".$agentName."</span>";} else{echo "<span class=\"label label-default\">".$tradeName."</span>";}?>
            </nobr>
          </td>
          <td>
            <nobr>
              <span class="label label-primary"><?=number_format($topInfo['TRADE_QUAN'])?></span><!-- 交易量 -->
              <span class="label label-success"><?=number_format($topInfo['TRADE_QUAN2'])?></span><!-- 去平后交易量 -->
            </nobr>
          </td>
          <td><!-- 交易量VOLUM/100 -->
            <nobr>
              <span class="label label-primary">$<?=number_format($topInfo['TRANSACTION'], 2)?></span><!-- 交易金额 -->
              <span class="label label-success">$<?=number_format($topInfo['TRANSACTION2'], 2)?></span><!-- 去平后交易金额 -->
            </nobr>
          </td>
          <td>
            <nobr>
              <?php if (in_array($topLogin, $agentLogins)){ ?>
              <span class="label label-primary">$<?=number_format($topInfo['TEAMTRAN'], 2)?></span><!-- 团队交易金额 -->
              <span class="label label-success">$<?=number_format($topInfo['COMTRAN'], 2)?></span> <!-- 返佣交易金额 -->
              <?php } else{ echo '/'; }?>
            </nobr>
          </td>
          <td>
            $<?=number_format($topInfo['PROFIT'], 2)?><!-- 累计利润 -->                                    
          </td>
          <!--产生的佣金-->
          <td>
            <?=$adminUserInfo[$topInfo['LOGIN']]['agent_account']==0?'/':'$'.number_format($topInfo['TRANSACTION2']/100*$currentRatio,2)?>
          </td>
          <!-- 即时返佣 -->
          <td>
          <?php 
            if (in_array($topInfo['LOGIN'], $agentLogins))
            {
              echo '$',number_format($topInfo['COM'],2);
            }
            else
            {
              echo '/';
            }?>
          </td>
          <td name="showDetail" onclick="showDetail(<?=$topInfo['LOGIN']?>)">
            查看详情
          </td>
        </tr>
<?php } ?>
<?php
    $totalQuan = 0;//累计交易量
    $totalQuan2 = 0;//去平后累计交易量
    $totalTransaction = 0;//累计交易金额
    $totalTransaction2 = 0;//去平后累计交易金额
    $totalProfit = 0;//累计利润和
    $i = 0;//序号
  foreach ($returnResult as $key=>$value) 
  { 
    $i++; 
    $totalQuan += $value['TRADE_QUAN'];//累计交易量
    $totalQuan2 += $value['TRADE_QUAN2'];//去平后累计交易量
    $totalTransaction += $value['TRANSACTION']/100;//累计交易金额
    $totalTransaction2 += $value['TRANSACTION2']/100;//去平后累计交易金额
    $totalProfit += $value['PROFIT'];//累计利润和
?>
        <tr onclick="hidePopup(<?=$value['LOGIN']?>);nextGenre(<?=$value['LOGIN']?>,<?=$value['LOGIN']?>,1)" data-toggle="tooltip" data-placement="bottom" title="点击查看下级和即时返佣" id="first_<?=$value['LOGIN']?>">
          <td><?=$i?></td>
          <td><?=$value['LOGIN']?></td><!-- 账户 -->
          <td>
            <span class="label label-primary"><?=$value['NAME']?></span>
            <?php if (in_array($value['LOGIN'], $agentLogins)){echo "<span class=\"label label-success\">".$agentName."</span>";} else{echo "<span class=\"label label-default\">".$tradeName."</span>";}?>
          </td><!-- 姓名 -->
          <td><!-- 交易多少笔 -->
            <span class="label label-primary"><?=number_format($value['TRADE_QUAN'])?></span><!-- 交易量 -->
            <span class="label label-success"><?=number_format($value['TRADE_QUAN2'])?></span><!-- 去平后交易量 -->
          </td>
          <td><!-- 交易量VOLUM/100 -->
            <span class="label label-primary">$<?=number_format($value['TRANSACTION'],2)?></span><!-- 交易金额 -->
            <span class="label label-success">$<?=number_format($value['TRANSACTION2'],2)?></span><!-- 去平后交易金额 -->
          </td>
          <td>
            <?php if (in_array($value['LOGIN'], $agentLogins)){ ?>
            <span class="label label-primary">$<?=number_format($value['TEAMTRAN'],2)?></span><!-- 团队交易金额 -->
            <span class="label label-success">$<?=number_format($value['COMTRAN'],2)?></span>
            <?php } else{ echo '/'; }?>
          </td>
          <td>
            $<?=number_format($value['PROFIT'],2)?><!-- 累计利润 -->                                    
          </td>
          <!--奉献上级的佣金-->
          <td>
            <?=$topLogin==0?'/':'$'.number_format($value['TRANSACTION2']*$currentRatio,2)?>
          </td>
          <!-- 即时返佣 -->
          <td>
          <?php 
            if ($value['COM'] !== '')
            {
              echo '$',number_format($value['COM'],2);
            }
            else
            {
              echo "/";
            }?>
          </td>
          <td name="showDetail" onclick="showDetail(<?=$value['LOGIN']?>)">
            查看详情
          </td>
        </tr>
<?php } ?>

<!-- 累计栏 -->
        <tr>
          <td>直属合计</td>
          <td></td>
          <td></td>
          <td>
            <span class="label label-primary"><?=$totalQuan?></span>
            <span class="label label-success"><?=$totalQuan2?></span>
          </td>
          <td><!-- 交易量-->
            <span class="label label-primary">$<?=number_format($totalTransaction,2)?></span>
            <span class="label label-success">$<?=number_format($totalTransaction2,2)?></span>
          </td>
          <td></td>
          <td>
            $<?=number_format($totalProfit,2)?>
          </td>
          <td></td>
          <td></td>
          <td></td>
        </tr>
<!-- 备注栏 -->
        <tr>
          <td colspan="10">
            备注：1、<span class="label label-primary">总计</span>&nbsp;所有数据累积;&nbsp;&nbsp;&nbsp;&nbsp;<span class="label label-success">去平后</span>&nbsp;除去持平的交易记录。2、『团队/返佣交易量』一栏中团队交易量值为去平后的有效值。
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div> 
<script type="text/javascript">
  /*比较日期大小*/
  function checkEndTime()
  {  
    var start=new Date(startTime.replace("-", "/").replace("-", "/"));  
    var end=new Date(endTime.replace("-", "/").replace("-", "/"));  
    if (end<start)
    {  
      return false;  
    }  
    return true;  
  }  
//根据数字返回空格
  function blankByNum(num)
  {
    var blank = '';
    for(var i=1;i<=num;i++)
    {
      blank+='&nbsp;&nbsp;&nbsp;&nbsp;';
    }
    return blank;
  }
//金额格式化,保留两位小数
  function formatCurrency(s) 
  {    
     var n = 2;   
     s = parseFloat((s + "").replace(/[^\d\.-]/g, "")).toFixed(n) + "";   
     var l = s.split(".")[0].split("").reverse(),   
     r = s.split(".")[1];   
     t = "";   
     for(i = 0; i < l.length; i ++)   
     {   
        t += l[i] + ((i + 1) % 3 == 0 && (i + 1) != l.length ? "," : "");   
     }   
     return t.split("").reverse().join("") + "." + r;     
  }  
  $(document).ready(function (){
    //手动输入框获得焦点，则下拉菜单没有值
    $('#search_login2').focus(function () 
    {
      $('#search_login1').val(''); 
    });
    
    //手动输入代码结束以后，下拉菜单跳转到相应的值
    $('#search_login2').blur(function ()
    {
      var manualAgency = $.trim($('#search_login2').val());//手动输入的机构代码(去除空格)
      //输入的是数字
      if (!isNaN(manualAgency))
      {
        var re = new RegExp("[0-9]*"+manualAgency+"[0-9]*");//以特定账号开头进行匹配
        $('#search_login1 option').each(function (i) 
        {
          if (re.test($(this).val()))
          {
            $(this).prop('selected',true);//下拉框选中相应的选项（切记以后不要用attr,因为只能使用一次）
            $('#search_login2').val($(this).val());//输入框中变成账号
            return false;
          }
        });
      }
      //输入的不是数字（即输入的是文字）
      else if (isNaN(manualAgency) && manualAgency != '') 
      {
        var re = new RegExp("[\u4e00-\u9fa5]*"+manualAgency+"[\u4e00-\u9fa5]*","g");//匹配任意中文
        $('#search_login1 option').each(function (i)
        {
          if (re.test($(this).text()))//匹配成功
          {
            $(this).prop('selected',true);//下拉框选中相应的选项（切记以后不要用attr,因为只能使用一次）
            $('#search_login2').val($(this).val());//输入框中变成账号
            return false;
          }
        });
      }
      //匹配失败
      if ($('#search_login1').val()=='')
      {
        alert('您输入的查询信息不正确或您无权限');
        $('#search_login2').val('');
      }
    }); 
    
    //下拉菜单选中的值，在输入框中也应该有所体现
    $('#search_login1').change(function ()
    {
      if ($("#search_login1 option:selected").val()!='')
      {
        $('#search_login2').val($("#search_login1 option:selected").val());
      } else
      {
        $('#search_login2').val('');
      }
    });


    $("td[name='showDetail']").click(function (evtnt)
    {
      event.stopPropagation();//防止子元素和父元素公用的函数一直执行下去
    });


    var dataRangeLable = $("#dataRangeLable").val();
    if (dataRangeLable != '')
    {
      $("#dateRange option[value="+dataRangeLable+"]").attr('selected',true);
    }
    //一旦日期框获得焦点，则下拉菜单值手动
    $('#startDate').focus(function () {
        $("#dateRange option[value='manual']").attr('selected',true);
    });
        //一旦日期框获得焦点，则下拉菜单值手动
    $('#endDate').focus(function () {
        $("#dateRange option[value='manual']").attr('selected',true);
    });

    $("#dateRange").change(function (){
      var dd = new Date();
      var nowYear = dd.getFullYear();//当前的年份
      nowYear += (nowYear < 2000) ? 1900 : 0;
      var nowMonth = formatMandD(dd.getMonth()+1);//当前的月份
      var selected = $("#dateRange option:selected").val();//获取下拉菜单被选中的值
      $("#dataRangeLable").val(selected);//隐藏域
      if (selected == 'month')//本月
      {
        $('#startDate').val(nowYear+'-'+nowMonth+'-01'); //本月开始的时间
        $('#endDate').val(getDateStr(0));//本月结束的时间(到目前为止)
      } else if (selected == 'week')//本周
      {
        $('#startDate').val(getWeekStartDate()); //本周开始的时间（周日开始）
        $('#endDate').val(getDateStr(0));//到目前为止
      } else if (selected == 'lastWeek')//上周
      {
        $('#startDate').val(getLastWeekStartAndEnd()[0]); //上周开始的时间
        $('#endDate').val(getLastWeekStartAndEnd()[1]);//上周结束的时间
      } else if (selected == 'today')//当日
      {
        $('#startDate').val(getDateStr(0)); //今天
        $('#endDate').val(getDateStr(0));//今天
      } else if (selected == 'yesterday')//昨天
      {
        $('#startDate').val(getDateStr(-1)); //开始时间是昨天
        $('#endDate').val(getDateStr(-1));//结束时间是昨天
      } else if (selected=='')
      {
        $('#startDate').val(''); //开始时间是昨天
        $('#endDate').val('');//结束时间是昨天
      }
      $("#trade-list").hide();
      //同时隐藏已经生成的报表
    }); 
    //手动输入日期不能超过上个月
    $('#startDate').blur(
      function (){
        var dd = new Date();
        var nowYear = dd.getFullYear();//当前的年份
        nowYear += (nowYear < 2000) ? 1900 : 0;
        var nowMonth = formatMandD(dd.getMonth()+1);//当前的月份
        var startDate = $('#startDate').val();
        var startDate2= new Date(startDate.replace("-", "/").replace("-", "/"));  
        var endDate = $('#endDate').val();
        var endDate2 = new Date(endDate.replace("-", "/").replace("-", "/"));
        var minDate = nowYear+'-'+nowMonth+'-01';
        var minDate2 = new Date(minDate.replace("-", "/").replace("-", "/"));  
        if (startDate2>endDate2)
        {
          alert('开始时间不能大于结束时间');
          $('#startDate').val(nowYear+'-'+nowMonth+'-01'); //本月开始的时间
        }
<?php if ($_SESSION['login'] != 'admin'){ ?>
        if (startDate2<minDate2)
        {
          alert('手动只能查询本月内的交易记录');
          $('#startDate').val(nowYear+'-'+nowMonth+'-01'); //本月开始的时间
        }
<?php } ?>
      });
    $('#endDate').blur(
      function (){
        var dd = new Date();
        var nowYear = dd.getFullYear();//当前的年份
        nowYear += (nowYear < 2000) ? 1900 : 0;
        var nowMonth = formatMandD(dd.getMonth()+1);//当前的月份
        var startDate = $('#startDate').val();
        var startDate2= new Date(startDate.replace("-", "/").replace("-", "/"));
        var endDate = $('#endDate').val();
        var endDate2= new Date(endDate.replace("-", "/").replace("-", "/"));  
        var minDate = nowYear+'-'+nowMonth+'-01';
        var minDate2 = new Date(minDate.replace("-", "/").replace("-", "/"));  
        if (endDate2<startDate2)
        {
          alert('查询的结束时间不能小于开始时间');
          $('#endDate').val(startDate); //本月开始的时间
        }
<?php if ($_SESSION['login'] != 'admin'){ ?>
        if (endDate2<minDate2)
        {
          alert('手动只能查询本月内的交易记录');
          $('#endDate').val(startDate); //本月开始的时间
        }
<?php } ?>
      });
      
  });
  //bootstrap提示框的必须js代码。下拉提示菜单
  $(function () { $("[data-toggle='tooltip']").tooltip(); });
  //点击查看下级的情况,
  function nextGenre(login,loginForLev,level)
  {
    var startDate = $("#startDate").val();
    var endDate = $("#endDate").val();
    var nextHtml = '';
    //下级
    if ($("tr[name="+loginForLev+"_"+parseInt(level+1)+"]").length == 0) 
    {
      $.ajax({
        type: "POST",
        url: "<?=$url?>ajax/trade-list.ajax.php",
        //data: "LOGIN="+login+"&startDate="+startDate+"&endDate="+endDate,
        data:{"LOGIN":login,"startDate":startDate,"endDate":endDate},
        dataType: 'json',//json类型只接受json类型的数据，否则进入error函数
        success:function (nextInfo)//成功的处理函数
        { 
          if (nextInfo != '')//说明存在该下级存在下级(返回的数组固定格式，两个元素)
          {
            l = 0;//序号
            $.each(nextInfo,function (i,item){
              l++;
              nextHtml+="<tr class= \"popup\" name = \"" + loginForLev + '_' + parseInt(level + 1) + "\" id=\"first_" +  item['LOGIN']  + "\" style=\"background:" 
              + getColorByLevel(level + 1) + ";\" onclick=\"nextGenre(" + item['LOGIN'] + ',' + loginForLev 
              + ',' + parseInt(level + 1) + ")\"><td>" + blankByNum(level) + "└" + (l) + "</td><td>" + item['LOGIN'] + "</td><td><span class=\"label label-primary\">" 
              + item['NAME'] + "</span>&nbsp;" + ifAgent(item['COM']) + "</td><td><span class=\"label label-primary\">" + item['TRADE_QUAN'] 
              + "</span>&nbsp;<span class=\"label label-success\">" + item['TRADE_QUAN2'] 
              + "</span></td><td><span class=\"label label-primary\">$" + formatCurrency(item['TRANSACTION']) 
              + "</span>&nbsp;<span class=\"label label-success\">$" + formatCurrency(item['TRANSACTION2']) + "</span></td><td>" 
              + ifAgent2(item['TEAMTRAN'],item['COMTRAN']) + "</td><td>$" 
              + formatCurrency(item['PROFIT']) + "</td><td id=\"commission_\"" + item['LOGIN'] + ">$" 
              + formatCurrency(item['TRANSACTION2']*<?=$currentRatio?>) + "<td>" 
              + ifComm(item['COM']) + "</td><td onclick=\"showDetail(" + item['LOGIN'] + ")\">查看详情</td></tr>";
            });
          } else
          {
            nextHtml+="<tr class=\"popup\" name=\""+ loginForLev +"_"+parseInt(level+1)+"\" style = \"background:"+getColorByLevel(level+1)+";\"><td colspan=\"10\" style=\"font-style:italic;\" class=\"text-center\">暂无下级</td></tr>";
          }
          $("#first_"+login).after(nextHtml);
        },  
        error: function () 
        {
          alert('加载失败，请稍后再试');
        }
      });
    } else
    {
      $("tr[name^="+loginForLev+"_]").each(function ()
      {
        if ($(this).attr("name").substring($(this).attr("name").indexOf('_')+1) > level)
        {
          $(this).remove();
        }
      });
    }
  }

  //移除所有的弹出框
  function hidePopup(login)
  {
    if ($("[name^='"+login+"']").length>0)
    {
      $(".popup").each(function ()
      {
        $(this).remove();
      });
    stopPropagation();//自身已经有下级，关闭以后就不再弹出
    }
    else//自身没有下级，关闭以后就不再弹出
    {
      $(".popup").each(function ()
      {
        $(this).remove();
      });
    }
  }

  //跳转到其他页面
  function showDetail(login)
  {
    var startDate = $("#startDate").val();//获取开始日期
    var endDate = $("#endDate").val();//获取结束的日期
    event.stopPropagation();
    window.open("<?=$erpurl?>trade-detail/"+login+'v'+startDate+'v'+endDate,'','height=300,width=400,top=0,left=0,toolbar=yes,menubar=yes,scrollbars=yes, resizable=yes,location=yes, status=yes');
  }

  //若是设置了值，则返回值，否则返回'/'
  function ifComm(comm)
  {
    if (comm =='/')
    {
      var a = '/';
    } else
    {
      var a = '$'+formatCurrency(comm);
    }
    return a;
  }

  //若是返回了佣金，说明是代理商，否则是交易者
  function ifAgent(comm)
  {
    var a = '';
    if (comm =='/')
    {
      a = "<span class=\"label label-default\"><?=$tradeName?><span>";
    } else
    {
      a = "<span class=\"label label-success\"><?=$agentName?><span>";
    }
    return a;
  }
  //根据等级，生成颜色
  function getColorByLevel(level)
  { 
    if (level == 1)
    {
      var c = '#4DFFFF';
    } else if (level == 2)
    {
      var c = '#BBFFBB';
    } else if (level == 3)
    {
      var c = '#FFCBB3';
    } else if (level == 4)
    {
      var c = '#DEDEBE';
    } else if (level == 5)
    {
      var c = '#CCFFFF';
    } else if (level == 6)
    {
      var c = '#CCFF99';
    } else if (level == 7)
    {
      var c = '#FFF8D7';
    } else if (level == 8)
    {
      var c = '#FCFCFC';
    } else if (level == 9)
    {
      var c = '#FFECEC';
    } else if (level == 10)
    {
      var c = '#003366';
    }
    return c; 
  }

  //判断是否具有团队交易金额和佣金交易金额
  function ifAgent2(teamTran, commTran)
  {
    var a  = '';
    //说明不是代理,则不存在团队交易金额和返佣的交易金额
    if (commTran == '/')
    {
      a = '/';
    } else
    {
      a = "<span class=\"label label-primary\">$"+formatCurrency(teamTran)+"</span>&nbsp;"+ "<span class=\"label label-success\">$"+formatCurrency(commTran)+"</span>";
    }
    return a;
  }
</script>