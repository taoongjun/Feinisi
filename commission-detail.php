<?php 
unset($userinfo);
$userinfo    = $_GET['do'];//通过url地址get方式传值 http://dev.bosince.com/bo/commission-detail/80097702v5v黄万严
$userinfoArr = explode('v', $userinfo);//url地址中传递的信息
$topLogin    = $_POST['search_login1'] ? $_POST['search_login1'] : $userinfoArr[0];   //所查询的用户的账号
$month       = $_POST['month'] ? $_POST['month']:$userinfoArr[1];//所查询的月份（手动查询以手动优先，无手动传值则以url地址为准）
$month       = $month > 9 ? $month : '0' . ltrim($month, '0');//月份，不足两位的补0
$period      = date('Y-' . $month);//年月例如：2016-02
$startDate   = date('Y-' . $month . '-01');//查询月份的第一天
$endDate     = date('Y-m-d', strtotime("$startDate +1 month -1 day"));//最后一天
$startTime   = date('Y-m-d 00:00:00', strtotime($startDate));//这个月1号的0点
$endTime     = date('Y-m-d 23:59:59', strtotime($endDate));//截止今天的日期

if ($mem) {
    $allUserInfo = $mem->get('commissionDetail_' . $period);//
}

if (!$allUserInfo) {
    //查询所有的用户信息（ 包含交易信息, 肯定已经进行存储 ）
    $allUserInfo = $dbLocal->get_results("SELECT LOGIN,AGENT_ACCOUNT,NAME,GENRE,TRADE_QUAN,FIR_TRADE_QUAN,
        TEAM_TRADE_QUAN,TRADE_QUAN2,FIR_TRADE_QUAN2,TEAM_TRADE_QUAN2,TRANSACTION,FIR_TRANSACTION,TEAM_TRANSACTION,
        TRANSACTION2,FIR_TRANSACTION2,TEAM_TRANSACTION2,PROFIT,`GROUP`,RATIO,REWARD FROM MT4_NEW_COM WHERE PERIOD='{$period}'", ARRAY_A);
    if ($mem && $allUserInfo) {
        $mem->set('commissionDetail_' . $period, $allUserInfo, 0, 0);//commission-list . ajax中有删除的操作
    }
}

//管理员登录
if ($_SESSION['login'] == 'admin') {
    //进行查询
    global $resultlll;
    $resultlll = null;
    //查询出所有和登录者有关的账号(并不包括其自己)
    $adminLogins = queryLowerLogin1($allUserInfo, $topLogin);
    if ($adminLogins) {
        array_unshift($adminLogins, $topLogin);
    } else {
        $adminLogins = array($topLogin);
    }
    foreach ($allUserInfo as $key => $value) {
        $relativeUserInfo[$value['LOGIN']] = $value;
        $relativeLogins[] = $value['LOGIN'];
        if (in_array($value['LOGIN'], $adminLogins)) {
            $adminUserInfo[$value['LOGIN']]['NAME'] = $value['NAME'];
            $adminUserInfo[$value['LOGIN']]['LOGIN'] = $value['LOGIN'];
            $adminUserInfo[$value['LOGIN']]['AGENT_ACCOUNT'] = $value['AGENT_ACCOUNT'];
            if ($value['GENRE'] == 1) {
                $agentsInfo[$value['LOGIN']]['LOGIN'] = $value['LOGIN'];
                $agentsInfo[$value['LOGIN']]['GROUP'] = $value['GROUP'];
                $agentsInfo[$value['LOGIN']]['RATIO'] = $value['RATIO'];
                $agentsInfo[$value['LOGIN']]['REWARD'] = $value['REWARD'];
                $agentLogins[] = $value['LOGIN'];
            }
            //获取本次计算所需要的数据
            $everAgentTeamFirSelTran[$value['LOGIN']]['TRADE_QUAN']        = $value['TRADE_QUAN'];//本人交易量（去平前）
            $everAgentTeamFirSelTran[$value['LOGIN']]['FIR_TRADE_QUAN']    = $value['FIR_TRADE_QUAN'];//直接下级交易量（去平前）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TEAM_TRADE_QUAN']   = $value['TEAM_TRADE_QUAN'];//团队交易量（去平前）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TRADE_QUAN2']       = $value['TRADE_QUAN2'];//本人交易量（去平后）
            $everAgentTeamFirSelTran[$value['LOGIN']]['FIR_TRADE_QUAN2']   = $value['FIR_TRADE_QUAN2'];//直接下级交易量（去平后）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TEAM_TRADE_QUAN2']  = $value['TEAM_TRADE_QUAN2'];//团队交易量（去平后）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TRANSACTION']       = $value['TRANSACTION'];//本人交易金额（去平前）
            $everAgentTeamFirSelTran[$value['LOGIN']]['FIR_TRANSACTION']   = $value['FIR_TRANSACTION'];//直接下级交易金额（去平前）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TEAM_TRANSACTION']  = $value['TEAM_TRANSACTION'];//团队交易金额（去平前）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TRANSACTION2']      = $value['TRANSACTION2'];//本人交易金额（去平后）
            $everAgentTeamFirSelTran[$value['LOGIN']]['FIR_TRANSACTION2']  = $value['FIR_TRANSACTION2'];//直接下级交易金额（去平后）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TEAM_TRANSACTION2'] = $value['TEAM_TRANSACTION2'];//团队交易金额（去平后）
        }
    }

//代理登录
} else if ($_SESSION['login'] != 'admin') {
    //查询自身
    if ($topLogin == $_SESSION['login']) {
        global $resultlll;
        $resultlll = null;
        $adminLogins = queryLowerLogin1($allUserInfo, $_SESSION['login']);//查询出所有和登录者有关的账号(并不包括其自己)
        if ($adminLogins) {
            array_unshift($adminLogins, $topLogin);
        } else {
            $adminLogins = array($_SESSION['login']);
        }  
        foreach ($allUserInfo as $key => $value) {
            if (in_array($value['LOGIN'], $adminLogins)) {
                $adminUserInfo[$value['LOGIN']]['NAME']             = $value['NAME'];
                $adminUserInfo[$value['LOGIN']]['LOGIN']            = $value['LOGIN'];
                $adminUserInfo[$value['LOGIN']]['AGENT_ACCOUNT']    = $value['AGENT_ACCOUNT'];
                $relativeUserInfo[$value['LOGIN']]['NAME']          = $value['NAME'];
                $relativeUserInfo[$value['LOGIN']]['LOGIN']         = $value['LOGIN'];
                $relativeUserInfo[$value['LOGIN']]['AGENT_ACCOUNT'] = $value['AGENT_ACCOUNT'];
                $relativLogins[] = $value['LOGIN'];
                if ($value['GENRE'] == 1) {
                    $agentsInfo[$value['LOGIN']]['LOGIN']  = $value['LOGIN'];
                    $agentsInfo[$value['LOGIN']]['GROUP']  = $value['GROUP'];
                    $agentsInfo[$value['LOGIN']]['RATIO']  = $value['RATIO'];
                    $agentsInfo[$value['LOGIN']]['REWARD'] = $value['REWARD'];
                    $agentLogins[] = $value['LOGIN'];
                }   
                $everAgentTeamFirSelTran[$value['LOGIN']]['TRADE_QUAN']        = $value['TRADE_QUAN'];//本人交易量（去平前）
                $everAgentTeamFirSelTran[$value['LOGIN']]['FIR_TRADE_QUAN']    = $value['FIR_TRADE_QUAN'];//直接下级交易量（去平前）
                $everAgentTeamFirSelTran[$value['LOGIN']]['TEAM_TRADE_QUAN']   = $value['TEAM_TRADE_QUAN'];//团队交易量（去平前）
                $everAgentTeamFirSelTran[$value['LOGIN']]['TRADE_QUAN2']       = $value['TRADE_QUAN2'];//本人交易量（去平后）
                $everAgentTeamFirSelTran[$value['LOGIN']]['FIR_TRADE_QUAN2']   = $value['FIR_TRADE_QUAN2'];//直接下级交易量（去平后）
                $everAgentTeamFirSelTran[$value['LOGIN']]['TEAM_TRADE_QUAN2']  = $value['TEAM_TRADE_QUAN2'];//团队交易量（去平后）
                $everAgentTeamFirSelTran[$value['LOGIN']]['TRANSACTION']       = $value['TRANSACTION'];//本人交易金额（去平前）
                $everAgentTeamFirSelTran[$value['LOGIN']]['FIR_TRANSACTION']   = $value['FIR_TRANSACTION'];//直接下级交易金额（去平前）
                $everAgentTeamFirSelTran[$value['LOGIN']]['TEAM_TRANSACTION']  = $value['TEAM_TRANSACTION'];//团队交易金额（去平前）
                $everAgentTeamFirSelTran[$value['LOGIN']]['TRANSACTION2']      = $value['TRANSACTION2'];//本人交易金额（去平后）
                $everAgentTeamFirSelTran[$value['LOGIN']]['FIR_TRANSACTION2']  = $value['FIR_TRANSACTION2'];//直接下级交易金额（去平后）
                $everAgentTeamFirSelTran[$value['LOGIN']]['TEAM_TRANSACTION2'] = $value['TEAM_TRANSACTION2'];//团队交易金额（去平后）
            }
        }
    //进行查询。切记防止url地址非法访问
    } else {
        global $resultlll;
        $resultlll = null;
        $adminLogins = queryLowerLogin1($allUserInfo, $topLogin);//查询出所有和登录者有关的账号(并不包括其自己)
        if ($adminLogins) {
          array_unshift($adminLogins, $topLogin);
        } else {
          $adminLogins = array($topLogin);
        }
        global $resultlll;
        $resultlll = null;
        $relativLogins = queryLowerLogin1($allUserInfo, $_SESSION['login']);//查询出所有和登录者有关的账号(并不包括其自己)
        if ($relativLogins) {
          array_unshift($relativLogins, $_SESSION['login']);
        } else {
          $relativLogins = array($_SESSION['login']);
        }  
        foreach ($allUserInfo as $key => $value) {
            if (in_array($value['LOGIN'], $adminLogins)) {
                $adminUserInfo[$value['LOGIN']]['NAME'] = $value['NAME'];
                $adminUserInfo[$value['LOGIN']]['LOGIN'] = $value['LOGIN'];
                $adminUserInfo[$value['LOGIN']]['AGENT_ACCOUNT'] = $value['AGENT_ACCOUNT'];  
                if ($value['GENRE'] == 1) {
                    $agentsInfo[$value['LOGIN']]['LOGIN'] = $value['LOGIN'];
                    $agentsInfo[$value['LOGIN']]['GROUP'] = $value['GROUP'];
                    $agentsInfo[$value['LOGIN']]['RATIO'] = $value['RATIO'];
                    $agentsInfo[$value['LOGIN']]['REWARD'] = $value['REWARD'];
                    $agentLogins[] = $value['LOGIN'];
                } 
            }
            $everAgentTeamFirSelTran[$value['LOGIN']]['TRADE_QUAN']        = $value['TRADE_QUAN'];//本人交易量（去平前）
            $everAgentTeamFirSelTran[$value['LOGIN']]['FIR_TRADE_QUAN']    = $value['FIR_TRADE_QUAN'];//直接下级交易量（去平前）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TEAM_TRADE_QUAN']   = $value['TEAM_TRADE_QUAN'];//团队交易量（去平前）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TRADE_QUAN2']       = $value['TRADE_QUAN2'];//本人交易量（去平后）
            $everAgentTeamFirSelTran[$value['LOGIN']]['FIR_TRADE_QUAN2']   = $value['FIR_TRADE_QUAN2'];//直接下级交易量（去平后）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TEAM_TRADE_QUAN2']  = $value['TEAM_TRADE_QUAN2'];//团队交易量（去平后）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TRANSACTION']       = $value['TRANSACTION'];//本人交易金额（去平前）
            $everAgentTeamFirSelTran[$value['LOGIN']]['FIR_TRANSACTION']   = $value['FIR_TRANSACTION'];//直接下级交易金额（去平前）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TEAM_TRANSACTION']  = $value['TEAM_TRANSACTION'];//团队交易金额（去平前）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TRANSACTION2']      = $value['TRANSACTION2'];//本人交易金额（去平后）
            $everAgentTeamFirSelTran[$value['LOGIN']]['FIR_TRANSACTION2']  = $value['FIR_TRANSACTION2'];//直接下级交易金额（去平后）
            $everAgentTeamFirSelTran[$value['LOGIN']]['TEAM_TRANSACTION2'] = $value['TEAM_TRANSACTION2'];//团队交易金额（去平后）
            if (in_array($value['LOGIN'], $relativLogins)) {
                $relativeUserInfo[$value['LOGIN']]['NAME'] = $value['NAME'];
                $relativeUserInfo[$value['LOGIN']]['LOGIN'] = $value['LOGIN'];
                $relativeUserInfo[$value['LOGIN']]['AGENT_ACCOUNT'] = $value['AGENT_ACCOUNT'];
            }
        }
    }
}

//如果url地址非法输入账号，查询不属于自己的账号时，将会强行终止
if (!in_array($topLogin, $relativeLogins)) {
    if ($mem) {
        $allUserInfo = $mem->delete('commissionDetail_' . $period);
    }
    echo '<a href="http://' . $erpurl . 'commission-list/' . $month . '">返回佣金报表</a><br>';
    die("出现该提示，请返回佣金报表页面，再进行查询");
}

$pathArr = getPathArr1($agentLogins, $adminUserInfo);
//自定义一些淡颜色，用来分级区分不同的级别
$colorArr = array('#CCCC99', '#D7EDFB', '#FFFFCC', '#CC9966', '#99CC99', '#CCFFFF', '#8d4653', '#91e8e1');
$backgroundsForPath = getColorByPath($searchLogin, $pathArr, $colorArr);
$commission = calculateCommsissionByFirTeam($pathArr, $agentsInfo, $everAgentTeamFirSelTran);
//查询分组信息
$subGroups = queryGroups($adminLogins);
 ?>
<div class="content-body">
  <div class="content-title">返佣详情</div>
  <!-- 区段选择 -->
  <div class="col-lg-12">
    <div class="alert alert-danger" role="alert"> 
      姓名：<?=$adminUserInfo[$topLogin]['NAME']?><br><!-- 所查询的用户的姓名 -->
      账号：<?=$topLogin?><br><!-- 所查询的账号 -->
      佣金：<?='$'.number_format($commission['allCom'][$topLogin]['allCom'],2)?><!-- 所查询用户的佣金 -->
      <form method="post" action="" style="margin-top:10px;">
        <div id="search_login">
          <input type="text" class="form-control" id="search_login2" placeholder="账号或姓名" value="<?php if (in_array($searchLogin,$adminLogins)) { echo $searchLogin; }else{ echo ''; }?>" style="width:103px;"/> 
          <select class="form-control" id="search_login1" name="search_login1" style="margin-left:108px;width:182px;">
              <option value=""></option>
              <?php foreach ($relativeLogins as $relativLoginsV) { ?>
                <option value="<?php echo $relativLoginsV;?>" <?php if ($topLogin == $relativLoginsV) { echo 'selected'; } ?>><?php echo $relativeUserInfo[$relativLoginsV]['NAME'];?></option>
              <?php } unset($relativeUserInfo);unset($relativLogins);?>
          </select>
        </div> 
        <div class="form-inline">
          <select name="month" class="form-control" style="width: 290px;">
            <?php for($i=date('n');$i>1;$i--) { ?>
              <option value="<?=$i-1?>" <?=$month==$i-1?'selected':''?>><?=date('Y')?>年<?=$i-1?>月</option>
            <?php } ?>
              <!-- sublime快捷键  option[value="$"]{$月}*12 -->
          </select>
          <button type="submit" class="btn btn-success" >搜索</button>
        </div>
      </form>
      备注：表中同一底色属于同一级别。
    </div> 
  </div>

  <div class="col-lg-12">
    <table class="table table-hover table-striped">
      <thead>
        <th align="center"><nobr>序号</nobr></th>
        <th align="center"><nobr>账号</nobr></th>
        <th align="center"><nobr>姓名</nobr></th>
        <th align="center"><nobr>上级用户</nobr></th>
        <th align="center"><nobr>本人交易量</nobr></th>
        <th align="center"><nobr>直接下级交易量</nobr></th>
        <th align="center"><nobr>本人交易金额</nobr></th>
        <th align="center"><nobr>直接下级交易金额</nobr></th>
        <th align="center"><nobr>团队去平交易额</nobr></th>
        <th align="center"><nobr>所属<?=$agentGroupName?></nobr></th>
        <th align="center"><nobr>分组比率</nobr></th>
        <th align="center"><nobr>奖励比率</nobr></th>
        <th align="center"><nobr>直接佣金</nobr></th>
        <th align="center"><nobr>贡献点差金额&nbsp;/&nbsp;得到点差者</nobr></th>
        <th align="center"><nobr>获得点差金额</nobr></th>
        <th align="center"><nobr>贡献奖励金额&nbsp;/&nbsp;得到奖励者</nobr></th>
        <th align="center"><nobr>获得奖励金额</nobr></th>
        <th align="center"><nobr>实时返佣</nobr></th>
        <th align="center"><nobr>佣金合计</nobr></th>
        <th align="center"><nobr>月结佣金</nobr></th>
      </thead>
      <tbody>
<?php foreach($adminLogins as $key=>$loginV) {?>
        <tr style="background:<?=$backgroundsForPath[$loginV]?>">
          <td><?=$key+1?></td>
          <td><?=$loginV?></td>
          <td>
          <nobr>
           <?=$adminUserInfo[$loginV]['NAME']?>&nbsp;&nbsp;<?=in_array($loginV, $agentLogins)?"<span class=\"label label-success\">".$agentName."</span>":"<span class=\"label label-default\">" . $tradeName . "</span>"?>
          </nobr>
          </td>
          <td><?=$adminUserInfo[$adminUserInfo[$loginV]['AGENT_ACCOUNT']]['NAME']?></td><!-- 所属上级 -->
          <td>
            <nobr>
              <span class="label label-primary"><?=number_format($everAgentTeamFirSelTran[$loginV]['TRADE_QUAN'])?></span><!-- 交易量 -->
              <span class="label label-success"><?=number_format($everAgentTeamFirSelTran[$loginV]['TRADE_QUAN2'])?></span><!-- 去平后交易量 -->
            </nobr>
          </td>
          <td>
            <nobr>
              <?php if (in_array($loginV, $agentLogins)) { ?>
                <span class="label label-primary"><?=number_format($everAgentTeamFirSelTran[$loginV]['FIR_TRADE_QUAN'])?></span><!-- 每个人直属下级的交易量 -->
                <span class="label label-success"><?=number_format($everAgentTeamFirSelTran[$loginV]['FIR_TRADE_QUAN2'])?></span><!-- 每个人直属下级去平后交易量 -->
              <?php }else{ ?>
                /
              <?php } ?>
            </nobr>
          </td>
          <td>
            <nobr>
              <span class="label label-primary">$<?=number_format($everAgentTeamFirSelTran[$loginV]['TRANSACTION']/100,2)?></span><!-- 交易金额 -->
              <span class="label label-success">$<?=number_format($everAgentTeamFirSelTran[$loginV]['TRANSACTION2']/100,2)?></span><!-- 去平后交易金额 -->
            </nobr>
          </td> 
          <td>
            <nobr>
              <?php if (in_array($loginV, $agentLogins)) { ?>
                <span class="label label-primary">$<?=number_format($everAgentTeamFirSelTran[$loginV]['FIR_TRANSACTION'],2)?></span><!-- 交易金额 -->
                <span class="label label-success">$<?=number_format($everAgentTeamFirSelTran[$loginV]['FIR_TRANSACTION2'],2)?></span><!-- 去平后交易金额 -->
              <?php }else{ ?>
                /
              <?php } ?>
            </nobr>
          </td>
          <td>
            <?php if (in_array($loginV, $agentLogins)) { ?>
               <span class="label label-success">$<?=number_format($everAgentTeamFirSelTran[$loginV]['TEAM_TRANSACTION2'],2)?></span><!-- 去平后交易金额 -->
            <?php }else{ ?>
              /
            <?php } ?>
          </td>
          <td>
            <?=$agentsInfo[$loginV]?$agentsInfo[$loginV]['GROUP']:'/'?>
          </td>
          <td>
            <?=$agentsInfo[$loginV]?number_format($agentsInfo[$loginV]['RATIO']*100,2).'%':'/'?>
          </td>
          <td>
            <?=$agentsInfo[$loginV]?number_format($agentsInfo[$loginV]['REWARD']*100,2).'%':'/'?>
          </td>
          <td>
            <!-- 代理才有佣金 -->
            <?=in_array($loginV, $agentLogins)?'$'.number_format($commission['dirCom'][$loginV]['dirCom'],2):'/'?><!-- 直接返佣 -->
          </td>
          <td><!--贡献返点金额 -->
            <?=!in_array($loginV, $agentLogins) || $topLogin==$loginV?'/':'$'.number_format($commission['gapComUp'][$loginV]['gapComUp'],2)?>
            <?=$commission['gapComUp'][$loginV]['gapComUp']?'/'.$adminUserInfo[$commission['gapComUp'][$loginV]['gapComUpLogin']]['NAME']:''?>
          </td>
          <td><!--获得返点金额 -->
            <?=in_array($loginV, $agentLogins)?'$'.number_format($commission['gapCom'][$loginV]['gapCom'],2):'/'?>
          </td>
          <td><!--贡献奖励金额 -->
            <?=!in_array($loginV, $agentLogins) || $topLogin==$loginV?'/':'$'.number_format($commission['rewardUp'][$loginV]['rewardUp'],2)?>
            <?=$commission['rewardUp'][$loginV]['rewardUp']?'/'.$adminUserInfo[$commission['rewardUp'][$loginV]['rewardUpLogin']]['NAME']:''?>  
          </td>
          <td><!--获得奖励金额 -->
            <?=in_array($loginV, $agentLogins)?'$'.number_format($commission['reward'][$loginV]['reward'],2):'/'?>
          </td>
          <td><!-- 实时返佣 -->
            <?=in_array($loginV, $agentLogins)?'$'.number_format($commission['curCom'][$loginV]['curCom'],2):'/'?><!-- 佣金合计 -->
          </td>
          <td>
            <?=in_array($loginV, $agentLogins)?'$'.number_format($commission['allCom'][$loginV]['allCom'],2):'/'?><!-- 佣金合计 -->
          </td>
          <td><!-- 月结佣金 -->
            <?=in_array($loginV, $agentLogins)?'$'.number_format($commission['monCom'][$loginV]['monCom'],2):'/'?><!-- 佣金合计 -->
          </td>
        </tr>
<?php } ?>
        <tr>
          <td colspan="20" class="17">
            备注：<span class="label label-primary">总计</span>&nbsp;&nbsp;所有数据累积；&nbsp;&nbsp;&nbsp;&nbsp;<span class="label label-success">去平后</span>&nbsp;&nbsp;除去持平的交易记录。
          </td>
        </tr>
        <tr>
          <td colspan="20" class="text-center" id="dddd">
          <a href="<?=$erpurl?>commission-list/<?=$month?>">返回佣金报表</a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
 </div>

 <script type="text/javascript">
   $(document).ready(function () {
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
    $('#search_login1').change(function () {
      if ($("#search_login1 option:selected").val()!='') {
        $('#search_login2').val($("#search_login1 option:selected").val());
      } else {
        $('#search_login2').val('');
      }
    });
  });
 </script>