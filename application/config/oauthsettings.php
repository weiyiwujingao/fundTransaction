<?php
defined('BASEPATH') OR exit('No direct script access allowed');

//第三方登录配置文件

$config['multi_account'] = array();

#新浪微博登录
$config['multi_account']['tsina']['client_id']        = '2828894773';
$config['multi_account']['tsina']['client_secret']    = 'abb31f4d689df83e681aec12609699b2';
$config['multi_account']['tsina']['authorize_url']    = 'https://api.weibo.com/oauth2/authorize';
$config['multi_account']['tsina']['access_token_url'] = 'https://api.weibo.com/oauth2/access_token';
$config['multi_account']['tsina']['redirect_uri']     = TRADE_WEB_URL.'/multiaccount/callback?type=tsina';
$config['multi_account']['tsina']['get_account_info'] = 'https://api.weibo.com/2/users/show.json';
$config['multi_account']['tsina']['add_weibo']        = 'https://api.weibo.com/2/statuses/update.json';

#QQ登录OAUTH
$config['multi_account']['qq']['client_id']        = '101372824';
$config['multi_account']['qq']['client_secret']    = '2266cb2d3a6d6cba1d227c7ca796ab30';
$config['multi_account']['qq']['authorize_url']    = 'https://graph.qq.com/oauth2.0/authorize';
$config['multi_account']['qq']['access_token_url'] = 'https://graph.qq.com/oauth2.0/token';
$config['multi_account']['qq']['redirect_uri']     = TRADE_WEB_URL.'/multiaccount/callback?type=qq';
$config['multi_account']['qq']['get_openid']       = 'https://graph.qq.com/oauth2.0/me';
$config['multi_account']['qq']['get_account_info'] = 'https://graph.qq.com/user/get_user_info';
$config['multi_account']['qq']['add_t']          	= 'https://graph.qq.com/t/add_t';



#微信登录
$config['multi_account']['wechat']['client_id']        = 'wxff74ba466eab40ca';
$config['multi_account']['wechat']['client_secret']    = '4e922c4eb4daf06b4bd2e8187953216c';
$config['multi_account']['wechat']['authorize_url']    = 'https://open.weixin.qq.com/connect/qrconnect';
$config['multi_account']['wechat']['authorize_userinfo'] = 'https://api.weixin.qq.com/sns/userinfo';
$config['multi_account']['wechat']['access_token_url'] = 'https://api.weixin.qq.com/sns/auth';
$config['multi_account']['wechat']['redirect_uri']     = TRADE_WEB_URL.'/multiaccount/callback?type=wechat';
$config['multi_account']['wechat']['getoath_url'] = 'https://api.weixin.qq.com/sns/oauth2/access_token';
$config['multi_account']['wechat']['getoath2_url'] = 'https://api.weixin.qq.com/sns/userinfo';

#中金登录
$config['multi_account']['cnfol']['oauth_user_id']        = 7;
$config['multi_account']['cnfol']['oauth_key']        = 'f41f7c00f8312dcda1839c66f5bfabc60589d77ce';
$config['multi_account']['cnfol']['channel']        = 11;
$config['multi_account']['cnfol']['authorize_url']    = 'https://passport.cnfol.com/userlogin/oauthloginpage';
$config['multi_account']['cnfol']['oauth_return']        = TRADE_WEB_URL.'/multiaccount/callback?type=cnfol';//回跳地址
$config['multi_account']['cnfol']['getoath_url']        = 'https://passport.cnfol.com/oauthphp/verfiyauth.php';//验证地址


?>