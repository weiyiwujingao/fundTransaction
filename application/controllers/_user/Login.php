<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 基金交易-用户登录
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2016-12-12
 ****************************************************************/
class Login extends MY_Controller 
{
	/* 传递到对应视图的数据 */
	private $_data     = array();

    public function __construct() 
    {
        parent::__construct();
        $this->_data['headerCss'] = '';
        $this->_data['loginUrl'] = TRADE_WEB_URL . '/user/login';
        $this->_data['tdloginUrl'] = TRADE_WEB_URL . '/user/widgetlogin.html';
        
        $this->_data['keywords'] = SEO_KEYWORDS;
        $this->_data['description'] = SEO_DESCRIPTION;
        $this->load->model('_user/User_Interact');
    }

   /**
     *
     * 登录页
	 *
	 * @return view
     *
    **/
    public function index() {

		$rt =  filter_slashes( $this->input->get('rt', true) );
	    $cUrl ='';
	    if($rt){
	        $cUrl =  base64_decode($rt);
	        //pre($cUrl);
	        $domain = get_host_domain($cUrl);
	        if(!is_allowed_login_domain($domain) ){
	            errorJump('回调URL不允许，请重试'); exit;
	        }

	    }

	    //用于默认填充用户名之用
	    $tmpUser = $this->User_Interact->getTmpUser();

	    $tid = time();
	    $this->_data['tid'] = $tid;
	    //验证手机号
	    $this->_data['kid'] = substr(md5(LOGIN_PAGE_KEY . $cUrl . substr(md5($tid), -5) . 'login' . '11'), 8, 16);
	    $this->_data['tmpuser'] = $tmpUser;

	    $this->_data['return'] =  $rt;
	    $this->_data['title'] = '基金交易登录_伯嘉基金网';
	    
	    $this->load->view('_user/login.html', $this->_data);
    }

    /**
     *
     * AJax登录
     *
     */
    public function ajaxLogin(){
	    $return = $this->_checkRequest();

	    if($return['flag']!='10000') {
	        $data = array('flag'=>'10001', 'msg'=>$return['msg'], 'info'=>'');
	    }
	    else
	    {
	        $params['LoginName'] = $return['info']['account'];
	        $params['Password']  = $return['info']['password'];
	        $params['LoginIP']   = $return['info']['ip'];
	        $params['Type']      = $return['info']['type'];
	        $params['LoginType'] = $return['info']['logintype'];
	        $params['LoginAddr'] = $return['info']['loginaddr'];

	        $result = $this->User_Interact->userlogin($params, 'ajaxlogin');
            
	        $data   = $this->_checkResponse($result);

	    }


	    if($data['flag']=='10000') {
	        $this->User_Interact->setCnfolCookie($data['info'], $return['info']['auto']);
	        $data['info'] = $return['info']['return'];
	    }

	    echo !empty($return['info']['jsoncallback']) ? $return['info']['jsoncallback'].'('.json_encode($data).')' : json_encode($data);
    }

    /**
     * @ _checkRequest 登录请求处理
     *
     * @param string $mode 登录方式
     * @access private
     * @return array
     */
    private function _checkRequest($mode='') {

        if(trim($this->input->post('act', true) != 'login')){
            return array('flag'=>'10001', 'msg'=>'参数有误', 'info'=>'');
        }

        $ip = $this->input->ip_address();
        //防刷
        $saRs = simpleAnti($ip, 'loginaction', 5, 6);
        if($saRs['flag'] != '10000'){
            return  $saRs;
        }

        $iParam =  $this->input->post(NULL, true);

        //pre($iParam);
        if(trim($iParam['act'])!='login') {
            return array('flag'=>'10002', 'msg'=>'传参有误', 'info'=>'');
        }

        $account     = isset($iParam['account']) ? filter_slashes(trim($iParam['account'])) : '';
        $password    = isset($iParam['password']) ? filter_slashes(trim($iParam['password'])) : '';
        $return      = isset($iParam['return']) ? filter_slashes(trim($iParam['return'])) : '';
        $logintype   = isset($iParam['platform']) ?  intval($iParam['platform']) : 0;//登录平台 1PC 2 手机网
        $loginaddr   = isset($iParam['channel']) ? intval($iParam['channel']) : 0;//登录位置  1 用户中心, 2港股
        $jsoncallback= isset($iParam['jsoncallback']) ? filter_slashes(trim($iParam['jsoncallback'])) : '';
        $force= isset($iParam['force']) ? filter_slashes(trim($iParam['force'])) : '';

        if(!check_str($account, 'mobile')){
            return array('flag' => '10001', 'msg'=>'您输入的手机号码不正确', 'info'=>'');
        }

        if($return != ''){
            if($return == TRADE_LOGIN_URL){
                $return = TRADE_WEB_URL;
            } else {
                //return的URL是否被允许
                $return =  base64_decode($return);
                $domain = get_host_domain($return);
                if(!is_allowed_login_domain($domain)){
                    return array('flag'=>'10005', 'msg'=>'请求URL不允许', 'info'=>'');
                }
            }

        } else {
            $return = TRADE_WEB_URL;
        }
        //设置临时用户名
        $this->User_Interact->setTempUserCookie($account);
        if(check_str($password, 'empty')) {
            return array('flag'=>'10005', 'msg'=>'请输入您的密码', 'info'=>'');
        }

        $type = 1; //PC登录
        $auto = 0;
        $info = array(
            'account'     => $account,
            'password'    => $password,
            'ip'          => $ip,
            'type'        => $type,
            'logintype'   => $logintype,
            'loginaddr'   => $loginaddr,
            'return'      => $return,
            'jsoncallback'=> $jsoncallback,
            'auto'        => $auto,
            'force'       => $force
        );

        $data = array('flag'=>'10000', 'msg'=>'成功', 'info'=>$info);
        return $data;
    }


    /**
     * @ _checkResponse 登录结果处理
     *
     * @param array $param 登录返回数据
     * @access private
     * @return array
     */
    private function _checkResponse($param) {
        switch($param['Code']) {
            case '00':
                $crInfo = $param['Record'];

                $params['userid']    = $crInfo['UserID'];
                $params['username']  = $crInfo['UserName'];
                $params['nickname']  = $crInfo['NickName'];
                $params['status']    = $crInfo['Status'];
                $params['keystr']    = $crInfo['KeyStr'];
                $params['logintime'] = $crInfo['ActiveTime'];
                $params['mobile']    = $crInfo['Mobile'];

                $data = array('flag'=>'10000', 'msg'=>'登录成功', 'info'=>$params);
                unset($params, $param);


                break;
            case '12':
                $data = array('flag'=>'30004', 'msg'=>'您输入的帐号不存在，请确认后登录', 'info'=>'');
                break;
            case '100005':
                $data = array('flag'=>'30005', 'msg'=>'您使用的手机号码未通过验证', 'info'=>'');
                break;
            case '100013':
                $data = array('flag'=>'30013', 'msg'=>$param['Msg'], 'info'=>'');
                break;
            case '100014':
                $data = array('flag'=>'30006', 'msg'=>$param['Msg'], 'info'=>'');
                break;
            case '100221':
                $data = array('flag'=>'30007', 'msg'=>$param['Msg'], 'info'=>'');
                break;
            case '100222':
                $data = array('flag'=>'30008', 'msg'=>'登录操作太频繁，请稍后再试', 'info'=>'');
                break;
            case '100223':
                $data = array('flag'=>'30009', 'msg'=>'账户登录太频繁，请30分钟后再来登录', 'info'=>'');
                break;
            case '100001':
                $data = array('flag'=>'30010', 'msg'=>'您使用的帐号处于非正常状态', 'info'=>'');
                break;
            case '03' :
                $data = array('flag'=>'30011', 'msg'=>$param['Msg'], 'info'=>'');
                break;
            default :
                $data = array('flag'=>'30012', 'msg'=>$param['Msg'], 'info'=>'');
                break;
        }
        return $data;
    }

    
    /**
     *
     * 退出
     *
     *
     */
    public function logout(){
         
        //判断来源是否合法
        $requestUrl = $this->input->server('HTTP_REFERER');
        if($requestUrl){
            $domain = get_host_domain($requestUrl);
            if(!is_allowed_login_domain($domain)){
                exit('非法请求');
            }
        }
    
         
        $tUrl = TRADE_LOGIN_URL;
        //判断回跳地址是否合法
        $return = trim($this->input->get('rt', true));
        if($return){
    
            $returnUrl =  base64_decode($return);
            $rDomain = get_host_domain($returnUrl);
            if(!is_allowed_login_domain($rDomain)){
                exit('非法回跳请求');
            }
    
            if($returnUrl){
                $tUrl = urldecode($returnUrl);
            }
        }
    
        //清除cookie
        $this->User_Interact->delCnfolCookie();
        cnfol_location($tUrl);
    }

    /*
     * 第三方登录'https://passport.cnfol.com/userlogin/thirdlogging?tp=qq&cb=';
     * */
    public function thirdlogging(){
        $cb = filter_slashes(trim($this->input->get('cb')));
        $tp = filter_slashes(trim($this->input->get('tp')));

        $callback = TRADE_WEB_URL;
        if($cb) {

            $callback = base64_decode($cb);
            $rDomain = get_host_domain($callback);
            if(!is_allowed_login_domain($rDomain)){
                exit('非法回跳请求');
            }
        }

        $this->config->load('oauthsettings');
        $setting = $this->config->item('multi_account');
        require_once APPLICATION_PATH . '/libraries/OauthClientV2.php';
        if(!isset($_SESSION))
            session_start();
        // strtoupper() 函数把字符串转换为大写。
        switch(strtoupper($tp)) {
            case 'QQ':
                $config              = $setting['qq'];
                $redirect_url        = $config['redirect_uri'].'&callback='.urlencode($callback);
                $oauth_client        = new OauthClientV2($config['client_id'], $config['client_secret'], $redirect_url);
                $third_authorize_url = $oauth_client->authorizeUrl($config['authorize_url']);
                $third_authorize_url .='%2Cadd_t';

                break;
            case 'SINA':
                $config              = $setting['tsina'];
                $redirect_url        = $config['redirect_uri'].'&callback='.urlencode($callback);
                $oauth_client        = new OauthClientV2($config['client_id'], $config['client_secret'], $redirect_url);
                $third_authorize_url = $oauth_client->authorizeUrl($config['authorize_url']);
                break;
            case 'WECHAT':
                $config              = $setting['wechat'];
                

                $third_authorize_url = $config['authorize_url'].'?appid='. $config['client_id'] . '&redirect_uri=' . $config['redirect_uri'] . '&response_type=code&scope=snsapi_login&state=' . urlencode(base64_encode($callback)) . '#wechat_redirect';

                break;
            case 'CNFOL':
                $config              = $setting['cnfol'];
                $tid=time();
                $redirect_url        = $config['oauth_return'].'&callback='.urlencode($callback);
                //pre($redirect_url);
                $keystr=strtolower (md5(PASSPORT_AUTH_KEY . $redirect_url. $tid .  md5($config['oauth_user_id'] . $config['oauth_key'] . $config['channel'] )));

                $third_authorize_url = $config['authorize_url'].'?oauth_user_id='.$config['oauth_user_id']."&oauth_key=".$config['oauth_key']
                    .'&channel='.$config['channel'].'&oauth_return='.urlencode($redirect_url).'&tid='.$tid.'&keystr='.$keystr;
                
                //echo $third_authorize_url;exit;
                break;


            default:
                echo '未知登录方式，请联系系统管理员！';
                exit;
                break;
        }

        echo '<script type="text/javascript">location.href="'.$third_authorize_url.'";</script>';
        exit;
    }

}

/* End of file stockquote.php */
/* Location: ./application/controllers/stockquote.php */