<script>
/********************************************************************************登录
*登录时的js
*/ 
<?php if( !$_COOKIE['agency'] || !$_COOKIE['name'] && $_GET['action'] ) {?>
    function check_login()
    {
        var username = $("#username").val();
        var password = $("#password").val();
        if( username == '' )
        {
          alert("请填入用户名");
          $("#username").focus();
          return false;
        }else if( password == '' )
        {
          alert("密码不能为空");
          $("#password").focus();
          return false;
        }
    }  
<?php } ?>
<?php if( (!$_COOKIE['agency'] || !$_COOKIE['name']) && $_GET['action'] == 'register'){?> 
  $(document).ready(function(){
    $("#registerFrm").validate({
      rules: {
        //机构代码必填
        agency: {
          remote: {
            url: "checkAgency.php",
            type: "post",
            dataType: "html",
            data: {
              registerAgency: function () {
                return $("#agency").val();　　　　//这个是去验证的机构代码
              }
            },
            dataFilter: function (data) {　　　　//判断控制器返回的内容
              if (data == "true") {
                return true;
              }
              else if( data == "false" ){
                return false;
              }
            },
         },
      },

      username: {
          remote: {
            url: "checkAgency.php",
            type: "post",
            dataType: "html",
            data: {
              registerUsername: function () {
                return $("#username").val();　　　　//这个是去验证的机构代码
              }
            },
            dataFilter: function (data) {　　　　//判断控制器返回的内容
              if (data == "true") {
                return true;
              }
              else if(data == "false") {
                return false;
              }
            },
         },
      },

      password: {
        required: true,
        minlength: 5,
      },

      checkPassword: {
        equalTo: '#password',
      },
    },
      messages: {
        agency: {
          remote:  "<span style='color:red;font-weight: 200;'>该机构代码已经存在。勿重复注册</span>",
        },
        username: {
          remote:  "<span style='color:red;font-weight: 200;'>该用户名已经存在，请进行更更换</span>",
        },

        password: {
          minlength: "<span style='color:red;font-weight: 200;'>密码至少5位</span>",
        },
        
        checkPassword: {
        equalTo: "<span style='color:red;font-weight: 200;'>两次密码输入不一致</span>",
        },

      },
  });
});
  
  /*验证用户名*/
  function checkedUsername()
  {
    var username = $("#username").val();
    var regUsername=/^[A-Za-z0-9_]{4,16}$/;//用户名的正则表达式，4-16位的数字字母及下划线
     if( username & !regUsername.exec(username) )
    {
      $("#checkUsername").html('<span style="color:red;">用户名格式不正确</span>');
      $("#username").focus();
    }else if( regUsername.exec(username) )
    {
      $("#checkUsername").html('<span style="color:green;">用户名格式正确</span>');
    }
  }
/*验证注册信息的时各个元素是否为空,以及各式是否符合要求,以及要求*/
  function checkRegister()
  {
    var agency = $("#agency").val();
    var username = $("#username").val();
    var regUsername=/^[A-Za-z0-9_]{4,16}$/;//用户名的正则表达式，4-16位的数字字母及下划线
    var password = $("#password").val();
    var regPassword=/^[A-Za-z0-9_]{6,20}$/;//密码的正则表达式，6-20位数字字母及下划线
    var checkPassword = $("#checkPassword").val();
    var verify = $("#verify").val();
    if(agency == '')
    {
      alert('请填写机构代码');
      $("#agency").focus();
      return false;
    }
    else if(username == '')
    {
      alert('请设置用户名');
      $("#username").focus();
      return false;
    }else if( !regUsername.exec(username) )
    {
      alert('用户名格式不正确');
      $("#username").focus();
      return false;
     }else if(password == '')
    {
      alert('请设置密码');
      $("#password").focus();
      return false;
    }else if( !regPassword.exec( password ) )
    {
      alert('密码格式不正确');
      $("#password").focus();
      return false;
    }
    else if(checkPassword == '')
    {
      alert('请确认密码');
      return false;
    }
    else if(checkPassword !== password)
    {
      alert('两次密码输入不一致');
      $("#checkPassword").focus();
      return false;
    }
    else if(verify == '')
    {
      alert('请输入注册验证码');
      $("#verify").focus();
      return false;
    }
  }  
<?php } ?>
  $(document).ready(function() {
    <?php if( $_GET['action'] == "apply" ) { ?>
    $('.delete').click(function(){
      var name = $(this).data('name'),
          r = confirm("是否删除『"+name+"』？注意，该操作不可逆转！");
      if (r==true) {
        var id = $(this).data('id');
        $.get("/index.php?action=delete-apply-item&id="+id,function(data,status){
          if(status=='success' && data==1) {
            $('#'+id).fadeOut("slow");
          } else {
            alert('抱歉，请重试！');
          }
        });
      } else return;
    });
    $('.done').click(function(){
      var name = $(this).data('name'),
          r = confirm("是否将『"+name+"』标记为已完成发送虚拟账户？");
      if (r==true) {
        var id = $(this).data('id'),
            account = $('#account-'+id).val(),
            password = $('#password-'+id).val();
        $.get("/index.php?action=done-apply-item&id="+id+"&account="+account+"&password="+password,function(data,status){
          if(status=='success' && data==1) {
            $('#'+id).fadeOut("slow");
          } else {
            alert('抱歉，请重试！');
          }
        });
      } else return;
    });
    $('.mailto').click(function(){
      var id = $(this).data('id'),
      name = $(this).data('name'),
      email = $(this).data('email'),
      remark = $('#remark-'+id).val(),
      account = $('#account-'+id).val(),
      password = $('#password-'+id).val();
      if (account=="") {
        alert('抱歉，请填入模拟账户后重试！');
      } else if (account=="0") {
        alert('抱歉，您填入模拟账户无效！');
      } else if (password=="") {
        alert('抱歉，请填入密码后重试！');
      } else { 
        $.get("/index.php?action=mailto-apply-item&id="+id+"&account="+account+"&password="+password+"&aname="+name+"&email="+email,function(data,status){
          if(status=='success' && data==1) {
            $('#'+id).fadeOut("slow");
          } else {
            alert('抱歉，请重试！');
          }
        });
      }
    });
    $('.remark').blur(function(){
      var id = $(this).data('id'),
          o = $(this).data('remark'),
          n = $(this).val();
      if(n!=o && n!="123456"){
        $.get("/index.php?action=update-apply-item&id="+id+"&remark="+n,function(data,status){
          if(status=='success' && data==1) {
            //alert('已经更新！');
            //$(this).after("");
          } else {
            alert('抱歉，请重试！');
          }
        });
      } else return;
    });
    <?php } ?>
    $('.adupdate').blur(function(){
      var id = $(this).data('id'),
          r = $(this).val();
      if(r){
        $.get("/index.php?action=ad&update="+id+"&remark="+r,function(data,status){
          if(status=='success') {
            return false;
          } else {
            alert('抱歉，请重试！');
          }
        });
      } else return false;
    });
    
    $("#author").mouseover(function(){
      $("#wechat").fadeIn();
      $(this).mouseleave(function(){
        $("#wechat").fadeOut();
      });
    });
    
    $(".js-AutoHeight").css("overflow","hidden").bind("keydown keyup", function(){
      $(this).height('0px').height($(this).prop("scrollHeight")+"px");  
    }).keydown();
    
    $(".fancybox").fancybox();
    $(".various").fancybox({
      maxWidth	: 800,
  		maxHeight	: 600,
  		fitToView	: false,
  		width		: '400',
  		height		: '400',
  		autoSize	: false,
  		closeClick	: false,
  		openEffect	: 'none',
  		closeEffect	: 'none'
  	});
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();

    /*编辑或者添加机构页面*/
    <?php if( ($_GET['action'] == "agents" && $_GET['do'] == "add") || ($_GET['action'] == "agents" && $_GET['do'] == "edit") ) { ?>
    new PCAS("province","city","area"<?php if( $_COOKIE['province'] ){echo ","."\"".$_COOKIE['province']."\"";if( $_COOKIE['city'] ){ echo ","."\"".$_COOKIE['city']."\""; if( $_COOKIE['area'] ){ echo ","."\"".$_COOKIE['area']."\"";}}}?>)//省市县，三级联动下拉菜单
    $("#higher_name").change(function(){
      if($(this).val()){
        $("#tripartite_agreement").fadeIn();
      }else{
        $("#tripartite_agreement1").attr("checked","checked");
        $("#tripartite_agreement").fadeOut(); 
      }
    });
    $(document).ready(function() {
      //加载页面时，自动调用函数，经纪机构的下级可以是自然人，自然人的下级也不能是经纪机构
      if( $('#genre1').attr("checked")=="checked" )
      {
        $("#genre2").attr("disabled", true); 
        $('#genre1-content').css('display','block');
        $('#genre2-content').css('display','none');
      }

      if( $('#genre2').attr("checked")=="checked" )
      {
        $('#genre2-content').css('display','block');
        $('#genre1-content').css('display','none');
      }

    <?php if($_GET['action'] == "agents" && $_GET['do'] == "add") {?>
      //表单验证插件(添加机构时才验证)
      $(".form-horizontal").validate({
        rules: {
          //机构代码必填
          agency: {
            required: true,
            number:true,
            minlength: 6,
            maxlength: 16,
            remote: {
              url: "checkAgency.php",
              type: "post",
              dataType: "html",
              data: {
                agency: function () {
                  return $("#agency").val();　　　　//这个是去验证的机构代码
                }
              },
              dataFilter: function (data) {　　　　//判断控制器返回的内容
                if (data == "true") {
                  return true;
                }
                else {
                  return false;
                }
              },
          },
          },
          //邀请码必填
          serialcode: {
            required: true,
            number: true,
            minlength: 6,
            maxlength: 16,
          },
          //签约日期必填
          begin: {
            required: true,
            date: true,
          },
          //省份不能为空
          province: {
            required: true,
          },
          //市、区不能为空
          city:  {
            required: true,
          },
      
          //地区不能为空
          area:  {
            required: true,
          },
          //机构名称不能为空
          name:  {
            required: true,
          },
          //联系人不能为空
          agency_contact:  {
            required: true,
          },
          //电话号码不能为空
          phone: {
            required: true,
            number: true,
            minlength: 6,
            maxlength: 12,
          },
          //QQ号码不能为空
          qq: {
            required: true,
            number: true,
            minlength: 5,
            maxlength: 14,
          },
          //居间人不能为空
          genre: {
            required: true,
          },
          //业务经理不能为空
          salesman: {
            required: true,
          },
          //客服经理不能为空
          service:  {
            required: true,
          },
        },

        messages: {
          agency: {
            number: "<span style='color:red;font-weight: 200;'>机构代码只是数字</span>",
            minlength: "<span style='color:red;font-weight: 200;'>机构代码不能小于6个数字</span>",
            maxlength: "<span style='color:red;font-weight: 200;'>机构代码不能大于16个数字</span>",
            required: "<span style='color:red;font-weight: 200;'>机构代码必须填写，并且只能为数字</span>",
            remote:  "<span style='color:red;font-weight: 200;'>该机构代码已经存在</span>",
          },

          serialcode: {
            required: "<span style='color:red;font-weight: 200;'>注册邀请码必须填写，并且只能为数字</span>",
            minlength: "<span style='color:red;font-weight: 200;'>注册邀请码不能小于6个数字</span>",
            maxlength: "<span style='color:red;font-weight: 200;'>注册邀请码不能大于10个数字</span>",
          },
          
          begin: {
            required: "<span style='color:red;font-weight: 200;'>日期不能为空。</span>",
            date: "<span style='color:red;font-weight: 200;'>请输入合法日期</span>"
          },

          province: {
            required: "<span style='color:red;font-weight: 200;'>请输入省份或直辖市</span>",
          },

          city: {
            required: "<span style='color:red;font-weight: 200;'>请输入所在市或区</span>",
          },

            area: {
            required: "<span style='color:red;font-weight: 200;'>请输入所属县、区</span>",
          },

           name: {
            required: "<span style='color:red;font-weight: 200;'>请输入机构名称</span>",
          },
          
           
          agency_contact: {
            required: "<span style='color:red;font-weight: 200;'>联系人不能为空</span>",
          },
          
          phone: {
            required: "<span style='color:red;font-weight: 200;'>电话号码不能为空</span>",
            number: "<span style='color:red;font-weight: 200;'>请输入正确的电话号码</span>",
            minlength: "<span style='color:red;font-weight: 200;'>请输入正确的电话号码</span>",
            maxlength: "<span style='color:red;font-weight: 200;'>请输入正确的电话号码</span>",
          },

          qq: {
            required: "<span style='color:red;font-weight: 200;'>QQ号码不能为空</span>",
            number: "<span style='color:red;font-weight: 200;'>请输入正确的QQ号码</span>",
            minlength: "<span style='color:red;font-weight: 200;'>请输入正确的QQ号码</span>",
            maxlength: "<span style='color:red;font-weight: 200;'>请输入正确的QQ号码</span>",
          },

          genre: {
            required: "<span style='color:red;font-weight: 200;'>请选择居间人类型</span>",
          },

          salesman: {
            required: "<span style='color:red;font-weight: 200;'>业务经理不能为空</span>",
          },

          service: {
            required: "<span style='color:red;font-weight: 200;'>客服经理不能为空</span>",
          },
        },
       
      });
  <?php } ?>
  });
    //居间人类型-------自然人/佣金
    $('#genre1').click(function(){
      $('#genre1-content').fadeIn();
      $('#reward input:radio:first').attr('checked', 'checked');//默认 额外奖励------无额外奖励
      $('#rebate input:radio').eq(1).attr('checked', 'true');//默认 返佣形式-------固定比率
      $('#genre2-content').fadeOut();
    });
    //居间人类型-------经济机构/头寸
    $('#genre2').click(function(){
      $('#genre2-content').fadeIn();
      $('#genre1-content').fadeOut();
      $('#reward input[name="reward"]').removeAttr("checked");
      $('#rebate input[name="rebate"]').removeAttr("checked");
    });
    $('.mode').click(function(){
      var bail = $(this).data('mode');
      $('#bail').val(bail);
      $('#bail-container').fadeIn();
    });
    $('#rebate1').click(function(){
      $('#level-container1').fadeIn();
      $('#level-container2').fadeOut();
    });
    $('#rebate2').click(function(){
      $('#level-container2').fadeIn();
      $('#level-container1').fadeOut();
    });

    $('.level').click(function(){
      var level = $(this).data('level');
          id = '#level-'+level;
      if($(this).is(':checked'))
        $(id).fadeIn();
      else
        $(id).fadeOut();
    });//返佣级别的弹出，选择相应的字母,出现相应的范围框
    
    $('#reward1').click(function(){
      $('#ratio-container').fadeOut();
      $('#ratio').val('');
    })
    $('#reward2').click(function(){
      $('#ratio-container').fadeIn();
    })
    $('#reward3').click(function(){
      $('#ratio-container').fadeOut();
      $('#ratio').val('');
    })
      //if($('#ratio').val()=='')
      //  $('#ratio').val('10%');
    <?php } ?>
  });
<?php if ( $_GET['action']=="upload" && $_GET['do']=='data' ){?>//上传数据表的css
  function checkFile() {
    var file = $("#file").val();
    var strFileName=file.replace(/^.+?\\([^\\]+?)(\.[^\.\\]*?)?$/gi,"$1");  //正则表达式获取文件名，不带后缀
    var FileExt=file.replace(/.+\./,"");   //正则表达式获取后缀
    if(file == '')
    {
      alert('上传文件为空');
      return false;
    }else if( FileExt!='csv')
    {
      alert('请上传csv文件');
      return false;
    }else
    {
      return true;
    }
  }
<?php }?>

//收益核算页面的js
<?php if( $_GET['action']=='income' ){ ?>
  //格式化日期（参数date为对象,返回对象为XXXX-XX-XX）
function formatDate(date) 
{   
  var myyear = date.getFullYear();   
  var mymonth = date.getMonth()+1;   
  var myweekday = date.getDate();  
if(mymonth < 10)
{   
  mymonth = "0" + mymonth;   
}   
if(myweekday < 10)
{   
  myweekday = "0" + myweekday;   
}   
  return (myyear+"-"+mymonth + "-" + myweekday);   
} 

//获取本周的开始时间
function getWeekStartDate() { 
  var now = new Date(); //当前日期   
  var nowDayOfWeek = now.getDay(); //今天本周的第几天   
  var nowDay = now.getDate(); //当前日   
  var nowMonth = now.getMonth(); //当前月   
  var nowYear = now.getFullYear(); //当前年   
  var weekStartDate = new Date(nowYear, nowMonth, nowDay - nowDayOfWeek);   
  return formatDate(weekStartDate);   
}  

//获取上周开始时间
function getLastWeekStartDate()
{
  var now = new Date();                    //当前日期
  var nowDayOfWeek = now.getDay();         //今天本周的第几天
  var nowDay = now.getDate();              //当前日
  var nowMonth = now.getMonth();           //当前月
  var nowYear = now.getYear();             //当前年
  nowYear += (nowYear < 2000) ? 1900 : 0;  //
  //获得上周的开始日期
  var getUpWeekStartDate = new Date(nowYear, nowMonth, nowDay - nowDayOfWeek -7);
  var getUpWeekStartDate =  formatDate(getUpWeekStartDate);
  return getUpWeekStartDate;
}

//获取上周结束的时间
function getLastWeekEndDate()
{
  var now = new Date();                    //当前日期
  var nowDayOfWeek = now.getDay();         //今天本周的第几天
  var nowDay = now.getDate();              //当前日
  var nowMonth = now.getMonth();           //当前月
  var nowYear = now.getYear();             //当前年
  nowYear += (nowYear < 2000) ? 1900 : 0;  //
  //获得上周的开始日期
  var getUpWeekEndDate = new Date(nowYear, nowMonth, nowDay + (6 - nowDayOfWeek - 7));
  var getUpWeekEndDate =  formatDate(getUpWeekEndDate);
  return getUpWeekEndDate;
}

//获得本季度的开始日期(结束日期默认为到今天为止)     
function getNowQuarterStartDate( )
{      
  var now = new Date();            //当前日期   
  var nowYear = now.getYear(); 
  nowYear += (nowYear < 2000) ? 1900 : 0;
  var currMonth = now.getMonth()+1;
  var currQuarter = Math.floor( ( currMonth % 3 == 0 ? ( currMonth / 3 ) : ( currMonth / 3 + 1 ) ) );
  switch ( currQuarter )
  {      
    case 1: return nowYear+"-01-01";
    case 2: return nowYear+"-04-01";
    case 3: return nowYear+"-07-01";
    case 0: return nowYear+"-10-01";//上一年的10月1日
  }
}

//获得上季度的开始日期      
function getLastQuarterStartDate( )
{      
  var now = new Date();            //当前日期          //当前月
  var nowYear = now.getYear(); 
  nowYear += (nowYear < 2000) ? 1900 : 0;
  var currMonth = now.getMonth()+1;
  var currQuarter = Math.floor( ( currMonth % 3 == 0 ? ( currMonth / 3 ) : ( currMonth / 3 + 1 ) ) );
  //当前日期减去1代表上1季度
  switch ( currQuarter -1 )
  {      
    case 0: return nowYear -1+"-10-01";//上一年的10月1日
    case 1: return nowYear+"-01-01";
    case 2: return nowYear+"-04-01";
    case 3: return nowYear+"-07-01";
  }
} 
//获得上季度的结束日期      
function getLastQuarterEndDate( )
{      
  var now = new Date();            //当前日期          //当前月
  var nowYear = now.getYear(); 
  nowYear += (nowYear < 2000) ? 1900 : 0;
  var currMonth = now.getMonth()+1;
  var currQuarter = Math.floor( ( currMonth % 3 == 0 ? ( currMonth / 3 ) : ( currMonth / 3 + 1 ) ) );
  switch ( currQuarter -1 )
  {      
     case 0 : return nowYear -1+"-12-31";
     case 1 : return nowYear+"-03-31";
     case 2 : return nowYear+"-06-30";
     case 3 : return nowYear+"-09-30";
  }
} 
  
$(document).ready(function (){
  //月和日不足两位的用0补全
  function formatMandD(val)
  {
    var valFormate = val;
    if(valFormate<10)
    {
      valFormate = "0"+valFormate;
    }
      return valFormate;
  }
  
  //获取某个年、月的天数
  function DayNumOfMonth(Year,Month)
  {
    var d = new Date(Year,Month,0);
    return d.getDate();
  }

  //默认的日期
  var date = new Date();
  var nowYear = date.getFullYear();//获取今年的年份
  nowYear += (nowYear < 2000) ? 1900 : 0;//年份的函数具有特殊清况，有时为年份减去1900
  var nowMonth = formatMandD( date.getMonth()+1 );//获取今天的月
  var lastMonth = formatMandD( date.getMonth());//获取上个月的月份
  var nowDay = formatMandD( date.getDate() );//获取今天的日
  var yesDay = formatMandD( date.getDate() -1);//获取昨天的日

  var today = nowYear+"-" + nowMonth + "-" + nowDay;//今天的日期
  var yesterday = nowYear+"-" + nowMonth + "-" + yesDay;//昨天的日期
  //默认选中昨天
  var checked = $('input[name="cycle"]:checked').val();
  //页面初始化时，未选中任何的查询周期
  if( checked == undefined)
  {
    $('#yday').attr("checked",'checked');
    $('#cycleStart').val( yesterday ); //开始时间默认昨天
    $('#cycleEnd').val( yesterday );//结束时间也是昨天天
  }

  //点击今天
  $('#tday').click(function(){
    $('#cycleStart').val( today ); //开始时间是当天
    $('#cycleEnd').val( today );//结束时间也是当天
  });
  //点击昨天
  $('#yday').click(function(){
    $('#cycleStart').val( yesterday ); //开始时间是昨天
    $('#cycleEnd').val( yesterday );//结束时间是昨天
  });

  //点击本周
  $('#tweek').click(function(){ 
    $('#cycleStart').val( getWeekStartDate() ); //本周开始的时间
    $('#cycleEnd').val( today );//本周结束的时间（到目前为止）
  });
  //点击上周
  $('#lweek').click(function(){ 
    $('#cycleStart').val( getLastWeekStartDate() ); //上周开始的时间
    $('#cycleEnd').val( getLastWeekEndDate() );//上周结束的时间
  });

  //点击本月
  $('#tmonth').click(function(){ 
    $('#cycleStart').val( nowYear+'-'+nowMonth+'-01' ); //本月开始的时间
    $('#cycleEnd').val( today );//本月结束的时间(到目前为止)
  });
  //点击上月
  $('#lmonth').click(function(){ 
    var days = DayNumOfMonth(nowYear,nowMonth-1);//获取上个月的天数
    $('#cycleStart').val( nowYear+'-'+lastMonth+'-01' ); //上月开始的时间
    $('#cycleEnd').val( nowYear+'-'+lastMonth+'-'+days);//上月结束的时间
  });

  //点击本季度
  $('#tquarter').click(function(){ 
    $('#cycleStart').val( getNowQuarterStartDate( ) ); //本季度开始的日期
    $('#cycleEnd').val( today );//本季度结束的日期（到今天为止）
  });
  //点击上季度
  $('#lquarter').click(function(){ 
    $('#cycleStart').val( getLastQuarterStartDate( ) ); //上季度开始的日期
    $('#cycleEnd').val( getLastQuarterEndDate( ) );//上季度结束的日期*/
  });

  //点击本年度
  $('#tyear').click(function() {
    $('#cycleStart').val( nowYear+'-01-01' ); //本年度开始的日期
    $('#cycleEnd').val( today );//默认为到现在今天
  });
  //点击上一年
  $('#lyear').click(function() {
    $('#cycleStart').val( nowYear -1+'-01-01' ); //上年开始的日期
    $('#cycleEnd').val( nowYear -1+'-12-31' );//到12月31
  });

  //若改变日期框中的值。则单选按钮都不被选中
  $('#cycleStart').change(function () {
    $('input[name="cycle"]:checked').removeAttr('checked');
  })


})

<?php } ?>
</script>