<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/****************************************************************
 * 用户定投
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * @author:jiangxuefeng @addtime:2016/12/27
 ****************************************************************/
class Funds_fix extends MY_Controller
{
    protected $_data = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('_fix/fix_mdl');
        $this->_data['keywords'] = SEO_KEYWORDS;
        $this->_data['description'] = SEO_DESCRIPTION;
        $this->_data['nav'] = 'jjdt';
        $this->_data['sidebar'] = 'jjdt';
        $this->_data['headerCss'] = '?v=' . time();
        //用户相关
        $this->load->model('_user/User_Interact');
        $this->_data['userID'] = $this->User_Interact->getUserID();
        //判断权限
        $this->User_Interact->passportCheck(true);
        $this->_data['userInfo'] = $this->User_Model->getUserBaseByUserID($this->_data['userID']);
        $this->_data['nickName'] = $this->User_Interact->getNickName();
    }

    public function index()
    {
        $this->_data['title'] = '我的定投_基金定投_伯嘉基金网';
        $this->_data['subnav'] = 'wddt';
        $client_id = $this->_data['userInfo']['HsUserID'];
        $this->_data['data'] = $this->_formatFix($this->fix_mdl->userFix($client_id));
        $this->load->view('_fix/funds_fix.html', $this->_data);
    }

    public function stop()
    {
        $this->_data['title'] = '定投终止记录_基金定投_伯嘉基金网';
        $this->_data['subnav'] = 'dtzzjl';
        $client_id = $this->_data['userInfo']['HsUserID'];
        $this->_data['data'] = $this->_formatStopFix($this->fix_mdl->getStopFix($client_id));
        $this->load->view('_fix/funds_fix_stop.html', $this->_data);
    }

    public function revert()
    {
        $this->_data['title'] = '恢复_基金定投_伯嘉基金网';
        $this->_data['subnav'] = 'dtzzjl';
        $postdata = array_map('filter_slashes', $this->input->post(null, true));
        $this->_data['data'] = $postdata;
        $this->_data['data']['rmb'] = numToRmb($postdata['balance']);
        $this->load->view('_fix/funds_fix_stop_revert.html', $this->_data);
    }

    /**
     * 格式化输出我的定投列表
     * @param $data
     * @return array
     */
    private function _formatFix($data)
    {
        if (empty($data)) return [];
        $this->load->library('buyfunds_mem', 'memcache');
        $funds = $this->buyfunds_mem->get('funds');
        if (empty($funds)) {
            $this->load->model('_cron/cron_mdl');
            $funds = $this->cron_mdl->sortFunds()['funds'];
        }
        $mdl = $this->fix_mdl;
        $return = $banks = [];
        $tmp = ['A' => '正常', 'H' => '终止', 'P' => '暂停'];
        foreach ($data as $key => $val) {
            if ($val['fix_state'] == 'H') continue;
            $return[$key]['fund_code'] = $val['fund_code'];
            $return[$key]['fund_name'] = $funds[$val['fund_code']]['fund_name'];
            if (isset($banks[$val['trade_acco']])) {
                $bank = $banks[$val['trade_acco']];
            } else {
                $bank = $mdl->getAccoInfo(['trade_acco' => $val['trade_acco']], 1, 1)[0];
                $banks[$val['trade_acco']] = $bank;
            }
            $return[$key]['bank_name'] = $mdl->datadictionary['1601'][$bank['bank_no']];
            $return[$key]['bank_account'] = substr($bank['bank_account'], -4);
            $return[$key]['balance'] = $val['balance'];
            $return[$key]['scheduled_protocol_id'] = $val['scheduled_protocol_id'];
            $return[$key]['protocol_fix_day'] = $val['protocol_fix_day'];
            $return[$key]['protocol_period_unit'] = $mdl->datadictionary['769035'][$val['protocol_period_unit']];
            $return[$key]['next_fixrequest_date'] = date('Y-m-d', strtotime($mdl->nextDayQry(date('Ymd', strtotime($val['next_fixrequest_date'] . ' -1 day')))));
            $return[$key]['fix_state'] = $tmp[$val['fix_state']];
            $return[$key]['fix_state_raw'] = $val['fix_state'];
        }
        unset($banks);
        return $return;
    }

    private function _formatStopFix($data)
    {
        if (empty($data)) return [];
        $this->load->library('buyfunds_mem', 'memcache');
        $funds = $this->buyfunds_mem->get('funds');
        if (empty($funds)) {
            $this->load->model('_cron/cron_mdl');
            $funds = $this->cron_mdl->sortFunds()['funds'];
        }
        $mdl = $this->fix_mdl;
        $return = [];
        foreach ($data as $key => $value) {
            $item = $this->fix_mdl->getFixBySPid($value['Scheduled_protocol_id']);
            $return[$key]['fund_code'] = $item['fund_code'];
            $return[$key]['fund_name'] = $funds[$item['fund_code']]['fund_name'];
            $return[$key]['share_type'] = $item['share_type'];
            $return[$key]['trade_acco'] = $item['trade_acco'];
            $return[$key]['bank_name'] = $value['BankName'];
            $return[$key]['bank_account'] = substr($value['BankCode'], -4);
            $return[$key]['bank_account_revert'] = bankCardReplace($value['BankCode']);
            $return[$key]['protocol_period_unit'] = $item['protocol_period_unit'];
            $return[$key]['period_unit'] = $mdl->datadictionary['769035'][$item['protocol_period_unit']];
            $return[$key]['protocol_fix_day'] = $item['protocol_fix_day'];
            $return[$key]['balance'] = $item['balance'];
            $return[$key]['time'] = $value['SubDate'];
            $return[$key]['id'] = $value['Scheduled_protocol_id'];
        }
        unset($banks);
        return $return;
    }
}