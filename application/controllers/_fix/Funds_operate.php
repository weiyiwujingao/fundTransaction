<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/****************************************************************
 * 定投基金的操作，暂停、终止、恢复等
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * @author:jiangxuefeng  @addtime:2017/01/09
 ****************************************************************/
class Funds_operate extends MY_Controller
{
    protected $_data = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('_fix/fix_mdl');
        $this->_data['keywords'] = SEO_KEYWORDS;
        $this->_data['description'] = SEO_DESCRIPTION;
        $this->_data['nav'] = 'jjdt';
        $this->_data['subnav'] = 'wddt';
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

    public function pause($id)
    {
        $this->_data['title'] = '暂停_基金定投_伯嘉基金';
        $mdl = $this->fix_mdl;
        $protocol_id = filter_slashes($id);
        $fix = $mdl->getFixBySPid($protocol_id);
        if (!$fix) errorJump('不存在的页面', '/fix/index.html');
        $bank = $mdl->getAccoInfo(['trade_acco' => $fix['trade_acco']], 1, 1, 1)[0];
        $this->load->library('buyfunds_mem', 'memcache');
        $funds = $this->buyfunds_mem->get('funds');
        if (empty($funds)) {
            $this->load->model('_cron/cron_mdl');
            $funds = $this->cron_mdl->sortFunds()['funds'];
        }
        $this->_data['data'] = [
            'name' => $funds[$fix['fund_code']]['fund_name'],
            'code' => $fix['fund_code'],
            'acco' => $mdl->datadictionary[1601][$bank['bank_no']] . '|' . substr($bank['bank_account'], -4),
            'bank_no' => $bank['bank_no'],
            'share_type' => $mdl->datadictionary[769023][$fix['share_type']],
            'period' => $mdl->datadictionary[769035][$fix['protocol_period_unit']],
            'period_raw' => $fix['protocol_period_unit'],
            'fix_day' => $fix['protocol_fix_day'],
            'balance' => $fix['balance'],
            'first_exchdate' => $fix['first_exchdate'],
            'protocol_id' => $protocol_id,
            'fix_state' => $fix['fix_state']
        ];
        $this->load->view('_fix/funds_fix_pause.html', $this->_data);
    }

    public function resume($id)
    {
        $this->_data['title'] = '恢复_基金定投_伯嘉基金';
        $mdl = $this->fix_mdl;
        $protocol_id = filter_slashes($id);
        $fix = $mdl->getFixBySPid($protocol_id);
        if (!$fix) errorJump('不存在的页面', '/fix/index.html');
        $bank = $mdl->getAccoInfo(['trade_acco' => $fix['trade_acco']], 1, 1, 1)[0];
        $this->load->library('buyfunds_mem', 'memcache');
        $funds = $this->buyfunds_mem->get('funds');
        if (empty($funds)) {
            $this->load->model('_cron/cron_mdl');
            $funds = $this->cron_mdl->sortFunds()['funds'];
        }
        $this->_data['data'] = [
            'name' => $funds[$fix['fund_code']]['fund_name'],
            'code' => $fix['fund_code'],
            'acco' => $mdl->datadictionary[1601][$bank['bank_no']] . '|' . substr($bank['bank_account'], -4),
            'bank_no' => $bank['bank_no'],
            'share_type' => $mdl->datadictionary[769023][$fix['share_type']],
            'period' => $mdl->datadictionary[769035][$fix['protocol_period_unit']],
            'period_raw' => $fix['protocol_period_unit'],
            'fix_day' => $fix['protocol_fix_day'],
            'balance' => $fix['balance'],
            'first_exchdate' => $fix['first_exchdate'],
            'protocol_id' => $protocol_id,
            'fix_state' => $fix['fix_state']
        ];
        $this->load->view('_fix/funds_fix_resume.html', $this->_data);
    }

    public function modify($id)
    {
        $this->_data['title'] = '修改_基金定投_伯嘉基金';
        $mdl = $this->fix_mdl;
        $protocol_id = filter_slashes($id);
        $fix = $mdl->getFixBySPid($protocol_id);
        if (!$fix) errorJump('不存在的页面', '/fix/index.html');
        $bank = $mdl->getAccoInfo(['trade_acco' => $fix['trade_acco']], 1, 1, 1)[0];
        $this->load->library('buyfunds_mem', 'memcache');
        $funds = $this->buyfunds_mem->get('funds');
        if (empty($funds)) {
            $this->load->model('_cron/cron_mdl');
            $funds = $this->cron_mdl->sortFunds()['funds'];
        }
        $this->_data['data'] = [
            'name' => $funds[$fix['fund_code']]['fund_name'],
            'code' => $fix['fund_code'],
            'acco' => $mdl->datadictionary[1601][$bank['bank_no']] . '|' . substr($bank['bank_account'], -4),
            'bank_no' => $bank['bank_no'],
            'share_type' => $mdl->datadictionary[769023][$fix['share_type']],
            'period' => $mdl->datadictionary[769035][$fix['protocol_period_unit']],
            'protocol_period_unit' => $fix['protocol_period_unit'],
            'fix_day' => $fix['protocol_fix_day'],
            'next_day' => date('Y-m-d', strtotime($mdl->nextDayQry(date('Ymd', strtotime($fix['next_fixrequest_date'] . ' -1 day'))))),
            'balance' => $fix['balance'],
            'rmb' => numToRmb($fix['balance']),
            'protocol_id' => $protocol_id,
        ];
        $this->load->view('_fix/funds_fix_modify.html', $this->_data);
    }


    public function abort($id)
    {
        $this->_data['title'] = '终止_基金定投_伯嘉基金';
        $mdl = $this->fix_mdl;
        $protocol_id = filter_slashes($id);
        $fix = $mdl->getFixBySPid($protocol_id);
        if (!$fix) errorJump('不存在的页面', TRADE_WEB_URL . '/fix/index.html');
        $bank = $mdl->getAccoInfo(['trade_acco' => $fix['trade_acco']], 1, 1, 1)[0];
        $this->load->library('buyfunds_mem', 'memcache');
        $funds = $this->buyfunds_mem->get('funds');
        if (empty($funds)) {
            $this->load->model('_cron/cron_mdl');
            $funds = $this->cron_mdl->sortFunds()['funds'];
        }
        $this->_data['data'] = [
            'name' => $funds[$fix['fund_code']]['fund_name'],
            'code' => $fix['fund_code'],
            'acco' => $mdl->datadictionary[1601][$bank['bank_no']] . '|' . substr($bank['bank_account'], -4),
            'bank_no' => $bank['bank_no'],
            'share_type' => $mdl->datadictionary[769023][$fix['share_type']],
            'period' => $mdl->datadictionary[769035][$fix['protocol_period_unit']],
            'period_raw' => $fix['protocol_period_unit'],
            'fix_day' => $fix['protocol_fix_day'],
            'balance' => $fix['balance'],
            'first_exchdate' => $fix['first_exchdate'],
            'protocol_id' => $protocol_id,
        ];
        $this->load->view('_fix/funds_fix_abort.html', $this->_data);
    }

    public function result()
    {
        if (!$this->input->post()) redirect(TRADE_WEB_URL . '/fix/index.html');
        $postdata = array_map('filter_slashes', $this->input->post(null, true));
        if (!isset($postdata['operation'])) errorJump('非法访问', TRADE_WEB_URL . '/fix/index.html');
        $mdl = $this->fix_mdl;
        $client_id = $this->_data['userInfo']['HsUserID'];
        $bank = $mdl->getAccoInfo(['client_id' => $client_id, 'bank_no' => $postdata['bank_no']], 1, 1, 1)[0];
        $smstemplate = config_item('fund_sms_content');
        $this->load->model('_user/Fund_Model');
        switch ($postdata['operation']) {
            case 'pause' :
                $this->_data['title'] = '暂停_基金定投_伯嘉基金';
                $result = $mdl->fixmodifyTrade($postdata['protocol_id'], 'P', tradePswd($postdata['password']));
                $this->_data['info'] = $result['msg'];
                $this->_data['success'] = $result['code'] == 200;
                $status = $this->_data['success'] ? 1 : 0;
                if ($this->_data['success']) {
                    //发送短信
                    $sendcontent = sprintf($smstemplate['20'], '您申请暂停定投', $postdata['fund_name'], date('m月d日', strtotime($mdl->nextDayQry($mdl->nextDayQry(date('Ymd'))))));
                    $info = array('sign' => 0, 'channel' => 3, 'content' => $sendcontent, 'mobile' => $this->_data['userInfo']['Mobile']);
                    sendSmsAction($info);
                }
                //插入数据库
                $this->Fund_Model->insertManagement(array('UserID' => $this->_data['userInfo']['UserID'], 'Type' => 4, 'Payway' => 2,
                    'Modeway' => 2, 'BankName' => $this->fix_mdl->datadictionary[1601][$bank['bank_no']], 'BankCode' => $bank['bank_account'], 'Cycle' => $postdata['period_raw'],
                    'Scheduled_protocol_id' => $postdata['protocol_id'],
                    'FixedDate' => date('Y-m-d H:i:s', strtotime(substr($postdata['first_exchdate'], 0, 6) . sprintf('%02d', $postdata['fix_day']))),
                    'TotalMoney' => (int)sprintf("%.2f", $postdata['balance'] * 100), 'TrueMoney' => '', 'Poundage' => '',
                    'Status' => $status, 'SubDate' => date('Y-m-d H:i:s'), 'allot_no' => $result['allot_no']));
                return $this->load->view('_fix/funds_fix_pause_result.html', $this->_data);
                break;
            case 'resume' :
                $this->_data['title'] = '恢复_基金定投_伯嘉基金';
                $result = $mdl->fixmodifyTrade($postdata['protocol_id'], 'A', tradePswd($postdata['password']));
                $this->_data['info'] = $result['msg'];
                $this->_data['success'] = $result['code'] == 200;
                $status = $this->_data['success'] ? 1 : 0;
                if ($this->_data['success']) {
                    //发送短信
                    $sendcontent = sprintf($smstemplate['21'], $postdata['fund_name'], date('m月d日', strtotime($mdl->nextDayQry($mdl->nextDayQry(date('Ymd'))))));
                    $info = array('sign' => 0, 'channel' => 3, 'content' => $sendcontent, 'mobile' => $this->_data['userInfo']['Mobile']);
                    sendSmsAction($info);
                }
                //插入数据库
                $this->Fund_Model->insertManagement(array('UserID' => $this->_data['userInfo']['UserID'], 'Type' => 4, 'Payway' => 2,
                    'Modeway' => 3, 'BankName' => $this->fix_mdl->datadictionary[1601][$bank['bank_no']], 'BankCode' => $bank['bank_account'], 'Cycle' => $postdata['period_raw'],
                    'Scheduled_protocol_id' => $postdata['protocol_id'],
                    'FixedDate' => date('Y-m-d H:i:s', strtotime(substr($postdata['first_exchdate'], 0, 6) . sprintf('%02d', $postdata['fix_day']))),
                    'TotalMoney' => (int)sprintf("%.2f", $postdata['balance'] * 100), 'TrueMoney' => '', 'Poundage' => '',
                    'Status' => $status, 'SubDate' => date('Y-m-d H:i:s'), 'allot_no' => $result['allot_no']));
                return $this->load->view('_fix/funds_fix_resume_result.html', $this->_data);
                break;
            case 'modify' :
                $this->_data['title'] = '修改_基金定投_伯嘉基金';
                $param = [
                    'protocol_period_unit' => $postdata['period'],
                    'protocol_fix_day' => $postdata['fix_day'],
                    'balance' => $postdata['balance'],
                ];
                $result = $mdl->fixmodifyTrade($postdata['protocol_id'], 'A', tradePswd($postdata['password']), $param);
                $this->_data['info'] = $result['msg'];
                $this->_data['success'] = $result['code'] == 200;
                $this->_data['period'] = $mdl->datadictionary[769035][$postdata['period']];
                $this->_data['fix_day'] = $postdata['fix_day'];
                $this->_data['balance'] = sprintf('%.2f', $postdata['balance']);
                $this->_data['fund_name'] = $postdata['fund_name'];
                $status = $this->_data['success'] ? 1 : 0;
                if ($this->_data['success']) {
                    //发送短信
                    $sendcontent = sprintf($smstemplate['19'], $postdata['fund_name'], date('m月d日', strtotime($mdl->nextDayQry($mdl->nextDayQry(date('Ymd'))))));
                    $info = array('sign' => 0, 'channel' => 3, 'content' => $sendcontent, 'mobile' => $this->_data['userInfo']['Mobile']);
                    sendSmsAction($info);
                }
                //插入数据库
                $this->Fund_Model->insertManagement(array('UserID' => $this->_data['userInfo']['UserID'], 'Type' => 4, 'Payway' => 2,
                    'Modeway' => 1, 'BankName' => $this->fix_mdl->datadictionary[1601][$bank['bank_no']], 'BankCode' => $bank['bank_account'], 'Cycle' => $postdata['period'],
                    'Scheduled_protocol_id' => $postdata['protocol_id'],
                    'FixedDate' => $this->_data['success'] ? date('Y-m-d H:i:s', strtotime(substr($this->_data['info'], 0, 6) . sprintf('%02d', $postdata['fix_day']))) : '',
                    'TotalMoney' => (int)sprintf("%.2f", $postdata['balance'] * 100), 'TrueMoney' => '', 'Poundage' => '',
                    'Status' => $status, 'SubDate' => date('Y-m-d H:i:s'), 'allot_no' => $result['allot_no']));
                return $this->load->view('_fix/funds_fix_modify_result.html', $this->_data);
                break;
            case 'abort' :
                $this->_data['title'] = '终止_基金定投_伯嘉基金';
                $result = $mdl->fixmodifyTrade($postdata['protocol_id'], 'H', tradePswd($postdata['password']));
                $this->_data['info'] = $result['msg'];
                $this->_data['success'] = $result['code'] == 200;
                $status = $this->_data['success'] ? 1 : 0;
                if ($this->_data['success']) {
                    //发送短信
                    $sendcontent = sprintf($smstemplate['22'], '您申请终止定投', $postdata['fund_name'], date('m月d日', strtotime($mdl->nextDayQry($mdl->nextDayQry(date('Ymd'))))));
                    $info = array('sign' => 0, 'channel' => 3, 'content' => $sendcontent, 'mobile' => $this->_data['userInfo']['Mobile']);
                    sendSmsAction($info);
                }
                //插入数据库
                $this->Fund_Model->insertManagement(array('UserID' => $this->_data['userInfo']['UserID'], 'Type' => 4, 'Payway' => 2,
                    'Modeway' => 4, 'BankName' => $this->fix_mdl->datadictionary[1601][$bank['bank_no']], 'BankCode' => $bank['bank_account'], 'Cycle' => $postdata['period_raw'],
                    'Scheduled_protocol_id' => $postdata['protocol_id'],
                    'FixedDate' => date('Y-m-d H:i:s', strtotime(substr($postdata['first_exchdate'], 0, 6) . sprintf('%02d', $postdata['fix_day']))),
                    'TotalMoney' => (int)sprintf("%.2f", $postdata['balance'] * 100), 'TrueMoney' => '', 'Poundage' => '',
                    'Status' => $status, 'SubDate' => date('Y-m-d H:i:s'), 'allot_no' => $result['allot_no']));
                return $this->load->view('_fix/funds_fix_abort_result.html', $this->_data);
                break;
            default :
                errorJump('不存在的页面', TRADE_WEB_URL . '/fix/index.html');
                break;
        }
    }
}