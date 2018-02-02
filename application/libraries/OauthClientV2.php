<?php
/**
* @功能：OAUTH2.0 流程类
*
* @author huanggy 2011-12-05
*/

/**
* @功能：Oauth 认证异常处理
*/
class OauthExceptionV2 extends Exception
{

}

/**
* @功能：Oauth认证类
*/

class OauthClientV2
{
	/**
	* 应用ID
	*/
	private $client_id;

	/**
	* 用户密钥
	*/
	private $client_secret;

	/**
	* openid
	*/
	public $openid;

	/**
	* 回调地址
	*/
	private $redirect_uri;

	/**
	* 返回结果的形式
	*/
	public $response_type = 'code';

	/**
	* 授权页面类型
	*/
	public $display = 'default';
	
	/**
	* 传参
	*/
	public $state;
	
	/**
	* 请求类型
	*/
	public $grant_type = 'authorization_code';


	/**
	* 构造函数
	*/
	public function __construct($client_id=null, $client_secret=null, $redirect_uri=null)
	{
		if($client_id) $this->setClientId($client_id);
		if($client_secret) $this->setClientSecret($client_secret);
		if($redirect_uri) $this->setRedirectUri($redirect_uri);

		return $this;
	}
	
	/**
	* 设置应用ID
	*/
	public function setClientId($client_id)
	{
		$this->client_id = $client_id;
		return $this;
	}
	
	/**
	* 设置应用密钥
	*/
	public function setClientSecret($client_secret)
	{
		$this->client_secret = $client_secret;
		return $this;
	}
	
	/**
	* 设置回调地址 
	*/
	public function setRedirectUri($redirect_uri)
	{
		$this->redirect_uri = $redirect_uri;
		return $this;
	}

	/**
	* 认证第一步：返回用户授权地址
	*
	* @param string $authrize_url 执行用户授权地址
	* @param array $get_param  用户授权地址额外添加的参数，通过GET传递
	*/
	public function authorizeUrl($authorize_url, $get_param=array())
	{
       /* stripos() 函数查找字符串在另一字符串中第一次出现的位置（不区分大小写）。
       注释：stripos() 函数是不区分大小写的。注释：该函数是二进制安全的。*/

		$sep = stripos($authorize_url, '?') === false ? '?' : '&';
		if( stripos($authorize_url, 'client_id=') === false ) $get_param['client_id'] = $this->client_id;
		if( stripos($authorize_url, 'redirect_uri') === false ) $get_param['redirect_uri'] = urlencode($this->redirect_uri);
		if( stripos($authorize_url, 'response_type') === false ) $get_param['response_type'] = $this->response_type;
        if( stripos($authorize_url, 'scope') === false ) $get_param['scope'] = urlencode('get_user_info,add_share');//配置申请用户授权列表 add by chen @2012-5-3
		foreach((array) $get_param as $k=>$v)
		{
			$authorize_url .= $sep.$k.'='.$v;
			if($sep == '?') $sep = '&';
		}

		return $authorize_url;
	}
	
	/**
	* 认真第二步：获取通过认证的ACCESS_TOKEN
	*/
	public function getAccessToken($access_token_uri, $param=array())
	{
		$sep = stripos($access_token_uri, '?') === false ? '?' : '&';
		if( stripos($access_token_uri, 'client_id=') === false ) $param['client_id'] = $this->client_id;
		if( stripos($access_token_uri, 'client_secret') === false ) $param['client_secret'] = $this->client_secret;
		
		return $this->post($access_token_uri, $param);
	}
	
	/**
	* 获取ACCESS_TOKEN之后调用接口
	*
	* @param string $open_api_url 接口地址
	* @param string $token ACCESS_TOKEN
	* @param string $method 请求方法 GET/POST
	* @param array $data 请求参数
	*/
	public function doOpenApi($open_api_url, $token, $method='get', $data=array())
	{
		if( !isset($data['access_token']) ) $data['access_token'] = $token;
		if( strtoupper($method) === 'GET')
			return $this->get($open_api_url, $data);
		else return $this->post($open_api_url, $data);
	}
	/**
	* HTTP请求类
	*/
	public function http($url, $header=array(), $data='')
	{
		$ch = curl_init();
		if(empty($ch)) return false;

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		curl_setopt($ch, CURLOPT_HEADER, 0);
		if(!empty($header) && is_array($header))
		{
			foreach($header as $k=>$v)
			{
				curl_setopt($ch, $k, $v);
			}
		}

		if(!empty($data))
		{
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		}

		$respone = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		return array('respone'=>$respone, 'code'=>$http_code);
	}
	
	/**
	* 发起GET请求
	*/
	public function get($url, $param=array())
	{

		if( is_array($param) && !empty($param) )
		{
			$sep = stripos($url, '?') ===  false ? '?' : '&';
			$url .= $sep.http_build_query($param);
		}

		$respone = $this->http($url);
		return $respone['respone'];
	}
	
	/**
	* 发起POST请求
	*/
	public function post($url, $param=array())
	{
		if(!empty($param)) $param = http_build_query($param);
		$respone = $this->http($url, array(), $param);
		return $respone['respone'];
	}

}
?>