<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @ 伯嘉基金信息模块
 *
 * @ Copyright (c) 2004-2012 CNFOL Inc. (http://www.cnfol.com)
 * @ version 3.0.1
 * @ author chensh
 */
 
class Fund_Model extends MY_Model {
    
    const TBL_Fund = 'tbpassportfund';  //基金表
    const TBL_TBank = 'tbpassporttbank';  //银行表
    const TBL_MANAGEMENT = 'tbpassportmanagement';  //申购，赎回，转换，普通定投共用
    const TBL_BONUS = 'tbpassportbonus';  //分红 tbPassportBonus

    
    function __construct() {
        parent::__construct();

    }
    

    /**
     * 
     * 获取基金表数据
     *
     */
    public function gFund($param, $limit, $offset){
        $tKey = implode('_', $param);
        $mKey = 'bj_fund_' . $tKey;
        unset($param['type']);
        $rs = $this->cache->get($mKey);
        if(!$rs){
            $rs = $this->getPageData(self::TBL_Fund, $param, $limit, $offset, 'ID,FundType,FundCode');
            if($rs){
                $this->cache->set($mKey, $rs, 10*60); //缓存10分钟
            }
        }
        
        return $rs;
    }
    /**
     * 
     * 获取用户交易状态信息
     *
     */
    public function _getUserTrade($where, $choice='*',$limit='',$order=''){
        return $this->getData(self::TBL_MANAGEMENT, $where, $choice, $order, $limit);
    }
	/**
     * 
     * 获取用户交易状态信息
     *
     */
    public function _getUserBonus($where, $choice='*',$limit='',$order=''){
        return $this->getData(self::TBL_BONUS, $where, $choice, $order, $limit);
    }
    /**
     * 
     * 通过银行代码查省份
     * 
     */
    public function getBankInfo($param, $choice = '*'){
        //pre($param);
        $tKey = implode('_', $param);
        $mKey = 'bj_provincebybankcode_' . $tKey;
        $rs = $this->cache->get($mKey);
        if(!$rs){
            $rs = $this->getDataAR(self::TBL_TBank, $param, $choice);
            if($rs){
                $this->cache->set($mKey, $rs, 60*60); //缓存1小时
            }
        }
        
        return $rs;

    } 
    /**
     * 
     * 申购，赎回，转换，普通定投 数据插入
     * 
     * UserID 用户ID
     * Type 操作类型：1-申购，2-赎回，3-基金转换，4-普通基金定投
     * Payway 支付方式：1-现金宝，2-银行
     * Modeway 操作方式：1-修改，2-暂停，3-恢复，4-终止，5-增加
     * BankName 银行名称
     * BankCode 银行账号
     * Cycle 定投周期：1-每月
     * FixedDate 定投日期
     * TotalMoney 总金额（单位：分）
     * TrueMoney 实际金额（单位：分）
     * Poundage 手续费（单位：分）
     * Status 状态:  1-成功,2-失败,3-审核中
     * SubDate 提交日期
     * 
     */
    public function insertManagement($param){
        return $this->insertTb(self::TBL_MANAGEMENT, $param, false);
    }

    /**
     * 
     * 分红 数据插入
     * 
     * UserID 用户id
     * BeforChge 修改前分红状态 1-现金分红；2-红利再投资
     * AfterChge 修改后分红状态 1-现金分红；2-红利再投资
     * Status 状态:  1-成功,2-失败,3-审核中
     * Date 操作日期
     * 
     */
    public function insertBonus($param){
        return $this->insertTb(self::TBL_BONUS, $param, false);
    }
	/**
     * 
     * 申购，赎回，转换，普通定投 数据更新
     * 
     * UserID 用户ID
     * Type 操作类型：1-申购，2-赎回，3-基金转换，4-普通基金定投
     * Payway 支付方式：1-现金宝，2-银行
     * Modeway 操作方式：1-修改，2-暂停，3-恢复，4-终止，5-增加
     * BankName 银行名称
     * BankCode 银行账号
     * Cycle 定投周期：1-每月
     * FixedDate 定投日期
     * TotalMoney 总金额（单位：分）
     * TrueMoney 实际金额（单位：分）
     * Poundage 手续费（单位：分）
     * Status 状态:  1-成功,2-失败,3-审核中
     * SubDate 提交日期
     * 
     */
    public function updateManagement($param, $where){
        $uRs = $this->updateTb(self::TBL_MANAGEMENT, $param, $where);
		return $uRs;
        if(isset($where['UserID']) && $uRs){
            $this->cache->set('u_tbpassportmanagement_' . $where['UserID'], '', 1);
        }
        return $uRs;  //更新用户详情
    }
	/**
     * 
     * 分红方式操作 状态更新
     * 
     * UserID 用户ID
     */
    public function updateBonus($param, $where){
        $uRs = $this->updateTb(self::TBL_BONUS, $param, $where);
		return $uRs;
        if(isset($where['UserID']) && $uRs){
            $this->cache->set('u_tbpassportbonus_' . $where['UserID'], '', 1);
        }
        return $uRs;  //更新用户详情
    }

}//end class
?>