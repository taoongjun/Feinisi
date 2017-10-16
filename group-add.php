<?php
  if ($_POST)//此处仅仅只能判断是否提交表单
  {
    $addGroup = addGroup($_POST);
    if ($mem)
    {
      $mem->delete('groupInfo');
    }
    if ($addGroup)
    {
      header("Location:{$erpurl}user-managenent/group-list");//数据库修改成功，返回分组列表
    } else
    {
      header("Location:{$url}index.php?action=addGroupRst&rst=faile");//添加失败
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
  <div class="content-title">添加分组</div>
  <div class="col-lg-12">
    <div class="alert alert-danger" role="alert"> 
      点击这里   
      <a style="font-style: italic" href="<?=$erpurl?>user-managenent/group-list">返回分组列表</a>
    </div>
  </div>
  <div class="col-lg-12">
    <form class="form-horizontal" action="" method="post" name="add-group-frm" onsubmit="return checkGroupSubmit()">
      <div class="form-group">
        <label for="GENRE" class="col-sm-2 control-label">分组类型</label>
        <div class="col-sm-5">
          <div class="radio" id="genre">
          <!-- 自然人的下级只能是自然人，经济机构的下级只能是经纪机构 -->
            <label class="radio-inline">
              <input type="radio" name="GENRE" value="1" id="agent1">
              <?=$agentGroupName?>
            </label>
            <label class="radio-inline">
              <input type="radio" name="GENRE" value="2" id="agent2">
              <?=$tradeGroupName?>
            </label>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="GROUP" class="col-sm-2 control-label">分组名称</label>
        <div class="col-sm-5">
          <input type="text" class="form-control" id="GROUP" name="GROUP" value="" >
        </div>
        <em id="checkGroup"></em>
      </div>
      <div class="form-group" id="ratioDiv">
        <label for="RATIO" class="col-sm-2 control-label">返点比率</label>
        <div class="col-sm-1">
          <input type="number" step="0.01" class="form-control" id="RATIO" name="RATIO" value=""><!-- 奖励下级居间人纯利返点的返点比率 --></nobr>
        </div>%
        <em id="checkRatio"></em>
      </div>
      <div class="form-group" id="rewardDiv">
        <label for="REWARD" class="col-sm-2 control-label">额外奖励</label>
        <div class="col-sm-1">
          <input type="number" step="0.1" class="form-control" id="REWARD" name="REWARD" value=""><!-- 奖励下级居间人纯利返点的返点比率 -->
        </div>%
        <em id="checkReward"></em>
      </div>

      <div class="form-group">
        <label class="col-sm-2 control-label"></label>
        <div class="col-sm-1">
          <button type="submit" class="btn btn-default">添加</button>
        </div>
      </div>
    </form>   
  </div>
</div>
<script type="text/javascript">
  $(document).ready(function ()
  {
    $('#GROUP').change(function (){
      $.ajax({
        url: "<?=$url?>ajax/group-add.ajax.php",
        type: 'POST',
        data: {'GROUP': $('#GROUP').val()},
        success: function (msg)
        {
          var html = msg > 0 ? '<font color="#4E7504"><b>√分组名可以使用</b></font>' : '<font color="red"><b>×分组名已存在！</b></font>';
          $("#checkGroup").html(html);
        }
      });
    });

    $('#RATIO').change(function ()
    {
      var ratio = $('#RATIO').val();
      if (ratio >3 || ratio<0)
      {
        var html = '<font color="red"><b>×返佣比率必须在%0到%3之间！</b></font>';
        
      } else
      {
        var html = '';
      }
      $('#checkRatio').html(html);
    });

    $('#REWARD').change(function ()
      {
       var reward = $('#REWARD').val();
       if (reward>10 || reward<0)
       {
         var html = '<font color="red"><b>×额外奖励必须在%0到%10之间！</b></font>';
         
       } else
       {
         var html = '';
       }
       $('#checkReward').html(html);
    });
/*代理人要设置返佣比率和奖励*/
    $("#agent1").click(function (){
      $("#ratioDiv").fadeIn();
      $("#rewardDiv").fadeIn();
    });
/*交易者没有返佣比率和奖励比率*/
    $("#agent2").click(function (){
      $("#ratioDiv").fadeOut();
      $("#rewardDiv").fadeOut();
    });
  });

  function checkGroupSubmit()
  {
    var group = $('#GROUP').val();
    var ratio = $('#RATIO').val();
    var reward = $('#REWARD').val();
    var genre = $('input[name="GENRE"]:checked').val();
    if (!genre)
    {
      alert('请设置分组类型');
      return false;
    } else
    {
      if (!group)
      {
        alert('请设置分组名称');
        $('#GROUP').focus();
        return false;  
      }
      //若是代理人
      if (genre == 1)
      {
        if (!ratio)
        {
          alert('请设置返佣比率');
          $('#RATIO').focus();
          return false;
        } else if (ratio<0 || ratio>3)
        {
          alert('分组比率必须设置在%0到%3中');
          $('#RATIO').focus();
          return false;
        } else if (!reward)
        {
          alert('请设置额外奖励');
          $('#REWARD').focus();
          return false;
        } else if (reward<0 || reward>10)
        {
          alert('额外奖励必须设置在0%到%10之间');
          $('#REWARD').focus();
          return false;
        }
      }
    }
  }
</script>
