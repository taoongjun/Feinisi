<div class="content-body">
  <div class="content-title">添加分组</div>
  <div class="col-lg-12">
    <div class="alert alert-danger" role="alert">    
      可以同时设置多组
    </div>
  </div>
  <div class="col-lg-12">
    <form class="form-horizontal" action="" method="post" name="add-group-frm">
      <div class="form-group">
        <label for="GROUP" class="col-sm-1 control-label">分组名称</label>
        <div class="col-sm-5">
          <input type="text" class="form-control" id="GROUP" name="GROUP" value="" >
        </div>
      </div>
      <div class="form-group">
        <label for="RATIO" class="col-sm-1 control-label">返点比率</label>
        <div class="col-sm-1">
          <input type="number" step="0.1" class="form-control" id="RATIO" name="RATIO" value=""><!-- 奖励下级居间人纯利返点的返点比率 -->
        </div>%
      </div>
      <div class="form-group">
        <label for="REWARD" class="col-sm-1 control-label">额外奖励</label>
        <nobr>
          <div class="col-sm-1">
            <input type="number" step="0.1" class="form-control" id="REWARD" name="REWARD" value=""><!-- 奖励下级居间人纯利返点的返点比率 -->
          </div>%
        </nobr>
      </div>

      <div class="form-group">
        <label for="GENRE" class="col-sm-1 control-label">分组类型</label>
        <div class="col-sm-5">
          <div class="radio" id="genre">
          <!-- 自然人的下级只能是自然人，经济机构的下级只能是经纪机构 -->
            <label class="radio-inline">
              <input type="radio" name="GENRE" value="1" >
              <?=$agentGroupName?>
            </label>
            <label class="radio-inline">
              <input type="radio" name="GENRE" value="2" >
              <?=$tradeGroupName?>
            </label>
          </div>
        </div>
      </div>
      <div class="form-group">
        <div class="col-sm-offset-1 col-sm-10">
          <button type="submit" class="btn btn-default">更改</button>
        </div>
      </div>
    </form>   
  </div>
</div>
<!-- 弹出框 ,同时兼具提交修改用户分组的功能-->
<form action="" name="" method="post">
  <div class="modal fade" id="group-alter" tabindex="-1" role="dialog" 
    aria-labelledby="myModalLabel" aria-hidden="true">
    <input type="hidden" name="do" value="alterGroup"/><!-- 修改分组的隐藏域 -->
    <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" 
                aria-hidden="true">
            </button>
            <h4 class="modal-title" id="myModalLabel">
                编辑分组信息
            </h4>
          </div>
          <div class="modal-body">
            <div class="col-lg-12">
                <div class="form-group">
                  <label for="GROUP" class="col-sm-1 control-label">分组名称</label>
                  <div class="col-sm-5">
                    <input type="text" class="form-control" id="GROUP" name="GROUP" value="" >
                  </div>
                </div>
                <div class="form-group">
                  <label for="RATIO" class="col-sm-1 control-label">返点比率</label>
                  <div class="col-sm-1">
                    <input type="number" step="0.1" class="form-control" id="RATIO" name="RATIO" value=""><!-- 奖励下级居间人纯利返点的返点比率 -->
                  </div>%
                </div>
                <div class="form-group">
                  <label for="REWARD" class="col-sm-1 control-label">额外奖励</label>
                  <div class="col-sm-1">
                    <input type="number" step="0.1" class="form-control" id="REWARD" name="REWARD" value=""><!-- 奖励下级居间人纯利返点的返点比率 -->
                  </div>%
                </div>
                <div class="form-group">
                  <label for="GENRE" class="col-sm-1 control-label">分组类型</label>
                  <div class="col-sm-5">
                    <div class="radio" id="genre">
                    <!-- 自然人的下级只能是自然人，经济机构的下级只能是经纪机构 -->
                      <label class="radio-inline">
                        <input type="radio" name="GENRE" value="1" deafult>
                        <?=$agentGroupName?>
                      </label>
                      <label class="radio-inline">
                        <input type="radio" name="GENRE" value="2" >
                        <?=$tradeGroupName?>
                      </label>
                    </div>
                  </div>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" 
                data-dismiss="modal">关闭
            </button>
            <button type="submit" class="btn btn-primary">
                提交更改
            </button>
          </div>
      </div>
    </div>
  </div>
</form> 
