<?php
  if ($mem)
  {
    $groupInfo = $mem->get('groupInfo');
  }
  if (!$groupInfo)
  {
    $groupInfo = $dbLocal->get_results("SELECT `GROUP_ID`,`GROUP`,`RATIO`,`REWARD`,`GENRE` FROM MT4_NEW_GROUP ORDER BY `GROUP`", ARRAY_A);
    if ($mem && $groupInfo)
    {
      $mem->set('groupInfo', $groupInfo ,0 ,0);//设置成永久有效
    }
  }
  //修改已有组的信息
  if ($_POST['do'] == 'alterGroup')
  {
    unset($_POST['do']);
    $alterGroup = alterGroup($_POST);
    if ($mem)
    {
      $mem->delete('groupInfo');//删除缓存
    }
    if ($alterGroup)
    {
      header("Location:{$erpurl}user-managenent/group-list");//数据库修改成功，返回分组列表
    } else
    {
      header("Location:{$url}index.php?action=editGroupRst&rst=faile");//修改用户失败
    }
  }
?>
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
  <div class="content-title">分组列表</div>
  <div class="col-lg-12">
    <div class="alert alert-danger" role="alert"> 点击这里   
    <a style="font-style: italic"<?php if ($_SESSION['login']=='admin') { echo "href=\"".$erpurl."user-managenent/group-add\""; } else {  echo "data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"不具备权限\""; }?>>添加分组</a><br>
    </div>
  </div>
  <div class="col-lg-12">
    <table class="table table-hover table-striped">
      <thead>
        <tr>
          <th align="center"><nobr>序号</nobr></th>
          <th align="center"><nobr>分组名称</nobr></th>
          <th align="center"><nobr>分组性质</nobr></th><!-- 代理人/普通用户 -->
          <th align="center"><nobr>返佣比率</nobr></th><!-- 1%-3% -->
          <th align="center"><nobr>额外奖励</nobr></th><!-- 5%-20% -->
          <th align="center"><nobr>操作</nobr></th><!-- 删除和修改 -->
        </tr>
      </thead>
      <tbody>
<?php if ($groupInfo) {?>
<?php $i=0; foreach ($groupInfo as $key => $value) { $i++;?>
        <tr>
          <td><?=$i?></td>
          <td><?=$value['GROUP']?></td>
          <td><?=$value['GENRE']==1?$agentName:$tradeName?></td>
          <td><?=$value['GENRE']==1?($value['RATIO']*100).'%':'/'?></td>
          <td><?=$value['GENRE']==1?($value['REWARD']*100).'%':'/'?></td>
          <td>
            <a data-gid="6" class="btn btn-danger btn-sm delGroup" <?php if ($_SESSION['login']=='admin') { echo "onclick=\"deleteGroup(".$value['GROUP_ID'].")\""; } else {  echo "data-toggle='tooltip' data-placement='bottom' title='不具备权限'"; }?>>删除</a>
            <a href="" class="btn btn-warning btn-sm"   <?php if ($_SESSION['login']=='admin') { echo "onclick=\"editGroupInfo(".$value['GROUP_ID'].")\" data-toggle=\"modal\" data-target=\"#group-edit\""; } else {  echo "data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"不具备权限\""; }?>>编辑</a>
          </td>
        </tr>
<?php }  unset($groupInfo);?>
  
<?php } else {?>
        <td colspan="6">暂未查询到分组信息</td>
<?php } ?>
      </tbody>
    </table>
  </div>
</div>  

<!-- 弹出框 ,编辑分组信息-->
 <div  class="modal fade" id="group-edit" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="content-title">编辑分组信息</div>
  <div class="col-lg-12">
    <form class="form-horizontal" action="" method="post">
    <!-- 隐藏域，传递分组的ID -->
      <input type="hidden" name="GROUP_ID" id="GROUP_ID" value="">
    <!-- 隐藏域, 标识采取的行动 -->
      <input type="hidden" labelfir="1" name="do" value="alterGroup">
      <div class="form-group">
        <label for="GROUP" class="col-sm-1 control-label">分组名称</label>
        <div class="col-sm-5" id="1">
          <input type="text" class="form-control" id="GROUP" name="GROUP" value="" >
        </div>
      </div>
      <div class="form-group" id="RATIODIV">
        <label for="RATIO" class="col-sm-1 control-label">返点比率</label>
        <div class="col-sm-3">
          <input type="number" step="0.01" class="form-control" id="RATIO" name="RATIO" value="" size="4"><!-- 奖励下级居间人纯利返点的返点比率 -->
        </div>%
      </div>
      <div class="form-group" id="REWARDDIV">
        <label for="REWARD" class="col-sm-1 control-label">额外奖励</label>
        <div class="col-sm-3">
          <input type="number" step="0.01" class="form-control" id="REWARD" name="REWARD" value="" size="4"><!-- 奖励下级居间人纯利返点的返点比率 -->
        </div>%
      </div>
      <div class="form-group">
        <label for="GENRE" class="col-sm-1 control-label">分组类型</label>
        <div class="col-sm-5">
          <div class="radio" id="genre">
            <label class="radio-inline">
              <input type="radio" name="GENRE" id="GENRE1" value="1" >
              <?=$agentGroupName?>
            </label>
            <label class="radio-inline">
              <input type="radio" name="GENRE" id="GENRE2" value="2" >
              <?=$tradeGroupName?>
            </label>
          </div>
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
<script type="text/javascript">
  $(function () { $("[data-toggle='tooltip']").tooltip(); });
  /*利用ajax进行传值*/
  function editGroupInfo(GROUP_ID)
  {
    //清除之前的内容
    $.ajax({
      type: "POST",
      url: "<?=$url?>ajax/group-list.ajax.php",
      data: 'GROUP_ID='+GROUP_ID,
      dataType: "json",
      success:function (groupInfo)//成功的处理函数
      {    
        $("#GROUP_ID").val(groupInfo[0].GROUP_ID);//分组的ID
        $("#GROUP").val(groupInfo[0].GROUP);//分组的ID
        $("input:radio[name='GENRE']").each(function (index,domEle) {
          if ($(this).val() == groupInfo[0].GENRE)
          {
            if (groupInfo[0].GENRE == 1)
            {
              $("#RATIO").val((groupInfo[0].RATIO*100).toFixed(2));//分组的ID,toFixed为四舍五入的小数
              $("#REWARD").val((groupInfo[0].REWARD*100).toFixed(1));//分组的ID,toFixed为四舍五入的小数
              $("#RATIODIV").css('display','block'); 
              $("#REWARDDIV").css('display','block');
            } else if (groupInfo[0].GENRE == 2)
            {
              $("#RATIODIV").css('display','none'); 
              $("#REWARDDIV").css('display','none');
            }
            $(this).prop('checked', true);//以后凡是涉及单选和多选按钮的选中属性，一律使用prop，可以有效避免第二次不能用的问题
          } else
          {
            $(this).prop('checked', false);
          }
        });
        $("#GENRE1").click(
          function () {
            if ($(this).prop("checked")) 
            {
              $("#RATIO").val((groupInfo[0].RATIO*100).toFixed(2));//分组的ID,toFixed为四舍五入的小数
              $("#REWARD").val((groupInfo[0].REWARD*100).toFixed(1));//分组的ID,toFixed为四舍五入的小数
              $("#RATIODIV").css('display','block'); 
              $("#REWARDDIV").css('display','block');
            }
          });
        $("#GENRE2").click(
          function () {
            if ($(this).prop("checked")) 
            {
              $("#RATIO").val('');//分组的ID,toFixed为四舍五入的小数
              $("#REWARD").val('');//分组的ID,toFixed为四舍五入的小数
              $("#RATIODIV").css('display','none'); 
              $("#REWARDDIV").css('display','none');
            }
          });
      },
      error:function ()
      {
        alert("获取信息失败，请稍后再试");
      }
    });
  }
  
  function deleteGroup(groupId)
  {
    $.ajax({
      type: "POST",
      url: "<?=$url?>ajax/group-list.ajax.php",
      data: "groupIdforDelete="+groupId,
      success: function (msg)
      {
        if (msg>0)
        {
          location.href = "<?=$erpurl?>user-managenent/group-list";
        }
      } 
    });
  }
</script>