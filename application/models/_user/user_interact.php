<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @ 用户交互模块
 * 
 * @ Copyright (c) 2004-2012 CNFOL Inc. (http://www.cnfol.com)
 * @ version 3.0.1
 * @ author chensh
 */
 
class User_Interact extends MY_Model {
    
	public function __construct(){
	    
		parent::__construct();
		
		$this->domain = '.'.WEB_DOMAIN_NAME;
		$this->expire = 24*60*60;
		$this->load->model('User_Model');
	}
    /**
     * @ checkUser 判断用户是否可以正常使用
     *
     * @param int $userid 用户编号
     * @param int $groupid 用户权限编号
     * @access public
     * @return void
    */
    public function checkUser($userid, $groupid=UNCHECKUSERGROUP) {
        
        $active_url = PASSPORT_ACTIVEUSER_URL;
        $groupid_in = explode(',', $groupid);
        
        if(in_array(UNCHECKUSERGROUP, $groupid_in)) {//未验证组
            $userinfo = $this->User_Model->getUserBaseInfo(array('UserID'=>$userid));
            //pre($userinfo); exit;
            if($userinfo['Code']=='00' && $userinfo['Record']['UserID']>0) {
                $userinfo = $userinfo['Record'];
                if($userinfo['UnEmail']) {
                    $tstr = time();
                    $kstr = md5(md5($userid).md5($tstr).md5(FORM_HASH_KEY));
                   
                    $active_url .= '?act=active&type=email&tstr='.$tstr.'&kstr='.$kstr.'&uid='.$userid;
                }
                else
                {
                    $tstr = time();
                    $kstr = md5(md5($userid).md5($tstr).md5(FORM_HASH_KEY));
                    //$vstr = md5(md5($userid).md5($tstr).md5($userinfo['MobileCheck']).md5(MOBILE_CHECK_KEY));
                    $vstr = md5(md5($userid).md5($tstr).md5(MOBILE_CHECK_KEY));
                
                    $active_url .= '?act=active&type=mobile&sign=1&tstr='.$tstr.'&kstr='.$kstr.'&uid='.$userid.'&vstr='.$vstr;
                }
                cnfol_location($active_url);
                exit;
            }
            else
            {
                cnfol_alert(cnfol_gw_error($userinfo['Code']));
                cnfol_location(TRADE_LOGIN_URL);
                exit;
            }
        }
    }
    /**
     * @ checkOnline 用户中心权限判断
     *
     * @param int $userid 用户编号
     * @access public
     * @return bool
     */
    public function checkOnline($userid) {
        $m_key = 'user_'.$userid.'_islogin';
        $flag  = $this->cache->get($m_key);
        
        if($flag) {
            return 1;
        }
        else
        {
            return 0;
        }
    }
    /**
     * @ passportCheck 用户中心权限判断
     *
     * @param string $access 权限串
     * @access public
     * @return void
     */
    public function passportCheck($jump='') {

        $islogin = $this->checkUserlogin();

        if($islogin) {
        } else {
			if($jump)
               cnfol_location(TRADE_LOGIN_URL.'?rt='.base64_encode(cur_page_url()));
		    else
               cnfol_location(TRADE_LOGIN_URL);
            exit;
        }
        
        $userid = $this->getUserID();
        $this->_setUserLogin($userid, 1);
    }
    /**
     * @ passportCheck 用户中心权限判断
     *
     * @param string $access 权限串
     * @access public
     * @return void
     */
    public function ajaxPassportCheck($access='') {

        $islogin = $this->checkUserlogin();

        if($islogin) {
        } else {
            return array('flag'=>'10001', 'msg'=>'请先登录', 'info'=>TRADE_LOGIN_URL);
	    
        }
        $userid = $this->getUserID();
        $this->_setUserLogin($userid, 1);
        return array('flag'=>'10000', 'msg'=>'已登录', 'info'=>$userid);
    }
    

    /**
     * 
     * 用户是否有操作
     * 
     */
    private function _isUserAction($userID){
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
        $m_key = 'u_useronlineact_' . $userID;
        $rs = $this->cache->get($m_key);
        
        logs('|-userID-|'.$userID .'|-rs-|' . $rs . '|-period-|' . USER_ACTION_PERIOD, $logFile);
        if(!$rs){
            return array('flag'=>'20001', 'msg'=> (int)(USER_ACTION_PERIOD/60) . '分钟内未操作', 'info'=>'');
        }
        //缓存超过30秒更新一次缓存
        logs('|-cha-|'. (int)(time()-$rs), $logFile);
        if((int)(time()-$rs)>=30){
            $this->cache->set($m_key, time(), (int)(USER_ACTION_PERIOD+30));
        }
        return array('flag'=>'10000', 'msg'=>'ok');
    }
    
    /**
     * @ checkUserlogin 判断用户是否登录
     *
     * @access private
     * @return bool
     */
    public function checkUserlogin() {
        $userID = $this->getUserID();

        if(empty($userID)) {
            return false;
        }
        
        $md5str = $this->_setCacheKey($userID);
        $keys   = $this->_getCacheKey();

        if($md5str==$keys) {//判断用户是否已退出

            $logininfo = $this->_getUserLoginInfo($userID, 0);//获取用户在线信息
            //pre($logininfo);
            //pre( $this->_getKeyStr()); exit;
            if(!empty($logininfo)) {
                $keystr = $this->_getKeyStr();
                if($keystr==$logininfo['KeyStr']) {//判断用户是否重复登录
                    $isAction = $this->_isUserAction($userID);
                    if($isAction['flag'] != '10000'){
                        $this->delCnfolCookie();
                        return false;
                    }
                    return true;
                }
                else
                {
                    $this->delCnfolCookie();
                    return false;
                }
            }
            else
            {
                $this->delCnfolCookie();
                return false;
            }
        }
        else
        {
            $this->delCnfolCookie();
            return false;
        }
    }
    /**
     * @ _checkUserAccess 判断用户是否有操作权限
     *
     * @param string $useraccess 用户权限
     * @param string $access 预设权限
     * @access private
     * @return void
    */
    private function _checkUserAccess($useraccess, $access) {
        $accesskey = $this->config->item('accesskey');
        $useraccess=$useraccess['AccessID'];
        if(!empty($access)) {
            $access     = explode(',', $access);
            $useraccess = explode(',', $useraccess);
            $count      = count($access);
            
            for($i=0; $i<$count; $i++) {
                if(!in_array($access[$i], $useraccess)) {
                    if($accesskey['Login'] == $access[$i]) {
                        cnfol_alert('您的帐号被锁定登录权限，请联系在线客服或拨打客服热线400-8888-366！');
                        //cnfol_location(TRADE_LOGIN_URL.'?rt='.base64_encode(cur_page_url()));
                        cnfol_location(TRADE_LOGIN_URL);
                    }
                    else
                    {
                        cnfol_alert('您的帐号没有该操作权限！');
                        cnfol_location(TRADE_WEB_URL);
                    }
                }
            }
        }
    }
    /**
     * @ _ajaxCheckUserAccess 判断用户是否有操作权限
     *
     * @param string $useraccess 用户权限
     * @param string $access 预设权限
     * @access private
     * @return void
    */
    private function _ajaxCheckUserAccess($useraccess, $access) {
        $accesskey = $this->config->item('accesskey');
        $useraccess=$useraccess['AccessID'];
        if(!empty($access)) {
            $access     = explode(',', $access);
            $useraccess = explode(',', $useraccess);
            $count      = count($access);
            
            for($i=0; $i<$count; $i++) {
                if(!in_array($access[$i], $useraccess)) {
                    if($accesskey['Login'] == $access[$i]) {
                        return array('flag'=>'10001', 'msg'=>'您的帐号被锁定登录权限，请联系在线客服或拨打客服热线400-8888-366！', 'info'=>TRADE_LOGIN_URL);
                    }
                    else
                    {
                        return array('flag'=>'10001', 'msg'=>'您的帐号没有该操作权限！', 'info'=>TRADE_LOGIN_URL);
                    }
                }
            }
        }
        
        return array('flag'=>'10000', 'msg'=>'ok');
    }
    
    /**
     * @ _checkUserAccessone 判断用户是否有操作权限
     *
     * @param string $useraccess 用户权限
     * @param string $access 预设权限
     * @access private
     * @return void
    */
    private function _checkUserAccessone($useraccess, $access) {
        $accesskey = $this->config->item('accesskey');
        $useraccess=$useraccess['AccessID'];
        if(!empty($access)) {
            $access     = explode(',', $access);
            $useraccess = explode(',', $useraccess);
            $count      = count($access);

            for($i=0; $i<$count; $i++) {
                if(!in_array($access[$i], $useraccess)) {
                    if($accesskey['Login'] == $access[$i]) {
                        //cnfol_alert('您的帐号被锁定登录权限，请联系在线客服或拨打客服热线400-8888-366！');
                        //cnfol_location(TRADE_LOGIN_URL.'?rt='.base64_encode(cur_page_url()));
                        return 1;
                    }
                    else
                    {
                        //cnfol_alert('您的帐号没有该操作权限！');
                        //cnfol_location(TRADE_WEB_URL);
                        return 2;
                    }
                }
            }
        }
    }
    /**
     * @ _getUserLoginInfo 获取用户登录信息
     *
     * @param $userid 用户编号
     * @param $operator 操作类型
     * @param $keystr 登录加密串
     * @access private
     * @return mixed
     */
    private function _getUserLoginInfo($userid, $operator, $keystr='') {
        
        $md5str = $this->_setCacheKey($userid);
        $keystr = $keystr ? $keystr : $this->_getKeyStr();
        $params = array('UserID'=>$userid,'Operator'=>$operator,'KeyStr'=>$keystr);//Operator必填：0-查询，1-更新
        $return = $this->User_Model->getUserLoginInfo($params, $md5str);
        //pre($return);
        if($return['Code']=='00' && $return['Record']['UserID']>0) {
            $return = $return['Record'];
        }
        else
        {
            $return = '';
        }
        
        return $return;
    }
    
    /**
     * @ _getAccessID 获取用户权限信息
     *
     * @access private
     * @return mixed
     */
    private function _getAccessID() {
        
        $userid = $this->getUserID();
        $params = array('UserID'=>$userid);
        $md5str = $this->_setCacheKey($userid);
        $return = $this->User_Model->getUserAccess($params, $md5str);
        
        if($return['Code']=='00' && !empty($return['Record'])) {
            $return = $return['Record'];
        }
        else
        {
            $return = '';
        }
        
        return $return;
    }
    /**
     * @ userLogin 用户登录
     *
     * @param array $param 登录请求数据
     * @param string $mode 登录方式
     * @access public
     * @return array
     */
    public function userLogin($param, $mode) {

        $md5str = $this->_getCacheKey();
        $data   = $this->User_Model->userLogin($param, $md5str);
        
        if(is_array($data) && $data['Code']=='00') {
            $this->_setLoginErrorInfo($param['LoginName']);//登录成功，则清空失败信息
        }
        else
        {
            $this->_setLoginErrorInfo($param['LoginName'], 1);//登录失败，则记录失败信息
        }
        
        return $data;
    }
    
    /**
     * 重新设置昵称Cookie
     * @param unknown $nickname
     */
    public function setNickNameCookie($nickname){
        $expiretime = time() + 60*60*24*7;//7天
        setcookie('trade[nickname]', $nickname,  $expiretime, '/', $this->domain); //用户昵称
    }
    
    /**
     * @ setCnfolCookie 注册cookie
     * 
     * userid
     * username
     * nickname
     * status
     * keystr
     * logintime
     *
     * @param array $user 用户信息
     * @param int $auto 自动登录标志
     * @access public
     * @return void
     */
    public function setCnfolCookie($user, $auto=0) {
        $expiretime = time() + 60*60*24*7;//7天
        
        if($auto) {
            $expiretime = time() + 60*60*24*30;//30天
        }
        
        $user['logintime'] = strtotime($user['logintime']);
        $md5str  = $this->_setCacheKey($user['userid']);
        
        $md5user = strtoupper(md5(md5($user['userid']).md5($user['logintime']).md5(LOGIN_LIMIT_KEY)));
        
        $md5twouser = strtoupper(md5(md5($user['logintime']).md5($user['userid'].$user['username'].LOGIN_TWO_LIMIT_KEY).md5(LOGIN_TWO_LIMIT_KEY)));
        $md5twouser=substr($md5twouser,3,16);
        setcookie('trade[keytty]',   $md5twouser,    $expiretime, '/', $this->domain); //其他网站验证用key
        
        $nickname = (isset($user['nickname']) && $user['nickname']) ? $user['nickname'] : $user['username'];;
        setcookie('trade[userId]',   $user['userid'],    $expiretime, '/', $this->domain); //用户ID
        //setcookie('trade[username]', $user['username'],  $expiretime, '/', $this->domain); //用户名
        //setcookie('trade[nickname]', $user['nickname'],  $expiretime, '/', $this->domain); //用户昵称
        setcookie('trade[nickname]', $nickname,  $expiretime, '/', $this->domain); //用户昵称
        setcookie('trade[keys]',     $md5user,           $expiretime, '/', $this->domain); //密钥
        setcookie('trade[logtime]',  $user['logintime'], $expiretime, '/', $this->domain); //登录时间
        setcookie('trade[keystr]',   $user['keystr'],    $expiretime, '/', $this->domain); //关键字
        setcookie('trade[cache]',    $md5str,            $expiretime, '/', $this->domain); //缓存键值
        setcookie('trade[auto]',     $auto,              $expiretime, '/', $this->domain); //自动登录

        setcookie('trade[tmpuser]',  $user['mobile'],  $expiretime, '/', $this->domain); //用于记忆用户名/手机/邮箱
        
        
        $this->_setUserLogin($user['userid'], 1);
        $m_key = 'u_'.$md5str.'_userlogininfo';
        $this->cache->set($m_key, '');
        $m_key = 'u_userbasebyuserid_' . $user['userid'];
        $this->cache->set($m_key, '');
        $m_key  = 'u_userdetailbyuserid_' . $user['userid'];
        $this->cache->set($m_key, '');
        $m_key  = 'u_userbankbyuserid_' . $user['userid'];
        $this->cache->set($m_key, '');
        $m_key  = 'u_userauthbyuserid_' . $user['userid'];
        $this->cache->set($m_key, '');
    }
    /**
     * @ delCnfolCookie 删除cookie
     *
     * @access public
     * @return void
     */
    public function delCnfolCookie() {
    	
        $userID = $this->getUserID();
        setcookie('trade[keytty]',   '', time()-3600, '/', $this->domain);
        setcookie('trade[userId]',   '', time()-3600, '/', $this->domain);
        //setcookie('trade[username]', '', time()-3600, '/', $this->domain);
        setcookie('trade[nickname]', '', time()-3600, '/', $this->domain);
        //setcookie('trade[money]',    '', time()-3600, '/', $this->domain);
        setcookie('trade[keys]',     '', time()-3600, '/', $this->domain);
        setcookie('trade[logtime]',  '', time()-3600, '/', $this->domain);
        setcookie('trade[keystr]',   '', time()-3600, '/', $this->domain);
        setcookie('trade[cache]',    '', time()-3600, '/', $this->domain);
        setcookie('trade[auto]',     '', time()-3600, '/', $this->domain);
        //setcookie('trade[agentids]', '', time()-3600, '/', $this->domain);
        setcookie('trade[newkeys]',  '', time()-3600, '/', $this->domain);
        setcookie('trade[vkkey]',  '', time()-3600, '/', $this->domain);
        //setcookie('myhkstocklist',  '', time()-3600, '/', $this->domain);
        //setcookie('loginned', '', time()-3600, '/', $this->domain);//清除首次登录弹出动态标识
        
        $md5str = $this->_getCacheKey();
        $m_key  = 'u_'.$md5str.'_userlogininfo';
        $this->cache->set($m_key, '');
        $m_key  = 'u_'.$md5str.'_useraccess';
        $this->cache->set($m_key, '');
        $m_key  = 'u_userbasebyuserid_' . $userID;
        $this->cache->set($m_key, '');
        $m_key  = 'u_userdetailbyuserid_' . $userID;
        $this->cache->set($m_key, '');
        $m_key  = 'u_userbankbyuserid_' . $userID;
        $this->cache->set($m_key, '');
        $m_key  = 'u_userauthbyuserid_' . $userID;
        $this->cache->set($m_key, '');
        $m_key  = 'u_useronlineact_' . $userID; //用户在线操作缓存，超过5分钟没操作，自动退出
        $this->cache->set($m_key, '');
    }
    /**
     * @ getUserID 获取用户编号
     *
     * @access public
     * @return int
     */
    public function getUserID() {
        $userid = 0;
        if(isset($_COOKIE['trade']['userId']) && $_COOKIE['trade']['userId'] && is_numeric($_COOKIE['trade']['userId'])) {
            $keys    = $this->_getUserKey();
            $time    = $this->_getLoginTime();
            $md5user = strtoupper(md5(md5($_COOKIE['trade']['userId']).md5($time).md5(LOGIN_LIMIT_KEY)));
            
            if($keys==$md5user) {//验证用户合法性
                $userid = (int)$_COOKIE['trade']['userId'];
            }
        }
        
        return $userid;
    }
    /**
     * @ getNickName 获取用户昵称
     *
     * @access public
     * @return string
     */
    public function getNickName() {
        
        $nickname = '';
        if(isset($_COOKIE['trade']['nickname']) && $_COOKIE['trade']['nickname']) {
            $nickname = filter_slashes($_COOKIE['trade']['nickname']);
        }
        
        return $nickname;
    }
    /**
     * @ getTmpUser 获取临时用户名
     *
     * @access public
     * @return string
     */
    public function getTmpUser() {
        $tmpUser = '';
        if(isset($_COOKIE['trade']['tmpuser']) && $_COOKIE['trade']['tmpuser']) {
            $tmpUser = filter_slashes($_COOKIE['trade']['tmpuser']);
        }
        
        return $tmpUser;
    }

    /**
     *
     * 设置临时用户名cookies
     *
     */
    public function setTempUserCookie($username){
        return setcookie('trade[tmpuser]',  $username,  time() + 60*60*24*7, '/', $this->domain); //用于记忆用户名/手机/邮箱
    }
    /**
     * @ getAgentFlag 获取商家标识
     *
     * @access public
     * @return int
     */
    public function getAgentFlag() {
        $isagent = 0;
        if(isset($_COOKIE['trade']['agentids']) && $_COOKIE['trade']['agentids']) {
            $md5agent = md5($_COOKIE['trade']['userId'].'1');
            if($md5agent==$_COOKIE['trade']['agentids']) {
                $isagent = 1;
            }
        }
        
        return $isagent;
    }
    /**
     * @ _getKeyStr 获取登录加密key
     *
     * @access private
     * @return string
     */
    private function _getKeyStr() {
        $keystr = '';
        if(isset($_COOKIE['trade']['keystr']) && $_COOKIE['trade']['keystr']) {
            $keystr = filter_slashes($_COOKIE['trade']['keystr']);
        }
        
        return $keystr;
    }
    /**
     * @ _getUserKey 获取用户信息校验key
     *
     * @access private
     * @return string
     */
    private function _getUserKey() {
        $key = '';
        if(isset($_COOKIE['trade']['keys']) && $_COOKIE['trade']['keys']) {
            $key = filter_slashes($_COOKIE['trade']['keys']);
        }
        
        return $key;
    }
    /**
     * @ _getLoginTime 获取用户最后登录时间
     *
     * @access private
     * @return string
     */
    private function _getLoginTime() {
        $time = '';
        if(isset($_COOKIE['trade']['logtime']) && $_COOKIE['trade']['logtime']) {
            $time = filter_slashes($_COOKIE['trade']['logtime']);
        }
        
        return $time;
    }
    /**
     * @ _setUserLogin 设置用户在线信息
     *
     * @param int $userid 用户编号
     * @param int $flag 在线状态 0-离线，1-在线
     * @access private
     * @return bool
     */
    private function _setUserLogin($userid, $flag) {
        $m_key = 'user_'.$userid.'_islogin';
        return $this->cache->set($m_key, $flag, MAXLOGINTIME);
    }
    /**
     * @ _setCacheKey 设定用户登录信息缓存key
     *
     * @param int $userid 用户编号
     * @access private
     * @return string
     */
    private function _setCacheKey($userid) {
        return strtoupper(md5($this->domain.$userid.LOGIN_LIMIT_KEY));
    }
    /**
     * @ _getCacheKey 获取用户登录信息缓存key
     *
     * @access private
     * @return string
     */
    private function _getCacheKey() {
        return isset($_COOKIE['trade']['cache'])  ? filter_slashes($_COOKIE['trade']['cache']) : 0;
    }
    /**
     * @ _setLoginErrorInfo 设置用户登录失败信息
     *
     * @param int $account 用户帐号
     * @param int $type 操作类型 0-清空 1-保存
     * @access private
     * @return mixed
     */
    private function _setLoginErrorInfo($account, $type=0) {
        $m_key = 'user_'.md5($account).'_login_error';
        
        if($type) {
            $data = $this->_getLoginErrorInfo($account);
            
            if(empty($data['time'])) {
                $data['time'] = time();
            }
            
            $data['times'] += 1;
            
            return $this->cache->set($m_key, serialize($data), $this->expire);
        }
        else
        {
            return $this->cache->set($m_key, '');
        }
    }
    /**
     * @ _setLoginErrorInfo 获取用户登录失败信息
     *
     * @param int $account 用户帐号
     * @access private
     * @return array
     */
    private function _getLoginErrorInfo($account) {
        $m_key = 'user_'.md5($account).'_login_error';
        $data  = $this->cache->get($m_key);
        
        if(!empty($data)) {
            return unserialize($data);
        }
        
        return array('time'=>'', 'times'=>0);
    }
}//end class
?>