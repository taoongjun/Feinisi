<?php 
//获取用户信息，根据登录者的身份（管理员和代理）
if ($mem) {
    $allUserInfo= $mem->get('allUserInfoRealTime');
}
//缓存中未能获取到信息
if (!$allUserInfo) {
    //$validGroupStr在config.php中配置
    $allUserInfo = $dbNRemote->get_results("SELECT login,agent_account,name FROM mt4_account", ARRAY_A);
    if ($mem) {
        $mem->set('allUserInfoRealTime', $allUserInfo, 0, 60);
    }
}
//管理员登录
if ($_SESSION['login']  == 'admin') {
    $adminUserInfo = array();//管理的用户的权限，是为了节约查询时的遍历时间
    $adminLogins = array();//管理的账号
    if ($allUserInfo) {    
        foreach ($allUserInfo AS $value) {
            $adminLogins[] = $value['login'];
            $adminUserInfo[$value['login']] =  $value;
        }
    }
//代理登录
} else  {
    if ($allUserInfo) { 
        //递归查询时，$resultlll此变量已经被污染，所有使用之前需要消除污染。
        global $resultlll;
        $resultlll = null;
        $adminLogins = queryLowerLogin($allUserInfo, $_SESSION['login']);//查询出所有和登录者有关的账号(并不包括其自己)
        if ($adminLogins) {
            array_unshift($adminLogins, $_SESSION['login']);//将用户自己的登录账号插入数组头部
        } else {
            $adminLogins = array($_SESSION['login']);
        }    
        $adminUserInfo = array();//取出便于后面根据代码查询    
        foreach ($allUserInfo as $key => $value) {
            if (in_array($value['login'], $adminlogins)) {
                $adminUserInfo[$value['login']] = $value;
            }
        }
    }
}

$relativeUserInfo = $adminUserInfo;
$relativNames = array_map('end', $relativeUserInfo);//取出姓名
array_multisort($relativNames, SORT_ASC, $relativeUserInfo);//根据姓名进行排序
unset($relativNames);

//一、默认进入，不进行任何操作
if (!$_POST['do']) {
    $logins = $adminLogins;//登录用户能管理的所有账号（能够具有权限的账号）
    $pageno = $_GET ['pageno'] ? $_GET ['pageno'] : 1;//当前页码
    //一1、按手动提交查询条件（分组）
    if ($_POST['userByGroup']) {
        //查询未分组
        if ($_POST['userByGroup'] == 'noGroup') {
            $loginsByGroup = $dbLocal->get_col("SELECT LOGIN FROM MT4_NEW_USERS WHERE GROUP_ID IS NOT NULL");
            $logins = array_diff($adminLogins, $loginsByGroup);//取差集

        //查询所有用户
        } else if ($_POST['userByGroup'] == 'allGroup') {
            $logins = $adminLogins;

        //按分组查询用户
        } else if (is_numeric($_POST['userByGroup'])) {
            $loginByGroupId = $dbLocal->get_col("SELECT LOGIN FROM MT4_NEW_USERS WHERE GROUP_ID = {$_POST['userByGroup']}");
            $logins = array_uintersect($adminLogins, $loginByGroupId, "strcasecmp");//合并分组，取出交集，即是分组查询的结果
        }
      
        $pageno = 1;//手动查询，页码从1开始
        $searchMethod = 'userByGroup';//查询方式
        $searchGoal = $_POST['userByGroup'];//查询目标(即分组的情况)

    //一2、按手动提交查询条件（ 按账号 ）
    } else if ($_POST['search_login1'] != '') {
        //此变量已经被污染，所有使用之前需要消除污染。
        global $resultlll;
        $resultlll = null;
        $logins = queryLowerLogin($adminUserInfo, $_POST['search_login1']);//查询备查账号的所有下级
        if ($logins) {
            array_unshift($logins, $_POST['search_login1']);//把备查账号放入数组头部
        } else {
            $logins = array($_POST['search_login1']);//所查询的用户没有下级，则只查该用户1人
        }
        $pageno = 1;//手动查询，页码从1开始
        $searchMethod = 'userByLogin';//查询方式
        $searchGoal = $_POST['search_login1'];//查询目标（查询的账号）
        
        //所查询用户的路径表
        $searchGoalHigherList =  array_reverse(getPath($searchGoal, $adminUserInfo));
        $searchGoalHigherList[] = $searchGoal;
    
    //一3、从url地址获取相关信息
    } else if ($_POST['search_login1'] == '' && isset($_POST['search_login1'])) {
        $pageno = 1;//手动查询，页码从1开始
        $logins = $adminLogins;
        $searchMethod = 'userByLogin';//查询方式
        $searchGoal = 'allUsers';//查询目标（查询的账号）
    }

    //三、不采用手动查询，根据url地址中的查询方式
    if (!isset($_POST['userByGroup']) && !isset($_POST['search_login1'])) {
        //url地址中没有查询方式（默认查询无分组的）
        if (!$_GET['searchMethod']) {
            $loginsByGroup = $dbLocal->get_col("SELECT LOGIN FROM MT4_NEW_USERS WHERE GROUP_ID IS NOT NULL");
            $logins = array_diff($adminLogins, $loginsByGroup);//取差集
            $searchMethod = 'userByGroup';
            $searchGoal = 'noGroup';  

        //url地址按分组查询
        } else if ($_GET['searchMethod'] == 'userByGroup') {
            //查询未分组
            if ($_GET['searchGoal'] == 'noGroup') {
                $loginsByGroup = $dbLocal->get_col("SELECT LOGIN FROM MT4_NEW_USERS WHERE GROUP_ID IS NOT NULL");//此处本可优化，但是影响太大，需要更改已经在使用的数据表，故而不予以优化
                $logins = array_diff($adminLogins, $loginsByGroup);//取交集

            //查询所有用户
            } else if ($_GET['searchGoal'] == 'allGroup') {
                $logins = $adminLogins;

            //按分组查询用户
            } else if (is_numeric($_GET['searchGoal'])) {
                //此处的缓存查询，当更改用户分组的操作进行时，就予以取消
                $loginByGroupId = $dbLocal->get_col("SELECT LOGIN FROM MT4_NEW_USERS WHERE GROUP_ID = {$_GET['searchMethod']}");
                $logins = array_uintersect($adminLogins, $loginByGroupId,"strcasecmp");//合并分组，取出交集，即是分组查询的结果
            }  
            $searchMethod = 'userByGroup';//查询方式
            $searchGoal = $_GET['searchGoal'];//查询目标(即分组的情况)

        //按用户账号查询
        } else if ($_GET['searchMethod'] == 'userByLogin') {
            //防止地址栏中非法查询
            if (in_array($_GET['searchGoal'], $adminLogins)) {
                //此变量已经被污染，所有使用之前需要消除污染。
                global $resultlll;
                $resultlll = null;
                $logins = queryLowerLogin($adminUserInfo, $_GET['searchGoal']);
                if ($logins) {
                    array_unshift($logins, $_GET['searchGoal']);
                } else {
                    $logins = array($_GET['searchGoal']);
                }
                $searchMethod = 'userByLogin';//查询方式
                $searchGoal = $_GET['searchGoal'];//查询目标（查询的账号）
            }
        }
    }
    
    //分页
    $rowCount = count($logins);//获取总的记录数 
    $pagesize = $pageSizeforUserList;//每一页显示的页码（config中可以自行设置）
    $pageCount = ceil ($rowCount / $pagesize);

    // 在初始浏览时,地址栏没有任何参数,包括pageno,所以应
    if ($pageno <= 0) {
        $pageno = 1;
    }

  //防止地址栏非法输入过大页码
    if ($pageno > $pageCount) {
        $pageno = $pageCount;
    }

  //开始查询用户
    if ($logins) {
        //按照页码取出需要查询的数字
        $needKeyStart = ($pageno-1) * $pagesize;//需要的账号的key的开始
        $needKeyEnd = $needKeyStart + $pagesize - 1;//需要的数组的结束
        $loginValue = '';
        $logins = array_values($logins);//让数组的下标从0开始
        $usersForDisplay = array();
         //$logins是所有分组下的login
        foreach ($logins as $key=>$login) {
            if ($key >= $needKeyStart && $key <= $needKeyEnd) {
                $usersForDisplay[] = $adminUserInfo[$login];
            } 
        }
    }
    //先全部查出来，再循环
    if ($mem) {
        $groupInfo = $mem->get('groupInfo');
    }
    if (!$groupInfo) {
        $groupInfo  = $dbLocal->get_results("SELECT `GROUP_ID`,`GROUP`,`RATIO`,`REWARD`,`GENRE` FROM MT4_NEW_GROUP ORDER BY `GROUP`", ARRAY_A);
        if ($mem) {
            $mem->set('groupInfo', $groupInfo, 0, 0);
        }
    }
    foreach($groupInfo AS $value) {
        if ($value['GENRE'] == 1) {
            $agentGroup[] = $value;
        } else if ($value['GENRE'] == 2) {
            $tradeGroup[] = $value;
        }
    }
    //查询分组信息
    $subGroups = queryGroups($logins);
    //页码编辑
    $pageHtml = getPageHtml($url, $pageno, $pageCount, $searchMethod , $searchGoal);//生成页码

//二 、对单个用户进行修改
} elseif ($_POST['do'] == 'alterGroup') {
    $InsertUserInGroup = updateUserGroup($_POST);
    if ($InsertUserInGroup) {
        header("Location:{$erpurl}user-managenent/user-list");//修改成功，不进行提示
    } else {
        header("Location:{$url}index.php?action=updateUser&rst=faile");//添加失败
    }

//三、批量修改用户信息
} else if ($_POST['do'] == 'batchAlterUsers') {
    unset($_POST['do']);
    $updateUsers = batchAlterUsers($_POST);
    if ($updateUsers) {
        header("Location:{$erpurl}user-managenent/user-list");//修改成功，不进行提示
    } else {
        header("Location:{$url}index.php?action=batchUpdateUser&rst=faile");//添加失败
    }
}
?>
<div class="content-body">
  <div class="content-title">用户列表</div>
  <div class="col-lg-12">
    <div class="alert alert-danger" role="alert">  
      <form action="" name="form1" method="post" class="form-horizontal" id="searchByGroupDiv">
        <select class="form-control" name="userByGroup" id="searchByGroup">
          <option value="" <?php if (in_array($searchGoal,$adminLogins)) { echo 'selected';}?>></option>
          <optgroup label="未分组">
            <option value="noGroup" <?php if ($searchGoal =='noGroup') { echo 'selected'; } ?>>未分组</option>
          </optgroup>
          <optgroup label="所有用户">
            <option value="allGroup" <?php if ($searchGoal == 'allGroup'){echo 'selected';} ?>>所有用户</option>
          </optgroup>
          <optgroup label="<?=$agentGroupName?>">
            <?php if ($agentGroup){ foreach ($agentGroup as $k1 => $v1) {?>
              <option value="<?=$v1['GROUP_ID']?>" <?php if ($searchGoal == $v1['GROUP_ID']){ echo 'selected'; }?>><?=$v1['GROUP']?></option>
            <?php } } ?> 
          </optgroup>
          <optgroup label="<?=$tradeGroupName?>">
            <?php if ($tradeGroup){ foreach ($tradeGroup as $k2 => $v2) { ?>
              <option value="<?=$v2['GROUP_ID']?>" <?php if ($searchGoal == $v2['GROUP_ID']){ echo 'selected'; }?>><?=$v2['GROUP']?></option>
            <?php } } ?>
          </optgroup>
        </select>
 
        <button type="submit" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="根据分组查看用户" id="searchByGroupBtn">
          分组查询  
        </button>  
      </form>
      <form action="" name="form1" method="post" class="form-horizontal">
        <input type="text" class="form-control" id="search_login2" placeholder="账号或姓名" value="<?php if (in_array($searchGoal,$adminLogins)){ echo $searchGoal; }?>"/> 
        <select class="form-control" id="search_login1" name="search_login1">
          <option value="" <?php if ($searchGoal == 'allUsers'){ echo 'selected'; } ?>>所有用户</option>
            <?php foreach ($relativeUserInfo as $key => $value) { ?>
              <option value="<?php echo $value['login'];?>" <?php if ($searchGoal == $value['login']){ echo 'selected'; } ?>><?php echo $value['name'];?></option>
            <?php } ?>
        </select>
        <button type="submit" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="根据账号查看用户" id="searchByLoginBtn">
          账号查询  
        </button>  
      </form>
      <?php if ($searchGoalHigherList){ ?> 用户上级追踪（高->低）：<?php  $listValue=''; foreach ($searchGoalHigherList as $key => $value) {
        if ( $adminUserInfo[$value]['name']){ $listValue.=$adminUserInfo[$value]['name'].'('.$value.')&nbsp;&nbsp;➻&nbsp;&nbsp;'; }
      } 
      $listValue = trim($listValue,'&nbsp;&nbsp;➻&nbsp;&nbsp;');
      echo $listValue?$listValue:'无上级';
      } ?>
    </div>
  </div>
  <div class="col-lg-12">
    <form action="" method="post" name="user-list" onsubmit="return batchAlterGroup()">
    <input type="hidden" name="do" value="batchAlterUsers"/><!-- 修改 -->
      <table class="table table-hover table-striped">
        <thead>
          <tr>
            <th align="center"><nobr>序号</nobr></th>
            <th align="center">
              <nobr>
                <input type="checkbox" id="selectAll" <?=$_SESSION['login']=='admin'?'':"disabled=\"disabled\""?>>
                <span data-toggle="tooltip" data-placement="top" title="管理员权限">全选</span>
              </nobr>
            </th>
            <th align="center"><nobr>用户账号</nobr></th><!-- 用户账号 -->
            <th align="center"><nobr>姓名</nobr></th><!-- 用户姓名 -->
            <th align="center"><nobr>上级</th><!-- 按所属机构 -->
            <th align="center"><nobr>所属分组</nobr></th><!-- 用户分组 -->
            <th align="center"><nobr>操作</nobr></th><!-- 删除和修改 -->
          </tr>
        </thead>
        <tbody>
  <?php foreach ($usersForDisplay as $key => $value) {?>
          <tr style="background:<?=$subGroups[$value['login']]?'':'#F2DEDE'?>;color:<?=$value['login']==$_POST['search_login1']?'red':''?>">
            <td><?=$key+1?></td>
            <td><input type="checkbox" name="login[]" value="<?=$value['login']?>" <?=$_SESSION['login']=='admin'?'':"disabled=\"disabled\""?>></td>
            <td><?=$value['login']?></td>
            <td><?=$value['name']?></td>
            <td><?=$adminUserInfo[$value['agent_account']]['name']?$adminUserInfo[$value['agent_account']]['name']:'/'?></td>
            <td>
            <nobr><?=$agentGroupName?>：</nobr><br>
            <nobr>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php 
    if ($agentGroup)
    {
    foreach ($agentGroup as $k1 => $v1) {?>
          <input type="radio" value="<?=$v1['GROUP_ID']?>" name="<?='GROUP_'.$value['login']?>[]" disabled="disabled" <?php if (array_key_exists($v1['GROUP_ID'],$subGroups[$value['login']]['GROUP'])){echo "checked=\"true\"";} ?>><?=$v1['GROUP'],'&nbsp;&nbsp;'?> <!-- 所属的代理组 -->
<?php } } else
{
            echo '暂未设置相应分组';
}?>
           </nobr>
           <br>
           <nobr><?=$tradeGroupName?>:</nobr><br>
           <nobr>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php if ($tradeGroup){ 
    foreach ($tradeGroup as $k2 => $v2) {?>
          <input type="checkbox" value="<?=$v2['GROUP_ID']?>" name="<?='GROUP_',$value['login']?>[]" disabled="disabled" <?php if (array_key_exists($v2['GROUP_ID'],$subGroups[$value['login']]['GROUP'])){echo "checked=\"true\"";} ?>><?=$v2['GROUP'],'&nbsp;&nbsp;'?> <!-- 所属的交易组 -->
<?php } } else{ echo '暂未设置相应分组'; }?>
           </nobr>
            </td>
            <td>
              <a href="" class="btn btn-warning btn-sm" <?php if ($_SESSION['login']=='admin'){ echo "onclick=\"alterGroup(".$value['login'].")\" data-toggle=\"modal\" data-target=\"#user-group-alter\""; } else{  echo "data-toggle=\"tooltip\" data-placement=\"bottom\" title=\"不具备权限\""; }?>>调整用户分组</a>
            </td>
          </tr>
  <?php }?>
        </tbody>
        <tfoot>
          <tr>
            <td></td>
            <td>    
              <input type="checkbox" id="reverse" <?=$_SESSION['login']=='admin'?'':"disabled=\"disabled\""?>/>  反选
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>
              <button type="submit" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="请先选定要调整的用户" <?=$_SESSION['login']=='admin'?'':"disabled=\"disabled\""?>>批量调整</button>
            </td>
          </tr>
          <tr>
            <td colspan="7"><nobr>备注：1、表中<span style="background:#F2DEDE;">&nbsp;&nbsp;深色区域&nbsp;&nbsp;</span>的用户不属于任何分组，请尽快联系相关人员进行调整;&nbsp;&nbsp;2、“/”表示没有上级;<?php if ($_POST['search_login1']){ ?>&nbsp;&nbsp;3、表中<span style="color:red">&nbsp;&nbsp;红色字体&nbsp;&nbsp;</span>是当前查询的用户。<?php } ?></nobr></nobr></td>
          </tr>
            <tr class="text-center"> 
              <td colspan="7">
                <ul class="pagination">
                <?=$pageHtml?>
                </ul><br>
                <?='共',$pageCount,'页',$rowCount,'名用户'?>
              </td>
            </tr>
        </tfoot>
      </table>
    </form>
  </div>
</div>  

<!-- 弹出框 ,同时兼具提交修改用户分组的功能-->
<form action="" name="" method="post">
  <div class="modal fade" id="user-group-alter" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <input type="hidden" name="do" value="alterGroup"/><!-- 修改分组的隐藏域 -->
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
          </button>
          <h4 class="modal-title" id="myModalLabel">
              调整用户分组
          </h4>
        </div>
        <div class="modal-body">
          <input type="hidden" id="logForAlterGroup" name="LOGIN">
          <?=$agentGroupName?>：<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php if ($agentGroup){ foreach ($agentGroup as $key => $value) { ?>
          <input type="radio" value="<?=$value['GROUP_ID']?>" name="GROUP_ID[]"><?=$value['GROUP'],'&nbsp;&nbsp;'?><!-- 所属的代理组 -->
          <?php } } ?>
          <input type="radio" value="" name="GROUP_ID[]">剔除代理身份
          <br>
          <?=$tradeGroupName?>：<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
          <?php if ($tradeGroup){ foreach ($tradeGroup as $k => $v) { ?>
          <input type="checkbox" value="<?=$v['GROUP_ID']?>" name="GROUP_ID[]"><?=$v['GROUP'],'&nbsp;&nbsp;'?><!-- 所属的交易组 -->
          <?php } } ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" 
              data-dismiss="modal">关闭
          </button>
          <button type="submit" class="btn btn-primary" <?=$_SESSION['login']=='admin'?'':"disabled=\"disabled\""?>>
              提交更改
          </button>
        </div>
      </div>
    </div>
  </div>
</form> 

<script>
  $(function () { $("[data-toggle='tooltip']").tooltip(); });
  $(document).ready(function () {
    $("#selectAll").click(//全选和反选之间的切换
      function () {
        if (this.checked) {
          $("input[name='login[]']").prop('checked', true);
          $("input[name^='GROUP_']").removeAttr("disabled");
          $('#reverse').prop('checked',false);
        } else {
          $("input[name^='GROUP_']").attr("disabled",true);
          $("input[name='login[]']").prop('checked', false);
          $('#reverse').prop('checked',false);
        } 
      } 
   );
    $("#reverse").click(function () {//反选  
      $('#selectAll').prop('checked',false); 
      $('#unSelect').prop('checked',false); 
      $("input[name='login[]']").each(function () {  
        $(this).prop("checked", !$(this).prop("checked"));
        if ($(this).prop("checked"))
        {
          $("input[name=\"GROUP_"+$(this).val()+"[]\"]").removeAttr("disabled"); 
        } else
        {
          $("input[name=\"GROUP_"+$(this).val()+"[]\"]").attr("disabled",true);
        }  
      });  
    }); 
    $("input[name='login[]']").click(function () {//手动选择之后，则全选和反选之间切换
      $('#selectAll').prop('checked',false); 
      $('#unSelect').prop('checked',false); 
      $('#reverse').prop('checked',false); 
      if ($(this).prop("checked"))
      {
        $("input[name=\"GROUP_"+$(this).val()+"[]\"]").removeAttr("disabled"); 
      } else
      {
        $("input[name=\"GROUP_"+$(this).val()+"[]\"]").attr("disabled",true);
      }
    });

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
      } else if (isNaN(manualAgency) && manualAgency != '') 
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

  });
 
  //修改用户分组
  function alterGroup(login) 
  {
    $.ajax({
      type: "POST",
      url: "<?=$url?>ajax/user-list.ajax.php",
      data: 'loginForAlter='+login,
      dataType: "json",
      //成功的处理函数
      success:function (groupInfo)
      {    
        $('#logForAlterGroup').val(login);//将分组的login加入到弹出框中
        $("input[name='GROUP_ID[]']").each(function () {
          $(this).prop('checked',false);
        });
        //groupInfo[i][0]分组ID  即GROUP_ID groupInfo[i][0]分组名称
        for(var i=0,l=groupInfo.length;i<l;i++)
        {
          $("input[name='GROUP_ID[]']").each(function () {
            if ($(this).val() == groupInfo[i][0])
            {
              $(this).prop('checked',true);
            }
          });
        }
      }
    });
  }
  $(function () { $('#user-eidt').modal({
     keyboard: true
  });});

  //删除用户
  function deleteUser(login)
  {
    $.ajax({
      type: "POST",
      url: "<?=$url?>ajax/user-list.ajax.php",
      data: "loginForDel="+login,
      success: function (msg) 
      {
        if (msg>0)
        {
          location.href="<?=$erpurl?>user-managenent/user-list";
        }
      }
    });
  }

  //批量添加分组
  function batchAlterGroup()
  {
    var falg = 0; 
    $("input[name='login[]']").each(function () { 
      if ($(this).prop("checked")) { 
        falg += 1; 
      } 
    }) ;
    if (falg > 0) 
      return true; 
    else 
      alert("至少选择一个用户");
      return false; 
  }

  //选定用户账号不能为空
  function search_login() {
    if ($("#search_login1 option:selected").val() == '')
    {
      alert("请选择用户");
      return false;
    }
  }
</script>