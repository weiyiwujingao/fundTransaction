<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 用户账单
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * @author:jiangxuefeng  @addtime:2017/01/16
 ****************************************************************/
class Bill extends MY_Controller
{
    protected $_data = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('_transaction/transaction_mdl');
        $this->_data['title'] = '对账单_交易查询_伯嘉基金网';
        $this->_data['keywords'] = SEO_KEYWORDS;
        $this->_data['description'] = SEO_DESCRIPTION;
        $this->_data['nav'] = 'jycx';
        $this->_data['subnav'] = 'wddzd';
        $this->_data['headerCss'] = '?v=' . time();
    }

    public function index()
    {
        //用户相关
        $this->load->model('_user/User_Interact');
        $this->_data['userID'] = $this->User_Interact->getUserID();
        //判断权限
        $this->User_Interact->passportCheck(true);
        $this->_data['userInfo'] = $this->User_Model->getUserBaseByUserID($this->_data['userID']);
        $this->_data['nickName'] = $this->User_Interact->getNickName();
        $client_id = $this->_data['userInfo']['HsUserID'];
        $funds = $this->buyfunds_mem->get('funds');
        if (empty($funds)) {
            $this->load->model('_cron/cron_mdl');
            $funds = $this->cron_mdl->sortFunds()['funds'];
        }
        $year = filter_slashes($this->input->get('year', true));
        $month = sprintf('%02d', filter_slashes($this->input->get('month', true)));
        if (empty($year) || !intval($month)) {
            $time = time();
            $year = date('Y', $time);
            $month = date('m', $time);
        }
        $begin_date = $year . $month . '01';
        $end_date = $year . $month . date('t', strtotime("{$year}-{$month}"));
        $mdl = $this->transaction_mdl;
        $data = $this->_getBill($client_id, $begin_date, $end_date, $funds, $mdl);
        $this->_data['data'] = [
            'name' => $this->_data['userInfo']['NickName'],
            'year' => $year,
            'month' => $month,
            'date_range' => $this->_getDateRange($year, $month),
            'data1' => $data['data1'],
            'data2' => $data['data2'],
            'data3' => $data['data3'],
        ];
        $this->load->view('_transaction/bill.html', $this->_data);
    }

    private function _getDateRange($year, $month)
    {
        $time = time();
        if("{$year}-{$month}" == date('Y-m', $time)) {
            return date('Y-m-01', $time) . '~' . date('Y-m-d', $time);
        } else {
            return "{$year}-{$month}-01~{$year}-{$month}-" . date('t', strtotime("{$year}-{$month}"));
        }
    }

    public function bill()
    {
        set_time_limit(0);
        $key = filter_slashes($this->input->get('authkey', true));
        if ($key !== md5(date('Y_m_d') . 'NYIPPDmkl81ZFvWxdpLclpOaXhZRCjIn')) exit('Forbidden!');
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        $sendomail = &load_class('Sendomail');
        $funds = $this->buyfunds_mem->get('funds');
        if (empty($funds)) {
            $this->load->model('_cron/cron_mdl');
            $funds = $this->cron_mdl->sortFunds()['funds'];
        }
        $cids = filter_slashes($this->input->get('cids', true));
        $cids_arr = $cids ? explode('-', $cids) : [];
        $mdl = $this->transaction_mdl;
        $res = $mdl->getBillMailUserInfo($cids_arr);
        if (empty($res)) {
            die('不需要发送邮件');
        }
        $n = filter_slashes($this->input->get('n', true)) ? : date('n');
        $y = filter_slashes($this->input->get('y', true)) ? : date('Y');
        $begin_date = $end_date = '';
        $today = date('Y年m月d日');
        $halfyear = $year = $month = $quarter = $send = [];
        if ($n == 1) {
            $halfyear[0] = ($y - 1) . '年下半年';
            $halfyear[1] = ($y - 1) . '年6~12月份';
            $year[0] = ($y - 1) . '年';
            $year[1] = $year[0] . '全年';
            $month[0] = ($y - 1) . '年12月份';
            $month[1] = $month[0];
            $quarter[0] = ($y - 1) . '年第四季度';
            $quarter[1] = ($y - 1) . '年10、11、12月份';
            $send = [2, 3, 4, 5];
            $begin_date = ['4' => ($y - 1) . '0601', '5' => ($y - 1) . '0101', '2' => ($y - 1) . '1201', '3' => ($y - 1) . '1001'];
            $end_date = ($y - 1) . '1231';
        } elseif ($n == 7) {
            $halfyear[0] = $y . '年上半年';
            $halfyear[1] = $y . '年1~6月份';
            $month[0] = $y . '年6月份';
            $month[1] = $month[0];
            $quarter[0] = $y . '年第二季度';
            $quarter[1] = $y . '年4、5、6月份';
            $send = [4, 2, 3];
            $begin_date = ['4' => $y . '0101', '2' => $y . '0601', '3' => $y . '0401'];
            $end_date = $y . '0630';
        } elseif ($n == 4 || $n == 10) {
            $month[0] = $y . '年' . ($n - 1) . '月份';
            $month[1] = $month[0];
            $quarter[0] = $y . '年第' . intval($n/3) . '季度';
            $quarter[1] = $y . '年' . ($n - 3) . '、' . ($n - 2) . '、' . ($n - 1) . '月份';
            $send = [2, 3];
            $begin_date = ['2' => $y . '0' . ($n - 1) . '01', '3' => $y . '0' . ($n - 3) . '01'];
            $end_date = $y . '0' . ($n - 1) . '31';
        } else {
            $month[0] = $y . '年' . ($n - 1) . '月份';
            $month[1] = $month[0];
            $send = [2];
            $begin_date = ['2' => $y . '0' . ($n - 1) . '01'];
            $end_date = date('Ymd', strtotime(date('Ym01') . ' -1 day'));
        }
        $period = ['4' => $halfyear, '5' => $year, '2' => $month, '3' => $quarter];
        foreach ($res as $row) {
            if (!in_array($row['SendRate'], $send)/* || $row['UserID'] != 13216*/) continue;
            $email = $row['Email'];
            $subject = '武汉市伯嘉基金' . $period[$row['SendRate']][0] . '电子对账单';
            $data = $this->_getBill($row['HsUserID'], $begin_date[$row['SendRate']], $end_date, $funds, $mdl);
            $data1 = $data2 = $data3 = '';
            if ($data['data1']) {
                foreach ($data['data1'] as $item) {
                    $data1 .= "<tr><td>{$item['fund_code']}</td><td>{$item['fund_name']}</td><td>{$item['fund_type']}</td><td>{$item['net_value']}</td><td>{$item['total_share']}</td><td>{$item['worth_value']}</td><td>{$item['bank_info']}</td></tr>";
                }
            } else {
                $data1 .= '<tr><td colspan="7" style="line-height:230%;">暂无数据</td></tr>';
            }
            if ($data['data2']) {
                foreach ($data['data2'] as $item) {
                    $data2 .= "<tr><td>{$item['affirm_date']}</td><td>{$item['fund_code']}</td><td>{$item['fund_name']}</td><td>{$item['busin_type']}</td><td>{$item['confirm_state']}</td><td>{$item['confirm_share']}</td><td>{$item['confirm_balance']}</td><td>{$item['fare_sx']}</td><td>{$item['net_value']}</td><td>{$item['bank_info']}</td></tr>";
                }
            } else {
                $data2 .= '<tr><td colspan="10" style="line-height:230%;">暂无数据</td></tr>';
            }
            if ($data['data3']) {
                foreach ($data['data3'] as $item) {
                    $data3 .= "<tr><td>{$item['affirm_date']}</td><td>{$item['fund_code']}</td><td>{$item['fund_name']}</td><td>{$item['auto_buy']}</td><td>{$item['dividend_bala']}</td><td>{$item['dividend_share']}</td><td>{$item['net_value']}</td></tr>";
                }
            } else {
                $data3 .= '<tr><td colspan="10" style="line-height:230%;">暂无数据</td></tr>';
            }
            $content = <<<EOT
<div style="margin-bottom:50px;">
    <div>尊敬的{$row['NickName']}先生/女士：<br>&nbsp; &nbsp; <br>&nbsp; &nbsp;
        &nbsp;&nbsp;您好！{$period[$row['SendRate']][0]}对账单已产生，记录您{$period[$row['SendRate']][1]}账户交易情况请您核对。如有任何异议，可登入武汉伯嘉基金网 <a href="http://www.buyfunds.cn" target="_blank" style="text-decoration:none;" title="伯嘉基金网">www.buyfunds.cn</a> 或致电4000279899查询。
    </div>
</div>
<div>
    <h3 style="text-align:center;">{$row['NickName']} {$period[$row['SendRate']][0]}对账单</h3>
	<div style="float:right;">
		<div>
			<span>货币单位：人民币（元）</span>
			<span>份额单位：份</span>
		</div>
	</div>
	<div style="clear:both;">
		<hr>
	</div>
	<h4>历史持仓明细（截止至{$today}）</h4>
	<table cellpadding="0" cellspacing="0" border="1" style="width:100%;text-align:center;">
		<thead style="font-weight:bold;">
			<tr>
				<th>基金代码</th>
				<th>基金名称</th>
				<th>基金类型</th>
				<th>单位净值</th>
				<th>持有份额</th>
				<th>参考市值</th>
				<th>关联银行卡</th>
			</tr>
		</thead>
		<tbody>
			{$data1}
		</tbody>
	</table>
	<h4>历史交易明细（{$data['date_range']}）</h4>
	<table cellpadding="0" cellspacing="0" border="1" style="width:100%;text-align:center;">
		<thead>
			<tr>
				<th>确认日期</th>
				<th>基金代码</th>
				<th>基金名称</th>
				<th>业务类型</th>
				<th>确认状态</th>
				<th>确认份额</th>
				<th>确认金额</th>
				<th>手续费</th>
				<th>确认净值</th>
				<th>关联银行卡</th>
			</tr>
		</thead>
		<tbody>
			{$data2}
		</tbody>
	</table>
	<h4>历史分红明细（{$data['date_range']}）</h4>
	<table cellpadding="0" cellspacing="0" border="1" style="width:100%;text-align:center;">
		<thead>
			<tr>
				<th>确认日期</th>
				<th>基金代码</th>
				<th>基金名称</th>
				<th>分红方式</th>
				<th>分红金额</th>
				<th>红利再投份额</th>
				<th>确认净值</th>
			</tr>
		</thead>
		<tbody>
           {$data3}
		</tbody>
	</table>
	<p>友情提示：本对账单仅供参考，不构成资产证明。参考市值展示的结果根据净值和份额计算，具体份额变动及分红情况以注册登记机构登记为准。</p>
</div>
<div>
    <sign signid="1">
        <div style="color:#909090;font-family:Arial Narrow;font-size:12px"><br><br>-----------------------------------------------------------------------------------------
        </div>
        <div style="font-size:14px;font-family:Verdana;color:#000;">
            <div><span style="line-height: 23.8px;"><font face="Arial Black"><b><span
                    style="line-height: 23px;">武汉伯嘉客服部 </span><br style="line-height: 23px;"><span
                    style="line-height: 23px;">武汉市伯嘉基金销售有限公司</span><br style="line-height: 23px;"><span
                    style="line-height: 23px;">电话：4000279899</span><span style="line-height: 23px;">&nbsp; &nbsp; &nbsp;邮编：</span><span
                    t="7" data="430022"
                    style="line-height: 23px; border-bottom-width: 1px; border-bottom-style: dashed; border-bottom-color: rgb(204, 204, 204); z-index: 1;"><span
                    t="7" data="430000"
                    style="border-bottom-width: 1px; border-bottom-style: dashed; border-bottom-color: rgb(204, 204, 204); z-index: 1;">430000</span></span></b></font></span>
            </div>
            <div><span style="line-height: 23.8px;"><font face="Arial Black"><b>网址：www.buyfunds.cn<br
                    style="line-height: 23px;"><span style="line-height: 23px;">地址：</span><span
                    class="readmail_locationTip" over="0"
                    style="position: relative; border-bottom-width: 1px; border-bottom-style: dashed; border-bottom-color: rgb(171, 171, 171); line-height: 23px; z-index: auto;"><span
                    class="readmail_locationTip" over="0"
                    style="position: relative; border-bottom-width: 1px; border-bottom-style: dashed; border-bottom-color: rgb(171, 171, 171); z-index: auto;">湖北省武汉市江汉区淮海路中央商务区泛海国际SOHO城7栋2301室</span></span></b></font></span>
            </div>
        </div>
    </sign>
</div>
EOT;

            $info = ['mailto' => $email, 'subject' => $subject, 'content' => $content];
            $status = 1;
            if (!$sendomail->send_mail($info)) {
                $status = 2;
                logs(date('H:i:s') . PHP_EOL . '|info|' . print_r($info, true) . PHP_EOL, $logFile);
            }
            $mdl->insertTbEmail($row['UserID'], 'service@buyfunds.cn', $email, $subject, $status);
        }
    }

    private function _getBill($client_id, $begin_date, $end_date, $funds, $mdl)
    {
//        $end_date = '20170215';
        $data1 = $data2 = $data3 = null;
        $i = $j = $k = 1;
        $share = $trade = $divi = [];
        while ($res = $mdl->shareQry($client_id, 50, 50 * ($i -1) + 1)) {
            $share = array_merge($share, $res);
            $i ++;
        }
        if ($share){
            foreach ($share as $key => $value) {
                if ($value['current_share'] == 0) continue;
                $data1[$key]['fund_code'] = $value['fund_code'];
                $data1[$key]['net_value'] = $funds[$value['fund_code']]['net_value'];
                $data1[$key]['total_share'] = $value['current_share'];
                $data1[$key]['fund_name'] = $funds[$value['fund_code']]['fund_name'];
                $data1[$key]['fund_type'] = $funds[$value['fund_code']]['ofund_type'];
                $data1[$key]['worth_value'] = $value['worth_value'];
                $data1[$key]['bank_info'] = $mdl->datadictionary[1601][$value['bank_no']] . '|' . substr($value['bank_account'], -4);
            }
        }
        while ($res = $mdl->tradeConfirmQry($client_id, $begin_date, $end_date, 50, 50 * ($j -1) + 1)) {
            $trade = array_merge($trade, $res);
            $j ++;
        }
        if ($trade){
            foreach ($trade as $key => $value) {
                $data2[$key]['fund_code'] = $value['fund_code'];
                $data2[$key]['affirm_date'] = date('Y-m-d', strtotime($value['affirm_date']));
                $data2[$key]['fund_name'] = $funds[$value['fund_code']]['fund_name'];
                $data2[$key]['busin_type'] = config_item('businessdictionary')[$value['fund_busin_code']];
                $data2[$key]['confirm_state'] = $mdl->datadictionary['C00006'][$value['taconfirm_flag']];
                if ($value['fund_busin_code'] == '129') {
                    $data2[$key]['confirm_share'] = '--';
                    $data2[$key]['confirm_balance'] = '--';
                    $data2[$key]['net_value'] = '--';
                    $data2[$key]['fare_sx'] = '--';
                } else {
                    $data2[$key]['confirm_share'] = $value['trade_confirm_type'];
                    $data2[$key]['confirm_balance'] = $value['trade_confirm_balance'];
                    $data2[$key]['net_value'] = $value['net_value'];
                    $data2[$key]['fare_sx'] = $value['fare_sx'];
                }
                $bank = $mdl->accobankQry($value['trade_acco']);
                $data2[$key]['bank_info'] = $mdl->datadictionary[1601][$bank['bank_no']] . '|' . substr($bank['bank_account'], -4);
            }
        }
        while ($res = $mdl->diviQry($client_id, $begin_date, $end_date, 50, 50 * ($k -1) + 1)) {
            $divi = array_merge($divi, $res);
            $k ++;
        }
        if ($divi){
            foreach ($divi as $key => $value) {
                $data3[$key]['fund_code'] = $value['fund_code'];
                $data3[$key]['affirm_date'] = date('Y-m-d', strtotime($value['affirm_date']));
                $data3[$key]['fund_name'] = $funds[$value['fund_code']]['fund_name'];
                $data3[$key]['auto_buy'] = $mdl->datadictionary['2010'][$value['auto_buy']];
                $data3[$key]['dividend_bala'] = $value['dividend_bala'];
                $data3[$key]['dividend_share'] = $value['dividend_share'];
                $data3[$key]['net_value'] = $value['net_value'];
            }
        }
        return [
            'date_range' => date('Y年m月d日', strtotime($begin_date)) . " - " . date('Y年m月d日', strtotime($end_date)),
            'data1' => $data1,
            'data2' => $data2,
            'data3' => $data3,
        ];
    }
}