<?php 
  if ($_POST)
  {
    $queryPassRst = obtainPassword($_POST['login_account'],  $_POST['email']);
    if (is_array($queryPassRst))//获取密码成功
    {

      $sendEmail = postEmail($queryPassRst);
     　if ($sendEmail)
      {
        header("Location:{$url}index.php?action=queryPassRst&rst=success");//邮件已经发送，但是无法跳转到邮箱登录页面
      } else
      {
        header("Location:{$url}index.php?action=queryPassRst&rst=notPost");
      }
    } else if ($queryPassRst == 'wrongLogin')
    {
      header("Location:{$url}index.php?action=queryPassRst&rst=wrongLogin");//账号错误
    } else if ($queryPassRst == 'notEnable')//账号失效
    {
      header("Location:{$url}index.php?action=queryPassRst&rst=notEnable");//账号失效
    } else if ($queryPassRst == 'notSet')
    {
      header("Location:{$url}index.php?action=queryPassRst&rst=notSet");//账号未设置
    }
    else if ($queryPassRst == 'wrongEmail')
    {
      header("Location:{$url}index.php?action=queryPassRst&rst=wrongEmail");//邮箱不一致
    } else if ($queryPassRst == 'wrongPrivi')
    {
      header("Location:{$url}index.php?action=queryPassRst&rst=wrongPrivi");//不具备权限（不是代理商）
    }
  }
?>
<div id="logContent">
  <h1 id="loginTitle" style="color: #E9913E;">获取密码</h1>
  <form action="" method="post" name="queryPassFrm" id="loginFrame" class="form-horizontal" role="form" onsubmit="return checkObtainPassword()"><!-- 提交给自身页面处理 -->
    <!-- 登录账号 -->
    <div class="form-group">
      <div class="col-sm-3"> 
        <input type="number" name="login_account" id="login_account" class="form-control"  placeholder="账&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;号">
      </div>
    </div>
    <!-- 邮箱 -->
    <div class="form-group">
      <div class="col-sm-3">
        <input type="text" name="email" id="email" class="form-control" placeholder="邮&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;箱" ><br>
      </div>
    </div>
    <div class="form-group">
      <div class="col-sm-3">
        <input type="submit" value="获取密码" class="btn btn-info" >
        <a href="<?=$erpurl?>" style="font-style: italic;">返回登录。</a>
      </div>
    </div>
  </form>
</div>
<!-- 获取密码时输入框的限制 -->
<script type="text/javascript" charset="utf-8">
  function checkObtainPassword() 
  {
    var login_account = $('#login_account').val();//登录账号
    var email = $('#email').val();//登录邮箱
    var reg = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;//正则表达式验证邮箱是否正确
    if (login_account == '')
    {
      $('#login_account').attr('placeholder','请输入账号')
      $('#login_account').focus();
      return false;
    } else if (email == '')
    {
      $('#email').attr('placeholder','请输入邮箱')
      $('#email').focus();
      return false;
    } else if (!reg.test(email))
    {
      alert('邮箱格式不正确');
      $('#email').focus();
      return false;
    }
  }
</script>