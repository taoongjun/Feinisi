<?php
ini_set('display_errors',1);            //错误信息
ini_set('display_startup_errors',1);    //php启动错误信息
error_reporting(-1);                    //打印出所有的 错误信息
include_once "lib/ezSQL/ez_sql_core.php";
include_once "lib/ezSQL/ez_sql_mysql.php";
$db = new ezSQL_mysql('shenzhen','AA4bW3sybS6ALLsT','mt4','47.90.60.122','utf-8');//实例化远程数据库
$query = "select NAME,BALANCE from 	`mt4_users` where `GROUP` like '%FEINISI%' and `LOGIN` not in(80057308,80054896)";
$query = $db->get_results($query);
//print_r($query);
$i = 1;
?>
<!DOCTYPE html>
<html lang="zh-CN" style="font-size: 62.5%;">
<head>
  <link rel="shortcut icon" href="/favicon.png">
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <meta name="renderer" content="webkit">
  <!-- js -->
  <script src="lib/js/jquery.min.js"></script>
  <script src="lib/js/bootstrap.min.js"></script>
  <!-- css -->
  <link rel="stylesheet" href="lib/css/bootstrap.min.css">
  <style>.js_dn{display:none}</style>
  <title>客户余额</title>
</head>
<body style="padding:20px">
    <table class="table table-bordered table-hover">
        <thead><tr><th>√</th><th>#</th><th>名字</th><th>余额 <a href="javascript:void(0);" id="js_z">显示/隐藏 0</a></th></tr></thead>
        <tbody>
            <?php foreach($query as $q){ ?>
                <tr<?php echo $q->BALANCE == 0 ? " class='js_dn'" : ""; ?>>
                    <td><input type="checkbox" class="item" name="item" value="<?=$q->BALANCE?>" checked></td>
                    <td><?=$i?></td>
                    <td><?=$q->NAME?></td>
                    <td><?=$q->BALANCE?></td>
                </tr>
            <?php $i++; } ?>
        </tbody>
        <tfoot><tr><td colspan="3" style="text-align:right">合计（$）</td><td><span id="js_sum"></span></td></tr></tfoot>
    </table>
    <script>
        jQuery(function($) {
            var sum = 0;
            $('input[name="item"]:checked').each(function(){
                sum += Number($(this).val());
            });
            $('#js_sum').text(sum);
            
            $('.item').click(function(){
                var sum = 0;
                $('input[name="item"]:checked').each(function(){
                    sum += Number($(this).val());
                });
                $('#js_sum').text(sum);
            });
            
            $('#js_z').click(function(){
               $('.js_dn').toggle();
            });
        });
    </script>
</body>
</html>