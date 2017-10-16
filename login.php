<?php 
  if ($_POST['login_account'] && $_POST['loginPassword'])
  {
    $login_account = $_POST['login_account'];
    $loginPassword = $_POST['loginPassword'];
    $loginRst = login($login_account, $loginPassword);
    //登录成功
    if ($loginRst == 'success')
    { 
      header("Location:{$erpurl}");//刷新网页
    } else if ($loginRst == 'wrongPrivi') //没有权限
    {
      header("Location:{$url}index.php?action=loginRst&rst=wrongPrivi");
    } else if ($loginRst == 'wrongPass')  //代理商密码错误
    {
      header("Location:{$url}index.php?action=loginRst&rst=wrongPass");
    } else if ($loginRst == 'wrongLogin') //账号错误
    {
      header("Location:{$url}index.php?action=loginRst&rst=wrongLogin");
    } else if ($loginRst == 'wrongAdminPass')//管理员密码错误
    {
      header("Location:{$url}index.php?action=loginRst&rst=wrongAdminPass");
    }
  }
?>
<div id="logContent">
  <h1 id="loginTitle">欢迎登陆</h1>
  <form class="form-horizontal" action="" method="post" role="form" id="loginFrame" onsubmit="return checkLogin()">
    <div class="form-group">
      <div class="col-sm-3">
        <input type="text" class="form-control" name="login_account" placeholder="账&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;号" id="login_account" class="form-control">
      </div>
    </div>
    <div class="form-group">
      <div class="col-sm-3">
        <input type="password" class="col-sm-1 form-control" name="loginPassword" placeholder="密&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;码" id="password" class="form-control">
      </div>
    </div>
    <div class="form-group">
      <div class="col-sm-3">
      <!-- 点击进行登录 -->
        <button type="submit" class="btn btn-primary">登&nbsp;&nbsp;&nbsp;录</button>
        <a href="<?=$erpurl?>obtain-password" style="font-style: italic;">点击这里获取密码。</a>
      </div>
    </div>
  </form>
</div>
<script type="text/javascript" charset="utf-8">
  function checkLogin(argument) 
  {
    var login_account = $('#login_account').val();
    var password = $('#password').val();
    if (login_account == '')
    {
      $('#login_account').attr('placeholder', '请输入账号')
      $('#login_account').focus();
      return false;
    } else if (password == '')
    {
      $('#password').attr('placeholder', '请输入密码')
      $('#password').focus();
      return false;
    }
  }
</script>