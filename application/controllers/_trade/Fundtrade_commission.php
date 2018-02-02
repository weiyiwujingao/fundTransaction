<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 基金交易-中金用户中心 交易入口接口
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 BUYFUNDS Inc. (https://trade.buyfunds.cn)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2017-02-22
 ****************************************************************/
class Fundtrade_commission extends MY_Controller 
{
	/* 传递到对应视图的数据 */
	private $_data     = array();
	private $cache;

    public function __construct() 
    {
        parent::__construct();
        
        $this->buydomain = '.'.WEB_DOMAIN_NAME;
		$this->load->helper('api');
        $this->load->library('Buyfunds_mem',NULL,'buyfunds_mem');
        $this->cache = $this->buyfunds_mem;
		$this->load->model('_user/Recommend_Model');
		$this->load->model('_user/User_Interact');

    }
	 /**
     * 
     * 推荐购买 
	 *
	 * @return view
     *
    **/
    public function trade() 
    { 
		//日志文件
		$logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$interval = 86400*30; //链接有效期一个月
		$key = ENCODE_KEY;
		$operation = 'DECODE';
		$tcparam  = authcode(base64_decode($this->input->get('s')), $operation, $key);
		//$zjUserID = $this->input->get('zjUserID');
		//pre($tcparam);
		//t($zjUserID);
		if(!isset($tcparam)){
			exit('错误链接!');
		}
		$paramarr = explode(',',$tcparam);
		if(!is_array($paramarr)||!$paramarr){
			exit('错误的参数！');
		}
//pre($paramarr);
		$param = array();
		if(!isset($paramarr['0'])) $paramarr['0'] = '';
		if(!isset($paramarr['1'])){
			$paramarr['1'] = $paramarr['0'];
			$paramarr['0'] = '';
		} 
		$param['investmentinviterid'] = intval($paramarr['0']); //推荐投资者id
		$param['investorid']  = intval($paramarr['1']); //投资者
		$param['fundcode']   = $this->input->get('fundcode'); //基金代码
		$param['money']   = $this->input->get('money'); //购买金额
		$param['proid']      = '102'; //项目id 1-基金超市	
//t($param);
		//请求源头是否合法
		$refererUrl = $this->input->server('HTTP_REFERER', true);
		if(!$refererUrl){
		  // exit('错误请求');
		}
		$domain = get_host_domain($refererUrl);
		if(!is_allowed_login_domain($domain) ){
		   // exit('请求错误');
		}
		if(!$param['proid'] || !$param['fundcode'] || (!isset($param['investmentinviterid']) && !$param['investorid'])){
		    exit('链接错误！') ;
		}
		//查看基金状态 是否为新基金 新基金费率为0
		$newcodeflag = $this->tradeapi_manage_mdl->fundType($param['fundcode'],'fund_status');
		//$key = RECOMMEND_KEY;
		$operation = 'ENCODE';
		//购买页链接创建
		$tParam = array();
		if($param['investorid']){
			$tParam['proid']      = base64_encode(authcode($param['proid'], $operation, $key, $interval));
			$tParam['investorid'] = base64_encode(authcode($param['investorid'], $operation, $key, $interval));
			$tParam['investmentinviterid'] = base64_encode(authcode($param['investmentinviterid'], $operation, $key, $interval
			));
			$tParam['fundcodes']  = $param['fundcode'];	
			setcookie('fundcodes',$param['fundcode'], time()+3600*24*30,'/',$this->buydomain );
		}
		$tParam['fundcode']   = $param['fundcode'];
		//$tParam['zjUserID']   = $zjUserID;
		if($param['money'])
			$tParam['money']  = $param['money'];	
		//t($tParam);
		$sURL = http_build_query($tParam);
		if($newcodeflag == '1'){
			$loUrl =  TRADE_WEB_URL . '/trade/subscription.html?' . $sURL;
			cnfol_location($loUrl);
		}else{
			$loUrl =  TRADE_WEB_URL . '/trade/fundtrade.html?' . $sURL;
			cnfol_location($loUrl);
		}		
	} 
	 /**
     * 
     * 基金超市推荐注册,中金账号登录绑定回调页面
	 *
	 * @return view
     *
    **/
    public function loginbing() 
    { 	
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$param = array();
		$param['fundcode'] = $this->input->get('fundcode'); //基金代码
		$param['money']    = $this->input->get('money'); //购买金额
		$param['tid']      = $tid = $this->input->get('tid');
		$param['userid']   = $zjUserID = (int)$this->input->get('userid'); //中金用户ID
		$param['keystr']   = $keystr = $this->input->get('keystr');
		$param['key']      = $key = $this->input->get('key');
		$sparam            = $this->input->get('s');//推荐投资参数
		
		$tid2 = time();
		//pre($sparam);
		$record = '';
		//请求源头是否合法
		$refererUrl = $this->input->server('HTTP_REFERER', true);
		if(!$refererUrl){
		    exit('错误请求');
		}
        //pre($refererUrl);
		$domain = get_host_domain($refererUrl);
		if(!is_allowed_login_domain($domain) ){
		    exit('请求错误');
		}
		if(!$param['fundcode']){
			exit('错误的参数！');
		}
		$vKey = getMysign($param, ENCODE_KEY);
		if($keystr != $vKey){
		    exit('链接错误');
		}
		$key = ENCODE_KEY;
		$operation = 'DECODE';
		$tcparam  = authcode(base64_decode($this->input->get('s')), $operation, $key);
		
		$paramarr = explode(',',$tcparam);
		
		$param['inviterid']  = $recid = filter_html($paramarr['0']); //注册推荐人
		$param['investorid'] = $zjUserID;
		if(!$param['inviterid'])
			$recid = $zjUserID;
		if($param['inviterid']==$param['investorid'])	
			$param['inviterid'] = '';
		$param['proid']      = '102'; //项目id 1-基金超市	
		
		if(!isset($param['inviterid']) || !$param['proid'] || !$param['investorid']){
		    exit('错误链接');
		}
		
		$r = $this->User_Interact->ajaxPassportCheck();
		if($r['flag'] == '10000'){ //已登录伯嘉帐号
			$loUserID = (int)$this->User_Interact->getUserID();
		}else{
			$tUrl = TRADE_LOGIN_URL . '?rt='. urlencode(base64_encode(cur_page_url()));
			cnfol_location($tUrl);
			exit;
		}
		
		if($record != '1'){
			$where =  " proid='{$param['proid']}' and investorid='{$zjUserID}'";
			$data = $this->Recommend_Model->_getUserRelation($where,'*',"1",'id desc');
			logs('|-没有伯嘉账号查询-|' . print_r($data, true), $logFile);
			if(empty($data)){
				 $pParam = array(
					'userID'     => $loUserID,
					'inviterid'  => $param['inviterid'],//注册邀请人
					'investorid' => $zjUserID,//注册人
					'time'       => time(),
					'proid'      => $param['proid'],
					'status'     => 1
				);
				logs('|-没有伯嘉账号入参-|' . print_r($pParam, true), $logFile);
				$this->Recommend_Model->insertUserRelation($pParam);
			}
			//调中金金融超市绑定用户接口
			$fsParam = array(
				'action' => 'setuserbinding', 
				'instid' => INSTID, //机构ID，伯嘉固定1
				'proid' => PROID, //项目ID，伯嘉固定1
				'userid' => $zjUserID, //中金用户ID
				'recid' => $recid, //注册推荐人
				'bindtype' => 2, //1:绑定 2:开户
				't' => time(),
			);
			$fsParam['secretkey'] = getMysign($fsParam, ENCODE_KEY);
			logs('|-fsParam-|' . print_r($fsParam, true), $logFile);
			$cgUrl = FINANCIAL_SUPERMARKET_USERBIND_URL . '?' . http_build_query($fsParam);
			logs('|-cgUrl-|' . $cgUrl, $logFile);
			$fsRs = curl_get($cgUrl);
			logs('|-fsRs-|' . print_r($fsRs, true), $logFile);
		}	
		//查看基金状态 是否为新基金 新基金费率为0
		$newcodeflag = $this->tradeapi_manage_mdl->fundType($param['fundcode'],'fund_status');
		$operation = 'ENCODE';
		//购买页链接创建
		$tParam = array();
		$tParam['fundcode'] = $param['fundcode'];
		if($param['money']){
		    $tParam['money'] = filter_html($param['money']);
		}
		if($sparam){
			$tParam['s'] = filter_html($sparam);
		}
		$sURL = http_build_query($tParam);
		$loUrl =  TRADE_WEB_URL . '/trade/fundtrade.html?' . $sURL;
		cnfol_location($loUrl);		
    }
	
	/**
     *  加密公式
     * 
     */
	private function keydata($inviterid,$investmentinviterid,$investorid,$time){
		$key = md5(md5(intval($inviterid)).md5(intval($investmentinviterid)).md5(intval($investorid)).$time);
		return $key;
	}

}

/* End of file stockquote.php */
/* Location: ./application/controllers/stockquote.php */