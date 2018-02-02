<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 基金交易-用户基金买卖交易后台
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2016-12-12
 ****************************************************************/
class Fund_ajax_manage extends MY_Controller 
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
        $this->load->model('_user/User_Interact');
		 //是否登录
        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }

		$this->_data['userID'] = (int)$this->User_Interact->getUserID();
		$this->_data['userInfo'] = $this->User_Model->getUserBaseByUserID($this->_data['userID']);
        if(!$this->_data['userInfo']){
			$data = array('flag'=>'10001','msg'=>'用户信息错误！');
			returnJsonStr($data);
        }
		$func = $this->input->post('action', TRUE);
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
     *  手动输入代码自动排序函数
	 *
     * @param string $search 输入框查询字符串
     * @param string $limit 限定排序数量
	 * @return void
     */
    public function Searchcode() 
    {
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$search = $this->input->post('search', TRUE);
        $limit = intval($this->input->post('limit', TRUE));
		if(!$limit){
			$data = array('flag'=>'10001','msg'=>'参数提交错误！');
			returnJsonStr($data);
		}
		$search = $search?$search:'0';
		$keys = $search.'search'.$limit;
		//获取排序结果
		$codes = codeSort($keys,$search,$limit);
		if($codes){
			$data = array('flag'=>'10000','info'=>$codes);
			returnJsonStr($data);
		}else{
			$data = array('flag'=>'10004','msg'=>'接口异常!');
			returnJsonStr($data);
		}
	}
	/**
     *  获取基金信息接口
	 *
     * @param string $search 基金代码
	 * @return json
     */
    public function Codetype() 
    {
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		//判断是否为新基金的标识
		$isnewfund ='';
		$search = $this->input->post('search', TRUE);
		$data = array('flag'=>'10001','msg'=>'提示信息');
		if(!$search){
			$data = array('flag'=>'10001','msg'=>'参数提交错误！');
			returnJson($data);
		}
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id  = $this->_data['userInfo']['HsUserID'];//用户编号
		if(!$trade_acco || !$client_id){
			$data = array('flag'=>'10002','msg'=>'非绑卡用户！');
			returnJson($data);
		}
		
		$fundcodemsg = array();
		//最新基金行情查询
		$fundcodemsg = $this->tradeapi_manage_mdl->fundType($search);
		$isnewfund = $this->tradeapi_manage_mdl->fundType($search,'fund_status');
		if(!in_array($isnewfund,array('0','1'))){
			$data = array('flag'=>'10005','msg'=>'该基金不能申购或者认购！');
			returnJson($data);
		}
		//为1是新基金
		if($isnewfund!=1)$isnewfund = '';
		$fund_busin_code = ($isnewfund==1)?'020':'022';
		//returnJson($fundcodemsg);
		if(!$fundcodemsg){
			$data = array('flag'=>'10005','msg'=>'该基金不存在！');
			returnJson($data);
		}
		//是否有份额获取
		$fundcodesharemsg = $this->tradeapi_manage_mdl->fundShare($search,$client_id);//
		//分红方式获取
		$fundcodefenhong = $this->tradeapi_manage_mdl->fundinfo_qry(array('trade_acco'=>$trade_acco,"fund_code" => $search));
		//returnJson($fundcodesharemsg);
		if(!$fundcodefenhong){
			$data = array('flag'=>'10005','msg'=>'该基金信息不全！');
			returnJson($data);
		}
		//交易限制查询 最低最高金额限制 
		$fundcoderestmsg = $this->tradeapi_manage_mdl->tradeRestrictions($search,$fundcodemsg[$search]['share_type'],$client_id,$fund_busin_code);
		if(!$fundcoderestmsg){
			$data = array('flag'=>'10005','msg'=>'该基金查询限制信息不全！');
			returnJson($data);
		}
		//合并基金分类信息
		foreach($fundcodemsg as $k=>$v){
			$fundcodemsg[$k]['dividendmethod'] = $fundcodefenhong['0']['auto_buy'];
			$fundcodemsg[$k]['c_flag'] = $fundcodesharemsg?'1':'';
			
			$fundcodemsg[$k]['max_value'] = strlen($fundcoderestmsg['max_value'])>13 ?'': $fundcoderestmsg['max_value'];
			$fundcodemsg[$k]['isnewfund'] = $isnewfund;
			$fundcodemsg[$k]['min_value'] = $fundcodesharemsg ? $fundcoderestmsg['second_min']:$fundcoderestmsg['min_value'];
			//$fundcodemsg[$k]['min_value'] = $fundcodesharemsg ? $fundcoderestmsg['min_value']:$fundcoderestmsg['min_value'];
		}
		$data = array('flag'=>'10000','info'=>$fundcodemsg);
		returnJson($data);

	}
	/**
     *  获取基金信息接口
	 *
     * @param string $search 基金代码
     * @param string $money 申购金额
	 * @return json
     */
    public function fareType() 
    {
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$money = (float)$this->input->post('money', TRUE);
		$fundcode = $this->input->post('fundcode', TRUE);

		if(!$money || !$fundcode){
			$data = array('flag'=>'10001','msg'=>'参数提交错误！');
			returnJson($data);
		}
		
		$fundcodefare = $counter = array();
		//基金费率查询
		$fundcodefare = $this->tradeapi_manage_mdl->fundShareSearch($fundcode,'022');
		if(!$fundcodefare){
			$data = array('flag'=>'10005','msg'=>'该基金接口异常！');
			returnJson($data);
		}

		$counter['counterfare'] = $fundcodefare['ratio']*$money;
		
		//小于最低费率 取最低费率手续费
		if($counter['counterfare'] < $fundcodefare['min_fare'])
			$counter['counterfare'] = $fundcodefare['min_fare'];
		//大于最低费率 取最高费率手续费
		if($counter['counterfare'] > $fundcodefare['max_fare'])
			$counter['counterfare'] = $fundcodefare['max_fare'];
		
		//查看基金状态 是否为新基金 新基金费率为0
		$newcodeflag = $this->tradeapi_manage_mdl->fundType($fundcode,'fund_status');
		if($newcodeflag=='1'){
			$counter['counterfare'] = '0';
		}
		//费率
		$counter['rate'] = $counter['counterfare']/$money*100?sprintf("%.2f", $counter['counterfare']/$money*100).'%':'0费率';
		//手续费
		$counter['counterfare'] = sprintf("%.2f", $counter['counterfare']);
		
		$data = array('flag'=>'10000','info'=>$counter);
		
		returnJson($data);

	}
	/**
     *  获取基金信息接口
	 *
     * @param string $search 基金代码
     * @param string $money 申购金额
	 * @return json
     */
    public function trade_apply_search() 
    {
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);

		//防刷3秒， 10次
//        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 2);
		
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//用户编号
		
		$search = $applyqry = array();
		$search['fund_busin_code'] = str_pad(intval($this->input->post('bussinesstype', TRUE)),3,"0",STR_PAD_LEFT);//业务类型
		$search['taconfirm_flag']  = intval($this->input->post('status', TRUE));//确认标识
		$search['sort_direction']  = 1;//排序方式倒叙
		$search['begin_date']      = str_replace('-','',$this->input->post('startdate', TRUE));//开始时间
		$search['end_date']        = str_replace('-','',$this->input->post('enddate', TRUE));//结束时间
		if($search['end_date']==date('Ymd'))
			$search['end_date'] = '';
		
		$limit = intval($this->input->post('limit', TRUE));
		$page  = intval($this->input->post('page', TRUE));
		
		//设置缓存key值
		$key = md5('trade_apply_search_buy_' . $search['begin_date'] . '_' . $search['end_date'] .'_' . $client_id.'_'.$search['fund_busin_code'].'_'.$search['taconfirm_flag'].'_' .$limit.'_' .$page);
		//设置缓存时间
		$time = ($page-1)*100+10;
		//非首页 获取恒生接口重新统计
		$record = '1';
		//if($page>1) $record = '0';
		
		//查看全部默认不传值
		if($search['fund_busin_code']==1000)
			$search['fund_busin_code'] = '';
		if($search['taconfirm_flag']==1000)
			$search['taconfirm_flag'] = '';
		
		$offset = ($limit*($page-1))+1;//分页
		$search['capital_mode'] = '';
		
		if( !$limit || !$page || $limit>100){
			$data = array('flag'=>'10001','msg'=>'参数提交错误！');
			returnJsonStr($data);
		}
		$this->load->library('buyfunds_mem', 'memcache');
        $applyqry = $this->buyfunds_mem->get($key);
        $applyqry = '';
		//returnJsonStr($search['fund_busin_code']);
		if (!$applyqry) {
			switch ($search['fund_busin_code'])
			{
				case '022':
					//筛选买入【认购、申购、预约认购、预约申购】
					$codes = ['020', '021', '022', '023','039'];
					$list = [];
					$total = 0;
					foreach ($codes as $code) {
						$search['fund_busin_code'] = $code;
						$i = 1;
						while ($res = $this->tradeapi_manage_mdl->trade_apply_qry($search, '', $client_id, 50, $record, ($i - 1) * 50 + 1)) {
							$list = array_merge($list, $res['list']);
							$total = $total + $res['total'];
							$i++;
						}
					}
					$applyqry = ['list' => $list, 'total' => $total];
					$tmp = [];
					foreach ($applyqry['list'] as $item) {
						$tmp[] = $item['time'];
					}
					array_multisort($tmp, SORT_DESC, $applyqry['list']);
					$applyqry['list'] = array_slice($applyqry['list'], $offset-1, $limit);
					break;
				case '090':
					//筛选定投【定期定额申购协议签订、定期定额申购协议修改、定期定额申购协议取消】
					$codes = ['090', '988', '093'];
					$list = [];
					$total = 0;
					foreach ($codes as $code) {
						$search['fund_busin_code'] = $code;
						$i = 1;
						while ($res = $this->tradeapi_manage_mdl->trade_apply_qry($search, '', $client_id, 50, $record, ($i - 1) * 50 + 1)) {
							$list = array_merge($list, $res['list']);
							$total = $total + $res['total'];
							$i++;
						}
					}
					$applyqry = ['list' => $list, 'total' => $total];
					$tmp = [];
					foreach ($applyqry['list'] as $item) {
						$tmp[] = $item['time'];
					}
					array_multisort($tmp, SORT_DESC, $applyqry['list']);
					$applyqry['list'] = array_slice($applyqry['list'], $offset-1, $limit);
					break;
				default:
					$applyqry = $this->tradeapi_manage_mdl->trade_apply_qry($search, '', $client_id, $limit, $record, $offset);
			}
			if($applyqry){
				$this->buyfunds_mem->set($key, $applyqry, $time);
			}else{
				$data = array('flag'=>'10005','msg'=>'该基金接口异常！');
				returnJsonStr($data);
			}
				
		}
		$this->load->helper('html');
		$data['data']     = $applyqry['list'];
		$data['pagelist'] = pageajax($applyqry['total'],$page,$limit);
		
		$data = array('flag'=>'10000','info'=>$data);		
		returnJsonStr($data);

	}
	/**
     * 
     * 购买基金操作
	 *
	 * @return view
     *
    **/
	public function applyPurchase()
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		//防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 2);
		
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//用户编号
		if(!$trade_acco || !$client_id ){
			$result = array('flag'=>'10001','msg'=>'非法提交用户！');
			returnJson($result);
		}
			
		$url = base_url().'trade/state.html';
		
		$data = $fundmsg = array();
		$data['paymentAmount'] = filter_slashes($this->input->post('money'));//支付金额
		$data['bankname'] = filter_slashes($this->input->post('bankname'));//银行名称
		$data['fundcode'] = filter_slashes($this->input->post('fundcode'));//基金代码
		$data['fundname'] = filter_slashes($this->input->post('fundname'));//基金代码
        $data['password'] = filter_slashes($this->input->post('password'));//密码
		
		if(!$data['paymentAmount'] || !$data['bankname'] || !$data['fundcode'] || !$data['password']){
			$result = array('flag'=>'10001','msg'=>'参数提交错误！');
			returnJson($result);
		}	        
        $data['password'] = tradePswd($data['password']);
		$data['bankname'] = trim(preg_replace('/\（[^\）]*\）/i', '',$data['bankname']));
		$bank_no = $this->tradeapi_manage_mdl->fundConfigSearch($data['bankname'],'1601');
		$bankcode = $this->tradeapi_manage_mdl->fundBankAccountSearch($bank_no,'',$client_id);
		//returnJson($bankcode);
		if(!isset($bankcode['bank_account']) || empty($bankcode['bank_account'])){
			$result = array('flag'=>'10002','msg'=>'接口异常！');
			returnJson($result);
		}
		$bankcodes = $bankcode['bank_account'];
		$bankcode = substr($bankcode['bank_account'],-4);
		
		
		
        //申购请求
		$applydata = $this->tradeapi_manage_mdl->fundAllotTrade($bank_no,$client_id,$data['fundcode'],$data['paymentAmount'],$data['password']);
		if(!$applydata){
			$time = date('Y-m-d H:i:s',time());
			$url = $url.'?status=2&time='.base64_encode($time).'&money='.base64_encode($data['paymentAmount']).'&bankname='.$data['bankname'].'&fundcode='.base64_encode($data['fundcode']).'&bankcode='.base64_encode($bankcode).'&fundname='.$data['fundname'].'&error_info=该基金接口异常！';
			
			$result = array('flag'=>'10000','msg'=>'该基金接口异常！','url'=>$url);
			returnJsonStr($result);
		}
		if($applydata['success_type'] == '0'){
			$time = date('Y-m-d H:i:s',strtotime($applydata['apply_date'].' '.$applydata['apply_time']));
			$returndata['money']= $applydata['balance'];
			$url = $url.'?status=1&time='.base64_encode($time).'&money='.base64_encode($applydata['balance']).'&bankname='.$data['bankname'].'&fundcode='.base64_encode($data['fundcode']).'&bankcode='.base64_encode($bankcode).'&fundname='.$data['fundname'];
			
			$result = array('flag'=>'10000','msg'=>'申购成功！','url'=>$url);
			//发送短信
			$sendcontent = config_item('fund_sms_content');
			$sendcontent = preg_replace(array('/基金名称/','/基金代码/','/数额xxx/'),array($data['fundname'],$data['fundcode'],'数额'.$applydata['balance'].'元'),$sendcontent['5']); 
			$info = array('sign' => 0, 'channel' =>3,'content'=>$sendcontent,'mobile'=>$this->_data['userInfo']['Mobile']);
			sendSmsAction($info);
			//插入数据库
			$this->load->model('_user/Fund_Model');
			$this->Fund_Model->insertManagement(array('UserID'=>$this->_data['userInfo']['UserID'],'Type'=>'1','Payway'=>'2','BankName'=>$data['bankname'],'BankCode'=>$bankcodes,'TotalMoney'=>(int)sprintf("%.0f", $applydata['balance']*100),'Status'=>'9','SubDate'=>date('Y-m-d H:i:s'),'allot_no'=>$applydata['allot_no']));
			//查看基金类型 为货币基金排除
			$ofund_type = $this->tradeapi_manage_mdl->fundType($data['fundcode'],'ofund_type');
			if($ofund_type!=2)
				$this->iscomssion($data['fundcode'],$applydata['allot_no'],$this->_data['userInfo']['UserID']);
			returnJsonStr($result);
		}else{
			$time = date('Y-m-d H:i:s',strtotime($applydata['apply_date'].' '.$applydata['apply_time']));
			$url = $url.'?status=2&time='.base64_encode($time).'&money='.base64_encode($data['paymentAmount']).'&bankname='.$data['bankname'].'&fundcode='.base64_encode($data['fundcode']).'&bankcode='.base64_encode($bankcode).'&fundname='.$data['fundname'].'&error_info='.$applydata['error_info'];
			
			$result = array('flag'=>'10000','msg'=>'申购失败！','url'=>$url);
			returnJsonStr($result);
		}

	}
	/**
     * 
     * 认购基金操作
	 *
	 * @return view
     *
    **/
	public function subscription()
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 2);

        /* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//用户编号

        $url = base_url().'trade/subscriptionstate.html';
        $data = $fundmsg = array();
        $data['paymentAmount'] = filter_slashes($this->input->post('money'));//支付金额
        $data['bankname'] = filter_slashes($this->input->post('bankname'));//银行名称
        $data['fundcode'] = filter_slashes($this->input->post('fundcode'));//基金代码
        $data['fundname'] = filter_slashes($this->input->post('fundname'));//基金代码
        $data['password'] = filter_slashes($this->input->post('password'));//密码

        if(!$data['paymentAmount'] || !$data['bankname'] || !$data['fundcode'] || !$data['password']){
            $result = array('flag'=>'10001','msg'=>'参数提交错误！');
            returnJson($result);
        }
        $data['password'] = tradePswd($data['password']);
		$data['bankname'] = trim(preg_replace('/\（[^\）]*\）/i', '',$data['bankname']));
		$bank_no = $this->tradeapi_manage_mdl->fundConfigSearch($data['bankname'],'1601');
		$bankcode = $this->tradeapi_manage_mdl->fundBankAccountSearch($bank_no,'',$client_id);
		if(!isset($bankcode['bank_account'])){
			$result = array('flag'=>'10002','msg'=>'接口异常！');
			returnJson($result);
		}
		$bankcodes = $bankcode['bank_account'];
		$bankcode = substr($bankcode['bank_account'],-4);

        //认购购请求
        $applydata = $this->tradeapi_manage_mdl->subscribe_trade($bank_no,$client_id,$data['fundcode'],$data['paymentAmount'],$data['password']);
        if(!$applydata){
			$time = date('Y-m-d H:i:s',time());
			$url = $url.'?status=2&time='.base64_encode($time).'&money='.base64_encode($data['paymentAmount']).'&bankname='.$data['bankname'].'&fundcode='.base64_encode($data['fundcode']).'&bankcode='.base64_encode($bankcode).'&fundname='.$data['fundname'].'&error_info=该基金接口异常！';
            $result = array('flag'=>'10000','msg'=>'该基金接口异常！','url'=>$url);
            returnJsonStr($result);
		}
		if($applydata['success_type'] == '0'){
			$time = date('Y-m-d H:i:s',strtotime($applydata['apply_date'].' '.$applydata['apply_time']));
			$returndata['money']= $applydata['balance'];
			$url = $url.'?status=1&time='.base64_encode($time).'&money='.base64_encode($applydata['balance']).'&bankname='.$data['bankname'].'&fundcode='.base64_encode($data['fundcode']).'&bankcode='.base64_encode($bankcode).'&fundname='.$data['fundname'];
			//发送短信
			$sendtemplate = config_item('fund_sms_content');
			$sendcontent = sprintf($sendtemplate['8'], date('m月d日H时i分'), "{$data['fundname']}({$data['fundcode']})", sprintf('%.2f', $applydata['balance']).'元');
			$info = array('sign' => 0, 'channel' =>3,'content'=>$sendcontent,'mobile'=>$this->_data['userInfo']['Mobile']);
			sendSmsAction($info);
			//插入数据库
			$this->load->model('_user/Fund_Model');
			$this->Fund_Model->insertManagement(array('UserID'=>$this->_data['userInfo']['UserID'],'Type'=>'5','Payway'=>'2','BankName'=>$data['bankname'],'BankCode'=>$bankcodes,'TotalMoney'=>(int)sprintf("%.0f", $applydata['balance']*100),'Status'=>'9','SubDate'=>date('Y-m-d H:i:s'),'allot_no'=>$applydata['allot_no']));
			$result = array('flag'=>'10000','msg'=>'认购成功！','url'=>$url);
			//查看基金类型 为货币基金排除
			$ofund_type = $this->tradeapi_manage_mdl->fundType($data['fundcode'],'ofund_type');
			if($ofund_type!=2)
				$this->iscomssion($data['fundcode'],$applydata['allot_no'],$this->_data['userInfo']['UserID']);
			returnJsonStr($result);
		}else{
			$time = date('Y-m-d H:i:s',strtotime($applydata['apply_date'].' '.$applydata['apply_time']));
			$url = $url.'?status=2&time='.base64_encode($time).'&money='.base64_encode($data['paymentAmount']).'&bankname='.$data['bankname'].'&fundcode='.base64_encode($data['fundcode']).'&bankcode='.base64_encode($bankcode).'&fundname='.$data['fundname'].'&error_info='.$applydata['error_info'];
			
			$result = array('flag'=>'10000','msg'=>'申购失败！','url'=>$url);
			returnJsonStr($result);
		}
	}
	/**
     * 
     * 卖基金操作
	 *
	 * @return view
     *
    **/
	public function sellFund()
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		//防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 2);
		
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//用户编号
		
		$url = base_url().'trade/fundsellstate.html';
		$data = array();
		$data['share_data'] = filter_slashes($this->input->post('share_data'));//卖出金额
        $data['bankname']   = filter_slashes($this->input->post('bankname'));//银行名称
        $data['fundcode']   = filter_slashes($this->input->post('fundcode'));//基金代码
        $data['fundname']   = filter_slashes($this->input->post('fundname'));//基金名称
        $data['password']   = filter_slashes($this->input->post('password'));//密码
        $data['share_type'] = filter_slashes($this->input->post('share_type'));//收费方式
		$data['isBack']     = intval($this->input->post('isBack'));//赎回方式
		
		//判断是否正确传参
        if(!$data['share_data'] || !$data['bankname'] || !$data['fundcode'] || !$data['password'] ||!$data['share_type'] || !in_array($data['isBack'],array('0','1'))){
            $result = array('flag'=>'10001','msg'=>'参数提交错误！');
			returnJson($result);
        }
		$data['password'] = tradePswd($data['password']);;
		$data['bankname'] = trim(preg_replace('/\（[^\）]*\）/i', '',$data['bankname']));
		$bank_no = $this->tradeapi_manage_mdl->fundConfigSearch($data['bankname'],'1601');
		
		//交易申请查询-判断是否是申购-确认的基金
//		$applyqry = $this->tradeapi_manage_mdl->tradeApplyQry($data['fundcode'],$trade_acco,$client_id);
		//判断是否是申购-确认的基金
//		if(!isset($applyqry['taconfirm_flag']) || $applyqry['taconfirm_flag'] != '1'){
//			if(isset($applyqry['taconfirm_flag']) && $applyqry['taconfirm_flag']) {
//                $error_info = $this->tradeapi_manage_mdl->fundConfigValue($applyqry['taconfirm_flag'], 'C00006');
//            }else{
//                $error_info = '赎回确认失败！';
//            }
//			$time = date('Y-m-d H:i:s',time());
//			$url = $url.'?status=2&time='.base64_encode($time).'&money='.base64_encode($data['share_data']).'&fundcode='.base64_encode($data['fundcode']).'&fundname='.$data['fundname'].'&error_info='.$error_info;
			
//			$result = array('flag'=>'10000','msg'=>'赎回确认失败！','url'=>$url);
//			returnJsonStr($result);
//		}
		//赎回请求
		$returndata = $this->tradeapi_manage_mdl->fundRedeemTrade($bank_no,$client_id,$data['fundcode'],$data['share_data'],$data['password'],$data['isBack']);
		if(!$returndata){
			$error_info = '赎回请求失败！';	
			$time = date('Y-m-d H:i:s',time());
			$url = $url.'?status=2&time='.base64_encode($time).'&money='.base64_encode($data['share_data']).'&fundcode='.base64_encode($data['fundcode']).'&fundname='.$data['fundname'].'&error_info='.$error_info;
			
			$result = array('flag'=>'10000','msg'=>'赎回请求失败！','url'=>$url);
			returnJsonStr($result);
		}
		if(isset($returndata['success_type']) && $returndata['success_type'] == '0'){	
			$time = date('Y-m-d H:i:s',time());
			$url = $url.'?status=1&time='.base64_encode($time).'&money='.base64_encode($data['share_data']).'&fundcode='.base64_encode($data['fundcode']).'&fundname='.$data['fundname'];
			$result = array('flag'=>'10000','msg'=>'赎回请求成功！','url'=>$url);
			//发送短信
			$sendcontent = config_item('fund_sms_content');
			$sendcontent = preg_replace(array('/基金名称/','/基金代码/','/份额xxx/'),array($data['fundname'],$data['fundcode'],'份额'.$data['share_data'].'份'),$sendcontent['9']);
			$info = array('sign' => 0, 'channel' =>3,'content'=>$sendcontent,'mobile'=>$this->_data['userInfo']['Mobile']);
			sendSmsAction($info);
			//插入数据库
			$this->load->model('_user/Fund_Model');
			$this->Fund_Model->insertManagement(array('UserID'=>$this->_data['userInfo']['UserID'],'Type'=>'2','Payway'=>'2','BankName'=>$data['bankname'],'BankCode'=>'','TotalMoney'=>(int)sprintf("%.0f", $data['share_data']*100),'Status'=>'9','SubDate'=>date('Y-m-d H:i:s'),'allot_no'=>$returndata['allot_no']));
			returnJsonStr($result);
		}else{
			$error_info = $returndata['error_info'];	
			$time = date('Y-m-d H:i:s',time());
			$url = $url.'?status=2&time='.base64_encode($time).'&money='.base64_encode($data['share_data']).'&fundcode='.base64_encode($data['fundcode']).'&fundname='.$data['fundname'].'&error_info='.$error_info;
			
			$result = array('flag'=>'10000','msg'=>'赎回请求成功！','url'=>$url);
			returnJsonStr($result);
		}
					
		
	}
	/**
     * 
     * 修改分红方式操作
	 *
	 * @return view
     *
    **/
	public function dividendMethod()
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		//防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 2);
		
		/* 需要通过注册获取的数据  */
        $trade_acco = $this->_data['userInfo']['HsAccount'];//交易编号
        $client_id = $this->_data['userInfo']['HsUserID'];//用户编号
		
		$url = base_url().'trade/dividendstatus.html';
		$data = array();
		$data['fundcode'] = filter_slashes($this->input->post('fundcode'));//基金代码
        $data['fundname'] = filter_slashes($this->input->post('fundname'));//基金名称
        $data['autodata'] = intval($this->input->post('FHFS'));//分红方式
        $data['password'] = filter_slashes($this->input->post('password'));//密码
		
		//判断是否正确传参
        if(!$data['fundcode'] || !$data['fundname'] || !$data['password'] || !in_array($data['autodata'],array('0','1'))){
            $result = array('flag'=>'10001','msg'=>'参数提交错误！');
			returnJson($result);
        }
		$this->_data['password'] = tradePswd($data['password']);
		
		$applyqry = $this->tradeapi_manage_mdl->shareQry(array('client_id'=>$client_id,'fund_code'=>$data['fundcode']));
		$applyqrydata = $this->tradeapi_manage_mdl->dividend_data($applyqry);		
		$newstrade_acco = $applyqrydata['0']['trade_acco'];
		unset($applyqry,$applyqrydata);
		
		$datamsg = $this->tradeapi_manage_mdl->dividendmethod_trade($data['fundcode'],$newstrade_acco,$data['autodata'],$this->_data['password']);
		if(!$datamsg){
			$result = array('flag'=>'10002','msg'=>'接口请求异常');
			returnJson($result);
		}
		
		
		if($datamsg['success_type'] == '0'){
			$url = $url.'?status=1&fundcode='.base64_encode($data['fundcode']).'&fundname='.$data['fundname'].'&autodata='.$data['autodata'];
			$result = array('flag'=>'10000','msg'=>'修改成功','url'=>$url);
			//发送短信
			$sendcontent = config_item('fund_sms_content');
			$sendcontent = $sendcontent['12']; 
			$info = array('sign' => 0, 'channel' =>3,'content'=>$sendcontent,'mobile'=>$this->_data['userInfo']['Mobile']);
			sendSmsAction($info);
			//插入数据库
			$this->load->model('_user/Fund_Model');
			$this->Fund_Model->insertBonus(array('UserID'=>$this->_data['userInfo']['UserID'],'BeforChge'=>$data['autodata'],'AfterChge'=>$data['autodata']?'0':'1','Status'=>'9','Date'=>date('Y-m-d H:i:s'),'allot_no'=>$datamsg['allot_no']));
			returnJson($result);
		}else{
			$url = $url.'?status=2&fundcode='.base64_encode($data['fundcode']).'&fundname='.$data['fundname'].'&error_info='.$datamsg['error_info'].'&autodata='.$data['autodata'];
			$result = array('flag'=>'10003','msg'=>'修改失败','url'=>$url);
			returnJson($result);
		}
		
	}
	/**
	 *   判断是否是推荐投资的，以及有佣金详情
	 *   $fundcode  string  购买的基金代码
	 *   $atto      striong 申购编号
	 */
	private function iscomssion($fundcode,$atto,$userID)
	{
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		if(!$fundcode || !$atto || !$userID) return;
		
		$operation  = 'DECODE';
		$key        = ENCODE_KEY;	
		$datas = array();
		$datas['fundcodes'] = $datas['investorid'] = '';
		if(isset($_COOKIE["fundcodes"]))
			$datas['fundcodes'] = $_COOKIE["fundcodes"];//推荐基金
		if(isset($_COOKIE["zjuserid"]))
			$datas['investorid'] = $_COOKIE["zjuserid"];//投资者  zjUserID investorid
		
		$datas['investmentinviterid'] = authcode(base64_decode($this->input->post('investmentinviterid')), $operation, $key);

		if(!$datas['investorid']) return;//投资者为空直接返回 inviterid
		$pParam = array();		
		if($datas['investorid'] && $datas['fundcodes']==$fundcode){
			$pParam['investmentinviterid'] = $datas['investmentinviterid'];
		}
		$this->load->model('_user/Recommend_Model');
		//$where =  " proid='{$datas['proid']}' and investorid='{$datas['investorid']}' and status='1'";
		$where =  " investorid='{$datas['investorid']}' and status='1'";
		$data = $this->Recommend_Model->_getUserRelation($where,'*',"1",'id desc');
		//pre($data);
		if(isset($data['0']['inviterid'])){
			$pParam['inviterid'] = $data['0']['inviterid'];
		}

		logs('|佣金关系入参前|'.date('Y-m-d').print_r($pParam,true).PHP_EOL.print_r($datas,true).PHP_EOL,$logFile);
		if(!isset($pParam['investmentinviterid']) && !isset($pParam['inviterid'])) return '';
		if($pParam){
			$pParam['status'] = 1;
			$pParam['allot_no'] = $atto;
			$pParam['fundcode'] = $fundcode;
			$pParam['userID'] = $userID;
			$pParam['investorid'] = $datas['investorid'];
			$pParam['time'] = time();
			$this->Recommend_Model->insertTradeRelation($pParam);
		}
		return '';
	} 
}

/* End of file stockquote.php */
/* Location: ./application/controllers/stockquote.php */