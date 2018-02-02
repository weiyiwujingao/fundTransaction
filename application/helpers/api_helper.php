<?php defined('BASEPATH') OR exit('No direct script access allowed');
/****************************************************************
 * 伯嘉基金 - API函数 v1.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2016 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:zf $addtime:2016-11-15
 ****************************************************************/


/**
 * 根据反馈回来的信息，生成签名结果
 * @param $para_temp 通知返回来的参数数组
 * @return 生成的签名结果
 */
function getMysign($para_temp, $key, $sign_type = "MD5") {
    //除去待签名参数数组中的空值和签名参数
    $para_filter = paraFilter($para_temp);

    //对待签名参数数组排序
    $para_sort = argSort($para_filter);

    //生成签名结果
    $mysign = buildMysign($para_sort, trim($key), strtoupper(trim($sign_type)));

    return $mysign;
}

/**
 * 生成签名结果
 * @param $sort_para 要签名的数组
 * @param $key 支付宝交易安全校验码
 * @param $sign_type 签名类型 默认值：MD5
 * return 签名结果字符串
 */
function buildMysign($sort_para,$key,$sign_type = "MD5") {
    //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
    $prestr = createLinkstring($sort_para);
    //把拼接后的字符串再与安全校验码直接连接起来
    $prestr = $prestr.$key;
    //把最终的字符串签名，获得签名结果
    $mysgin = sign($prestr,$sign_type);
    return $mysgin;
}
/**
 * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
 * @param $para 需要拼接的数组
 * return 拼接完成以后的字符串
 */
function createLinkstring($para) {
    $arg  = "";
    while (list ($key, $val) = each ($para)) {
        $arg.=$key."=".$val."&";
    }
    //去掉最后一个&字符
    $arg = substr($arg,0,count($arg)-2);

    //如果存在转义字符，那么去掉转义
    if(get_magic_quotes_gpc()){$arg = stripslashes($arg);}

    return $arg;
}
/**
 * 除去数组中的空值和签名参数
 * @param $para 签名参数组
 * return 去掉空值与签名参数后的新签名参数组
 */
function paraFilter($para) {
    $para_filter = array();
    while (list ($key, $val) = each ($para)) {
        if($key == "keystr" || $val == "")continue;
        else	$para_filter[$key] = $para[$key];
    }
    return $para_filter;
}
/**
 * 对数组排序
 * @param $para 排序前的数组
 * return 排序后的数组
 */
function argSort($para) {
    ksort($para);
    reset($para);
    return $para;
}
/**
 * 签名字符串
 * @param $prestr 需要签名的字符串
 * @param $sign_type 签名类型 默认值：MD5
 * return 签名结果
 */
function sign($prestr,$sign_type='MD5') {
    $sign='';
    if($sign_type == 'MD5') {
        $sign = md5($prestr);
    }elseif($sign_type =='DSA') {
        //DSA 签名方法待后续开发
        die("DSA 签名方法待后续开发，请先使用MD5签名方式");
    }else {
        die("暂不支持".$sign_type."类型的签名方式");
    }
    return $sign;
}


/**
 * curl_post 模拟post
 *
 * @param string $url 请求地址
 * @param array $curlPost 请求参数
 * @return mixed
 */
function curl_post2($url, $curlPost, $timeout = '30') {
	
    $ssl = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
    $ch = curl_init();
    if (is_array($curlPost)) {
        $curlPost = http_build_query($curlPost);
    }
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // 设置超时限制防止死循环
    if ($ssl) {
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    }
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

 /* End of file api_helper.php */
/* Location: ./application/helper/api_helper.php */