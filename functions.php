<?php
/********************************************************************************************************************************************************
*功能：进行登录
*参数：登录账号和密码
*返回值：返回成功或者失败的原因
*/
function login($login, $password)
{
  global $dbLocal;
  //管理登录
  if (!is_numeric($login))//说明是管理员的登录
  {
    $password1 = substr(sha1(md5(md5($password))), 2, 13);
    if ($password1 == 'bfce6c06e146e')//密码不能写在程序中
    {
      $_SESSION['login'] = $login;//用session更安全
      setcookie('login',  $login, time() + 3600 * 2);
      $_SESSION['password'] = $password;//用session更安全
      $name = 'admin';
      $_SESSION['name'] = 'admin';//
      return 'success';
    }else
    {
      return 'wrongAdminPass';//管理员密码错误
    }
  }
  //非管理员登录
  else
  {
    global $dbNRemote;
    global $validGroupStr;
    $emailAndName = $dbNRemote->get_row("SELECT name,email FROM mt4_account WHERE login={$login}", ARRAY_A);
    if (!$emailAndName)//账号错误，或者账号已经失效
    {
      return 'wrongLogin';
    }else
    {
      //获取用户类的类型和各个类型对应的不同的组
      $group_genre = $dbLocal->get_results("SELECT a.`GENRE` AS GENRE FROM `MT4_NEW_GROUP` AS a LEFT JOIN `MT4_NEW_USERS` as b ON a.`GROUP_ID`=b.`GROUP_ID` WHERE b.`LOGIN` = '{$login}'",ARRAY_A);
      
      if (!$group_genre)//说明数据库中没有进行分组
      {
        return 'wrongPrivi';
      }else
      {
        //用户所在的各个组中其中有一个有代理商的身份即可
        foreach ($group_genre as $key => $value) 
        {
          //只要其中有一个组的身份类型是代理商
          if ($value['GENRE'] == 1 )
          {
            $passwordStr = sha1(md5(md5($login . $emailAndName['name'] . $emailAndName['email'])));
            $truePassword = substr($passwordStr, substr($login, -1), 10);  
            //密码正确
            if ($truePassword === $password)
            {
              $_SESSION['login'] = $login;//用session更安全
              setcookie('login',  $login, time() + 3600 * 2);//设置是方便ajax页面调用
              $_SESSION['password'] = $password;//用session更安全
              $_SESSION['name'] = $emailAndName['name'];
              $result =  'success';
            }else
            {
              $result = 'wrongPass';
            }
            break;
          }
        }
        if (!$result) {
          return 'wrongPrivi';//没有权限
        } else {
          return $result;
        }
      }
    }
  }
}
/***************************************************************
*功能：代理商获取密码
*参数：$login登录账号;$email登录邮箱
*返回值：成功返回数组，失败返回不具有权限
*/
function obtainpassword($login, $email)
{
  global $dbLocal;//本地数据库实例化对象
  global $dbNRemote;//远程数据库实例化

  $emailAndName = $dbNRemote->get_row("SELECT name,email FROM mt4_account WHERE login={$login}",ARRAY_A);
  //登录账号不正确
  if (!$emailAndName)
  {
    return 'wrongLogin';
  }
  //邮箱不正确
  else if (($emailAndName['email'] != $email) && $emailAndName)
  {
    return 'wrongEmail';//邮箱与预留邮箱不一致

  }else
  {
    //获取用户类的类型和各个类型对应的不同的组
    $group_genre = $dbLocal->get_results("SELECT a.`GENRE` AS GENRE,a.`GROUP` AS `GROUP` FROM `MT4_NEW_GROUP` AS a LEFT JOIN `MT4_NEW_USERS` as b ON a.`GROUP_ID`=b.`GROUP_ID` WHERE b.`LOGIN` = '{$login}'", ARRAY_A);
    
    //账号未进行设置
    if (!$group_genre)
    {
      return 'notSet';
    }
    //已经设置
    else
    {
      //用户所在的各个组中其中有一个有代理商的身份即可
      foreach ($group_genre as $key => $value) 
      {
        //只要其中有一个组的身份类型是代理商(值为1)
        if ($value['GENRE'] == 1)
        {
          $passwordStr = sha1(md5(md5($login . $emailAndName['name'] . $emailAndName['email'])));//从数组中取出的值和直接拼接字符串的结果是不一样的
          $password = substr($passwordStr, substr($login, -1), 10);
          $result =  array('email'=>$email,'password'=>$password);
          break;//将不再循环
        }
      }
      //不具备代理权限
      if (!$result) {
        return 'wrongPrivi';
      } else {
        return $result;
      }
    }
  }
}
/******************************************************************
*功能：发送密码到用户的邮箱
*参数：数组，包含用户的邮箱地址和密码
*返回值：成功返回true，失败返回false
*/
function postEmail($info) {
    global $erpurl;
    date_default_timezone_set('Etc/UTC');
    require './lib/class/PHPMailerAutoload.php';
    //Create a new PHPMailer instance
    $mail = new PHPMailer;
    $mail->CharSet    = "UTF-8";
    //Tell PHPMailer to use SMTP
    $mail->isSMTP();
    $mail->SMTPSecure = "SSL";//必须大写
    $mail->Debugoutput = 'html';
    //Set the hostname of the mail server
    $mail->Host = "smtp.chengmail.cn";//服务器地址
    //Set the SMTP port number - likely to be 25, 465 or 587
    $mail->Port = 25;
    //Whether to use SMTP authentication，使用smtp服务器
    $mail->SMTPAuth = true;
    //Username to use for SMTP authentication
    $mail->Username = "op@service-finsfx.net";//服务器的地址
    //Password to use for SMTP authentication
    $mail->Password = "Op12345678";//密码
    //Set who the message is to be sent from
    $mail->setFrom('op@service-finsfx.net', '菲尼斯微交易');
    //Set an alternative reply-to address，回复地址
    $mail->addReplyTo('513380450@qq.com', '密码管理员');
    //Set who the message is to be sent to
    $mail->addAddress($info['email'], '代理用户');
    //Set the subject line
    $mail->Subject = '登陆密码获取';
    $mail->Body = "<h1'>获取密码<h1/>尊敬的代理商：<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;您的密码是"
        . $info['password'] . "!您可以选择重新获取！返回登录网址，请点击<a href=\"" . $erpurl . "\">链接地址</a>"; //邮件内容
    $mail->AltBody = "尊敬的代理商：，您的密码是" . $info['password']; //当邮件不支持html时备用显示，可以省略 
    //send the message, check for errors
    if (!$mail->send()) {
        return false;
    } else {
        return true;
    }
}
/*********************************************************************
*功能：添加分组
*参数：$groupInfo添加的信息
*返回值：插入的结果
*/
function addGroup($groupInfo)
{
  $keys = '';//字段
  $values = '';//插入值
  foreach ($groupInfo as $key => $value) 
  {
    $keys .= '`'.$key.'`,';

    if ($key == 'RATIO')
    {
      $value = $value/100;
    }else if ($key == 'REWARD')
    {
      $value = $value/100;
    }
    $values .= '\''.$value.'\',';
  }
  $keys = rtrim($keys, ',');//去掉最后一个逗号
  $values = rtrim($values,',');//去掉最后一个逗号
  global $dbLocal;
  $dbLocal->query("set names utf8");
  $addGroup = $dbLocal->query("INSERT MT4_NEW_GROUP($keys) VALUES($values)");
  if ($addGroup)
  {
    return true;
  }else
  {
    return false;
  }
}
/************************************************************************
*功能：生成页码
*参数：总页数和页码
*返回值：返回页码html
*/
function getPageHtml($url, $pageno, $pageCount, $searchMethod , $searchGoal)
{
      //1、只有一页
      if ($pageCount == 1)
      {
        $pageHtml = <<<EOT
        <li><a onclick="return false" disabled>首页</a></li>
        <li><a onclick="return false" disabled>&laquo;</a></li>
        <li><a onclick="return false" disabled style="background: #ddd;">1</a></li>
        <li><a onclick="return false" disabled>&raquo;</a></li>
        <li><a onclick="return false" disabled>尾页</a></li>
EOT;
      }

      //2、共有两页
      else if ($pageCount == 2)
      {
        // 当前为第一页
        if ($pageno == 1)
        { 
          $pageHtml = <<<EOT
            <li><a onclick="return false" disabled>首页</a></li>
            <li><a onclick="return false" disabled>&laquo;</a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">1</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">2</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">&raquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">尾页</a></li>
EOT;
        }
          // 当前页为最后页
        else if ($pageno == 2) 
        { 
          $pageHtml=<<<EOD
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">首页</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">&laquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">1</a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">2</a></li>
            <li><a onclick="return false" disabled>&raquo;</a></li>
            <li><a onclick="return false" disabled>尾页</a></li>
EOD;
        }
      } 
      //3、共三页
      else if ($pageCount == 3)
      {
        if ($pageno == 1)
        { 
          $pageHtml = <<<EOT
            <li><a onclick="return false" disabled>首页</a></li>
            <li><a onclick="return false" disabled>&laquo;</a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">1</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">2</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=3&searchMethod=$searchMethod&searchGoal=$searchGoal">3</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">&raquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=3&searchMethod=$searchMethod&searchGoal=$searchGoal">尾页</a></li>
EOT;
        }
        else if ($pageno == 2)
        {
          $pageHtml=<<<EOD
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">首页</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">&laquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">1</a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">2</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=3&searchMethod=$searchMethod&searchGoal=$searchGoal">3</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=3&searchMethod=$searchMethod&searchGoal=$searchGoal">&raquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=3&searchMethod=$searchMethod&searchGoal=$searchGoal">尾页</a></li>
EOD;
        }
        // 当前页为最后页
        else if ($pageno == 3) 
        { 
          $pageHtml=<<<EOD
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">首页</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">&laquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">1</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">2</a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">3</a></li>
            <li><a onclick="return false" disabled>&raquo;</a></li>
            <li><a onclick="return false" disabled>尾页</a></li>
EOD;
        }
      }
      //4、共四页
      else if ($pageCount == 4)
      {
        if ($pageno == 1)
        { 
          $pageHtml = <<<EOT
            <li><a onclick="return false" disabled>首页</a></li>
            <li><a onclick="return false" disabled>&laquo;</a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">1</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">2</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=3&searchMethod=$searchMethod&searchGoal=$searchGoal">3</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=4&searchMethod=$searchMethod&searchGoal=$searchGoal">4</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">&raquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=4&searchMethod=$searchMethod&searchGoal=$searchGoal">尾页</a></li>
EOT;
        }
        else if ($pageno == 2)
        {
          $pageHtml=<<<EOD
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">首页</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">&laquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">1</a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">2</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=3&searchMethod=$searchMethod&searchGoal=$searchGoal">3</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=4&searchMethod=$searchMethod&searchGoal=$searchGoal">4</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=3&searchMethod=$searchMethod&searchGoal=$searchGoal">&raquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=4&searchMethod=$searchMethod&searchGoal=$searchGoal">尾页</a></li>
EOD;
        }
        else if ($pageno == 3) 
        { 
          $pageHtml=<<<EOD
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">首页</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">&laquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">1</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">2</a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">3</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=4&searchMethod=$searchMethod&searchGoal=$searchGoal">4</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=4&searchMethod=$searchMethod&searchGoal=$searchGoal">&raquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=4&searchMethod=$searchMethod&searchGoal=$searchGoal">尾页</a></li>
EOD;
        }
        else if ($pageno == 4)
        {
          $pageHtml=<<<EOD
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">首页</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=3&searchMethod=$searchMethod&searchGoal=$searchGoal">&laquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">1</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">2</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=3&searchMethod=$searchMethod&searchGoal=$searchGoal">3</a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">4</a></li>
            <li><a onclick="return false" disabled>&raquo;</a></li>
            <li><a onclick="return false" disabled>尾页</a></li>
EOD;
        }
      }

      //5、五页或者以上
      else 
      {
        if ($pageno == 1)
        { 
          $pageHtml = <<<EOT
            <li><a onclick="return false" disabled>首页</a></li>
            <li><a onclick="return false" disabled>&laquo;</a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">1</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">2</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=3&searchMethod=$searchMethod&searchGoal=$searchGoal">3</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=4&searchMethod=$searchMethod&searchGoal=$searchGoal">4</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=5&searchMethod=$searchMethod&searchGoal=$searchGoal">5</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=2&searchMethod=$searchMethod&searchGoal=$searchGoal">&raquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$pageCount&searchMethod=$searchMethod&searchGoal=$searchGoal">尾页</a></li>
EOT;
        }
        else if ($pageno == 2)
        {
          $pageHtml=<<<EOD
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">首页</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">&laquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">1</a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">2</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=3&searchMethod=$searchMethod&searchGoal=$searchGoal">3</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=4&searchMethod=$searchMethod&searchGoal=$searchGoal">4</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=5&searchMethod=$searchMethod&searchGoal=$searchGoal">5</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=3&searchMethod=$searchMethod&searchGoal=$searchGoal">&raquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$pageCount&searchMethod=$searchMethod&searchGoal=$searchGoal">尾页</a></li>
EOD;
        }
        //第三页以后
        else if ($pageno >= 3)
        { 
          $prevPage1 = $pageno -1; //上一页
          $prevPage2 = $pageno -2; //上两页
          $nextPage1 = $pageno +1; //下一页
          $nextPage2 = $pageno +2; //下两页
          //不是倒数两页
          if (($pageno != $pageCount-1) && ($pageno != $pageCount) )
          {
          $pageHtml=<<<EOF
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">首页</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$prevPage1&searchMethod=$searchMethod&searchGoal=$searchGoal">&laquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$prevPage2&searchMethod=$searchMethod&searchGoal=$searchGoal">$prevPage2 </a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$prevPage1&searchMethod=$searchMethod&searchGoal=$searchGoal">$prevPage1 </a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">$pageno</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$nextPage1&searchMethod=$searchMethod&searchGoal=$searchGoal">$nextPage1</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$nextPage2&searchMethod=$searchMethod&searchGoal=$searchGoal">$nextPage2</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$nextPage1&searchMethod=$searchMethod&searchGoal=$searchGoal">&raquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$pageCount&searchMethod=$searchMethod&searchGoal=$searchGoal">尾页</a></li>
EOF;
          //倒数第二页
          }else if ($pageno == $pageCount-1)
          {
          $prevPage3 = $pageno -3; //上三页
          $pageHtml=<<<EOF
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">首页</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$prevPage1&searchMethod=$searchMethod&searchGoal=$searchGoal">&laquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$prevPage3&searchMethod=$searchMethod&searchGoal=$searchGoal">$prevPage3 </a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$prevPage2&searchMethod=$searchMethod&searchGoal=$searchGoal">$prevPage2 </a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$prevPage1&searchMethod=$searchMethod&searchGoal=$searchGoal">$prevPage1 </a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">$pageno</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$pageCount&searchMethod=$searchMethod&searchGoal=$searchGoal">$pageCount</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$pageCount&searchMethod=$searchMethod&searchGoal=$searchGoal">&raquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$pageCount&searchMethod=$searchMethod&searchGoal=$searchGoal">尾页</a></li>
EOF;
          //倒数第一页
          }else if ($pageno == $pageCount)
          {
          $prevPage3 = $pageno -3; //上三页
          $prevPage4 = $pageno -4; //上四页
          $pageHtml=<<<EOF
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&searchMethod=$searchMethod&searchGoal=$searchGoal">首页</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$prevPage1&searchMethod=$searchMethod&searchGoal=$searchGoal">&laquo;</a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$prevPage4&searchMethod=$searchMethod&searchGoal=$searchGoal">$prevPage4 </a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$prevPage3&searchMethod=$searchMethod&searchGoal=$searchGoal">$prevPage3 </a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$prevPage2&searchMethod=$searchMethod&searchGoal=$searchGoal">$prevPage2 </a></li>
            <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$prevPage1&searchMethod=$searchMethod&searchGoal=$searchGoal">$prevPage1 </a></li>
            <li><a onclick="return false" disabled style="background: #ddd;">$pageno</a></li>
            <li><a onclick="return false" disabled>&raquo;</a></li>
            <li><a onclick="return false" disabled>尾页</a></li>
EOF;
          }
        }

      }
    return $pageHtml;
}

/************************************************************************
*功能：编辑分组
*参数：提交的信息
*返回值：插入的结果
*/
function alterGroup($groupInfo)
{
  global $dbLocal;
  $updateValue = '';
  $GROUP_ID = $groupInfo['GROUP_ID'];
  unset($groupInfo['GROUP_ID']);

  foreach ($groupInfo as $key => $value) 
  {
    if ($key == 'RATIO')
    {
      $value = $value/100;//返佣比率
    }else if ($key == 'REWARD')
    {
      $value = $value/100;//奖励比率
    }
    $updateValue .= "`$key` = '{$value}',";
  }
  $updateValue = rtrim($updateValue,',');
  $result = $dbLocal->query("update MT4_NEW_GROUP set {$updateValue} WHERE GROUP_ID = {$GROUP_ID}");
  if ($result)
  {
    return true;
  }else
  {
    return false;
  }
}
/***************************************************************************
*功能：根据登录账号查询用户的分组信息
*参数：$logins为包含所查询的登录账号的一维数组
*返回值：$newGroup用户的分组信息
*/
function queryGroups($logins)
{
  $loginValue = '';
  foreach ($logins as $login) 
  {
    $loginValue .= ','.$login;
  }
  $loginValue = ltrim($loginValue, ',');//去除第一个逗号
  global $dbLocal;
  $sql = "SELECT a.LOGIN,a.GROUP_ID,b.`GROUP` FROM MT4_NEW_USERS as a left join MT4_NEW_GROUP AS b on a.GROUP_ID = b.GROUP_ID WHERE LOGIN IN ({$loginValue}) ORDER BY LOGIN ASC";
  $userGroupInfo = $dbLocal->get_results("$sql",ARRAY_A);//查询出所有分组信息
  $newGroup = mergeGroup($userGroupInfo);
  return $newGroup;
}
/***************************************************************************
*功能：对用户的分组信息进行合并
*参数：$userInfo
*/
function mergeGroup($userInfo)
{ 
  $LOGINArr = array();
  foreach ($userInfo as $key => $value) 
  {
    $LOGIN_Arr[] = $value['LOGIN'];
  }
  $LOGIN_Arr = array_unique($LOGIN_Arr);//不重复的账号
  
  $newArr = array();
  foreach ($LOGIN_Arr as $key => $value) 
  {
    $newArr[$value]['GROUP'] = array();
    foreach ($userInfo as $k => $v) 
    {
      if ($v['LOGIN'] == $value)
      {
        if ($v['GROUP'])
        {
          $newArr[$value]['GROUP'][$v['GROUP_ID']] = $v['GROUP']; 
        } 
      }
    }
    ksort($newArr[$value]['GROUP']);//按键值从低到高排序，krsort相反
  }
  return $newArr;
}

//更新用户的分组信息，也可以批量对象对用户更新，也可以单个对用户进行更新。
function updateUserGroup($info)
{
  $newInfo = array();
  //批量选定用户添加进入相应的组
  global $dbLocal;
  $dbLocal->query("DELETE FROM MT4_NEW_USERS WHERE LOGIN = '{$info['LOGIN']}'");//先删除该用户所有的分组
  //没有删除代理商分组

  foreach($info['GROUP_ID'] as $v)
  {
    if ($v)
    {
      $values.='(\''.$info['LOGIN'].'\','.$v.'),';
    }
  }
  if ($values)
  {
    $values = rtrim($values,',');//去掉最后一个逗号
    $insertLoginGroupIdRst = $dbLocal->query("INSERT MT4_NEW_USERS(`LOGIN`,`GROUP_ID`) values $values");
    //删除重复信息
    $deleteRepeat = $dbLocal->query("delete from MT4_NEW_USERS where ID in (select * from (select max(ID) from MT4_NEW_USERS group by LOGIN,GROUP_ID having count(LOGIN) > 1 and count(GROUP_ID)>1) as b)");
  }
  if ($insertLoginGroupIdRst)
  {
    return true;
  }else
  {
    $dbLocal->query("DELETE FROM MT4_NEW_USERS WHERE LOGIN = '{$info['LOGIN']}'");//先删除所有分组
    return false;
  }
}

//把对象数组变成普通数组
function object_to_array($obj) 
{ 
  $_arr = is_object($obj) ? get_object_vars($obj) : $obj; 
  foreach ($_arr as $key => $val) 
  { 
    $val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val; 
    $arr[$key] = $val; 
  } 
  return $arr; 
}

//将php页面获取的数据插入数据库中
//$table数据表名,$info数组信息名称，$url为获取内容的地址 
function storeInfoIntoMysql($url ,$table)
{
  $info = file_get_contents($url);//获取页面的内容
  $info = json_decode($info);//转化为数组，是对象数组
  $columnStr = '';//字段名称的字符串值
  $valueStr = '';//插入值
  $columeArr = array();
  foreach ($info[2] as $key => $value) 
  {
    $columnStr.='`'.$key.'`,';
    $columeArr[] = $key;
  }
  $columnStr = rtrim($columnStr, ',');//取出最后一个逗号
  foreach ($info as $key => $value) 
  {
    $str = '(';
    for($i = 0;$i<count($columeArr); $i++)
    {
     if ($i != (count($columeArr)-1))
      {
        $str .= "'{$value->$columeArr[$i]}',";
      }else
      {
        $str .= "'{$value->$columeArr[$i]}'";
      }
    }
    $str .= '),';
    $valueStr .= $str;
    unset($str);
  }
  $valueStr = rtrim($valueStr, ',');
  global $dbLocal;
  $insert = $db->query("INSERT {$table}({$columnStr}) values{$valueStr}");
  if ($insert)
  {
    return true;
  }else
  {
    return false;
  }
}

/**********************************************************************************
*功能：分别获取每个直接下级的交易量和交易金额（包括去平前和去平后）
*参数：$login为登录账号LOGIN,$startDate, $endDate和开始的时间和结束的时间
*返回值：$result为多维数组array(//去平前0=>array(0=>array(0=>'',1=>'')),//去平后1=>array())
*/
function getFirstLowerTrade($login, $startDate, $endDate)
{
  global $validGroupStr;
  $startTime = date('Y-m-d 00:00:00',strtotime($startDate));//这个月1号的0点
  $endTime = date('Y-m-d 23:59:59',strtotime($endDate));//截止今天的日期
  global $dbNRemote;
  //获取直接下级去平前交易情况
  $getFirstLowerInfo = $dbNRemote->get_results("SELECT A.login AS LOGIN,A.name AS NAME,SUM(CASE WHEN B.update_time BETWEEN '{$startTime}' AND '{$endTime}' THEN 1 ELSE 0 END) AS TRADE_QUAN,SUM(CASE WHEN B.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}'THEN B.VOLUME ELSE 0 END) AS TRANSACTION FROM MT4_USERS AS A LEFT JOIN MT4_OPTIONS AS B ON A.LOGIN=B.LOGIN WHERE A.AGENT_ACCOUNT={$login} AND A.`GROUP` IN($validGroupStr) AND A.ENABLE=1 GROUP BY A.LOGIN",ARRAY_A);

  $getFirstFlatQuna = $dbRemote->get_results("SELECT SUM(CASE WHEN B.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' AND B.PROFIT!=0 THEN 1 ELSE 0 END) AS TRADE_QUAN,SUM(CASE WHEN B.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' AND B.PROFIT!=0 THEN B.VOLUME ELSE 0 END) AS TRANSACTION,SUM(CASE WHEN B.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' THEN B.PROFIT ELSE 0 END) AS PROFIT FROM MT4_USERS AS A LEFT JOIN MT4_OPTIONS AS B ON B.LOGIN=A.LOGIN WHERE A.AGENT_ACCOUNT={$login} AND A.`GROUP` IN($validGroupStr) AND A.ENABLE=1 GROUP BY A.LOGIN",ARRAY_A);
  $result = array($getFirstLowerInfo, $getFirstFlatQuna);
  if ($getFirstLowerInfo && $getFirstFlatQuna)
  {
    return $result;
  }else
  {
    return false;
  }
}
//功能：分别获取每个直接下级的交易金额（只计算去平后）
function getFirstTransac($login, $startDate, $endDate)
{
  global $validGroupStr;
  $startTime = date('Y-m-d 00:00:00',strtotime($startDate));//这个月1号的0点
  $endTime = date('Y-m-d 23:59:59',strtotime($endDate));//截止今天的日期
  global $dbNRemote;
  //获取直接下去平后的交易情况
  $getTransaction = $dbNRemote->get_results("SELECT A.login AS LOGIN,A.name AS NAME,SUM(CASE WHEN B.result<>3 THEN B.money ELSE 0 END) AS TRANSACTION FROM mt4_account AS A LEFT JOIN mt4_binary_option_history AS B ON B.login=A.login WHERE A.agent_account={$login} AND B.update_time BETWEEN '{$startTime}' AND '{$endTime}' AND B.status=1 GROUP BY A.login",ARRAY_A);
 
  if ($getTransaction)
  {
    return $getTransaction;
  }else
  {
    return false;
  }
}
function getFirstTransaction($adminLogins, $month, $adminUserInfo)
{
  global $validGroupStr;
  global $dbNRemote;
  $month = $month>9?$month:'0'.trim($month,'0');//月份，不足两位的补0
  $startDate = date('Y-'.$month.'-01');//查询月份的第一天
  $endDate = date('Y-m-d', strtotime("$startDate +1 month -1 day"));//最后一天
  $startTime = date('Y-m-d 00:00:00',strtotime($startDate));//这个月1号的0点
  $endTime = date('Y-m-d 23:59:59',strtotime($endDate));//截止今天的日期
  
  //获取每个人的交易额
  if (count($adminLogins)<50)
  {
    $adminLoginsV = implode(',', $adminLogins);
    $everyTradeInfo = $dbRemote->get_results("SELECT LOGIN,COUNT(ID) AS TRADE_QUAN,SUM(CASE WHEN PROFIT<>0 THEN 1 ELSE 0 END) AS TRADE_QUAN2,SUM(VOLUME) AS TRANSACTION,SUM(CASE WHEN PROFIT<>0 THEN VOLUME ELSE 0 END) AS TRANSACTION2,SUM(PROFIT) AS PROFIT FROM MT4_OPTIONS WHERE LOGIN IN($adminLoginsV) AND CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' GROUP BY LOGIN",ARRAY_A);  
  }
  //人数多时，牺牲性能全部查询出来
  else
  {
    //获取每个人的交易数据
    $everyTradeInfo = $dbRemote->get_results("SELECT A.LOGIN,COUNT(A.ID) AS TRADE_QUAN,SUM(CASE WHEN A.PROFIT<>0 THEN 1 ELSE 0 END) AS TRADE_QUAN2,SUM(A.VOLUME) AS TRANSACTION,SUM(CASE WHEN A.PROFIT<>0 THEN VOLUME ELSE 0 END) AS TRANSACTION2,SUM(A.PROFIT) AS PROFIT FROM MT4_OPTIONS AS A LEFT JOIN MT4_USERS AS B ON A.LOGIN=B.LOGIN WHERE A.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' AND B.`GROUP` IN($validGroupStr) AND B.ENABLE=1 GROUP BY A.LOGIN",ARRAY_A);
  }

  $everAgentFirLowTran = array();//每个代理直接下级的交易情况
  if ($everyTradeInfo)
  {
    //循环查询交易记录
    foreach ($everyTradeInfo as $key => $value) 
    {
      $upLogin = $adminUserInfo[$value['LOGIN']]['AGENT_ACCOUNT'];
      if ($upLogin)
      {
        $everAgentFirLowTran[$upLogin]['FIR_TRADE_QUAN']+=$value['TRADE_QUAN'];
        $everAgentFirLowTran[$upLogin]['FIR_TRADE_QUAN2']+=$value['TRADE_QUAN2'];
        $everAgentFirLowTran[$upLogin]['FIR_TRANSACTION']+=$value['TRANSACTION'];
        $everAgentFirLowTran[$upLogin]['FIR_TRANSACTION2']+=$value['TRANSACTION2'];
      }
    }
  }
  return $everAgentFirLowTran;//每个代理直接下级的交易情况
}
/*****************************************************************************************
*功能：将标准的下级居间人信息组成的多维数组取出机构代码组成一维数组
*参数：$arr为查询到的所有的下级居间人构成的对象数组$db->get_results获取
*返回值： $lowerAgencys3为所有的从属下级居间人构成的一维数组（方便调用）
*/
function queryLowerLogin($arr,$login)
{
  global $resultlll;
  foreach ($arr as $value)
  {
    if ($value['agent_account'] == $login)
    {
      $resultlll[] = $value['login'];
      queryLowerLogin($arr,$value['login']);
    }
  }
  return $resultlll;
}

function queryLowerLogin1($arr,$login)
{
  global $resultlll;
  foreach ($arr as $value)
  {
    if ($value['AGENT_ACCOUNT'] == $login)
    {
      $resultlll[] = $value['LOGIN'];
      queryLowerLogin1($arr,$value['LOGIN']);
    }
  }
  return $resultlll;
}
/*****************************************************************************************
*功能：获取具有代理商权限的机构，剔除不具有代理商身份的LOGIN
*参数：$logins为机构代码组成的一维数组(既包括具有代理商权限的，也包括不具有代理商权限的)
*返回值：完全具有代理商权限的登录账号
*/
function getAgentLoginsInfo($logins)
{
  //一、提取出登录账号组成的字符串
  $loginValue = implode(',', $logins);
  global $dbLocal; 
  //二、删选出具有代理商身份的用户的登录账号，分组名称，返佣比率，额外奖励额度
  if (count($logins)<50)
  {
    $getAgentLoginsInfo = $dbLocal->get_results("SELECT a.LOGIN,b.`GROUP`,b.RATIO,b.REWARD FROM MT4_NEW_USERS AS a LEFT JOIN MT4_NEW_GROUP AS b ON a.GROUP_ID=b.GROUP_ID WHERE a.LOGIN IN ($loginValue) AND b.GENRE =1 GROUP BY a.LOGIN",ARRAY_A);
    //获取出代理商的信息
    foreach ($getAgentLoginsInfo as $value) 
    {
      $result[$value['LOGIN']] = $value;
    }
  }else
  {
    $allAgentLoginsInfo = $dbLocal->get_results("SELECT a.LOGIN,b.`GROUP`,b.RATIO,b.REWARD FROM MT4_NEW_USERS AS a LEFT JOIN MT4_NEW_GROUP AS b ON a.GROUP_ID=b.GROUP_ID WHERE b.GENRE =1 GROUP BY a.LOGIN",ARRAY_A);
    foreach ($allAgentLoginsInfo as $key => $value) 
    {
      if (in_array($value['LOGIN'], $logins))
      {
        $result[$value['LOGIN']] = $value;
      }
    }
  }
  krsort($result);
  return $result;
}
/*****************************************************************************************
*功能：判断某个元素是否在多维数组中
*参数：$value-判断的元素；$array参考的数组
*返回值：true和false
*/
function deep_in_array($value, $array) 
{ 
  foreach($array as $item) { 
    if (!is_array($item)) 
    { 
      if ($item == $value) {
        return true;
      } else {
        continue; 
      }
    } 
    if (in_array($value, $item)) 
    {
      return true; 
    } else if (deep_in_array($value, $item)) 
    {
      return true; 
    }
  } 
  return false; 
}
/****************************************************************************************
*功能：寻找某个元素的路径
*参数：登录账号
*/
function getPath($login, $adminUserInfo)
{
  global $resultsss;//独特的变量名减少污染
  if (isset($adminUserInfo[$login]))
  {
    $path = $adminUserInfo[$login]['agent_account'];
    $resultsss[] = $path;
    getPath($adminUserInfo[$login]['agent_account'], $adminUserInfo );
  }
  return $resultsss;
}

function getPath1($login, $adminUserInfo)
{
  global $resultsss;//独特的变量名减少污染
  if (isset($adminUserInfo[$login]))
  {
    $path = $adminUserInfo[$login]['AGENT_ACCOUNT'];
    $resultsss[] = $path;
    getPath1($adminUserInfo[$login]['AGENT_ACCOUNT'],$adminUserInfo );
  }
  return $resultsss;
}

/******************************************************************************************
*功能：提取出所有的登录login
*参数：包含login的二维数组
*返回值：一维数组
*/
function getLogins ($loginInfo)
{
  $logins = array();
  foreach ($loginInfo as $key => $value) {
    $logins[] = $value['LOGIN'];
  }
  return $logins;
}

/********************************************************************************************
*功能：提取出所有用户的NAME值
*参数：包含所有login信息的一维数组
*返回值：以以以登录账号作为键名的一维数组
*/
function getNames($logins)
{
  $loginValue = '';
  foreach ($logins as $key => $value) {
    $loginValue .= ','.$value;
  }
  $loginValue = ltrim($loginValue, ',');//去除第一个逗号
  global $dbRemote;
  $loginNames = $dbRemote->get_results("SELECT LOGIN as LOGIN,NAME AS NAME FROM MT4_USERS WHERE LOGIN IN({$loginValue})",ARRAY_A);
  $names = array();
  foreach($loginNames as $value)
  {
    $names[$value['LOGIN']] = $value['NAME'];
  }
  return $names;
}

/*************************************************************************************************
*功能：计算所有代理的实时返佣
*参数：$adminLogins所有账号，$validGroupStr当前有效的账号，$currentRatio实时返佣的比率
*返回值：所有代理的实时返佣
*/
function getCurrentCommission($pathArr, $agentsRatio, $allLowerTransaction, $currentRatio)
{
  //查询
  $currentCommission = array();
  foreach ($pathArr as $login => $path) 
  {
    $allLowerTransaction[$login] = $allLowerTransaction[$login] ? $allLowerTransaction[$login] : 0;//如不存在该值，说明是0
    //如果该代理的返点不为0
    if ($agentsRatio[$login]!=0)
    {
      $currentCommission[$login]['COM'] += $allLowerTransaction[$login] * $currentRatio;
      $currentCommission[$login]['TRAN'] += $allLowerTransaction[$login];
    }
    //返点比率等于零
    else if ($agentsRatio[$login]==0)
    {
      $currentCommission[$login]['COM'] = 0;//本身的返点比率为0
      $currentCommission[$login]['TRAN']  = 0;
      $pathaaaa = $path;//将路径数组你逆向排列
      unset($pathaaaa['KT']);
      foreach ($pathaaaa as $hlogin) 
      { 
        //一直往上，直到找到一个返点比率不为零的用户
        if ($agentsRatio[$hlogin] != 0)
        {

          $currentCommission[$hlogin]['COM'] +=  $allLowerTransaction[$login] * $currentRatio;
          $currentCommission[$hlogin]['TRAN'] += $allLowerTransaction[$login];
          break;
        }
      }
    }
  }
  return $currentCommission;
}

/***************************************************************************************************
*功能：返回路径数组
*参数：$logins代理账号组成的一维数组
*返回值：返会从最底层代理到最高层代理的顺序
*/
function getPathArr($agentLogins, $adminUserInfo)
{
  foreach ($agentLogins as $key => $login) 
  {
    global $resultsss;$resultsss = null;
    $pathArr[$login.' '] = getPath($login, $adminUserInfo);
  }
  
 //将二维数组变成一维数组
  $aa = array();
  foreach($pathArr as $key=>$value)
  {
    $aa = array_merge($value,$aa);
  }
  $keyTimes = array_count_values($aa);  //每一个值出现的次数

  $arrSort = array();  
  $newPath = array();
  foreach($pathArr AS $key => $row)
  {  
    $newPath[$key] = $row;
    $newPath[$key]['KT'] = $keyTimes[trim($key)]?$keyTimes[trim($key)]:0;
    $arrSort['KT'][$key] = $keyTimes[trim($key)]?$keyTimes[trim($key)]:0; 
  }  
  
  array_multisort($arrSort['KT'], SORT_ASC, $newPath);  //排序的关联数组键名不变，但是数组键名会变成01234

  
  foreach ($newPath as $key => $value) 
  {
    $result[trim($key)] = $value;
  }

  return $result;
}

function getPathArr1($agentLogins, $adminUserInfo)
{
  foreach ($agentLogins as $key => $login) 
  {
    global $resultsss;$resultsss = null;
    $pathArr[$login.' '] = getPath1($login,$adminUserInfo);
  }
  
 //将二维数组变成一维数组
  $aa = array();
  foreach($pathArr as $key=>$value)
  {
    $aa = array_merge($value,$aa);
  }
  $keyTimes = array_count_values($aa);  //每一个值出现的次数

  $arrSort = array();  
  $newPath = array();
  foreach($pathArr AS $key => $row)
  {  
    $newPath[$key] = $row;
    $newPath[$key]['KT'] = $keyTimes[trim($key)]?$keyTimes[trim($key)]:0;
    $arrSort['KT'][$key] = $keyTimes[trim($key)]?$keyTimes[trim($key)]:0; 
  }  
  
  array_multisort($arrSort['KT'], SORT_ASC, $newPath);  //排序的关联数组键名不变，但是数组键名会变成01234
  foreach ($newPath as $key => $value) 
  {
    $result[trim($key)] = $value;
  }

  return $result;
}

/***************************************************************************************************
*功能：判断元素在二维数组中出现的次数
*参数：$a查询的元素，$arr查询的二维数组
*返回值：元素出现的次数
*/
function getEleTimes($a,$arr)
{
  $times = 0;
  foreach ($arr as $key => $value) 
  {
    if (in_array(rtrim($a), $value))
    {
      $times+=1;
    }else
    {
      $times+=0;
    }
  }
  return $times;
}
/****************************************************************************************************
*功能：计算所有客户的佣金和额外奖励
*参数：$pathArr是包含每个元素路径的二维数组（索引数组）
切记：：：：若出现 代理->交易者->代理的情况，会出现奖励和点差返佣被中途消失的情况
*/
function calculateCommsission($pathArr,$newAgentLogins,$allTradeInfo,$currentRatio)
{
  $dirCom = array();//直接返佣directCommission
  $gapCom = array();//下级点差返佣
  $gapComUp = array();//返点给上级
  $reward = array();//下级奖励返佣
  $rewardUp = array();//奖励上级
  $allCom = array();//返佣之和
  $curCom = array();//实时返佣curCom=currentCommission
  $monCom = array();//月结佣金monCom=monthlCommission（=佣金合计-实时返佣）
  //将索引数组变成关联数组，方便操作
  foreach ($pathArr as $login => $path) 
  {
    //一、KT = 等于0，说明是最底层的代理商（点差返佣和奖励肯定是0）
    if ($path['KT'] == 0)
    {
      $dirCom[$login]['dirCom'] = $allTradeInfo[$login]['FIR_TRANSACTION2']*$newAgentLogins[$login]['RATIO'];//直接佣金 = 直属下级有效交易额*返点比率
      $gapCom[$login]['gapCom'] = 0;//点差佣金
      $reward[$login]['reward'] = 0;//奖励佣金
      $allCom[$login]['allCom'] = $dirCom[$login]['dirCom'];//佣金合计
    }
    else
    { 
      $dirCom[$login]['dirCom'] = $allTradeInfo[$login]['FIR_TRANSACTION2']*$newAgentLogins[$login]['RATIO']/100;//直接佣金 = 直属下级有效交易额*返点比率
      $gapCom[$login]['gapCom'] = $gapCom[$login]['gapCom']?$gapCom[$login]['gapCom']:0;
      $reward[$login]['reward'] = $reward[$login]['reward']?$reward[$login]['reward']:0;
      $allCom[$login]['allCom'] = $dirCom[$login]['dirCom'] + $gapCom[$login]['gapCom'] + $reward[$login]['reward'];//佣金合计
    }
    
    //计算实时返佣
    //若返佣比率为0，则不存在实时返佣
    if ($newAgentLogins[$login]['RATIO'] == 0)
    {
      $curCom[$login]['curCom'] = 0;//实时返佣金额为0
      $newPath = $path;
      unset($newPath['KT']);
      //往上找，找到一个返佣比率不等于零的代理，把实时返佣送给他
      foreach($newPath as $hlogin)  
      {
        if ($newAgentLogins[$hlogin]['RATIO']>0)
        {
          $curCom[$hlogin]['curCom'] += $allTradeInfo[$login]['FIR_TRANSACTION2']/100*$currentRatio;
          break;
        }
      }
    }
    //临时返佣比率不等于零
    else
    {
      $curCom[$login]['curCom'] += $allTradeInfo[$login]['FIR_TRANSACTION2']/100*$currentRatio;
    }

    $monCom[$login]['monCom'] = $allCom[$login]['allCom'] - $curCom[$login]['curCom'];//月结佣金
    
    //三、判断是否奖励上级，奖励完之后进行返点给上级
    //该交易者的返佣比率和上级的代理相等或者还高于上级，则说明要进行奖励，$pathArr[$login][0]返回的是用户的直接上级
    if ($newAgentLogins[$login]['RATIO'] >= $newAgentLogins[$pathArr[$login][0]]['RATIO'])//$pathArr[$login][0]正好是是上级
    {
      //上下级比率相同时，上级奖励=下级纯利润*奖励比率
      $reward[$pathArr[$login][0]]['reward'] += $newAgentLogins[$pathArr[$login][0]]['REWARD']*$allCom[$login]['allCom'];
      //奖励上级的金额 = 本身的返佣之和*返佣比率
      $rewardUp[$login]['rewardUp'] = $newAgentLogins[$pathArr[$login][0]]['REWARD']*$allCom[$login]['allCom'];
      $rewardUp[$login]['rewardUpLogin'] = $pathArr[$login][0];
      //对该路径进行翻转，确保从底层向上
      $newPath = $path;
      unset($newPath['KT']);
      foreach ($newPath as $hlogin) 
      { 
        //如果能找到比率比他高的人，则返点给他，此处找到一次就必须停止，因为点差只补一次
        if (isset($newAgentLogins[$hlogin])&&($newAgentLogins[$hlogin]['RATIO']>$newAgentLogins[$login]['RATIO']))
        {
          $gapCom[$hlogin]['gapCom'] += ($newAgentLogins[$hlogin]['RATIO']-$newAgentLogins[$login]['RATIO'])*$allTradeInfo[$login]['FIR_TRANSACTION2']/100;
          $gapComUp[$login]['gapComUp'] = ($newAgentLogins[$hlogin]['RATIO']-$newAgentLogins[$login]['RATIO'])*$allTradeInfo[$login]['FIR_TRANSACTION2']/100;
          $gapComUp[$login]['gapComUpLogin'] = $hlogin;
          break;
        }
      }
    //四、判断是否返点给上级
    //直接上级的返点比率高于当前用户时，则只进行点差返点
    }else if ($newAgentLogins[$login]['RATIO'] < $newAgentLogins[$pathArr[$login][0]]['RATIO'])
    {
      $rewardUp[$login]['rewardUp'] = 0;//不奖励上级
      $gapCom[$pathArr[$login][0]]['gapCom'] += ($newAgentLogins[$pathArr[$login][0]]['RATIO']-$newAgentLogins[$login]['RATIO'])*$allTradeInfo[$login]['FIR_TRANSACTION2']/100;
      $gapComUp[$login]['gapComUp'] = ($newAgentLogins[$pathArr[$login][0]]['RATIO']-$newAgentLogins[$login]['RATIO'])*$allTradeInfo[$login]['FIR_TRANSACTION2']/100;//返点给直接上级的金额
      $gapComUp[$login]['gapComUpLogin'] = $pathArr[$login][0];//接受返点的上级
    }
  }
  return array('dirCom'=>$dirCom, 'gapCom'=>$gapCom,'gapComUp'=>$gapComUp, 'reward'=>$reward,'rewardUp'=>$rewardUp, 'allCom'=>$allCom, 'monCom'=>$monCom,'curCom'=>$curCom);
}
/***************************************************************************************************
* 功能：根据邮箱账号，前往邮箱登录页面
* 参数: 邮箱账号
* 返回值：响应的邮箱登录页面
*/
function gotomail($mail)
{ 
  $t=explode('@',$mail); 
  $t=strtolower($t[1]); 
  if ($t=='163.com'){ 
    return 'mail.163.com'; 
  }else if ($t=='vip.163.com'){ 
    return 'vip.163.com'; 
  }else if ($t=='126.com'){ 
    return 'mail.126.com'; 
  }else if ($t=='qq.com'||$t=='vip.qq.com'||$t=='foxmail.com'){ 
    return 'mail.qq.com'; 
  }else if ($t=='gmail.com'){ 
    return 'mail.google.com'; 
  }else if ($t=='sohu.com'){ 
    return 'mail.sohu.com'; 
  }else if ($t=='tom.com'){ 
    return 'mail.tom.com'; 
  }else if ($t=='vip.sina.com'){ 
    return 'vip.sina.com'; 
  }else if ($t=='sina.com.cn'||$t=='sina.com'){ 
    return 'mail.sina.com.cn'; 
  }else if ($t=='tom.com'){ 
    return 'mail.tom.com'; 
  }else if ($t=='yahoo.com.cn'||$t=='yahoo.cn'){ 
    return 'mail.cn.yahoo.com'; 
  }else if ($t=='tom.com'){ 
    return 'mail.tom.com'; 
  }else if ($t=='yeah.net'){ 
    return 'www.yeah.net'; 
  }else if ($t=='21cn.com'){ 
    return 'mail.21cn.com'; 
  }else if ($t=='hotmail.com'){ 
    return 'www.hotmail.com'; 
  }else if ($t=='sogou.com'){ 
    return 'mail.sogou.com'; 
  }else if ($t=='188.com'){ 
    return 'www.188.com'; 
  }else if ($t=='139.com'){ 
    return 'mail.10086.cn'; 
  }else if ($t=='189.cn'){ 
    return 'webmail15.189.cn/webmail'; 
  }else if ($t=='wo.com.cn'){ 
    return 'mail.wo.com.cn/smsmail'; 
  }else if ($t=='139.com'){ 
    return 'mail.10086.cn'; 
  }else { 
    return ''; 
  } 
} 
/*********************************************************************************************************
* 功能：批量添加用户的分组信息
* 参数: 批量添加的用户的信息
* 返回值：true和false
*/
function batchAlterUsers($info)
{
  $insertValue = '';
  $loginValue = '';
  foreach ($info['login'] as $login) 
  {
    
    if ($info['GROUP_'.$login])
    { 

      foreach($info['GROUP_'.$login] as $k)
      {
        $insertValue .= '('.$login.','.$k.'),';
      }
    }
    $loginValue .= $login.',';
  }
  $insertValue = rtrim($insertValue,',');
  $loginValue = rtrim($loginValue,',');
  global $dbLocal;
  
  //删除本次添加的所有分组
  $delete = $dbLocal->query("DELETE FROM MT4_NEW_USERS WHERE LOGIN IN ({$loginValue})");
  $alterRst = $dbLocal->query("INSERT MT4_NEW_USERS(LOGIN,GROUP_ID) VALUES{$insertValue}");
  if ($alterRst)//批量修改成功
  {
    //删除重复信息
    $dbLocal->query("delete from MT4_NEW_USERS where ID in (select * from (select max(ID) from MT4_NEW_USERS group by LOGIN,GROUP_ID having count(LOGIN) > 1 and count(GROUP_ID)>1) as b)");
    return true;
  }else
  {
    return false;
  }
}
/*******************************************************************************************************
* 功能：显示数据，小数位数不限制
* 参数: 要处理的数据
* 返回值：处理后的数据
*/
function number_format_nodecimal($num)
{
  if (!is_numeric($num)) 
  {
    return false;
  }
  $num = explode('.', $num); // 把整数和小数分开
  $integer = $num[0]; //整数
  $decimal = $num[1]; //小数部分的值
  $integer = number_format($integer);//整数部分进行处理
  if (!empty($decimal)) 
  {
    $rvalue = $integer . '.' . $decimal; // 小数不为空，整数和小数合并
  } else 
  {
    $rvalue = $integer; // 小数为空，只有整数
  }
  return $rvalue;
}
/********************************************************************************************************
* 功能：根据登录的login,返回等级关系
* 参数: 所有要处理的login
* 返回值：处理后的数据
*/
function getAgentAccount($logins)
{
  global $dbRemote;
  $loginsValue = '';
  foreach ($logins as $key => $value) 
  {
    $loginsValue .= $value.',';
  }
  $loginsValue = rtrim($loginsValue,',');
  $usersInfo = $dbRemote->get_results("SELECT LOGIN,NAME,AGENT_ACCOUNT FROM MT4_USERS WHERE LOGIN IN($loginsValue) ORDER BY REGDATE ASC",ARRAY_A);
  $result = array();
  foreach ($usersInfo as $key => $value) 
  {
    $result[$value['AGENT_ACCOUNT']][] = $value;
  }
  return $result;
}

/*******************************************************************************************************
* 功能：根据登录账号查询交易情况
* 参数: 所有要查询的用户的账号组成的一维数组$logins,所查询的月份$month
* 返回值：处理后的数据
*/
function getEveryTradeInfo($logins,$month)
{
  global $dbRemote;
  $month = $month>9?$month:'0'.trim($month,'0');//月份，不足两位的补0
  $startDate = date('Y-'.$month.'-01');//查询月份的第一天
  $endDate = date('Y-m-d', strtotime("$startDate +1 month -1 day"));;//每个月最后一天
  $startTime = date('Y-m-d 00:00:00',strtotime($startDate));//这个月1号的0点
  $endTime = date('Y-m-d 23:59:59',strtotime($endDate));//截止今天的日期
  $loginStr = '';
  foreach ($logins as $key => $login) 
  {
    $loginStr .= $login.',';  
  }
  $loginStr = rtrim($loginStr,',');
  
  //去平前的交易情况
  $tradeInfo1 = $dbRemote->get_results("SELECT A.LOGIN AS LOGIN,A.NAME AS NAME,SUM(CASE WHEN B.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' THEN 1 ELSE 0 END) AS TRADE_QUAN,SUM(CASE WHEN B.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}'THEN B.VOLUME ELSE 0 END) AS TRANSACTION,SUM(CASE WHEN B.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' THEN B.PROFIT ELSE 0 END) AS PROFIT FROM MT4_USERS AS A LEFT JOIN MT4_OPTIONS AS B ON B.LOGIN=A.LOGIN WHERE A.LOGIN IN ($loginStr) GROUP BY A.LOGIN ASC",ARRAY_A);

  //去平后的交易情况
  $tradeInfo2 = $dbRemote->get_results("SELECT A.LOGIN AS LOGIN,A.NAME AS NAME,SUM(CASE WHEN B.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' AND B.PROFIT!=0 THEN 1 ELSE 0 END) AS TRADE_QUAN,SUM(CASE WHEN B.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' AND B.PROFIT!=0 THEN B.VOLUME ELSE 0 END) AS TRANSACTION,SUM(CASE WHEN B.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' AND B.PROFIT!=0 THEN B.PROFIT ELSE 0 END) AS PROFIT FROM MT4_USERS AS A LEFT JOIN MT4_OPTIONS AS B ON B.LOGIN=A.LOGIN WHERE A.LOGIN IN ($loginStr) GROUP BY A.LOGIN ASC",ARRAY_A);
  
  $result[0] = array();
  $result[1] = array();
  foreach ($tradeInfo1 as $key => $value)
  {
    $result[0][$value['LOGIN']] = $value;
    $result[1][$value['LOGIN']] = $tradeInfo2[$key];
  }
  return $result;
}

/*********************************************************************************************************
* 功能：生成不同的颜色（用在，根据不同的颜色生成不同的颜色）
* 参数: 顶级的账号login,路径函数$pathArr,$colorArr自定义的颜色
* 返回值：true和false
*/
function getColorByPath($login,$pathArr,$colorArr)
{
  foreach ($pathArr as $key => $value) 
  {
    $loginAndLevel[$key] = count($value);//数组的长度，代表着该用户的级别
  }
  $levelArr1 = array_unique($loginAndLevel);//或取一共存在的级别情况
  foreach ($levelArr1 as $key => $value) 
  {
    $levelArr2[] = $value;
  }
  //不同的级别对应不同的颜色
  foreach ($levelArr2 as $key => $value) 
  {
    if (isset($colorArr[$key]))
    {
      $bgColor[$value] = $colorArr[$key];
    }else
    {
      $bgColor[$value] = randrgb();
    }
  }
  
  foreach ($loginAndLevel as $key => $value) 
  {
    $result[$key] = $bgColor[$value];
  }
  return $result;
}

//随机生成颜色，当自定义的颜色不够时，随机生成颜色补充
function randrgb()  
{  
  $str='0123456789ABCDEF';  
    $estr='#';  
    $len=strlen($str);  
    for($i=1;$i<=6;$i++)  
    {  
        $num=rand(0,$len-1);    
        $estr=$estr.$str[$num];   
    }  
    return $estr;  
} 
/*********************************************************************************************************
* 功能：查询自己的姓名和父级的姓名
* 参数: 
* 返回值：true和false
*/
function getNamesAndParent($logins)
{
  $loginValue = '';
  foreach ($logins as $key => $value) {
    $loginValue .= ','.$value;
  }
  $loginValue = ltrim($loginValue, ',');//去除第一个逗号
  global $dbRemote;
  $loginNames = $dbRemote->get_results("SELECT A.LOGIN as LOGIN,A.NAME AS NAME,A.AGENT_ACCOUNT AS AGENT_ACCOUNT,B.NAME AS PARENT FROM MT4_USERS AS A LEFT JOIN MT4_USERS AS B ON A.AGENT_ACCOUNT = B.LOGIN WHERE A.LOGIN IN({$loginValue})",ARRAY_A);
  $SQL = "SELECT A.LOGIN as LOGIN,A.NAME AS NAME,B.NAME AS PARENT FROM MT4_USERS AS A LEFT JOIN MT4_USERS AS B ON A.AGENT_ACCOUNT = B.LOGIN WHERE LOGIN IN({$loginValue})";
  $names = array();
  foreach($loginNames as $value)
  {
    $names[$value['LOGIN']] = $value['NAME'];
    $parent[$value['LOGIN']] = $value['PARENT'];
    $agentAccount[$value['LOGIN']] = $value['AGENT_ACCOUNT'];
  }
  return array('NAME'=>$names,'PARENT'=>$parent,'AGENT_ACCOUNT'=>$agentAccount);
}
/*******************************************************************************************************************************
*功能：获取查询的账号的中的代理账号
*参数：$logins需要查询的账号
*返回值：返回所有的代理账号
*/
function getAgentLogins($logins)
{
  //一、提取出登录账号组成的字符串
  $loginValue = implode(',', $logins);
  $sql = "SELECT A.LOGIN FROM MT4_NEW_USERS AS A LEFT JOIN MT4_NEW_GROUP AS B ON A.GROUP_ID = B.GROUP_ID WHERE A.LOGIN IN ({$loginValue}) AND B.GENRE = 1 GROUP BY A.LOGIN"; 
  global $dbLocal;
  //二、删选出具有代理商身份的用户的登录账号，分组名称，返佣比率，额外奖励额度
  $logins = $dbLocal->get_col("$sql");
  //获取出代理商的信息
  return $logins;
}
/*******************************************************************************************************************************
*功能：获取所有相应的账号的返佣比率
*参数：$logins所有的需要查询的账号
*返回值：返回每个账号的返佣比率的一维数组
*/
function getAgentRatios($logins)
{
  //一、提取出登录账号组成的字符串
  global $dbLocal;
  $loginValue = '';
  foreach ($logins as $login) 
  {
    $loginValue .= ','.$login;
  }
  $loginValue = ltrim($loginValue, ',');//去除第一个逗号
  
  if (count($logins)<50)
  {
    $ratioInfo = $dbLocal->get_results("SELECT B.LOGIN,A.RATIO FROM MT4_NEW_GROUP AS A LEFT JOIN MT4_NEW_USERS AS B ON A.GROUP_ID = B.GROUP_ID WHERE B.LOGIN IN ($loginValue) AND A.GENRE = 1 GROUP BY B.LOGIN",ARRAY_A);

    foreach ($ratioInfo as $value) 
    {
      $result[$value['LOGIN']] = $value['RATIO'];  
    }
  }else
  {
    $allRatioInfo = $dbLocal->get_results("SELECT B.LOGIN,A.RATIO FROM MT4_NEW_GROUP AS A LEFT JOIN MT4_NEW_USERS AS B ON A.GROUP_ID = B.GROUP_ID WHERE A.GENRE = 1 GROUP BY B.LOGIN",ARRAY_A);
    $result = array();
  
    foreach ($allRatioInfo as $value) 
    {
      if (in_array($value['LOGIN'],$logins))
      {
        $result[$value['LOGIN']] = $value['RATIO'];
      }
    }
  }
  return $result;
}

/*******************************************************************************************************************************
*功能：获取返佣比率为零的账号
*参数：所有需要查询的账号
*返回值：所查询的账号中所有的代理返佣为0的账号
*/
function getZeroAgents($logins)
{
  //一、提取出登录账号组成的字符串
  $loginValue = '';
  foreach ($logins as $login) 
  {
    $loginValue .= ','.$login;
  }
  $loginValue = ltrim($loginValue, ',');//去除第一个逗号
  global $dbLocal;
  $sql = "SELECT B.LOGIN FROM MT4_NEW_GROUP AS A LEFT  JOIN MT4_NEW_USERS AS B ON A.GROUP_ID = B.GROUP_ID WHERE B.LOGIN IN ($loginValue) AND A.GENRE = 1 AND A.RATIO = 0 GROUP BY B.LOGIN";
  $logins = $dbLocal->get_col("$sql");

  return $logins;
}

/****************************************************************************************************************************
*功能：计算每个代理的团队交易量
*参数：$adminLogins权限内的所有的账号,$adminAgentLogins权限内的代理商信息,$month计算的月份,$adminUserInfo所有用户的详细信息
*返回值：返回每个代理的团队交易额和实时返佣金额
*/
function getEverTeamFirTransaction($adminLogins, $adminAgentLogins, $month, $adminUserInfo)
{
  global $validGroupStr;
  global $dbRemote;
  $month = $month>9?$month:'0'.trim($month,'0');//月份，不足两位的补0
  $startDate = date('Y-'.$month.'-01');//查询月份的第一天
  $endDate = date('Y-m-d', strtotime("$startDate +1 month -1 day"));//最后一天
  $startTime = date('Y-m-d 00:00:00',strtotime($startDate));//这个月1号的0点
  $endTime = date('Y-m-d 23:59:59',strtotime($endDate));//截止今天的日期
  //获取每个人的交易额
  if (count($adminLogins)<50)
  {
    $adminLoginsV = implode(',', $adminLogins);
    $everyTradeInfo = $dbRemote->get_results("SELECT LOGIN,COUNT(ID) AS TRADE_QUAN,SUM(CASE WHEN PROFIT<>0 THEN 1 ELSE 0 END) AS TRADE_QUAN2,SUM(VOLUME) AS TRANSACTION,SUM(CASE WHEN PROFIT<>0 THEN VOLUME ELSE 0 END) AS TRANSACTION2,SUM(PROFIT) AS PROFIT FROM MT4_OPTIONS WHERE LOGIN IN($adminLoginsV) AND CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' GROUP BY LOGIN",ARRAY_A);  
  }
  //人数多时，牺牲性能全部查询出来
  else
  {
    //获取每个人的交易数据
    $everyTradeInfo = $dbRemote->get_results("SELECT A.LOGIN,COUNT(A.ID) AS TRADE_QUAN,SUM(CASE WHEN A.PROFIT<>0 THEN 1 ELSE 0 END) AS TRADE_QUAN2,SUM(A.VOLUME) AS TRANSACTION,SUM(CASE WHEN A.PROFIT<>0 THEN VOLUME ELSE 0 END) AS TRANSACTION2,SUM(A.PROFIT) AS PROFIT FROM MT4_OPTIONS AS A LEFT JOIN MT4_USERS AS B ON A.LOGIN=B.LOGIN WHERE A.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' AND B.`GROUP` IN($validGroupStr) AND B.ENABLE=1 GROUP BY A.LOGIN",ARRAY_A);
  }

  $result = array();//每个代理直接下级的交易情况
  if ($everyTradeInfo)
  {
    //循环查询交易记录
    foreach ($everyTradeInfo as $key => $value) 
    {
      $newEveryTradeInfo[$value['LOGIN']] = $value;
      $upLogin = $adminUserInfo[$value['LOGIN']]['AGENT_ACCOUNT'];
      if ($upLogin)
      {
        $result[$upLogin]['FIR_TRADE_QUAN']+=$value['TRADE_QUAN'];
        $result[$upLogin]['FIR_TRADE_QUAN2']+=$value['TRADE_QUAN2'];
        $result[$upLogin]['FIR_TRANSACTION']+=$value['TRANSACTION'];
        $result[$upLogin]['FIR_TRANSACTION2']+=$value['TRANSACTION2'];
      }
    }
  }
  //计算每个人的团队
  foreach ($adminAgentLogins as $key => $value) 
  {
    global $resultlll;$resultlll = null;
    $teamLogins = queryLowerLogin($adminUserInfo,$value);
    if ($teamLogins)
    {
      foreach ($teamLogins as $k => $v) 
      {
        $result[$value]['TEAM_TRADE_QUAN']+=$newEveryTradeInfo[$v]['TRADE_QUAN'];
        $result[$value]['TEAM_TRADE_QUAN2']+=$newEveryTradeInfo[$v]['TRADE_QUAN2'];
        $result[$value]['TEAM_TRANSACTION']+=$newEveryTradeInfo[$v]['TRANSACTION'];
        $result[$value]['TEAM_TRANSACTION2']+=$newEveryTradeInfo[$v]['TRANSACTION2'];
      }
    }
    else
    {
      $result[$value]['TEAM_TRADE_QUAN']=0;
      $result[$value]['TEAM_TRADE_QUAN2']=0;
      $result[$value]['TEAM_TRANSACTION']=0;
      $result[$value]['TEAM_TRANSACTION2']=0;
    }
  }
  ksort($result);
  return $result;//每个代理直接下级的交易情况
}
/****************************************************************************************************************************
*功能：计算每个代理的团队交易量
*参数：$adminLogins权限内的所有的账号,$adminAgentLogins权限内的代理商信息,$month计算的月份,$adminUserInfo所有用户的详细信息
*返回值：返回每个代理的团队交易额和实时返佣金额
*/
function getEverTeamFirTransaction2($adminLogins, $month, $adminUserInfo)
{
  global $dbNRemote;//远程数据库连接类
  global $dbLocal;//本地数据库连接类
  $month = $month>9?$month:'0'.trim($month, '0');//月份，不足两位的补0
  $period = date('Y-'.$month);//年月例如：2016-02
  $startDate = date('Y-' . $month . '-01');//查询月份的第一天
  $endDate = date('Y-m-d', strtotime("$startDate +1 month -1 day"));//最后一天
  $startTime = date('Y-m-d 00:00:00', strtotime($startDate));//这个月1号的0点
  $endTime = date('Y-m-d 23:59:59', strtotime($endDate));//截止今天的日期

  //需要查询的信息中，已经存储的信息
  if (count($adminLogins) < 300) 
  {
    $adminLoginsStr = implode(',',$adminLogins);
    //查询本月中已经储存在在佣金报表中的交易信息
    $allHasStroreInfo = $dbLocal->get_results("SELECT LOGIN,TRADE_QUAN,FIR_TRADE_QUAN,
      TEAM_TRADE_QUAN,TRADE_QUAN2,FIR_TRADE_QUAN2,TEAM_TRADE_QUAN2,TRANSACTION,FIR_TRANSACTION,
      TEAM_TRANSACTION,TRANSACTION2,FIR_TRANSACTION2,TEAM_TRANSACTION2 FROM MT4_NEW_COM 
      WHERE PERIOD='{$period}' AND LOGIN IN($adminLoginsStr)", ARRAY_A);
  }
  else
  {
    $allHasStroreInfo = $dbLocal->get_results("SELECT LOGIN,TRADE_QUAN,FIR_TRADE_QUAN,
      TEAM_TRADE_QUAN,TRADE_QUAN2,FIR_TRADE_QUAN2,TEAM_TRADE_QUAN2,TRANSACTION,FIR_TRANSACTION,
      TEAM_TRANSACTION,TRANSACTION2,FIR_TRANSACTION2,TEAM_TRANSACTION2 FROM MT4_NEW_COM 
      WHERE PERIOD='{$period}'", ARRAY_A);
  }
  //所有已经存储的账号（不一定是本次计算所需要的账号）
  $allHasStroreLogins = array();
  if ($allHasStroreInfo)
  {
    $allHasStroreLogins = array_map('reset', $allHasStroreInfo);//返回已经储存的用户的账号
  }else
  {
    $allHasStroreLogins = array();
  }

  $hasNoStroreLogins = array_diff($adminLogins, $allHasStroreLogins);//查询出还没有进行存储的信息,array_diff找出差集
  $hasStoreLogins = array_intersect($adminLogins, $allHasStroreLogins);//已经进行储存过的账号(并且是此次查询所需要的账号)

  //有部分信息进行存储
  if ($hasStoreLogins)
  {
    foreach ($allHasStroreInfo as $key => $value) 
    {
      if (in_array($value['LOGIN'], $hasStoreLogins))
      {
        $newEveryTradeInfo[$value['LOGIN']] = $value;//存储，用于计算团队交易额
        $result[$value['LOGIN']]['FIR_TRADE_QUAN'] = $value['FIR_TRADE_QUAN'];
        $result[$value['LOGIN']]['TEAM_TRADE_QUAN'] = $value['TEAM_TRADE_QUAN'];
        $result[$value['LOGIN']]['FIR_TRADE_QUAN2'] = $value['FIR_TRADE_QUAN2'];
        $result[$value['LOGIN']]['TEAM_TRADE_QUAN2'] = $value['TEAM_TRADE_QUAN2'];
        $result[$value['LOGIN']]['FIR_TRANSACTION'] = $value['FIR_TRANSACTION'];
        $result[$value['LOGIN']]['TEAM_TRANSACTION'] = $value['TEAM_TRANSACTION'];
        $result[$value['LOGIN']]['FIR_TRANSACTION2'] = $value['FIR_TRANSACTION2'];
        $result[$value['LOGIN']]['TEAM_TRANSACTION2'] = $value['TEAM_TRANSACTION2'];
        $upLogin = $adminUserInfo[$value['LOGIN']]['AGENT_ACCOUNT'];
        //必须是没有存储的信息
        if (in_array($upLogin, $hasNoStroreLogins))
        {
          $result[$upLogin]['FIR_TRADE_QUAN'] += $value['TRADE_QUAN'];
          $result[$upLogin]['FIR_TRADE_QUAN2'] += $value['TRADE_QUAN2'];
          $result[$upLogin]['FIR_TRANSACTION'] += $value['TRANSACTION'];
          $result[$upLogin]['FIR_TRANSACTION2'] += $value['TRANSACTION2'];
        }
      } 
    }
  }
  //存在没有存储的信息
  if ($hasNoStroreLogins)
  {

    //查询出还没有进行交易的账户
    if (count($hasNoStroreLogins) < 300)
    {
      $hasNoLoginsValue = implode(',', $hasNoStroreLogins);
      $hasNostroeInfo = $dbNRemote->get_results("SELECT login AS LOGIN,
          COUNT(id) AS TRADE_QUAN,
          SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS TRADE_QUAN2,
          SUM(`money`) AS TRANSACTION,
          SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TRANSACTION2,
          SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS PROFIT FROM mt4_binary_option_history 
          WHERE login IN($hasNoLoginsValue) AND update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1 GROUP BY login", ARRAY_A);
    }
    //人数多时，牺牲性能全部查询出来
    else
    {
      //获取每个人的交易数据
      $hasNostroeInfo = $dbNRemote->get_results("SELECT login AS LOGIN,
          COUNT(id) AS TRADE_QUAN,
          SUM(CASE result WHEN 3 THEN 0 ELSE 1 END) AS TRADE_QUAN2,
          SUM(`money`) AS TRANSACTION,
          SUM(CASE result WHEN 3 THEN 0 ELSE `money` END) AS TRANSACTION2,
          SUM(CASE result WHEN 1 THEN -`money` WHEN 2 THEN `money`*commision_level/100 ELSE 0 END) AS PROFIT FROM mt4_binary_option_history WHERE update_time BETWEEN '{$startTime}' AND '{$endTime}' AND status=1 GROUP BY login", ARRAY_A);
    }

    if ($hasNostroeInfo)
    {
      //循环查询交易记录
      foreach ($hasNostroeInfo as $key => $value) 
      {
        if (in_array($value['LOGIN'], $hasNoStroreLogins))
        {
          $newEveryTradeInfo[$value['LOGIN']] = $value;                                                                                                    //此处理便于后续计算团队交易额
          $willStoreInfo[$value['LOGIN']]['TRADE_QUAN'] = $value['TRADE_QUAN'];
          $willStoreInfo[$value['LOGIN']]['TRADE_QUAN2'] = $value['TRADE_QUAN2'];
          $willStoreInfo[$value['LOGIN']]['TRANSACTION'] = $value['TRANSACTION'];
          $willStoreInfo[$value['LOGIN']]['TRANSACTION2'] = $value['TRANSACTION2'];
          $willStoreInfo[$value['LOGIN']]['PROFIT'] = $value['PROFIT'];
          $upLogin = $adminUserInfo[$value['LOGIN']]['agent_account'];
          //还没有存储的代理信息
          if (in_array($upLogin, $hasNoStroreLogins))
          {
            $willStoreInfo[$upLogin]['FIR_TRADE_QUAN'] += $value['TRADE_QUAN'];
            $result[$upLogin]['FIR_TRADE_QUAN'] += $value['TRADE_QUAN'];
            $willStoreInfo[$upLogin]['FIR_TRADE_QUAN2'] += $value['TRADE_QUAN2'];
            $result[$upLogin]['FIR_TRADE_QUAN2'] += $value['TRADE_QUAN2'];
            $willStoreInfo[$upLogin]['FIR_TRANSACTION'] += $value['TRANSACTION'];
            $result[$upLogin]['FIR_TRANSACTION'] += $value['TRANSACTION'];
            $willStoreInfo[$upLogin]['FIR_TRANSACTION2'] += $value['TRANSACTION2'];
            $result[$upLogin]['FIR_TRANSACTION2'] += $value['TRANSACTION2'];
          }
        }
      }
    }

    //计算每个人的团队
    foreach ($hasNoStroreLogins as $key => $value) 
    {
      global $resultlll;
      $resultlll = null;
      $teamLogins = queryLowerLogin($adminUserInfo,$value);
      if ($teamLogins)
      {
        foreach ($teamLogins as $k => $v) 
        {
          $willStoreInfo[$value]['TEAM_TRADE_QUAN'] += $newEveryTradeInfo[$v]['TRADE_QUAN'];
          $result[$value]['TEAM_TRADE_QUAN'] += $newEveryTradeInfo[$v]['TRADE_QUAN'];
          $willStoreInfo[$value]['TEAM_TRADE_QUAN2'] += $newEveryTradeInfo[$v]['TRADE_QUAN2'];
          $result[$value]['TEAM_TRADE_QUAN2'] += $newEveryTradeInfo[$v]['TRADE_QUAN2'];
          $willStoreInfo[$value]['TEAM_TRANSACTION'] += $newEveryTradeInfo[$v]['TRANSACTION'];
          $result[$value]['TEAM_TRANSACTION'] += $newEveryTradeInfo[$v]['TRANSACTION'];
          $willStoreInfo[$value]['TEAM_TRANSACTION2'] += $newEveryTradeInfo[$v]['TRANSACTION2'];
          $result[$value]['TEAM_TRANSACTION2'] += $newEveryTradeInfo[$v]['TRANSACTION2'];
        }
      }
      else
      {
        $willStoreInfo[$value]['TEAM_TRADE_QUAN'] = 0;
        $result[$value]['TEAM_TRADE_QUAN'] = 0;
        $willStoreInfo[$value]['TEAM_TRADE_QUAN2'] = 0;
        $result[$value]['TEAM_TRADE_QUAN2'] = 0;
        $willStoreInfo[$value]['TEAM_TRANSACTION'] = 0;
        $result[$value]['TEAM_TRANSACTION'] = 0;
        $willStoreInfo[$value]['TEAM_TRANSACTION2'] = 0;
        $result[$value]['TEAM_TRANSACTION2'] = 0;
      }
    }
    //把商未存进库的信息存库
    //需要存库的信息的数量
    $stroreQuan = count($hasNoStroreLogins);
    $hhhh = ceil($stroreQuan / 500);//每2000条信息存库一次,向上取整
    for ($i=1; $i <= $hhhh ; $i++) 
    { 
      if ($i!=$hhhh)
      {
        $insertInfo = array_slice($hasNoStroreLogins, ($i - 1) * 500, 500);//每次取500条数据
      }else
      {
        $insertInfo = array_slice($hasNoStroreLogins, ($i - 1) * 500);//一直取到最后
      }
      $insertValue = '';
      foreach ($insertInfo as $login) 
      {

        $insertValue .= '(' . $login . ',' . $adminUserInfo[$login]['agent_account'] . ",'" . $adminUserInfo[$login]['name'] . "'," 
          . ($willStoreInfo[$login]['TRADE_QUAN'] ? $willStoreInfo[$login]['TRADE_QUAN']:0) . ',' 
          . ($willStoreInfo[$login]['FIR_TRADE_QUAN'] ? $willStoreInfo[$login]['FIR_TRADE_QUAN']:0) . ',' 
          . ($willStoreInfo[$login]['TEAM_TRADE_QUAN'] ? $willStoreInfo[$login]['TEAM_TRADE_QUAN']:0) . ',' 
          . ($willStoreInfo[$login]['TRADE_QUAN2'] ? $willStoreInfo[$login]['TRADE_QUAN2']:0) . ',' 
          . ($willStoreInfo[$login]['FIR_TRADE_QUAN2'] ? $willStoreInfo[$login]['FIR_TRADE_QUAN2']:0) . ',' 
          . ($willStoreInfo[$login]['TEAM_TRADE_QUAN2'] ? $willStoreInfo[$login]['TEAM_TRADE_QUAN2']:0) . ',' 
          . ($willStoreInfo[$login]['TRANSACTION'] ? $willStoreInfo[$login]['TRANSACTION']:0) . ',' 
          . ($willStoreInfo[$login]['FIR_TRANSACTION'] ? $willStoreInfo[$login]['FIR_TRANSACTION']:0) . ',' 
          . ($willStoreInfo[$login]['TEAM_TRANSACTION'] ? $willStoreInfo[$login]['TEAM_TRANSACTION']:0) . ',' 
          . ($willStoreInfo[$login]['TRANSACTION2'] ? $willStoreInfo[$login]['TRANSACTION2']:0) . ',' 
          . ($willStoreInfo[$login]['FIR_TRANSACTION2'] ? $willStoreInfo[$login]['FIR_TRANSACTION2']:0) . ',' 
          . ($willStoreInfo[$login]['TEAM_TRANSACTION2'] ? $willStoreInfo[$login]['TEAM_TRANSACTION2']:0) . ',' 
          . ($willStoreInfo[$login]['PROFIT'] ? $willStoreInfo[$login]['PROFIT']:0) . ',\'' . $period . '\'),';
      }
      $insertValue = trim($insertValue,',');
      $insertRst = $dbLocal->query("INSERT MT4_NEW_COM(LOGIN,AGENT_ACCOUNT,NAME,TRADE_QUAN,FIR_TRADE_QUAN,TEAM_TRADE_QUAN,TRADE_QUAN2,FIR_TRADE_QUAN2,TEAM_TRADE_QUAN2,TRANSACTION,FIR_TRANSACTION,TEAM_TRANSACTION,TRANSACTION2,FIR_TRANSACTION2,TEAM_TRANSACTION2,PROFIT,PERIOD) VALUES{$insertValue}");
    }
  }
  ksort($result);//按键名进行排序
  return $result;//返回值
}
/****************************************************************************************************************************
*功能：根据只根据每个人的直接下级团队交易量计算返佣（可能是错的，先不使用）
*参数：$pathArr每个代理的路径信息,$everAgentsInfo代理商信息,$everAgentTeamTran每个人的团队交易额
*返回值：实时返佣
*/
function calculateCommsissionByFirTeam($pathArr, $everAgentsInfo, $everAgentTeamTran)
{
  global $currentRatio;//实时返佣的返点比率
  $dirCom = array();//直接返佣directCommission
  $gapCom = array();//下级点差返佣
  $gapComUp = array();//返点给上级
  $reward = array();//下级奖励返佣
  $rewardUp = array();//奖励上级
  $allCom = array();//返佣之和
  //将索引数组变成关联数组，方便操作
  foreach ($pathArr as $login => $path) 
  {
    //一、计算直接佣金和所有已得的佣金
    //KT = 等于0，说明是最底层的代理商（点差返佣和奖励肯定是0）
    if ($path['KT'] == 0)
    {
      $dirCom[$login]['dirCom'] = $everAgentTeamTran[$login]['FIR_TRANSACTION2']*$everAgentsInfo[$login]['RATIO'];//直接佣金 = 直属下级有效交易额*返点比率
      $gapCom[$login]['gapCom'] = 0;//点差佣金
      $reward[$login]['reward'] = 0;//奖励佣金
      $allCom[$login]['allCom'] = $dirCom[$login]['dirCom'];//佣金合计
    }
    //非底层的代理商的佣金计算（$path['KT']！= 0）
    else
    { 
      $dirCom[$login]['dirCom'] = $everAgentTeamTran[$login]['FIR_TRANSACTION2']*$everAgentsInfo[$login]['RATIO'];//直接佣金 = 直属下级有效交易额*返点比率
      $gapCom[$login]['gapCom'] = $gapCom[$login]['gapCom']?$gapCom[$login]['gapCom']:0;
      $reward[$login]['reward'] = $reward[$login]['reward']?$reward[$login]['reward']:0;
      $allCom[$login]['allCom'] = $dirCom[$login]['dirCom'] + $gapCom[$login]['gapCom'] + $reward[$login]['reward'];//佣金合计
    }
    //二、计算实时返佣
    //若返佣比率为0，则不存在实时返佣
    if ($everAgentsInfo[$login]['RATIO'] == 0)
    {
      $curCom[$login]['curCom'] = 0;//实时返佣金额为0
      $newPath = $path;
      unset($newPath['KT']);
      //往上找，找到一个返佣比率不等于零的代理，把实时返佣送给他
      foreach($newPath as $hlogin)  
      {
        if ($everAgentsInfo[$hlogin]['RATIO']>0)
        {
          $curCom[$hlogin]['curCom'] += $everAgentTeamTran[$login]['FIR_TRANSACTION2']*$currentRatio;
          break;
        }
      }
    }
    //临时返佣比率不等于零
    else
    {
      $curCom[$login]['curCom'] += $everAgentTeamTran[$login]['FIR_TRANSACTION2']*$currentRatio;
    }

    //三、判断是否奖励直接上级，奖励完之后进行返点给上级
    //该代理的的返佣比率和上级的代理相等或者还高于上级，则说明要进行奖励，$pathArr[$login][0]返回的是用户的直接上级
    if ($everAgentsInfo[$login]['RATIO'] >= $everAgentsInfo[$pathArr[$login][0]]['RATIO'] && $everAgentsInfo[$pathArr[$login][0]]['RATIO']>0)//$pathArr[$login][0]正好是是上级
    {
      //上下级比率相同时，上级奖励=下级纯利润*奖励比率
      $reward[$pathArr[$login][0]]['reward'] += $everAgentsInfo[$pathArr[$login][0]]['REWARD']*$allCom[$login]['allCom'];
      //奖励上级的金额 = 本身的返佣之和*返佣比率
      $rewardUp[$login]['rewardUp'] = $everAgentsInfo[$pathArr[$login][0]]['REWARD']*$allCom[$login]['allCom'];
      $rewardUp[$login]['rewardUpLogin'] = $pathArr[$login][0];
    //四、判断是否返点给上级
    //直接上级的返点比率高于当前用户时，则只进行点差返点
    }else if ($everAgentsInfo[$login]['RATIO'] < $everAgentsInfo[$pathArr[$login][0]]['RATIO'])
    {
      $rewardUp[$login]['rewardUp'] = 0;//不奖励上级
      $gapCom[$pathArr[$login][0]]['gapCom'] += ($everAgentsInfo[$pathArr[$login][0]]['RATIO']-$everAgentsInfo[$login]['RATIO'])*$everAgentTeamTran[$login]['TEAM_TRANSACTION2'];
      $gapComUp[$login]['gapComUp'] = ($everAgentsInfo[$pathArr[$login][0]]['RATIO']-$everAgentsInfo[$login]['RATIO'])*$everAgentTeamTran[$login]['TEAM_TRANSACTION2'];//返点给直接上级的金额
      $gapComUp[$login]['gapComUpLogin'] = $pathArr[$login][0];//接受返点的上级
    }

    $monCom[$login]['monCom'] = $allCom[$login]['allCom'] - $curCom[$login]['curCom'];//月结佣金
  }
  return array('dirCom'=>$dirCom, 'gapCom'=>$gapCom,'gapComUp'=>$gapComUp, 'reward'=>$reward,'rewardUp'=>$rewardUp, 'allCom'=>$allCom,'curCom'=>$curCom,'monCom'=>$monCom);
}

/****************************************************************************************************************************
*功能：根据每个人的团队交易量计算返佣（暂时不用，可能是错的）
*参数：$pathArr每个代理的路径信息,$everAgentsInfo代理商信息,$everAgentTeamTran每个人的团队交易额
*返回值：实时返佣
*/
function calculateCommsissionByTeam($pathArr, $everAgentsInfo, $everAgentTeamTran)
{
  global $currentRatio;
  $dirCom = array();//直接返佣directCommission
  $gapCom = array();//下级点差返佣
  $gapComUp = array();//返点给上级
  $reward = array();//下级奖励返佣
  $rewardUp = array();//奖励上级
  $allCom = array();//返佣之和
  $monCom = array();//月结佣金
  //将索引数组变成关联数组，方便操作
  foreach ($pathArr as $login => $path) 
  {
    //一、KT = 等于0，说明是最底层的代理商（点差返佣和奖励肯定是0）
    if ($path['KT'] == 0)
    {
      $dirCom[$login]['dirCom'] = $everAgentTeamTran[$login]['TEAM_TRANSACTION2']*$everAgentsInfo[$login]['RATIO'];//直接佣金 = 直属下级有效交易额*返点比率
      $gapCom[$login]['gapCom'] = 0;//点差佣金
      $reward[$login]['reward'] = 0;//奖励佣金
      $allCom[$login]['allCom'] = $dirCom[$login]['dirCom'];//佣金合计
    }
    //二、非底层的代理商的佣金计算（$path['KT']！= 0）
    else
    { 
      $dirCom[$login]['dirCom'] = $everAgentTeamTran[$login]['TEAM_TRANSACTION2']*$everAgentsInfo[$login]['RATIO']/100;//直接佣金 = 直属下级有效交易额*返点比率
      $gapCom[$login]['gapCom'] = $gapCom[$login]['gapCom']?$gapCom[$login]['gapCom']:0;
      $reward[$login]['reward'] = $reward[$login]['reward']?$reward[$login]['reward']:0;
      $allCom[$login]['allCom'] = $dirCom[$login]['dirCom'] + $gapCom[$login]['gapCom'] + $reward[$login]['reward'];//佣金合计
    }

    //若返佣比率为0，则不存在实时返佣
    if ($everAgentsInfo[$login]['RATIO'] == 0)
    {
      $curCom[$login]['curCom'] = 0;//实时返佣金额为0
      $newPath = $path;
      unset($newPath['KT']);
      //往上找，找到一个返佣比率不等于零的代理，把实时返佣送给他
      foreach($newPath as $hlogin)  
      {
        if ($everAgentsInfo[$hlogin]['RATIO']>0)
        {
          $curCom[$hlogin]['curCom'] += $everAgentTeamTran[$login]['FIR_TRANSACTION2']/100*$currentRatio;
          break;
        }
      }
    }
    //临时返佣比率不等于零
    else
    {
      $curCom[$login]['curCom'] += $everAgentTeamTran[$login]['FIR_TRANSACTION2']/100*$currentRatio;
    }

    $monCom[$login]['monCom'] = $allCom[$login]['allCom'] - $curCom[$login]['curCom'];//月结佣金
    //三、判断是否奖励上级，奖励完之后进行返点给上级
    //该交易者的返佣比率和上级的代理相等或者还高于上级，则说明要进行奖励，$pathArr[$login][0]返回的是用户的直接上级
    if ($everAgentsInfo[$login]['RATIO'] >= $everAgentsInfo[$pathArr[$login][0]]['RATIO'] && $everAgentsInfo[$pathArr[$login][0]]['RATIO']>0 )//$pathArr[$login][0]正好是是上级
    {
      //上下级比率相同时，上级奖励=下级纯利润*奖励比率
      $reward[$pathArr[$login][0]]['reward'] += $everAgentsInfo[$pathArr[$login][0]]['REWARD']*$allCom[$login]['allCom'];
      //奖励上级的金额 = 本身的返佣之和*返佣比率
      $rewardUp[$login]['rewardUp'] = $everAgentsInfo[$pathArr[$login][0]]['REWARD']*$allCom[$login]['allCom'];
      $rewardUp[$login]['rewardUpLogin'] = $pathArr[$login][0];
      //对该路径进行翻转，确保从底层向上
      $newPath = $path;
      unset($path['KT']);
      foreach ($newPath as $hlogin) 
      { 
        //如果能找到比率比他高的人，则返点给他，此处找到一次就必须停止，因为点差只补一次
        if (isset($everAgentsInfo[$hlogin])&&($everAgentsInfo[$hlogin]['RATIO']>$everAgentsInfo[$login]['RATIO']))
        {
          $gapCom[$hlogin]['gapCom'] += ($everAgentsInfo[$hlogin]['RATIO']-$everAgentsInfo[$login]['RATIO'])*$everAgentTeamTran[$login]['TEAM_TRANSACTION2']/100;
          $gapComUp[$login]['gapComUp'] = ($everAgentsInfo[$hlogin]['RATIO']-$everAgentsInfo[$login]['RATIO'])*$everAgentTeamTran[$login]['TEAM_TRANSACTION2']/100;
          $gapComUp[$login]['gapComUpLogin'] = $hlogin;
          break;
        }
      }
    //四、判断是否返点给上级
    //直接上级的返点比率高于当前用户时，则只进行点差返点
    }else if ($everAgentsInfo[$login]['RATIO'] < $everAgentsInfo[$pathArr[$login][0]]['RATIO'])
    {
      $rewardUp[$login]['rewardUp'] = 0;//不奖励上级
      $gapCom[$pathArr[$login][0]]['gapCom'] += ($everAgentsInfo[$pathArr[$login][0]]['RATIO']-$everAgentsInfo[$login]['RATIO'])*$everAgentTeamTran[$login]['TEAM_TRANSACTION2']/100;
      $gapComUp[$login]['gapComUp'] = ($everAgentsInfo[$pathArr[$login][0]]['RATIO']-$everAgentsInfo[$login]['RATIO'])*$everAgentTeamTran[$login]['TEAM_TRANSACTION2']/100;//返点给直接上级的金额
      $gapComUp[$login]['gapComUpLogin'] = $pathArr[$login][0];//接受返点的上级
    }
  }
  return array('dirCom'=>$dirCom, 'gapCom'=>$gapCom,'gapComUp'=>$gapComUp, 'reward'=>$reward,'rewardUp'=>$rewardUp, 'allCom'=>$allCom,'monCom'=>$monCom);
}
/****************************************************************************************************************************
*功能：计算每个人的团队交易量，直接下级交易量和本人交易量。在佣金详情页面使用
*参数：$adminLogins权限内的所有的账号,$adminAgentLogins权限内的代理商信息,$month计算的月份,$adminUserInfo所有用户的详细信息
*返回值：返回每个代理的团队交易额和实时返佣金额
*/
function getEverTeamFirSelTransaction($adminLogins,$adminAgentLogins, $month, $adminUserInfo)
{
  global $validGroupStr;
  global $dbRemote;
  $month = $month>9?$month:'0'.trim($month,'0');//月份，不足两位的补0
  $startDate = date('Y-'.$month.'-01');//查询月份的第一天
  $endDate = date('Y-m-d', strtotime("$startDate +1 month -1 day"));//最后一天
  $startTime = date('Y-m-d 00:00:00',strtotime($startDate));//这个月1号的0点
  $endTime = date('Y-m-d 23:59:59',strtotime($endDate));//截止今天的日期
  //获取每个人的交易额
  if (count($adminLogins)<50)
  {
    $adminLoginsV = implode(',', $adminLogins);
    $everyTradeInfo = $dbRemote->get_results("SELECT LOGIN,COUNT(ID) AS TRADE_QUAN,SUM(CASE WHEN PROFIT<>0 THEN 1 ELSE 0 END) AS TRADE_QUAN2,SUM(VOLUME) AS TRANSACTION,SUM(CASE WHEN PROFIT<>0 THEN VOLUME ELSE 0 END) AS TRANSACTION2,SUM(PROFIT) AS PROFIT FROM MT4_OPTIONS WHERE LOGIN IN($adminLoginsV) AND CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' GROUP BY LOGIN",ARRAY_A);  
  }

  //人数多时，牺牲性能全部查询出来
  else
  {
    //获取每个人的交易数据
    $everyTradeInfo = $dbRemote->get_results("SELECT A.LOGIN,COUNT(A.ID) AS TRADE_QUAN,SUM(CASE WHEN A.PROFIT<>0 THEN 1 ELSE 0 END) AS TRADE_QUAN2,SUM(A.VOLUME) AS TRANSACTION,SUM(CASE WHEN A.PROFIT<>0 THEN VOLUME ELSE 0 END) AS TRANSACTION2,SUM(A.PROFIT) AS PROFIT FROM MT4_OPTIONS AS A LEFT JOIN MT4_USERS AS B ON A.LOGIN=B.LOGIN WHERE A.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' AND B.`GROUP` IN($validGroupStr) AND B.ENABLE=1 GROUP BY A.LOGIN",ARRAY_A);
  }
  $result = array();//每个代理直接下级的交易情况
  if ($everyTradeInfo)
  {
    //循环查询交易记录
    foreach ($everyTradeInfo as $key => $value) 
    {
      $newEveryTradeInfo[$value['LOGIN']] = $value;
      $result[$value['LOGIN']]['TRADE_QUAN'] = $value['TRADE_QUAN'];
      $result[$value['LOGIN']]['TRADE_QUAN2'] = $value['TRADE_QUAN2'];
      $result[$value['LOGIN']]['TRANSACTION'] = $value['TRANSACTION'];
      $result[$value['LOGIN']]['TRANSACTION2'] = $value['TRANSACTION2'];
      $upLogin = $adminUserInfo[$value['LOGIN']]['AGENT_ACCOUNT'];
      if ($upLogin)
      {
        $result[$upLogin]['FIR_TRADE_QUAN'] += $value['TRADE_QUAN'];
        $result[$upLogin]['FIR_TRADE_QUAN2'] += $value['TRADE_QUAN2'];
        $result[$upLogin]['FIR_TRANSACTION'] += $value['TRANSACTION'];
        $result[$upLogin]['FIR_TRANSACTION2'] += $value['TRANSACTION2'];
      }
    }
  }
  //计算每个人的团队
  foreach ($adminAgentLogins as $key => $value) 
  {
    global $resultlll;$resultlll = null;
    $teamLogins = queryLowerLogin($adminUserInfo,$value);
    if ($teamLogins)
    {
      foreach ($teamLogins as $k => $v) 
      {
        $result[$value]['TEAM_TRADE_QUAN']+=$newEveryTradeInfo[$v]['TRADE_QUAN'];
        $result[$value]['TEAM_TRADE_QUAN2']+=$newEveryTradeInfo[$v]['TRADE_QUAN2'];
        $result[$value]['TEAM_TRANSACTION']+=$newEveryTradeInfo[$v]['TRANSACTION'];
        $result[$value]['TEAM_TRANSACTION2']+=$newEveryTradeInfo[$v]['TRANSACTION2'];
      }
    }
    else
    {
      $result[$value]['TEAM_TRADE_QUAN']=0;
      $result[$value]['TEAM_TRADE_QUAN2']=0;
      $result[$value]['TEAM_TRANSACTION']=0;
      $result[$value]['TEAM_TRANSACTION2']=0;
    }
  }
  krsort($result);
  return $result;//每个代理直接下级的交易情况
}
/****************************************************************************************************************************
*功能：计算每个人的团队交易量，直接下级交易量和本人交易量。在佣金详情页面使用
*参数：$adminLogins权限内的所有的账号,$adminAgentLogins权限内的代理商信息,$month计算的月份,$adminUserInfo所有用户的详细信息
*返回值：返回每个代理的团队交易额和实时返佣金额
*/
function getEverTeamFirSelTransaction2($adminLogins, $adminAgentLogins, $month, $adminUserInfo)
{
  global $validGroupStr;
  global $dbRemote;
  global $dbLocal;
  $month = $month>9?$month:'0'.trim($month, '0');//月份，不足两位的补0
  $period = date('Y-'.$month);//年月例如：2016-02
  $startDate = date('Y-'.$month.'-01');//查询月份的第一天
  $endDate = date('Y-m-d', strtotime("$startDate +1 month -1 day"));//最后一天
  $startTime = date('Y-m-d 00:00:00',strtotime($startDate));//这个月1号的0点
  $endTime = date('Y-m-d 23:59:59',strtotime($endDate));//截止今天的日期
  //需要查询的信息中，已经存储的信息
  if (count($adminLogins)<100) 
  {
    $adminLoginsStr = implode(',',$adminLogins);
    //查询本月中已经储存在在佣金报表中的交易信息
    $allHasStroreInfo = $dbLocal->get_results("SELECT LOGIN,TRADE_QUAN,FIR_TRADE_QUAN,TEAM_TRADE_QUAN,TRADE_QUAN2,FIR_TRADE_QUAN2,TEAM_TRADE_QUAN2,TRANSACTION,FIR_TRANSACTION,TEAM_TRANSACTION,TRANSACTION2,FIR_TRANSACTION2,TEAM_TRANSACTION2 FROM MT4_NEW_COM WHERE PERIOD='{$period}' AND LOGIN IN($adminLoginsStr)", ARRAY_A);
  }
  else
  {
    $allHasStroreInfo = $dbLocal->get_results("SELECT LOGIN,TRADE_QUAN,FIR_TRADE_QUAN,TEAM_TRADE_QUAN,TRADE_QUAN2,FIR_TRADE_QUAN2,TEAM_TRADE_QUAN2,TRANSACTION,FIR_TRANSACTION,TEAM_TRANSACTION,TRANSACTION2,FIR_TRANSACTION2,TEAM_TRANSACTION2 FROM MT4_NEW_COM WHERE PERIOD='{$period}'", ARRAY_A);
  }

  //所有已经存储的账号（不一定是本次计算所需要的账号）
  $allHasStroreLogins = array();
  if ($allHasStroreInfo)
  {
    $allHasStroreLogins = array_map('reset', $allHasStroreInfo);//返回已经储存的用户的账号
  }else
  {
    $allHasStroreLogins = array();
  }
  
  $hasNoStroreLogins = array_diff($adminLogins, $allHasStroreLogins);//查询出还没有进行存储的信息,array_diff找出差集
  $hasStoreLogins = array_intersect($adminLogins, $allHasStroreLogins);//已经进行储存过的账号(并且是此次查询所需要的账号)
  //有部分信息进行存储
  if ($hasStoreLogins)
  {
    foreach ($allHasStroreInfo as $key => $value) 
    {
      if (in_array($value['LOGIN'], $hasStoreLogins))
      {
        $newEveryTradeInfo[$value['LOGIN']] = $value;//存储，用于计算团队交易额
        $result[$value['LOGIN']]['TRADE_QUAN'] = $value['TRADE_QUAN'];
        $result[$value['LOGIN']]['FIR_TRADE_QUAN'] = $value['FIR_TRADE_QUAN'];
        $result[$value['LOGIN']]['TEAM_TRADE_QUAN'] = $value['TEAM_TRADE_QUAN'];
        $result[$value['LOGIN']]['TRADE_QUAN2'] = $value['TRADE_QUAN2'];
        $result[$value['LOGIN']]['FIR_TRADE_QUAN2'] = $value['FIR_TRADE_QUAN2'];
        $result[$value['LOGIN']]['TEAM_TRADE_QUAN2'] = $value['TEAM_TRADE_QUAN2'];
        $result[$value['LOGIN']]['TRANSACTION'] = $value['TRANSACTION'];
        $result[$value['LOGIN']]['FIR_TRANSACTION'] = $value['FIR_TRANSACTION'];
        $result[$value['LOGIN']]['TEAM_TRANSACTION'] = $value['TEAM_TRANSACTION'];
        $result[$value['LOGIN']]['TRANSACTION2'] = $value['TRANSACTION2'];
        $result[$value['LOGIN']]['FIR_TRANSACTION2'] = $value['FIR_TRANSACTION2'];
        $result[$value['LOGIN']]['TEAM_TRANSACTION2'] = $value['TEAM_TRANSACTION2'];
        $result[$value['LOGIN']]['PROFIT'] = $value['PROFIT'];
        $upLogin = $adminUserInfo[$value['LOGIN']]['AGENT_ACCOUNT'];
        if (in_array($upLogin, $adminAgentLogins) && !in_array($upLogin, $hasStoreLogins))
        {
          $result[$upLogin]['FIR_TRADE_QUAN'] += $value['TRADE_QUAN'];
          $result[$upLogin]['FIR_TRADE_QUAN2'] += $value['TRADE_QUAN2'];
          $result[$upLogin]['FIR_TRANSACTION'] += $value['TRANSACTION'];
          $result[$upLogin]['FIR_TRANSACTION2'] += $value['TRANSACTION2'];
        }
      }
    }
  }
  //存在没有存储的信息
  if ($hasNoStroreLogins)
  {
    //查询出还没有进行交易的账户
    if (count($hasNoStroreLogins)<100)
    {
      $hasNoLoginsValue = implode(',', $hasNoStroreLogins);
      $hasNostroeInfo = $dbRemote->get_results("SELECT LOGIN,COUNT(ID) AS TRADE_QUAN,SUM(CASE WHEN PROFIT=0 THEN 0 ELSE 1 END) AS TRADE_QUAN2,SUM(VOLUME) AS TRANSACTION,SUM(CASE WHEN PROFIT=0 THEN 0 ELSE VOLUME END) AS TRANSACTION2,SUM(PROFIT) AS PROFIT FROM MT4_OPTIONS WHERE LOGIN IN($hasNoLoginsValue) AND CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' GROUP BY LOGIN",ARRAY_A);
    }
    //人数多时，牺牲性能全部查询出来
    else
    {
      //获取每个人的交易数据
      $hasNostroeInfo = $dbRemote->get_results("SELECT A.LOGIN,COUNT(A.ID) AS TRADE_QUAN,SUM(CASE WHEN A.PROFIT=0 THEN 0 ELSE 1 END) AS TRADE_QUAN2,SUM(A.VOLUME) AS TRANSACTION,SUM(CASE WHEN A.PROFIT=0 THEN 0 ELSE VOLUME END) AS TRANSACTION2,SUM(A.PROFIT) AS PROFIT FROM MT4_OPTIONS AS A LEFT JOIN MT4_USERS AS B ON A.LOGIN=B.LOGIN WHERE B.`GROUP` IN($validGroupStr) AND  A.CLOSE_TIME BETWEEN '{$startTime}' AND '{$endTime}' AND B.ENABLE=1 GROUP BY A.LOGIN",ARRAY_A);
    }

    if ($hasNostroeInfo)
    {
      //循环查询交易记录
      foreach ($hasNostroeInfo as $key => $value) 
      {
        if (in_array($value['LOGIN'], $hasNoStroreLogins))
        {
          $newEveryTradeInfo[$value['LOGIN']] = $value;//此处理便于后续计算团队交易额
          $willStoreInfo[$value['LOGIN']]['TRADE_QUAN'] = $value['TRADE_QUAN'];
          $willStoreInfo[$value['LOGIN']]['TRADE_QUAN2'] = $value['TRADE_QUAN2'];
          $willStoreInfo[$value['LOGIN']]['TRANSACTION'] = $value['TRANSACTION'];
          $willStoreInfo[$value['LOGIN']]['TRANSACTION2'] = $value['TRANSACTION2'];
          $willStoreInfo[$value['LOGIN']]['PROFIT'] = $value['PROFIT'];
          $upLogin = $adminUserInfo[$value['LOGIN']]['AGENT_ACCOUNT'];
          if (in_array($upLogin, $adminAgentLogins) && in_array($upLogin, $hasNoStroreLogins))
          {
            $willStoreInfo[$upLogin]['FIR_TRADE_QUAN'] += $value['TRADE_QUAN'];
            $willStoreInfo[$upLogin]['FIR_TRADE_QUAN2'] += $value['TRADE_QUAN2'];
            $willStoreInfo[$upLogin]['FIR_TRANSACTION'] += $value['TRANSACTION'];
            $willStoreInfo[$upLogin]['FIR_TRANSACTION2'] += $value['TRANSACTION2'];
          }
        }
      }
    }
    //没有存储信息的的代理
    $hasNoStoreAgentLogins = array_intersect($adminAgentLogins,$hasNoStroreLogins);
    //计算每个人的团队
    foreach ($hasNoStoreAgentLogins as $key => $value) 
    {
      global $resultlll;$resultlll = null;
      $teamLogins = queryLowerLogin($adminUserInfo,$value);
      if ($teamLogins)
      {
        foreach ($teamLogins as $k => $v) 
        {
          $willStoreInfo[$value]['TEAM_TRADE_QUAN']+=$newEveryTradeInfo[$v]['TRADE_QUAN'];
          $willStoreInfo[$value]['TEAM_TRADE_QUAN2']+=$newEveryTradeInfo[$v]['TRADE_QUAN2'];
          $willStoreInfo[$value]['TEAM_TRANSACTION']+=$newEveryTradeInfo[$v]['TRANSACTION'];
          $willStoreInfo[$value]['TEAM_TRANSACTION2']+=$newEveryTradeInfo[$v]['TRANSACTION2'];
        }
      }
      else
      {
        $willStoreInfo[$value]['TEAM_TRADE_QUAN']=0;
        $willStoreInfo[$value]['TEAM_TRADE_QUAN2']=0;
        $willStoreInfo[$value]['TEAM_TRANSACTION']=0;
        $willStoreInfo[$value]['TEAM_TRANSACTION2']=0;
      }
    }
    //把商未存进库的信息存库
    //需要存库的信息的数量
    $stroreQuan = count($hasNoStroreLogins);
    $hhhh = ceil($stroreQuan/2000);//每2000条信息存库一次,向上取整
    for ($i=1; $i <= $hhhh ; $i++) 
    { 
      if ($i!=$hhhh)
      {
        $insertInfo = array_slice($hasStoreLogins, ($i-1)*2000,2000);//每次取2000条数据
      }else
      {
        $insertInfo = array_slice($hasStoreLogins, ($i-1)*2000);//一直取到最后
      }
      $insertValue = '';
      foreach ($hasNoStroreLogins as $login) 
      {

        $insertValue .= '('.$login.",'".$adminUserInfo[$login]['NAME']."',"
        .($willStoreInfo[$login]['TRADE_QUAN']?$willStoreInfo[$login]['TRADE_QUAN']:0).','
        .($willStoreInfo[$login]['FIR_TRADE_QUAN']?$willStoreInfo[$login]['FIR_TRADE_QUAN']:0).','
        .($willStoreInfo[$login]['TEAM_TRADE_QUAN']?$willStoreInfo[$login]['TEAM_TRADE_QUAN']:0).','
        .($willStoreInfo[$login]['TRADE_QUAN2']?$willStoreInfo[$login]['TRADE_QUAN2']:0).','
        .($willStoreInfo[$login]['FIR_TRADE_QUAN2']?$willStoreInfo[$login]['FIR_TRADE_QUAN2']:0).','
        .($willStoreInfo[$login]['TEAM_TRADE_QUAN2']?$willStoreInfo[$login]['TEAM_TRADE_QUAN2']:0).','
        .($willStoreInfo[$login]['TRANSACTION']?$willStoreInfo[$login]['TRANSACTION']:0).','
        .($willStoreInfo[$login]['FIR_TRANSACTION']?$willStoreInfo[$login]['FIR_TRANSACTION']:0).','
        .($willStoreInfo[$login]['TEAM_TRANSACTION']?$willStoreInfo[$login]['TEAM_TRANSACTION']:0).','
        .($willStoreInfo[$login]['TRANSACTION2']?$willStoreInfo[$login]['TRANSACTION2']:0).','
        .($willStoreInfo[$login]['FIR_TRANSACTION2']?$willStoreInfo[$login]['FIR_TRANSACTION2']:0).','
        .($willStoreInfo[$login]['TEAM_TRANSACTION2']?$willStoreInfo[$login]['TEAM_TRANSACTION2']:0).','
        .($willStoreInfo[$login]['PROFIT']?$willStoreInfo[$login]['PROFIT']:0).',\''.$period.'\'),';
      }
      $insertValue = trim($insertValue,',');
      $insertRst = $dbLocal->query("INSERT MT4_NEW_COM(LOGIN,NAME,TRADE_QUAN,FIR_TRADE_QUAN,TEAM_TRADE_QUAN,TRADE_QUAN2,FIR_TRADE_QUAN2,TEAM_TRADE_QUAN2,TRANSACTION,FIR_TRANSACTION,TEAM_TRANSACTION,TRANSACTION2,FIR_TRANSACTION2,TEAM_TRANSACTION2,PROFIT,PERIOD) VALUES{$insertValue}");
    }
  }
  $res = array_merge_recursive($result, $willStoreInfo);
  if (empty($result))
  {
    $res = $willStoreInfo;
  }else if (empty($willStoreInfo))
  {
    $res = $result;
  }else
  {
    $res = array_merge_recursive($result, $willStoreInfo);
  }
  ksort($res);//按键名进行排序
  return $res;//返回值
}
/****************************************************************************************************************************
*功能：获取代理的信息
*参数：$logins需要获取的账号,$month需要获取的月份
*返回值：返回每个代理的团队交易额和实时返佣金额
*/
function getAndStoreAgentLoginsInfo($logins, $month)
{
  $month = $month>=10?$month:'0'.trim($month, '0');
  $period = date('Y-'.$month);
  global $dbLocal; 
  //获取已经存储的代理商信息
  if (count($logins)<100)
  {
    $loginValue = implode(',', $logins);
    $hasStroeAgentsInfo = $dbLocal->get_results("SELECT LOGIN,GROUP_ID,`GROUP`,RATIO,REWARD FROM MT4_NEW_COM WHERE PERIOD='{$period}' AND LOGIN IN($loginValue) AND GENRE=1",ARRAY_A);
    if ($hasStroeAgentsInfo)
    {
      foreach ($hasStroeAgentsInfo as $key => $value) 
      {
        $result[$value['LOGIN']] = $value;
      }
    }
  }
  else
  {
    $allHasStroreAgentsInfo = $dbLocal->get_results("SELECT LOGIN,GROUP_ID,`GROUP`,RATIO,REWARD FROM MT4_NEW_COM WHERE PERIOD='{$period}' AND GENRE=1",ARRAY_A);
    foreach ($allHasStroreAgentsInfo as $key => $value) 
    {
      if (in_array($value['LOGIN'], $logins))
      {
        $result[$value['LOGIN']] = $value;
      }
    }
  }
  //获取已经插入的代理商信息
  if ($result)
  {
    $hasStoreAgentLogins = array_map('reset', $result);
  }else
  {
    $hasStoreAgentLogins = array();
  }

  //找出还没有进行存储的账号(也许是代理商，也许是交易者)
  $hasNoStoreAgentLogins = array_diff($logins, $hasStoreAgentLogins);

  if (count($hasNoStoreAgentLogins)<100)
  {
    $hsvstr = implode(',', $hasNoStoreAgentLogins);
    $hasNostroeAgentInfo = $dbLocal->get_results("SELECT a.LOGIN,b.`GROUP`,b.GROUP_ID,b.RATIO,b.REWARD FROM MT4_NEW_USERS AS a LEFT JOIN MT4_NEW_GROUP AS b ON a.GROUP_ID=b.GROUP_ID WHERE a.LOGIN IN ($hsvstr) AND b.GENRE =1 GROUP BY a.LOGIN", ARRAY_A);
    //获取出代理商的信息
    foreach ($hasNostroeAgentInfo as $value) 
    {
      $result[$value['LOGIN']] = $value;
    }
  }else
  {
    $allAgentLoginsInfo = $dbLocal->get_results("SELECT a.LOGIN,b.`GROUP`,b.GROUP_ID,b.RATIO,b.REWARD FROM MT4_NEW_USERS AS a LEFT JOIN MT4_NEW_GROUP AS b ON a.GROUP_ID=b.GROUP_ID WHERE b.GENRE =1 GROUP BY a.LOGIN", ARRAY_A);
    foreach ($allAgentLoginsInfo as $key => $value) 
    {
      if (in_array($value['LOGIN'], $hasNoStoreAgentLogins))
      {
        $hasNostroeAgentInfo[] = $value;
        $result[$value['LOGIN']] = $value;
      }
    }
  }

  if ($hasNostroeAgentInfo)
  {
    //未进行存储的代理信息的数量
    $noStoQuan = count($hasNostroeAgentInfo);
    $ii = ceil($noStoQuan/500);
    for ($i=1; $i <=$ii ; $i++) 
    { 
      if ($i!=$ii)
      {
        $updateInfo = array_slice($hasNostroeAgentInfo, ($i-1)*500, 500);
      }else
      {
        $updateInfo = array_slice($hasNostroeAgentInfo, ($i-1)*500);//一直取到最后
      }

      $updateLogins = array_map('reset', $updateInfo);
      $updateLoginStr = implode(',', $updateLogins);

      $sql1 = "UPDATE MT4_NEW_COM "; 
      $sql_group_id = "SET GROUP_ID = CASE LOGIN ";
      $sql_group = ",`GROUP` = CASE LOGIN ";//分组名称
      $sql_ratio = ",RATIO=CASE LOGIN ";//分组比率
      $sql_reward = ",REWARD=CASE LOGIN ";//奖励比率
      foreach ($updateInfo as $value) 
      { 
        $sql_group_id .= sprintf("WHEN %u THEN %u ", $value['LOGIN'], $value['GROUP_ID']);
        $sql_group .= sprintf("WHEN %u THEN '%s' ", $value['LOGIN'], $value['GROUP']);
        $sql_ratio .= sprintf("WHEN %u THEN %F ", $value['LOGIN'], $value['RATIO']);
        $sql_reward .= sprintf("WHEN %u THEN %F ", $value['LOGIN'], $value['REWARD']);
      } 
      $sql = $sql1.$sql_group_id.'END'.$sql_group.'END'.$sql_ratio.'END'.$sql_reward."END,GENRE =1 WHERE PERIOD='{$period}' AND LOGIN IN($updateLoginStr)";
      $dbLocal->query($sql);
    }
  }
  krsort($result);
  return $result;
}