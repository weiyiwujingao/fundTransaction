<?php defined('BASEPATH') OR exit('No direct script access allowed');
/****************************************************************
 * 伯嘉基金 - 类库预加载配置 v1.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2016 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:linfeng $addtime:2016-11-15
 ****************************************************************/
/* 预加载模型文件 */
$autoload['model']     = array('_trade/tradeapi_manage_mdl', '_user/User_Model');
/* 预加载配置文件 */
$autoload['config']    = array('main_setting','datadictionary_setting');
/* 预加载公共函数文件 */
$autoload['helper']    = array('url','func','security');
/* 预加载类库 */
$autoload['libraries'] = array('buyfunds_mem','cnfol_file');
/* 预加载驱动 */
$autoload['drivers']   = array();
/* 预加载语言文件 */
$autoload['language']  = array();
/* 预加载包文件 */
$autoload['packages']  = array();

/* End of file autoload.php */
/* Location: ./application/config/autoload.php */