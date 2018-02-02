<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @ 伯嘉基金信息模块
 *
 * @ Copyright (c) 2004-2012 CNFOL Inc. (http://www.cnfol.com)
 * @ version 3.0.1
 * @ author chensh
 */
 
class Recommend_Model extends MY_Model {
    
    const TBL_COMMISSIONUSER = 'tbpassportcommissionuser';  //推荐注册关系表
    const TBL_COMISSIONSTRADE = 'tbpassportcomissionstrade';  //推荐投资关系表
	
    function __construct() {
        parent::__construct();

    }
    
    /**
     * 
     * 获取用户对应关系
     *
     */
    public function _getUserRelation($where, $choice='*',$limit='',$order=''){
        return $this->getData(self::TBL_COMMISSIONUSER, $where, $choice, $order, $limit);
    }
	 /**
     * 
     * 获取用户推荐投资对应关系
     *
     */
    public function _getUserTradeRelation($where, $choice='*',$limit='',$order=''){
        return $this->getData(self::TBL_COMISSIONSTRADE, $where, $choice, $order, $limit);
    }
	/**
     * 
     * 插入用户佣金对应关系
     * 
     * inviterid  推荐用户
     * investorid 注册用户
     * proid      项目标识 1-基金超市
     * Status     状态:  0-推荐成功初始状态,1-注册成功,-1-解除对应关系
     * time       更新时间戳
     * 
     */
    public function insertUserRelation($param){
        return $this->insertTb(self::TBL_COMMISSIONUSER, $param, false);
    }
	/**
     * 
     * 插入用户佣金对应关系
     * 
     * inviterid  推荐用户
     * investorid 注册用户
     * proid      项目标识 1-基金超市
     * Status     状态:  0-推荐成功初始状态,1-注册成功,-1-解除对应关系
     * time       更新时间戳
     * 
     */
    public function insertTradeRelation($param){
        return $this->insertTb(self::TBL_COMISSIONSTRADE, $param, false);
    }
	/**
     * 
     * 更新用户佣金对应关系
     * 
     * inviterid  推荐用户
     * investorid 注册用户
     * proid      项目标识 1-基金超市
     * Status     状态:  0-推荐成功初始状态,1-注册成功,-1-解除对应关系
     * time       更新时间戳
	 *
     */
    public function updateUserRelation($param, $where){
        $uRs = $this->updateTb(self::TBL_COMMISSIONUSER, $param, $where);
		return $uRs;//更新用户详情
    }
	/**
     * 
     * 更新投资信息
     * 
     * inviterid  推荐用户
     * investorid 注册用户
     * proid      项目标识 1-基金超市
     * Status     状态:  0-推荐成功初始状态,1-投资提交成功,-3-投资确认失败 3 - 投资确认成功
     * time       更新时间戳
	 *
     */
    public function updateTradeRelation($param, $where){
        $uRs = $this->updateTb(self::TBL_COMISSIONSTRADE, $param, $where);
		return $uRs;//更新用户详情
    }
	
}//end class
?>