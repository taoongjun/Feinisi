<!-- content-body,body-title为自定义属性 -->
<div class="content-body">
  <div class="content-title">佣金报表</div>
    <div class="col-lg-12">
      <div  class="alert alert-danger">
        <form method="post" action="">
          <div id="search_login">
            <input type="text" class="form-control" id="search_login2" placeholder="账号或姓名" value="<?php if ($searchAgent) {echo $searchAgent;}?>"/> 
            <select class="form-control" id="search_login1" name="search_login1">
              <option value="">所有代理</option>
                <?php foreach ($relativeUserInfo as $key =>$value) { if (in_array($value['login'], $relativeAgentLogins)) {
                ?>
                  <option value="<?php echo $value['login'];?>" <?php if ($topLogin == $value['login']) { echo 'selected'; } ?>><?php echo $value['name'];?></option>
                <?php }} ?>
            </select>
          </div>
            <select id="month" name="month" class="form-control" style="width:210px;">
             <?php for($i=date('n');$i>1;$i--) {?>
               <option value="<?=$i-1?>" <?=$month==$i-1?'selected':''?>><?=date('Y')?>年<?=$i-1?>月</option>
             <?php } ?>
              <!-- sublime快捷键  option[value="$"]{$月}*12 -->
            </select>
            <button type="submit" class="btn btn-primary" id="submitAll">
              按月查看
            </button>
        </form>
        <div style="margin-top:10px;">
          <a href="<?=$erpurl?>commission-list/downloadM<?=$month?$month:''?>M<?=$searchAgent?>">
            <button type="submit" class="btn btn-primary" id="downloadReport">
              导出报表
            </button>
          <a>
        </div>
        <?php if ($mustAgeButTrade)
        { 
          foreach ($mustAgeButTrade as $key => $value) 
          {
            $alertStr .= $adminUserInfo[$value]['name'].'(&nbsp;'.$value.'&nbsp;),';
          }
          $alertStr = trim($alertStr,',');
          echo '<span style="color:red;">警告：</span>'.$alertStr.'必须被设置成代理,因其存在下级，否则影响返佣计算<br>';
        }
        ?>
        <span style="color:red;">备注：</span>1、若您对返佣有疑问，点击相应行便可以核对明细；<br>
        <!-- 管理员才具有的权限 -->
        <?php if ($_SESSION['login'] == 'admin') { ?>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2、若用户的『从属关系』或用户的所属『代理商分组』或『分组比率』调整过后，而您希望按照新的从属关系计算佣金，点击 更新佣金 （谨慎操作）
        <br>
        <button class="btn btn-primary" id="updateCommission" onclick="updateCommission(<?="'$period'"?>, <?="'$topLogin'"?>, <?="'".$_SESSION['login']."'" ?>)">更新佣金</button>
        <?php } ?>
      </div>
    </div>

  <div class="col-lg-12 table-responsive">
    <table class="table table-hover table-striped">
      <thead>
        <tr>
          <th align="center"><nobr>账号</nobr></th>
          <th align="center"><nobr>姓名</nobr></th>
          <th align="center"><nobr>直接下级交易量</nobr></th>
          <th align="center"><nobr>直接下级交易金额</nobr></th>
          <th align="center"><nobr>团队去平交易额</nobr></th>
          <th align="center"><nobr>所属代理商分组</nobr></th>
          <th align="center"><nobr>分组比率</nobr></th>
          <th align="center"><nobr>奖励比率</nobr></th>
          <th align="center"><nobr>直接佣金</nobr></th>
          <th align="center"><nobr>下级点差佣金</nobr></th>
          <th align="center"><nobr>下级奖励</nobr></th>
          <th align="center"><nobr id="COMM" data-toggle="tooltip" data-placement="bottom" title="点击查看所有代理佣金">佣金合计（<button type="button" class="btn btn-success" style="height:20px;padding:0px;">&nbsp;&nbsp;去&nbsp;〇&nbsp;&nbsp;</button>）</nobr></th>
          <th align="center"><nobr>月结佣金</nobr></th>
        </tr>
      </thead>
      <tbody>
<!-- 键值为登录账号 -->
<?php 
if ($adminAgentLogins) {
$i = 0;foreach ($adminAgentLogins as $agentLogin) {  
?>
        <tr onclick="showCommDetail(<?=$agentLogin?>)" data-toggle="tooltip" data-placement="top" title="点击查看明细" <?=$commission['allCom'][$agentLogin]['allCom']?'':"name=\"noCommTr\""?>>
          <td><?=$agentLogin?></td>
          <td id="<?=$agentLogin?>"><?=$adminUserInfo[$agentLogin]['name']?></td>
          <td>
            <nobr>
              <span class="label label-primary"><?=number_format($everAgentTeamFirTran[$agentLogin]['FIR_TRADE_QUAN'])?></span><!-- 直属下级交易量（去平前） -->
              <span class="label label-success"><?=number_format($everAgentTeamFirTran[$agentLogin]['FIR_TRADE_QUAN2'])?></span><!-- 直属下级交易量（去平后） -->
            <nobr>
          </td>
          <td>
            <nobr>
              <span class="label label-primary">$<?=number_format($everAgentTeamFirTran[$agentLogin]['FIR_TRANSACTION'], 2)?></span><!-- 直属下级交易金额（去平前） -->
              <span class="label label-success">$<?=number_format($everAgentTeamFirTran[$agentLogin]['FIR_TRANSACTION2'], 2)?></span><!-- 直属下级交易金额（去平后） --> 
            </nobr>
          </td>
          <td>
            <span class="label label-success">$<?=number_format($everAgentTeamFirTran[$agentLogin]['TEAM_TRANSACTION2'], 2)?></span>
          </td>
          <td><?=$adminAgentsInfo[$agentLogin]['GROUP']?></td>
          <td><?=number_format($adminAgentsInfo[$agentLogin]['RATIO']*100,2)?>%</td>
          <td><?=number_format($adminAgentsInfo[$agentLogin]['REWARD']*100,2)?>%</td>
          <td>$<?=number_format($commission['dirCom'][$agentLogin]['dirCom'],2)?></td><!-- 直接返佣 -->
          <td>$<?=number_format($commission['gapCom'][$agentLogin]['gapCom'],2)?></td><!-- 点差佣金 -->
          <td>$<?=number_format($commission['reward'][$agentLogin]['reward'],2)?></td><!-- 额外奖励 -->
          <td>$<?=number_format($commission['allCom'][$agentLogin]['allCom'],2)?></td><!-- 佣金合计 -->
          <td>$<?=number_format($commission['monCom'][$agentLogin]['monCom'],2)?></td><!-- 月结佣金 -->
        </tr> 
<?php $i++;  }  }else{ ?>
        <tr>
          <td colspan="13" class="text-center">
            未查询到记录，您的下级没有代理商。
          </td>
        </tr>
<?php  } ?>
        <tr>
          <td colspan="13">
            备注：1、单击任意行查看明细；2、<span class="label label-primary"> 总计 </span>&nbsp;所有数据累积；&nbsp;&nbsp;&nbsp;&nbsp;<span class="label label-success"> 去平后 </span>&nbsp;除去持平的交易记录；3、表中<span style="background:#F2DEDE">&nbsp;深色区域&nbsp;</span>代表返佣金额为0
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>  
<script type="text/javascript">
  $(function () { $("[data-toggle='tooltip']").tooltip(); });
  function showCommDetail(login)
  {
    var month = $("#month option:selected").val();//月份
    var name = $("#"+login).html();//姓名
    window.open('<?=$erpurl?>commission-detail/'+login+'v'+month+'v'+name,'','height=300,width=400,top=0,left=0,toolbar=yes,menubar=yes,scrollbars=yes, resizable=yes,location=yes, status=yes');
  }
  $(document).ready(function ()
  {
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
      }else
      {
        $('#search_login2').val('');
      }
    });
    //佣金为0的行进行隐藏，点击以后出现
<?php if ($topLogin == 0 || $topLogin == $_SESSION['login']) { ?>
    $("tr[name='noCommTr']").hide();
<?php }else{ ?>
    $("tr[name='noCommTr']").show();
<?php } ?>
    $("tr[name='noCommTr']").css('background','#F2DEDE');
    $("#COMM").click(function () {
      if ($("tr[name='noCommTr']").is(":hidden"))
      {
        $("tr[name='noCommTr']").show();
        $("#COMM").html("佣金合计（<button type=\"button\" class=\"btn btn-success\" style=\"height:20px;padding:0px;\">&nbsp;&nbsp;所&nbsp;有&nbsp;&nbsp;</button>）");
      }else if ($("tr[name='noCommTr']").is(":visible"))
      {
        $("tr[name='noCommTr']").hide();
        $("#COMM").html("佣金合计（<button type=\"button\" class=\"btn btn-success\" style=\"height:20px;padding:0px;\">&nbsp;&nbsp;去&nbsp;〇&nbsp;&nbsp;</button>)");
      }
    });
  });

  function updateCommission(period, topLogin, sessionLogin)
  {
    $.ajax({
      type: "POST",
      url: "<?=$url?>ajax/commission-list.ajax.php",
      data:{"period":period,'topLogin':topLogin,'sessionLogin':sessionLogin},
      success:function (msg) //成功的处理函数
      { 
        if (msg == 1)
        {                                                                                                                                                    
          window.location.reload();
        }
      }
    });
  }
</script>