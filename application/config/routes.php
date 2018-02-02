<?php defined('BASEPATH') OR exit('No direct script access allowed');
/****************************************************************
 * 伯嘉基金 - 全站路由配置 v1.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2016 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:linfeng $addtime:2016-11-15
 ****************************************************************/

/* 默认路由 */
$route['default_controller'] = '_user/Center';


/* 404页面设置 */
$route['404_override'] = '';

/* 将uri中的"-"转换成"_" TRUE:开启 FALSE:关闭 */
$route['translate_uri_dashes'] = FALSE;

/* 用户申购基金页面 */
$route['trade/fundtrade.html'] = '_trade/Fundtrade_manage/index';
/* 用户申购基金预览页面 */
$route['trade/preview.html'] = '_trade/Fundtrade_manage/fundPurchasePreview';
/* 用户申购基金状态页面 */
$route['trade/state.html'] = '_trade/Fundtrade_manage/fundPurchaseState';

/* 用户认购基金页面 */
$route['trade/subscription.html'] = '_trade/Fundtrade_manage/subscription';
/* 用户认购基金预览页面 */
$route['trade/subscriptionpreview.html'] = '_trade/Fundtrade_manage/subscriptionPreview';
/* 用户认购基金状态页面 */
$route['trade/subscriptionstate.html'] = '_trade/Fundtrade_manage/subscriptionState';

/* 用户卖基金页面 */
$route['trade/sellfund.html'] = '_trade/Fundtrade_manage/Sell_fund';
/* 用户卖基金操作页面 */
$route['trade/sell.html'] = '_trade/Fundtrade_manage/sell';
/* 用户卖基金操作预览页 */
$route['trade/sellpreview.html'] = '_trade/Fundtrade_manage/Sell_preview';
/* 用户卖基金状态页面 */
$route['trade/fundsellstate.html'] = '_trade/Fundtrade_manage/fund_sell_state';

/* 修改分红首页 */
$route['trade/dividendmethod.html'] = '_trade/Fundtrade_manage/dividend_method';
/* 基金修改分红确认页面 */
$route['trade/dividendmethod/([0-9]+).html'] = '_trade/Fundtrade_manage/dividend_method_confirm/$1';
/* 基金修改状态页面 */
$route['trade/dividendstatus.html'] = '_trade/Fundtrade_manage/dividend_method_status';

/* 基金撤单首页 */
$route['trade/fundrevoke.html'] = '_trade/Fundtrade_manage/fund_revoke';
/* 基金撤单确认页面 */
$route['trade/revoke/([0-9]+).html'] = '_trade/Fundtrade_manage/revoke/$1';
/* 基金撤单状态页面 */
$route['trade/revokestatus.html'] = '_trade/Fundtrade_manage/revoke_status';

/* 用户页面跳转-操作状态页面 */
$route['manage/(\w+).html'] = '_trademanage/Trade_manage/$1';

/* api定时脚本 */
$route['shell/jsfundvar.html'] = '_shell/Shell_manage_fund/JsFundvar';
$route['shell/(\w+).html'] = '_shell/Shell_manage_fund/$1';

/* 用户组ajax调用 */
$route['trade/ajax.html'] = '_trade/Fund_Ajax_manage/index';
/* 用户组ajax调用 */
$route['fare/api.html'] = '_trade/Fund_get_api/index';

/* 用户申购基金页面 */
$route['trade/test.html'] = '_trade/Show/index';
$route['trade/api.html'] = '_trade/Show/apiTest';


/* 用户登录页*/
$route['user/login.html'] = '_user/Login/index';
$route['user/logout.html'] = '_user/Login/logout';
$route['user/login/(\w+)'] = '_user/Login/$1';
$route['user/widgetlogin.html']='_user/Login/thirdlogging';
$route['multiaccount\/(.*)$'] = '_user/Loginwidget/$1';
/* 用户注册页*/
$route['user/register.html'] = '_user/Register/index';
$route['user/register/(\w+)'] = '_user/Register/$1';
/*用户找回密码页面*/
$route['user/findpwdcheck.html'] = '_user/Register/pwdindex';
$route['user/findpwdnew.html'] = '_user/Register/intoNewpwdAction';
$route['user/findpwdsucceed.html'] = '_user/Register/intoSucpwdAction';
/* 银行信息ajax */
$route['user/ajaxcenter/(\w+)'] = '_user/Ajaxcenter/$1';

/* 用户中心页页*/
$route['user/register.html'] = '_user/Register/index';

$route['user/center/(\w+)'] = '_user/Center/$1';
$route['user/center/unbindbank/([0-9]+).html'] = '_user/Center/unbindbank/$1';

/* 用户验证码接口*/
$route['ajax/verifycode.html'] = '_user/Verificationcode/index';
$route['ajax/verifycodeshow.html'] = '_user/Verificationcode/show';

/* API 接口 */
$route['api/fund/(\w+)'] = '_api/Fund/$1';

/* 基金定投*/
$route['fix/index.html'] = '_fix/Funds_fix/index';
$route['fix/new.html'] = '_fix/Funds_new/index';
$route['fix/new_preview.html'] = '_fix/Funds_new/preview';
$route['fix/new_result.html'] = '_fix/Funds_new/result';
$route['fix/stop.html'] = '_fix/Funds_fix/stop';
$route['fix/stop_revert/([0-9]+).html'] = '_fix/Funds_fix/revert/$1';
$route['fix/pause/([0-9]+).html'] = '_fix/Funds_operate/pause/$1';
$route['fix/resume/([0-9]+).html'] = '_fix/Funds_operate/resume/$1';
$route['fix/modify/([0-9]+).html'] = '_fix/Funds_operate/modify/$1';
$route['fix/abort/([0-9]+).html'] = '_fix/Funds_operate/abort/$1';
$route['fix/((pause|resume|modify|abort))_result.html'] = '_fix/Funds_operate/result';
// ajax请求
$route['fix/searchcodesbyletter'] = '_fix/Funds_new/searchcodesbyletter';
$route['fix/searchfunds'] = '_fix/Funds_new/searchfunds';
$route['fix/getfundinfo/(\w+)'] = '_fix/Funds_new/getfundinfo/$1';
$route['fix/getfare'] = '_fix/Funds_new/getfare';
$route['fix/getdeductionday'] = '_fix/Funds_new/getdeductionday';

/* 基金产品 */
$route['product/convert.html'] = '_product/Funds_convert/index';
$route['product/convert_step/([0-9]{6}).html'] = '_product/Funds_convert/step/$1';
$route['product/convert_result.html'] = '_product/Funds_convert/result';
// ajax请求
$route['product/getallshare'] = '_product/Funds_convert/getallshare';

/* 交易查询 */
$route['transaction/bill.html'] = '_transaction/Bill/index';
$route['transaction/log.html'] = '_transaction/Log/index';

/* 用户状态脚本 */
$route['shell/user/(\w+).html'] = '_shell/Shell_backstage_staus/$1';

/* 中金用户跳转页面 */
$route['trade/tradecnfol/(\w+).html'] = '_trade/Fundtrade_cnfol/$1';
/* 推荐跳转页面 */
$route['trade/recommend/(\w+).html'] = '_trade/Fundtrade_commission/$1';
/* 推荐佣金更新脚本 */
$route['shell/recommend/(\w+).html'] = '_shell/Shell_comssion_staus/$1';

/* End of file routes.php */
/* Location: ./application/config/routes.php */