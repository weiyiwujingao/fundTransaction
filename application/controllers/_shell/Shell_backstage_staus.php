<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/****************************************************************
 * 2015财经排行榜-定时脚本
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:linfeng $addtime:2015-10-27
 ****************************************************************/
class Shell_backstage_staus extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
       // $this->access_token = getAccessToken(__METHOD__);
		//$this->datadictionary = config_item('datadictionary');	
        //$this->load->model('_cron/cron_mdl');
    }
	 /**
     * 
     * 申购，赎回，转换，普通定投 状态更细
     * 
     */
    public function runstaus()
    {
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$this->load->model('_user/Fund_Model');
		$where =  " Status in('2','3','4','5','9') ";
		$datas = $data = [];
		$i='0';
		while($data = $this->Fund_Model->_getUserTrade($where,'*',"{$i},100",'id desc')){
			$datas = array_merge($datas,$data);
			$i++;
		}
		if(!$datas){
			exit('暂无审核中的数据');
			unset($data,$datas);
		} 
		//t($datas);
		foreach($datas as $k=>$v){
			if(!$v['allot_no'])continue;
			$applyqry = $this->tradeapi_manage_mdl->trade_apply_qry(array('allot_no'=>$v['allot_no']));
			if(!$applyqry) continue;
			//t($v);
			$param = array('Status'=>$applyqry['data']['0']['taconfirm_flag']);
			$where = array("allot_no"=>"{$v['allot_no']}","UserID"=>"{$v['UserID']}");			
			if($applyqry['data']['0']['taconfirm_flag'] != $v['Status']){
				$r = $this->Fund_Model->updateManagement($param, $where);
			}
		}
    }
	 /**
     * 
     * 改变分红方式 状态更细
     * 
     */
    public function runBonusStaus()
    {
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$this->load->model('_user/Fund_Model');
		$where =  " Status in('2','3','4','5','9') ";
		$datas = $data = [];
		$i='0';
		while($data = $this->Fund_Model->_getUserBonus($where,'*',"{$i},100",'BUID desc')){
			$datas = array_merge($datas,$data);
			$i++;
		}
		if(!$datas){
			exit('暂无审核中的数据');
			unset($data,$datas);
		} 
		foreach($datas as $k=>$v){
			if(!$v['allot_no'])continue;
			$applyqry = $this->tradeapi_manage_mdl->trade_apply_qry(array('allot_no'=>$v['allot_no'],'capital_mode'=>''));
			if(!$applyqry) exit('无法查询到交易信息');
			$param = array('Status'=>$applyqry['data']['0']['taconfirm_flag']);
			$where = array("allot_no"=>"{$v['allot_no']}","UserID"=>"{$v['UserID']}");			
			if($applyqry['data']['0']['taconfirm_flag'] != $v['Status']){
				$r = $this->Fund_Model->updateBonus($param, $where);
			}
		}
    }
}

/* End of file shell_manage.php */
/* Location: ./application/controllers/_shell/shell_manage.php */