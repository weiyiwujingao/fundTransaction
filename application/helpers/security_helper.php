<?php defined('BASEPATH') OR exit('No direct script access allowed');
/****************************************************************
 * 伯嘉基金 - 安全过滤公共函数 v1.0
 * 使用方法:filter_slashes(接收的参数);
 *---------------------------------------------------------------
 * Copyright (c) 2004-2016 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:linfeng $addtime:2016-11-15
 ****************************************************************/
/** 
  * 安全过滤函数,主要用于防范sql注入 
  * 
  * @param  mixed   $string 字符串/数组 
  * @param  integer $force  强制进行过滤
  * @param  boolean $strip  是否需要去除反转义符号
  * @return mixed
  *
  */  
function bf_addslashes($string,$force=0,$strip=FALSE)
{
	if(!MAGIC_QUOTES_GPC_ON || $force)
	{
		if(is_array($string))
		{
			foreach($string as $key => $val)
			{
				$string[$key] = bf_addslashes($val, $force, $strip);
			}
		}
		else
		{
			$string = addslashes($strip ? stripslashes($string) : $string);
		}
	}
	return $string;
}

/** 
  * 安全过滤函数1,转义单引号
  * 
  * @param  mixed   $string 字符串/数组 
  * @param  integer $force  强制进行过滤
  * @param  boolean $strip  是否需要去除反转义符号
  * @return mixed 
  */  
function filter_slashes($string, $force = 1, $strip = FALSE)
{
	/* 如果是表单则需要判断MAGIC_QUOTES_GPC状态 */
	if(!MAGIC_QUOTES_GPC_ON || $force)
	{
		if(is_array($string))
		{
			foreach($string as $key => $val)
			{
				$string[$key] = filter_slashes($val, $force, $strip);
			}
		}
		else
		{
			$string = addslashes($strip ? stripslashes($string) : $string);
		}
	}
	$string = bf_addslashes($string);
	$string = filter_sql($string);
	$string = filter_str($string);
	$string = filter_html($string);

	return $string;
}

/** 
  * 安全过滤函数2,过滤html、进制代码
  * 
  * @param  mixed $string 需要过滤的数据 
  * @param  mixed $flags  是否使用PHP自带函数
  * @return mixed 
  */  
function filter_html($string, $flags = NULL)
{
	if(is_array($string))
	{
		foreach($string as $key => $val)
			$string[$key] = filter_html($val, $flags);
	}
	else
	{
		if($flags === NULL)
		{
			$string = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $string);
			if(strpos($string, '&amp;#') !== FALSE)
				$string = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $string);
		}
		else
		{
			if(PHP_VERSION < '5.4.0')
				$string = htmlspecialchars($string, $flags);
			else
				$string = htmlspecialchars($string, $flags, 'UTF-8');
		}
	}
	return $string;
}

/**
  * 安全过滤函数3,数据加下划线防止SQL注入
  *
  * @param  string $value 需要过滤的值
  * @return string
  */
function filter_sql($value)
{
	$sql = array("select", 'insert', "update", "delete", "\'", "\/\*", 
					"\.\.\/", "\.\/", "union", "into", "load_file", "outfile");
	$sql_re = array("","","","","","","","","","","","");

	return str_replace($sql, $sql_re, $value);
}

/**
  * 安全过滤函数4,过滤特殊有危害字符
  * 
  * @param string $value 需要过滤的数据
  * @return string
  */
function filter_str($value)
{
	$value = str_replace(array("\0","%00","\r"), '', $value); 
	$value = preg_replace(array('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/','/&(?!(#[0-9]+|[a-z]+);)/is'), array('', '&amp;'), $value);
	$value = str_replace(array("%3C",'<'), '&lt;', $value);
	$value = str_replace(array("%3E",'>'), '&gt;', $value);
	$value = str_replace(array('"',"'","\t",'  '), array('&quot;','&#39;','    ','&nbsp;&nbsp;'), $value);

	return $value;
}

/* End of file security_helper.php */
/* Location: ./application/helper/security_helper.php */