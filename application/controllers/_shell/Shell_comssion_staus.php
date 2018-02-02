<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/****************************************************************
 * 用户中心佣金-定时脚本
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2017-03-20
 ****************************************************************/
class Shell_Comssion_Staus extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('_user/Recommend_Model');
		header("Content-type: text/html; charset=utf-8");
    }
	 /**
     * 
     * 申购，认购状态查询
     * 
     */
    public function runstaus()
    {
		/* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$logpath = 'fund_staus_ref_comission';
		$biaoshi = $this->cnfol_file->get($logpath);
		if($biaoshi==date('ymdH'))
			t('状态已经更新！');			
		
		$failsearr  = array('0','3');
		$successarr = array('1');
		
		$limittime = time()-3600*24*10;
		$where =  " status = '1' and time >{$limittime} ";
		$datas = $data = [];
		$i='0';
		while($data = $this->Recommend_Model->_getUserTradeRelation($where,'*',"{$i},100",'id desc')){
			$datas = array_merge($datas,$data);
			$i = $i+100;
		}
		if(!$datas){
			t('暂无审核中的数据');
			unset($data,$datas);
		} 		
		foreach($datas as $k=>$v){
			if(!$v['allot_no']){
				pre('申请编号不存在！');
				continue;
			}
			//查看基金类型 为货币基金排除
			$ofund_type = $this->tradeapi_manage_mdl->fundType($v['fundcode'],'ofund_type');
			if($ofund_type==2){
				$param = array('status'=>'-3');
				$where = array("allot_no"=>"{$v['allot_no']}","status"=>"1");
				$r = $this->Recommend_Model->updateTradeRelation($param, $where);
				continue;
			}	
			$applyqry = $this->tradeapi_manage_mdl->trade_apply_qry(array('allot_no'=>$v['allot_no']));
			if(!$applyqry || !isset($applyqry['data']['0']['taconfirm_flag'])){
				pre('交易查询为空 ！');
				continue;
			} 
			if(in_array($applyqry['data']['0']['taconfirm_flag'],$failsearr)){
				$param = array('status'=>'-3');
				$where = array("allot_no"=>"{$v['allot_no']}","status"=>"1");
				$r = $this->Recommend_Model->updateTradeRelation($param, $where);
				return;
			}
			if(in_array($applyqry['data']['0']['taconfirm_flag'],$successarr)){
				
				$userInfo = $this->User_Model->getUserBaseByUserID($v['userID']);
				$calfee = $this->comssionreturn($v['fundcode'],$ofund_type,$applyqry['data']['0']['trade_acco'],$v['allot_no']);
				//pre($calfee);
				if($calfee)
					logs('|-原始佣金记录：-|'.print_r($calfee,true),$logFile);
				$pos = strpos($calfee,'.');
				if($pos)
					$calfee = substr($calfee,0,$pos+3);
				//$calfee = sprintf("%.3f", $calfee);
				if($calfee<0){
					$param = array('status'=>'-3','comission'=>$calfee);
					$where = array("allot_no"=>"{$v['allot_no']}","status"=>"1");
					$r = $this->Recommend_Model->updateTradeRelation($param, $where);
					continue;
				}
				//pre($calfee);continue;
				if($calfee && $calfee>0){
					$time = time();
					$key  = $this->keydata($v['inviterid'],$v['investmentinviterid'],$v['investorid'],$time);
					//接口链接创建
					$tParam = array();
					$tParam['time']      = $time;
					$tParam['keys']      = $key;
					$tParam['proid']     = PROID;	//项目id				
					$tParam['inviterid'] = intval($v['inviterid']); //推荐注册人
					$tParam['investorid']   = intval($v['investorid']);//投资者
					$tParam['fundid']   = $v['fundcode'];//股票代码
					$tParam['brokeragefee'] = $calfee;//手续费
					$tParam['investmentinviterid'] = intval($v['investmentinviterid']);//投资推荐人
					$tParam['action'] = 'setUserSharing';
					
					$sURL   = http_build_query($tParam);
					//接口传参
					logs('|参数|'.date('Y-m-d H:i:s').print_r($sURL,true),$logFile);
					$apiUrl = FINANCIAL_SUPERMARKET_USERBIND_URL . '?' . $sURL;
					//接口
					logs('|接口|'.date('Y-m-d H:i:s').print_r($apiUrl,true),$logFile);
					$result = file_get_contents($apiUrl);
					//接口回参
					logs('|参数返回|'.date('Y-m-d H:i:s').print_r($result,true),$logFile);
					$result = json_decode($result,true);
					if(!isset($result['Code']) || $result['Code']!='200') continue;
					$param = array('status'=>'3','comission'=>$calfee);
					$where = array("allot_no"=>"{$v['allot_no']}","status"=>"1");
					$r = $this->Recommend_Model->updateTradeRelation($param, $where);
				}

			}
		}
		$this->cnfol_file->set($logpath, date('ymdH'));
    }
	
	/**
     * 
     * 佣金计算公式
     * 
     * 债券型基金总佣金(非货基) = 收入(认、申购手续费) - 支出【2.2‰(快钱手续费) + 1‰(恒生销售服务费，最高400元)+0.5‰(民生监管费)】 - 民生转账手续费(小于5万为2元一笔，大于等于5万为5.5元一笔
     *
     * 其它类基金总佣金(非货基) = 收入(认、申购手续费) - 支出*【2.2‰(快钱手续费) + 1‰(恒生销售服务费，最高400元) + 1‰(民生监管费)】- 民生转账手续费(小于5万为2元一笔，大于等于5万为5.5元一笔)
     * 
     */
    private function comssionreturn($fundcode,$ofund_type,$trade_acco,$allot_no)
    {
		
		$cost   = $this->tradeapi_manage_mdl->trade_confirm_qry($trade_acco, $allot_no, 'fare_sx');
        $blance = $this->tradeapi_manage_mdl->trade_confirm_qry($trade_acco, $allot_no, 'trade_confirm_balance');
		
        if($ofund_type==2 || !$cost || !$blance) return '';
		
        $kqcalfee = $cost*2.2*0.001<400?$cost*1*0.001:400;
        $hscalfee = $cost*1*0.001<400?$cost*1*0.001:400;
        $zzcalfee = $blance<50000?2:5;
        if($ofund_type==6){
            $mscalfee = $cost*0.5*0.001<400?$cost*1*0.001:400;           
            $calfee = $cost - $kqcalfee - $hscalfee - $zzcalfee - $mscalfee;
        }else{
            $mscalfee = $cost*1*0.001<400?$cost*1*0.001:400;           
            $calfee = $cost - $kqcalfee - $hscalfee - $zzcalfee - $mscalfee;
        }
        return $calfee;
	}
	/**
     *  佣金秘钥公式
     * 
     */
	private function keydata($inviterid,$investmentinviterid,$investorid,$time){
		$key = md5(md5(intval($inviterid)).md5(intval($investmentinviterid)).md5(intval($investorid)).$time);
		return $key;
	}

}

/* End of file shell_manage.php */
/* Location: ./application/controllers/_shell/shell_manage.php */