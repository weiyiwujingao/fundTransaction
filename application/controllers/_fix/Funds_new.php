<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/****************************************************************
 * 新增定投基金
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * @author:jiangxuefeng  @addtime:2017/01/05
 ****************************************************************/
class Funds_new extends MY_Controller
{
    protected $_data = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('_fix/fix_mdl');
        $this->load->model('_user/User_Interact');
        $this->_data['title'] = '新增定投_基金定投_伯嘉基金网';
        $this->_data['keywords'] = SEO_KEYWORDS;
        $this->_data['description'] = SEO_DESCRIPTION;
        $this->_data['nav'] = 'jjdt';
        $this->_data['subnav'] = 'xzdt';
        $this->_data['sidebar'] = 'jjdt';
        $this->_data['headerCss'] = '?v=' . time();
        if (!$this->input->is_ajax_request()) {
            //判断权限
            $this->User_Interact->passportCheck(true);
            //用户相关
            $this->_data['userID'] = $this->User_Interact->getUserID();
            $this->_data['userInfo'] = $this->User_Model->getUserBaseByUserID($this->_data['userID']);
            $this->_data['nickName'] = $this->User_Interact->getNickName();
            if (!$this->_data['userInfo']['HsAccount']) errorJump('请先绑定银行卡再进行购买操作！', TRADE_ADD_BANK_URL);
        }
    }

    public function index()
    {
        $client_id = $this->_data['userInfo']['HsUserID'];
        $trade_acco = $this->_data['userInfo']['HsAccount'];
        $fund_code = filter_slashes($this->input->get('fundcode', true));
        $bank_form_db = $this->User_Model->getUserBankByUserID($this->_data['userID']);
        $this->_data['risk_bear'] = $this->User_Model->getUserDetailByUserID($this->_data['userID'])['RiskBear'];
        $this->_data['fund_code'] = $fund_code;
        $this->_data['accos'] = $this->_formatBankAcco($this->fix_mdl->getAccoInfo(['client_id' => $client_id]), $bank_form_db);
        $this->_data['master_acco'] = $trade_acco;
        //默认1号位定投日
        $this->_data['deduction_day'] = date('Y-m-d', strtotime($this->_getDeductionDay('1')));
        $this->load->view('_fix/funds_fix_new.html', $this->_data);
    }

    public function preview()
    {
        if (!$this->input->post()) redirect(TRADE_WEB_URL . '/fix/new.html');
        $postdata = array_map('filter_slashes', $this->input->post(null, true));
        $postdata['balance'] = sprintf('%.2f', $postdata['balance']);
        $acco = explode('|', $postdata['acco']);
        $data = [
            'trade_acco' => $acco[0],
            'acco_info' => $acco[1],
            'period_unit' => $this->fix_mdl->datadictionary[769035][$postdata['period']],
        ];
        $this->_data['data'] = array_merge($postdata, $data);
        $this->load->view('_fix/funds_fix_new_preview.html', $this->_data);
    }

    public function result()
    {
        if (!$this->input->post()) redirect(TRADE_WEB_URL . '/fix/new.html');
        $data = array_map('filter_slashes', $this->input->post(null, true));
        $fund = $this->fix_mdl->newhqQry($data['fund_code']);
        $param = [
            'fund_code' => $data['fund_code'],
            'trade_acco' => $data['trade_acco'],
            'protocol_period_unit' => $data['period'],
            'protocol_fix_day' => $data['fixday'],
            'balance' => $data['balance'],
            'password' => tradePswd($data['password']),
            'share_type' => $fund['share_type'],
            'first_exchdate' => substr($this->_getDeductionDay($data['fixday']), 0, 6),
            'expiry_date' => '99990101',
            'trade_period' => '1',
        ];
        $result = $this->fix_mdl->fixallotTrade($param);
        $this->_data['info'] = $result['success_type'] == 0 ? '' : $result['error_info'];
        $this->_data['fund_code'] = $data['fund_code'];
        $this->_data['success'] = empty($this->_data['info']);
        $this->_data['fix_day'] = $data['fixday'];
        $this->_data['period'] = $this->fix_mdl->datadictionary[769035][$data['period']];
        $this->_data['balance'] = sprintf('%.2f',$data['balance']);
        $this->_data['acco_info'] = $data['acco_info'];
        $this->_data['fund_name'] = $fund['fund_name'];
        $status = $this->_data['success'] ? 1 : 0;
        if ($this->_data['success']) {
            //发送短信
            $smstemplate = config_item('fund_sms_content');
            $sendcontent = sprintf($smstemplate['18'], $fund['fund_name'], sprintf('%.2f', $data['balance']));
            $info = array('sign' => 0, 'channel' => 3, 'content' => $sendcontent, 'mobile' => $this->_data['userInfo']['Mobile']);
            sendSmsAction($info);
        }
        $bank = $this->fix_mdl->getAccoInfo(['trade_acco' => $data['trade_acco'], 1, 1, 1])[0];
        //插入数据库
        $this->load->model('_user/Fund_Model');
        $this->Fund_Model->insertManagement(array('UserID' => $this->_data['userInfo']['UserID'], 'Type' => 4, 'Payway' => 2,
            'Modeway' => 5, 'BankName' => $this->fix_mdl->datadictionary[1601][$bank['bank_no']], 'BankCode' => $bank['bank_account'], 'Cycle' => 1,
            'Scheduled_protocol_id' => isset($result['scheduled_protocol_id']) ? $result['scheduled_protocol_id'] : null,
            'FixedDate' => date('Y-m-d H:i:s', strtotime($param['first_exchdate'] . sprintf('%02d', $data['fixday']))),
            'TotalMoney' => (int)sprintf("%.2f", $data['balance'] * 100), 'TrueMoney' => '', 'Poundage' => '',
            'Status' => $status, 'SubDate' => date('Y-m-d H:i:s'), 'allot_no' => $result['allot_no']));
        if (isset($data['id']) && $data['id']) {
            $this->Fund_Model->insertManagement(array('UserID' => $this->_data['userInfo']['UserID'], 'Type' => 4, 'Payway' => 2,
                'Modeway' => 6, 'BankName' => $this->fix_mdl->datadictionary[1601][$bank['bank_no']], 'BankCode' => $bank['bank_account'], 'Cycle' => 1,
                'Scheduled_protocol_id' => $data['id'],
                'FixedDate' => date('Y-m-d H:i:s', strtotime($param['first_exchdate'] . sprintf('%02d', $data['fixday']))),
                'TotalMoney' => (int)sprintf("%.2f", $data['balance'] * 100), 'TrueMoney' => '', 'Poundage' => '',
                'Status' => $status, 'SubDate' => date('Y-m-d H:i:s'), 'allot_no' => $result['allot_no']));
        }
        $this->load->view('_fix/funds_fix_new_result.html', $this->_data);
    }

    /**
     * 获取费率及手续费
     */
    public function getFare()
    {
        if (!$this->input->is_ajax_request()) exit('Forbidden!');
        $islogin = $this->User_Interact->ajaxPassportCheck();
        if ($islogin['flag'] != '10000') {
            returnJsonStr(['code' => 0, 'message' => '登录状态已失效，请重新登录', 'url' => TRADE_LOGIN_URL . '?rt=' . base64_encode($_SERVER['HTTP_REFERER'])]);
        }
        $this->_data['userID'] = $this->User_Interact->getUserID();
        $this->_data['userInfo'] = $this->User_Model->getUserBaseByUserID($this->_data['userID']);
        $client_id = $this->_data['userInfo']['HsUserID'];
        $fund_code = filter_slashes($this->input->post('code', true));
        $trade_acco = filter_slashes($this->input->post('trade_acco', true));
        $balance = filter_slashes($this->input->post('balance', true));
        $fare = $this->fix_mdl->calcFeeTrade($trade_acco, $fund_code, $balance, $client_id);
        if ($fare['success_type']) {
            returnJsonStr(['code' => 201, 'message' => $fare['error_info']]);
        }
        $ratio = $fare['fare_ratio'] * $fare['discount'];
        returnJsonStr(['code' => 200, 'data' => ['fare_sx' => sprintf('%.2f', $balance * $ratio / (1 + $ratio)), 'fare_ratio' => sprintf('%.2f', $fare['fare_ratio'] * 100), 'bj_ratio' => sprintf('%.2f', $ratio * 100), 'discount' => $fare['discount']]]);
    }

    /**
     * 根据代码、首字母简写、汉字检索基金
     */
    public function searchFunds()
    {
        if (!$this->input->is_ajax_request()) exit('Forbidden!');
        $islogin = $this->User_Interact->ajaxPassportCheck();
        if ($islogin['flag'] != '10000') {
            returnJsonStr(['code' => 0, 'message' => '登录状态已失效，请重新登录', 'url' => TRADE_LOGIN_URL . '?rt=' . base64_encode($_SERVER['HTTP_REFERER'])]);
        }
        $key = filter_slashes(trim($this->input->post('key', true)));
        $rows = filter_slashes($this->input->post('rows', true));
        $res = $this->fix_mdl->searchFunds($key, $rows);
        if (!$res) returnJson(['code' => 201, 'message' => '没有查询结果']);
        returnJsonStr(['code' => 200, 'data' => $res]);
    }

    /**
     * 根据字母检索基金，返回json
     */
    public function searchCodesByLetter()
    {
        if (!$this->input->is_ajax_request()) exit('Forbidden!');
        $islogin = $this->User_Interact->ajaxPassportCheck();
        if ($islogin['flag'] != '10000') {
            returnJsonStr(['code' => 0, 'message' => '登录状态已失效，请重新登录', 'url' => TRADE_LOGIN_URL . '?rt=' . base64_encode($_SERVER['HTTP_REFERER'])]);
        }
        $key = filter_slashes(trim($this->input->post('key', true)));
        $res = $this->fix_mdl->searchCodesByLetter($key);
        if (!$res) returnJson(['code' => 201, 'message' => '没有查询结果']);
        returnJsonStr(['code' => 200, 'data' => $res]);
    }

    /**
     * 获取基金的名称、类型、风险等级、份额类型、分红方式
     * @param $fund_code
     */
    public function getFundInfo($fund_code)
    {
        if (!$this->input->is_ajax_request()) exit('Forbidden!');
        $islogin = $this->User_Interact->ajaxPassportCheck();
        if ($islogin['flag'] != '10000') {
            returnJsonStr(['code' => 0, 'message' => '登录状态已失效，请重新登录', 'url' => TRADE_LOGIN_URL . '?rt=' . base64_encode($_SERVER['HTTP_REFERER'])]);
        }
        $fund_code = filter_slashes($fund_code);
        $trade_acco = filter_slashes($this->input->post('acco', true));
        $this->load->library('buyfunds_mem', 'memcache');
        $funds = $this->buyfunds_mem->get('funds');
        $funds_raw = $this->buyfunds_mem->get('funds_raw');
        if (empty($funds) || empty($funds_raw)) {
            $this->load->model('_cron/cron_mdl');
            $cron = $this->cron_mdl->sortFunds();
            $funds = $cron['funds'];
            $funds_raw = $cron['funds_raw'];
        }
        $res = isset($funds[$fund_code]) ? $funds[$fund_code] : null;
        if ($res) {
            $share = $this->fix_mdl->shareQry(['trade_acco' => $trade_acco, 'fund_code' => $fund_code], 1, 1);
            if ($share && $share[0]['current_share'] != 0) {
                $res['min_value'] = $funds_raw[$fund_code]['second_min'];
            }
            $res['fund_status_raw'] = isset($funds_raw[$fund_code]['fund_status']) ? $funds_raw[$fund_code]['fund_status'] : '-1';
            $res['ofund_risklevel_raw'] = isset($funds_raw[$fund_code]['ofund_risklevel']) ? $funds_raw[$fund_code]['ofund_risklevel'] : '0';
            returnJsonStr(['code' => 200, 'data' => $res]);
        }
        returnJsonStr(['code' => 201, 'message' => '没有找到相关的基金']);
    }

    /**
     * 查询首次扣款的日期
     * @return false|string
     */
    public function getDeductionDay()
    {
        if (!$this->input->is_ajax_request()) exit('Forbidden!');
        $islogin = $this->User_Interact->ajaxPassportCheck();
        if ($islogin['flag'] != '10000') {
            returnJsonStr(['code' => 0, 'message' => '登录状态已失效，请重新登录', 'url' => TRADE_LOGIN_URL . '?rt=' . base64_encode($_SERVER['HTTP_REFERER'])]);
        }
        $day = filter_slashes(trim($this->input->post('fix_day', true)));
        $deduction_day = date('Y-m-d', strtotime($this->_getDeductionDay($day)));
        if ($deduction_day) returnJsonStr(['code' => 200, 'data' => $deduction_day]);
        returnJsonStr(['code' => 0, 'data' => '服务器出错']);
    }

    /**
     * 获取定投的扣款日期，格式为20170909
     * @param $day
     * @return mixed
     */
    private function _getDeductionDay($day)
    {
        $day = sprintf("%02d", $day);
        $set_day = date('Ym' . $day);
        $tmp1 = date('Ymd', strtotime($set_day . ' +1 month -1 day'));
        if ($day <= date('d')) {
            $tmp = $tmp1;
        } else {
            $tmp2 = date('Ymd', strtotime($set_day . ' -1 day'));
            if (date('H') < 15) {
                $tmp = $tmp2;
            } else {
                $next_next_day = $this->fix_mdl->nextDayQry($this->fix_mdl->nextDayQry(date('Ymd')));
                $tmp = $next_next_day > $set_day ? $tmp1 : $tmp2;
            }
        }
        return $this->fix_mdl->nextDayQry($tmp);
    }

    /**
     * 格式化银行卡信息（中国银行（**************0098））
     * @param $data
     * @return array
     */
    private function _formatBankAcco($data, $db_data)
    {
        if (!$data||!$db_data) return [];
        $return = $tmp = $bankcard = [];
        foreach($db_data as $k=>$v){
            $bankcard[] = $v['BankCard'];
        }
        foreach ($data as $key => $value) {
            if (in_array($value['bank_account'], $tmp) || !$value['bank_account'] || !in_array($value['bank_account'], $bankcard)) continue;
            $return[$key]['info'] = $this->fix_mdl->datadictionary['1601'][$value['bank_no']] . '(' . bankCardReplace($value['bank_account']) . ')';
            $return[$key]['trade_acco'] = $value['trade_acco'];
            $return[$key]['bank_account'] = $value['bank_account'];
            $tmp[] = $value['bank_account'];
        }
        return $return;
    }
}