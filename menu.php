<!-- 顶部样式设计 -->
<nav class="navbar navbar-inverse navbar-static-top" role="navigation">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">切换菜单</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?=$erpurl?>"><span class="glyphicon glyphicon-home"></span>代理商后台</a>
    </div>
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
      <?php if( $_SESSION['login'] == 'admin' ){ ?>
        <li class="dropdown<?=$_GET['action']=='user-managenent'?' active':'';?>">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <span class="glyphicon glyphicon-user"></span>
            用户管理<b class="caret"></b>
          </a>
          <ul class="dropdown-menu">

            <li class="<?=$_GET['do']=='group-list'?'active':''?>"><a href="<?=$erpurl?>user-managenent/group-list">用户分组</a></li>
   
            <li class="<?=$_GET['do']=='user-list'?'active':''?>"><a href="<?=$erpurl?>user-managenent/user-list">用户列表</a></li>
          </ul>
        </li>
      <?php } ?>
        <li <?php if( $_GET['action']=='trade-list' || $_GET['action']=='trade-detail' ){ echo "class=\"active\""; } ?>>
          <a href="<?=$erpurl?>trade-list">
            <span class="glyphicon glyphicon-pencil"></span>实时统计<?=$_GET['action']=='trade-detail'?'--交易详情':''?>
          </a>
        </li>
        <li <?php if( $_GET['action']=='commission-list' || $_GET['action'] == 'commission-detail' ){ echo "class=\"active\""; } ?>>
          <a href="<?=$erpurl?>commission-list">
            <span class="glyphicon glyphicon-list-alt"></span>
            佣金报表<?=$_GET['action'] == 'commission-detail'?'--佣金详情':''?>
          </a>
        </li>
  <!-- 仅仅管理员可见 -->
  <?php if( $_SESSION['login'] == 'admin' ){?>
        <li <?php if( $_GET['action']=='profit-list' ){ echo "class=\"active\""; } ?>>
          <a href="<?=$erpurl?>profit-list">
            <span class="glyphicon glyphicon-list"></span>
            业务报表
          </a>
        </li>
  <?php }?>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li>
          <a href="" >
            <span class="glyphicon glyphicon-thumbs-up"></span>欢迎:&nbsp;&nbsp;<?=$_SESSION['name']?>
          </a>
        </li>
        <li style="color: #F2FCB8">
          <a href="<?=$erpurl?>logout"><span class="glyphicon glyphicon-off"></span> 注销
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
