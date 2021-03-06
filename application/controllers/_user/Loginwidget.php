<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 基金交易-第三方登录
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2017-1-20
 ****************************************************************/
class Loginwidget extends MY_Controller
{
	/* 传递到对应视图的数据 */
	private $_data     = array();

    public function __construct() 
    {
        parent::__construct();
        $this->_data['headerCss'] = '';
        $this->_data['loginUrl'] = TRADE_WEB_URL . '/user/login';
        $this->load->model('_user/User_Interact');
        $this->config->load('oauthsettings');
        $this->setting = $this->config->item('multi_account');
    }

    /**
     * @ callback 第三方登录结果回调
     *
     * @access public
     * @return void
     */
    public function callback() {

        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);

        $type     = strtolower(filter_slashes(trim($this->input->get('type', true))));//接收类型
        require_once APPLICATION_PATH . '/libraries/OauthClientV2.php';


        //回跳路径
        $callback = $this->input->get('callback', true) ? trim($this->input->get('callback', true)) : urlencode(TRADE_WEB_URL);


        //分类类型
        switch($type){

            case 'qq':
                $config       = $this->setting['qq'];
                $original     = 2;  //qq代号2
                $oauth_client = new OauthClientV2($config['client_id'], $config['client_secret'], $config['redirect_uri']);//新建对象初步输入数据
                $code         = filter_slashes(trim($this->input->get('code', true)));//-_-!
                $grant_type   = 'authorization_code';   //固定参数
                $state        = '';//状态
                $redirect_uri = $config['redirect_uri'].'&callback='.$callback; //回调地址
                //传参并获取通过认证的ACCESS_TOKEN
                $response     = $oauth_client->getAccessToken($config['access_token_url'], compact('code', 'grant_type', 'state', 'redirect_uri'));
                //如未通过认证
                if(empty($response) || strpos($response, 'error')!== false) {
                    $return   = json_decode(substr($response, 10, -4), true);
                    errorJump('授权失败，请重新尝试。您的错误代码是：'.$return['error'].'，如有疑问请联系客服！');
                    exit;
                }
                //parse_str() 函数把查询字符串解析到变量中。
                parse_str($response, $return);
                $access_token = $return['access_token'];
                //获取ACCESS_TOKEN之后调用接口
                $response     = $oauth_client->doOpenApi($config['get_openid'], $access_token, 'get');//获取用户OPENID

                if(empty($response)) {
                    errorJump('接口出错，请重新尝试。获取OPENID失败！');
                    exit;
                }
                //解析获取的字符串
                $response = json_decode(substr($response, 10, -4), true);
                if(isset($response['error_code'])) {
                    errorJump('授权失败，请重新尝试。您的错误代码是：'.$response['error_code'].'，如有疑问请联系客服！');
                    exit;
                }

                if(!isset($response['openid']) || !$response['openid']){
                    errorJump('授权失败，请返回重新尝试。');
                    exit;
                }
                //获取openid
                $openid  = $response['openid'];
                //用详细参数再次获取参数
                $qq_user_info = $oauth_client->doOpenApi($config['get_account_info'], $access_token, 'get', array('oauth_consumer_key'=>$config['client_id'], 'openid'=>$openid));
                if(empty($qq_user_info)) {
                    errorJump('获取QQ授权信息失败,请重新尝试！');
                    exit;
                }
                //解析参数
                $qq_user_info = json_decode($qq_user_info, true);

                logs('|||type|||'. $type.'|||qq_user_info|||'. print_r($qq_user_info, true). PHP_EOL,$logFile);

                $this->JudgeCreate($openid,$unionID=0,$original,$callback);
                return ;
                break;

            case 'tsina':
                $config       = $this->setting['tsina'];
                $original     = 3;  //新浪代号3
                $oauth_client = new OauthClientV2($config['client_id'], $config['client_secret'], $config['redirect_uri']);//新建对象初步输入数据
                $code         = filter_slashes(trim($this->input->get('code', true)));//-_-!
                $grant_type   = 'authorization_code';   //固定参数
                $state        = '';//状态
                $redirect_uri = $config['redirect_uri'].'&callback='.$callback; //回调地址
                //传参并获取通过认证的ACCESS_TOKEN
                $response     = $oauth_client->getAccessToken($config['access_token_url'], compact('code', 'grant_type', 'state', 'redirect_uri'));
                //如未通过认证
                if(empty($response) || strpos($response, 'error')!== false) {
                    $return   = json_decode(substr($response, 10, -4), true);
                    errorJump('授权失败，请重新尝试。您的错误代码是：'.$return['error'].'，如有疑问请联系客服！');
                    exit;
                }
                //parse_str() 函数把查询字符串解析到变量中。
                //parse_str($response, $return);

                $return=json_decode($response,true);
                //pre($return);
                $access_token = $return['access_token'];
                //获取ACCESS_TOKEN之后调用接口
                $response     = $oauth_client->doOpenApi($config['get_account_info'], $access_token, 'get',array('uid'=>$return['uid'], 'source'=>$config['client_id']));//获取用户OPENID
                if(empty($response)) {
                    errorJump('接口出错，请重新尝试。获取OPENID失败！');
                    exit;
                }
                //解析获取的字符串
                $response = json_decode($response, true);
                if(isset($response['error_code'])) {
                    errorJump('授权失败，请重新尝试。您的错误代码是：'.$response['error_code'].'，如有疑问请联系客服！');
                    exit;
                }

                if(!isset($response['id']) || !$response['id']){
                    errorJump('获取新浪授权信息授权失败，请返回重新尝试。');
                    exit;
                }
                //获取openid
                $openid  = $response['id'];

                logs('|||type|||'. $type.'|||tsina_user_info|||'. print_r($response, true). PHP_EOL,$logFile);

                $this->JudgeCreate($openid,$unionID=0,$original,$callback);
                return ;
                break;

            case 'wechat':
                //$logFile = 'wechatthird_' . date('Ymd') . '.log';
                $original = 4;
                $config = $this->setting['wechat'];

                //微信服务器返回值
                $code = filter_slashes(trim($this->input->get('code', true)));
                $state = filter_slashes(trim($this->input->get('state', true)));
                $callback = base64_decode($state);
                if(!$code || !$callback) {
                    echo json_encode(array('flag'=>'10028','msg'=>'参数不全'));
                    exit;
                }
                //获取微信信息
                $wearr=array(
                    'appid'=>$config['client_id'],
                    'secret'=>$config['client_secret'],
                    'code'=>$code,
                    'grant_type'=>'authorization_code'
                );
                $sRs = curl_post($config['getoath_url'].'?',$wearr);
                $aRs=json_decode($sRs['data'],true);

                logs('|||type|||'. $type.'|||$sRs|||'. print_r($sRs, true). PHP_EOL,$logFile);
                //@error_log(date('Y-m-d H:i:s').  '|||type|||'. $type.'|||$aRs|||'. print_r($aRs, true). PHP_EOL,3, LOG_PATH . '/' . $logFile);

                if(isset($aRs['errcode']) && $aRs['errcode'] ){
                    errorJump('微信授权失败，请重新尝试。<br/>如有疑问请联系客服！ '. $aRs['errcode'] . '-' . $aRs['errmsg']);
                    exit;
                }
                if(isset($aRs['access_token']) && $aRs['access_token'] && isset($aRs['openid']) && $aRs['openid'] && isset($aRs['unionid']) && $aRs['unionid'] ){
                    $access_token = $aRs['access_token'];
                    $openid = $aRs['openid'];
                    $unionID = $aRs['unionid'];

                    //将得来的access_token二次验证,并获取数据
                    $wearr2=array(
                        'access_token'=>$access_token,
                        'openid'=>$openid,
                        'lang'=>'zh_CN',
                    );
                    $sRs2 = curl_post($config['getoath2_url'].'?',$wearr2);
                    $aRs2=json_decode($sRs2['data'],true);
                    if(isset($aRs2['errcode']) && $aRs2['errcode']!='00' ){
                        errorJump(json_encode(array('flag'=>'0005', 'msg'=>'参数错误', 'info'=>'[]')));
                        exit;
                    }

                    $this->JudgeCreate($openid,$unionID,$original,$callback);

                } else {
                    echo '微信授权失败，请重新尝试。<br/>如有疑问请联系客服002！' ;
                    exit;
                }
                return;
                break;

            case 'cnfol':

                $allow=1;
                $original = 5;
                $config = $this->setting['cnfol'];

                //中金服务器返回值
                $token = filter_slashes(trim($this->input->get('token', true)));
                $tid = filter_slashes(trim($this->input->get('tid', true)));
                $getcontent = filter_slashes(trim($this->input->get('getcontent', true)));
                $un = filter_slashes(trim($this->input->get('un', true)));
                $keystr=filter_slashes(trim($this->input->get('keystr', true)));
                $nkeystr=md5(PASSPORT_AUTH_KEY . $tid . md5($token. $getcontent . $un));

                //pre($nkeystr);
                //pre($keystr);
                if(!$token || !$tid || !$getcontent || !$un || !$keystr || $keystr!=$nkeystr ||  !isset($callback)){
                    errorJump('中金授权失败，请重新尝试。<br/>如有疑问请联系客服！');
                    exit;
                }

                //验证后获取中金信息
                $cnarr=array(
                    'allow'=>$allow,
                    'oauth_token'=>$token,
                    'un'=>$un,
                    'sys'=>$config['oauth_user_id'],
                    'oauth_key'=>$config['oauth_key'],
                    'getcontent'=>$getcontent,
                    'tid'=>$tid,
                    'keystr'=>md5(PASSPORT_AUTH_KEY . $allow . $tid .  md5($token . $un . $config['oauth_user_id'] . $getcontent . $config['oauth_key'] ))
                );
                //开始验证连接并获取结果
                $cnsRs = curl_post($config['getoath_url'].'?',$cnarr);
                if(!isset($cnsRs) || !isset($cnsRs['data']) ){
                    errorJump(json_encode(array('flag'=>'10006', 'msg'=>'连接校验失败', 'info'=>'[]')));
                    exit;
                }
                $cnaRs=json_decode($cnsRs['data'],true);

                logs('|||type|||'. $type.'|||$cnsRs|||'. print_r($cnsRs, true). PHP_EOL,$logFile);

                //失败
                if(!isset($cnaRs['flag']) || $cnaRs['flag']!='10000' ){
                    //pre($cnarr);
                    //pre($cnaRs);
                    errorJump(json_encode(array('flag'=>'10005', 'msg'=>$cnaRs['msg'], 'info'=>'[]')));
                    exit;
                }

                $openid=$cnaRs['info']['base'];
                $aUser = explode('.', $un);
                $zjUserID = $aUser[1];
                $this->JudgeCreate($openid,$unionID=0,$original,$callback, $zjUserID);

            default:
                echo '中金在线，欢迎您！';
                break;
        }
    }


    /*
     * 判断已通过验证的第三方登录是否要新创造一个用户还是直接进行登录
     * $openid——第三方接口用户辨识id
     * $unionID——第三方接口用户辨识id,微信接口一定要加
     * $original——类别：1--伯嘉；2--qq；3--微博；4——微信；5——中金
     *
     * */
    public function JudgeCreate($openid,$unionID=0,$original,$callback, $zjUserID=0){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);

        logs('|-original-|' . $original . '|-zjUserID-|' . $zjUserID, $logFile);

        if(!$openid  || !$original || !$callback){
            echo json_encode(array('flag'=>'10025', 'msg'=>'创建判断有关的数据缺失！'));
            exit;
        }
        
        $domain = get_host_domain($callback);

        if(!is_allowed_login_domain($domain)){
            echo json_encode(array('flag'=>'10033', 'msg'=>'请求URL不允许！'));
            exit;
        }

    
        $ip=$this->input->ip_address();//获取当前登录ip

        $userinfo = $this->User_Model->getOauthinfo(array('OpenID'=>$openid,'UnionID'=>$unionID,'original'=>$original), 'OauthID,UserID');

        if(isset($userinfo['UserID'])){ //已登录过
            //清空残留cookie
            $this->User_Interact->delCnfolCookie();
            //跳转
            $param=array(
                'UserID'=>$userinfo['UserID'],
                'LoginType'=>1,
                'LoginIP'=>$ip,
                'LoginTime'=>NOW_TIME,
                'LoginPage'=>$original,
                'LoginAddr'=>'0',
            );
            $this->User_Model->userLoginLog($param);

            //获取用户信息
            $result=$this->User_Model->getUserBaseByUserID($userinfo['UserID']);
            logs('|-第三方登录用户信息$result-|'. print_r($result, true),$logFile);

            if(!is_array($result) || !isset($result['UserID'])){
                echo json_encode(array('flag'=>'10026', 'msg'=>'表格获取数据缺失!'));
                echo '<script>alert("表格获取数据缺失!");window.location.href="'.TRADE_WEB_URL.'";</script>';
                exit;
            }
            if(!isset($result['Status']) || $result['Status']!=1){
                echo json_encode(array('flag'=>'10030', 'msg'=>'账号已锁定!'));
                echo '<script>alert("账号已锁定!");window.location.href="'.TRADE_WEB_URL.'";</script>';
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

        }else{//未登录过
            //输入新建映射表与用户表所需的数据
            $oparam=array(
                'Password'=>sprintf('%06s',rand(0,9999999)).'@',
                'Status'=>1,
                'RegPlatFormType'=>1,
                'RegIP'=>$ip,
                'LastLoginIP'=>$ip,
                'OpenID'=>$openid,
                'Original'=>$original,
                'UnionID'=>$unionID
            );
            //新建映射表与用户表
            $res=$this->User_Model->setOauthinfo($oparam);
            logs('|-第三方登录新建用户信息$oparam-|'. print_r($oparam, true).'|-第三方登录新建用户信息结果$res-|'. print_r($res, true),$logFile);
            if($res['Code']=='00'){

                if($original == 5 && $zjUserID){
                    $this->load->helper('api');
                    //调中金金融超市绑定用户接口
                    $fsParam = array(
                        'action' => 'setuserbinding',
                        'instid' => INSTID, //机构ID，伯嘉正式固定52
                        'proid' => PROID, //项目ID，伯嘉正式固定19
                        'bindtype' => 2, //1:绑定 2:开户
                        'userid' => $zjUserID, //中金用户ID
                        't' => time()
                    );
                    $fsParam['secretkey'] = getMysign($fsParam, ENCODE_KEY);
					if(isset($_COOKIE['recid']))
						$fsParam['recid'] = $_COOKIE['recid'];
                    logs('|-fsParam-|' . print_r($fsParam, true), $logFile);
                    $cgUrl = FINANCIAL_SUPERMARKET_USERBIND_URL . '?' . http_build_query($fsParam);
                    logs('|-cgUrl-|' . $cgUrl, $logFile);
                    $fsRs = curl_get($cgUrl);
                    logs('|-fsRs-|' . print_r($fsRs, true), $logFile);
                }

                cnfol_location($callback);
            }else{
                $ms='您的错误代码是：'.$res['Code'].'错误原因：'.$res['Msg'];
                echo '<script>alert('.$ms.');window.location.href='.TRADE_WEB_URL.';</script>';
                exit;
            }
        }
    }
}

/* End of file stockquote.php */
/* Location: ./application/controllers/stockquote.php */