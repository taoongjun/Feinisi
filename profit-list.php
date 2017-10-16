<!-- bootstrap源文件被更改，故在此进行补充 -->
<style type="text/css">
   @media (min-width: 768px) {
  .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12 {
    float: left;
  }
  .col-sm-offset-1 {
    margin-left: 8.33333333%;
  }
</style>
<div class="content-body">
  <div class="content-title">业务报表</div>
  <div class="col-lg-12">
    <div class="alert alert-danger" role="alert" style="overflow:auto;"> 
      <div style="float:left;paddin">
        <form action="" name="form1" method="post" class="form-horizontal">
          <div id="search_login">
            <input type="text" class="form-control" id="search_login2" placeholder="账号或姓名" value="<?=$searchLogin?$searchLogin:''?>"/> 
            <select class="form-control" id="search_login1" name="search_login1">
              <option value="" <?php if ($searchLogin == '') { echo 'selected'; } ?>>综合报表</option>
                <?php foreach ($relativeUserInfo as $value) { ?>
                  <option value="<?=$value['login']?>" <?php if ($searchLogin == $value['login']) { echo 'selected'; } ?>><?=$value['name']?></option>
                <?php } ?>
            </select>
          </div>  

          <select name="month" class="form-control" id="timeDiv" style="width:210px;">
            <?php for ($i=date('n'); $i > 0; $i--) {?>
              <option value="<?=$i?>" <?=$month==$i?'selected':''?>><?=date('Y')?>年<?=$i?>月</option>
            <?php } ?>
            <!-- sublime快捷键  option[value="$"]{$月}*12 -->
          </select>  
          <div id="btnDiv">
            <button type="submit" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="根据账号进行查询" id="searchProfitBtn">
              查询报表 
            </button>  
          </div>
        </form>
        <?php if ($searchLogin != '') { ?>
          <button type="submit" class="btn btn-primary" onclick="updateSearchProfit(<?="'$searchLogin'" ?>,<?="'$period'" ?>)">
            更新结果
          </button><br>
若用户的的团队情况，有所变化，请点击此处     
        <?php } ?>
<!-- 查询单个用户不予以下载，没有利润报表不予以下载-->
<?php if (!$searchLogin && $allBusiness) { ?>
        <a href="<?=$erpurl?>profit-list/downloadM<?=str_replace ("0", "", $month)?>M<?=$searchLogin?$searchLogin:''?>">
          <button type="submit" class="btn btn-primary">导出报表
          </button>
        <a> 
<?php } ?>
        备注：导出报表前，请先查询
      </div> 
      <!-- 只在查询综合报表时才会显示 -->
      <?php if (!$searchLogin) { ?>
        <div class="profitDiv">
          <div class="profitTitle text-center">本期业务情况</div>
          <div class="profitRow"><span>公司业务量&nbsp;&nbsp;&nbsp;</span><span>$<?=$companyProfit?number_format($companyProfit,2):'0.00'?></span></div>
          <div class="profitRow"><span>总计业务量&nbsp;&nbsp;&nbsp;</span><span>$<?=$totalProfit?number_format($totalProfit,2):'0.00'?></span></div>
          <div class="profitRow"><span>运营业务量&nbsp;&nbsp;&nbsp;</span><span>$<?=$invalidProfit?number_format($invalidProfit,2):'0.00'?></span></div>
          <div class="profitRow"><span>运营账号数&nbsp;&nbsp;&nbsp;</span><span><?=$noProfitLogins?count($noProfitLogins):"<span style=\"color:red;\">请尽快设置</span>"?></span></div>
          <div class="profitRow"><span><?=date('Y'),'年',ltrim($month,0)?>月</span>&nbsp;&nbsp;&nbsp;<span class="btn btn-primary btn-noProfit" data-toggle="modal" data-target="#noProfitLoginDiv">运营号-设置</span></div>
        </div>
      <?php } ?>

    </div> 
    <div style="clear:both;"></div>
  </div>

  <div class="col-lg-12">
    <table class="table table-hover table-striped">
      <thead>
        <th align="center"><nobr>序号</nobr></th>
        <th align="center"><nobr>账号</nobr></th>
        <th align="center"><nobr>姓名</nobr></th>
        <th align="center"><nobr>交易次数</nobr></th>
        <th align="center"><nobr>累计资金流量</nobr></th>
<?php if ($searchLogin) { ?>
        <th align="center"><nobr>团队资金流量</nobr></th>
<?php } ?>      
        <th align="center"><nobr>累计盈亏金额</nobr></th>
<?php if ($searchLogin) { ?>
        <th align="center"><nobr>团队盈亏金额</nobr></th>
<?php } ?>
      </thead>
      <tbody>
<?php
    if ($businessForList)
    {
      $i = 1;
      foreach ($businessForList as $key => $businessForListV) {?>
        <tr style="<?php if ($businessForListV['PROFIT'] >0) { echo 'background-color:#F2DEDE;'; }if (in_array($businessForListV['LOGIN'],$noProfitLogins)) { echo 'color:red;'; }?>;">
          <td><?= $i ?></td>
          <td><?=$businessForListV['LOGIN']?></td>
          <td>
            <nobr>
              <?=$businessForListV['NAME']?>&nbsp;&nbsp;&nbsp;<?=in_array($businessForListV['LOGIN'], $adminAgentLogins)?"<span class=\"label label-success\">".$agentName."</span>":"<span class=\"label label-default\">".$tradeName."</span>"?>
            </nobr>
          </td>
          <td>
            <nobr>
              <span class="label label-primary"><?=number_format($businessForListV['TRADE_QUAN'])?></span><!-- 交易量 -->
              <span class="label label-success"><?=number_format($businessForListV['TRADE_QUAN2'])?></span><!-- 去平后交易量 -->
            </nobr>
          </td>
          <td>
            <nobr>
              <span class="label label-primary">$<?=number_format($businessForListV['TRANSACTION'], 2)?></span><!-- 交易金额 -->
              <span class="label label-success">$<?=number_format($businessForListV['TRANSACTION2'], 2)?></span><!-- 去平后交易金额 -->
            </nobr>
          </td>
<?php if ($searchLogin) {  ?>
          <td>
            <nobr>
              <span class="label label-primary">$<?=number_format($teamTranAndPro['TEAMTRANSACTION'], 2)?></span><!-- 交易量 -->
              <span class="label label-success">$<?=number_format($teamTranAndPro['TEAMTRANSACTION2'], 2)?></span><!-- 去平后交易量 -->
            </nobr>
          </td><!-- 团队累计投资金额 -->
<?php } ?>
          <td>$<?=number_format($businessForListV['PROFIT'], 2)?></td>
<?php if ($searchLogin) {  ?>
          <td>$<?=number_format($teamTranAndPro['TEAMPROFIT'], 2)?></td><!-- 累计盈亏金额 -->
<?php } ?>
        </tr> 
<?php 
    $i++;
  } 
  //合计栏(综合报表时才有)
  if (!$_POST['search_login1']) {?>

        <tr>
          <td>合计</td>
          <td></td>
          <td></td>
          <td>
            <nobr>
              <span class="label label-primary"><?=number_format($allTradeTimes)?></span><!-- 交易量 -->
              <span class="label label-success"><?=number_format($allTradeTimes2)?></span><!-- 去平后交易量 -->
            </nobr>
          </td><!-- 累计交易次数 -->
          <td>
            <nobr>
              <span class="label label-primary"><?=number_format($allTransaction, 2)?></span><!-- 交易量 -->
              <span class="label label-success"><?=number_format($allTransaction2, 2)?></span><!-- 去平后交易量 -->
            </nobr>
          <td>$<?=number_format($totalProfit,2)?></td><!-- 累计盈亏金额 -->
        </tr>
<?php } }else{ ?>
        <tr>
          <td colspan="6" style="font-family:italic;color:red;" class="text-center">此时间段内未查询到相关用户的交易记录</td>
        </tr> 
<?php } ?>
        <tr>
          <td colspan="<?=$searchLogin?8:6?>">
            <nobr>
              备注：1、表中<span style="display:inline-block;background-color:#F2DEDE;">&nbsp;&nbsp;深色部分&nbsp;&nbsp;</span>利润为正；2、<span style="color:red;">红色字体</span>为运营账号交易情况；3、<span class="label label-primary">合计</span>&nbsp;&nbsp;<span class="label label-success">去除平手后</span><!-- 去平后交易量 -->
            </nobr>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- 弹出，用于添加运营的利润表 -->
<!-- 弹出框 ,编辑分组信息-->
<div  class="modal fade" id="noProfitLoginDiv" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="content-title">运营账户</div>
  <div class="col-lg-12">
    <form class="form-horizontal" action="" method="post" name="noProfitLogin">
      <input type="hidden" name="search_login1" value="<?=$searchLogin?$searchLogin:''?>" />
      <input type="hidden" name="month" value="<?=$month?$month:''?>" />
      <div class="form-group">
        <label class="col-sm-1 control-label">周期</label>
        <div class="col-sm-5">
          <select name="noProfitMonth" class="form-control" id="noProfitMonth">
            <?php for ($i=date('n'); $i>0; $i--) { ?>
              <option value="<?=$i?>" <?php if ($monthForNoProfit == $i) { echo 'selected'; } else{ if ($month == $i) { echo 'selected'; } } ?>><?=date('Y')?>年<?=$i?>月</option>
            <?php } ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-1 control-label">账号</label>
        <div class="col-sm-5">
          <textarea class="form-control" name="noProfitLogin" id="noProfitLogin">
          <?=$no_profit_login?$no_profit_login:''?>
          </textarea>
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-1 control-label">备注</label>
        <div class="col-sm-5">
        <span style="color: red;">手动添加账号时，每一行设置一个账号。</span>
        </div>
      </div>

      <div class="form-group">
        <div class="col-sm-offset-1 col-sm-10 text-left">
          <button type="submit" class="btn btn-default">保存编辑</button>
          &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
        </div>
      </div>
    </form>   
  </div>
</div> 

<script>
  $(function () { $("[data-toggle='tooltip']").tooltip(); });
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

      //输入的不是数字（即输入的是文字）
      }else if (isNaN(manualAgency) && manualAgency != '') 
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
        $('#search_login1 option:first').prop("selected","selected");
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

    //改变查询日期时，触动ajax
    $('#noProfitMonth').change(function ()
    {
      var month = $('#noProfitMonth option:selected').val();//被选中的月份
      $.ajax({
        type: "POST",
        url: "<?=$url?>ajax/profit-list.ajax.php",
        data: "month="+month,
        success:function (noProfitLogins)//成功的处理函数
        {    
          $('#noProfitLogin').html(noProfitLogins);
        }
      });
    });
  });
  //点击表单页面的排序规则触发表单提交
  function triggerSubmit()
  {
    $(document).ready(function () {
      $('#searchProfitBtn').click();
    });
  }
  //利用ajax更新所查询用户的团队交易情况
  function updateSearchProfit(searchLogin, period)
  {
    $.ajax({
      type: "POST",
      url: "<?=$url?>ajax/profit-list.ajax.php",
      data:{"searchLogin":searchLogin,"period":period},
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