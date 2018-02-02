<?php defined('BASEPATH') OR exit('No direct script access allowed');
/****************************************************************
 * 伯嘉基金 - 公共函数 v1.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2016 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:linfeng $addtime:2016-11-15
 ****************************************************************/

/**
  * 输出友好的调试信息
  *
  * @param mixed $vars 需要判断的日期
  * @return mixed
  */
function t($vars)
{
	if(is_array($vars))
		exit("<pre><br>" . print_r($vars, TRUE) . "<br></pre>".rand(1000,9999));
	else
		exit($vars);
}

function pre($param='') {
    echo '<br/><pre>';
    var_dump($param);
    echo '</pre>';
}

function pres($str){
    
    $str = str_replace('[', '', $str);
    $str = str_replace(']', '', $str);
    $aT = explode("\r\n", $str);
    $att = array();
    foreach($aT as $ak => $av){
        if($av){
            $x = explode("=>", trim($av));
            //pre($x);
            $x1 = trim($x[0]);
            $x2 = trim($x[1]);
            $att[$x1] = $x2;
        }
    }
    return $att;
}

/**
  * 格式化缓存键名称
  *
  * @return string
  */
function get_keys()
{
    $argList = func_get_args();

	return join('_', $argList);
}

/**
  * 返回json结构,并支持ajax跨域
  *
  * @param array  $data 数组
  * @param string $call 匿名函数
  * @return json
  */
function returnJson($data = array(),$call ='')
{
	exit(empty($call) ? json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE) : $call.'('.json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE).')');
}
/**
  * 返回json结构,并支持ajax跨域
  *
  * @param array  $data 数组
  * @param string $call 匿名函数
  * @return json
  */
function returnJsonStr($data = array(),$call ='')
{
	exit(empty($call) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $call.'('.json_encode($data, JSON_UNESCAPED_UNICODE).')');
}
/**
  * 小数格式化
  *
  * @param mixed   $value 数据
  * @param integer $limit 保留位数
  * @param string  $mark  替代符号,一般用于数据为null或空字符串情况
  * @return mixed
  */
function formats($value, $limit = 2, $mark = '')
{
	$value = number_format($value, $limit, '.', '');

	return empty($value) ?  $value . $mark : $value;
}

/**  
  * 中文编码转换
  *
  * @param string|array $data      字符串|数组
  * @param string       $newencode 转换后的编码
  * @return string|array
  */  
function string_convert($data, $newencode = 'utf-8')
{  
    $encodeconfig = array('UTF-8', 'ASCII', 'GBK', 'GB2312', 'BIG5', 'JIS', 'eucjp-win', 'sjis-win', 'EUC-JP');

    $oldencode = mb_detect_encoding($data, $encodeconfig);

	return mb_convert_encoding($data, $newencode, $oldencode);
}

/**
  * CURL请求
  *
  * @param string  $url     请求地址
  * @param array   $data    请求数据 key=>value 键值对
  * @param integer $timeout 超时时间,单位秒
  * @param integer $ishttp  是否使用https连接 0:否 1:是
  * @return array
  */
function curl_post($url, $data, $timeout = 5)
{
	$ishttp = substr($url, 0, 8) == "https://" ? TRUE : FALSE;
	
	$ch = curl_init();
	if (is_array($data)) {
	    $data = http_build_query($data);
	}
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

	if($ishttp)
	{
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	}
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

	$result['data'] = curl_exec($ch);
	$result['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	 
	curl_close($ch);

	return $result;
}

/**
 * 远程获取数据，GET模式
 * 注意：
 * 1.使用Crul需要修改服务器中php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
 * 2.文件夹中cacert.pem是SSL证书请保证其路径有效，目前默认路径是：getcwd().'\\cacert.pem'
 * @param $url 指定URL完整路径地址
 * return 远程输出的数据
 */
function curl_get($url, $timeout = '30') {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_HEADER, 0 ); // 过滤HTTP头
    curl_setopt($curl,CURLOPT_RETURNTRANSFER, 1);// 显示输出结果
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);//SSL证书认证
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);//严格认证
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); // 设置超时限制防止死循环
    $responseText = curl_exec($curl);
    //var_dump( curl_error($curl) );//如果执行curl过程中出现异常，可打开此开关，以便查看异常内容
    curl_close($curl);

    return $responseText;
}

/**
 * 记录和统计时间(微秒)和内存使用情况
 * 使用方法:
 * <code>
 * 记录开始标记位 runTime('begin');
 * ... 区间运行代码
 * 记录结束标签位runTime('end');
 * 统计区间运行时间 精确到小数后6位 echo runTime('begin','end',6);
 * 统计区间内存使用情况echo runTime('begin','end','m');
 * 如果end标记位没有定义,则会自动以当前作为标记位
 * 其中统计内存使用需要 MEMORY_LIMIT_ON 常量为true才有效
 * </code>
 * @param string $start 开始标签
 * @param string $end   结束标签
 * @param integer|string $dec 小数位或者m
 * @return mixed
 */
function runTime($start, $end = '', $dec = 4)
{
    static $_mem  = array();
    static $_info = array();

    if(is_float($end))
	{ 
		/* 记录时间 */
        $_info[$start] = $end;
    }
	else if(!empty($end))
	{ 
		/* 统计时间和内存使用 */
        if(!isset($_info[$end])) $_info[$end] = microtime(TRUE);

        if(MEMORY_LIMIT_ON && $dec=='m')
		{
            if(!isset($_mem[$end])) $_mem[$end] = memory_get_usage();
				
            return number_format(($_mem[$end] - $_mem[$start])/1024);
        }
		else
		{
            return number_format(($_info[$end] - $_info[$start]),$dec);
        }
    }
	else
	{	/* 记录时间和内存使用 */
        $_info[$start] = microtime(TRUE);

        if(MEMORY_LIMIT_ON) $_mem[$start] = memory_get_usage();
    }
    return NULL;
}
/**
 * 获取字符串首字母
 * @param string $str 字符串
 * @return string
 */
function getFirstCharter($str)
{

	if(empty($str)){return '';}

	$fchar=ord($str{0});

	if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0});

	$s1=iconv('UTF-8','gb2312',$str);

	$s2=iconv('gb2312','UTF-8',$s1);

	$s=$s2==$str?$s1:$str;

	$asc=ord($s{0})*256+ord($s{1})-65536;

	if($asc>=-20319&&$asc<=-20284) return 'A';

	if($asc>=-20283&&$asc<=-19776) return 'B';

	if($asc>=-19775&&$asc<=-19219) return 'C';

	if($asc>=-19218&&$asc<=-18711) return 'D';

	if($asc>=-18710&&$asc<=-18527) return 'E';

	if($asc>=-18526&&$asc<=-18240) return 'F';

	if($asc>=-18239&&$asc<=-17923) return 'G';

	if($asc>=-17922&&$asc<=-17418) return 'H';

	if($asc>=-17417&&$asc<=-16475) return 'J';

	if($asc>=-16474&&$asc<=-16213) return 'K';

	if($asc>=-16212&&$asc<=-15641) return 'L';

	if($asc>=-15640&&$asc<=-15166) return 'M';

	if($asc>=-15165&&$asc<=-14923) return 'N';

	if($asc>=-14922&&$asc<=-14915) return 'O';

	if($asc>=-14914&&$asc<=-14631) return 'P';

	if($asc>=-14630&&$asc<=-14150) return 'Q';

	if($asc>=-14149&&$asc<=-14091) return 'R';

	if($asc>=-14090&&$asc<=-13319) return 'S';

	if($asc>=-13318&&$asc<=-12839) return 'T';

	if($asc>=-12838&&$asc<=-12557) return 'W';

	if($asc>=-12556&&$asc<=-11848) return 'X';

	if($asc>=-11847&&$asc<=-11056) return 'Y';

	if($asc>=-11055&&$asc<=-10247) return 'Z';


	return null;

}
/**
 * 获取URL的一级域名
 * @param unknown $url
 * @return string
 */
function get_host_domain($url)
{
	$domain = '';
    if(empty($url)){ return $domain; } //URL为空
    $data = parse_url(trim($url));
    if(!isset($data['host'])){
        return $domain;
    }
    $sdata = $data['host'];
    $data = explode('.', $sdata);
    $domain = $data[count($data) - 2] . '.' . $data[count($data) - 1];
    if(strpos($sdata, 'com.cn')){
        $domain = $data[count($data) - 3] . '.' . $data[count($data) - 2] . '.' . $data[count($data) - 1];
    }
    
    return $domain;
}
/**
 * 是否为允许的登录一级域名
 * @param unknown $domain
 * @return boolean
 */
function is_allowed_login_domain($domain){

	if(empty($domain)){ return false; } //不存在域名

	$CI = &get_instance();
	//获取允许域名
	$allowedDomain = $CI->config->item('allowedLoginDomain');
	if($allowedDomain){ //存在允许登录的域名
		if(!in_array($domain, $allowedDomain)){
			return false;
		}
	}

	return true;
}

/**
 *
 * 预置一个的字符数组 $chars ，包括 a – z，A – Z，0 – 9，以及一些特殊字符
 * 通过array_rand()从数组 $chars 中随机选出 $length 个元素
 * 根据已获取的键名数组 $keys，从数组 $chars 取出字符拼接字符串。该方法的缺点是相同的字符不会重复取。
 *
 * @param number $length 随机选出 $length 个元素
 * @param string $type 类型 password, char, number, lowerchar, upperchar, username
 *
 * @return string
 */
function makeRandChar( $length = 8, $type='' ) {
    // 密码字符集，可任意添加你需要的字符
    switch ($type){
        case 'lowerchar':
            $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y','z');
            break;
        case 'upperchar':
            $chars = array('A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z');
            break;
        case 'char':
            $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z');
            break;
        case 'password':
            $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
            '@','#', '$', '_');
            break;
        case 'charnumber':
            $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
            break;
        case 'username':
            $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y','z', '0', '1', '2', '3',
            '4', '5', '6', '7', '8', '9', '_');
            break;
        default:
            $chars = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
            'i', 'j', 'k', 'l','m', 'n', 'o', 'p', 'q', 'r', 's',
            't', 'u', 'v', 'w', 'x', 'y','z', 'A', 'B', 'C', 'D',
            'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L','M', 'N', 'O',
            'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y','Z',
            '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '!',
            '@','#', '$', '%', '^', '&', '*', '(', ')', '-', '_',
            '[', ']', '{', '}', '<', '>', '~', '`', '+', '=', ',',
            '.', ';', ':', '/', '?', '|');
    }

    // 在 $chars 中随机取 $length 个数组元素键名
    $keys = array_rand($chars, $length);

    $string = '';
    for($i = 0; $i < $length; $i++) {
        // 将 $length 个数组元素连接成字符串
        $string .= $chars[$keys[$i]];
    }

    return $string;
}

 /**
 *
 * 简单防刷
 * 默认15秒
 * $obj string  可以是ip，userid，ip跟userid的组合或者其他参数
 * $sign string 功能说明
 * $period int 不允许请求间隔时间，即在period秒内不能进行第二次请求。默认15秒。 
 * $max 默认max+1次 内允许操作. 默认3次
 * 
 * 
 * $cInfo array 缓存信息。 startTime 第一次请求时间， lastTime 最近一次请求时间，times请求次数
 *
 */
function simpleAnti($obj, $sign='passport', $period=15, $max=2){

	$data = array('flag'=>'10000', 'msg'=>'');
	
    $CI = &get_instance();
    $CI->load->library('Buyfunds_mem',NULL,'buyfunds_mem');
    $pcache = $CI->buyfunds_mem;

	$limitKey = $sign . '_Limit_List';
	
	
	$aOkObj = $CI->config->item('whiteIPs');

	//白名单直接返回否
	if(in_array($obj, $aOkObj)){
			return $data;
	}
	
	$aBlackObj = $CI->config->item('blackIPs');
	if(in_array($obj, $aBlackObj)){
		$msg = '亲，您的操作太过于频繁';
		$data = array('flag'=>'10010', 'msg'=>$msg);
		return $data;
	}
	
	//是否已进入黑名单
	if(isLimitedObj($obj, $sign, $aOkObj)){
		//$data = array('flag'=>'10010', 'msg'=>$obj .'-已进入黑名单');
		
		$msg = '亲，您的操作过于频繁';
		$data = array('flag'=>'10010', 'msg'=>$msg);
		return $data;
	}
	
	
	$isLimit = 0; //是否加入黑名单 1是，0否
	$key = $obj . '_' .$sign;
    //$pcache->set($key,'',1);
	$setTmpCacheInfo = array();
	$cInfo = array();
    
	if($cRes = $pcache->get($key)){
		$cInfo = unserialize($cRes);
		//pre($cInfo);
		if($cInfo){
		$startTime = ($cInfo['lastTime'] - $cInfo['startTime'])>180 ? time() : $cInfo['startTime']; //最后时间与开始时间间隔超过180秒，开始时间记录值为当前时间
		$setTmpCacheInfo = array(
			'startTime' => $startTime,
			'lastTime' => time(),
			'times' => $cInfo['times']+1,
		);
		
		//请求密集，请求间隔少于period秒
		if(time()- $cInfo['lastTime']<$period && $cInfo['times']>$max){
			$data = array('flag'=>'20001', 'msg'=>'亲，您的操作过于频繁，过一会儿再操作');
			//return $aData;
		}
			
			//符合以下条件加入黑名单
			if($cInfo['lastTime']-$cInfo['startTime']<30 && $cInfo['times']>=30){ //30秒内请求大于等于30次
				$isLimit = 1;
				$data = array('flag'=>'20002', 'msg'=>'30秒内请求大于等于30次');
			} else if($cInfo['lastTime']-$cInfo['startTime']<60 && $cInfo['times']>=40){ //60秒内请求大于等于40次
				$isLimit = 1;
				$data = array('flag'=>'20003', 'msg'=>'60秒内请求大于等于40次');
			} else if($cInfo['lastTime']-$cInfo['startTime']<90 && $cInfo['times']>=60){ //90秒内请求大于等于60次
				$isLimit = 1;
				$data = array('flag'=>'20004', 'msg'=>'90秒内请求大于等于60次');
			} else if($cInfo['lastTime']-$cInfo['startTime']<120 && $cInfo['times']>=60){ //120秒内请求大于等于60次
				$isLimit = 1;
				$data = array('flag'=>'20005', 'msg'=>'120秒内请求大于等于60次');
			} else if($cInfo['lastTime']-$cInfo['startTime']<180 && $cInfo['times']>=100){ //180秒内请求大于等于100次
				$isLimit = 1;
				$data = array('flag'=>'20006', 'msg'=>'180秒内请求大于等于100次');
			} else if($cInfo['lastTime']-$cInfo['startTime']<300 && $cInfo['times']>=100){ //300秒内请求大于等于100次
				$isLimit = 1;
				$data = array('flag'=>'20007', 'msg'=>'300秒内请求大于等于100次');
			} else if($cInfo['lastTime']-$cInfo['startTime']<600 && $cInfo['times']>=100){ //600秒内请求大于等于100次
				$isLimit = 1;
				$data = array('flag'=>'20008', 'msg'=>'600秒内请求大于等于100次');
			} else if($cInfo['lastTime']-$cInfo['startTime']<6000 && $cInfo['times']>=100){ //6000秒内请求大于等于100次
				$isLimit = 1;
				$data = array('flag'=>'20009', 'msg'=>'6000秒内请求大于等于100次');
			}
			
			//符合加入黑名单条件
			if($isLimit == 1){
				$aLimits = array();
				//获取黑名单缓存
				if($cLimit = $pcache->get($limitKey)){
					$aLimits = unserialize($cLimit);
				}
				//$obj为键名，次数为键值
				$aLimits[$obj] = $cInfo['times'];
				$pcache->set($limitKey, serialize($aLimits), 60*60);//黑名单缓存1小时
				$pcache->set($key, serialize($setTmpCacheInfo), 10*60);//记录该IP请求数
				unset($aLimits);
				return $data;
			}

		} else {
			//临时限制内容
			$setTmpCacheInfo = array(
				'startTime' => time(),
				'lastTime' => time(),
				'times' => 1,
			);
		}
		
	} else {
		//临时限制内容
		$setTmpCacheInfo = array(
			'startTime' => time(),
			'lastTime' => time(),
			'times' => 1,
		);
	}


	$pcache->set($key, serialize($setTmpCacheInfo), 10*60);//记录该IP请求数
	unset($cInfo, $setTmpCacheInfo);
	return $data;
}
	

/**
 *
 * IP是否在黑名单里
 *
 **/
function isLimitedObj($obj, $project='passport', $aOkObj){
	
	$flag = false;

	//白名单直接返回否
	if(in_array($obj, $aOkObj)){
		return $flag;
	}

    $CI = &get_instance();
    $CI->load->library('Buyfunds_mem',NULL,'buyfunds_mem');
    $pcache = $CI->buyfunds_mem;
	
	$limitKey = $project . '_Limit_List';

	//将黑名单缓存key存入缓存
	$allLimitedKeys =  'All_Limited_Keys';
    
    //$pcache->set($allLimitedKeys,'',1);
	//$pcache->set($limitKey,'',1);
	//所有黑名单缓存key
	$aLimitsKeys = array();
	//获取黑名单缓存
	if($cLimitKeys = $pcache->get($allLimitedKeys)){
		$aLimitsKeys = unserialize($cLimitKeys);
		//pre($aLimitsKeys);
		//在黑名单里
		if( !isset($aLimitsKeys[$limitKey])){
			//$obj为键名，次数为键值
			$aLimitsKeys[$limitKey] = 1;
			$pcache->set($allLimitedKeys, serialize($aLimitsKeys), 30*60);//黑名单缓存半小时
		} else {
			
		}
	} else {
		$aLimitsKeys[$limitKey] = 1;
		$pcache->set($allLimitedKeys, serialize($aLimitsKeys), 30*60);//黑名单缓存半小时
	}

	
	//黑名单缓存
	$aLimits = array();
	//获取黑名单缓存
	if($cLimit = $pcache->get($limitKey)){
		$aLimits = unserialize($cLimit);
		//pre($aLimits);
		//在黑名单里
		if(isset($aLimits[$obj])){
			$flag = true;
			//$obj为键名，次数为键值
			$aLimits[$obj] += 1;
			$pcache->set($limitKey, serialize($aLimits), 60*60);//黑名单缓存1小时
		}
	}

	return $flag;

}
/**
 * 获取SIT token
 *
 * @return array
 */
 function getAccessToken($logfile)
{		
	$keys = ENV . 'buyfunds_access_token';
	$CI = &get_instance();
	$CI->load->library('Buyfunds_mem',NULL,'buyfunds_mem');
	$data = $CI->buyfunds_mem->get($keys);
	//$data = '';
	if(empty($data))
	{
		/* 常量配置在 CI config/constants.php */
		$param['open_id'] = OPEN_ID;
		$param['client_id']  = CLIENT_ID;
		$param['grant_type'] = GRANT_TYPE;
		$param['client_secret'] = CLIENT_SECRET;

		$json = curl_post(STI_OAUTH2, $param, 5);
		$data = json_decode($json['data'], TRUE);
		
		if(!isset($data['access_token']) || empty($data['access_token']))
		{
			logs('access_token获取失败' . PHP_EOL . 'json_data:' . print_r($data, TRUE), $logfile);

			return false;
		}

		/* 销毁无用的数据 */
		unset($param, $data['refresh_token'], $data['scope'], $data['expires_in']);

		$CI->buyfunds_mem->set($keys, $data['access_token'], ONE_DAYS);
		$data = $data['access_token'];
	}
	return $data;
}
/**
 * 搜索基金代码排序 
 *
 * @return array
 */
 function codeSort($keys,$search,$limit)
{
	if(!$keys) return ;
	$sort = $num = $codes = $code = '';
	$CI = &get_instance();
	$CI->load->library('Buyfunds_mem',NULL,'buyfunds_mem');
	$CI->load->library('Cnfol_file',NULL,'cnfol_file');
	//$codes = $CI->buyfunds_mem->get($keys);
	//if($codes) return $codes;
	
	$codemsg = $CI->cnfol_file->get('fundmsg');
	$codearr = $CI->cnfol_file->get('fundcode');
	foreach($codearr as $key=>$val){
		$sort = strpos(' '.$val,$search);
		if($sort){
			//第一组满足条件结束循环
			if($num==$limit) break;
			if($sort==1) $num += 1;			
			$code[$val] = $sort;
			
		}
	}
	if($code){
		asort($code);
		$num = '0';
		foreach($code as $k=>$v){
			if($num==$limit) break;
			$codes[$num]['code'] = $k; 
			$codes[$num]['name'] = $codemsg[$k]['name']; 
			$codes[$num]['types'] = $codemsg[$k]['types']; 
			$codes[$num]['type'] = $codemsg[$k]['type']; 
			$codes[$num]['sname'] = $codemsg[$k]['sname']; 
			$num++;
		}
		$CI->buyfunds_mem->set($keys, $codes, ONE_DAYS);
	}
	return $codes;
}
/**
 * 判断是否存在目录，不存在创建一个新的目录
 *
 * @param string $dir   目录串
 * @param string $module 权限
 * @return string
 */
function mk_dir($dir, $module=0775)
{
	if(!is_dir($dir))
	{
		if(!mkdir($dir, $module, TRUE))
		{
			@error_log($dir.'-'.'mkdir:create dir fail'.date('Ymd H:i:s').PHP_EOL, 3, LOG_PATH . '/make_dir.log');
			return false;
		}
	}
	create403Index($dir);
	return true;
}

/**
 *
 * 在空文件夹里创建403的index.html文件
 *
 */
function create403Index($fileFolder){
    $fileName = $fileFolder . '/index.html';
    if(!file_exists($fileName)){
        $fContent = '<!DOCTYPE html>
<html>
<head>
	<title>403 Forbidden</title>
</head>
<body>

<p>Directory access is forbidden.</p>

</body>
</html>
    ';
        file_put_contents($fileName, $fContent);
    }

}
/**
  * 日志加强版
  *
  * @param string $msg  股票代码
  * @param string $file 日志文件名
  * @return boolean
  */
function logs($msg, $file = 'system',$date='',$type='.log')
{
	
	$log = '['.date('H:i:s').']['.$msg.']'.PHP_EOL;
	$date = $date ? $date : date('Ymd');
	$filePath = LOG_PATH . $file . '/'  .$date . $type;
	
	if(mk_dir(LOG_PATH . $file)==false){
		return false;
	}

	return @error_log($log, 3, $filePath);
}

/**
 *
 *  请求是否超时
 *  默认15分钟
 *
 */
function isRequestTimeout($tid, $interval=900, $msg = '请求时间已失效，请刷新页面重试'){
    if($tid-time() > $interval || time()-$tid > $interval){
        /*请求超时*/
        echo json_encode(array('flag'=>'10002', 'msg'=>$msg, 'info'=>'[]')); exit;
    }
}

/**
 * 
 * 短信发送
 * @param unknown $info
 * @param number $sysType
 * @return multitype:string |number
 */
function sendSmsAction($info, $sysType=1) {
    
    $sign = (isset($info['sign']) &&  $info['sign']) ? (int)$info['sign'] : 0;
    $sendChannel = (isset($info['channel']) &&  $info['channel']) ? (int)$info['channel'] : 9; //发送频道 9阿里，6漫道
    @error_log(date('Y-m-d H:i:s'). ' |-info-| '. print_r($info, true) . $smsRs['state'] . PHP_EOL , 3, LOG_PATH.'/sendsmsaction_'.date('Ymd').'.log');

    $info['content']=urlencode($info['content']);
    $postParam=array('p.apiKey'=>'8BE5k8204bo33F382JIA951F24','p.can'=>1,'p.msg'=>$info['content'],'p.priority'=>0,'p.receiver'=>$info['mobile'],'p.sendChannel'=>$sendChannel,'p.smstype'=>2,'p.sysType'=>$sysType,'p.sign'=>$sign);
    $smsRs2=curl_post('http://mobilemessager.api.cnfol.net:8085/sms?', $postParam);
    
    if($smsRs2['code'] != 200){
        return array('flag'=>'50005','msg'=>'短信服务器连接失败！');
    }
    //pre($smsRs2);
    //$smsRs=curl_post('test.access.cnfol.com:7001/send.shtml?',$postParam);
    $smsRs=json_decode($smsRs2['data'], true);
    //pre($smsRs);
    @error_log(date('Y-m-d H:i:s'). '|-postParam-|' .print_r($postParam,true). '|-smsRs-|'. $smsRs['state'] . PHP_EOL , 3, LOG_PATH.'/sendsmsaction_'.date('Ymd').'.log');
    if($smsRs){
        if($smsRs['state'] == 0)
        {
            return array('flag'=>'10000','msg'=>'短信发送成功');
        }
        else if($smsRs['state'] == -10)
        {
            return array('flag'=>'50001','msg'=>'短信发送间隔不能少于3分钟');
        }
        else if($smsRs['state'] == -11)
        {
            return -2;
        }
        else if($smsRs['state'] == -9)
        {
            return array('flag'=>'50002','msg'=>'短信发送太过频繁！');
        }
        else if($smsRs['state'] == -8)
        {
            return array('flag'=>'50003','msg'=>'短信发送异常！');
        }
        else if($smsRs['state'] == -2)
        {
            return array('flag'=>'50007','msg'=>'无匹配TTS模板！');
        }
        else if($smsRs['state'] == -1)
        {
            return array('flag'=>'50008','msg'=>'无SMS匹配模板！');
        }
        else
        {
            return array('flag'=>'50004','msg'=>'其他错误！');
        }
    } else {
        return array('flag'=>'50006','msg'=>'短信服务器数据异常！');
    }

}


/**
 * @ set_sms_info 设置短信内容
 *
 * @param string $mobile 用户手机
 * @param string key 修改密码密钥
 * @param string key 7 为申购短信 $key['fname']-基金名称 $key['fcode']-基金代码 $key['money']--申请数额
 * @param string key 8 为申购短信 $key['fname']-基金名称 $key['fcode']-基金代码 $key['applymoney']--申请金额
 * $key['suremoney']--确认份额 $key['networth']--成交净值
 * @param string $type 短信类别
 * @param string $channel 发送通道：1-移动信息机，2-短信猫，3-短信王，4-莫名 9-阿里大鱼
 * @return array
 */
function set_sms_info($mobile, $key, $type, $channel=9) {

    if($type==7){
        if(!$key || !$key['fname'] || !$key['fcode'] || !$key['money']){
            $type=0;
        }
    }else if($type==8){
        if(!$key || !$key['fname'] || !$key['fcode'] || !$key['applymoney'] || !$key['suremoney'] || !$key['networth']){
            $type=0;
        }
    }
    if($type==7){
        $list=array(//申购通知
            7=>'尊敬的客户，您于'.date('Y').'年'.date('m').'月'.date('d').'日'.date('H').'时办理（'.$key['fname'].'和'.$key['fcode'].'）的申购业务，申请数额：'.$key['money'].'元，交易申请已受理。客服电话400-027-9899。',
        );
    }else if($type==8){
        $list=array(//申购确认
            8=>'尊敬的客户，您于'.date('m').'月'.date('d').'日（申请日期）申购（'.$key['fname'].'和'.$key['fcode'].'）已确认成功，申请金额'.$key['applymoney'].'元，确认份额'.$key['suremoney'].'份，成交净值为'.$key['networth'].'元，请登录伯嘉基金网，查看详情。客服电话400-027-9899。',
        );
    }else{
        $list=array(//1 注册短信，2 找回密码 3 重置交易密码 4 修改手机，5 绑定手机 6，提现
            0=>'数据输入错误！',
            1=>'尊敬的客户，您本次开户的验证码为：' . print_r($key,true). '，请及时输入。如有疑问请致电400-027-9899。',
            2=>'尊敬的客户，您找回登录密码的验证码为：' . print_r($key,true) . '。如有疑问请致电400-027-9899。',
            3=>'尊敬的客户，您重置交易密码的验证码为：' . print_r($key,true) . '。如有疑问请致电400-027-9899。',
            4=>'尊敬的客户，您修改手机号码的验证码为：' . print_r($key,true) . '。如有疑问请致电400-027-9899。',
            5=>'尊敬的客户，您绑定手机号码的验证码为：' . print_r($key,true) . '。如有疑问请致电400-027-9899。',
            6=>'尊敬的客户，您本次操作的验证码为：' . print_r($key,true) . '，如有疑问请致电400-027-9899。',
        );
    }
    if(!isset($list[$type])){
        $type=0;
    }
    $return['content'] = isset($list[$type]) ? $list[$type] : $list[0];
    $return['mobile'] = $mobile;
    $return['type']   = $channel;

    return $return;
}


/**
 * 数字金额转换成中文大写金额的函数
 * String Int  $num  要转换的小写数字或小写字符串
 * return 大写字母
 * 小数位为两位
 *
 */
function numToRmb($num)
{
	$c1 = "零壹贰叁肆伍陆柒捌玖";
	$c2 = "分角元拾佰仟万拾佰仟亿";
	//精确到分后面就不要了，所以只留两个小数位
	$num = round($num, 2); 
	//将数字转化为整数
	$num = $num * 100;
	if (strlen($num) > 10) {
			return "金额太大，请检查";
	} 
	$i = 0;
	$c = "";
	while (1) {
		if ($i == 0) {
				//获取最后一位数字
				$n = substr($num, strlen($num)-1, 1);
		} else {
				$n = $num % 10;
		}
		//每次将最后一位数字转化为中文
		$p1 = substr($c1, 3 * $n, 3);
		$p2 = substr($c2, 3 * $i, 3);
		if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
				$c = $p1 . $p2 . $c;
		} else {
				$c = $p1 . $c;
		}
		$i = $i + 1;
		//去掉数字最后一位了
		$num = $num / 10;
		$num = (int)$num;
		//结束循环
		if ($num == 0) {
				break;
		} 
	}
	$j = 0;
	$slen = strlen($c);
	while ($j < $slen) {
			//utf8一个汉字相当3个字符
			$m = substr($c, $j, 6);
			//处理数字中很多0的情况,每次循环去掉一个汉字“零”
			if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
					$left = substr($c, 0, $j);
					$right = substr($c, $j + 3);
					$c = $left . $right;
					$j = $j-3;
					$slen = $slen-3;
			} 
			$j = $j + 3;
	} 
	//这个是为了去掉类似23.0中最后一个“零”字
	if (substr($c, strlen($c)-3, 3) == '零') {
			$c = substr($c, 0, strlen($c)-3);
	}
	//将处理的汉字加上“整”
	if (empty($c)) {
			return "零元整";
	}else{
			return $c . "整";
	}
}

/**
 * 
 * 
 * 
 * @param number $mobile 手机号
 * @param number $userID  用户ID
 * @param unknown $period  间隔时间 默认180秒， 3分钟
 * @param number $type  请求的短信对象 1是短信接口 2恒生接口
 * @return multitype:string
 */
function mobileAndUser($mobile=0, $userID=0, $period = 180, $type = 1){
    $CI = &get_instance();
    $CI->load->library('Buyfunds_mem',NULL,'buyfunds_mem');
    $pCache = $CI->buyfunds_mem;
    
    $tSign = ($type == '1') ? '' : $type;
    if($userID>0){
        $t1  = $tSign . 'time_sendsms_times_user_'.date('Ymd').'_'.$userID;

        $user_t1   = $pCache->get($t1);
         
        if($user_t1){
            $ut1=time()-$user_t1;
             
            if($ut1<$period){
                return array('flag'=>'10000', 'msg'=>'短信间隔不少于' . (int)($period/60) . '分钟');
            }
        }

    }
    if($mobile>0){
        $t2  = $tSign . 'time_sendsms_times_mobile_'.date('Ymd').'_'.$mobile;
        $mobile_t2 = $pCache->get($t2);
         
        if($mobile_t2){

            $mt1=time()-$mobile_t2;

            if($mt1<$period){
                return array('flag'=>'10000', 'msg'=>'短信间隔不少于' . (int)($period/60) . '分钟');
            }

        }
    }

}


/**
 * @ check_str 信息校验
 *
 * @param string $input 输入信息
 * @param $type $type 信息类型
 * @return mixed
 */
function check_str($input, $type) {
    $formats = array(
        'email'         =>'/^[\w\-\.]+\@(([0-9a-zA-Z]+)[.])+[a-z]{2,4}$/i',
        'mobile'        =>'/^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/',
        'hkmobile'      =>'/^([2|5|6|8|9])\d{7}$/',
        'password'      =>'/[^\w\@\#\$]/',
        'username'      =>'/[^\w]|^\d+$/',
        'ousername'     =>'/[^\w\-]/',
        'nickname'      =>'',
        'ip'            =>'',
        'empty'         =>''
    );

    switch($type) {
        case 'empty':
            return empty($input);
        default:
            return preg_match($formats[$type], $input);
    }

    return true;
}


/**
 * 
 * ip是否存在于$iparr段内 $iparr = array('173.23','196.12.125')
 * false 存在
 * @param unknown $ip
 * @param unknown $iparr
 * @return boolean
 */
function ip_fiter($ip, $iparr){
    foreach($iparr as $k=>$v){
        if(strpos(' '.$v,$ip)==1){
            return false;
        }
    }
    return true;

}

/**
 * 判断参数是否完整
 *
 */
function check_param($param, $need){

    //$param,$need 必须为数组并且有值
    if(!is_array($param) || count($param) <= 0 || !is_array($need) || count($need) <= 0 ){
        return array('flag'=>'10001', 'msg'=>'参数错误', 'info'=>'01');
    }
    $str = '';
    foreach($need as $nk => $nv){
        if(!isset($param[$nv]) || $param[$nv] === false){
            $str .= $nv . ', ';
        }
    }

    if($str){
        return array('flag'=>'10001', 'msg'=>'参数缺失', 'info'=>$str);
    }

    return array('flag'=>'10000', 'msg'=>'成功', 'info'=>'[]');
}


/**
 * 
 * model判断参数是否完整
 *
 */
function checkModelParam($param, $need){

    //$param,$need 必须为数组并且有值
    if(!is_array($param) || count($param) <= 0 || !is_array($need) || count($need) <= 0 ){
        return array('flag'=>'10001', 'msg'=>'参数错误', 'info'=>'01');
    }
    $str = '';
    foreach($need as $nk => $nv){
        if(!isset($param[$nv]) || $param[$nv] === false || $param[$nv] == '' || $param[$nv] === NULL || $param[$nv] === null){
            $str .= $nv . ', ';
        }
    }

    if($str){
        return array('flag'=>'10001', 'msg'=>'参数缺失', 'info'=>$str);
    }

    return array('flag'=>'10000', 'msg'=>'成功', 'info'=>'[]');
}

/**
 *
 * 记录并统计cahce key
 * 返回key 和 count（次数）
 *
 */
function cacheKeyCount($key){
    $count = 1;
    $CI = &get_instance();
    $CI->load->library('Buyfunds_mem',NULL,'buyfunds_mem');
    $pcache = $CI->buyfunds_mem;
    $tCont = (int)$pcache->get($key);
    if($tCont > 0){
        $count = $tCont+1;
    }
    $pcache->set($key, $count, 3*60); //缓存1小时

    return array('key'=>$key, 'count'=>$count);
}

/**
 *
 * 清缓存
 *
 * @param unknown $str
 */
function cleanCache($str){
    $CI = &get_instance();
    $CI->load->library('Buyfunds_mem',NULL,'buyfunds_mem');
    $pcache = $CI->buyfunds_mem;
    if(strpos(trim($str), ',') !==FALSE){  //多key
        $aStr = explode(',', $str);
        foreach($aStr as $sk=> $sv){
            if(trim($sv)){
                $pcache->set($sv, '', 1);
            }
        }
    } else { //单key
        //pre($str);
        $pcache->set(trim($str), '', 1);
    }
}


/**
 * 密码是否符合要求
 *
 * @param string $password 密码
 * @return multitype:number string
 */
function checkPassword($password, $minLength=8, $maxLength=20, $str =''){

    $data = array();
    switch(true) {
        case (empty($password)):
            $data = array('flag'=>'10001', 'msg'=> $str . '密码不能为空！', 'info'=>'');
            break;
        case (strlen($password)<$minLength):
            $data = array('flag'=>'10002', 'msg'=> $str . '密码太短！', 'info'=>'');
            break;
        case (strlen($password)>$maxLength):
            $data = array('flag'=>'10003', 'msg'=> $str . '密码太长！', 'info'=>'');
            break;
        case (check_str($password, 'password')):
            $data = array('flag'=>'10004', 'msg'=> $str . '密码' . $minLength . '-' . $maxLength . '个字符，可由数字，字母和_@#$组成(必须包含两种以上类型组合)', 'info'=>'');
            break;
        default:

            break;
    }
    if(empty($data)) {
        $nLoginpasstype = 0;
        //安全密码规则
        if(preg_match('/[a-zA-Z]/', $password))
            $nLoginpasstype++;
        if(preg_match('/[\_\@\#\$]/', $password))
            $nLoginpasstype++;
        if(preg_match('/[0-9]/', $password))
            $nLoginpasstype++;
        if($nLoginpasstype < 2) {
            $data = array('flag'=>'10005', 'msg'=> $str . '密码不能为纯数字或纯字母或纯特殊字符！', 'info'=>'');
        }
    }
    if(!empty($data)) {
        return $data;
    } else {
        return array('flag'=>'10000', 'msg'=>$str . '密码符合要求', 'info'=>'');
    }
}

/**
 *
 * 弹框跳转
 * @param unknown $msg
 * @param string $url
 * @param string $target
 */
function errorJump($msg, $url='', $target=''){

    if(empty($url)) {
        $url = TRADE_WEB_URL;
    }
    echo '<script type="text/javascript">';
    echo 'alert("'.$msg.'");';
    if($target=='top') {
        echo 'window.top.location.href="'.$url.'";';
    }
    else
    {
        echo 'window.location.href="'.$url.'";';
    }

    echo '</script>';
    exit;
}

/**
 *
 * js 提示跳转
 * @param array $data 信息
 *
 **/
function arrayErrorJump($data){
    //pre($data); exit;
    echo '<script type="text/javascript">';

    echo 'alert("'. $data['msg'] .'");';

    if(isset($data['info']) && $data['info']){
        echo 'window.top.location.href="'. $data['info'] . '";';
    }
    echo '</script>';
    exit;
}

/**
 * @ 获取文件名和扩展名
 * @param string $filename 附件名
 * @return array
 */
function get_file_name($filename) {
    $tmp  = explode('.' , $filename);
    $count= count($tmp);
    
    if($count < 2) {
        $result['filename'] = $filename;
    } else {
        $prename = '';
        for($i=0; $i<$count-1; $i++) {
            $prename .= $tmp[$i];
        }
        $result['filename'] = $prename;
        $result['extdname'] = $tmp[$count-1];
    }
    
    return $result;
}

/**
 * @ get_user_head 头像地址
 *
 * @param int $uid 用户编号
 * @param int $size 大小
 * @return string
 */
function getUserHead($uid, $size=96) {

    //先判断头像是否存在
    if(file_exists(getHeadImgFullPath($uid, $size))){
        $head = TRADE_HEAR_URL.'/'.getIDFolder($uid). '/' .$uid.'.'.$size;
    } else {
        $head = TRADE_HEAR_URL.'/default/prephoto.jpg';
    }

    return $head;
}

/**
 *
 *  获取头像图片绝对路径
 *
 */
function getHeadImgFullPath($userID = 0, $w=48){
    $imgPath = '';
    if ($userID == 0){
        return $imgPath;
    }
    return  $imgPath = HEAD_PATH . '/' . getHeadImgPath($userID, $w);

}

/**
 *
 *  获取头像图片绝对路径
 *
 */
function getCacheHeadImgFullPath($userID = 0, $w=48){
    $imgPath = '';
    if ($userID == 0){
        return $imgPath;
    }
    return  $imgPath = CACHE_HEAD_PATH . '/' . getHeadImgPath($userID, $w);

}

/**
 *
 * 获取头像图片的相对路径
 *
 **/
 function getHeadImgPath($userID = 0, $w=48){
    $imgPath = '';
    if ($userID == 0){
        return $imgPath;
    }
    return  $imgPath = getIDFolder($userID) . '/' . $userID . SPOT. $w;
 }


/**
 * @ getIDFolder 头像附件地址
 *
 * @param int $id 用户编号
 * @return string
 */
function getIDFolder($id=0) {
    $id_encode = md5($id . 'uQP1FyaA@PwU34b');
    $folder_1  = substr($id_encode, 0, 2);
    $folder_2  = substr($id_encode, 2, 2);

    return $folder_1.'/'.$folder_2.'/'.$id.'/';
}

	
	
	
/**
 *
 * 图片上传
 *
 *
 *
 **/
function uploadImg($userID, $imgFile){
    $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__) . '_' . date('Ymd') . '.log';
    if(!$userID || !$imgFile){
        $data = array('flag'=>'10014', 'msg'=>'图片参数错误', 'info'=>'');
        return $data;
    }
    $upError = array(
        '0' => '正常',
        '1' => '上传的文件超过最大值',
        '2' => '上传文件的大小超过HTML最大值',
        '3' => '文件只有部分被上传',
        '4' => '没有文件被上传',
        '6' => '找不到临时文件夹',
        '7' => '文件写入失败'
    );
     
     
    if($imgFile['error'] != '0'){
        $data = array('flag'=>'10001', 'msg'=>$upError[$imgFile['error']], 'info'=>'');
        return $data;
    }
     
    if(empty($imgFile['name'])){
        $data = array('flag'=>'10002', 'msg'=>'图片没被上传', 'info'=>'');
        return $data;
    }
     
    if(!is_uploaded_file($imgFile['tmp_name'])){
        $data = array('flag'=>'10003', 'msg'=>'图片不合法', 'info'=>'');
        return $data;
    }
     
    $aImgSize = getimagesize($imgFile['tmp_name']);
    if($aImgSize === false){
        $data = array('flag'=>'10004', 'msg'=>'非法图片', 'info'=>'');
        return $data;
    }
     
    //上传类型
    $aAllowType = array('image/gif','image/jpg','image/jpeg','image/png','image/x-png','image/pjpeg');
    if(!in_array($imgFile['type'], $aAllowType)){
        $data = array('flag'=>'10005', 'msg'=>'图片仅支持jpg，png，gif格式', 'info'=>'');
        return $data;
    }
     
    //获取文件名、后缀、大小
    $getFilename = get_file_name($imgFile['name']);
    $imgSuffix  = strtolower($getFilename['extdname']); //获取图片类型
    $fileSize = filesize($imgFile['tmp_name']);
    $allowType = array('jpg','jpeg','png','bmp','gif');
     
    //文件类型判断
    if(!in_array($imgSuffix, $allowType)){
        $data = array('flag'=>'10006', 'msg'=>'仅支持jpg、png、gif、bmp、jpeg的图片格式', 'info'=>'');
        return $data;
    }
     
    //文件大小判断
    if(($fileSize/1024>500)) {
        $data = array('flag'=>'10007', 'msg'=>'图片大小不能超过500K', 'info'=>'');
        return $data;
    }
    // 载入原图
    $createFun = 'ImageCreateFrom'.($imgSuffix=='jpg'?'jpeg':$imgSuffix);
    $source     = $createFun($imgFile['tmp_name']);
    if($source){
        $data = array('flag'=>'10000', 'msg'=>'图片上传成功', 'info'=>array('source'=>$source, 'aImgSize'=>$aImgSize));

    } else {
        $data = array('flag'=>'10016', 'msg'=>'图片上传失败', 'info'=>'');
    }

    return $data;

}

/**
 *
 * 创建头像
 *
 **/
function createHeadImg($userID, $source, $aImgSize){
    $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__) . '_' . date('Ymd') . '.log';

    $w = 96;
    $h = 96;

    $croped = imagecreatetruecolor($w, $h);
    imagecopyresampled($croped, $source, 0, 0, 0, 0, $w, $h, $aImgSize[0], $aImgSize[1]);

    //首先定义好要生成的本地文件的保存路径
    $remoteFile     = getHeadImgPath($userID, $w);
    $rsyncFile      = HEAD_PATH . '/' .  $remoteFile;
    //pre($rsyncFile);
    $dFile = dirname($rsyncFile);
    mk_dir($dFile);
    create403Index($dFile);
    if(imagejpeg($croped, $rsyncFile)) {
         
        //$destFile = HEAD_PATH . '/' .  $remoteFile;

        // 目标地址、源地址
        //copyHead($rsyncFile, $destFile);
        log('||==rsyncFile==||' . $rsyncFile. '||==destFile==||' . $destFile, $logFile);
    } else {
        $data = array('flag'=>'10006', 'msg'=>'服务错误，请联系管理员', 'info'=>'');
        return $data;
    }

    $data = array('flag'=>'10000', 'msg'=>'头像修改成功', 'info'=>TRADE_WEB_URL);
    return $data;
}
	


/**
 * @ cur_page_url 获取当前地址
 *
 * @return string
 */
function cur_page_url() {
    $pageUrl = "http";

    if(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
        $pageUrl .= "s";
    }
    $pageUrl .= "://";

    $pageUrl .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];

    return $pageUrl;
}

/**
 * @ cnfol_location 地址跳转
 *
 * @param string $url 地址
 * @param string $target 打开方式
 * @return void
 */
function cnfol_location($url, $target='top') {
    if(empty($url)) {
        $url = PASSPORT_MYACCOUNT_URL;
    }

    echo '<script type="text/javascript">';
    if($target=='top') {
        echo 'window.top.location.href="'.$url.'";';
    }
    else
    {
        echo 'window.location.href="'.$url.'";';
    }
    echo '</script>';
}

/**
 *
 * 身份证中间8位星号代替
 *
 * @param unknown $str
 * @return mixed
 */
function identReplace($str){
    return str_replace(substr($str, 3, 12), '********', $str);
}
/**
 *
 * 手机号中间4位星号代替
 *
 * @param unknown $str
 * @return mixed
 */
function mobileReplace($str){
    return str_replace(substr($str, 3, 4), '****', $str);
}

/**
 *
 * 银行卡4位星号代替
 *
 * @param unknown $str
 * @return mixed
 */
function bankCardReplace($str){
    
    return '************' . substr($str, -4);
}
/**
 * 字符串中间加星号
 * @param unknown $string
 * @return unknown|string
 */
function str_mid_replace($string) {
    if (! $string || !isset($string[1])) return $string;

    $len = strlen($string);
    $starNum = floor($len / 2);
    $noStarNum = $len - $starNum;
    $leftNum = ceil($noStarNum / 2);
    $starPos = $leftNum;
    for($i=0; $i<$starNum; $i++) @$string[$starPos+$i] = '*';

    return $string;
}

/**
 * 将汉字转换为大写字母
 * @param $str
 * @return string
 */
function chi2Caps($str)
{
    $length = mb_strlen($str);
    $return = [];
    for ($i=0; $i<$length; $i++) {
        if ($cap = getFirstCharter(mb_substr($str, $i, 1))) $return[] = $cap;
    }
    return $return;
}
/**
 * 交易密码是否符合要求
 *
 * @param string $password 密码
 * @param string $idcode 身份证号
 * @return multitype:number string
 */
function tradeCheckpwd($password, $idcode, $minLength=6, $maxLength=20, $str =''){
    if(!isset($password) || !isset($idcode)){
        return array('flag'=>'10009', 'msg'=> '参数不全！', 'info'=>'');
    }
    //身份证
    if($idcode==$password){
        return array('flag'=>'10007', 'msg'=> '密码不能是身份证！', 'info'=>'');
    }
    $data = array();
    switch(true) {
        case (empty($password)):
            $data = array('flag'=>'10001', 'msg'=> $str . '密码不能为空！', 'info'=>'');
            break;
        case (strlen($password)<$minLength):
            $data = array('flag'=>'10002', 'msg'=> $str . '密码太短！', 'info'=>'');
            break;
        case (strlen($password)>$maxLength):
            $data = array('flag'=>'10003', 'msg'=> $str . '密码太长！', 'info'=>'');
            break;
        case (check_str($password, 'password')):
            $data = array('flag'=>'10004', 'msg'=> $str . '密码' . $minLength . '-' . $maxLength . '个字符，可由数字，字母和_@#$组成', 'info'=>'');
            break;
        default:

            break;
    }
    if(empty($data)) {
//重复密码
        $cn=strlen((string)$password)-1;
        $reg='/^.*(.)\\1{'.$cn.'}.*$/';
        //安全密码规则
        if(preg_match($reg, $password)){
            return array('flag'=>'10005', 'msg'=> '密码不能重复字符！', 'info'=>'');
        }
        //连续密码
        $reg='/^[0-9]*$/';
        if(preg_match($reg, $password)){
            $flag=true;
            $str=(string)$password;
            $arr=str_split($str);
            for($i=0;$i<count($arr)-1;$i++){
                if((int)$arr[$i+1]-(int)$arr[$i]!=1){
                    $flag=false;
                }
                if($flag==false){
                    break;
                }
            }
            if($flag==true){
                return array('flag'=>'10006', 'msg'=> '密码不能是连续递增数字！', 'info'=>'');
            }
        }

    }
    if(!empty($data)) {
        return $data;
    } else {
        return array('flag'=>'10000', 'msg'=>$str . '密码符合要求', 'info'=>'');
    }
}
/**
 * 
 * 交易密码生成
 * 
 */
function tradePswd($password){
    return md5($password . PASSPORT_AUTH_KEY);
}
/**
  * 加解密操作
  *
  * @param string $string    加密串
  * @param string $operation 加解密标识 DECODE:解密
  * @param string $key       密匙
  * @param expiry $expiry    密文有效期
  * @return string
  */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{

	$ckey_length = 4;

	$key = md5($key ? $key : UC_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';

	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);

	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);

	$result = '';
	$box = range(0, 255);

	$rndkey = array();
	for($i = 0; $i <= 255; $i++)
	{
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}

	for($j = $i = 0; $i < 256; $i++)
	{
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}

	for($a = $j = $i = 0; $i < $string_length; $i++)
	{
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}

	if($operation == 'DECODE')
	{
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16))
		{
			return substr($result, 26);
		}
		else
		{
			return '';
		}
	}
	else
	{
		return $keyc.str_replace('=', '', base64_encode($result));
	}

}
/**
 * 字符截取 支持UTF8/GBK
 * @param $string
 * @param $length
 * @param $dot
 */
function str_cut($string,$star='0',$length='4', $dot = '...',$charset='utf-8') {
	if(!$string) return $dot;
	$str = mb_substr($string, $star, $length, $charset);
	if(mb_strlen($string,$charset)>$length)
		$str .= $dot; 
	return $str;
}
 /* End of file func_helper.php */
/* Location: ./application/helper/func_helper.php */