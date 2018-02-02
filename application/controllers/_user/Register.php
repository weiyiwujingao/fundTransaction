<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 基金交易-用户注册
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2016-12-12
 ****************************************************************/
class Register extends MY_Controller 
{
	/* 传递到对应视图的数据 */
	private $_data     = array();
	private $aType;
	private $cache;
	
    public function __construct() 
    {
        parent::__construct();
        $this->_data['headerCss'] = '';
        $this->_data['title'] = '注册-开户';
        $this->_data['keywords'] = SEO_KEYWORDS;
        $this->_data['description'] = SEO_DESCRIPTION;
        
        $this->_data['regUrl'] = TRADE_WEB_URL . '/user/register';
        $this->_data['verifyCodeUrl']  = TRADE_WEB_URL . '/ajax/verifycode.html';
        $this->_data['verifyCodeShowUrl']  = TRADE_WEB_URL . '/ajax/verifycodeshow.html';
        
        $this->aType = array('1' => 'reg', '2'=>'findpswd', '3'=>'code'); //请求类型 1注册，2找回密码
        
        $this->load->library('Buyfunds_mem',NULL,'buyfunds_mem');
        $this->cache = $this->buyfunds_mem;
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
	   // echo get_host_domain('https://trade.buyfunds.cn/trade/fundtrade.html?6666');exit;
        //logs('1111', $logFile);
        $tid = time();
        $this->_data['tid'] = $tid;
        //验证手机号
        $this->_data['mkid'] = substr(md5(ENCODE_KEY . substr(md5($tid), -5) . 'mobile'), 8, 16);
        //获取验证码
        $this->_data['ckid'] = substr(md5(ENCODE_KEY . substr(md5($tid), -5) . 'code'), 8, 16);
        //验证昵称
        $this->_data['nkid'] = substr(md5(ENCODE_KEY . substr(md5($tid), -5) . 'nickname'), 8, 16);
        //注册验证
        $this->_data['rkid'] = substr(md5(ENCODE_KEY . substr(md5($tid), -5) . 'reg'), 8, 16);
        
        
        $this->_data['title'] = '免费开户_伯嘉基金网';
		$this->load->view('_user/register.html', $this->_data);
    }


    
    /**
     *
     *  获取注册验证码
     *
     */
    public function getMobileCode(){
    
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__) . '_' . date('Ymd') . '.log';
    
        $mobile = filter_slashes(trim($this->input->post('mobile', true)));
        $type = (int)trim($this->input->post('type', true));  //1 注册页， 2 找回密码
        $tstr = filter_slashes(trim($this->input->post('tstr', true))); //图片验证码
        $pCode = filter_slashes(trim($this->input->post('pcode', true))); //图片验证码
        $codeID = filter_slashes(trim($this->input->post('codeid', true))); //验证码ID
        $codeIDX = filter_slashes(trim($this->input->post('codeidx', true))); //验证码随机串
    
        $tid = $this->input->post('tid', true);
        $kid = $this->input->post('kid', true);
    
        //参数是否完整
        if(!$mobile || !$type || !$tid || !$kid || !$pCode || !$codeID || !$codeIDX || !$tstr){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }
    
        if(!isset($this->aType[$type])){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误2，请重试')); exit;
        }
        //页面过期, 半小时
        isRequestTimeout($tid, 60*30, '请求已失效，请刷新页面重试');

        $sType = $this->aType[$type];
        $xVer = md5(md5($kid) . $tid . md5($tstr . ENCODE_KEY) . $codeIDX. $sType);
        
        if($xVer != $codeID){
            echo json_encode(array('flag'=>'10003', 'msg'=>'校验错误')); exit;
        }
        

        $cPcode = $this->cache->get($codeID . $sType);
        //pre($cPcode);
        //pre($pCode);
        if(!$cPcode){
            echo json_encode(array('flag'=>'10301', 'msg'=>'验证码不存在或已失效,点击验证码图片刷新')); exit;
        }
        //pre(strtolower($cPcode));
        //pre(strtolower($pCode));
        if(strtolower($cPcode) != strtolower($pCode)){
            echo json_encode(array('flag'=>'10301', 'msg'=>'验证码错误')); exit;
        }
        $this->cache->set($codeID . $sType, '', 1); //清掉验证码缓存
        
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);

        //短信是否在3分钟内有发送过
        $mauRs = mobileAndUser($mobile);
        if($mauRs['flag'] == '10000'){
            echo json_encode(array('flag'=>'10008', 'msg'=>$mauRs['msg'])); exit;
        }

        $ip = $this->input->ip_address();
        @error_log(date('Y-m-d H:i:s').'|-ip-|' . $ip . '|-mobile-|' . $mobile . '|-type-|' . $type . '|||' .PHP_EOL,3,LOG_PATH. '/'. $logFile);
        //检查手机
        $cParam = array('mobile'=>$mobile, 'ip'=>$ip);
        $cRs =  $this->checkMobileStatus($cParam);
        @error_log(date('Y-m-d H:i:s').'|-$cParam-|' . print_r($cParam, true) . '|-cRs-|' . print_r($cRs, true) . PHP_EOL,3,LOG_PATH. '/'. $logFile);
    
    
        if($type == 1){
            if ($cRs['flag'] == '10010'){ //已注册
                echo json_encode(array('flag'=>'10007', 'msg'=>'手机号已被注册')); exit;
            } else if($cRs['flag'] == '10000') { //未注册
    
                $uParam = array('Type'=>1, 'Mobile' => $mobile, 'IP'=>$ip);
                $umRs = $this->User_Model->updateMobileVerifyCode($uParam);
                //pre($umRs);
                
                if($umRs['Code'] == '00'){
                    //pre($umRs['Record']);
                    $mobilekey      = $umRs['Record']['MobileCheck'];  //新的验证码
                    $tmpMobileID      = $umRs['Record']['TmpMobileID'];  //新的验证码
                    
                    $aParam = array(
                        'mobile'    => $mobile, //手机号
                        'code'    => $mobilekey, //验证码
                        'tmpMobileID'    => $tmpMobileID, //临时手机存放表ID
                        'IP'    => $ip, //ip
                        'sType' => 1, //1 注册短信，2 找回密码 3 确认手机 4 修改手机，5 绑定手机 6，提现
                        'sign' =>'bj_reg_check'
                    );
                    //pre($tmpMobileID);
                    $svcRs = $this->sendVerifyCode($aParam);
                    @error_log(date('Y-m-d H:i:s').'|-aParam-|' . print_r($aParam, true) . '|-svcRs-|' . print_r($svcRs, true) . PHP_EOL,3,LOG_PATH. '/'. $logFile);
    
                    if($svcRs['flag'] == '10000'){
                        $tstr2 = time();
                        $vstr2 = md5(md5($tmpMobileID . $kid) . $tid . md5($tstr2 . ENCODE_KEY));
                        $this->cache->set($vstr2 . 'pCode', strtolower($pCode), 6*60); //清掉验证码缓存
                        echo json_encode(array('flag'=>'10000', 'msg'=>$svcRs['msg'], 'info'=> array('type'=>'mobile', 'tstr'=>$tstr2, 'vstr'=>$vstr2, 'tmid'=>$tmpMobileID)));
                        exit;
                    } else {
                        echo json_encode($svcRs); exit;
                    }
                } else if($umRs['Code'] == '100092'){
                    echo json_encode(array('flag'=>'10010', 'msg'=>'手机号已被注册')); exit;
                } else {
                    echo json_encode(array('flag'=>'10011', 'msg'=>$umRs['Msg'])); exit;
                }
            } else {
                echo json_encode($cRs); exit;
            }
    
    
        }else if ($type == 2) {

            if ($cRs && isset($cRs['flag']) && $cRs['flag'] == '10010') { //已注册

                $userID=$cRs['info'];
                //获取用户参数
                $res=$this->User_Model->getUserBaseByUserID($userID);

                if(!$res || !isset($res['Mobile']) || !isset($res['Status'])){
                    echo json_encode(array('flag'=>'10025', 'msg'=>'用户参数获取失败！')); exit;
                }
                if($res['Status']!=1){
                    echo json_encode(array('flag'=>'10026', 'msg'=>'用户状态锁定！')); exit;
                }

                $uParam = array('Type' => 2, 'Mobile' => $mobile, 'IP' => $ip,'UserID'=>$cRs['info']);
                //获取找回密码的手机口令
                $umRs = $this->User_Model->updateUserVerifyCode($uParam);
                //pre($umRs);

                if ($umRs['Code'] == '00') {
                    //pre($umRs['Record']);
                    $mobilekey = $umRs['Record']['CheckCode'];  //新的验证码
                    $UserID = $umRs['Record']['UserID'];  //新的验证码id

                    $aParam = array(
                        'mobile' => $mobile, //手机号
                        'code' => $mobilekey, //验证码
                        'userID' => $UserID, //用户ID
                        'IP' => $ip, //ip
                        'sType' => 2, //1 注册短信，2 找回密码 3 确认手机 4 修改手机，5 绑定手机 6，提现
                        'sign' => 'bj_bkpwd_check'
                    );
                    //pre($tmpMobileID);

                    //发送短信
                    $svcRs = $this->sendVerifyCode($aParam);
                    //@error_log(date('Y-m-d H:i:s').'|-aParam-|' . print_r($aParam, true) . '|-svcRs-|' . print_r($svcRs, true) . PHP_EOL,3,LOG_PATH. '/'. $logFile);
                    $svcRs['flag']='10000';
                    if ($svcRs['flag'] == '10000') {
                        $tstr2 = time();
                        $vstr2 = md5(md5($UserID . $kid) . $tid . md5($tstr2 . ENCODE_KEY));
                        $this->cache->set($vstr2 . 'pCode', strtolower($pCode), 6 * 60); //清掉图片验证码缓存
                        echo json_encode(array('flag' => '10000', 'msg' => $svcRs['msg'], 'info' => array('type' => 'mobile', 'tstr' => $tstr2, 'vstr' => $vstr2, 'uid' => $UserID)));
                        exit;
                    } else {
                        echo json_encode($svcRs);
                        exit;
                    }
                } else {
                    echo json_encode(array('flag' => '10011', 'msg' => $umRs['Msg']));
                    exit;
                }

            } else {
                echo json_encode(array('flag' => '10021', 'msg' => '手机号未注册'));
                exit;
            }
        } else {
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试' . '2')); exit;
        }
    
    }
    

    /**
     *
     * 注册动作
     *
     */
    public function ajaxRegAction(){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__) . '_' . date('Ymd') . '.log';
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
         
        $mobile = filter_slashes(trim($this->input->post('mobile', true))); //手机号
        $pCode = filter_slashes(trim($this->input->post('pcode', true))); //图片验证码
        $mCode = filter_slashes(trim($this->input->post('mcode', true))); //短信验证码
        $password = filter_slashes(trim($this->input->post('pswd', true))); //密码
        $comPassword = filter_slashes(trim($this->input->post('cpswd', true))); //密码
        $tmpMobileID = filter_slashes(trim($this->input->post('tmid', true))); //临时手机存放表ID
        $type = (int)trim($this->input->post('type', true));  //1 注册页， 2绑定修改手机号
         
        $tstr = $this->input->post('tstr', true);
        $vstr = $this->input->post('vstr', true);
        $tid = $this->input->post('tid', true);
        $kid = $this->input->post('kid', true);
        $ip = $this->input->ip_address();
         
        //参数是否完整
        if(!$mobile || !$pCode || !$mCode || !$password || !$tmpMobileID || !$type || !$tid || !$kid || !$tstr || !$vstr){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }
        
         
        //获取验证码请求过期, 半小时
        isRequestTimeout($tstr, 60*30, '请求已失效，请刷新页面重试');
        $qVer = md5(md5($tmpMobileID . $kid) . $tid . md5($tstr . ENCODE_KEY));
        if($qVer != $vstr){
            echo json_encode(array('flag'=>'100031', 'msg'=>'校验错误')); exit;
        }
        
        $qKeyCount = cacheKeyCount($vstr);
        //pre($qKeyCount);
        if($qKeyCount['count'] > 15){ //一个key请求不能超过10次
            echo json_encode(array('flag'=>'10006', 'msg'=>'校验过期')); exit;
        }
    
        //密码判断
        $checkPwd =  checkPassword($password);
        if($checkPwd['flag'] != '10000'){
            echo json_encode($checkPwd); exit;
        }
        
        //图片验证码
        $cPCode = $this->cache->get($qVer . 'pCode');
        //pre($cPCode);
        if(!$cPCode){
            echo json_encode(array('flag'=>'10007', 'msg'=>'图片验证码不存在或已过期!')); exit;
        }
        
        if($cPCode != strtolower($pCode)){
            echo json_encode(array('flag'=>'10008', 'msg'=>'图片验证码错误1')); exit;
        }
        
        //短信验证码判断
        $m_key = SEND_SMS_SIGN . 'bj_reg_checkTMP' . $tmpMobileID  . $mobile;
        //pre($m_key);
        $vCode = $this->cache->get($m_key);
        //pre($vCode);
        if(!$vCode){
            echo json_encode(array('flag'=>'10007', 'msg'=>'短信验证码不存在或已过期')); exit;
        }
        if($mCode != $vCode){
            echo json_encode(array('flag'=>'10008', 'msg'=>'短信验证码错误')); exit;
        }
 
        //手机号判断
        @error_log(date('Y-m-d H:i:s').'|-ip-|' . $ip . '|-mobile-|' . $mobile . '|-phoneArea-|' . $phoneArea . '|||' .PHP_EOL,3,LOG_PATH. '/'. $logFile);
        //检查手机
        $cParam = array('mobile'=>$mobile, 'ip'=>$ip);
        $cRs =  $this->checkMobileStatus($cParam);
        @error_log(date('Y-m-d H:i:s').'|-$cParam-|' . print_r($cParam, true) . '|-cRs-|' . print_r($cRs, true) . PHP_EOL,3,LOG_PATH. '/'. $logFile);
    
        if ($cRs['flag'] == '10010'){ //已注册
            echo json_encode(array('flag'=>'10010', 'msg'=>'手机号已被注册')); exit;
        } else if($cRs['flag'] == '10000') { //未注册
             
            $cParam = array(
                'Type' => 1, // type=1， 手机注册，手机绑定， type=2 手机修改, type=3 找回密码
                'TmpMobileID' => $tmpMobileID,
                'Mobile' => $mobile,
                'IP' => $ip,
                'MobileCheck' => $mCode
            );
            $cRs = $this->User_Model->checkMobileCode($cParam);
            @error_log( date('Y-m-d H:i:s') . PHP_EOL . '|-cParam-|' . print_r($cParam, true)  . '|-cRs-|' . print_r($cRs, true) . PHP_EOL, 3, LOG_PATH.'/' . $logFile);
    
            if($cRs['Code'] == '00'){
    
                $rParam = array(
                    'Type' =>'1',  //1=>'CM-', 2=>'QQ-', 3=>'SINA-', 4=>'WX-'
                    'Mobile' => $mobile,
                    'Password' => $password,
                    'RegIP' => $ip,
                    'RegPlatFormType' => 1, //1 PC, 2手机网
                    'RegTime' => $cRs['Record']['ActiveTime'],
                    'GroupID' =>'1',
                    'Status' =>'1',
                    'CustType'=>CUST_TYPE
                );
                $cRs = $this->User_Model->userReg($rParam);
                if($cRs['Code'] == '00'){
                    $userID = $cRs['UserID'];
                    $lParam = array(
                        'UserID' =>$userID,
                        'LoginType'=>1, // 登录类别  1-PC端 2-手机端
                        'LoginIP'=>$ip, 
                        'LoginPage'=>1, //登录方式 1-伯嘉基金 2-腾讯QQ 3-新浪微博 4-微信 5-中金
                        'LoginAddr'=>1 //登陆地址
                    );
                    $this->User_Model->userLoginLog($lParam);
                    
                    $user = $this->User_Model->getUserBaseByUserID($userID);
                    $userParam['userid']    = $user['UserID'];
                    $userParam['username']  = $user['UserName'];
                    $userParam['nickname']  = $user['NickName'];
                    $userParam['status']    = $user['Status'];
                    $userParam['mobile']    = $user['Mobile'];
                    $userParam['keystr']    = $user['KeyStr'];
                    $userParam['logintime'] = $user['ActiveTime'];
                    $this->load->model('_user/User_Interact');
                    $this->User_Interact->setCnfolCookie($userParam);
                    
                    
                    echo json_encode(array('flag'=>'10000', 'msg'=>'注册成功', 'info'=> TRADE_WEB_URL . '/user/center/regbindbank')) ; exit;
    
                } else {
                    echo json_encode(array('flag'=>'10011', 'msg'=>$cRs['Msg'] )) ; exit;
                }
            } else {
                echo json_encode(array('flag'=>'10009', 'msg'=>$cRs['Msg'] )) ; exit;
    
            }
    
        } else {
            echo json_encode($cRs); exit;
        }
        
    }
    /**
     *
     */
    public function regBindSuccess(){
        
        $this->_data['title'] = '免费开户_伯嘉基金网';
        $this->load->view('_user/regbindsuccess.html', $this->_data);
    }


    /**
     *
     * 找回密码首页
     *
     * @return view
     *
     **/
    public function pwdindex()
    {

        $tid = time();
        $this->_data['tid'] = $tid;
        //验证手机号
        $this->_data['mkid'] = substr(md5(ENCODE_KEY     . substr(md5($tid), -5) . 'mobile'), 8, 16);
        //获取验证码
        $this->_data['ckid'] = substr(md5(ENCODE_KEY . substr(md5($tid), -5) . 'code'), 8, 16);
        $this->_data['title'] = '找回密码_基金交易登录_伯嘉基金网';

        $this->load->view('_user/back_pwd_check.html', $this->_data);

    }
    /**
     *
     * 找回密码动作
     *
     */
    public function ajaxBkpwdAction(){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);

        $mobile = filter_slashes(trim($this->input->post('mobile', true))); //手机号
        $pCode = filter_slashes(trim($this->input->post('pcode', true))); //图片验证码
        $mCode = filter_slashes(trim($this->input->post('mcode', true))); //短信验证码
        $userID = filter_slashes(trim($this->input->post('uid', true))); //用户表ID
        $type = (int)trim($this->input->post('type', true));  //1 注册页， 2绑定修改手机号

        $tstr = $this->input->post('tstr', true);
        $vstr = $this->input->post('vstr', true);
        $tid = $this->input->post('tid', true);
        $kid = $this->input->post('kid', true);
        $ip = $this->input->ip_address();

        logs('|-找回密码动作检查参数-|  |-$mobile-|'.$mobile.'  |-$pCode-|'.$pCode.
            '  |-$mCode-|'.$mCode.'  |-$userID-|'.$userID.'  |-$type-|'.$type.
            '  |-$tstr-|'.$tstr.'  |-$vstr-|'.$vstr.'  |-$tid-|'.$tid.'  |-$kid-|'.$kid.
            '  |-$ip-|'.$ip,$logFile);
        //参数是否完整
        if(!$mobile || !$pCode || !$type || !$mCode  || !$userID  || !$tid || !$kid || !$tstr || !$vstr){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }

        //获取验证码请求过期, 半小时
        isRequestTimeout($tstr, 60*30, '请求已失效，请刷新页面重试');
        $qVer = md5(md5($userID . $kid) . $tid . md5($tstr . ENCODE_KEY));
        if($qVer != $vstr){
            echo json_encode(array('flag'=>'100031', 'msg'=>'校验错误')); exit;
        }

        $qKeyCount = cacheKeyCount($vstr);
        //pre($qKeyCount);
        if($qKeyCount['count'] > 15){ //一个key请求不能超过15次
            echo json_encode(array('flag'=>'10006', 'msg'=>'校验过期')); exit;
        }

        //图片验证码
        $cPCode = $this->cache->get($qVer . 'pCode');
        //pre($cPCode);
        if(!$cPCode){
            echo json_encode(array('flag'=>'10007', 'msg'=>'图片验证码不存在或已过期!')); exit;
        }

        if($cPCode != strtolower($pCode)){
            echo json_encode(array('flag'=>'10008', 'msg'=>'图片验证码错误1')); exit;
        }


        //短信验证码判断
        $m_key = SEND_SMS_SIGN . 'bj_bkpwd_check' . $userID  . $mobile;
        //pre($m_key);
        $vCode = $this->cache->get($m_key);
        //pre($vCode);
        if(!$vCode){
            echo json_encode(array('flag'=>'10007', 'msg'=>'短信验证码不存在或已过期')); exit;
        }
        if($mCode != $vCode){
            echo json_encode(array('flag'=>'10008', 'msg'=>'短信验证码错误')); exit;
        }
        //验证用户表中的短信验证码
        $cParam = array(
            'Type' => $type, //type=1修改手机号 type=2 找回密码
            'UserID' => $userID,
            'Mobile' => $mobile,
            'IP' => $ip,
            'CheckCode' => $mCode
        );
        $cRs = $this->User_Model->checkUserCode($cParam);
        logs('|-cParam-找回密码|' . print_r($cParam, true)  . '|-cRs-|' . print_r($cRs, true) . PHP_EOL,$logFile);

        if($cRs['Code'] == '00'){
            $rbk=$userID.sprintf('%06s', rand(1, 999999));
            //设置页面链接，验证乱码
            $vstr=md5( md5($userID . $rbk) . md5( $mobile . ENCODE_KEY ));
            $this->cache->set('vster_'.$userID.'_'.$rbk.'_'.$tstr,$vstr,6*60);
            //图片验证码清除
            $this->cache->set($qVer . 'pCode','',1);
            echo json_encode(array('flag'=>'10022', 'msg'=>$cRs['Msg'] , 'URL' => TRADE_WEB_URL.'/user/findpwdnew.html?rbk='.$rbk.'&mobile='.$mobile.'&mcode='.$mCode.'&tstr='.$tstr.'&vstr='.$vstr)); exit;
        } else {
            echo json_encode(array('flag'=>'10009', 'msg'=>$cRs['Msg'] )) ; exit;
        }

    }

    /*
     * 进入新密码页面
     * */
    public function intoNewpwdAction(){
        $this->_data['title'] = '找回密码_基金交易登录_伯嘉基金网';
        /*防止直接有人使用用户id，绕过前面的验证程序*/
        $tstr =$this->_data['tstr']= (int)($this->input->get('tstr', true));
        $vstr = $this->_data['vstr'] = trim($this->input->get('vstr', true));
        $rbk=$this->_data['rbk']=filter_slashes(trim($this->input->get('rbk', true)));
        $userID  =$this->_data['stut']= (int)(substr($rbk,0,count((string)$rbk)-7));
        $mCode=$this->_data['mcode'] = filter_slashes(trim($this->input->get('mcode', true))); //短信验证码
        $mobile = filter_slashes(trim($this->input->get('mobile', true))); //手机号

        //参数是否完整
        if(!$mobile || !$mCode  || !$userID || !$tstr || !$vstr){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }

        //链接过期
        isRequestTimeout($tstr, 60*30, '请求已失效，请刷新页面重试');

        //短信乱码验证
        $m_vstr='vster_'.$userID.'_'.$rbk.'_'.$tstr;
        $qVstr=$this->cache->get($m_vstr);
        if($vstr!=$qVstr || !$qVstr){
            echo json_encode(array('flag'=>'10023', 'msg'=>'非法链接进入！')); exit;
        }

        //短信验证码判断
        $m_key = SEND_SMS_SIGN . 'bj_bkpwd_check' . $userID  . $mobile;

        $vCode = $this->cache->get($m_key);
        if(!$vCode){
            echo json_encode(array('flag'=>'10007', 'msg'=>'短信验证码不存在或已过期!')); exit;
        }
        if($mCode != $vCode){
            echo json_encode(array('flag'=>'10008', 'msg'=>'短信验证码错误')); exit;
        }
        //短信验证码缓存删除
        $this->cache->set($m_key,'',1);

        //记录跳转时间
        $tstr2=$this->_data['tstr2']=time();

        //生成新的乱码
        $this->_data['vstr'] = $vstr=md5( md5($userID . $rbk) . md5( $tstr2 . ENCODE_KEY ));
        $this->cache->set('vster_'.$userID.'_'.$rbk.'_'.$tstr,$vstr,6*60);

        $this->load->view('_user/back_pwd_new.html',$this->_data);
    }
    /*
     * 验证新密码
     * */
    public function ajaxNewpwdAction(){
        /* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);

        $tstr = (int)($this->input->post('tstr', true));
        $tstr2 = (int)($this->input->post('tstr2', true));
        $password=filter_slashes(trim($this->input->post('password', true)));
        $vstr=filter_slashes(trim($this->input->post('vstr', true)));
        $rbk=filter_slashes(trim($this->input->post('rbk', true)));
        $userID  =$this->_data['stut']= (int)(substr($rbk,0,count((string)$rbk)-7));
        $PasswordCheck=filter_slashes(trim($this->input->post('mcode', true)));//短信验证码
        $ip = $this->input->ip_address();

        logs('|-验证新密码检查参数-|  |-$tstr-|'.$tstr.'  |-$tstr2-|'.$tstr2.
            '  |-$vstr-|'.$vstr.'  |-$rbk-|'.$rbk.'  |-$PasswordCheck-|'.$PasswordCheck.
            '  |-$ip-|'.$ip,$logFile);
        
        //参数是否完整
        if(!$userID || !$tstr || !$tstr2 || !$PasswordCheck || !$password || !$rbk){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        //链接过期
        isRequestTimeout($tstr2,60*30, '请求已失效，请刷新页面重试');

        //短信乱码验证
        $m_vstr='vster_'.$userID.'_'.$rbk.'_'.$tstr;
        $qVstr=$this->cache->get($m_vstr);
        if($vstr!=$qVstr || !$qVstr){
            echo json_encode(array('flag'=>'10023', 'msg'=>'非法链接进入！')); exit;
        }

        //密码判断
        $checkPwd =  checkPassword($password);
        if($checkPwd['flag'] != '10000'){
            echo json_encode($checkPwd); exit;
        }
        $cParam=array(
            'Type'=>2,
            'Password'=>$password,
            'UserID'=>$userID,
            'CheckCode'=>$PasswordCheck,
            'IP'=>$ip
        );

        $rst = $this->User_Model->updatePassWord($cParam);
        logs('|-$rst-密码修改|' . print_r($rst, true)  . PHP_EOL,$logFile);

        if($rst['Code']=='00'){
            //修改成功清除验证乱码
            $this->cache->set($m_vstr,'',1);
            echo json_encode(array('flag'=>$rst['Code'], 'msg'=>$rst['Msg'] , 'url' => TRADE_WEB_URL.'/user/findpwdsucceed.html')); exit;

        }else{
            echo json_encode(array('flag'=>$rst['Code'], 'msg'=>$rst['Msg']));
        }
    }
    /*
    *进入密码成功页面
    */
    public function intoSucpwdAction(){
        $this->_data['title'] = '找回密码_基金交易登录_伯嘉基金网';
        $this->load->view('_user/back_pwd_success.html',$this->_data);
    }

}

/* End of file stockquote.php */
/* Location: ./application/controllers/stockquote.php */