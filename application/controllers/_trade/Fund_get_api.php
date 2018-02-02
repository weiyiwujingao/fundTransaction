<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 基金交易-用户基金买卖交易后台
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2016-12-12
 ****************************************************************/
class Fund_get_api extends MY_Controller 
{
	/* 传递到对应视图的数据 */
	private $_data     = array();

    public function __construct() 
    {
        parent::__construct();
    }

  /**
     * ajax入口
	 *
     * @param string $action 方法名称
	 * @return void
     */
    public function index() 
    {
        $func = $this->input->get('action', TRUE);
        if(TRUE === method_exists(__CLASS__, trim($func)))
		{
            $this->$func();
		}
		else
		{
			exit;
		}
	}
	/**
     *  获取基金信息接口
	 *
     * @param string $search 基金代码
     * @param string $money 申购金额
	 * @return json
     */
    public function faretype() 
    {
		$fundcode = $this->input->get('fundcode', TRUE);

		if(!$fundcode){
			$data = array('flag'=>'10001','msg'=>'参数提交错误！');
			returnJson($data);
		}
		
		$fundcodefare = $counter = array();
		//基金费率查询
		$fundcodefare = $this->tradeapi_manage_mdl->fundShareSearch($fundcode);
		if(!$fundcodefare){
			$data = '';
			returnJson($data);
		}
		$data = array('flag'=>'10000','info'=>$fundcodefare['ratio']);
		$data = sprintf("%.2f",$fundcodefare['ratio']).'%';
		
		returnJson($data);
		$counter['counterfare'] = $fundcodefare['ratio']*$money*0.01;
		
		//小于最低费率 取最低费率手续费
		if($counter['counterfare'] < $fundcodefare['min_fare'])
			$counter['counterfare'] = $fundcodefare['min_fare'];
		//大于最低费率 取最高费率手续费
		if($counter['counterfare'] > $fundcodefare['max_fare'])
			$counter['counterfare'] = $fundcodefare['max_fare'];
		
		//费率
		$counter['rate'] = $counter['counterfare']/$money*0.01?sprintf("%.2f", $counter['counterfare']/$money*0.01).'%':'0费率';
		//手续费
		$counter['counterfare'] = sprintf("%.2f", $counter['counterfare']);
		
		$data = array('flag'=>'10000','info'=>$counter);
		
		returnJson($data);

	}
	/**
     *  获取基金起购价
	 *
     * @param string $search 基金代码
     * @param string $money 申购金额
	 * @return json
     */
    public function faremin() 
    {
		$search = $this->input->get('fundcode', TRUE);
		if(!$search){
			$data = array('flag'=>'10001','msg'=>'参数提交错误！');
			returnJson($data);
		}
		
		$fundcodemsg = array();
		//最新基金行情查询
		$fundcodemsg = $this->tradeapi_manage_mdl->fundType($search);
		if(!$fundcodemsg){
			$data = array('flag'=>'10005','msg'=>'该基金不存在！');
			returnJson($data);
		}
		//分红方式获取
		$fundcodesharemsg = $this->tradeapi_manage_mdl->fundShare($search);
		if(!$fundcodesharemsg){
			$data = array('flag'=>'10005','msg'=>'该基金信息不全！');
			returnJson($data);
		}
		//交易限制查询 最低最高金额限制 
		$fundcoderestmsg = $this->tradeapi_manage_mdl->tradeRestrictions($search,$fundcodemsg[$search]['share_type']);
		if(!$fundcoderestmsg){
			$data = array('flag'=>'10005','msg'=>'该基金查询限制信息不全！');
			returnJson($data);
		}
		//合并基金分类信息
		foreach($fundcodemsg as $k=>$v){
			$fundcodemsg[$k]['dividendmethod'] = $fundcodesharemsg['share_type'];
			$fundcodemsg[$k]['c_flag'] = $fundcodesharemsg['c_flag'];
			
			$fundcodemsg[$k]['max_value'] = strlen($fundcoderestmsg['max_value'])>8 ? $fundcoderestmsg['max_value']:$fundcoderestmsg['max_value'];
			//$fundcodemsg[$k]['min_value'] = $fundcodesharemsg['c_flag'] ? $fundcoderestmsg['second_min']:$fundcoderestmsg['min_value'];
			$fundcodemsg[$k]['min_value'] = $fundcodesharemsg['c_flag'] ? $fundcoderestmsg['min_value']:$fundcoderestmsg['min_value'];
		}
		$data = array('flag'=>'10000','info'=>$fundcodemsg);
		returnJson($data);

	}
  
}

/* End of file stockquote.php */
/* Location: ./application/controllers/stockquote.php */