<?php defined('BASEPATH') OR exit('No direct script access allowed');
/****************************************************************
 * 扩展控制器,所有控制器必须继承它
 *---------------------------------------------------------------
 * Copyright (c) 2004-2016 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:linfeng $dtime:2016-11-24
 ****************************************************************/
class MY_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
		@header("Content-type: text/html; charset=utf-8"); 
		
    }

   /**
	 * 判断用户是否登录
	 *
	 * @return integer
	 */
	protected function isLogin()
	{
		$this->load->helper('func');
		$this->load->library('buyfunds_mem');
		
		$keys = ENV . 'buyfunds_access_token';
		$data = $this->buyfunds_mem->get($keys);

		if(empty($data))
		{
			/* 常量配置在 CI config/constants.php */
			$param['open_id'] = md5(rand(100000, 999999) . time() . 'buyfunds.cn');
			$param['client_id']  = CLIENT_ID;
			$param['grant_type'] = GRANT_TYPE;
			$param['client_secret'] = CLIENT_SECRET;

			$json = curl_post(STI_OAUTH2, $param, 5, 1);

			$data = json_decode($json, TRUE);

			if(!isset($data['access_token']) || empty($data['access_token']))
			{
				logs('access_token获取失败' . PHP_EOL . 'json_data:' . print_r($data, TRUE), __METHOD__);

				return array();
			}

			/* 销毁无用的数据 */
			unset($param, $data['refresh_token'], $data['scope'], $data['expires_in']);

			$this->buyfunds_mem->set($keys, $data, ONE_DAYS);
		}
		return $data;
	}
	
	/**
	 *
	 * ajax 请求判断 防刷
	 *
	 * @param unknown $sign
	 * @param number $max 默认max+1次 内允许操作. 默认5次
	 * @param number $period  不允许请求间隔时间，即在period秒内不能进行第二次请求。默认5秒。
	 * @param bool $isLogin 是否判断已登录。 默认判断
	 *
	 */
	protected function _ajaxRequestCheck($sign, $max=4, $period=5, $isAjax = true){
	     
	    /* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
	
	    //判断是否是ajax请求
	    if(!$this->input->is_ajax_request()){
	        echo json_encode(array('flag'=>'10004', 'msg'=>'请求方式，请重试')); exit;
	    }
	    //请求源头是否合法
	    $refererUrl = $this->input->server('HTTP_REFERER', true);
	    $domain = get_host_domain($refererUrl);
	    if(!is_allowed_login_domain($domain) ){
	        echo json_encode(array('flag'=>'10005', 'msg'=>'请求错误00，请重试')); exit;
	    }
	     
	    $ip = $this->input->ip_address();
	    logs('|-ip-|' . $ip  . '|--sign--|' . $sign, $logFile);
	    
	    //防刷
	    $saRs = simpleAnti($ip, $sign, $period, $max);
	    logs('|-saRs-|' . print_r($saRs, true)  . '|--sign--|' . $sign, $logFile);
	   
	    if($saRs['flag'] != '10000'){
	        echo json_encode($saRs); exit;
	    }
	
	}
	/**
	 * 
	 * 避免单个用户多次刷
	 * @param unknown $userID
	 * @param unknown $sign
	 */
	protected function _userAnti($userID, $sign, $max, $period){
	    /* 日志路径 */
	    $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
	    //防刷
	    $saRs = simpleAnti($userID, 'userid' .$sign, $period, $max);
	    logs('|-saRs-|' . print_r($saRs, true)  . '|--sign--|' . $sign, $logFile);
	    
	    if($saRs['flag'] != '10000'){
	        echo json_encode($saRs); exit;
	    }
	}
	
	/**
	 *
	 * 检查手机号注册状态
	 *
	 * $param['ip'] IP地址
	 * $param['mobile'] 手机号
	 * @param unknown $param
	 * @return multitype:string
	 *
	 * 返回10003，10004，10000 都可看做手机未被注册。  返回10010手机已被注册
	 *
	 *
	 */
	protected function checkMobileStatus($param){
	
	    $logFile = strtolower(__FUNCTION__) . '_' . date('Ymd') . '.log';
	    $mobile = $param['mobile'];
	    $ip = $param['ip'];
	
	    if(!check_str($mobile, 'mobile')){
	        return array('flag' => '10001', 'msg'=>'您输入的手机号码不正确', 'info'=>'');
	    }

	    //手机号是否被注册
	    $cParam = array();
	     
	    $cParam = array(
	        'Type'      =>  4, //手机4
	        'CheckData' =>  $mobile,
	    );
	    //pre($param);
	    $result = $this->User_Model->checkUserExist($cParam);
	    @error_log( date('Y-m-d H:i:s') . PHP_EOL . '|-cParam-|' . print_r($cParam, true)  . '|-result-|' . print_r($result, true) . PHP_EOL, 3, LOG_PATH.'/' . $logFile);
	    //pre($result);
	    //exit;
	    unset($cParam);
	    $aParam = array();
	    if($result['Code']=='00'){
	        $info = array();
	        $user = $result['Record'];
	        $userID = (int)$user['UserID'];
	        //手机号码已注册未验证
	        $data = array('flag'=>'10010','msg'=>'该手机号码已注册', 'info'=>$userID);
	         
	    } elseif($result['Code']=='12') {
	        unset($result);
	        $data = array('flag'=>'10000','msg'=>'该手机未注册', 'info'=>'');
	
	    } else {
	        //手机号是否被注册gw出错
	        $data = array('flag'=>'10005', 'msg'=>$result['Msg'] . '-' . $result['Code'], 'info'=>'');
	    }
	
	     
	    return $data;
	}
	
	/**
	 *
	 * 短信发送与验证
	 *
	 *'mobile'    => $mobile, //手机号
	 *'code'    => $mobilekey, //验证码
	 *'userID'    => $userID, //用户id
	 *
	 */
	protected function sendVerifyCode($param){
	    //pre($param);
	    $logFile = 'function/' . strtolower(__FUNCTION__);
	    logs('|-param-|' . print_r($param, true), $logFile);

        $sendChannel = (isset($param['channel']) &&  $param['channel']) ? (int)$param['channel'] : 9; //发送频道 9阿里，6漫道
        $mobile = (int)$param['mobile'];
	    if(!check_str($mobile, 'mobile')){
	        return array('flag' => '10001', 'msg'=>'您输入的手机号码不正确', 'info'=>'');
	    }
	
	    $sMobile = $mobile;
	
	    $code = $param['code'];
	    $tmpCode = $code;
	
	    if(isset($param['tmpMobileID']) && $param['tmpMobileID']){
	        $userID = 'TMP' . (int)$param['tmpMobileID'];
	    } else if (isset($param['userID']) && $param['userID']){
	        $userID = (int)$param['userID'];
	    } else {
	        return $data = array('flag' => '10008', 'msg'=>'传参错误', 'info'=>'');
	    }
	
	
	    //发送手机验证码
        $this->load->library('Buyfunds_mem',NULL,'buyfunds_mem');
        $cache = $this->buyfunds_mem;
	    $m_key = 'user_sendsms_times_'. $userID . '_'.date('Ymd');
	    $times = $cache->get($m_key);
	    //加序号
	    //$code   .= ' 序号' .(++$times);
	
	    //设置验证码缓存 6分钟
	    $m_code_key = SEND_SMS_SIGN . $param['sign']. $userID . $sMobile;
	    //pre($m_code_key);
	    logs('|m_code_key|' . $m_code_key, $logFile);
	
	    $cache->set($m_code_key, $tmpCode, 6*60);
	
	
	    //test
	    //$data = array('flag'=>'10000', 'msg'=>'短信发送成功，请注意查收', 'info'=>$param);
	    //return $data;
	
	
	    $sType =  (isset($param['sType']) && $param['sType']) ? (int)$param['sType'] : '1'; //1 注册短信，2 找回密码 3 确认手机 4 修改手机，5 绑定手机 6，提现
	    $mobileinfo = set_sms_info($sMobile, $code, $sType);
	    //pre($sMobile);
	    //pre($mobileinfo);

        $mobileinfo['channel'] = $sendChannel; //阿里大鱼 通道
	    $mobileinfo['sign'] = 0;
	
	    //插入短信记录
	    $iParam = array(
	        'UserID' => $userID,
	        'Mobile' => $mobile,
	        'Type' => $sType,
	        'Content' =>$mobileinfo['content'],
	        'Status' => 2,
	        'SendTime' => date('Y-m-d H:i:s'),
	        'DateTime' => date('Y-m-d H:i:s')
	    );
	    $smsID = $this->User_Model->insertSMS($iParam);
	    //pre($smsID);
	    if($smsID){
	        $sResult = sendSmsAction($mobileinfo);
	        $sStatus = 2;
	              
	        //$sResult = array('flag'=>'10000', 'msg'=>'成功');
	        //$sResult['flag'] == '10000';
	        if($sResult['flag'] == '10000'){
	            $sStatus = 1;
	            $cache->set($m_key, $times);
	            $sid = 2;
	            $tid = time();
	            //设置发送短信的时间 用于函数mobileAndUser
	            $t1  = 'time_sendsms_times_user_'.date('Ymd').'_'.$userID;
	            $t2  = 'time_sendsms_times_mobile_'.date('Ymd').'_'.$sMobile;
	            $cache->set($t1, $tid, 7*60);
	            $cache->set($t2, $tid, 7*60);
	        
	            $aData = array(
	                'userID'    => $userID,
	                'mobile'    => $mobile,
	                'tid'      => $tid,
	            );
	            $data = array('flag'=>'10000', 'msg'=>'短信发送成功，请注意查收！', 'info'=>$aData);
	        } else {
	            $data = $sResult;
	        }
	        
	        $uParam = array(
	            'Status' => $sStatus,
	            'ErrorCode' => $sResult['flag'],
	            'Remark' =>$sResult['msg'],
	            'DateTime' => date('Y-m-d H:i:s')
	        );
	        //pre($uParam);
	        $sr = $this->User_Model->updateSMS($uParam, array('SMSID'=>$smsID));
	        //pre($sr);
	    } else {
	        $data = array('flag'=>'10019', 'msg'=>'短信信息记录失败，请重试！', 'info'=>$aData);
	    }

	
	    return $data;
	}
}

/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */