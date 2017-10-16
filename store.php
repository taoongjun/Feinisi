5551,55511,555111,5551111,55511111,555111111,5551112,555112,55512,55513,5552,55521,55522,55523,5554,55541,5553
55511,555111,5551111,55511111,555111111,5551112,555112,55512,55513
INSERT MT4_USERS LOGIN,NAME,GROUP,AGENT_ACCOUNT,REGDATE,EMAIL VALUES(5552,5552,FEINISI1,555,'2016-07-31 23:59:59',),(5553),(5554)
http://stackoverflow.com/questions/22453574/phpmyadmin-no-login-form-cookies-must-be-enabled-past-this-point     phpmyadmin打不开的原因
网页色彩搭配http://www.360doc.com/content/13/0114/04/11472472_260028286.shtml
  setcookie( 'login',  '80097702', time()+3600*24 );//将机构代码存入cookie中（登录凭证1）80097702，cookeie无法识别0
  setcookie( 'password',  'sss', time()+3600*24 );//将密码存入cookie中
新服务器
http://dev.bosince.com/TYJ
ftp & mysql
user: dev_tyj
ps: kRGQGNS5Dsz5
phpmyadmin:
http://49.213.13.141/phpmyadmin/

标签: 
  $smtpserver = "smtp.163.com";
  $smtpserverport =25;
  $smtpusermail = "yongjuntao@163.com";
  $smtpemailto = 'yongjuntao@163.com';
  $smtpuser = "yongjuntao@163.com";
  $smtppass = "1234560tao135790";
  $mailtitle = 'asdfasdfasdfasdfasdfadsfsad';
  $mailcontent = "<h1>".'asdfadfadfasdfasdfasdf'."</h1>";
  $mailtype = "HTML";
  $smtp = new smtp($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);
  $smtp->debug = false;
  $state = $smtp->sendmail($smtpemailto, $smtpusermail, $mailtitle, $mailcontent, $mailtype);
8382222333 
e44d1fc68941db93e1c6b4231e2ca896  
ftp://dev_tyj:zFa8UC9@218.244.146.220
http://218.244.146.220/phpmyadmin/
http://dev.jdwjy.com/TYJ/
mysqlÍ¬FTPÕË»m

新服务器
http://dev.bosince.com/TYJ
ftp & mysql
user: dev_tyj
ps: kRGQGNS5Dsz5
phpmyadmin:
http://49.213.13.141/phpmyadmin/

MySql
db:com_bosince_crm
ur:crm_bosince
pw:PTSSahCAczcYsNhe

ftp
user:crm
pw:vuuFx9quN5edENnFt35W5pDG
//黄宇非的博客网站
webjlw.com
  路由器：192.168.0.1
  临时用户名和密码：guest guest
  主DNS 202.96.128.166 /从DNS 202.96.134.133
ftp
user:crm
pw:vuuFx9quN5edENnFt35W5pDG

摩凝(M.Chan) 2016/5/4 13:18:46
crm.bosince.com
13:23:47
摩凝(M.Chan) 2016/5/4 13:23:47
MySql
db:com_bosince_crm
ur:crm_bosince
pw:PTSSahCAczcYsNhe
摩凝(M.Chan) 2016/5/5 15:23:16
http://office.fe09.com/Manage/
mysql那些招：执行大批量删除、查询和索引等操作
http://www.cr173.com/html/18258_1.html
DELETE P1 FROM TABLE MT4_NEW_USERS AS P1, TABLE MT4_NEW_USERS AS P2 WHERE P1.LOGIN = P2.LOGIN AND P1.GROUP_ID = P2.GROUP_ID AND P1.ID < P2.ID;
DELETE P1 FROM TABLE MT4_NEW_USERS AS P1, TABLE MT4_NEW_USERS AS P2 WHERE P1.LOGIN = P2.LOGIN AND P1.GROUP_ID = P2.GROUP_ID AND P1.ID < P2.ID;
delete from MT where id not in(select name,email,max(id) from test group by name,email having id is not null) 
delete from MT4_NEW_USERS where ID not in(select LOGIN,GROUP_ID,max(ID) from MT4_NEW_USERS group by LOGIN,GROUP_ID having ID is not null) 
计算数据
交易记录：http://dev.bosince.com/mt4.php?select=options
用户列表：http://dev.bosince.com/mt4.php

SELECT CASE 1 WHEN 1 THEN 'one'
  WHEN 2 THEN 'two' 
   ELSE 'more' END
as testCol

select name,sum(money*IF(stype=4,-1,1)) as M 
from table 
group by name 

$.ajax({
      alert(id);
                url: 'aaa.php?act=updact',
        type: 'POST',
        data: "id=" + id + "&name="+$("#name").val(),
                success: function(msg) {
          alert(msg);
                  window.location.href='caixi.php?act=upd&id='+obj;
                }
            });
jquery

            :even 选择器选取每个带有偶数 index 值的元素（比如 2、4、6）

            &emsp;制表符
 //mysql一句话插叙递归关系
SELECT LOGIN AS LOGIN,AGENT_ACCOUNT AS 父ID ,levels AS 父到子之间级数, paths AS 父到子路径 FROM (
   SELECT LOGIN,AGENT_ACCOUNT,
   @le:= IF (AGENT_ACCOUNT = 0 ,0, 
     IF( LOCATE( CONCAT('|',AGENT_ACCOUNT,':'),@pathlevel)  > 0 ,   
         SUBSTRING_INDEX( SUBSTRING_INDEX(@pathlevel,CONCAT('|',AGENT_ACCOUNT,':'),-1),'|',1) +1
    ,@le+1) ) levels
   , @pathlevel:= CONCAT(@pathlevel,'|',LOGIN,':', @le ,'|') pathlevel
   , @pathnodes:= IF( AGENT_ACCOUNT =0,',0', 
      CONCAT_WS(',',
      IF( LOCATE( CONCAT('|',AGENT_ACCOUNT,':'),@pathall) > 0 , 
        SUBSTRING_INDEX( SUBSTRING_INDEX(@pathall,CONCAT('|',AGENT_ACCOUNT,':'),-1),'|',1)
       ,@pathnodes ) ,AGENT_ACCOUNT ) )paths
  ,@pathall:=CONCAT(@pathall,'|',LOGIN,':', @pathnodes ,'|') pathall 
    FROM MT4_USERS, 
  (SELECT @le:=0,@pathlevel:='', @pathall:='',@pathnodes:='') vv
  ORDER BY AGENT_ACCOUNT,LOGIN
  ) src
ORDER BY LOGIN

//解释
@是用户变量，@@是系统变量。
:= 赋值运算符
if(条件,满足条件,不满足条件)
LOCATE(substr,str,pos) 返回子串 substr 在字符串 str 中的第 pos 位置后第一次出现的位置，pos可省略
CONCAT(str1,str2,…) 
SUBSTRING_INDEX(str,delim,count) 返回字符串 str 中在第 count 个出现的分隔符 delim 之前的子串
CONCAT_WS(",","First name","Second name","Last Name");拼接字符串

WITH OrgPath(LOGIN,AGENT_ACCOUNT)  
AS  
(  
 SELECT LOGIN,AGENT_ACCOUNT FROM MT4_USERS WHERE ename='JONES'  
   
 UNION ALL  
   
 SELECT MT4_USERS.LOGIN,MT4_USERS.AGENT_ACCOUNT  
 FROM MT4_USERS   
 INNER JOIN OrgPath on MT4_USERS.LOGIN=OrgPath.AGENT_ACCOUNT  
)  
SELECT  MT4_USERS.*  
FROM MT4_USERS  
INNER JOIN OrgPath ON OrgPath.LOGIN=MT4_USERS.LOGIN;  

WITH MT4_USERS(LOGIN,AGENT_ACCOUNT)  
AS  
(  
 SELECT LOGIN,AGENT_ACCOUNT FROM MT4_USERS WHERE LOGIN = 80097702  
   
 UNION ALL  
   
 SELECT MT4_USERS.LOGIN,MT4_USERS.AGENT_ACCOUNT  
 FROM MT4_USERS   
 INNER JOIN MT4_USERS on MT4_USERS.AGENT_ACCOUNT=MT4_USERS.LOGIN  
)  
SELECT  MT4_USERS.*  
FROM MT4_USERS  
INNER JOIN MT4_USERS ON MT4_USERS.LOGIN=MT4_USERS.LOGIN;  

SELECT T2.LOGIN, T2.NAME 
FROM ( 
    SELECT 
        @r AS _id, 
        (SELECT @r := AGENT_ACCOUNT FROM MT4_USERS WHERE LOGIN = _id) AS parent_id, 
        @l := @l + 1 AS lvl 
    FROM 
        (SELECT @r := 5, @l := 0) vars, 
        MT4_USERS h 
    WHERE @r <> 0) T1 
JOIN MT4_USERS T2 
ON T1._id = T2.LOGIN 
ORDER BY T1.lvl DESC 
//PHPMYADMIN中出现无法增删改查的问题
Resolution: This table does not contain a unique column. Grid edit, checkbox, Edit, Copy and Delete features are not available
解决方案：http://stackoverflow.com/questions/18922503/resolution-this-table-does-not-contain-a-unique-column-grid-edit-checkbox-ed
增加一列主键或者unique键

SELECT LOGIN,AGENT_ACCOUNT,LEVELS, `PATH`FROM ( SELECT LOGIN,AGENT_ACCOUNT, @le:= IF (AGENT_ACCOUNT = 0 ,0, IF( LOCATE( CONCAT('|',AGENT_ACCOUNT,':'),@pathlevel) > 0 , SUBSTRING_INDEX( SUBSTRING_INDEX(@pathlevel,CONCAT('|',AGENT_ACCOUNT,':'),-1),'|',1) +1 ,@le+1) ) LEVELS , @pathlevel:= CONCAT(@pathlevel,'|',LOGIN,':', @le ,'|') pathlevel , @pathnodes:= IF( AGENT_ACCOUNT =0,',0', CONCAT_WS(',', IF( LOCATE( CONCAT('|',AGENT_ACCOUNT,':'),@pathall) > 0 , SUBSTRING_INDEX( SUBSTRING_INDEX(@pathall,CONCAT('|',AGENT_ACCOUNT,':'),-1),'|',1) ,@pathnodes ) ,AGENT_ACCOUNT ) )`PATH` ,@pathall:=CONCAT(@pathall,'|',LOGIN,':', @pathnodes ,'|') pathall FROM MT4_USERS, (SELECT @le:=0,@pathlevel:='', @pathall:='',@pathnodes:='') vv ORDER BY AGENT_ACCOUNT,LOGIN ) src ORDER BY LEVELS ASC

<!--         <tr>
  <td><?=$_COOKIE['login']?></td>
  <td></td>管理员登录怎么处理？？？？？？？？？？？？？？？？？
  <td>
    <span class="label label-primary"><?=$allTradeInfo[$_COOKIE['login']]['LOWER_TRADE_QUAN1']?></span>登录用户的直属下级交易量（去平前）
    <span class="label label-success"><?=$allTradeInfo[$_COOKIE['login']]['LOWER_TRADE_QUAN2']?></span>登录用户的直属下级交易量（去平后）
  </td>
  <td>
    <span class="label label-primary"><?=$allTradeInfo[$_COOKIE['login']]['LOWER_TRANSACTION1']?></span>登录用户的直属下级交易金额（去平前）
    <span class="label label-success"><?=$allTradeInfo[$_COOKIE['login']]['LOWER_TRANSACTION2']?></span>登录用户的直属下级交易金额（去平后） 
  </td>
  <td></td>
  <td></td>
  <td></td>
  <td></td>
</tr>  -->

//查询分组信息
function mergeGroup( $userInfo )
{ 
  $LOGINArr = array();
  foreach ($userInfo as $key => $value) 
  {
    $LOGIN_Arr[] = $value['LOGIN'];
  }
  $LOGIN_Arr = array_unique($LOGIN_Arr);//不重复的账号
  
  $newArr = array();
  foreach ( $LOGIN_Arr as $key => $value ) 
  {
     $newArr[$value]['GROUP'] = array();
/*     $newArr[$value]['GROUP_ID'] = array();*/
    foreach ( $userInfo as $k => $v ) 
    {
      if( $v['LOGIN'] == $value )
      {
        if( $v['GROUP'] )
        {
          $newArr[$value]['GROUP'][$v['GROUP_ID']] = $v['GROUP']; 
        }
         
/*        if( $v['GROUP_ID'] )
        {
          $newArr[$value]['GROUP_ID'][] = $v['GROUP_ID'];
        }*/
          
      }
    }
  }
  return $newArr;
}
<?=!$subGroups[$value]['GROUP']?'<span style="color:red;font-style:italic;">暂无分组</span>':implode('/', $subGroups[$value]['GROUP'])?>

/*echo <<< EOF
<script type="text/javascript">
window.onload = refresh();
//刷新的公式
function refresh()
{
  window.location.reload();
}
</script>
EOF;*/

<?php
//安全性问题（温馨提示每当您更换电脑时，请重新获取密码）

//防止sql非法注入安全公式
function post_check($post) 
{ 
  if(!get_magic_quotes_gpc()) {//php默认自动进行转义，如果默认失败，则主动进行转义 
    $post = addslashes($post);
  } 
  $post = str_replace("_", "\_", $post); 
  $post = str_replace("%", "\%", $post); 
  $post = nl2br($post); 
  $post = htmlspecialchars($post); 
  return $post; 
}

//登陆
function login( $login,$password )
{
  global $dbLocal;
  //获取用户类的类型和各个类型对应的不同的组
  $group_genre = $dbLocal->get_results("SELECT a.`GENRE`,a.`GROUP` FROM `MT4_NEW_GROUP` AS a LEFT JOIN `MT4_NEW_USERS` as b ON a.`GROUP_ID`=b.`GROUP_ID` WHERE b.`LOGIN` = '{$login}'",'ARRAY_N');
  //用户所在的各个组中其中有一个有代理商的身份即可
  foreach ( $group_genre as $key => $value ) 
  {
    //只要其中有一个组的身份类型是代理商
    if( in_array( 1, $value ) )
    {
      $group = $value[1];
      $correct = $login.$group;//验证是第一个是login和组的名称
      //将密码进行哈希，结果是一个长度为60个字符的字符串  
      global $t_hasher;
      $check = $t_hasher->CheckPassword( $correct, $password ); 
      unset($t_hasher);
      //哈希验证码等于,登录成功
      if( $check )
      {
        setcookie( 'login',  $login, time()+3600*24 );//将机构代码存入cookie中（登录凭证1）
        setcookie( 'password',  $password, time()+3600*24 );//将密码存入cookie中
        return 'success';
      }else
      {
        return 'wrongPass';
      }
    }else
    {
      return 'wrongPrivi';//没有权限
    }
  }
}

//获取密码,只有代理商才能获取密码
function obtainPassword( $login, $email )
{
  global $dbLocal;
  $trueEmail = $dbLocal->get_var( "SELECT EMAIL FROM MT4_NEW_USERS WHERE LOGIN={$login}" );
  //邮箱正确，才会进行下一步操作
  if( $trueEmail == $email )
  {
    //获取用户类的类型和各个类型对应的不同的组
    $group_genre = $dbLocal->get_results("SELECT a.`GENRE`,a.`GROUP` FROM `MT4_NEW_GROUP` AS a LEFT JOIN `MT4_NEW_USERS` as b ON a.`GROUP_ID`=b.`GROUP_ID` WHERE b.`LOGIN` = '{$login}'",ARRAY_N);
    //用户所在的各个组中其中有一个有代理商的身份即可
    foreach ( $group_genre as $key => $value ) 
    {
      //只要其中有一个组的身份类型是代理商(值为1)
      if( in_array( 1, $value ) )
      {
        $group = $value[1];
        global $t_hasher;
        $correct = $login.$group;//验证是第一个是login和组的名称
        //将密码进行哈希，结果是一个长度为60个字符的字符串  
        $hashPassword = $t_hasher->HashPassword( $correct ); 
        unset($t_hasher);
        return array('email'=>$email,'password'=>$hashPassword);
      }else
      {
        return 'wrongPrivi';//不具备权限
      }
    }
  }else
  {
    return 'wrongEmail';//邮箱与预留邮箱不一致
  }
}

//添加分组,$groupInfo为关联数组
function addGroup( $groupInfo )
{
  $keys = '';//字段
  $values = '';//插入值
  foreach ( $groupInfo as $key => $value ) 
  {
    $keys .= '`'.$key.'`,';

    if( $key == 'RATIO' )
    {
      $value = $value/100;
    }else if( $key == 'REWARD' )
    {
      $value = $value/100;
    }

    $values .= '\''.$value.'\',';
  }
  $keys = rtrim( $keys, ',' );//去掉最后一个逗号
  $values = rtrim( $values,',' );//去掉最后一个逗号
  global $dbLocal;
  $dbLocal->query("set names utf8");
  //若提交信息中包含GROUP_ID,则说明是更改分组信息
  if( $groupInfo['GROUP_ID'] ) 
  {
    $dbLocal->query("DELETE FROM MT4_NEW_GROUP WHERE GROUP_ID = {$groupInfo['GROUP_ID']}");//若分组已经存在，则进行删除
  }
  $addGroup = $dbLocal->query("INSERT MT4_NEW_GROUP($keys) VALUES($values)");
  if( $addGroup )
  {
    return true;
  }else
  {
    return false;
  }
}

/********************************************************************************************************************************************************
*功能：根据登录账号查询用户的分组信息
*参数：$logins为包含所查询的登录账号的一维数组
*返回值：$newGroup用户的分组信息
[80078704] => Array
        (
            [GROUP] => Array
                (
                    [41] => 坪山组
                    [19] => 坪山组新
                    [40] => 南山组
                    [39] => 宝安组
                )

        )

*/
function queryGroups( $logins )
{
  $loginValue = '';
  foreach ($logins as $login) 
  {
    $loginValue .= ','.$login;
  }
  $loginValue = ltrim( $loginValue, ',' );//去除第一个逗号
  global $dbLocal;
  $sql = "SELECT a.LOGIN,a.GROUP_ID,b.`GROUP` FROM MT4_NEW_USERS as a left join MT4_NEW_GROUP AS b on a.GROUP_ID = b.GROUP_ID WHERE LOGIN IN ({$loginValue}) ORDER BY LOGIN ASC";
  $userGroupInfo = $dbLocal->get_results( "$sql",ARRAY_A);//查询出所有分组信息
  $newGroup = mergeGroup($userGroupInfo);
  return $newGroup;
}


/****************************************************************************************************************************************************
*功能：对用户的分组信息进行合并
*参数：$userInfo
*/
function mergeGroup( $userInfo )
{ 
  $LOGINArr = array();
  foreach ($userInfo as $key => $value) 
  {
    $LOGIN_Arr[] = $value['LOGIN'];
  }
  $LOGIN_Arr = array_unique($LOGIN_Arr);//不重复的账号
  
  $newArr = array();
  foreach ( $LOGIN_Arr as $key => $value ) 
  {
    $newArr[$value]['GROUP'] = array();
    foreach ( $userInfo as $k => $v ) 
    {
      if( $v['LOGIN'] == $value )
      {
        if( $v['GROUP'] )
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
function updateUserGroup( $info )
{
  $newInfo = array();
  //批量选定用户添加进入相应的组
  global $dbLocal;
  if( $info['do'] == 'batchAddGroup' )
  {
    foreach ($info['LOGIN'] as $key => $value) 
    {
      $newInfo[$key]['LOGIN'] = $value;
      $newInfo[$key]['GROUP_ID'] = $info['GROUP_ID'];
    }
    $values = '';
    foreach( $newInfo as $v)
    {
      $values.='(\''.$v['LOGIN'].'\','.$info['GROUP_ID'].'),';
    }

  //修改单个用户的分组信息
  }else if( $info['do'] == 'alterGroup' )
  {
    $dbLocal->query("DELETE FROM MT4_NEW_USERS WHERE LOGIN = '{$info['LOGIN']}'");//先删除所有分组
    //没有删除所有分组
    if( !empty($info['GROUP_ID'])  )
    {
      foreach( $info['GROUP_ID'] as $v)
      {
        $values.='(\''.$info['LOGIN'].'\','.$v.'),';
      }
    }else
    {
      return true;
    }

  }
 
  $values = rtrim( $values,',' );//去掉最后一个逗号
  
  $insertLoginGroupIdRst = $dbLocal->query("INSERT MT4_NEW_USERS(`LOGIN`,`GROUP_ID`) values $values");
  //删除重复信息
  $deleteRepeat = $dbLocal->query("delete from MT4_NEW_USERS where ID in (select * from (select max(ID) from MT4_NEW_USERS group by LOGIN,GROUP_ID having count(LOGIN) > 1 and count(GROUP_ID)>1) as b)");
  
  //需要时候再去设置。 
  if( $insertLoginGroupIdRst )
  {
    return true;
  }else
  {
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
function storeInfoIntoMysql( $url ,$table )
{
  $info = file_get_contents($url);//获取页面的内容
  $info = json_decode($info);//转化为数组，是对象数组
  $columnStr = '';//字段名称的字符串值
  $valueStr = '';//插入值
  $columeArr = array();
  foreach ( $info[2] as $key => $value ) 
  {
    $columnStr.='`'.$key.'`,';
    $columeArr[] = $key;
  }
  $columnStr = rtrim( $columnStr, ',' );//取出最后一个逗号
  foreach ($info as $key => $value) 
  {
    $str = '(';
    for($i = 0;$i<count( $columeArr ); $i++)
    {
     if( $i != (count( $columeArr )-1) )
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
  $valueStr = rtrim( $valueStr, ',' );
  global $dbLocal;
  $insert = $db->query("INSERT {$table}({$columnStr}) values{$valueStr}");
  if( $insert )
  {
    return true;
  }else
  {
    return false;
  }
}

/*****************************************************************************************************************
*功能：分别获取每个直接下级的交易量和交易金额（包括去平前和去平后）
*参数：$login为登录账号LOGIN,$startDate, $endDate和开始的时间和结束的时间
*返回值：$result为多维数组array(//去平前0=>array(0=>array(0=>'',1=>'') ),//去平后1=>array())
*/
function getFirstLowerTrade( $login, $startDate, $endDate )
{
  $startTime = date('Y-m-01 00:00:00',strtotime($startDate));//这个月1号的0点
  $endTime = date('Y-m-d 00:00:00',strtotime($endDate));//截止今天的日期
  global $dbRemote;
  //获取直接下级所有的交易情况
  $getFirstLowerInfo = $dbRemote->get_results("SELECT A.LOGIN AS LOGIN,A.NAME AS NAME,SUM(CASE WHEN B.OPEN_TIME BETWEEN '{$startTime}' AND '{$endTime}' THEN 1 ELSE 0 END ) AS TRADE_QUAN,SUM(CASE WHEN B.OPEN_TIME BETWEEN '{$startTime}' AND '{$endTime}'THEN B.VOLUME/100 ELSE 0 END) AS TRANSACTION,SUM(CASE WHEN B.OPEN_TIME BETWEEN '{$startTime}' AND '{$endTime}' THEN B.PROFIT ELSE 0 END ) AS PROFIT FROM MT4_USERS AS A LEFT JOIN MT4_OPTIONS AS B ON B.LOGIN=A.LOGIN WHERE A.AGENT_ACCOUNT={$login} GROUP BY A.LOGIN ASC",ARRAY_A);

  //获取直接下去平后的交易情况
 $getFirstFlatQuna = $dbRemote->get_results("SELECT A.LOGIN AS LOGIN,A.NAME AS NAME,SUM(CASE WHEN B.OPEN_TIME BETWEEN '{$startTime}' AND '{$endTime}' AND B.PROFIT!=0 THEN 1 ELSE 0 END ) AS TRADE_QUAN,SUM(CASE WHEN B.OPEN_TIME BETWEEN '{$startTime}' AND '{$endTime}' AND B.PROFIT!=0 THEN B.VOLUME/100 ELSE 0 END) AS TRANSACTION,SUM(CASE WHEN B.OPEN_TIME BETWEEN '{$startTime}' AND '{$endTime}' AND B.PROFIT!=0 THEN B.PROFIT ELSE 0 END ) AS PROFIT FROM MT4_USERS AS A LEFT JOIN MT4_OPTIONS AS B ON B.LOGIN=A.LOGIN WHERE A.AGENT_ACCOUNT={$login} GROUP BY A.LOGIN ASC",ARRAY_A);
 
 $result = array( $getFirstLowerInfo, $getFirstFlatQuna );

  if( $getFirstLowerInfo )
  {
    return $result;
  }else
  {
    return false;
  }
}
/*****************************************************************************************************************
*功能：获取直接下级以及下级的下级的用户信息组成的多维标准数组（不包含自身）
*参数：$arr为查询到的所有的目前所有用户的信息，$login是当前用户的账号
*返回值：$lowers为所有的从属下级信息
*/
function queryLowerInfo( $arr, $login )
{
  foreach ( $arr as $key => $value )
  {
    if( $value['AGENT_ACCOUNT'] == $login )//上级机构填写自己会出现死循环
    {
      $over = $value;//借助过度变量
      $over['SUB'] = queryLowerInfo( $arr, $value['LOGIN'] );
      $lowers[] = $over;
    }  
  }
  return $lowers;//返回值为所有丛属下级构成的数组
}
/*****************************************************************************************************************
*功能：从多维数组中取出账号login值
*参数：$arr为查询到的所有的下级居间人构成的多维
*返回值：$data为所有的下级以及下级的下级（既所有下级返回的一维数组）
*/
function arrList( $arr )
{
  if( is_array( $arr ) )
  {
    foreach( $arr as $v )
    {
      if( is_array( $v ) )
      {
        if( isset( $v['LOGIN'] ) )
        {
          global $data;//此变量会造成污染，调用之前一定要记住消除global $data;$data = null;
          $data[] = $v['LOGIN'];
        }
        arrList( $v );
      }
    }
  }
  return $data;
}
/*****************************************************************************************************************
*功能：从多维数组中取出账号姓名NAME
*参数：$arr为查询到的所有的下级居间人构成的多维数组
*返回值：$data为所有的下级以及下级的下级（既所有下级返回的一维数组）
*/
function arrListName( $arr )
{
  if( is_array( $arr ) )
  {
    foreach( $arr as $v )
    {
      if( is_array( $v ) )
      {
        if( isset( $v['NAME'] ) )
        {
          global $data;//此变量会造成污染，调用之前一定要记住消除global $data;$data = null;
          $data[] = $v['NAME'];
        }
        arrListName( $v );
      }
    }
  }
    return $data;
}
/*****************************************************************************************************************
*功能：获取所有下级的姓名
*参数：$arr为查询到的所有的下级居间人构成的多维数组
*返回值：$data为所有的下级以及下级的下级（既所有下级返回的一维数组）
*/
function queryLowerName( $arr, $login )
{
  $lowerInfo = queryLowerInfo( $arr, $login );//递归获取下级机构的
  if( $lowerInfo )
  {
    global $data;$data = null;//使用递归时，造成了$data变量污染，需要消除
    $lowerNames2 = arrListName( $lowerInfo );//返回下级机构代码构成的一维数组
    $lowerNames3 =  array_unique( $lowerNames2 );//去掉重复的元素
  }
  return $lowerNames3;
}
/*****************************************************************************************************************
*功能：将标准的下级居间人信息组成的多维数组取出机构代码组成一维数组
*参数：$arr为查询到的所有的下级居间人构成的对象数组$db->get_results获取
*返回值： $lowerAgencys3为所有的从属下级居间人构成的一维数组（方便调用）
*/
function queryLowerLogin( $arr, $login )
{
  $lowerInfo = queryLowerInfo( $arr, $login );//递归获取下级机构的
  if( $lowerInfo )
  {
    global $data;$data = null;//使用递归时，造成了$data变量污染，需要消除
    $lowerLogins2 = arrList( $lowerInfo );//返回下级机构代码构成的一维数组
    $lowerLogins3 =  array_unique( $lowerLogins2 );//去掉重复的元素
  }
  return $lowerLogins3;
}
/*****************************************************************************************************************
*功能：获取具有代理商权限的机构，剔除不具有代理商身份的LOGIN
*参数：$logins为机构代码组成的一维数组(既包括具有代理商权限的，也包括不具有代理商权限的)
*返回值：完全具有代理商权限的登录账号
*/
function getAgentLogins( $logins )
{
  //一、提取出登录账号组成的字符串
  $loginValue = '';
  foreach ($logins as $login) 
  {
    $loginValue .= ','.$login;
  }
  $loginValue = ltrim( $loginValue, ',' );//去除第一个逗号
  $sql = "SELECT a.LOGIN,b.GROUP,b.RATIO,b.REWARD FROM MT4_NEW_USERS AS a LEFT JOIN MT4_NEW_GROUP AS b ON a.GROUP_ID=b.GROUP_ID WHERE a.LOGIN IN ($loginValue) AND b.GENRE =1 GROUP BY a.LOGIN ASC"; 
  global $dbLocal;
  //二、删选出具有代理商身份的用户的登录账号，分组名称，返佣比率，额外奖励额度
  $getAgentLogins = $dbLocal->get_results( "$sql",ARRAY_A );
  return $getAgentLogins;
}
/*****************************************************************************************************************
*功能：判断某个元素是否在多维数组中
*参数：$value-判断的元素；$array参考的数组
*返回值：true和false
*/
function deep_in_array($value, $array) 
{ 
  foreach($array as $item) { 
    if(!is_array($item)) 
    { 
      if ($item == $value) {
        return true;
      } else {
        continue; 
      }
    } 
    if(in_array($value, $item)) 
    {
      return true; 
    } else if(deep_in_array($value, $item)) 
    {
      return true; 
    }
  } 
  return false; 
}
/*****************************************************************************************************************
*功能：寻找某个元素的路径
*参数：登录账号
*/
function getPath($login) 
{
  global $dbRemote;
  $agentAccount = $dbRemote->get_var("SELECT AGENT_ACCOUNT FROM MT4_USERS WHERE LOGIN = {$login}");
    // 将树状路径保存在数组里面
    $path = array();

    //如果父亲节点不为空（根节点），就把父节点加到路径里面
    if ( $agentAccount!=null ) 
    {
      $parent[] = $agentAccount;
      //递归的将父节点加到路径中
      $path = array_merge(getPath($agentAccount), $parent);
    }
  return $path;
}


代码如下:
$ages = array();
foreach ($users as $user) {
    $ages[] = $user['age'];
}
 
array_multisort($ages, SORT_ASC, $users);
 
 
 
执行后，$users就是排序好的数组了，可以打印出来看看。如果需要先按年龄升序排列，再按照名称升序排列，方法同上，就是多提取一个名称数组出来，最后的排序方法这样调用：
 

 <?php 
$array[] = array('id'=>1,'price'=>50);
$array[] = array('id'=>2,'price'=>70);
$array[] = array('id'=>3,'price'=>30);
$array[] = array('id'=>4,'price'=>20);
 
foreach ($array as $key=>$value){
    $id[$key] = $value['id'];
    $price[$key] = $value['price'];
}
 
array_multisort($price,SORT_NUMERIC,SORT_DESC,$id,SORT_STRING,SORT_ASC,$array);
echo '<pre>';
print_r($array);
echo '</pre>';
?>

运行结果：

Array
(
[0] => Array
(
[id] => 2
[price] => 70
)

[1] => Array
(
[id] => 1
[price] => 50
)

[2] => Array
(
[id] => 3
[price] => 30
)

[3] => Array
(
[id] => 4
[price] => 20
)

)

function addGroup( $groupInfo )
{
  $keys = '';//字段
  $values = '';//插入值
  foreach ( $groupInfo as $key => $value ) 
  {
    $keys .= '`'.$key.'`,';

    if( $key == 'RATIO' )
    {
      $value = $value/100;
    }else if( $key == 'REWARD' )
    {
      $value = $value/100;
    }
    $values .= '\''.$value.'\',';
  }
  $keys = rtrim( $keys, ',' );//去掉最后一个逗号
  $values = rtrim( $values,',' );//去掉最后一个逗号
  global $dbLocal;
  $dbLocal->query("set names utf8");
  //若提交信息中包含GROUP_ID,则说明是更改分组信息
  if( $groupInfo['GROUP_ID'] ) 
  {
    $dbLocal->query("DELETE FROM MT4_NEW_GROUP WHERE GROUP_ID = {$groupInfo['GROUP_ID']}");//若分组已经存在，则进行删除
  }
  $addGroup = $dbLocal->query("INSERT MT4_NEW_GROUP($keys) VALUES($values)");
  if( $addGroup )
  {
    return true;
  }else
  {
    return false;
  }
}

function calculateCommsission( $pathArr,$newAgentLogins,$allTradeInfo )
{
  $dirCom = array();//直接返佣directCommission
  $gapCom = array();//下级点差返佣
  $reward = array();//下级奖励返佣
  $allCom = array();//返佣之和
  //将索引数组变成关联数组，方便操作
  foreach ($pathArr as $login => $path) 
  {
    //一、KT = 等于0，说明是最底层的代理商（点差返佣和奖励肯定是0）
    if( $path['KT'] == 0 )
    {
      $dirCom[$login]['dirCom'] = $allTradeInfo[$login]['LOWER_TRANSACTION2']*$newAgentLogins[$login]['RATIO'];//直接佣金 = 直属下级有效交易额*返点比率
      $gapCom[$login]['gapCom'] = 0;//点差佣金
      $reward[$login]['reward'] = 0;//奖励佣金
      $allCom[$login]['allCom'] = $dirCom[$login]['dirCom'];//佣金合计
    //二、非底层的代理商的佣金计算（$path['KT']！= 0）
    }else
    { 
      $gapCom[$login]['gapCom'] = $gapCom[$login]['gapCom']?$gapCom[$login]['gapCom']:0;
      $reward[$login]['reward'] = $reward[$login]['reward']?$reward[$login]['reward']:0;
      $dirCom[$login]['dirCom'] = $allTradeInfo[$login]['LOWER_TRANSACTION2']*$newAgentLogins[$login]['RATIO'];//直接佣金 = 直属下级有效交易额*返点比率
      $allCom[$login]['allCom'] = $dirCom[$login]['dirCom'] + $gapCom[$login]['gapCom'] + $reward[$login]['reward'];//佣金合计
    }

    //三、判断是否奖励上级
    //该交易者的返佣比率和上级的代理相等或者还高于上级，则说明要进行奖励，end($pathArr[$login])返回的是用户的直接上级
    if( $newAgentLogins[$login]['RATIO'] >= $newAgentLogins[end($pathArr[$login])]['RATIO'] )
    {
      //上下级比率相同时，上级奖励=下级纯利润*奖励比率
      $reward[end($pathArr[$login])]['reward'] += $newAgentLogins[end($pathArr[$login])]['REWARD']*$allCom[$login]['allCom'];
/*$pathArr[$login][count($pathArr[$login])-2];*/
      //对该路径进行翻转，确保从底层向上
      $newPath = array_reverse($path);

      foreach ($newPath as $hlogin) 
      { 
        //如果能找到比率比他高的人，则返点给他，此处找到一次就必须停止，因为点差只补一次
        if( isset($newAgentLogins[$hlogin])&&( $newAgentLogins[$hlogin]['RATIO']>$newAgentLogins[$login]['RATIO'] ) )
        {
          $gapCom[$hlogin]['gapCom'] += ($newAgentLogins[$hlogin]['RATIO']-$newAgentLogins[$login]['RATIO'])*$allTradeInfo[$login]['LOWER_TRANSACTION2'];
          break;
        }
      }
    //四、判断是否返点给上级
    //直接上级的返点比率高于当前用户时，则只进行点差返点
    }else if( $newAgentLogins[$login]['RATIO'] < $newAgentLogins[end($pathArr[$login])]['RATIO'] )
    {
      $gapCom[end($pathArr[$login])]['gapCom'] += ($newAgentLogins[end($pathArr[$login])]['RATIO']-$newAgentLogins[$login]['RATIO'])*$allTradeInfo[$login]['LOWER_TRANSACTION2'];
    }
  }
  return array('dirCom'=>$dirCom, 'gapCom'=>$gapCom, 'reward'=>$reward, 'allCom'=>$allCom);
}
/*****************************************************************************************************************
* 功能：根据的登录账号login插叙直属下级的身份
* 参数: $login父级的登录login
* 返回值：数组
*/
function getFirstGenre($login)
{
  global $dbRemote;
  global $dbLocal;
  $firLogin = $dbRemote->get_col("select LOGIN from MT4_USERS WHERE AGENT_ACCOUNT={$login}");
  $firLoginValue = '';
  foreach ($firLogin as $key => $value) 
  {
    $loginValue .= $value.',';
  }
  $loginValue = rtrim(',',$loginValue);
}

        //页码的处理
        //只有一页
        if( $pageCount == 1 )
        {
          
          $pageHtml = <<<EOT
          <li><a onclick="return false" disabled>首页</a></li>
          <li><a onclick="return false" disabled>&laquo;</a></li>
          <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&orderBy=$orderBy?$orderBy:''&order=$order?$order:''">$pageno</a></li>
          <li><a onclick="return false" disabled>&raquo;</a></li>
          <li><a onclick="return false" disabled>尾页</a></li>
EOT;
        }
        //不只有一页 
        else 
        {
          // 当前为第一页
          if ( $pageno == 1 )
          { 
            $pageHtml = <<<EOT
              <li><a onclick="return false" disabled>首页</a></li>
              <li><a onclick="return false" disabled>&laquo;</a></li>
              <li><a onclick="return false" disabled>$pageno</a></li>
              <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$pageno+1&orderBy=$orderBy?$orderBy:''&order=$order?$order:''">&raquo;</a></li>
              <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$pageCount&orderBy=$orderBy?$orderBy:''&order=$order?$order:''">尾页</a></li>
EOT;                                                                                                                          
          }
          // 当前页为最后页
          else if ( $pageno == $pageCount ) 
          { 
            $pageHtml = <<<EOT
              <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&orderBy=$orderBy?$orderBy:''&order=$order?$order:''">首页</a></li>
              <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$pageCount-1&orderBy=$orderBy?$orderBy:''&order=$order?$order:''">&laquo;</a></li>
              <li><a onclick="return false" disabled>$pageno</a></li>
              <li><a onclick="return false" disabled>&raquo;</a></li>
              <li><a onclick="return false" disabled>尾页</a></li>
EOT;
          }
          // 当前页介于1到总页数之间
          else if ( $pageno > 1 && $pageno < $pageCount ) 
          { 
            
            $pageHtml = <<<EOT
              <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=1&orderBy=$orderBy?$orderBy:''&order=$order?$order:''">首页</a></li>
              <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$pageno-1&orderBy=$orderBy?$orderBy:''&order=$order?$order:''">&laquo;</a></li>
              <li><a onclick="return false" disabled>$pageno</a></li>
              <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$pageno+1&orderBy=$orderBy?$orderBy:''&order=$order?$order:''">&raquo;</a></li>
              <li><a href="{$url}index.php?action=user-managenent&do=user-list&pageno=$pageCount&orderBy=$orderBy?$orderBy:''&order=$order?$order:''">尾页</a></li>
EOT;  
          }
            $searchPage = <<<EOT
             $separator,$separator,<input type="button" value="搜索页码" onclick="location.href=\'' , $_SERVER['PHP_SELF'] ,'?action=agents','&orderBy=',$orderBy?$orderBy:'','&order=',$order?$order:'','&pageno=\'+document.getElementById(\'t\').value"','/>', $separator,$separator,$separator,$separator,'共计',$separator,$separator,$pageCount,$separator,$separator,'页',$separator,$separator,$separator,$separator,$separator,'累计',$separator,$separator,$separator,  $rowCount  ,$separator,$separator,$separator,'名用户'
EOT;
        }