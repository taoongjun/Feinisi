//日期函数
//当前日期之前几天或之后的日期,AddDayCount为天数（可以为负数0为今天）
  function getDateStr(AddDayCount) 
  {
    var dd = new Date();
    dd.setDate(dd.getDate()+AddDayCount);//获取AddDayCount天后的日期
    var y = dd.getFullYear();
    y += (y < 2000) ? 1900 : 0;
    var m = formatMandD(dd.getMonth()+1);//获取当前月份的日期
    var d = formatMandD(dd.getDate());
    return y+"-"+m+"-"+d;
  }

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
  //格式化日期
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
//获取本周开始的时间和结束的时间
  function getWeekStartDate() 
  { 
    var now = new Date(); //当前日期   
    var nowDayOfWeek = now.getDay(); //今天本周的第几天   
    var nowDay = now.getDate(); //当前日   
    var nowMonth = now.getMonth(); //当前月（此处不要加1，加1是便于显示用的）   
    var nowYear = now.getFullYear(); //当前年   
    var weekStartDate = new Date(nowYear, nowMonth, nowDay - nowDayOfWeek);   
    return formatDate(weekStartDate);   
  }  
  //获取上周的开始和结束时间(若没有赋予时间参数，则默认当前日期的上个礼拜)
  function getLastWeekStartAndEnd( date )
  {
    var startAndEnd = new Array();             
    var date = date;
    if( date==null )
    {
      var now = new Date();  
    }else
    {
      var now = new Date( date ); 
    }
    var nowDayOfWeek = now.getDay();         //今天本周的第几天
    var nowDay = now.getDate();              //当前日
    var nowMonth = now.getMonth();           //当前月（此处不要加1，加1是便于显示用的）
    var nowYear = now.getYear();             //当前年
    nowYear += (nowYear < 2000) ? 1900 : 0;  //
    //获得上周的开始日期
    var getLastWeekStartDate = new Date(nowYear, nowMonth, nowDay - nowDayOfWeek -7);
    startAndEnd[0] =  formatDate(getLastWeekStartDate);
    //获取上周结束的日期
    var getLastWeekEndDate = new Date(nowYear, nowMonth, nowDay + (6 - nowDayOfWeek - 7));
    startAndEnd[1] =  formatDate(getLastWeekEndDate);
    return startAndEnd;
  }
  //获取上个月的第一天和最后一天
  function getLastMonthStartAndEnd( date )
  {
    var date = date;
    if( date==null )
    {
      var nowdays = new Date();  
    }else
    {
      var nowdays = new Date(date); 
    }
    var year = nowdays.getFullYear();    
    var month = nowdays.getMonth();//当前的月份，用于显示的（月份值0-11）    
    if(month==0) { month=12; year=year-1; }    
    if (month < 10) { month = '0' + month; }    
    var firstDay = year + "-" + month + "-" + "01";//上个月的第一天       
    var myDate = new Date(year, month, 0); //月份值：1-12   
    var lastDay = year + "-" + month + "-" + myDate.getDate();//上个月的最后一天Date(year, month, 0)，这种书写方式，其月份是1-12，myDate.getDate()获取当月的天数
    var startAndEnd = [ firstDay, lastDay ];
    return startAndEnd;
  } 
//日期函数