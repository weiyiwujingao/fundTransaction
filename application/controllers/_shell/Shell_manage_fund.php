<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/****************************************************************
 * 2015财经排行榜-定时脚本
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:linfeng $addtime:2015-10-27
 ****************************************************************/
class Shell_manage_fund extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->access_token = getAccessToken(__METHOD__);
		$this->datadictionary = config_item('datadictionary');	
        $this->load->model('_cron/cron_mdl');
    }

    /**
     * 每天初始化基金代销代码信息,生成基金代码搜索的信息缓存
     *
     * @return void
     */
    public function FundCodevar()
    {
        /* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$access_token = '';
        $fundtypearr = config_item('fund_risklevel');
        //$access_token = $this->getAccessToken();
        $access_token = $this->access_token;
		$data = $this->tradeapi_manage_mdl->getnewhq_qry();
		if(!$data) exit('数据异常！');
        $fundcode = $fundcodemsg = array();
        foreach ($data as $val) {
            $fundcode[] = $val['fund_code'];
            $fundcodemsg[$val['fund_code']]['name']  = $val['fund_full_name'];
            $fundcodemsg[$val['fund_code']]['sname'] = $val['fund_name'];
            $fundcodemsg[$val['fund_code']]['type']  = $this->datadictionary['2029'][$val['ofund_risklevel']];
            $fundcodemsg[$val['fund_code']]['types'] = $this->datadictionary['2022'][$val['ofund_type']];
            $fundcodemsg[$val['fund_code']]['code']  = $val['fund_code'];
        }
       // pre($fundcodemsg);//exit;
        if ($fundcode && $fundcodemsg) {
		
            //file_put_contents(APPPATH.'logs/funddata/fundcode.log',$fundcode,LOCK_EX);
            $this->cnfol_file->set('fundmsg', $fundcodemsg);
            $this->cnfol_file->set('fundcode', $fundcode);
        }
        echo PHP_EOL . 'END';
        //$a = file(APPPATH.'logs/funddata/fundcode.log',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }

    /**
     * 每天初始化基金代销代码前端js文件信息
     *
     * @return void
     */
    public function JsFundvar()
    {
        /* 日志路径 */
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
		$this->tradeapi_manage_mdl->set_log_file($logFile);
		
		$fundtypearr = config_item('fund_risklevel');
        //$access_token = $this->getAccessToken();
        $access_token = $this->access_token;

        $data = $this->tradeapi_manage_mdl->getnewhq_qry();
		if(!$data) exit('数据异常！');
		
        $fundcode = 'var glo_fundlist=[';
        $zimu = '';
        foreach ($data as $val) {
            $zimu = getFirstCharter($val['fund_name']);
            if (!$zimu) {
                continue;
            }
            $fundcode .= '[' . "'{$val['fund_code']}'" . ',' . "'{$zimu}'" . ',' . "'{$val['fund_full_name']}'" . ',' . "'{$fundtypearr[$val['ofund_risklevel']]}'" . '],';
        }
        $fundcode = rtrim($fundcode, ',');;
        $fundcode .= '];';
		pre($fundcode);//exit;
        if ($fundcode) {
            //file_put_contents('/home/httpd/trade.buyfunds.cn/front/buy/js/jsonfund.js',$fundcode,LOCK_EX);
            file_put_contents(WEB_PATH . '/front/buy/js/jsonfund.js', $fundcode, LOCK_EX);
        }
        //$a = file(APPPATH.'logs/funddata/fundcode.log',FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    }
	/**
     * 热销基金静态页生成
     */
	public function hotfunds(){
		$data = @file_get_contents(WEB_URL.'/home/getHotjj');
		//t(WEB_URL.'/home/getHotjj');
		
		if(!$data) exit('数据缺失！');
		$data = json_decode($data,true);
		if(!$data) exit('转码失败！');
		$html = '<table class="hotTable Mt10" cellpadding="0" cellspacing="0">'.PHP_EOL;
		$fundtype = array('a'=>'债券型','b'=>'股票型','c'=>'货币型','d'=>'指数型','e'=>'混合型','f'=>'QDII');
		$intype = array('a','b','d','e');
		foreach($data as $key=>$value){
			if(!in_array($key,$intype)) continue;
			foreach($value as $k=>$v){				
				$ofund_risklevel = $this->tradeapi_manage_mdl->fundtype($v['jydm'],'ofund_risklevel');
				//pre($ofund_risklevel);
				if($ofund_risklevel!='')
				$ofund_risklevel = $this->datadictionary['2029'][$ofund_risklevel];
				$html .= '<tr>'.PHP_EOL;
				$html .= '<td width="10%"><b>'.$v['y'].'</b><span>近3月涨幅</span></td>'.PHP_EOL;
				$html .= '<td width="35%"><p><a href="'.WEB_URL.'/fund/'.$v["jydm"].'" target="_blank">'.$v['jjjc'].'</a></p><span>'.$fundtype[$key].'</span></td>'.PHP_EOL;
				$html .= '<td width="10%"><p>'.round($v['l']).'元起投</p><span>'.$ofund_risklevel.'</span></td>'.PHP_EOL;
				$html .= '<td width="10%"><p><s>'.sprintf("%.2f", trim($v['yfl'])).'%</s><em>'.sprintf("%.2f", trim($v['zdfl'])*100).'%</em></p><span>费率</span></td>'.PHP_EOL;
				$html .= '<td width="5%"><a href="'.base_url().'trade/fundtrade.html?fundcode='.$v['jydm'].'">购买</a></td>'.PHP_EOL;
				$html .= '</tr>'.PHP_EOL;
				break;
			}
		}
		$html .= '</table>';
		echo($html);
		if($html)
			 file_put_contents(WEB_PATH . '/application/views/hotfund/hotfund.html',$html,LOCK_EX);
	}
	/**
     * 更新令牌
     */
	public function updateTtoken(){
		$access_token = getAccessToken(__METHOD__);
		if(date('H')=='06'){
			getAccessToken(__METHOD__,1);
		}	
		if ($access_token) {
            $url = STI_CWSALE . 'newhq_qry';
            $arr = array('targetcomp_id' => TARGETCOMP_ID, 'sendercomp_id' => SENDERCOMP_ID, 'access_token' => $access_token, 'request_num' => '1', 'reqry_recordsum_flag' => '1', 'qry_beginrownum' => '0');

            $result = curl_post($url, $arr, 10, 1);
			$data = json_decode($result['data'],true);
			if(isset($data['error'])&&$data['error']=='invalid_token'){
				getAccessToken(__METHOD__,1);
			}	
        }
	}
    /**
     * 每天更新基金信息
     * $letter2funds 单字母索引；
     * $word2funds 字母串索引；
     * $num2funds 数字索引；
     * $ta_no2funds 基金公司索引；
     * $funds 基金信息 代码 =>【代码，简名，全名，前后端收费，净值，净值日期，状态（开放、封闭），风险等级，类型，分红方式，TA编号，首次最低，追加最低、最高，是否支持定投】
     * $funds_raw 未经字典翻译的基金信息 代码 =>【代码，简名，全名，前后端收费，净值，净值日期，状态（开放、封闭），风险等级，类型，分红方式，TA编号，首次最低，追加最低，最高，是否支持定投】
     */
    public function sortFunds()
    {
        set_time_limit(7200);
        $this->cron_mdl->allFixDivi();
        $res = $this->cron_mdl->sortFunds();
        foreach ($res as $item) {
            if (empty($item)) {
                logs(date('Y-m-d H:i:s') . '获取基金信息脚本出错', 'cron');
                die('获取基金信息脚本出错');
            }
        }
    }

    public function sendMail()
    {
        if (date('d') != '01') die('Forbidden');
        set_time_limit(7200);
        $authkey = md5(date('Y_m_d') . 'NYIPPDmkl81ZFvWxdpLclpOaXhZRCjIn');
        $url = TRADE_WEB_URL . '/_transaction/Bill/bill?authkey=' . $authkey;
        $res = curl_post($url, [], 20);
        var_dump($res);
    }
}

/* End of file shell_manage.php */
/* Location: ./application/controllers/_shell/shell_manage.php */