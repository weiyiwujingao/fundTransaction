<?php
/****************************************************************
 * 伯嘉基金定时脚成入口
 *---------------------------------------------------------------
 * Copyright (c) 2004-2016 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:linfeng $dtime:2016-11-23
 ****************************************************************/

define('CI_INDEX', dirname(dirname(__FILE__)).'/index.php');   

is_array($argv) or exit('No direct script access allowed'); 

array_shift($argv);

list($param,$value) = explode('=', $argv[0]);

switch($param)
{
	case 'run':
		$_SERVER['PATH_INFO'] = $value;
		$_SERVER['REQUEST_URI'] = $value;
		$_SERVER['QUERY_STRING'] = $value;
		require(CI_INDEX); 
	break;
	default:
		exit('script param is null');
}