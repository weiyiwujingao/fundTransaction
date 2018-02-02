<?php defined('BASEPATH') OR exit('No direct script access allowed');
/****************************************************************
 * 伯嘉基金 - 数据库配置 v1.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2016 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:linfeng $addtime:2016-11-15
 ****************************************************************/
/* 配置所属组名称,如:模型中$this->db->database() 连接的默认组配置为"slave" */
$active_group = 'slave';
/* TRUE:使用CI AR类操作数据库 FALSE:不使用 */
$query_builder = TRUE;
/* 主库配置,仅支持写入 模型中初始化 $this->db->database('master') */
$db['master'] = array(
	'dsn'	   => '',
	'hostname' => '',
	'username' => '',
	'password' => '',
	'database' => 'passport',
	'dbdriver' => 'mysqli',
	'dbprefix' => '',
	'db_debug' => FALSE,
    'pconnect' => FALSE,
	'cache_on' => FALSE,
	'cachedir' => '',
	'char_set' => 'utf8',
	'dbcollat' => 'utf8_general_ci',
	'swap_pre' => '',
	'encrypt'  => FALSE,
	'compress' => FALSE,
	'stricton' => FALSE,
	'failover' => array(),
	'save_queries' => TRUE
);

/* 从库配置,仅支持读取 模型中初始化 $this->db->database() */
$db['slave'] = array(
    'dsn'	   => '',
    'hostname' => '',
    'username' => '  ',
    'password' => '  ',
    'database' => ' ',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => FALSE,
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt'  => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);

/* End of file database.php */
/* Location: ./application/config/database.php */
