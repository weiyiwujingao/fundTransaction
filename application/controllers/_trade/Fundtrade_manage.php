<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 基金交易-用户基金买卖交易后台
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2016-12-12
 ****************************************************************/
class Fundtrade_manage extends MY_Controller 
{
	/* 传递到对应视图的数据 */
	private $_data     = array();

    public function __construct() 
    {
        parent::__construct();
		$this->load->model('_user/User_Interact');
		$this->_data['headerCss'] = '?v='.time();
        $this->_data['title'] = '买基金_基金交易_伯嘉基金网';
		$this->_data['nav'] = 'jjcp';
		$this->_data['sidebar'] = 'mjj';
		$this->_data['subnav'] = 'mjj';
		$this->_data['keywords'] = SEO_KEYWORDS;
        $this->_data['description'] = SEO_DESCRIPTION;
		
		//判断权限
		$this->User_Interact->passportCheck(1);
		
		$this->_data['userID'] = (int)$this->User_Interact->getUserID();
        $this->_data['nickName'] = filter_slashes($this->User_Interact->getNickName());
		
		$this->datadictionary = config_item('datadictionary');
		
		$this->_data['userInfo'] = $this->User_Model->getUserBaseByUserID($this->_data['userID']);
        if(!$this->_data['userInfo']){
            exit('用户信息错误！');
        }
		if(!$this->_data['userInfo']['HsAccount'] || !$this->_data['userInfo']['HsUserID']){
			arrayErrorJump(array('msg'=>'请绑定银行卡!','info'=>TRADE_ADD_BANK_URL));
		}

    }

   /**
     * 
     * 购买基金首页
	 *
	 * @return view
     *
    **/
    public function index() 
    { 	
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id  = $this->_data['userInfo']['HsUserID'];//用户编号

		if(!$trade_acco || !$client_id){
			arrayErrorJump(array('msg'=>'请绑定银行卡，再购买！','info'=>TRADE_ADD_BANK_URL));
		}		
		
		$this->_data['fundcode'] = filter_slashes($this->input->get('fundcode'));//代码
		$this->_data['money'] = filter_slashes($this->input->get('money'));//支付金额
		
		$this->_data['userDetail'] = $this->User_Model->getUserDetailByUserID($this->_data['userID']);
		 //拥有的银行卡数
	    $this->_data['userBankCount'] = $userBankCount = $this->User_Model->getUserBankCountByUserID($this->_data['userID']);
		
	    if($userBankCount<1){
	       arrayErrorJump(array('msg'=>'没有可用银行卡！','info'=>TRADE_ADD_BANK_URL));
	        exit;
	    }
		//获取银行卡信息
	    $userBank = $this->User_Model->getUserBankByUserID($this->_data['userID']);
		$this->load->model('_fix/fix_mdl');
		$this->_data['bank'] = $this->_formatBankAcco($this->fix_mdl->getAccoInfo(['client_id' => $client_id]),$userBank);
		if(!$this->_data['bank']){
			arrayErrorJump(array('msg'=>'没有可用银行卡！','info'=>TRADE_ADD_BANK_URL));
		}
		$this->_data['submiturl'] = base_url().'trade/preview.html';
		$this->_data['zjUserID'] = '';
		//推荐链接信息
		if(isset($_COOKIE['zjuserid']) && $_COOKIE['zjuserid'])
		$this->_data['zjUserID'] = $_COOKIE['zjuserid'];//项目标识
		if($this->_data['zjUserID'] && $this->_data['fundcode']){
			$this->_data['fundcodes'] = $this->input->get('fundcodes');//投资推荐基金代码
			$this->_data['investorid'] = $this->input->get('investorid');//投资者id
			//$this->_data['zjUserID'] = $this->input->get('zjUserID');//投资者id,当前登录中金用户
			$this->_data['investmentinviterid'] = $this->input->get('investmentinviterid');//投资推荐者id
			
			//购买页链接创建
			$tParam = array();
			//$tParam['proid']      = $this->_data['proid'];
			$tParam['fundcode']   = $this->_data['fundcode'];
			$tParam['fundcodes']  = $this->_data['fundcodes'];
			$tParam['investorid'] = $this->_data['investorid'];
			$tParam['zjUserID']   = $this->_data['zjUserID'];
			$tParam['investmentinviterid'] = $this->_data['investmentinviterid'];
			$sURL = http_build_query($tParam);
			$this->_data['submiturl'] = base_url().'trade/preview.html?' . $sURL;			
		}
		
		$this->load->view('_trade/index.html',$this->_data);
    }
	/**
     * 
     * 购买基金首页预览页面
	 *
	 * @return view
     *
    **/
	public function fundPurchasePreview()
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		
		$data = $fundmsg = array();
		
		$this->_data['paymentAmount'] = filter_slashes($this->input->post('money'));//支付金额
		$this->_data['capitalAmount'] = numToRmb($this->_data['paymentAmount']);//支付金额,中文大写
		$this->_data['fundcode'] = filter_slashes($this->input->post('fundid'));//基金代码
		$this->_data['paymentMethod'] = filter_slashes($this->input->post('bank'));//支付方式1:现金宝2:银行
		if($this->_data['paymentMethod']=='2')
			$this->_data['selecltbanks'] = filter_slashes($this->input->post('selecltbanks'));
		else
			$this->_data['selecltbanks'] = '现金宝支付';
		//根据基金代码重新查询基金信息
		$fundmsg = $this->tradeapi_manage_mdl->fundType($this->_data['fundcode']);
		foreach($fundmsg as $val){
			$this->_data['fundcodename'] = $val['name'];//基金名称
			$this->_data['chargemode'] = $val['chargemode'];//收费方式
		}
		//t($this->_data);
		$this->load->view('_trade/preview.html',$this->_data);
	}
	/**
     * 
     * 购买基金状态查看页面
	 *
	 * @return view
     *
    **/
	public function fundPurchaseState()
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		
		$data = $fundmsg = array();
		$this->_data['time']     = base64_decode($this->input->get('time'));//时间
	//	$this->_data['title']    = '买基金 - 受理成功 - 伯嘉基金网';		
		$this->_data['status']   = intval($this->input->get('status'));//状态				
		$this->_data['bankname'] = $this->input->get('bankname');//银行名称
		$this->_data['bankcode'] = base64_decode($this->input->get('bankcode'));//银行代码
		$this->_data['fundcode'] = base64_decode($this->input->get('fundcode'));//基金代码
		$this->_data['fundname'] = $this->input->get('fundname');//基金名称
		$this->_data['paymentAmount'] = base64_decode($this->input->get('money'));//支付金额		
		
		if($this->_data['status']==1){			
			$this->load->view('_trade/StateSuccese.html',$this->_data);
		}
		
		if($this->_data['status']==2){
			//$this->_data['title'] = '买基金 - 受理失败 - 伯嘉基金网';
			$this->_data['error_info'] = $this->input->get('error_info');//申购失败原因
			$this->load->view('_trade/StateFailse.html',$this->_data);
		}
				
		
	}
	/**
     * 
     * 购买基金首页
	 *
	 * @return view
     *
    **/
    public function subscription() 
    { 	
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id  = $this->_data['userInfo']['HsUserID'];//用户编号
		
		$this->_data['title'] = '买新基金_基金交易_伯嘉基金网';
		$this->_data['sidebar'] = 'mxjj';
		$this->_data['subnav'] = 'mxjj';

		if(!$trade_acco || !$client_id){
			arrayErrorJump(array('msg'=>'请绑定银行卡，再购买！','info'=>TRADE_ADD_BANK_URL));
		}
		$this->_data['userDetail'] = $this->User_Model->getUserDetailByUserID($this->_data['userID']);
		//拥有的银行卡数
	    $this->_data['userBankCount'] = $userBankCount = $this->User_Model->getUserBankCountByUserID($this->_data['userID']);
	    if($userBankCount<1){
	       arrayErrorJump(array('msg'=>'没有可用银行卡！','info'=>TRADE_ADD_BANK_URL));
	        exit;
	    }
		//获取银行卡信息
	    $userBank = $this->User_Model->getUserBankByUserID($this->_data['userID']);
		$this->load->model('_fix/fix_mdl');
		$this->_data['bank'] = $this->_formatBankAcco($this->fix_mdl->getAccoInfo(['client_id' => $client_id]),$userBank);
		if(!$this->_data['bank']){
			arrayErrorJump(array('msg'=>'没有可用银行卡！','info'=>TRADE_ADD_BANK_URL));
		}
		$this->_data['submiturl'] = base_url().'trade/subscriptionpreview.html';
		$this->_data['zjuserid'] = $_COOKIE['zjuserid'];//项目标识
		if($this->_data['zjUserID'] && $this->_data['fundcode']){
			$this->_data['fundcodes'] = $this->input->get('fundcodes');//投资推荐基金代码
			$this->_data['investorid'] = $this->input->get('investorid');//投资者id
			$this->_data['zjUserID'] = $this->input->get('zjUserID');//投资者id,当前登录中金用户
			$this->_data['investmentinviterid'] = $this->input->get('investmentinviterid');//投资推荐者id
			
			//购买页链接创建
			$tParam = array();
			//$tParam['proid']      = $this->_data['proid'];
			$tParam['fundcode']   = $this->_data['fundcode'];
			$tParam['fundcodes']   = $this->_data['fundcodes'];
			$tParam['investorid'] = $this->_data['investorid'];
			$tParam['zjUserID']   = $this->_data['zjUserID'];
			$tParam['investmentinviterid'] = $this->_data['investmentinviterid'];
			$sURL = http_build_query($tParam);
			$this->_data['submiturl'] = base_url().'trade/subscriptionpreview.html?' . $sURL;			
		}
		$this->load->view('_trade/subscription.html',$this->_data);
    }
	/**
     * 
     * 认购基金预览页面
	 *
	 * @return view
     *
    **/
	public function subscriptionPreview()
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		
		$this->_data['title'] = '买新基金_基金交易_伯嘉基金网';
		$this->_data['sidebar'] = 'mxjj';
		$this->_data['subnav'] = 'mxjj';
		
		$data = $fundmsg = array();
		
		$this->_data['paymentAmount'] = filter_slashes($this->input->post('money'));//支付金额
		$this->_data['capitalAmount'] = numToRmb($this->_data['paymentAmount']);//支付金额,中文大写
		$this->_data['fundcode'] = filter_slashes($this->input->post('fundid'));//基金代码
		$this->_data['paymentMethod'] = filter_slashes($this->input->post('bank'));//支付方式1:现金宝2:银行
		if($this->_data['paymentMethod']=='2')
			$this->_data['selecltbanks'] = filter_slashes($this->input->post('selecltbanks'));
		else
			$this->_data['selecltbanks'] = '现金宝支付';
		//根据基金代码重新查询基金信息
		$fundmsg = $this->tradeapi_manage_mdl->fundType($this->_data['fundcode']);
		foreach($fundmsg as $val){
			$this->_data['fundcodename'] = $val['name'];//基金名称
			$this->_data['chargemode'] = $val['chargemode'];//收费方式
		}
		
		$this->load->view('_trade/subscriptionpreview.html',$this->_data);
	}
	/**
     * 
     * 认购基金状态查看页面
	 *
	 * @return view
     *
    **/
	public function subscriptionState()
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		
		$data = $fundmsg = array();
		$this->_data['time']     = base64_decode($this->input->get('time'));//时间
		$this->_data['title'] = '买新基金_基金交易_伯嘉基金网';	
		$this->_data['sidebar'] = 'mxjj';
		$this->_data['subnav'] = 'mxjj';		
		$this->_data['status']   = intval($this->input->get('status'));//状态				
		$this->_data['bankname'] = $this->input->get('bankname');//银行名称
		$this->_data['bankcode'] = base64_decode($this->input->get('bankcode'));//银行代码
		$this->_data['fundcode'] = base64_decode($this->input->get('fundcode'));//基金代码
		$this->_data['fundname'] = $this->input->get('fundname');//基金名称
		$this->_data['paymentAmount'] = base64_decode($this->input->get('money'));//支付金额		
		
		if($this->_data['status']==1){			
			$this->load->view('_trade/subscriptionsuccese.html',$this->_data);
		}
		
		if($this->_data['status']==2){
			$this->_data['error_info'] = $this->input->get('error_info');//申购失败原因
			$this->load->view('_trade/subscriptionfailse.html',$this->_data);
		}
				
		
	}
	/**
     * 
     * 卖基金首页
	 *
	 * @return view
     *
    **/
    public function Sell_fund() 
    { 	
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$this->_data['title'] = '卖基金_基金交易_伯嘉基金网';
		$this->_data['sidebar'] = '_mjj';
		$this->_data['subnav'] = '_mjj';
		
        /* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		if(!$trade_acco || !$client_id){
			arrayErrorJump(array('msg'=>'请绑定银行卡!','info'=>TRADE_ADD_BANK_URL));
		}
		$applyqry = array();
		$i = $offset = 0;
		while($applyqrys = $this->tradeapi_manage_mdl->fundShareQry('','',50,$client_id,$offset)){
			$applyqry = array_merge($applyqry,$applyqrys);
			$i++;
			$offset = $i*50;
		}
        if($applyqry)
           $this->_data['data'] = $applyqry;
		$this->load->view('_trade/sellfund.html',$this->_data);
    }
	/**
     * 
     * 卖基金操作首页
	 *
	 * @return view
     *
    **/
    public function Sell() 
    { 	
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$this->_data['title'] = '卖基金_基金交易_伯嘉基金网';
		$this->_data['sidebar'] = '_mjj';
		$this->_data['subnav'] = '_mjj';
        /* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		if(!$trade_acco || !$client_id){
			arrayErrorJump(array('msg'=>'请绑定银行卡!','info'=>TRADE_ADD_BANK_URL));
		}
		$fundcode = $this->input->get('fundcode',true);
		if(!$fundcode){
			$this->Sell_fund();
		}
		 //申购请求
        $datas = $this->tradeapi_manage_mdl->fundShareQry($fundcode,'',50,$client_id);//600570
		$data = '';
        foreach ($datas as $v) {
            if ($v['current_share'] !=0) {
                $data['0'] = $v;
                break;
            }
        }
        $this->_data['minvalue'] = $this->tradeapi_manage_mdl->fundType($fundcode,'en_minshare');//600570
		//拥有的银行卡数
	    $this->_data['userBankCount'] = $userBankCount = $this->User_Model->getUserBankCountByUserID($this->_data['userID']);
	    if($userBankCount<1){
	       arrayErrorJump(array('msg'=>'没有可用银行卡！','info'=>TRADE_ADD_BANK_URL));
	        exit;
	    }
		//获取银行卡信息
	    $userBank = $this->User_Model->getUserBankByUserID($this->_data['userID']);
		$this->load->model('_fix/fix_mdl');
		$this->_data['bank'] = $this->_formatBankAcco($this->fix_mdl->getAccoInfo(['client_id' => $client_id]),$userBank);
		if(!$this->_data['bank']){
			arrayErrorJump(array('msg'=>'没有可用银行卡！','info'=>TRADE_ADD_BANK_URL));
		}
		
		$bankcode = $this->tradeapi_manage_mdl->fundBankAccountSearch('','',$client_id,'50');
		//$bankcode = array_unique($bankcode);
		if(!$bankcode){
			arrayErrorJump(array('msg'=>'没有可用银行卡！','info'=>TRADE_ADD_BANK_URL));
		}		
        if($data)
           $this->_data['data'] = $data;

		$this->load->view('_trade/sell.html',$this->_data);
    }
	/**
     * 
     * 卖基金操作预览页
	 *
	 * @return view
     *
    **/
    public function Sell_preview() 
    { 	
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$this->_data['title'] = '卖基金_基金交易_伯嘉基金网';
		$this->_data['sidebar'] = '_mjj';
		$this->_data['subnav'] = '_mjj';
        /* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		if(!$trade_acco || !$client_id){
			arrayErrorJump(array('msg'=>'请绑定银行卡!','info'=>TRADE_ADD_BANK_URL));
		}
		
		$this->_data['share_data'] = filter_slashes($this->input->post('share_data'));//卖出份额
		$this->_data['capitalAmount'] = numToRmb($this->_data['share_data']);//支付金额,中文大写
		$this->_data['fundcode'] = filter_slashes($this->input->post('fundcode'));//基金代码
		$this->_data['fundname'] = filter_slashes($this->input->post('fundname'));//基金代码
		$this->_data['selecltbanks'] = filter_slashes($this->input->post('selecltbanks'));
		$this->_data['isBack'] = intval($this->input->post('isBack'));//赎回方式
		$this->_data['share_type'] = filter_slashes($this->input->post('share_type'));//收费类型
		if($this->_data['isBack']==1)
			$this->_data['isBacktype'] = '连续赎回';
		else
			$this->_data['isBacktype'] = '取消赎回';

		$this->load->view('_trade/SellPreview.html',$this->_data);
    }
	/**
     * 
     * 购买基金状态查看页面
	 *
	 * @return view
     *
    **/
	public function fund_sell_state()
	{    
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$staus = '';
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		if(!$trade_acco || !$client_id){
			arrayErrorJump(array('msg'=>'请绑定银行卡!','info'=>TRADE_ADD_BANK_URL));
		}
		
		$this->_data['title']      = '卖基金_基金交易_伯嘉基金网';
		$this->_data['sidebar'] = '_mjj';
		$this->_data['subnav'] = '_mjj';
		
		$this->_data['status']  = intval($this->input->get('status'));//赎回方式
		$this->_data['time']    = base64_decode($this->input->get('time'));//时间
        $this->_data['fundcode']= base64_decode($this->input->get('fundcode'));//基金代码
        $this->_data['fundname']= filter_slashes($this->input->get('fundname'));//基金名称
        $this->_data['money']   = base64_decode($this->input->get('money'));//密码
		if($this->_data['status'] == 2){
			$this->_data['title']      = '卖基金 - 赎回失败 - 伯嘉基金网';
			$this->_data['error_info'] = filter_slashes($this->input->get('error_info'));//失败原因
		}
		if($this->_data['status']=='1')
			$this->load->view('_trade/sellSuccese.html',$this->_data);
		if($this->_data['status']=='2')
			$this->load->view('_trade/sellFailse.html',$this->_data);
        
	}
	/**
     * 
     * 基金撤单
	 *
	 * @return view
     *
    **/
	public function fund_revoke()
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$staus = '';
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		if(!$trade_acco || !$client_id){
			arrayErrorJump(array('msg'=>'请绑定银行卡！','info'=>TRADE_ADD_BANK_URL));
		}
		$this->_data['title'] = '撤单_基金交易_伯嘉基金网';
		$this->_data['nav'] = 'jjcp';
		$this->_data['subnav'] = 'cd';
		$this->_data['sidebar'] = 'jycd';
		$this->_data['data'] = '';
		//if(date('H')<'15')
        $i = 1;
        $data = [];
        while ($res = $this->tradeapi_manage_mdl->revokeTradeApplyQry('',$client_id,50,1,50*($i-1)+1)) {
            $data = array_merge($data, $res);
            $i ++;
        }
		//pre($data);
        $this->_data['data'] = $data;
		$this->load->view('_trade/revoke.html',$this->_data);		
	}
	/**
     * 
     * 基金撤单确认页面
	 *
	 * @return view
     *
    **/
	public function revoke($allot_no)
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		$allot_no = filter_slashes($allot_no);
		$staus = '';
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		if(!$trade_acco || !$client_id){
			arrayErrorJump(array('msg'=>'请绑定银行卡','info'=>TRADE_ADD_BANK_URL));
		}
		$this->_data['title'] = '撤单_基金交易_伯嘉基金网';
		$this->_data['nav'] = 'jjcp';
		$this->_data['subnav'] = 'cd';
		$this->_data['sidebar'] = 'jycd';
		$this->_data['data'] = '';
		
		$this->_data['allot_no'] = $allot_no;
		$this->_data['data'] = $this->tradeapi_manage_mdl->revokeCodeTradeApplyQry('',$client_id,$allot_no,1);
		//t($this->_data['data']);
		$this->load->view('_trade/revokeconfirm.html',$this->_data);		
	}
	/**
     * 
     * 基金撤单执行状态页面
	 *
	 * @return view
     *
    **/
	public function revoke_status()
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$staus = '';
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		if(!$trade_acco || !$client_id){
			arrayErrorJump(array('msg'=>'请绑定银行卡','info'=>TRADE_ADD_BANK_URL));
		}
		
		$this->_data['title'] = '撤单_基金交易_伯嘉基金网';
		$this->_data['nav'] = 'jjcp';
		$this->_data['subnav'] = 'cd';
		$this->_data['sidebar'] = 'jycd';
		
        $this->_data['fundcode'] = filter_slashes($this->input->post('fundcode'));//基金代码
        $this->_data['fundname'] = filter_slashes($this->input->post('fundname'));//基金名称
        $this->_data['password'] = filter_slashes($this->input->post('password'));//密码
		$this->_data['allot_no'] = intval($this->input->post('allotno'));//申请编号
		$this->_data['balance'] = intval($this->input->post('balance'));//拥有数额
		$this->_data['fund_busin_code'] = filter_slashes($this->input->post('fund_busin_code'));//业务名称
		
		//判断是否正确传参
        if( !$this->_data['fundcode'] || !$this->_data['password'] ||!$this->_data['allot_no']){
 
			$this->_data['staus'] = '请求参数缺失！';
			$staus = '2';
        }
		$this->_data['password'] = tradePswd($this->_data['password']);//密码
		if(!$staus){
			$this->_data['data'] = $this->tradeapi_manage_mdl->revokeCodeTradeApplyQry('',$client_id,$this->_data['allot_no'],1);
			if(!isset($this->_data['data']['0']['taconfirm_flag_code']) || $this->_data['data']['0']['taconfirm_flag_code']!=9 || !$this->_data['data']['0']['trade_acco'])
			{
				//$this->_data['title'] = '撤单 - 失败 - 伯嘉基金网';
				$this->_data['staus'] = '请求撤单条件不符合！';
				$staus = '2';
			}
			if(!$staus){
				$result = $this->tradeapi_manage_mdl->undotradeapplyTrade($this->_data['data']['0']['trade_acco'],$this->_data['allot_no'],$this->_data['password']);
				if(!$result){
					//$this->_data['title'] = '撤单 - 失败 - 伯嘉基金网';
					$this->_data['staus'] = '请求撤单申请失败！';
					$staus = '2';
				}
				if(isset($result['success_type']) && $result['success_type']==0){
					$this->_data['staus'] = '请求撤单申请成功！';
					//发送短信
					$sendcontent = config_item('fund_sms_content');
					$sendcontent = preg_replace(array('/基金名称/','/xx/'),array($this->_data['fundname'],$this->_data['fund_busin_code']),$sendcontent['11']); 
					$info = array('sign' => 0, 'channel' =>3,'content'=>$sendcontent,'mobile'=>$this->_data['userInfo']['Mobile']);
					sendSmsAction($info);
					$staus = '1';
				}else{
					//$this->_data['title'] = '撤单 - 失败 - 伯嘉基金网';
					$this->_data['staus'] = $result['error_info'];
					$staus = '2';
				}
			}
		}
		
		
		if($staus==1)
			$this->load->view('_trade/revokeSuccese.html',$this->_data);
		if($staus==2)
			$this->load->view('_trade/revokeFailse.html',$this->_data);
	}
	/**
     * 
     * 修改分红方式
	 *
	 * @return view
     *
    **/
	public function dividend_method()
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$staus = '';
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		if(!$trade_acco || !$client_id){
			arrayErrorJump(array('msg'=>'请绑定银行卡','info'=>TRADE_ADD_BANK_URL));
		}
		
		$this->_data['data'] = array();
		$this->_data['title'] = '修改分红方式_基金交易_伯嘉基金网';
		$this->_data['subnav'] = 'jjfhfs';
		$this->_data['sidebar'] = 'fhsz';
		
		$applyqry = array();
		$i = $offset = 0;
		while($applyqrys = $this->tradeapi_manage_mdl->shareQry(array('client_id'=>$client_id),50,$offset)){
			$applyqry = array_merge($applyqry,$applyqrys);
			$i++;
			$offset = $i*50;
		}
		$data = $this->tradeapi_manage_mdl->dividend_data($applyqry);
		if($data)
			$this->_data['data'] = $data;
		//t($data);
		$this->load->view('_trade/dividendMethod.html',$this->_data);
	}
	/**
     * 
     * 修改分红方式 预览
	 *
	 * @return view
     *
    **/
	public function dividend_method_confirm($fundcode)
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$staus = '';
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		if(!$trade_acco || !$client_id){
			arrayErrorJump(array('msg'=>'请绑定银行卡!','info'=>TRADE_ADD_BANK_URL));
		}
		$this->_data['data'] = array();
		$this->_data['title'] = '修改分红方式_基金交易_伯嘉基金网';
		$this->_data['subnav'] = 'jjfhfs';
		$this->_data['sidebar'] = 'fhsz';
		
		$this->_data['fund_code'] = $fundcode;
		$this->_data['fund_name'] = $this->tradeapi_manage_mdl->fundType($fundcode,'fund_name');
	
		//分红方式获取
        $share = $this->tradeapi_manage_mdl->fundShare($fundcode, $client_id);
		$this->_data['fenhongfs'] = empty($share) ? '0' : $share['share_type_code'];
		//t($this->_data['fenhongfs']);
		$this->load->view('_trade/dividendMethodConfirm.html',$this->_data);
	}
	/**
     * 
     * 修改分红方式 
	 *
	 * @return view
     *
    **/
	public function dividend_method_status()
	{
		
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$staus = '';
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//交易编号
		if(!$trade_acco || !$client_id){
			arrayErrorJump(array('msg'=>'请绑定银行卡!','info'=>TRADE_ADD_BANK_URL));
		}
		$this->_data['data'] = array();
		$this->_data['subnav'] = 'jjfhfs';
		$this->_data['sidebar'] = 'fhsz';
		
		$this->_data['title']    = '修改分红方式_基金交易_伯嘉基金网';
		$this->_data['status']   = intval($this->input->get('status'));//成功标识
		$this->_data['fundcode'] = base64_decode($this->input->get('fundcode'));//基金代码
        $this->_data['fundname'] = filter_slashes($this->input->get('fundname'));//基金名称
        $this->_data['autodata'] = intval($this->input->get('autodata'));//分红方式
		if($this->_data['status']==2){
			$this->_data['title'] = '修改分红方式_基金交易_伯嘉基金网';
			$this->_data['error_info'] = filter_slashes($this->input->get('error_info'));//基金名称
		}
		if($this->_data['status']==1){			
			$this->load->view('_trade/dividendSuccese.html',$this->_data);
		}						
		if($this->_data['status']==2){
			$this->load->view('_trade/dividendFailse.html',$this->_data);
		}
						
	}
	/**
     * 格式化银行卡信息（中国银行（**************0098））
     * @param $data
     * @return array
     */
    private function _formatBankAcco($data,$userBank=array())
    {
        //@error_log(date('Y-m-d H:i:s').print_r($data,true),'3','/var/tmp/trade.buyfunds.cn/fundtrade_manage/index/ceshi.log');
		if (!$data||!$userBank) return [];
        $return = $tmp = $bankcard = [];
		foreach($userBank as $k=>$v){
			$bankcard[] = $v['BankCard'];
		}
        foreach ($data as $key => $value) {
            if (in_array($value['bank_account'], $tmp) || !$value['bank_account'] || !in_array($value['bank_account'], $bankcard)) continue;
            $return[$key]['trade_acco'] = $value['trade_acco'];
			$return[$key]['bankname'] = $this->datadictionary['1601'][$value['bank_no']];
			$return[$key]['bankcode'] = bankCardReplace($value['bank_account']);
            $tmp[] = $value['bank_account'];
        }
        return $return;
    }
	  
}

/* End of file stockquote.php */
/* Location: ./application/controllers/stockquote.php */