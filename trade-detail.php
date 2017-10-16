<?php 
  $adminLoginsAndNames = array();
  //管理员登录
  if ($mem)
  {
    $allUserInfo = $mem->get('allUserInfoRealTime');
  }
  if (!$allUserInfo)
  {
    $allUserInfo = $dbNRemote->get_results("SELECT login,agent_account,name FROM mt4_account", ARRAY_A);//$validGroupStr在config.php中配置      //注意：如果有下级的用户没有被设置成代理。那会计算结果会出错
    if ($mem)
    {
      $mem->set('allUserInfoRealTime', $allUserInfo, 0, 60);//此处设置60秒的会在一定程度上减少数据库的连接
    }
  }
  if ($_SESSION['login']  == 'admin')
  {
    if ($allUserInfo)
    {    
      foreach($allUserInfo AS $value)
      {
        $adminLoginsAndNames[$value['login']]['login'] = $value['login'];
        $adminLoginsAndNames[$value['login']]['name'] = $value['name'];
      }
    }
  }
  //代理登录
  else 
  {
    if ($allUserInfo)
    {
      //此变量已经被污染，所有使用之前需要消除污染。
      global $resultlll;
      $resultlll = null;
      $adminLogins = queryLowerLogin($allUserInfo, $_SESSION['login']);//查询出所有和登录者有关的账号(并不包括其自己)
      if ($adminLogins)
      {
        array_unshift($adminLogins, $_SESSION['login']);//将用户自己的登录账号插入数组头部
      }else
      {
        $adminLogins = array($_SESSION['login']);
      }    

      foreach ($allUserInfo as $key => $value) 
      {
        if (in_array($value['login'], $adminLogins))
        {
          $adminLoginsAndNames[$value['login']]['login'] = $value['login'];
          $adminLoginsAndNames[$value['login']]['name'] = $value['name'];
        }
      }
    }
  }

  unset($allUserInfo);
  unset($adminLogins);
  $userinfo = $_GET['do'];
  $userinfoArr = explode('v', $userinfo);
  $login = $_POST['search_login1']?$_POST['search_login1']:$userinfoArr[0]; 
  $tradeName = $adminLoginsAndNames[$login]['name'];

  $startDate = $_POST['startDate'] ? $_POST['startDate'] : $userinfoArr[1];
  $endDate = $_POST['endDate']?$_POST['endDate']:$userinfoArr[2];
  $startTime = date('Y-m-d 00:00:00', strtotime($startDate));//这个月1号的0点
  $endTime = date('Y-m-d 23:59:59', strtotime($endDate));//截止时间
  $todayEndTime = date('Y-m-d 23:59:59');//今天截止时间
  if ($mem)
  {
    $tradeDetail = $mem->get('tradeDetail_'.$startTime.'_'.$endTime.'_'.$login);
  }
  if (!$tradeDetail)
  {
    $tradeDetail = $dbNRemote->get_results("SELECT 
      ticket AS TICKET,
      create_time AS OPEN_TIME,
      update_time AS CLOSE_TIME,
      symbol AS SYMBOL,
      open_price AS OPEN_PRICE,
      close_price AS CLOSE_PRICE,
      CASE result WHEN 3 THEN 0 ELSE 1 END AS CMD,
      CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END AS PROFIT,
      `money` AS VOLUME
      FROM mt4_binary_option_history WHERE login={$login} AND update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1", ARRAY_A);
    if ($mem)
    {
      if ($endTime == $todayEndTime)
      {
        $mem->set('tradeDetail_'.$startTime.'_'.$endTime.'_'.$login, $tradeDetail, 0, 20);//设置成20秒，减少数据库连接
      }else
      {
        $mem->set('tradeDetail_'.$startTime.'_'.$endTime.'_'.$login, $tradeDetail, 0, 0);//设置成永久有效
      }
    }
  }
  //累计情况
  $quan1 = 0;$quan2 = 0;$transaction1 = 0 ;$transaction2 = 0;
  if ($tradeDetail)
  {
    foreach ($tradeDetail as $key => $value) 
    {
      $quan1 ++;
      $transaction1 += $value['VOLUME'];
      if ($value['PROFIT'] != 0)
      {
        $quan2 ++;
        $transaction2 += $value['VOLUME'];
      }
    }
  }
  $totalTrade = array($quan1,$quan2,$transaction1,$transaction2);
  $adminNames = array_map('end', $adminLoginsAndNames);//取出姓名
  array_multisort($adminNames,SORT_ASC,$adminLoginsAndNames);//根据姓名进行排序
  unset($adminNames);
?>
<div class="content-body">
  <div class="content-title">交易详情列表</div>
  <!-- 区段选择 -->
  <div class="col-lg-12">
    <div class="alert alert-danger" role="alert"> 
    用户：<?=$tradeName?>(<?=$login?>)<br>
    交易量：<?=number_format($totalTrade[0])?>&nbsp;&nbsp;/&nbsp;&nbsp;<?=number_format($totalTrade[1])?>(去平后)<br>
    交易金额：$<?=number_format($totalTrade[2],2)?>&nbsp;&nbsp;/&nbsp;&nbsp;$<?=number_format($totalTrade[3],2)?>(去平后)
    
  <!-- 表单，提交查询的日期 -->
      <form action="" method="post" style="margin-top:10px;">
        <div id="search_login">
          <input type="text" class="form-control" id="search_login2" placeholder="账号或姓名" value="<?=$login?>" style="width:103px;"/> 
          <select class="form-control" id="search_login1" name="search_login1" style="margin-left:108px;width:182px;">
              <option value=""></option>
              <?php foreach ($adminLoginsAndNames as $key => $adminLoginsAndNamesV) { ?>
                <option value="<?php echo $adminLoginsAndNamesV['login'];?>" <?php if ($login == $adminLoginsAndNamesV['login']) { echo 'selected'; } ?>><?php echo $adminLoginsAndNamesV['name'];?></option>
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
          <th align="center"><nobr>单号</nobr></th>
          <th align="center"><nobr>入场时间</nobr></th>
          <th align="center"><nobr>出场时间</nobr></th>
          <th align="center"><nobr>交易品种</nobr></th>
          <th align="center"><nobr>入场价格</nobr></th>
          <th align="center"><nobr>结束价格</nobr></th>
          <th align="center"><nobr>看涨/看跌</nobr></th>
          <th align="center"><nobr>盈亏金额</nobr></th>
          <th align="center"><nobr>投资金额</nobr></th>
        </tr>
      </thead>
      <tbody>
      <?php if ($tradeDetail) { foreach($tradeDetail as $key => $value) {?> 
        <tr>
          <td><?=$key+1?></td>
          <td><?=$value['TICKET']?></td>
          <td><?=$value['OPEN_TIME']?></td>
          <td><?=$value['CLOSE_TIME']?></td>
          <td><?=$value['SYMBOL']?></td>
          <td>$<?=number_format_nodecimal($value['OPEN_PRICE'])?></td>
          <td>$<?=number_format_nodecimal($value['CLOSE_PRICE'])?></td>
          <td><?=$value['CMD']==0?'看跌':'看涨'?></td>
          <td>$<?=number_format($value['PROFIT'],2)?></td>
          <td>$<?=number_format($value['VOLUME'],2)?></td>
        </tr>
      <?php } }else{?>
        <tr>
          <td colspan="9" class="text-center">该用户在此时间段内无交交易记录</td>
        </tr>
      <?php } ?>     
      </tbody>
    </table>
  </div>
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
        $('#search_login1 option').each(function(i) 
        {
          if (re.test($(this).val()))
          {
            $(this).prop('selected',true);//下拉框选中相应的选项（切记以后不要用attr,因为只能使用一次）
            $('#search_login2').val($(this).val());//输入框中变成账号
          }
        });
      }
      //输入的不是数字（即输入的是文字）
      else if (isNaN(manualAgency) && manualAgency != '') 
      {
        var re = new RegExp("[\u4e00-\u9fa5]*"+manualAgency+"[\u4e00-\u9fa5]*","g");//匹配任意中文
        $('#search_login1 option').each(function(i)
        {
          if (re.test($(this).text()))//匹配成功
          {
            $(this).prop('selected',true);//下拉框选中相应的选项（切记以后不要用attr,因为只能使用一次）
            $('#search_login2').val($(this).val());//输入框中变成账号
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
      }else
      {
        $('#search_login2').val('');
      }
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

    $("#dateRange").change(function () {
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
      }else if (selected == 'lastMonth')//上月
      {
        $('#startDate').val(getLastMonthStartAndEnd()[0]); //上月1号
        $('#endDate').val(getLastMonthStartAndEnd()[1]);//上月月末
      }else if (selected == 'week')//本周
      {
        $('#startDate').val(getWeekStartDate()); //本周开始的时间（周日开始）
        $('#endDate').val(getDateStr(0));//到目前为止
      }else if (selected == 'lastWeek')//上周
      {
        $('#startDate').val(getLastWeekStartAndEnd()[0]); //上周开始的时间
        $('#endDate').val(getLastWeekStartAndEnd()[1]);//上周结束的时间
      }else if (selected == 'today')//当日
      {
        $('#startDate').val(getDateStr(0)); //今天
        $('#endDate').val(getDateStr(0));//今天
      }else if (selected == 'yesterday')//昨天
      {
        $('#startDate').val(getDateStr(-1)); //开始时间是昨天
        $('#endDate').val(getDateStr(-1));//结束时间是昨天
      }else if (selected=='')
      {
        $('#startDate').val(''); //开始时间是昨天
        $('#endDate').val('');//结束时间是昨天
      }
      $("#trade-list").hide();
      //同时隐藏已经生成的报表
    }); 

    //手动输入日期不能超过上个月
    $('#startDate').blur(
      function () {
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
        if (startDate2<minDate2)
        {
          alert('手动只能查询本月内的交易记录');
          $('#startDate').val(nowYear+'-'+nowMonth+'-01'); //本月开始的时间
        }
      });
    $('#endDate').blur(
      function () {
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
        if (endDate2<minDate2)
        {
          alert('手动只能查询本月内的交易记录');
          $('#endDate').val(startDate); //本月开始的时间
        }
      });
  });
</script>