<?php 
if ($mem) {
  $allUserInfo= $mem->get('allUserInfoRealTimeId');
}
//如果缓存中没有获取到，就只能去数据库中进行获取
if (!$allUserInfo) {
  $allUserInfo = $dbNRemote->get_results("SELECT login,agent_account,phone,email,idcardNumber,name FROM mt4_account", ARRAY_A);//$validGroupStr在config.php中配置

  //数据中获取到以后，就顺便存入缓存中，以便于下次调用，因为随时可能更新，所以时间定为60秒
  if ($mem && $allUserInfo) {
    $mem->set('allUserInfoRealTimeId', $allUserInfo, 0, 60);
  }
}

//用于便于用户搜查的信息，按照姓名进行排序
$userInfoForSearch = $allUserInfo;
//取出姓名
$names = array_map('end', $userInfoForSearch);
//根据姓名进行排序
array_multisort($names, SORT_ASC, $userInfoForSearch);

//如果不进行手动查询，则默认顶级的账号是0(原系统的定义)
$topLogin = $_POST['search_login1'] ? $_POST['search_login1'] : 0;

//对信息进行处理，变成关联数组，便于调用
if ($allUserInfo) { 
  //消除递归循环的变量污问题。
  global $resultlll;
  $resultlll = null;
  $listLogins = queryLowerLogin($allUserInfo, $topLogin);
  
  //如果顶级的账号是有效的（不是0），则把其加到头部
  if ($topLogin ) {
    array_unshift($listLogins, $topLogin);
  }

  foreach ($allUserInfo AS $value) {
  	if (in_array($value['login'], $listLogins)) {
      $adminUserInfo[$value['login']] = $value;
  	}
    $relativeUserInfo[$value['login']] = $value;
    //所有有下级的账号
    $agentLogins[] = $value['agent_account'];
  }
  
  $agentLogins = array_unique($agentLogins);
  //计算每个账号距离顶级账号的层数
  if ($listLogins) {
    foreach ($listLogins as $value) {
      //消除变量污染
      global $resultsss;
      $resultsss = null;
      //获取每个账号的上级账号（最后一维是0，即最顶级用户的账号）
      $path = getPath($value, $adminUserInfo);
      //去掉最后一个元素，即0
      array_pop($path);
      $pathes[$value] = $path; 
      $levels[$value] = count($path);
    }
  }
}
?>
<div class="content-body">
  <div class="content-title">层级树状列表</div>
  <!-- 区段选择 -->
  <div class="col-lg-12">
    <div class="alert alert-danger" role="alert"> 
      <form action="" name="form1" method="post" class="form-horizontal">
        <input type="text" class="form-control" id="search_login2" placeholder="账号或姓名" value="<?=$_POST['search_login1'] ? $_POST['search_login1'] : ''; ?>"/> 
        <select class="form-control" id="search_login1" name="search_login1">
          <option value="">显示所有--</option>
          <?php  if ($userInfoForSearch) { foreach ($userInfoForSearch as $key => $value) { ?>
            <option value="<?php echo $value['login'];?>" <?php if($topLogin == $value['login']) { echo 'selected'; } ?>><?php echo $value['NAME'];?>
            </option>
          <?php } } ?>
        </select>
        <button type="submit" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="根据账号查看用户" id="searchByLoginBtn">
          账号查询  
        </button>  
      </form>
    </div>
  </div>

 <!-- 列表主体 -->
 <div class="col-lg-12">
 	<table class="table table-hover table-striped">
      <thead>
        <tr style="font-size:1.6rem;">
          <th align="center"><nobr>序号</nobr></th>
          <th align="center" onclick="showAndHideAll()"><span class="glyphicon glyphicon-folder-close" id="showAll"></span></th><!-- 字体图标，用于显示文件夹的打开和折叠状态 -->
          <th align="center"><nobr>账号</nobr></th>
          <th align="center"><nobr>姓名</nobr></th>
          <th align="center"><nobr>邮箱</nobr></th>
          <th align="center"><nobr>证件号</nobr></th>
          <th align="center"><nobr>手机</nobr></th>
          <th align="center"><nobr>上级姓名（账号）</nobr></th>
        </tr>
      </thead>
      <tbody>
      	<?php if ($listLogins): foreach ($listLogins as $key => $value): ?>
      	<tr <?php if (in_array($value, $agentLogins)) { echo "onclick = showAndHideNext(".$value.")"; } ?> class="<?=implode(' ' , $pathes[$value])?> <?= $relativeUserInfo[$value]['agent_account'] ? '' : 'first'?>" name="<?=$value?>"> 
      	  <td><?=$key + 1 ?></td>
      	  <td width="16px"><span class="<?=in_array($value, $agentLogins) ? 'glyphicon glyphicon-folder-close' : '' ?>" id="<?=$value?>"></span></td>
      	  <td>
      	    <nobr>
      	      <?php for ($i = 1; $i <= $levels[$value]; $i++) { ?> <span style="float: left; width: 20px; height: 20px; border-left: 1px dashed #000; border-bottom: 1px dashed #000;"></span><?php } ?><?=$relativeUserInfo[$value]['login'] ?>
      	    </nobr>
      	  </td>
          <td>
            <?=$relativeUserInfo[$value]['name'] ? $relativeUserInfo[$value]['name'] : '' ?>
          </td>
          <!-- 邮箱 -->
          <td>
            <?=$relativeUserInfo[$value]['email'] ? $relativeUserInfo[$value]['email'] : ''?>
          </td> 
          <!-- 证件号 --> 
          <td>
            <?=$relativeUserInfo[$value]['idcardNumber'] ? $relativeUserInfo[$value]['idcardNumber'] : ''?>
          </td>
          <!-- 手机号 -->      	  
          <td>
      	  	<?=$relativeUserInfo[$value]['phone'] ? $relativeUserInfo[$value]['phone'] : ''?>
      	  </td>
          <!-- 上级 -->
      	  <?php if($relativeUserInfo[$value]['agent_account'] ){ ?>
      	  <td>
            <nobr>
      	      <?=$relativeUserInfo[$relativeUserInfo[$value]['agent_account']]['name'] ?>（<?=$relativeUserInfo[$value]['agent_account'] ?>）
            </nobr>
      	  </td>
      	  <?php } else { ?>
      	  <td>/</td>
      	  <?php }?>
        </tr>
      	<?php endforeach; endif; ?>
      </tbody>
 	</table>
 </div>
</div>
<script type="text/javascript">
  $(document).ready(function () {
    //手动输入框获得焦点，则下拉菜单没有值
    $('#search_login2').focus(function () {
      $('#search_login1').val(''); 
    });
    
    //手动输入代码结束以后，下拉菜单跳转到相应的值
    $('#search_login2').blur(function () {
      var manualAgency = $.trim($('#search_login2').val());//手动输入的机构代码(去除空格)
      //输入的是数字
      if (!isNaN(manualAgency)) {
        var re = new RegExp("^"+manualAgency+"[0-9]*");//以特定账号开头进行匹配
        $('#search_login1 option').each(function (i) {
          if (re.test($(this).val())) {
            $(this).prop('selected',true);//下拉框选中相应的选项（切记以后不要用attr,因为只能使用一次）
            $('#search_login2').val($(this).val());//输入框中变成账号
            return false;
          }
        });
      //输入的不是数字（即输入的是文字）
      } else if (isNaN(manualAgency) && manualAgency != '') {
        var re = new RegExp("[\u4e00-\u9fa5]*"+manualAgency+"[\u4e00-\u9fa5]*","g");//匹配任意中文
        $('#search_login1 option').each(function (i) {
          //匹配成功
          if (re.test($(this).text())) {
            $(this).prop('selected',true);//下拉框选中相应的选项（切记以后不要用attr,因为只能使用一次）
            $('#search_login2').val($(this).val());//输入框中变成账号
            return false;
          }
        });
      }
      //匹配失败
      if ($('#search_login1').val()=='') {
        alert('您输入的查询信息不正确或您无权限');
        $('#search_login2').val('');
      }
    });

    //下拉菜单选中的用户，代码在左边显示
    $('#search_login1').change(function () {
      if ($("#search_login1 option:selected").val()!='') {
        $('#search_login2').val($("#search_login1 option:selected").val());
      } else {
        $('#search_login2').val('');
      }
    });

    //载入时默认只打开第一级代理
    $("tbody tr:not(.first)").hide();
  });

  //点击隐藏和显示
  function showAndHideNext(login) {
    //如果字体图标的文件夹时是打开的，那么则进行关闭，并且隐藏所有的下级元素
    if ($("#"+login).hasClass('glyphicon-folder-open') ) {
      //所在的将字体图标切换成关闭样式
      $("#"+login).removeClass('glyphicon-folder-open').addClass('glyphicon-folder-close');
      //隐藏所有的下级元素
      $("."+login).each(function (){
        if ($(this).is(":visible")) {
          $(this).hide();//不占位
        }
      });

    //如果字体图标显示的文件夹是打开的
    } else if ($("#"+login).hasClass('glyphicon-folder-close')) {
      //首先切换文件夹
      $("#"+login).removeClass('glyphicon-folder-close').addClass('glyphicon-folder-open');
      $("."+login).each(function (){
        $(this).show();//不占位，下面的写法占位
        thisid = $(this).attr('name');
        //只有上级的文件夹时打开的，才进行显示
        if ($("#"+thisid).hasClass('glyphicon-folder-close')) {
          $("."+thisid).hide();
        }
      });

      $("."+login).each(function (){
        //获取下级的name值
        thisid = $(this).attr('name');
        //只有上级的文件夹时打开的，才进行显示
        if ($("#"+thisid).hasClass('glyphicon-folder-close')) {
          $("."+thisid).hide();
        }
      });
    }
    
    //顶部打开和关闭按钮的样式改变
    //若顶级账号的关闭数量等与所有的顶级文件夹关闭数量，说明全部关闭，需要关闭显示所有的文件夹
    if ($('.first').children("td:nth-child(2)").children(".glyphicon").length == $('.first').children("td:nth-child(2)").children(".glyphicon-folder-close").length) {
      //顶部的字体图标打开
      if ($("#showAll").hasClass("glyphicon-folder-open")) {
        //顶部的打开和关闭所有的文件夹关闭样式
        $("#showAll").removeClass('glyphicon-folder-open').addClass('glyphicon-folder-close');
      }

    //若关闭
    } else {
      if ($("#showAll").hasClass("glyphicon-folder-close")) {
        //顶部的打开和关闭所有的文件夹关闭样式
        $("#showAll").removeClass('glyphicon-folder-close').addClass('glyphicon-folder-open');
      }
    } 
  }

  //显示和隐藏所有
  function showAndHideAll() {
    if ($("#showAll").hasClass("glyphicon-folder-open")) {
      //所有的文件夹都关闭
      $(".glyphicon-folder-open").removeClass('glyphicon-folder-open').addClass('glyphicon-folder-close');
      //关闭所有非顶级的文件夹样式
      $("tbody tr:not(.first)").hide();

      /*$("tbody tr:not(.first)").css("visibility","hidden");*/
    } else if ($("#showAll").hasClass("glyphicon-folder-close")) {
      //顶部文件夹切换为打开样式
      $('.glyphicon-folder-close').removeClass('glyphicon-folder-close').addClass('glyphicon-folder-open');
      //所有的文件夹都切换为打开模式
      $("tbody tr").show();
    }
  }
</script>