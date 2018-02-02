<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 基金交易-中金用户中心 交易入口接口
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 BUYFUNDS Inc. (https://trade.buyfunds.cn)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2017-02-22
 ****************************************************************/
class Fundtrade_cnfol extends MY_Controller 
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
     * 中金用户中心 购买基金页面入口
	 *
	 * @return view
     *
    **/
    public function trade() 
    { 	
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
        $param = array();
		$param['fundcode'] = filter_html($this->input->get('fundcode'));
		$param['money']    = $this->input->get('money');
		$param['tid']      = $tid = $this->input->get('tid');
		$param['userid']   = $zjUserID = (int)$this->input->get('userid'); //中金用户ID
		$param['keystr']   = $keystr = $this->input->get('keystr');
		$param['key']      = $key = $this->input->get('key');
		$sparam            = $this->input->get('s');//推荐投资参数
		//t($param);
		$tid2 = time();
		//请求源头是否合法
		$refererUrl = $this->input->server('HTTP_REFERER', true);
		if(!$refererUrl){
		    exit('错误请求');
		}
		$domain = get_host_domain($refererUrl);
		//t($domain);
		if(!is_allowed_login_domain($domain) ){
		  exit('请求错误');
		}
		if(!$param['fundcode'] || !$param['tid'] || !$param['userid'] || !$param['key'] || !$param['keystr']){
		    exit('错误链接');
		}
		
		$interval = 60*30; //链接有效期半小时
		if($tid-$tid2 > $interval || $tid2-$tid > $interval){
		    exit('链接已过期');
		}
		//print_r($param);exit;
		$vKey = getMysign($param, ENCODE_KEY);
		if($keystr != $vKey){
		    exit('链接错误');
		}
		
		$isRequest = $this->cache->get($keystr);
		if($isRequest == '1'){
		    exit('链接不能重复使用，请刷新页面后在进行购买');
		}
		if(!$isRequest){
		    //验证加密串准确性
		    $pURL = 'https://passport.cnfol.com/api/userinfo/checkuserislogin';
		    $this->config->load('oauthsettings');
		    $setting = $this->config->item('multi_account');
		    $sysID = $setting['cnfol']['oauth_user_id'];
		    
		    unset($setting);
		    
		    $pParam = array(
		        'tid' => $tid2,
		        'sysid' => $sysID,
		        'ktid' => $tid,
		        'userid' => $zjUserID,
		        'key' => $key,
		        'keystr' => md5($tid2 . md5($zjUserID) . PASSPORT_AUTH_KEY . $key . $tid . $sysID)
		    );
		    $cRs = curl_post($pURL, $pParam);
		    logs('|-cRs-|' . print_r($cRs, true), $logFile);
		    if(!isset($cRs['code']) || $cRs['code'] != '200' || !isset($cRs['data']) || !$cRs['data']){
		        exit('信息获取失败');
		    }
		    $aRs = json_decode($cRs['data'], true);
		    
		    if(!isset($aRs['flag']) || $aRs['flag']!= '10000'){
		        exit('信息获取失败，请重新登录中金帐号' . $aRs['flag'] . '-' . $aRs['msg']);
		    }
		    
		    $openID = $aRs['info'];
		    $this->cache->set($keystr, $openID, 15*60);
		} else {
		    //如果已验证过，无需再验证验证码
		    $openID = $isRequest;
		}
		
		$original = 5;
		
		//购买页链接创建
		$tParam = array();
		$tParam['fundcode'] = $param['fundcode'];
		if($param['money']){
		    $tParam['money'] = filter_html($param['money']);
		}
		$recommendid = '';
		if($sparam){
			$tParam['s'] = filter_html($sparam);
			$key = ENCODE_KEY;
			$operation = 'DECODE';
			$tcparam  = authcode(base64_decode($this->input->get('s')), $operation, $key);
			$paramarr = explode(',',$tcparam);
			if(is_array($paramarr)&&$paramarr){
				$recommendid = filter_html($paramarr['0']); //注册推荐人
			}
			
		}
		setcookie('zjuserid',$zjUserID, time()+3600*24*30,'/',$this->buydomain );
		setcookie('fundcodes',$param['fundcode'], time()+3600*24*30,'/',$this->buydomain );
		$sURL = http_build_query($tParam);
		$loUrl =  TRADE_WEB_URL . '/trade/fundtrade.html?' . $sURL;
		if(isset($tParam['s']) && $tParam)
			$loUrl =  TRADE_RECOMMEND_URL .'?'. $sURL;//推荐投资购买页面
		$userInfo = $this->User_Model->getOauthinfo(array('OpenID'=>$openID,'UnionID'=>0,'original'=>$original), 'OauthID,UserID');
		if(isset($userInfo['UserID']) && $userInfo['UserID']){ //绑定过中金帐号，直接登录
		    $this->cache->set($keystr, 1, 60*30);
		    $this->_thirdLogin($userInfo['UserID'], $original, $loUrl);
		    exit;
		}
		//是否登录
		$r = $this->User_Interact->ajaxPassportCheck();
		if($r['flag'] == '10000'){ //已登录伯嘉帐号
		    $loUserID = (int)$this->User_Interact->getUserID();
		        if(!$isRequest){
		            //清除cookie
		            $this->User_Interact->delCnfolCookie();
		            $tUrl = TRADE_LOGIN_URL . '?rt='. urlencode(base64_encode(cur_page_url()));
		            cnfol_location($tUrl);
		            exit;
		        } 
                $oParam = array(
    		        'UserID'   => $loUserID,
    		        'OpenID'   => $openID,
    		        'UnionID'  =>0,
    		        'Original' => $original
    		    );
				logs('|参数进入添加|' . print_r($oParam, true), $logFile);
    		    $aoRs = $this->User_Model->addOauth($oParam);
    		    if($aoRs['flag'] != '10000'){
    		        echo $aoRs['msg']; exit;
    		    }
				logs('|参数进入添加绑定前|' . print_r($sparam, true), $logFile);
    		    if($sparam){
					logs('|参数添加绑定前|' . print_r($loUserID, true), $logFile);
					$this->bindShare($loUserID, $sparam);
				}
				logs('|-调用金融超市接口前-|', $logFile);
				if(!$recommendid) $recommendid = $zjUserID;
    		    //调中金金融超市绑定用户接口
    		    $fsParam = array(
    		        'action' => 'setUserbinding', 
                    'instid' => INSTID, //机构ID，伯嘉固定1
                    'proid' => PROID, //项目ID，伯嘉固定1
    		        'userid' => $zjUserID, //中金用户ID
    		        'recid' => $recommendid, //中金用户ID
                    'bindtype' => 1, //1:绑定 2:开户
    		        't' => time(),
    		    );
				logs('|-调用金融超市接口前-|', $logFile);
    		    $fsParam['secretkey'] = getMysign($fsParam, ENCODE_KEY);
    		    logs('|-fsParam-|' . print_r($fsParam, true), $logFile);
                $cgUrl = FINANCIAL_SUPERMARKET_USERBIND_URL . '?' . http_build_query($fsParam);
                logs('|-cgUrl-|' . $cgUrl, $logFile);
    		    $fsRs = curl_get($cgUrl);
    		    logs('|-fsRs-|' . print_r($fsRs, true), $logFile);

		    $this->cache->set($keystr, 1, 60*30);
		    $this->_thirdLogin($loUserID, $original, $loUrl);
		    exit;

		}
		//清除cookie
		$this->User_Interact->delCnfolCookie();
		
		$tUrl = TRADE_LOGIN_URL . '?rt='. urlencode(base64_encode(cur_page_url()));
		cnfol_location($tUrl);
		
    }
	/**
     * 
     * 中金用户中心 伯嘉用户页面入口
	 *
	 * @return view
     *
    **/
    public function usercenter() 
    { 	
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
        $param = array();

		$param['tid']      = $tid = $this->input->get('tid');
		$param['userid']   = $zjUserID = (int)$this->input->get('userid'); //中金用户ID
		$param['keystr']   = $keystr = $this->input->get('keystr');
		$param['key']      = $key = $this->input->get('key');
		//$sparam            = $this->input->get('s');//推荐投资参数
		$tid2 = time();
		//请求源头是否合法
		$refererUrl = $this->input->server('HTTP_REFERER', true);
		if(!$refererUrl){
		    exit('错误请求');
		}
		$domain = get_host_domain($refererUrl);
		if(!is_allowed_login_domain($domain) ){
		   exit('请求错误');
		}
		if(!$param['tid'] || !$param['userid'] || !$param['key'] || !$param['keystr']){
		    exit('错误链接');
		}
		
		$interval = 60*30; //链接有效期半小时
		if($tid-$tid2 > $interval || $tid2-$tid > $interval){
		    exit('链接已过期');
		}
		
		$vKey = getMysign($param, ENCODE_KEY);
		if($keystr != $vKey){
			logs($param,$logFile);
		    exit('链接错误');
		}
		setcookie('zjuserid',$zjUserID, time()+3600*24*30,'/',$this->buydomain );
		setcookie('recid',$zjUserID, time()+3600*24*30,'/',$this->buydomain );
		$isRequest = $this->cache->get($keystr);
		if(!$isRequest){
		    //验证加密串准确性
		    $pURL = 'https://passport.cnfol.com/api/userinfo/checkuserislogin';
		    $this->config->load('oauthsettings');
		    $setting = $this->config->item('multi_account');
		    $sysID = $setting['cnfol']['oauth_user_id'];
		    
		    unset($setting);
		    
		    $pParam = array(
		        'tid' => $tid2,
		        'sysid' => $sysID,
		        'ktid' => $tid,
		        'userid' => $zjUserID,
		        'key' => $key,
		        'keystr' => md5($tid2 . md5($zjUserID) . PASSPORT_AUTH_KEY . $key . $tid . $sysID)
		    );
			//pre($pParam);
		    $cRs = curl_post($pURL, $pParam);
			//pre($cRs);
		    logs('|-cRs-|' . print_r($cRs, true), $logFile);
		    if(!isset($cRs['code']) || $cRs['code'] != '200' || !isset($cRs['data']) || !$cRs['data']){
		        exit('信息获取失败');
		    }
		    $aRs = json_decode($cRs['data'], true);
		    if(!isset($aRs['flag']) || $aRs['flag']!= '10000'){
		        exit('信息获取失败，请重新登录中金帐号' . $aRs['flag'] . '-' . $aRs['msg']);
		    }
		    
		    $openID = $aRs['info'];
		    $this->cache->set($keystr, $openID, 15*60);
		} else {
		    //如果已验证过，无需再验证验证码
		    $openID = $isRequest;
		}
		
		$original = 5;
		
		//$sURL = http_build_query($tParam);
		$loUrl =  TRADE_WEB_URL;
		$userInfo = $this->User_Model->getOauthinfo(array('OpenID'=>$openID,'UnionID'=>0,'original'=>$original), 'OauthID,UserID');
		//var_dump($userInfo);
		if(isset($userInfo['UserID']) && $userInfo['UserID']){ //绑定过中金帐号，直接登录
		    $this->cache->set($keystr, 1, 60*30);
		    $this->_thirdLogin($userInfo['UserID'], $original, $loUrl);
		    exit;
		}
		//是否登录
		$r = $this->User_Interact->ajaxPassportCheck();
		if($r['flag'] == '10000'){ //已登录伯嘉帐号
		    $loUserID = (int)$this->User_Interact->getUserID();
		        if(!$isRequest){
		            //清除cookie
		            //$this->User_Interact->delCnfolCookie();
		           // $tUrl = TRADE_LOGIN_URL . '?rt='. urlencode(base64_encode(cur_page_url()));
		           // cnfol_location($tUrl);
		          //  exit;
		        } 
                $oParam = array(
    		        'UserID'   => $loUserID,
    		        'OpenID'   => $openID,
    		        'UnionID'  =>0,
    		        'Original' => $original
    		    );
				logs('|参数进入添加|' . print_r($oParam, true), $logFile);
    		    $aoRs = $this->User_Model->addOauth($oParam);

    		    if($aoRs['flag'] != '10000' && $aoRs['flag'] != '10006'){
    		        echo $aoRs['msg']; exit;
    		    }
				if($aoRs['flag'] == '10006'){
					cnfol_location($loUrl);
					//$this->_thirdLogin($userInfo['UserID'], $original, $loUrl);
					exit;
				}
				logs('|参数进入添加绑定前|' . print_r($loUserID, true), $logFile);
				$this->bindShare($loUserID, '');
				logs('|-调用金融超市接口前-|', $logFile);
    		    //调中金金融超市绑定用户接口
    		    $fsParam = array(
    		        'action' => 'setUserbinding', 
                    'instid' => INSTID, //机构ID，伯嘉固定1
                    'proid' => PROID, //项目ID，伯嘉固定1
    		        'userid' => $zjUserID, //中金用户ID
    		        'recid' => $zjUserID, //中金用户ID
                    'bindtype' => 2, //1:绑定 2:开户
    		        't' => time(),
    		    );
				logs('|-调用金融超市接口前-|', $logFile);
    		    $fsParam['secretkey'] = getMysign($fsParam, ENCODE_KEY);
    		    logs('|-fsParam-|' . print_r($fsParam, true), $logFile);
                $cgUrl = FINANCIAL_SUPERMARKET_USERBIND_URL . '?' . http_build_query($fsParam);
                logs('|-cgUrl-|' . $cgUrl, $logFile);
    		    $fsRs = curl_get($cgUrl);
    		    logs('|-fsRs-|' . print_r($fsRs, true), $logFile);

		   // $this->cache->set($keystr, 1, 60*30);
		    $this->_thirdLogin($loUserID, $original, $loUrl);
		    exit;

		}
		//清除cookie
		$this->User_Interact->delCnfolCookie();
		$tUrl = TRADE_LOGIN_URL . '?rt='. urlencode(base64_encode(cur_page_url()));
		cnfol_location($tUrl);
		
    }
    
    /**
     * 
     * 第三方登录
     * 
     */
    private function _thirdLogin($userID, $original, $callback){
        
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        
        $ip = $this->input->ip_address();
        $this->User_Interact->delCnfolCookie();
        //跳转
        $param=array(
            'UserID'=>$userID,
            'LoginType'=>1,
            'LoginIP'=>$ip,
            'LoginPage'=>$original,
            'LoginAddr'=>'0',
        );
        $this->User_Model->userLoginLog($param);
        
        //获取用户信息
        $result=$this->User_Model->getUserBaseByUserID($userID);
        logs('|-第三方登录用户信息$result-|'. print_r($result, true), $logFile);
        
        if(!is_array($result) || !isset($result['UserID'])){
            echo json_encode(array('flag'=>'10026', 'msg'=>'表格获取数据缺失!'));
            echo '<script>alert("表格获取数据缺失!");window.location.href='.TRADE_WEB_URL.';</script>';
            exit;
        }
        if(!isset($result['Status']) || $result['Status']!=1){
            echo json_encode(array('flag'=>'10030', 'msg'=>'账号已锁定!'));
            echo '<script>alert("账号已锁定!");window.location.href='.TRADE_WEB_URL.';</script>';
            exit;
        }
        //将获取的信息导入cookie
        $user=array(
            'logintime'=>$result['LastLoginTime'],
            'userid'=>$result['UserID'],
            'username'=>$result['UserName'],
            'nickname'=>$result['NickName'],
            'keystr'=>$result['KeyStr'],
            'auto'=>0,
            'mobile'=>$result['Mobile'],
        );
        $this->User_Interact->setCnfolCookie($user, 0);
        //echo $callback;exit;
        cnfol_location($callback);
    }
	 /**
     * 
     * 绑定分享关系
     * 
     */
    private function bindShare($loUserID = '', $sparam = ''){
		$logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
		logs('|-进入分享绑定-|'.date('Y-m-d H:i:s').print_r($loUserID.$sparam,true).PHP_EOL,$logFile);
		if(!$loUserID) return;
		
		$key = ENCODE_KEY;
		$operation = 'DECODE';
		$tcparam  = authcode(base64_decode($sparam), $operation, $key);
		
		$paramarr = explode(',',$tcparam);

		$param['inviterid'] = $param['investorid'] = '';
		if(isset($paramarr['0']))
			$param['inviterid']  = filter_html($paramarr['0']); //注册推荐人
		if(isset($paramarr['1']))
			$param['investorid'] = filter_html($paramarr['1']); //注册人
		if(isset($_COOKIE["zjuserid"]))
			$param['investorid'] = $_COOKIE["zjuserid"];
		if(!$param['investorid']) return;
		$param['proid']      = PROID; //项目id 1-基金超市	
		logs('|-分享对应关系加密串-|'.date('Y-m-d H:i:s').print_r($param,true).PHP_EOL,$logFile);
		$where =  " proid='{$param['proid']}' and investorid='{$param['investorid']}'";
		$data = $this->Recommend_Model->_getUserRelation($where,'*',"1",'id desc');
		logs('|-查看用户关系是否已经生成-|'.date('Y-m-d H:i:s').print_r($data,true).PHP_EOL,3,$logFile);
		//pre($data);
		if(empty($data)){
			 $pParam = array(
				'userID'     => $loUserID,
				'inviterid'  => $param['inviterid'],
				'investorid' => $param['investorid'],
				'time'       => time(),
				'proid'      => $param['proid'],
				'status'     => 1
			);
			logs('|-插入数据库数据-|'.date('Y-m-d H:i:s').print_r($pParam,true).PHP_EOL,3,$logFile);
			$a = $this->Recommend_Model->insertUserRelation($pParam);
			logs('|-插入数据库数据结果-|'.date('Y-m-d H:i:s').print_r($a,true).PHP_EOL,3,$logFile);
		}
			
	}
	  
}

/* End of file stockquote.php */
/* Location: ./application/controllers/stockquote.php */