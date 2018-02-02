<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @ 用户信息模块
 *
 * @ Copyright (c) 2004-2012 CNFOL Inc. (http://www.cnfol.com)
 * @ version 3.0.1
 * @ author chensh
 */
 
class User_Model extends MY_Model {

    private $expire;
    protected $access_token;
    
    const TBL_User = 'tbpassportuser';  //用户表
    const TBL_UserDetail = 'tbpassportuserdetail';  //用户详细表
    const TBL_TmpMobile = 'tbpassporttmpmobile';  //手机临时表
    const TBL_UserGroup = 'tbpassportusergroup';  //用户组
    const TBL_UserLoginLog = 'tbpassportuserloginlog';  //登录记录
    const TBL_UserSMS = 'tbpassportsms';  //登录记录
    const TBL_Bank = 'tbpassportbank';  //银行绑定信息
    const TBL_UserAuthenticate = 'tbpassportuserauthenticate';  //实名认证信息
    const TBL_Oauth = 'tbaccountoauth';  //第三方登录用户映射表



    function __construct() {
        parent::__construct();
        $this->access_token = getAccessToken(__METHOD__);
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
     * 获取用户实名信息
     *
     */
    private function _getUserAuth($where, $choose='*'){
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
    private function _insertUser($param){
        return $this->insertTb(self::TBL_User, $param);  //添加用户
    }
    
    
    /**
     * 
     * 用户详情表插入信息
     *
     */
    private function _insertUserDetail($param){
        return $this->insertTb(self::TBL_UserDetail, $param,false);  //添加用户详情
    }
    /**
     * 
     * 用户详情表修改信息
     *
     */
    private function _updateUserDetail($param, $where){
        $uRs = $this->updateTb(self::TBL_UserDetail, $param, $where);
        if(isset($where['UserID']) && $uRs){
            $this->cache->set('u_userdetailbyuserid_' . $where['UserID'], '', 1);
        }
        return $uRs;  //更新用户详情
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
        $uRs = $this->updateTb(self::TBL_User, $param, $where);
        if(isset($where['UserID']) && $uRs){
            $this->cache->set('u_userbasebyuserid_' . $where['UserID'], '', 1);
        }
        return $uRs;  //更新用户
    }
    /**
     *
     * 银行表 更新信息
     *
     */
    private function _updateBank($param, $where){
        $uRs = $this->updateTb(self::TBL_Bank, $param, $where);
        if(isset($where['UserID']) && $uRs){
            $this->cache->set('u_userbankbyuserid_' . $where['UserID'], '', 1);
        }
        return $uRs;  //更新用户
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
        return $this->insertTb(self::TBL_UserGroup, $param);  //添加用户
    }
    
    /**
     *
     * 登录记录表 插入信息
     *
     */
    private function _insertUserLoginLog($param){
        $flag =  false;
        $iRs = $this->insertTb(self::TBL_UserLoginLog, $param);  //添加用户;
        
        if($iRs){
            $this->cache->set('u_useronlineact_' . $param['UserID'], time(), USER_ACTION_PERIOD); //用户在线操作缓存设置，周期时间内不操作，自动退出
            $flag =  true;
        }
        
        return $flag;
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
    * @ getUserInfoByOpenID 获取第三方关联中金帐号信息
    */
    public function getOauthinfo($where, $choose='*'){
        return $this->getDataAR(self::TBL_Oauth,$where,$choose, '', '', TRUE);
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

        $cKey = 'bjLoginErrorCount_' . $param['LoginIP']; //登录次数记录
        $badKey = 'bjerrorloginip_' . $param['LoginIP']; //登录记录错误超过5次记录
        //pre($param['Password']);
        //pre($mRs);
        $tPassword = $this->_makePswd($param['Password'], $mRs['RegTime'], $mRs['RegIP'], $mRs['RegPlatFormType']);
        //pre($tPassword);
        if($tPassword != $mRs['Password']){
            $errorCount = 0;
            $aOkObj = $this->config->item('whiteIPs');
            //pre($aOkObj);
            if(!in_array($param['LoginIP'], $aOkObj)){
                $errorCount = (int)$this->cache->get($cKey);
                if($errorCount > 4 ){
                    if($errorCount == 5){
                        //记录错误IP
                        $this->_updateUser(array('ErrLoginIP'=>$param['LoginIP']), array('UserID'=>$mRs['UserID']));
                    }
                    $errorCount ++;
                    $this->cache->set($badKey, 1, 30*60);
                    $this->cache->set($cKey, $errorCount, 30*60);
                    return array('Code'=>'100221', 'Msg'=>'您已连续输错5次密码，请在30分钟后在尝试', 'Record' =>array());
                }
                $errorCount ++;
                $this->cache->set($cKey, $errorCount, 60*60*12);
            }

            $error = '账号或密码错误，请确认后登录';
            if($errorCount>2){
                $hErrorCount = 5 - $errorCount;
                $error = '您已经输错' . $errorCount . '次密码，还有' . (int)$hErrorCount . '次登录机会';
                return array('Code'=>'100014', 'Msg'=>$error, 'Record' =>array());
            }
            return array('Code'=>'100013', 'Msg'=>$error, 'Record' =>array());
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
        $now = NOW_TIME;
        $userID = $mRs['UserID'];
        $keyStr = $this->_makeKeystr($tid, $param['LoginIP'], $userID);
        
        $uuParam = array(
            'KeyStr' => $keyStr,
            'LastLoginIP' => $param['LoginIP'],
            'ActiveTime' => $now,
            'LastLoginTime' => $mRs['ActiveTime']
        );
        if(!$this->_updateUser($uuParam, array('UserID'=>$mRs['UserID']))){
            return array('Code'=>'11', 'Msg'=>'数据更新失败', 'Record' =>array());
        }
        if(empty($md5str)) {
            $m_key = 'u_'.$md5str.'_userlogininfo';
            $this->cache->set($m_key, '');
        }
        $m_key = 'u_userbasebyuserid_' . $userID;
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
        
        //$logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
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
    	
    	$mKey = 'bjerrorloginip_' . $ip;
    	//pre($mKey);
    	//pre($this->cache->get($mKey));
        $mVal = $this->cache->get($mKey);
        //logs('|-mKey-|'.print_r($mKey,true) . '|-mVal-|'.print_r($mVal,true),$logFile);
    	if($mVal){
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
     * Type 注册方式 1-中金本地注册 2-QQ注册 3-微信注册 4-微博注册  5-中金其他账号注册
     * UserName 用户名(三选一)
     * Email 邮件(三选一)
     * Mobile 手机(三选一)
     * EMailCheck 是否产生邮件验证码 0-否，1-是
     * MobileCheck 是否产生手机验证码 0-否，1-是
     * NickName 昵称
     * Password 密码
     * IP 注册IP
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

        
        $mRs = $this->_getUser(array('Mobile'=>$param['Mobile']), 'UserID');
        if($mRs && isset($mRs['UserID']) && $mRs['UserID']>0){
            return array('Code'=>'01', 'Msg'=>'手机号已被注册', 'Record' =>array());
        }
        unset($mRs);
        }
        //$nRs = $this->_getUser(array('NickName'=>$param['NickName']), 'UserID');
        //if($nRs && isset($nRs['UserID']) && $nRs['UserID']>0){
            //return array('Code'=>'01', 'Msg'=>'昵称已被注册', 'Record' =>array());
        //}

        $now = NOW_TIME;
        
        $password = $this->_makePswd($param['Password'], $param['RegTime'], $param['RegIP'], $param['RegPlatFormType']);

        $iuParam = array(
            'Mobile' => $param['Mobile'],
            'Password' => $password,
            'RegPlatFormType' => $param['RegPlatFormType'],
            'RegTime' => $param['RegTime'],
            'ActiveTime' => $now,
            'LastLoginTime' => $now,
            'RegIP' => $param['RegIP'],
            'Status' => $param['Status'],
            'CustType' => $param['CustType'],
        );
    
        //pre($iParam);
        if(!$userID = $this->_insertUser($iuParam)){
            return array('Code'=>'11', 'Msg'=>'用户信息添加失败', 'Record' =>array());
        }
        
        //$userID = (int)$this->mdb->insert_id();
        $uType = array(1=>'CM-', 2=>'QQ-', 3=>'SINA-', 4=>'WX-', 5=>'ZJ-', );
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
        if(!$r=$this->_insertUserGroup($ugParam)){
            return array('Code'=>'11', 'Msg'=>'组别添加失败', 'Record' =>array());
        }
        
        $udParam = array(
            'UserID' => $userID,
            'DateTime' => NOW_TIME,
            'Country' => '中国',
            'SendRate'=>1 //默认不寄送
        );
        if(!$res=$this->_insertUserDetail($udParam)){
            return array('Code'=>'11', 'Msg'=>'用户详情添加失败', 'Record' =>$res);
        }
        
        return array('Code'=>'00', 'Msg'=>'用户注册成功', 'UserID' =>$userID);

    }
    /**
     * @ userReg 用户绑卡
     *
     * @param array $param
     * Type 注册方式 1-中金本地注册 2-QQ注册 3-微信注册 4-微博注册  5-中金其他账号注册
     * UserName 用户名(三选一)
     * Email 邮件(三选一)
     * Mobile 手机(三选一)
     * EMailCheck 是否产生邮件验证码 0-否，1-是
     * MobileCheck 是否产生手机验证码 0-否，1-是
     * NickName 昵称
     * Password 密码
     * IP 注册IP
     * @access public
     * @return array
     */
    public function userBindphone($param) {

        $cmp = checkModelParam($param, array('Type', 'Password', 'RegPlatFormType', 'RegTime', 'RegIP', 'GroupID', 'Status','UserID'));

        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }

        if($param['Type'] == 1){
            if(!isset($param['Mobile']) || !$param['Mobile']){
                return array('Code'=>'10003', 'Msg'=>'参数缺失, Mobile', 'Record' =>array());
            }

        
        $mRs = $this->_getUser(array('Mobile'=>$param['Mobile']), 'UserID');
        if($mRs && isset($mRs['UserID']) && $mRs['UserID']>0){
            return array('Code'=>'01', 'Msg'=>'手机号已被注册', 'Record' =>array());
        }
        unset($mRs);
        }
        //$nRs = $this->_getUser(array('NickName'=>$param['NickName']), 'UserID');
        //if($nRs && isset($nRs['UserID']) && $nRs['UserID']>0){
            //return array('Code'=>'01', 'Msg'=>'昵称已被注册', 'Record' =>array());
        //}

        $now = NOW_TIME;
        
        $password = $this->_makePswd($param['Password'], $param['RegTime'], $param['RegIP'], $param['RegPlatFormType']);

        $iuParam = array(
            'Mobile' => $param['Mobile'],
            'Password' => $password,
        );
		if(!$this->_updateUser($iuParam, array('UserID'=>$param['UserID']))){
            return array('Code'=>'11', 'Msg'=>'数据更新失败', 'Record' =>array());
        }        
        return array('Code'=>'00', 'Msg'=>'用户绑卡成功', 'UserID' =>$param['UserID']);

    }
    /**
     * 
     * 登录日志
     * LoginType 登录类别  1-PC端 2-手机端
     * LoginIP
     * LoginPage 登录方式 1-伯嘉基金 2-腾讯QQ 3-新浪微博 4-微信 5-中金
     * LoginAddr 登陆地址
     * @param unknown $param
     * @return multitype:string multitype:
     */
    public function userLoginLog($param){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        //@error_log(NOW_TIME.'|-param-|' . print_r($param, true)  . PHP_EOL,3,LOG_PATH. '/'. $logFile);

        $cmp = checkModelParam($param, array('UserID','LoginType', 'LoginIP', 'LoginPage', 'LoginAddr'));
        
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }
        
        // 更新keystr, LastLoginIP, LastLoginTime
        $tid = time();
        $now = NOW_TIME;
        $keyStr = $this->_makeKeystr($tid, $param['LoginIP'], $param['UserID']);
        
        $mRs = $this->_getUser(array('UserID'=>$param['UserID']), 'UserID, ActiveTime');
        
        $uuParam = array(
            'KeyStr' => $keyStr,
            'LastLoginIP' => $param['LoginIP'],
            'ActiveTime' => $now,
            'LastLoginTime' => $mRs['ActiveTime']
        );
        unset($now);
        if(!$this->_updateUser($uuParam, array('UserID'=>$param['UserID']))){
            return array('Code'=>'11', 'Msg'=>'数据更新失败', 'Record' =>array());
        }
        
        $param['LoginPort'] = $_SERVER['REMOTE_PORT'];
        //pre($param);
        //pre($this->_insertUserLoginLog($param));
        //exit;
        $ir = $this->_insertUserLoginLog($param);
        @error_log(NOW_TIME.'|-param-|' . print_r($param, true) . '|-ir-|' . print_r($ir, true) . PHP_EOL,3,LOG_PATH. '/'. $logFile);
        
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
        //pre(NOW_TIME);
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
        //pre(NOW_TIME);
        //pre($rs);
        exit;
        
        
        
        return $rs;
    }

    /**
     * @ checkBankExist 检查用户是否存在
     *
     * @param array $param
     * Mobile 电话
     * BankNo 银行编号
     * UserID 用户名id
     * @access public
     * @return array
     */
    public function checkBankExist($param) {
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
        $cmp = checkModelParam($param, array('UserID', 'BankNo','Mobile'));
        if($cmp['flag'] != '10000'){
            return array('flag'=>'10003', 'msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }

        $uParam=array(
            'BankNo'=>$param['BankNo'],
            'UserID'=>$param['UserID'],
            'Mobile'=>$param['Mobile'],
        );
        //判断银行用户是否存在
        $cRs = $this->getDataAR(self::TBL_Bank, $uParam, 'count(*) as count','','',true);
        logs('|-$uParam-|'.print_r($uParam,true).print_r($cRs,true),$logFile);
        if($cRs && isset($cRs['count'])){

            if($cRs['count'] == '0'){
                return array('flag'=>'10012', 'msg'=>'没有记录', 'Record'=>array());
            } else {
                return array('flag'=>'10000', 'msg'=>'有记录', 'Record'=>array());
            }
        } else {
            return array('flag'=>'10011', 'msg'=>'数据库查询错误', 'Record' =>array());
        }
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
                    'RequestTime' => NOW_TIME,
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
                    'RequestTime' => NOW_TIME,
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
     * array $ba：是否需要验证银行mobile.传入：BankNo
     */
    public function updateUserVerifyCode($param,$ba=''){

        $cmp = checkModelParam($param, array('Type', 'Mobile', 'IP', 'UserID'));
        if($cmp['flag'] != '10000'){
            return array('Code'=>'10003', 'Msg'=>'参数缺失,' . $cmp['info'], 'Record' =>array());
        }

        $uParam = array(
            'Mobile' => $param['Mobile'],
            'UserID' => $param['UserID']
        );
        if($ba==''){
            //判断用户是否存在
            $uRs = $this->_getUser($uParam);
            if(!$uRs){
                return array('Code'=>'12', 'Msg'=>'信息不存在');
            }
        }else{
            if(!isset($ba['BankNo'])){
                return array('Code'=>'13', 'Msg'=>'信息不存在');
            }
            $uParam['BankNo']=$ba['BankNo'];
            //判断银行用户是否存在
            $choose = 'BankID, UserID, Mobile, HsAccount, AccountName, Province, City, BankName, BankNo, BranchBank, BranchName, BankCard, Master, Status';
            $uRs = $this->getDataAR(self::TBL_Bank, $uParam, $choose,'','',true);
            if(!$uRs){
                return array('Code'=>'14', 'Msg'=>'信息不存在');
            }
        }

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
                    'ActiveTime' => NOW_TIME,
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
    public function getUserBase($param, $choose = 'UserID, HsUserID, HsAccount, UserName, NickName, Mobile, Status, LastLoginIP, LastLoginTime, ActiveTime, KeyStr , RegTime') {

        if(isset($param['UserID'])){
            $where = array('UserID'=>$param['UserID']);
            
        } else if(isset($param['UserName'])){
            $where = array('UserName'=>$param['UserName']);
            
        } else if(isset($param['Mobile'])){
            $where = array('Mobile'=>$param['Mobile']);
            
        } else if(isset($param['NickName'])){
            $where = array('NickName'=>$param['NickName']);
            
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

        $m_key = 'u_userbasebyuserid_' . $userID;
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
        $m_key = 'u_userdetailbyuserid_' . $userID;
        $rs = $this->cache->get($m_key);
        //$rs = '';
        if(!$rs){
            $param = array('UserID' => $userID);
            $choose='UserID, Country, RiskBear, PaperID, TelPhone, Email, Address, Postal, Job, SendRate, SendMethod, ReservedContent';
            $rs = $this->_getUserDetail($param, $choose);
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
     * 
     * 
     * 
     * @param unknown $userID
     * @param string $choose
     * @return Ambigous <string, multitype:, unknown>
     */
    public function getUserBankByUserID($userID){
        
        $m_key = 'u_userbankbyuserid_' . $userID;

        $rs = $this->cache->get($m_key);
        //$rs = '';
        if(!$rs){
            $choose = 'BankID, UserID, Mobile, HsAccount, AccountName, Province, City, BankName, BankNo, BranchBank, BranchName, BankCard, Master, Status';
            $param = array('UserID' => $userID, 'Status'=>1);
            $rs = $this->getDataAR(self::TBL_Bank, $param, $choose);
            if($rs){
                $this->cache->set($m_key, $rs, 30*60); //缓存半小时
            }
        }
        return $rs;
    }
    
    /**
     * 
     * 获取用户主卡信息
     * 
     */
    public function getUserBankMasterByUserID($userID){
        $param = array('UserID' => $userID, 'Status'=>1, 'Master'=>1);
        $rs = $this->getDataAR(self::TBL_Bank, $param, 'BankID, UserID, HsAccount, Master, Status', '', '', TRUE);
        return $rs;
    }
    
    public function getUserBankCountByUserID($userID){

        $m_key = 'u_userbankcountbyuserid_' . $userID;

        $rs = $this->cache->get($m_key);
        //$rs = '';
        if(!$rs){
            $choose = 'count(*) as count';
            $param = array('UserID' => $userID, 'Status'=>1);
            $rs = $this->getDataAR(self::TBL_Bank, $param, $choose);
            if($rs){
                $this->cache->set($m_key, $rs, 30*60); //缓存半小时
            }
        }
        return $rs[0]['count'];

    }
    
    public function getUserBankCodeByUserID($userID){
        $param = array('UserID' => $userID, 'Status'=>1);
        $rs = $this->getDataAR(self::TBL_Bank, $param, 'BankNo');
        return $rs;
    }
    
    
    /**
     *
     * 设置主卡
     *
     */
    public function setMasterCard($param){
    
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        $tempParam = $param;
        unset($tempParam['password']);
        logs('|-Param-|' . print_r($tempParam, true), $logFile);
    
        $sign = 'maintransaccount_mod_acct';
        
        $userID = $param['UserID'];
        $sParam = array(
            'trust_way'=>TRANSACTION_MODE,
            'trade_acco' => $param['trade_acco'],
            'ta_acco'=> $param['ta_acco'],
            'password' => $param['password'],
            'origin_tradeacco'=>$param['origin_tradeacco'],
        );
        
        //pre($xParam);
        $rs = $this->curlPostUFX($sParam, $sign, $this->access_token);
        logs('|-rs-|' . print_r($rs, true), $logFile);
    
    
        if(isset($rs['data'][0]['error_code'])){
            if($rs['data'][0]['error_code']){
                $rData = array('flag'=>'10009', 'msg'=>$rs['data'][0]['error_info'] , 'info'=>'');
                return $rData;
            }
        } else {
            if(isset($rs['error_info']) && $rs['error_info']){
                $rData = array('flag'=>'10011', 'msg'=>$rs['error_info'], 'info'=>'');
                return $rData;
            }
            $rData = array('flag'=>'10008', 'msg'=>'主卡修改接口失败', 'info'=>'');
            return $rData;
        }
    
        $uParam = array('HsAccount'=>$param['trade_acco']);
        $uRs = $this->_updateUser($uParam, array('UserID'=>$userID));
        if(!$uRs){
            $rData = array('flag'=>'10011', 'msg'=>'用户信息更新失败', 'info'=>'');
            return $rData;
        }
        $obParam = array('Master'=>2);
        $obRs = $this->updateTb(self::TBL_Bank, $obParam , array('BankID'=>$param['oldBid'], 'UserID'=>$userID));
        if(!$obRs){
            $rData = array('flag'=>'10012', 'msg'=>'主卡更新失败', 'info'=>'');
            return $rData;
        }
        $bParam = array('Master'=>1);
        $bRs = $this->updateTb(self::TBL_Bank, $bParam , array('BankID'=>$param['bid'], 'UserID'=>$userID));
        if(!$bRs){
            $rData = array('flag'=>'10013', 'msg'=>'付卡更新失败', 'info'=>'');
            return $rData;
        }
        
        $m_key = 'u_userauthbyuseridbankid_' . $userID;
        $this->cache->set($m_key, '', 1);
        $m_key = 'u_userbankcountbyuserid_' . $userID;
        $this->cache->set($m_key, '', 1);
        $m_key = 'u_userbankbyuserid_' . $userID;
        $this->cache->set($m_key, '', 1);
        
        $rData = array('flag'=>'10000', 'msg'=>'修改成功', 'info'=>'');
        return $rData;
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
                        'RequestTime' => NOW_TIME,
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
                        'RequestTime' => NOW_TIME,
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
                'ActiveTime' => NOW_TIME,
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
            
            $m_key = 'u_userbasebyuserid_' . $param['UserID'];
            $this->cache->set($m_key, '');
        
            return array('Code'=>'00', 'Msg'=>'成功', 'Record' => array('TmpMobileID' =>$tmpMobileID, 'ActiveTime' => $iParam['ActiveTime']));
        
        
        } else {
            return array('Code'=>'11', 'Msg'=>'数据查询失败', 'Record' =>array());
        }
        
    }

    
    /**
     * 
     * 短信签约
     * 
     */
    public function sign_sms($param){
        $sign = 'pay_sign_contract_sms';
        $rs = $this->curlPostUFX($param, $sign, $this->access_token, 'pay');
        return $rs;
    }
    
    
    /**
     * 
     * 银行卡绑定
     * 
     */
    public function regBindBank($param){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        $tempParam = $param;
        unset($tempParam['Password']);
        logs('|-Param-|' . print_r($tempParam, true), $logFile);
        
        $rData = array('flag'=>'10000', 'msg'=>'ok', 'info'=>'');

        //pre($param);
        //开户
        $xParam = array(
            'trust_way' => TRANSACTION_MODE, //交易委托方式  2代表的是网上委托 7 代表的是手机委托
            'en_entrust_way' => '0|1|1|0|0|0|0|', //开通委托方式  格式为：1|1|1|1|1|1|1|,1为开通，0为不开通，顺序为电话委托，网上委托，自助委托，传真委托，手机委托，经纪人委托，其他委托
            'order_date' => '', // 下单日期
            'apply_time' => '', // 申请时间
            'account_type' => ACCOUNT_TYPE, //账户类型
            'cust_type' => CUST_TYPE, //客户类别(0 机构；1个人）
            'client_full_name' => $param['NickName'], //客户名称全称
            'client_name' => $param['NickName'], //客户姓名
            'fund_nationality' => FUND_NATIONALITY, //默认中国 156
            'id_kind_gb' => 0, //0 身份证
            'id_no' => $param['IdentityNumber'], //0 身份证
            'password' => $param['Password'],
            'mobile_tel' => $param['Mobile'],
            'bank_no' => $param['BankNo'], // 银行代码
            'bank_name' => $param['BankName'], // 银行名称
            'bank_account' => $param['BankCard'], //银行户名
            'bank_account_name' => $param['NickName'],
            'capital_mode' => CAPITAL_MODE,
            'ta_no' => TA_NO,
            'fund_card' => $param['protocolNo'], //资金卡号（签约协议号，如果渠道返回签约协议号需传到这个字段上）
            'branchbank' => $param['BranchBank'],  //联行号（民生监管的销售商需要传）
            'detail_fund_way' => '01',  //明细资金方式（默认传01），根据支付渠道的模式修改
            'id_enddate' => $param['EndValidDate'],  //身份证有效期
            'fund_interface_type' => 1, //短信签约 为1
        );

        $sign = 'fundacct_direct_open_acct';
        logs('|-xParam-|' . print_r($xParam, true), $logFile);
        //pre($xParam);
        $rs = $this->curlPostUFX($xParam, $sign, $this->access_token);
        logs('|-rs-|' . print_r($rs, true), $logFile);
        
        if(isset($rs['data'][0]['error_code'])){
            if($rs['data'][0]['error_code']){
                $rData = array('flag'=>'10009', 'msg'=>$rs['data'][0]['error_info'] , 'info'=>'');
                return $rData;
            }
        } else {
            if(isset($rs['error_info']) && $rs['error_info']){
                $rData = array('flag'=>'10011', 'msg'=>$rs['error_info'], 'info'=>'');
                return $rData;
            }
            $rData = array('flag'=>'10008', 'msg'=>'开户接口失败', 'info'=>'');
            return $rData;
        }
        
        $oParam = array(
            'UserID' => $param['UserID'],
            'OperatType' =>1, //开户
            'OperatStatus'=>1, //操作状态 必填 1成功 2失败
            'HsUserID'=>$rs['data'][0]['client_id'],
            'OperatDetail' =>'开户-' . $rs['data'][0]['trade_acco']
            
        );
        $this->operatLog($oParam);
        //pre($rs);
        //exit;
        
        //查看是否有昵称,没有就更新， 同时更新恒生帐号ID
        $user = $this->_getUser('UserID=' . $param['UserID'], 'NickName');
        if(isset($user['NickName']) && $user['NickName']){
            if($user['NickName'] != $param['NickName']){
                $rData = array('flag'=>'10005', 'msg'=>'真实姓名不符合', 'info'=>'');
                return $rData;
            }
        } else {
            $uParam = array('NickName'=>$param['NickName'], 'HsAccount'=>$rs['data'][0]['trade_acco'], 'HsUserID'=>$rs['data'][0]['client_id']);
            $uuRs = $this->_updateUser($uParam, array('UserID'=>$param['UserID']));
            logs('|-uuRs-|' . print_r($uuRs, true), $logFile);
        }
        //银行绑定表插入
        $ibParam = array(
            'UserID' => $param['UserID'],
            'AccountName' => $param['NickName'],
            'HsAccount'=>$rs['data'][0]['trade_acco'],
            'Master'=>1, //是否主账号 1是 2否
            'Province' => $param['Province'],
            'City' => $param['City'],
            'BankName' => $param['BankName'],
            'BankNo' => $param['BankNo'],
            'BranchBank' => $param['BranchBank'],
            'BranchName' => $param['BranchName'],
            'BankCard' => $param['BankCard'],
            'Mobile' => $param['Mobile'], //银行预留电话
            'Status' => 1, //状态： 1审核通过 2 审核中 3 审核不通过
            'DataTime' => NOW_TIME
        );
        logs('|-ibParam-|' . print_r($ibParam, true), $logFile);
        $ibRs = $this->insertTb(self::TBL_Bank, $ibParam);
        logs('|-ibRs-|' . print_r($ibRs, true), $logFile);
        if(!$ibRs){
            $rData = array('flag'=>'10006', 'msg'=>'银行绑定失败', 'info'=>'');
            return $rData;
        }
        
        
        $this->cache->set('u_userbasebyuserid_'. $param['UserID'], '', 1);
        $this->cache->set('u_userbankbyuserid_'. $param['UserID'], '', 1);
        $this->cache->set('u_userbankcountbyuserid_'. $param['UserID'], '', 1);
        //是否存在实名认证信息，没有就添加
        $tRs = $this->_getUserAuth(array('UserID'=> $param['UserID']), 'count(*) as count');
        if(isset($tRs['count']) && $tRs['count'] != 1){
            //实名认证
            $iaParam = array(
                'UserID' => $param['UserID'],
                'TrueName' => $param['NickName'],
                'Country' => FUND_NATIONALITY, //默认中国
                'IdentType' => 0, //默认身份证
                'Sex' => $param['Sex'],
                'IdentityNumber' => $param['IdentityNumber'],
                'Status' => 1, //审核通过
                'CheckTime' => NOW_TIME,
                'DataTime' => NOW_TIME,
                'EndValidDate' => $param['EndValidDate']
            );
            logs('|-iaParam-|' . print_r($iaParam, true), $logFile);
            $iaRs = $this->insertTb(self::TBL_UserAuthenticate, $iaParam, false);
            logs('|-iaRs-|' . print_r($iaRs, true), $logFile);
            if(!$iaRs){
                $rData = array('flag'=>'10007', 'msg'=>'实名认证添加失败', 'info'=>'');
                return $rData;
            }
            $this->cache->set('u_userauthbyuserid_' . $param['UserID'], '',1);
        }
        return $rData;
        
    }
    
    /**
     * 
     * 银行卡再次绑定
     * 
     */
    public function addBindBank($param){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        $tempParam = $param;
        unset($tempParam['Password']);
        logs('|-Param-|' . print_r($tempParam, true), $logFile);
        
        $rData = array('flag'=>'10000', 'msg'=>'ok', 'info'=>'');

        //pre($param);
        //增开交易账号
        $xParam = array(
            'trust_way' => TRANSACTION_MODE, //交易委托方式  2代表的是网上委托 7 代表的是手机委托
            'trade_acco'=>$param['HsAccount'], //交易账号
            'password' => $param['Password'], //密码
            'mobile_tel' => $param['Mobile'],//手机号
            'bank_no' => $param['BankNo'], // 银行代码
            'bank_name' => $param['BankName'], // 银行名称
            'bank_account' => $param['BankCard'], //银行户名
            'bank_account_name' => $param['NickName'], //户主
            'capital_mode' => CAPITAL_MODE, //资金方式
            'fund_card' => $param['protocolNo'], //资金卡号（签约协议号，如果渠道返回签约协议号需传到这个字段上）
            'bank_open_id_kind_gb' => 0, //银行卡开户证件类型 0 身份证
            'bank_open_id_no' => $param['IdentityNumber'], //身份证
            'detail_fund_way' => '01',  //明细资金方式（默认传01），根据支付渠道的模式修改
            'branchbank' => $param['BranchBank'],  //联行号（民生监管的销售商需要传）
            'fund_interface_type' => 1,  //接口类型（快捷签约传1））   
        );

        $sign = 'tradeaccount_add_acct';
        logs('|-xParam-|' . print_r($xParam, true), $logFile);
        //pre($xParam);
        $rs = $this->curlPostUFX($xParam, $sign, $this->access_token);
        logs('|-rs-|' . print_r($rs, true), $logFile);
        
        if(isset($rs['data'][0]['error_code'])){
            if($rs['data'][0]['error_code']){
                $rData = array('flag'=>'10009', 'msg'=>$rs['data'][0]['error_info'] , 'info'=>'');
                return $rData;
            }
        } else {
            if(isset($rs['error_info']) && $rs['error_info']){
                $rData = array('flag'=>'10011', 'msg'=>$rs['error_info'], 'info'=>'');
                return $rData;
            }
            $rData = array('flag'=>'10008', 'msg'=>'开户接口失败', 'info'=>'');
            return $rData;
        }
        
        $oParam = array(
            'UserID' => $param['UserID'],
            'OperatType' =>1, //开户
            'OperatStatus'=>1, //操作状态 必填 1成功 2失败
            'HsUserID'=>$param['HsUserID'],
            'OperatDetail' =>'增开交易账号-' . $rs['data'][0]['trade_acco']
            
        );
        $this->operatLog($oParam);
        //pre($rs);
        //exit;
        
        //银行绑定表插入
        $ibParam = array(
            'UserID' => $param['UserID'],
            'AccountName' => $param['NickName'],
            'HsAccount'=>$rs['data'][0]['trade_acco'],
            'Master'=>2, //是否主账号 1是 2否
            'Province' => $param['Province'],
            'City' => $param['City'],
            'BankName' => $param['BankName'],
            'BankNo' => $param['BankNo'],
            'BranchBank' => $param['BranchBank'],
            'BranchName' => $param['BranchName'],
            'BankCard' => $param['BankCard'],
            'Mobile' => $param['Mobile'], //银行预留电话
            'Status' => 1, //状态： 1审核通过 2 审核中 3 审核不通过
            'DataTime' => NOW_TIME
        );
        logs('|-ibParam-|' . print_r($ibParam, true), $logFile);
        $ibRs = $this->insertTb(self::TBL_Bank, $ibParam);
        logs('|-ibRs-|' . print_r($ibRs, true), $logFile);
        if(!$ibRs){
            $rData = array('flag'=>'10006', 'msg'=>'银行绑定失败', 'info'=>'');
            return $rData;
        }
        
        $this->cache->set('u_userbankbyuserid_'. $param['UserID'], '', 1);
        $this->cache->set('u_userbankcountbyuserid_'. $param['UserID'], '', 1);
        return $rData;
        
    }
    
    /**
     * 
     * 解绑银行卡
     */
    public function unbindBankCard($param, $userBank){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        $tempParam = $param;
        unset($tempParam['password']);
        logs('|-Param-|' . print_r($tempParam, true), $logFile);
        
        $sign = 'transaccount_close_acct';
        //pre($xParam);
        $rs = $this->curlPostUFX($param, $sign, $this->access_token);
        logs('|-rs-|' . print_r($rs, true), $logFile);
        
        if(isset($rs['data'][0]['error_code'])){
            if($rs['data'][0]['error_code']){
                $rData = array('flag'=>'10009', 'msg'=>$rs['data'][0]['error_info'] , 'info'=>'');
                return $rData;
            }
        } else {
            if(isset($rs['error_info']) && $rs['error_info']){
                $rData = array('flag'=>'10011', 'msg'=>$rs['error_info'], 'info'=>'');
                return $rData;
            }
            $rData = array('flag'=>'10008', 'msg'=>'解绑接口失败', 'info'=>'');
            return $rData;
        }
        $uParam = array('Status'=>4);
        $where = array('BankID'=>$userBank['BankID']);
        $uRs = $this->updateTb(self::TBL_Bank, $uParam , $where);
        if(!$uRs){
            $rData = array('flag'=>'10009', 'msg'=>'解绑银行失败', 'info'=>'');
            return $rData;
        }
        
        $oParam = array(
            'UserID' => $userBank['UserID'],
            'OperatType' =>2, //开户
            'OperatStatus'=>1, //操作状态 必填 1成功 2失败
            'HsUserID'=>'',
            'OperatDetail' =>'银行卡解绑-' . $userBank['HsAccount']
        
        );
        $this->operatLog($oParam);
        
        $m_key = 'u_userauthbyuseridbankid_' . $userBank['UserID'];
        $this->cache->set($m_key, '', 1);
        $m_key = 'u_userbankcountbyuserid_' . $userBank['UserID'];
        $this->cache->set($m_key, '', 1);
        $m_key = 'u_userbankbyuserid_' . $userBank['UserID'];
        $this->cache->set($m_key, '', 1);
        
        $rData = array('flag'=>'10000', 'msg'=>'解绑成功', 'info'=>'');
        return $rData;
        
    }
    
    /**
     * 
     * 获取用户实名认证信息
     * @param unknown $param
     *
     */
    public function getUserAuthByUserID($userID, $choose = 'UserID, TrueName, IdentityNumber, Sex, IdentType, EndValidDate, Country, Status'){
        
        $m_key = 'u_userauthbyuserid_' . $userID;
        $rs = $this->cache->get($m_key);
        //$rs = '';
        if(!$rs){
            $param = array('UserID' => $userID);
            $rs = $this->_getUserAuth($param, $choose);
            if($rs){
                $this->cache->set($m_key, $rs, 30*60); //缓存半小时
            }
        }
        return $rs;
    }
    
    /**
     * 
     * 获取用户实名认证信息
     * @param unknown $param
     */
    public function getUserBankByUserIDBankID($userID, $bankID, $choose = 'BankID, HsAccount, UserID, AccountName, BankName, BankNo, BranchBank, BranchName, BankCard, Master, Status, Mobile'){
      
        $m_key = 'u_userauthbyuseridbankid_' . $userID . '_' . $bankID;
        $rs = $this->cache->get($m_key);
        $rs = '';
        if(!$rs){
            $param = array('UserID' => $userID, 'BankID'=>$bankID, 'Status'=>1);
            $rs = $this->getDataAR(self::TBL_Bank, $param, $choose,'','', TRUE);
            if($rs){
                $this->cache->set($m_key, $rs, 30*60); //缓存半小时
            }
        }
        return $rs;
    }
    
    /**
     * 
     * 风险评测问题题目获取
     * 
     */
    public function riskQuesTitle($param){
        $logFile = strtolower(__CLASS__) .  '/' . strtolower(__FUNCTION__) . '_' . date('Ymd') . '.log';
        logs('|-Param-|' . print_r($param, true), $logFile);
        $sign = 'paperinfo_question_qry';
        $mKey = $sign . implode('_', $param);
        $rRs    = $this->cache->get($mKey);
        //$rRs = '';
        if(!$rRs){
            $rs = $this->curlPostUFX($param, $sign, $this->access_token);
            //pre($rs);
            logs('|-rs-|' . print_r($rs, true), $logFile);
            $rRs = array();
            if(isset($rs['data']) && $rs['data']){
                $rRs = $rs['data'];
                $this->cache->set($mKey, $rRs, 30*60); //缓存半小时
            }
        }
        return $rRs;
        
    }
    /**
     * 
     * 风险评测问题选项获取
     * 
     */
    public function riskQuesOption($param){
        $logFile = strtolower(__CLASS__) .  '/' . strtolower(__FUNCTION__) . '_' . date('Ymd') . '.log';
        logs('|-Param-|' . print_r($param, true), $logFile);
        $sign = 'paperinfo_question_section_qry';
        $mKey = $sign . implode('_', $param);
        $rRs    = $this->cache->get($mKey);
        if(!$rRs){
            $rs = $this->curlPostUFX($param, $sign, $this->access_token);
            logs('|-rs-|' . print_r($rs, true), $logFile);

            $rRs = array();
            if(isset($rs['data']) && $rs['data']){
                $rRs = $rs['data'];
                $this->cache->set($mKey, $rRs, 30*60); //缓存半小时
            }
        }
        return $rRs;
        
    }
    
    /**
     * 
     * 风险评测提交
     * 
     */
    public function submitRiskAsk($param){
        $logFile = strtolower(__CLASS__) .  '/' . strtolower(__FUNCTION__) . '_' . date('Ymd') . '.log';
        logs('|-Param-|' . print_r($param, true), $logFile);
        
        $userID = $param['UserID'];
        unset($param['UserID']);
        $userDetail = $this->getUserDetailByUserID($userID);
        if(!$userDetail){
            $rData = array('flag'=>'10004', 'msg'=>'用户信息错误', 'info'=>'');
            return $rData;
        }
        
        //pre($userDetail);exit;
        //第一次问卷调查提交
        if(!isset($userDetail['RiskBear']) || !$userDetail['RiskBear'] || !isset($userDetail['PaperID']) || !$userDetail['PaperID']){
            $sign = 'paperinfo_add_acct';
            $rs = $this->curlPostUFX($param, $sign, $this->access_token);
        } else { //问卷调查修改
            $sign = 'paperinfo_mod_acct';
            
            $mParam = array(
                'trust_way' =>$param['trust_way'],
                'paper_id'=>$userDetail['PaperID'],
                'elig_content' => $param['elig_content']
            );
            //$param['paper_id'] = $userDetail['PaperID'];
            $rs = $this->curlPostUFX($mParam, $sign, $this->access_token);
        }


        if(isset($rs['data'][0]['error_code'])){
            if($rs['data'][0]['error_code']){
                $rData = array('flag'=>'10005', 'msg'=>$rs['data'][0]['error_info'] . $rs['data'][0]['error_code'], 'info'=>'');
                return $rData;
            }
        } else {
            $rData = array('flag'=>'10006', 'msg'=>'提交失败', 'info'=>'');
            return $rData;
        }
        
        $sRs = $rs['data'][0];
        $uParam = array('RiskBear'=>$sRs['invest_risk_tolerance'], 'PaperID'=>$sRs['paper_id']);
        $uRs = $this->_updateUserDetail($uParam, array('UserID'=>$userID));
        if(!$uRs){
            $rData = array('flag'=>'10007', 'msg'=>'详情更新失败', 'info'=>'');
            return $rData;
        }
        
        $rData = array('flag'=>'10000', 'msg'=>'ok', 'info'=>'');
        return $rData;


    }
    
    /**
     * 
     * 通过UserID更新用户详细信息
     * @param unknown $userID
     * @param unknown $param
     * @return multitype:string
     */
    public function updateUserDetailByUserID($userID, $param){
        $uRs = $this->_updateUserDetail($param, array('UserID'=>$userID));
        if(!$uRs){
            $rData = array('flag'=>'10007', 'msg'=>'详情更新失败', 'info'=>'');
            return $rData;
        }
        $rData = array('flag'=>'10000', 'msg'=>'修改成功', 'info'=>'');
        return $rData;
    }
    /**
     * 
     * 通过UserID更新用户实名认证信息
     * @param unknown $userID
     * @param unknown $param
     * @return multitype:string
     */
    public function updateUserAuthByUserID($userID, $param){
        $uRs = $this->_updateUserAuth($param, array('UserID'=>$userID));
        if(!$uRs){
            $rData = array('flag'=>'10007', 'msg'=>'详情更新失败', 'info'=>'');
            return $rData;
        }
        $rData = array('flag'=>'10000', 'msg'=>'ok', 'info'=>'');
        return $rData;
    }
    /**
     *
     * 通过UserID更新银行信息
     * @param unknown $userID
     * @param unknown $param
     * @return multitype:string
     */
    public function updateBankByUserID($userID, $param){
        $uRs = $this->_updateBank($param, array('UserID'=>$userID));
        if(!$uRs){
            $rData = array('flag'=>'10007', 'msg'=>'银行信息更新失败', 'info'=>'');
            return $rData;
        }
        $rData = array('flag'=>'10000', 'msg'=>'ok', 'info'=>'');
        return $rData;
    }
    /**
     *
     * 通过UserID更新用户信息
     * @param unknown $userID
     * @param unknown $param
     * @return multitype:string
     */
    public function updateUserByUserID($userID, $param,$keystr=0){
        if($keystr!=0){
            $res=$this->_getUser(array('UserID'=>$userID),'LastLoginIP');
            $keyStr = $this->_makeKeystr(time(), $res['LastLoginIP'], $userID);
            if(!$res){
                return array('flag'=>'10001','msg'=>'用户信息更新失败');
            }
            $param['keyStr']=$keyStr;
        }
        $uRs = $this->_updateUser($param, array('UserID'=>$userID));
        if(!$uRs){
            $rData = array('flag'=>'10002', 'msg'=>'用户信息更新失败', 'info'=>'');
            return $rData;
        }
        $rData = array('flag'=>'10000', 'msg'=>'ok', 'info'=>'');
        return $rData;
    }

    public function modUserPersonal($dParam, $xParam){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        
        $sign = 'clientinfo_mod_acct';
        $txParam = $xParam;
        unset($txParam['password']);
        logs('|-xParam-|' . print_r($txParam, true), $logFile);
        //pre($xParam);
        $rs = $this->curlPostUFX($xParam, $sign, $this->access_token);
        
        logs('|-rs-|' . print_r($rs, true), $logFile);
        if(isset($rs['data'][0]['error_code'])){
            if($rs['data'][0]['error_code']){
                $rData = array('flag'=>'10009', 'msg'=>$rs['data'][0]['error_info'] , 'info'=>'');
                return $rData;
            }
        } else {
            if(isset($rs['error_info']) && $rs['error_info']){
                $rData = array('flag'=>'10011', 'msg'=>$rs['error_info'], 'info'=>'');
                return $rData;
            }
            $rData = array('flag'=>'10008', 'msg'=>'修改用户信息接口失败', 'info'=>'');
            return $rData;
        }
        
        $userID = $dParam['UserID'];
        logs('|-dParam-|' . print_r($dParam, true), $logFile);
        unset($dParam['UserID']);
        $uRs = $this->updateUserDetailByUserID($userID, $dParam);
        return $uRs;
    }
    /*
     * 接口简易修改
     * */
    public function upSimpchange($param,$sign,$type = 'sale'){
        if(!$param){
            return array('flag'=>'01');
        }
        //连接接口
        $rs = $this->curlPostUFX($param, $sign, $this->access_token,$type);
        return $rs;
    }

    /**
     * 
     * 判断银行卡和身份证是否存在
     * 
     */
    public function isExistBankOrIndent($param){
        
        if(isset($param['BankCard'])){
            $cRs = $this->getDataAR(self::TBL_Bank, array('BankCard' => $param['BankCard'], 'Status'=>1), 'count(*) as count','','',true);
            //pre($cRs);
            if(!$cRs || !isset($cRs['count'])){
                return array('flag'=>'10004', 'msg'=>'查询错误');
            }
            if($cRs['count']>0){
                return array('flag'=>'10005', 'msg'=>'银行卡已被注册');
            }
        }
        
        if(isset($param['IdentityNumber'])){
            $tRs = $this->_getUserAuth(array('IdentityNumber'=> $param['IdentityNumber'], 'Status'=>1), 'count(*) as count');
            
            if(!$tRs || !isset($tRs['count'])){
                return array('flag'=>'10004', 'msg'=>'查询错误');
            }
            if($tRs['count']>0){
                return array('flag'=>'10005', 'msg'=>'身份证已被注册');
            }
        }
        
        return array('flag'=>'10000', 'msg'=>'不存在');
    }
    
    /**
     * 
     * TA账号查询
     * 
     * @param unknown $param
     */
    public function accoutQry($param){
        $sign = 'account_qry';
        $sRs = $this->curlPostUFX($param, $sign, $this->access_token);
        //pre($sRs);
        if(isset($sRs['error_code'])){
            //echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_info'] . $sRs['error_code'])); exit;
            return array('flag'=>'10011', 'msg'=>$sRs['error_info']);
        }
        
        if(isset($sRs['error']) && isset($sRs['error_description']) && $sRs['error_description']){
            //echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_info'] . $sRs['error_code'])); exit;
            return  array('flag'=>'10011', 'msg'=>$sRs['error_description']);
        }
        return $sRs['data'];
    }
    
    
    
    /**
     * 
     * 新的第三方登录之后，创建新账号与其映射
     * 
     **/
    public function setOauthinfo($param){

        if(!is_array($param) || count($param)<2){  //添加用户
            return array('Code'=>'112', 'Msg'=>'参数有误', 'Record' =>array());
        }

        $password = $this->_makePswd($param['Password'], NOW_TIME, $param['RegIP'], $param['RegPlatFormType']);

        //创建用户表
        $iuParam = array(
            'Type' => $param['Original'],
            'Password' => $password,
            'RegPlatFormType' => $param['RegPlatFormType'],
            'RegTime' => NOW_TIME,
            'RegIP' => $param['RegIP'],
            'GroupID' =>'1',
            'Status' =>'1',
            'Mobile'=>'',
            'CustType'=>'1',
        );
        //注册
        $res=$this->userReg($iuParam);
        if($res['Code']!='00' || !isset($res['UserID'])){
            return array('Code'=>'113', 'Msg'=>'用户信息添加失败', 'Record' =>array());
        }

        //记录登录表
        $paramlog=array(
            'UserID'=>$res['UserID'],
            'LoginType'=>1,
            'LoginIP'=>$param['RegIP'],
            'LoginPage'=>$param['Original'],
            'LoginTime'=> NOW_TIME,
            'LoginAddr'=>'0',
        );
        $this->userLoginLog($paramlog);

        //获取id
        $userID=$res['UserID'];
        //在数据库中再次验证数据，并提取所需的数据
        $result=$this->_getUser(array('UserID'=>$userID),'UserName,KeyStr');
        if(!isset($result['UserName']) || !isset($result['KeyStr'])){
            return array('Code'=>'114', 'Msg'=>'用户信息添加失败', 'Record' =>array());
        };

        $username=$result['UserName'];
        $keystr=$result['KeyStr'];

        //创建映射表
        $aparam=array(
            'UserID'=>$userID,
            'OpenID'=>$param['OpenID'],
            'Original'=>$param['Original'],
            'OauthToken'=>0,
            'TokenSecret'=>0,
            'DataTime'=>NOW_TIME,
            'UnionID'=>$param['UnionID'],
            'Status'=>1,
        );

        //添加用户
        if(!$oauthID = $this->insertTb(self::TBL_Oauth, $aparam)){
            return array('Code'=>'11', 'Msg'=>'用户信息添加失败', 'Record' =>array());
        }

        //登陆cookie创建
        $user=array(
            'logintime'=>NOW_TIME,
            'userid'=>$userID,
            'username'=>$username,
            'nickname'=>null,
            'keystr'=>$keystr,
            'auto'=>0,
            'mobile'=>null,
        );

        $this->User_Interact->setCnfolCookie($user, 0);

        return array('Code'=>'00', 'Msg'=>'创建成功', 'Record' =>$userID);
    }
    
    /**
     * 
     * 添加设置oauth
     * 
     * @param unknown $param
     */
    public function addOauth($param){
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
        logs('|-param-|' . print_r($param, true), $logFile);
        $userID = $param['UserID'];
        $cpRs = check_param($param, array('UserID', 'OpenID', 'Original'));
        if($cpRs['flag'] != '10000'){
            return $cpRs;
        }
        if($param['Original'] == '4' && (!isset($param['UnionID']) || !$param['UnionID'] )){
            return array('flag'=>'10005', 'msg'=>'参数有误');
        }

        $uParam = array();
        //用户是否绑定过同一种登录方式的帐号
        $uParam = array(
            'UserID' => $userID,
            'Original' => $param['Original'],
            'Status' => 1
        );
        $uRs = $this->getOauthinfo($uParam, 'count(*) as count');

        if($uRs && isset($uRs['count']) && $uRs['count']>0){
            return array('flag'=>'10006', 'msg'=>'用户已绑定过中金帐号');
        }
        $uParam2 = array();
        $uParam2 = array(
            'Original' => $param['Original'],
            'Status' => 1
        );
        if($param['Original'] == 4){ //微信登录
            $uParam2['UnionID'] = $param['UnionID'];
        } else {
            $uParam2['OpenID'] = $param['OpenID'];
        }
        
        $uRs2 = $this->getOauthinfo($uParam2, 'count(*) as count');
        if($uRs2 && isset($uRs2['count']) && $uRs2['count']>0){
            return array('flag'=>'10006', 'msg'=>'中金帐号已绑定过用户');
        }
        
        $iParam = array(
            'UserID' => $userID,
            'Original' => $param['Original'],
            'OpenID' => $param['OpenID'],
            'UnionID' => $param['UnionID'],
            'DataTime'=>NOW_TIME,
            'OauthToken'=>0,
            'TokenSecret'=>0,
            'Status' => 1
        );
        logs('|-iParam-|' . print_r($iParam, true), $logFile);
        //添加用户
        if(!$oauthID = $this->insertTb(self::TBL_Oauth, $iParam)){
            return array('flag'=>'10007', 'msg'=>'用户信息添加失败');
        }
        return array('flag'=>'10000', 'msg'=>'ok');
        
    }
    

    

    /**
     * 验证新旧密码
     * $navpassword---原始密码
     * $password----输入新密码
     * $regTime----注册时间
     * $regIP----注册ip
     * $regPlatFormType----注册方式
     ***/
    public function repeatPwd($newpwd,$navpwd,$uid){
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
        if(!$navpwd || !$newpwd || !$uid){
            return array('flag'=>'10004','msg'=>'验证参数不全！');
        }

        $param=$this->_getUser(array('UserID'=>$uid),'UserID,Password,RegTime,RegIP,RegPlatFormType');
        if(!$param  || !$param['UserID'] || !$param['RegTime'] || !$param['RegIP'] || !$param['RegPlatFormType']){
            return array('flag'=>'100041','msg'=>'输入数据错误！');
        }
        $xparam=$param;
        unset($xparam['Password']);
        logs('|-$param-|'.print_r($xparam,true), $logFile);
        //验证新旧密码一致性
        $tpassword=$this->_makePswd($navpwd, $param['RegTime'], $param['RegIP'], $param['RegPlatFormType']);

        if($param['Password']!==$tpassword){
            return array('flag'=>'10005','msg'=>'输入密码与原密码不符！');

        }
        //修改旧密码
        $newpassword=$this->_makePswd($newpwd, $param['RegTime'], $param['RegIP'], $param['RegPlatFormType']);
        $res=$this->User_Model->_updateUser(array('Password'=>$newpassword),array('UserID'=>$param['UserID']));
        if(!$res){
            return array('flag'=>'10006','msg'=>'修改密码失败！');
        }
        return array('flag'=>'10000','msg'=>'修改密码成功！');
    }

}//end class
?>