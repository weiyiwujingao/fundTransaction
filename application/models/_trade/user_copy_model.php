<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @ 用户信息模块
 *
 * @ Copyright (c) 2004-2012 CNFOL Inc. (http://www.cnfol.com)
 * @ version 3.0.1
 * @ author chensh
 */
 
class User_Copy_Model extends MY_Model {

    private $expire;
    private $iscache;
    private $getIPFromUrl = "http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=php";	//2014-11-23 IP登陆来源 1	-1	-1	澳大利亚

    
    const TBL_User2 = 'tbpassportuser';  //用户表
    const TBL_User = 'tbpassportuser';  //用户表
    const TBL_UserDetail = 'tbpassportuserdetail';  //用户详细表
    const TBL_UserDetail2 = 'tbpassportuserdetail';  //用户详细表
    const TBL_UserBank2 = 'tbpassportbank';  //用户详细表
    const TBL_UserBank = 'tbpassportbank';  //用户详细表
    const TBL_UserAuthenticate = 'tbpassportuserauthenticate';  //用户详细表
    const TBL_TmpMobile = 'tbpassporttmpmobile';  //手机临时表
    const TBL_UserGroup = 'tbpassportusergroup';  //用户组
    const TBL_UserLoginLog = 'tbpassportuserloginlog';  //登录记录
    const TBL_UserSMS = 'tbpassportsms';  //登录记录
    const TBL_TEA = 'teaccoinfo';  //登录记录

    
    function __construct() {
        parent::__construct();

    }
    
    /**
     * 
     * 获取用户信息
     *
     */
    public function getALLUser($where, $choose='UserID, UserName, NickName, Mobile, HsUserID, HsAccount, RegTime,RegIP,RegPlatFormType'){
        return $this->getDataAR(self::TBL_User, $where, $choose);
    }
    /**
     * 
     * 获取用户信息
     *
     */
    public function getALLTEA($where, $choose='VC_CUSTNO,VC_RESERVEWORDS'){
        return $this->getDataAR(self::TBL_TEA, $where, $choose);
    }
    
    /**
     * 
     * 获取用户信息
     *
     */
    public function getALLUserDetail($where, $choose='UserID, RiskBear'){
        return $this->getDataAR(self::TBL_UserDetail, $where);
    }

    /**
     * 
     * 获取用户信息
     *
     */
    private function _getUser($where, $choose='*'){
        return $this->getDataAR(self::TBL_User, $where, $choose, '', '', TRUE);
    }
    
    /**
     * 
     * 获取用户信息
     *
     */
    public function getUserAuth($where, $choose='TrueName, IdentityNumber'){
        return $this->getDataAR(self::TBL_UserAuthenticate, $where, $choose, '', '', TRUE);
    }
    
    /**
     *
     * 获取用户详细信息
     * 
     */
    private function _getUserDetail($where, $choose='*'){
        return $this->getDataAR(self::TBL_UserDetail, $where, $choose, '', '', TRUE);
    }
    
    
    
    /**
     * 
     * 用户表插入信息
     *
     */
    public function insertUser($param){
        return $this->insertTb(self::TBL_User, $param,true);  //添加用户
    }
    /**
     * 
     * 用户表插入信息
     *
     */
    public function insertUserAuth($param){
        return $this->insertTb(self::TBL_UserAuthenticate, $param,true);  //添加用户
    }
    /**
     * 
     * 用户表插入信息
     *
     */
    public function insertUserBank($param){
        return $this->insertTb(self::TBL_UserBank, $param,true);  //添加用户
    }
    /**
     * 
     * 用户表插入信息
     *
     */
    public function insertUserBank2($param){
        return $this->insertTb(self::TBL_UserBank2, $param,true);  //添加用户
    }
    
    /**
     *
     * 用户表 更新信息
     *
     */
    public function updateUserBank2($param, $where){
        
        return $this->updateTb(self::TBL_UserBank2, $param, $where);  //添加用户
    }
    
    public function getUserBank2($param){
        $cRs = $this->getDataAR(self::TBL_UserBank2, $param, 'UserID, HsAccount, AccountName','','',true);
        return $cRs;
    }
    
	/**
     * 
     * 用户表详细插入信息
     *
     */
    public function insertUserDetail($param){
        return $this->insertTb(self::TBL_UserDetail, $param,true);  //添加用户
    }
    
    /**
     *
     * 用户详情表修改信息
     *
     */
    private function _updateUserAuth($param, $where){
        $uRs = $this->updateTb(self::TBL_UserAuthenticate, $param, $where);
        if(isset($where['UserID']) && $uRs){
            $this->cache->set('u_userauthbyuserid_' . $where['UserID'], '', 1);
        }
        return $uRs;  //更新用户详情
    }
    
    /**
     *
     * 用户表 更新信息
     *
     */
    private function _updateUser($param, $where){
        return $this->updateTb(self::TBL_User, $param, $where);  //添加用户
    }
    /**
     *
     * 用户表 更新信息
     *
     */
    public function updateUser2($param, $where){
        
        return $this->updateTb(self::TBL_User2, $param, $where);  //添加用户
    }
    /**
     *
     * 用户表 更新信息
     *
     */
    public function updateUserDetail2($param, $where){
        
        return $this->updateTb(self::TBL_UserDetail2, $param, $where);  //添加用户
    }
    
    /**
     * 
     * 获取手机临时表信息
     * 
     */
    private function _getTmpMobile($param, $choose='*'){
        return $this->getDataAR(self::TBL_TmpMobile, $param, $choose, '', '', TRUE);
    }
    
    /**
     *
     * 手机临时表 插入信息
     *
     */
    private function _insertTmpMobile($param){
        return $this->insertTb(self::TBL_TmpMobile, $param);  //添加用户
    }
    
    /**
     *
     * 手机临时表 更新信息
     *
     */
    private function _updateTmpMobile($param, $where){
        return $this->updateTb(self::TBL_TmpMobile, $param, $where);  //添加用户
    }
    
    
    /**
     * 
     * 获取用户组表信息
     * 
     */
    private function _getUserGroup($param, $choose='*'){
        return $this->getDataAR(self::TBL_UserGroup, $param, $choose);
    }
    
    /**
     *
     * 用户组临时表 插入信息
     *
     */
    private function _insertUserGroup($param){
        return $this->insertTb(self::TBL_UserGroup, $param, false);  //添加用户
    }
    
    
    public function iGroup($userID){
        $ugParam = array(
            'UserID' => $userID,
            'GroupID' => 1,
            'DataTime' =>NOW_TIME
        );
        return $r=$this->_insertUserGroup($ugParam);
    }
    /**
     *
     * 登录记录表 插入信息
     *
     */
    private function _insertUserLoginLog($param){
        return $this->insertTb(self::TBL_UserLoginLog, $param);  //添加用户
    }
    
    /**
     *
     * 短信记录表 插入信息
     *
     */
    public function insertSMS($param){
        return $this->insertTb(self::TBL_UserSMS, $param);  //短信记录
    }

    
    /**
     *
     * 短信记录表 更新信息
     *
     */
    public function updateSMS($param, $where){
        $rs = $this->updateTb(self::TBL_UserSMS, $param, $where);
        return $rs;  //更新短信记录
    }

    /**
     * 
     * getEnvSetting 获取环境变量
     * 
     * @return unknown
     */
    public function getEnvSetting(){
        $m_key = 'trade_sys_envsetting';
        $env    = $this->cache->get($m_key);
        if(empty($rs)) {
            $env = $this->getDataAR('tbPassportEnvSetting', array('ID'=>'1'), 'LoginErrCnt,LoginErrRemark,IpRestrct');
            if($env) {
                $this->cache->set($m_key, $env, 10*60);//缓存设置10分钟
            }
        }
        
        return $env;        
    }
    
    /**
     * @ userLogin 用户登录
     *
     * @param array $param
     * Type 登录方式 0-用户名，1-用户昵称，2-模拟登录，3-手机，4-邮箱，5-新手机(包含未激活)
     * LoginName 登录内容
     * Password 密码
     * LoginIP 登录IP
     * LoginType 登录类别  1PC 2 手机网
     * LoginAddr 登录位置 1用户中心
     * @param string $md5str 加密串
     * @param int LoginPage 登录类型 1-中金 2-qq 3-sina 4-豆瓣
     * @access public
     * @return array
     */
    public function userLogin($param, $md5str) {
        
        $cmp = checkModelParam($param, array('Type', 'Password', 'LoginName', 'LoginIP', 'LoginType', 'LoginAddr'));
        
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }
        $loginPort = $_SERVER['REMOTE_PORT'];
        $param['LoginPort']= $loginPort;
        $param['LoginPage'] = 1; //此接口为中金帐号登录
        
        
        $cll = $this->_checkLoginLimitedIP($param['LoginIP']);
        //pre($cll);
        if($cll['flag'] != '10000'){
            return array('Code'=>$cll['flag'], 'Msg'=>$cll['msg'], 'Record' =>array());
        }

        $mRs = $this->_getUser(array('Mobile'=>$param['LoginName']), 'UserID, Password, UserName, NickName, Mobile, Status, RegTime, RegPlatFormType, RegIP, LastLoginIP, LastLoginTime, ActiveTime');


        if(!$mRs){
            return array('Code'=>'12', 'Msg'=>'您输入的帐号不存在，请确认后登录', 'Record' =>array());
        }
        
        if($mRs['Status'] != '1'){
            return array('Code'=>'100001', 'Msg'=>'您使用的帐号处于非正常状态', 'Record' =>array());
        }

        $cKey = 'hkLoginErrorCount_' . $param['LoginIP']; //登录次数记录
        $badKey = 'hkerrorloginip_' . $param['LoginIP']; //登录记录错误超过5次记录
        //pre($param['Password']);
        //pre($mRs);
        $tPassword = $this->_makePswd($param['Password'], $mRs['RegTime'], $mRs['RegIP'], $mRs['RegPlatFormType']);
        //pre($tPassword);
        if($tPassword != $mRs['Password']){

            $aOkObj = $this->config->item('whiteIPs');
            //pre($aOkObj);
            if(!in_array($param['LoginIP'], $aOkObj)){
                $errorCount = (int)$this->cache->get($cKey);
                if($errorCount > 5 ){
                    if($errorCount == 6){
                        //记录错误IP
                        $this->_updateUser(array('ErrLoginIP'=>$param['LoginIP']), array('UserID'=>$mRs['UserID']));
                    }
                    $errorCount ++;
                    $this->cache->set($badKey, 1, 60*60*24);
                    $this->cache->set($cKey, $errorCount, 60*60*24);
                    return array('Code'=>'100221', 'Msg'=>'登录失败已超过5次，请稍后再试', 'Record' =>array());
                }
                $errorCount ++;
                $this->cache->set($cKey, $errorCount, 60*60*24);
            }

            return array('Code'=>'100013', 'Msg'=>'账号或密码错误，请确认后登录', 'Record' =>array());
        }
        cleanCache($cKey); //清登录次数记录

        //获取组别
        $gRs = $this->getGroup(array('UserID'=> $mRs['UserID']));
        if($gRs['Code'] != '00'){
            return $gRs;
        }

        $mRs['GroupIDs'] = $aGroup = $gRs['Record'];
        if(!in_array(1, $aGroup)){
            return array('Code'=>'100001', 'Msg'=>'您使用的帐号处于非正常状态2', 'Record' =>array());
        }
        //pre($groupRs);
        //pre($aGroup);
        //exit;

        // 更新keystr, LastLoginIP, LastLoginTime
        $tid = time();
        $now = date('Y-m-d H:i:s');
        $userID = $mRs['UserID'];
        $keyStr = $this->_makeKeystr($tid, $param['LoginIP'], $userID);
        
        $uuParam = array(
            'KeyStr' => $keyStr,
            'LastLoginIP' => $param['LoginIP'],
            'ActiveTime' => $now,
            'LastLoginTime' => $now
        );
        if(!$this->_updateUser($uuParam, array('UserID'=>$mRs['UserID']))){
            return array('Code'=>'11', 'Msg'=>'数据更新失败', 'Record' =>array());
        }
        if(empty($md5str)) {
            $m_key = 'u_'.$md5str.'_userlogininfo';
            $this->cache->set($m_key, '');
        }
        $m_key = 'hk_userbasebyuserid_' . $userID;
        $this->cache->set($m_key, '');

        $ulParam = array(
            'UserID' => $userID,
            'LoginType'=>$param['LoginType'],
            'LoginIP'=>$param['LoginIP'],
            'LoginPage'=>$param['LoginPage'],
            'LoginAddr' =>$param['LoginAddr'],
            'LoginTime' => $now,
            'LoginPort' => $loginPort
        );

        $ir = $this->_insertUserLoginLog($ulParam);

        $mRs['KeyStr'] = $keyStr;
        unset($mRs['Password'], $mRs['RegTime'], $mRs['RegPlatFormType'], $mRs['RegIP']);
        return array('Code'=>'00', 'Msg'=>'登录成功', 'Record'=>$mRs);
    }
    

    /**
     * 
     * 判断用户是否登录失败超过5次
     * @param unknown $ip
     * @return multitype:string
     */
    private function _checkLoginLimitedIP($ip){
        $rData = array('flag'=>'10000', 'msg'=>'ok');
        //白名单判断
        $aOkObj = $this->config->item('whiteIPs');
        //pre($aOkObj);
    	if(in_array($ip, $aOkObj)){
                return $rData;
    	}
    	//黑名单判断
    	$aBlackObj = $this->config->item('blackIPs');
    	if(in_array($ip, $aBlackObj)){
    	    $msg = '亲，登录太过于频繁！';
    	    $rData = array('flag'=>'100222', 'msg'=>$msg);
    	    return $rData;
    	}
    	
    	$mKey = 'hkerrorloginip_' . $ip;
    	//pre($mKey);
    	//pre($this->cache->get($mKey));
    	if($this->cache->get($mKey)){
    	    $msg = '登录失败已超过5次，请稍后再试';
    	    $rData = array('flag'=>'100221', 'msg'=>$msg);
    	    return $rData;
    	}
    	
    	return $rData;
    }
    
    /**
     * 
     * 创建密码
     * 
     */
    private function _makePswd($password, $regTime, $regIP, $regPlatFormType){
        $rPassword = md5(substr(md5($regPlatFormType . $password), 10) . substr(md5($regTime . base64_encode($regIP)), -10) . LOGIN_LIMIT_KEY);
        return $rPassword;
    }
    
    /**
     * 
     * 创建keystr
     * 
     */
    public function _makeKeystr($tid, $ip, $userID){
        $rKeystr = md5(makeRandChar('10') . rand(0, 99999) . substr(md5($tid), 10) . substr(md5($userID . base64_encode($ip)), -10) . LOGIN_LIMIT_KEY . md5(makeRandChar()));
        return $rKeystr;
    }
    
    /**
     * @ userReg 用户注册
     *
     * @param array $param
     * Type 注册方式 0-用户名，1-邮箱，2-手机
     * UserName 用户名(三选一)
     * Email 邮件(三选一)
     * Mobile 手机(三选一)
     * EMailCheck 是否产生邮件验证码 0-否，1-是
     * MobileCheck 是否产生手机验证码 0-否，1-是
     * NickName 昵称
     * Password 密码
     * IP 注册IP
     * HeadIco 是否有头像 0-否，1-是
     * Point 初始化积分(选填)
     * Money 初始化金币(选填)
     * UserGroup 用户组(选填)
     * @access public
     * @return array
     */
    public function userReg($param) {

        $cmp = checkModelParam($param, array('Type', 'Password', 'RegPlatFormType', 'RegTime', 'RegIP', 'GroupID', 'Status'));

        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }

        if($param['Type'] == 1){
            if(!isset($param['Mobile']) || !$param['Mobile']){
                return array('Code'=>'10003', 'Msg'=>'参数缺失, Mobile', 'Record' =>array());
            }
        }
        
        $mRs = $this->_getUser(array('Mobile'=>$param['Mobile']), 'UserID');
        if($mRs && isset($mRs['UserID']) && $mRs['UserID']>0){
            return array('Code'=>'01', 'Msg'=>'手机号已被注册', 'Record' =>array());
        }
        unset($mRs);
        //$nRs = $this->_getUser(array('NickName'=>$param['NickName']), 'UserID');
        //if($nRs && isset($nRs['UserID']) && $nRs['UserID']>0){
            //return array('Code'=>'01', 'Msg'=>'昵称已被注册', 'Record' =>array());
        //}

        $now = date('Y-m-d H:i:s');
        
        $password = $this->_makePswd($param['Password'], $param['RegTime'], $param['RegIP'], $param['RegPlatFormType']);

        $iuParam = array(
            'Mobile' => $param['Mobile'],
            'Password' => $password,
            'RegPlatFormType' => $param['RegPlatFormType'],
            'RegTime' => $param['RegTime'],
            'ActiveTime' => $now,
            'RegIP' => $param['RegIP'],
            'Status' => $param['Status'],
        );
    
        //pre($iParam);
        if(!$userID = $this->_insertUser($iuParam)){
            return array('Code'=>'11', 'Msg'=>'用户信息添加失败', 'Record' =>array());
        }
        
        //$userID = (int)$this->mdb->insert_id();
        $uType = array(1=>'CM-', 2=>'QQ-', 3=>'SINA-', 4=>'WX-');
        $username = $uType[$param['Type']] . $userID;
        $keystr = $this->_makeKeystr(time(), $param['RegIP'], $userID);
        if(!$this->_updateUser(array('UserName'=> $username, 'KeyStr'=>$keystr), array('UserID'=>$userID))){
            return array('Code'=>'11', 'Msg'=>'用户名更新失败', 'Record' =>array());
        }
        
        $ugParam = array(
            'UserID' => $userID,
            'GroupID' => $param['GroupID'],
            'DataTime' =>$now
        );
        if(!$this->_insertUserGroup($ugParam)){
            return array('Code'=>'11', 'Msg'=>'组别添加失败', 'Record' =>array());
        }
        
        return array('Code'=>'00', 'Msg'=>'用户注册成功', 'Record' =>array());

    }
    public function userLoginLog($param){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__) . '_' . date('Ymd') . '.log';
        $cmp = checkModelParam($param, array('UserID','LoginType', 'LoginIP', 'LoginPage', 'LoginAddr'));
        
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }
        
        // 更新keystr, LastLoginIP, LastLoginTime
        $tid = time();
        $now = date('Y-m-d H:i:s');
        $keyStr = $this->_makeKeystr($tid, $param['LoginIP'], $param['UserID']);
        
        $uuParam = array(
            'KeyStr' => $keyStr,
            'LastLoginIP' => $param['LoginIP'],
            'ActiveTime' => $now,
            'LastLoginTime' => $now
        );
        if(!$this->_updateUser($uuParam, array('UserID'=>$mRs['UserID']))){
            return array('Code'=>'11', 'Msg'=>'数据更新失败', 'Record' =>array());
        }
        
        $param['LoginPort'] = $_SERVER['REMOTE_PORT'];
        //pre($param);
        //pre($this->_insertUserLoginLog($param));
        //exit;
        $ir = $this->_insertUserLoginLog($param);
        @error_log(date('Y-m-d H:i:s').'|-param-|' . print_r($param, true) . '|-ir-|' . print_r($ir, true) . PHP_EOL,3,LOG_PATH. '/'. $logFile);
        
        if(!$ir){
            return array('Code'=>'11', 'Msg'=>'登录日志记录失败', 'Record' =>array());
        }
        return array('Code'=>'00', 'Msg'=>'登录日志记录成功', 'Record' =>array());
    }
    


    
    /**
     * @ checkUserExist 检查用户是否存在
     *
     * @param array $param
     * Type 检查方式 0-用户编号，1-用户名，2-用户昵称，3-手机，4-邮箱，5-密码
     * CheckData 检查内容
     * UserID 用户编号(选填,当Type=5时为必填)
     * UserName 用户名(选填,当Type=5时为必填)
     * @access public
     * @return array
     */
    public function checkUserExist($param) {
        //$type = 'U002';
        $cmp = checkModelParam($param, array('Type', 'CheckData'));
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());    
        }
        
        switch ($param['Type'] ) {
            case 1: //用户ID
                $where = array('UserID'=>$param['CheckData']);
            break;
            case 2: //用户名
                $where = array('UserName'=>$param['CheckData']);
            break;
            case 3: //昵称
                $where = array('NickName'=>$param['CheckData']);
            break;
            case 4: //中国大陆手机
                //$where = array('Mobile'=>$param['CheckData'], 'MobileArea'=>'1');
                $where = array('Mobile'=>$param['CheckData']);
            case 5: //香港手机
                //$where = array('Mobile'=>$param['CheckData'], 'MobileArea'=>'2');
                $where = array('Mobile'=>$param['CheckData']);
            break;
            default:
                return array('Code'=>'10003', 'Msg'=>'参数错误,Type', 'Record' =>array());
        }
        //pre(date('Y-m-d H:i:s'));
        $cRs = $this->_getUser($where, 'count(*) as count');
        if($cRs && isset($cRs['count'])){
                    
            if($cRs['count'] == '0'){
                return array('Code'=>'12', 'Msg'=>'没有记录', 'Record'=>array());
            } else {
                unset($cRs);
                $uRs = $this->_getUser($where, 'UserID');
                //pre($uRs);
                if($uRs && isset($uRs['UserID'])){
                    return array('Code'=>'00', 'Msg'=>'有记录', 'Record'=>array('UserID'=>(int)$uRs['UserID']));
                } else {
                    return array('Code'=>'11', 'Msg'=>'数据库查询错误2', 'Record' =>array());
                }
                
            }
        } else {
            return array('Code'=>'11', 'Msg'=>'数据库查询错误', 'Record' =>array());
        }
        //pre($rs);
        //pre(date('Y-m-d H:i:s'));
        //pre($rs);
        exit;
        
        
        
        return $rs;
    }

 
    /**
     *
     * 
     * 获取手机口令
     *
     */
    public function updateMobileVerifyCode($param){
    
        $cmp = checkModelParam($param, array('Type', 'Mobile', 'IP'));
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }
        
        
        if($param['Type'] == 1){ //手机注册，绑定手机号
            $tmParam = array(
                'Mobile' => $param['Mobile'],
                'Status !=' => 1,
            );
            $tmRs = $this->_getTmpMobile($tmParam, 'TmpMobileID, RequestTime');
            
            if(!$tmRs){ //没记录, 插入一条记录
                $mobileCheck = sprintf('%06s', rand(1, 999999));
                $iParam = array(
                    'Mobile' => $param['Mobile'],
                    'MobileCheck' => $mobileCheck,
                    'RequestTime' => date('Y-m-d H:i:s'),
                    'RequestIP' => $param['IP'],
                    'Status' =>0 //状态 0未激活， 1已使用
                );
                //pre($iParam);
                if(! $tmpMobileID = $this->_insertTmpMobile($iParam)){
                    return array('Code'=>'11', 'Msg'=>'数据添加失败', 'Record' =>array());
                }
            
                //$tmpMobileID = (int)$this->mdb->insert_id();
            
                return array('Code'=>'00', 'Msg'=>'成功', 'Record' => array('MobileCheck'=>$mobileCheck, 'TmpMobileID' =>$tmpMobileID ));
            
            } else if($tmRs && isset($tmRs['TmpMobileID'])){

                
                if(abs(strtotime($tmRs['RequestTime']) - time())<30  ) {
                    //return array('Code'=>'101', 'Msg'=>'30秒内只能更新一次验证码', 'Record' =>array());
                }
                
                //有记录，未验证
                $mobileCheck = sprintf('%06s', rand(1, 999999));
                $iParam = array(
                    'MobileCheck' => $mobileCheck,
                    'RequestTime' => date('Y-m-d H:i:s'),
                    'RequestIP' => $param['IP'],
                    'Status' =>0, //状态 0未激活， 1已激活
                );
                $tmpMobileID = $tmRs['TmpMobileID'];
                if(!$this->_updateTmpMobile($iParam, array('TmpMobileID'=>$tmpMobileID))){
                    return array('Code'=>'11', 'Msg'=>'数据更新失败', 'Record' =>array());
                }
                return array('Code'=>'00', 'Msg'=>'成功', 'Record' => array('MobileCheck'=>$mobileCheck, 'TmpMobileID' =>$tmpMobileID ));

            
            } else {
                return array('Code'=>'11', 'Msg'=>'数据查询失败', 'Record' =>array());
            }
        }

    
    }
  
    /**
     * 
     * 更新用户表
     * 找回密码获取口令
     * 
     */
    public function updateUserVerifyCode($param){
    
        $cmp = checkModelParam($param, array('Type', 'Mobile', 'IP', 'UserID'));
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }
        
        $uParam = array(
            'Mobile' => $param['Mobile'],
            'UserID' => $param['UserID']
        );
        //判断用户是否存在
        $uRs = $this->_getUser($uParam);
        if(!$uRs){
            $rs = array('Code'=>'12', 'Msg'=>'信息不存在');
        } else {

            $checkCode = sprintf('%06s', rand(1, 999999));
            if($param['Type'] == 1){
                $upParam = array(
                    'MobileCheck' => $checkCode
                );
            } else if($param['Type'] == 2){
                $upParam = array(
                    'PasswordCheck' => $checkCode
                );
            }

            if(!$this->_updateUser($upParam, array('UserID'=>$param['UserID']) )){
                return array('Code'=>'11', 'Msg'=>'数据更新失败', 'Record' =>array());
            }
            return array('Code'=>'00', 'Msg'=>'成功', 'Record' => array('CheckCode'=>$checkCode, 'UserID' =>$param['UserID'] ));
            
        }

    }
    
    /**
     * @ checkUserReg 验证用户注册验证码
     *
     * @param array $param
     * UserID 用户编号
     * Type 检查方式 0-邮件验证码，1-短信验证码
     * CheckData 检查内容
     * @access public
     * @return mixed
     */
    public function checkMobileCode($param) {
        $cmp = checkModelParam($param, array('Type', 'Mobile', 'MobileCheck', 'TmpMobileID', 'IP'));
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }
        
        if($param['Type'] == 1){ //手机注册，绑定手机号
            
            $tmParam = array(
                'TmpMobileID' => $param['TmpMobileID'],
                'Mobile' => $param['Mobile'],
                'MobileCheck' => $param['MobileCheck']
            );
            //$tmParam = array(
            //'Mobile' => $param['Mobile'],
            //'MobileArea' => $param['MobileArea']
            //);
            //pre($tmParam);
            $tmRs = $this->_getTmpMobile($tmParam, 'TmpMobileID, RequestTime');
            //pre($tmRs);
            
            if(!$tmRs){ //没记录
            
                return array('Code'=>'100010', 'Msg'=>'验证码不正确，请核对您的验证码！', 'Record' => array());
            
            } else if($tmRs && isset($tmRs['TmpMobileID'])){

                $rTime = strtotime($tmRs['RequestTime']);
                if(time() - $rTime > 300 || $rTime-time()>300){
                    return array('100009'=>'11', 'Msg'=>'验证码已失效或已验证过！', 'Record' =>array());
                }
        
                //将记录改成已使用
                $iParam = array(
                    'ActiveTime' => date('Y-m-d H:i:s'),
                    'ActiveIP' => $param['IP'],
                    'MobileCheck' => '',
                    'Status' =>1, //状态 0未激活， 1已使用
                );
                $tmpMobileID = $tmRs['TmpMobileID'];
                if(!$this->_updateTmpMobile($iParam, array('TmpMobileID'=>$tmpMobileID))){
                    return array('Code'=>'11', 'Msg'=>'数据更新失败', 'Record' =>array());
                }
                return array('Code'=>'00', 'Msg'=>'成功', 'Record' => array('TmpMobileID' =>$tmpMobileID, 'ActiveTime' => $iParam['ActiveTime']));

        
            } else {
                return array('Code'=>'11', 'Msg'=>'数据查询失败', 'Record' =>array());
            }
        } 

    }
    
    /**
     * 
     * 验证用户表的验证码
     * @param unknown $param
     */
    public function checkUserCode($param){
        $cmp = checkModelParam($param, array('Type', 'CheckCode', 'UserID', 'IP'));
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }
        
        if($param['Type'] == 1) { //修改手机号
            $uParam = array(
                'UserID' => $param['UserID'],
                'MobileCheck' => $param['CheckCode']
            );

        } else if($param['Type'] == 2){//找回密码
            $uParam = array(
                'UserID' => $param['UserID'],
                'PasswordCheck' => $param['CheckCode']
            );
        }
        
        $cRs = $this->_getUser($uParam, 'count(*) as count');
        
        if($cRs && isset($cRs['count'])){
        
            if($cRs['count'] == '0'){
                return array('Code'=>'12', 'Msg'=>'验证码错误', 'Record'=>array());
            } else {
                unset($cRs);
                return array('Code'=>'00', 'Msg'=>'成功', 'Record' => array());
        
            }
        } else {
            return array('Code'=>'11', 'Msg'=>'数据库查询错误', 'Record' =>array());
        }
    }
    
    
    /**
     *
     * 用户中心修改密码
     */
    public function changePswd($param){
        $cmp = checkModelParam($param, array('OldPassword', 'NewPassword', 'UserID', 'IP'));
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }
        $uParam = array(
            'UserID' => $param['UserID']
        );
        
        $cRs = $this->_getUser($uParam, 'UserID, Password, RegTime, RegPlatFormType, RegIP');
    
        if($cRs && isset($cRs['UserID']) && $cRs['UserID']>0){
            
            $oMPassword = $this->_makePswd($param['OldPassword'], $cRs['RegTime'], $cRs['RegIP'], $cRs['RegPlatFormType']);
            if($oMPassword != $cRs['Password']){
                return array('Code'=>'15', 'Msg'=>'旧密码错误', 'Record'=>array());
            }
            $uParam = array(
                'Password' => $this->_makePswd($param['NewPassword'], $cRs['RegTime'], $cRs['RegIP'], $cRs['RegPlatFormType']),
                'KeyStr' => $this->_makeKeystr(time(), $param['IP'], $param['UserID'])
            );
            
            unset($cRs);
            if(!$this->_updateUser($uParam, array('UserID'=>$param['UserID']) )){
                return array('Code'=>'11', 'Msg'=>'数据更新失败', 'Record' =>array());
            }
            
            return array('Code'=>'00', 'Msg'=>'成功', 'Record' => array());

        } else {
            return array('Code'=>'12', 'Msg'=>'用户信息错误', 'Record'=>array());
        }


    }
    /**
     *
     * 找回密码修改密码
     */
    public function updatePassWord($param){
        $cmp = checkModelParam($param, array('Type', 'CheckCode', 'UserID', 'Password', 'IP'));
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }
        if($param['Type'] == 2) { //找回密码
            $uParam = array(
                'UserID' => $param['UserID'],
                'PasswordCheck' => $param['CheckCode']
            );
            
            $cRs = $this->_getUser($uParam, 'UserID, RegTime, RegPlatFormType, RegIP');
        
            if($cRs && isset($cRs['UserID']) && $cRs['UserID']>0){

                $uParam = array(
                    'Password' => $this->_makePswd($param['Password'], $cRs['RegTime'], $cRs['RegIP'], $cRs['RegPlatFormType']),
                    'PasswordCheck' => ''
                );
                
                unset($cRs);
                if(!$this->_updateUser($uParam, array('UserID'=>$param['UserID']) )){
                    return array('Code'=>'11', 'Msg'=>'数据更新失败', 'Record' =>array());
                }
                
                return array('Code'=>'00', 'Msg'=>'成功', 'Record' => array());

            } else {
                return array('Code'=>'12', 'Msg'=>'验证码错误', 'Record'=>array());
            }
        
        }

    }
    
    /**
     * @ getUserBase 获取用户基本信息公共函数
     *
     * @param array $param
     * UserID 用户编号
     * UserName 用户名
     * Mobile 手机
     * Email 邮箱
     * @access public
     * @return array
     */
    public function getUserBase($param, $choose = 'UserID, UserName, NickName, Mobile, Status, LastLoginIP, LastLoginTime, ActiveTime, KeyStr') {

        if(isset($param['UserID'])){
            $where = array('UserID'=>$param['UserID']);
            
        } else if(isset($param['UserName'])){
            $where = array('UserName'=>$param['UserName']);
            
        } else if(isset($param['Mobile'])){
            $where = array('Mobile'=>$param['Mobile']);
            
        } else if(isset($param['NickName'])){
            $where = array('NickName'=>$param['NickName']);
            
        } else if(isset($param['HsUserID'])){
            $where = array('HsUserID'=>$param['HsUserID']);
            
        } else {
            return array('Code'=>'03', 'Msg'=>'参数错误', 'Record' =>array());
        }

        $mRs = $this->_getUser($where, $choose);
        
        if(!$mRs){
            return array('Code'=>'12', 'Msg'=>'用户信息不存在', 'Record' =>array());
        }
        //获取组别
        $gRs = $this->getGroup(array('UserID'=> $mRs['UserID']));
        if($gRs['Code'] != '00'){
            return $gRs;
        }
        $mRs['GroupIDs'] = $gRs['Record'];
        
        return array('Code'=>'00', 'Msg'=>'用户信息获取成功', 'Record' =>$mRs);
    }
    
    
    public function getUserBaseByUserID($userID){

        $m_key = 'bj_userbasebyuserid_' . $userID;
        $rs = $this->cache->get($m_key);
        //$rs = '';
        if(!$rs){
            $param = array('UserID' => $userID);
            $uRs = $this->getUserBase($param);
            if($uRs['Code'] == '00' && $uRs['Record']){
                $rs = $uRs['Record'];
                $this->cache->set($m_key, $rs, 30*60); //缓存半小时
            }
        }
        return $rs;
    }
    
    /**
     * 
     * 通过用户ID获取用户详细信息
     * 
     */
    public function getUserDetailByUserID($userID){
        $m_key = 'bj_userdetailbyuserid_' . $userID;
        $rs = $this->cache->get($m_key);
        //$rs = '';
        if(!$rs){
            $param = array('UserID' => $userID);
            $rs = $this->_getUserDetail($param, '*');
            if($rs){
                $this->cache->set($m_key, $rs, 30*60); //缓存半小时
            }
        }
        return $rs;
    }
    
    /**
     * 
     * 获取组别
     * 
     */
    public function getGroup($param){
        $cmp = checkModelParam($param, array('UserID'));
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }
        
        $groupRs =  $this->_getUserGroup($param, 'UserID, GroupID');
        if(!$groupRs){
            return array('Code'=>'11', 'Msg'=>'获取组别错误', 'Record' =>array());
        }
        $aGroup = array();
        foreach ($groupRs as $gk => $gv){
            $aGroup[] = $gv['GroupID'];
        }
        return array('Code'=>'00', 'Msg'=>'ok', 'Record' =>$aGroup);
    }
    
    
    /**
     * @ getUserLoginInfo 获取用户在线信息
     *
     * @param array $param
     * UserID 用户编号
     * Operator 操作类型 0-查询，1-更新
     * KeyStr 密钥 选填
     * @access public
     * @return array
     */
    public function getUserLoginInfo($param, $md5str) {
        
        $cmp = checkModelParam($param, array('UserID', 'KeyStr'));
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }
        
        $m_key = 'u_'.$md5str.'_userlogininfo';
        $rs    = $this->cache->get($m_key);
        
        if(empty($rs)) {
            $uParam = array(
                'UserID' => $param['UserID'],
                'KeyStr' => $param['KeyStr']
            );
            $uRs = $this->_getUser($uParam, 'UserID, KeyStr, ActiveTime');
            if(!$uRs){
                $rs = array('Code'=>'12', 'Msg'=>'信息不存在');
            } else {

                $rs = array('Code'=>'00', 'Msg'=>'ok', 'Record' => $uRs);
                $this->cache->set($m_key, $rs, $this->expire);
            }
        }
    
        return $rs;
    }
    

    /**
     * 
     * 修改手机号,生成验证码
     */
    public function updateTempCode($param){
        $cmp = checkModelParam($param, array('UserID', 'Mobile', 'CheckCode', 'IP'));
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }

        $uParam = array(
            'UserID' => $param['UserID'],
            'MobileCheck' => $param['CheckCode']
        );

        $cRs = $this->_getUser($uParam, 'count(*) as count');
        if($cRs && isset($cRs['count'])){
        
            if($cRs['count'] == '0'){
                return array('Code'=>'12', 'Msg'=>'验证码错误', 'Record'=>array());
            } else {
                unset($cRs);
                
                $tmParam = array(
                    'Mobile' => $param['Mobile'],
                    'Status!=' => 1,
                );
                $tmRs = $this->_getTmpMobile($tmParam, 'TmpMobileID, Status');
 
                //pre($tmRs);
                if(!$tmRs){ //没记录, 插入一条记录
                    $mobileCheck = sprintf('%06s', rand(1, 999999));
                    $iParam = array(
                        'Mobile' => $param['Mobile'],
                        'MobileCheck' => $mobileCheck,
                        'UserID' => $param['UserID'],
                        'RequestTime' => date('Y-m-d H:i:s'),
                        'RequestIP' => $param['IP'],
                        'Status' =>0 //状态 0未激活， 1已使用
                    );
                    //pre($iParam);
                    if(!$tmpMobileID = $this->_insertTmpMobile($iParam)){
                        return array('Code'=>'11', 'Msg'=>'数据添加失败', 'Record' =>array());
                    }

                
                    return array('Code'=>'00', 'Msg'=>'成功', 'Record' => array('MobileCheck'=>$mobileCheck, 'TmpMobileID' =>$tmpMobileID ));
                
                } else if($tmRs && isset($tmRs['Status'])){
                
                    //有记录，未验证
                    $mobileCheck = sprintf('%06s', rand(1, 999999));
                    $iParam = array(
                        'MobileCheck' => $mobileCheck,
                        'UserID' => $param['UserID'],
                        'RequestTime' => date('Y-m-d H:i:s'),
                        'RequestIP' => $param['IP'],
                        'Status' =>0, //状态 0未激活， 1已激活
                    );
                    $tmpMobileID = $tmRs['TmpMobileID'];
                    if(!$this->_updateTmpMobile($iParam, array('TmpMobileID'=>$tmpMobileID))){
                        return array('Code'=>'11', 'Msg'=>'数据更新失败', 'Record' =>array());
                    }
                    return array('Code'=>'00', 'Msg'=>'成功', 'Record' => array('MobileCheck'=>$mobileCheck, 'TmpMobileID' =>$tmpMobileID ));
                
                
                } else {
                    return array('Code'=>'11', 'Msg'=>'数据查询失败', 'Record' =>array());
                }
                return array('Code'=>'00', 'Msg'=>'成功', 'Record' => array());
        
            }
        } else {
            return array('Code'=>'11', 'Msg'=>'数据库查询错误', 'Record' =>array());
        }
    }
    
    public function CheckTempCode($param){
        
        $cmp = checkModelParam($param, array('Type', 'UserID', 'TmpMobileID', 'Mobile', 'MobileCheck', 'IP'));
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }


        $tmParam = array(
            'Mobile' => $param['Mobile'],
            'MobileCheck' => $param['MobileCheck'],
            'UserID' => $param['UserID'],
            'Status!=' => 1,
        );

        $tmRs = $this->_getTmpMobile($tmParam, 'TmpMobileID, RequestTime');

        
        if(!$tmRs){ //没记录
        
            return array('Code'=>'100010', 'Msg'=>'验证码不正确，请核对您的验证码！', 'Record' => array());
        
        } else if($tmRs && isset($tmRs['TmpMobileID'])){
        
            $rTime = strtotime($tmRs['RequestTime']);
            if(time() - $rTime > 300 || $rTime-time()>300){
                return array('100009'=>'11', 'Msg'=>'验证码已失效或已验证过！', 'Record' =>array());
            }
        
            //将记录改成已使用
            $iParam = array(
                'ActiveTime' => date('Y-m-d H:i:s'),
                'ActiveIP' => $param['IP'],
                'MobileCheck' => '',
                'Status' =>1, //状态 0未激活， 1已使用
            );
            $tmpMobileID = $tmRs['TmpMobileID'];
            if(!$this->_updateTmpMobile($iParam, array('TmpMobileID'=>$tmpMobileID))){
                return array('Code'=>'11', 'Msg'=>'数据更新失败', 'Record' =>array());
            }
            
            $uuParam = array(
                'Mobile' => $param['Mobile'],
                'MobileCheck' => '',
            );
            
            if(!$this->_updateUser($uuParam, array('UserID'=>$param['UserID']))){
                return array('Code'=>'11', 'Msg'=>'用户名更新失败', 'Record' =>array());
            }
            
            $m_key = 'hk_userbasebyuserid_' . $param['UserID'];
            $this->cache->set($m_key, '');
        
            return array('Code'=>'00', 'Msg'=>'成功', 'Record' => array('TmpMobileID' =>$tmpMobileID, 'ActiveTime' => $iParam['ActiveTime']));
        
        
        } else {
            return array('Code'=>'11', 'Msg'=>'数据查询失败', 'Record' =>array());
        }
        
    }

    public function riskQry($param){
        $sign = 'paperinfo_qry_acct';
        $rs = $this->curlPostUFX($param, $sign, $this->access_token);
        return $rs;
    }
    
    /**
     * 
     * 短信签约
     * 
     */
    public function sign_sms($param){
        $sign = 'pay_sign_contract_sms';
        $rs = $this->curlPostUFX($param, $sign, 'pay');
        return $rs;
    }
    
}//end class
?>