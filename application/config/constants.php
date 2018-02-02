<?php defined('BASEPATH') OR exit('No direct script access allowed');
/****************************************************************
 * 伯嘉基金 - 全站常量配置 v1.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2016 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:linfeng $addtime:2016-11-15
 ****************************************************************/
/* 系统常量配置 */
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0755);
define('FOPEN_READ', 'rb');
define('FOPEN_READ_WRITE', 'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb');
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE', 'w+b');
define('FOPEN_WRITE_CREATE', 'ab');
define('FOPEN_READ_WRITE_CREATE', 'a+b');
define('FOPEN_WRITE_CREATE_STRICT', 'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT', 'x+b');
define('SHOW_DEBUG_BACKTRACE', TRUE);
define('EXIT_SUCCESS', 0);
define('EXIT_ERROR', 1);
define('EXIT_CONFIG', 3);
define('EXIT_UNKNOWN_FILE', 4);
define('EXIT_UNKNOWN_CLASS', 5);
define('EXIT_UNKNOWN_METHOD', 6);
define('EXIT_USER_INPUT', 7);
define('EXIT_DATABASE', 8);
define('EXIT__AUTO_MIN', 9);
define('EXIT__AUTO_MAX', 125);

define('DS',DIRECTORY_SEPARATOR); //斜杠
define('SPOT', '.'); //点
define('NOW_TIME', date('Y-m-d H:i:s'));

/* 自定义常量配置 */
defined('ENV') or define('ENV', 'product_');
defined('MEMORY_LIMIT_ON') or define('MEMORY_LIMIT_ON', function_exists('memory_get_usage'));
defined('MAGIC_QUOTES_GPC_ON') or define('MAGIC_QUOTES_GPC_ON', get_magic_quotes_gpc());
define('APPLICATION_PATH', dirname(dirname(__FILE__)));
define('HEAD_PATH', WEB_PATH . DS . 'head/');

define('MAXLOGINTIME', 900); //最长在线时间
define('SEND_SMS_SIGN', 'sendsmscache'); //短信发送缓存标识


define('LOGIN_LIMIT_KEY', '5z*8ip9nWKS3wAaeh&AQPFyaA@PwUmMPEOIJaFK%E8&W9pSHh*XGiUTuZGGZCfbcagZb4@c$VOE!SpQTfcZhSunla&UxRBnlD4#NNl1hOrHFh%O9NxwQ%Cw7!1OsPKCufh83k#3od^HFSQNdZn&o0u#dxQexpHuG^qUD%HQsG&GSLgtj6IxpujSDBq3Egl4uBxI3oIF#mY#ODP#7a1yB@$HJxBV7UPI30iIwXeM3!NZmfr%y84cMI$9IQjYSfrTb');
//登录页使用的KEY
define('LOGIN_PAGE_KEY', 'QPFylA@PwUmMuEOIJaFK%E9W5RSHh*XGiUTuZGGZCfbcagZb4@c$VOE&5z*8ip9NWKS3wAaeh&A!SpQTfcZhSunla&UxRBnlD4#NNl1hOrHFh%O9NxwQ%Cw7!1OsPKCufh83k#3od^HFSQNdZnujSDBqexgl4uBxI3oIF#mY#ODP#7a1yB@$HJxBV7UPI30iIwXeM3!NZmfr%y84cMI$9IQjYSfrTb&o0u#dxQmmpHuG^qUD%HQsG&GSLgtj6Ixp');
define('LOGIN_TWO_LIMIT_KEY', 'z*8ip9NWKS3oAaeh&A!SpQTfxjmSunla&UxRBnlD4#NNl1hOxgFh%O9NxwQ%Cw7!1OsPKCufh83QPFyaA@PwUmMPEOIJaFK%E8W5RSHh*XGiUTuZGGZCfbcagZb4@c$Vfr%y84cMI$9IQjYSfrTb&o0u#dxQmmpHuG^qUD%HQsG&GSLgtj6IxpnujSDBq3Egl4uBxIOE&5k#3od^HFSQNdZ3oIF#mY#ODP#7a1yB@$HJxBV7UPI30iIwXeM3!NZm');
define('PASSPORT_AUTH_KEY' , 'QPFyaA@PwUmMPEOIJaFK%E8W5RSHh*XGiUTuZGGZCf5sxPDb4@c$VOE&5z*8ip9NWKS3wAaeh&A!SpQTfcZhSunla&UxRBnlD4#NNl1hOrHFh%O9NxwQ%Cw7!1OsPKCuew51k#3od^HFSQNdZnujSDBq3Egl4uBxI3oIF#mY8ODP#7a1yB@$HJxBV7UPI30iIwXeM3!NZmfr%y84cMI$9IQjYSfrTb&o0u#dxQmmpHuG^qUD%HQsG&GSLgtj6Ixo');

//注册页key
define('ENCODE_KEY', 'IF#mY#ODP#7a1yB@$HJxBV7UPI30iIwXeM3!NZm');

/* SEO关键字和描述 */
define('SEO_KEYWORDS', '基金,基金销售,基金第三方销售平台,网上买基金,现金宝,质押宝,固定收益,打新基金,高端理财,基金申购,基金认购,基金费率,免费开户,网上基金开户,开户流程');
define('SEO_DESCRIPTION', '证监会批准的独立基金销售机构，民生银行全程监管资金安全。网上买基金，首选伯嘉。7*24小时免费基金开户，基金开户流程方便快捷，仅需10秒。');


//*****网址设置*****BEGIN*****//
define('WEB_DOMAIN_NAME', 'buyfunds.cn'); //主域名
define('WEB_URL', 'http://www.buyfunds.cn'); //主页
define('WEB_ABOUT_US', WEB_URL . '/main/aboutus'); //关于我们
define('WEB_CONTACT_US', WEB_URL . '/main/contactus'); //联系我们
define('WEB_HELP', WEB_URL . '/bangzhuzhongxin'); //帮助中心
define('WEB_PROTOCOL', WEB_URL . '/jijinketang/20170206/24244564.shtml'); //开户协议
define('TRADE_WEB_URL', 'https://trade.buyfunds.cn');//用户中心网站URL
define('TRADE_REG_URL', TRADE_WEB_URL . '/user/register.html');//用户中心注册页
define('TRADE_LOGIN_URL', TRADE_WEB_URL . '/user/login.html');//用户中心登录页
define('TRADE_LOGOUT_URL', TRADE_WEB_URL . '/user/logout.html');//用户中心退出
define('TRADE_FINDPWD_URL', TRADE_WEB_URL . '/user/findpwdcheck.html');//用户中心找回密码
define('TRADE_HEAR_URL', TRADE_WEB_URL . '/head/');//用户中心找回密码
define('TRADE_WIDGET_URL',TRADE_WEB_URL.'/user/center/index');//用户中心第三方登录跳转页面
define('TRADE_RISK_QUES_URL',TRADE_WEB_URL.'/user/center/riskevaluation');//问卷调查页面
define('TRADE_RISK_QUES_RESULT_URL',TRADE_WEB_URL.'/user/center/riskevaluationrs');//问卷调查结果页
define('TRADE_REG_BANK_URL',TRADE_WEB_URL.'/user/center/regbindbank');//首次绑定银行卡页面
define('TRADE_BANK_LIST_URL',TRADE_WEB_URL.'/user/center/banklist');//绑定银行卡页面
define('TRADE_ADD_BANK_URL',TRADE_WEB_URL.'/user/center/addbank');//绑定银行卡页面
define('TRADE_PERSONAL_URL',TRADE_WEB_URL.'/user/center/presonal');//个人信息修改页
define('TRADE_BINDPHONE_URL',TRADE_WEB_URL.'/manage/bindphone.html');//手机号码绑定
define('TRADE_MODIFYPHONE_URL',TRADE_WEB_URL.'/manage/modifyphone.html');//手机号码修改
define('TRADE_LGPWD_URL',TRADE_WEB_URL.'/user/center/Loginpwd');//登录密码修改
define('TRADE_TRPWD_URL',TRADE_WEB_URL.'/user/center/tradePwd');//交易密码修改
define('TRADE_RECOMMEND_URL',TRADE_WEB_URL . '/trade/recommend/trade.html');//推荐投资链接


//站外接口
define('FINANCIAL_SUPERMARKET_USERBIND_URL', 'http://licai.api.cnfol.com/fund.html');//金融超市绑定用户接口
define('INSTID', 52);//机构ID，伯嘉固定1
define('PROID', 19);//项目ID，伯嘉固定1


/* 恒生STI环境接口,用于获取全站access_token */
define('STI_OAUTH2', 'qqq ');
define('CLIENT_ID', 'qqq');
define('CLIENT_SECRET', 'qq');
define('GRANT_TYPE', 'client_credentials');
define('OPEN_ID', 'qq'));

/* 恒生理财和支付接口,用于获取公共参数 */
define('STI_CWSALE', 'qq');//理财接口
define('STI_CWPAY', 'qq');//支付接口
define('TARGETCOMP_ID', 'qq');//机构号，唯一分配
define('SENDERCOMP_ID', 'qq');

//交易委托方式  2代表的是网上委托 7 代表的是手机委托
define('TRANSACTION_MODE', '2');
//1-普通方式,G-招行网银,l-快钱,I-富友,M-通联,P-易宝,r-收付捷,AI-宝付,AA-快付通,3-银联通,6-汇付天下
define('CAPITAL_MODE', 'l');//资金方式
//客户类别 1-个人 0-机构
define('CUST_TYPE', '1');
//币种类别:0-人民币;1-美  元;2-港  币;3-SZ-HKD;6-AUD;E-CAD;Q-GBP;k-丹麦克朗;o-澳门元;r-瑞典克朗;t-SGD;v-THB;S-IDR;f-MYR;l-PHP;M-EUR;W-JPY;w-TWD
define('CURRENCY', '0');
//账户类型(A-普通账户，默认传A)
define('ACCOUNT_TYPE', 'A');
//TA编号
define('TA_NO', '98');
//商户名称
define('MERCHANT_NAME', 'BJJJ');
//交易来源
define('SOURCE_TRADE', '');
//资金明细方式
define('DETAIL_FUND_WAY', '01');
//默认国籍 中国 156
define('FUND_NATIONALITY', 156);
/* 缓存过期时间 */
define('ONE_DAYS', 3600*5);
//用户操作有效周期，周期时间内不操作，自动退出
define('USER_ACTION_PERIOD', 60*30);


/* 目录配置 */
define('CACHE_PATH', APPPATH . 'cache' . DS);//缓存目录
define('LOG_PATH', '/var/tmp/trade.buyfunds.cn' . DS);//日志目录 


//允许登录的域名
$config['allowedLoginDomain'] = array('buyfunds.cn', 'cnfol.com');


/* End of file constants.php */
/* Location: ./application/config/constants.php */
