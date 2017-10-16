<?php
  $message =  "";//提示信息
  $jumpUrl =  "";//跳转的地址
  $action = $_GET["action"];//操作类型
  $rst = $_GET["rst"];//操作结果
  //登录
  if ($action == 'loginRst')
  {
    if ($rst == 'wrongPass')
    {
      $message = '密码错误';
    } elseif ($rst == 'wrongPrivi')
    {
      $message = '您尚不具备权限，请重新获取密码或者联系技术人员';
    } else if ($rst=='success')
    {
      $message = '登录成功';
    } else if ($rst=='wrongLogin')
    {
      $message = '账号不存在';
    } else if ($rst == 'wrongAdminPass')
    {
      $message = '错误的管理员密码';
    }
    $jumpUrl  = "{$url}index.php";
  
  //添加分组
  } else if ($action == 'addGroupRst')
  {
    if ($rst == 'success')
    {
      $message = '添加分组成功';
      $jumpUrl  = "{$erpurl}user-managenent/group-list";
    } else
    {
      $message = '添加分组失败';
      $jumpUrl  = "{$erpurl}user-managenent/group-add";
    }

  //修改组的信息
  } else if ($action == 'editGroupRst')
  {
    if ($rst = 'success')
    {
      $message = '编辑分组信息成功';
      $jumpUrl  = "{$erpurl}user-managenent/group-list";
    } else
    {
      $message = '编辑分组信息失败';
      $jumpUrl  = "{$erpurl}user-managenent/group-list";
    }
  //修改单个用户的分组情况
  } else if ($action == 'updateUser')
  {
    if ($rst = 'faile')
    {
      $message = '用户分组修改失败';
    }
    $jumpUrl = "{$erpurl}user-managenent/user-list";
  //批量修改用户的情况
  } else if ( $action == 'batchUpdateUser')
  {
    if ($rst = 'faile')
    {
      $message = '用户信息批量修改失败';
    }
    $jumpUrl = "{$erpurl}user-managenent/user-list";
  }

  //获取密码
  else if ($action == 'queryPassRst')
  {
    if ($rst == 'success') 
    {
      $message = '密码获取成功，请前往邮箱进行获取,或前往邮箱垃圾箱';
      $jumpUrl  = "{$erpurl}";
    } else if ($rst == 'notPost')
    {
      $message = '密码获取失败';
      $jumpUrl  = "{$erpurl}";
    }
    else if ($rst == 'wrongLogin')
    {
      $message = '该登录账号不存在，请核对账号';
      $jumpUrl  = "{$erpurl}obtain-password";
    } else if ($rst == 'notEnable')
    {
      $message = '该登录账号已经被注销';
      $jumpUrl  = "{$erpurl}obtain-password";
    } else if ($rst == 'notSet')
    {
      $message = '您尚不具备相应权限，请重新获取密码或者联系技术人员';
      $jumpUrl  = "{$erpurl}obtain-password";   
    }
    else if ($rst == 'wrongEmail')
    {
      $message = '预留邮箱不一致，请核对邮箱';
      $jumpUrl  = "{$erpurl}obtain-password";
    } else if ($rst == 'wrongPrivi')
    {
      $message = '您尚不具备相应权限，请重新获取密码或者联系技术人员';
      $jumpUrl  = "{$erpurl}obtain-password";
    }
    
  }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>操作处理中……</title>
<style>
  .successNote{
    width: 100%;
    margin-top: 100px;
    text-align: center;
    background-color: #D9EDF7;
  }
</style>
<script type="text/javascript">
var index = 2;//时间
function changeTime()
{
  document.getElementById("timeSpan").innerHTML = index;
  index--;
  if (index < 0)
  {
    window.location = "<?php echo $jumpUrl?>";
  }
  else
  {
    window.setTimeout("changeTime()", 1000);
  }
}
</script>

</head>
<body onload="changeTime()"><!-- 自动加载 -->
  <div class="alert alert-success" role="alert">
    <?php if ($rst=='success') { echo '<span style="color:green;">',$message,'<span>。'; } else{echo '<span style="color:red;">',$message,'<span>';}?>页面将在 <span id="timeSpan">2</span> 秒钟内自动跳转！
    <br/><br/> 如果没有自动跳转，<a href="<?php echo $jumpUrl;?>">请点击这里</a>。
    <br/><br/>
  </div>
</body>
</html>

<script type="text/javascript">
(function () {
var wait = document.getElementById('wait'),href = document.getElementById('href').href;
var interval = setInterval(function () {
  var time = --wait.innerHTML;
  if (time <= 0) {
    location.href = href;
    clearInterval(interval);
  };
}, 1000);
})();
</script>