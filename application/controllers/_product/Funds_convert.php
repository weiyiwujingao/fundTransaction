<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/****************************************************************
 * 基金转换
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * @author:jiangxuefeng  @addtime:2016/12/30
 ****************************************************************/
class Funds_convert extends MY_Controller
{
    protected $_data = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('_product/convert_mdl');
        $this->load->model('_user/User_Interact');
        $this->_data['title'] = '转换_基金交易_伯嘉基金网';
        $this->_data['keywords'] = SEO_KEYWORDS;
        $this->_data['description'] = SEO_DESCRIPTION;
        $this->_data['nav'] = 'jjcp';
        $this->_data['subnav'] = 'jjzh';
        $this->_data['sidebar'] = 'jjzh';
        $this->_data['headerCss'] = '?v=' . time();
        if (!$this->input->is_ajax_request()) {
            //判断权限
            $this->User_Interact->passportCheck(true);
            //用户相关
            $this->_data['userID'] = $this->User_Interact->getUserID();
            $this->_data['userInfo'] = $this->User_Model->getUserBaseByUserID($this->_data['userID']);
            $this->_data['nickName'] = $this->User_Interact->getNickName();
        }
    }

    public function index()
    {
        $client_id = $this->_data['userInfo']['HsUserID'];
        $i = 1;
        $data = [];
        while ($res = $this->convert_mdl->shareQry(['client_id' => $client_id], ($i - 1) * 50 + 1, 50)) {
            $data = array_merge($data, $res);
            $i++;
        }
        $this->_data['data'] = $this->_formatConvert($data);
        $this->load->view('_product/funds_convert.html', $this->_data);
    }

    public function step($fund_code)
    {
        $client_id = $this->_data['userInfo']['HsUserID'];
        $fund_code = filter_slashes($fund_code);
        $bank_no = filter_slashes($this->input->get('bank', true));
        $trade_acco = $this->convert_mdl->getTradeAcco($client_id, $bank_no)['trade_acco'];
        if (!$fund_code) redirect('/product/convert.html');
        $this->_data['fund_code'] = $fund_code;
        $data = $this->convert_mdl->getConvertibleFunds($fund_code, 10, 0);
        $this->_data['data'] = $data['res'];
        $this->_data['num'] = $data['arrkey'];
        $this->_data['min_share'] = $data['min_share'];
        $share = $this->convert_mdl->shareQry(['client_id' => $client_id, 'trade_acco' => $trade_acco, 'fund_code' => $fund_code], 1, 1);
        $this->_data['allshare'] = isset($share[0]['enable_shares']) ? $share[0]['enable_shares'] : 0;
        $this->load->view('_product/funds_convert_step.html', $this->_data);
    }

    public function result()
    {
        $client_id = $this->_data['userInfo']['HsUserID'];
        $this->load->library('buyfunds_mem', 'memcache');
        $funds_raw = $this->buyfunds_mem->get('funds_raw');
        if (empty($funds_raw)) {
            $this->load->model('_cron/cron_mdl');
            $funds_raw = $this->cron_mdl->sortFunds()['funds_raw'];
        }
        $postdata = filter_slashes($this->input->post());
        $fund_code = $postdata['source_fund'];
        $target_fund_code = $postdata['target_fund'];
        $share = $this->convert_mdl->shareQry(['client_id' => $client_id, 'fund_code' => $fund_code], 1, 1);
        if (empty($share)) errorJump('暂未持有' . $fund_code, TRADE_WEB_URL . '/product/convert.html');
        $data = [
            'trade_acco' => $share[0]['trade_acco'],
            'ta_acco' => $share[0]['ta_acco'],
            'password' => tradePswd($postdata['password']),
            'fund_code' => $fund_code,
            'share_type' => $share[0]['share_type'],
            'target_fund_code' => $target_fund_code,
            'target_share_type' => $funds_raw[$target_fund_code]['share_type'],
            'shares' => $postdata['shares'],
            'fund_exceed_flag' => $postdata['exceed_flag'],
            'trade_source' => SOURCE_TRADE
        ];
        $result = $this->convert_mdl->convert($data);
        $this->_data['info'] = $result['success_type'] == 0 ? '' : $result['error_info'];
        $this->_data['success'] = empty($this->_data['info']);
        $status = $this->_data['success'] ? 9 : 0;
        if ($this->_data['success']) {
            //发送短信
            $smstemplate = config_item('fund_sms_content');
            $sendcontent = sprintf($smstemplate['10'], $funds_raw[$fund_code]['fund_name'], sprintf('%.2f', $postdata['shares']));
            $info = array('sign' => 0, 'channel' => 3, 'content' => $sendcontent, 'mobile' => $this->_data['userInfo']['Mobile']);
            sendSmsAction($info);
        }
        //插入数据库
        $bank = $this->convert_mdl->getAccoInfo($data['trade_acco']);
        $this->load->model('_user/Fund_Model');
        $this->Fund_Model->insertManagement(array('UserID' => $this->_data['userInfo']['UserID'], 'Type' => 4, 'Payway' => 2,
            'Modeway' => 2, 'BankName' => $this->convert_mdl->datadictionary['1601'][$bank['bank_no']], 'BankCode' => $bank['bank_account'], 'Cycle' => '',
            'FixedDate' => '',
            'TotalMoney' => (int)$postdata['shares'] * 100,
            'Status' => $status, 'SubDate' => date('Y-m-d H:i:s'), 'allot_no' => $result['allot_no']));
        $this->load->view('_product/funds_convert_result.html', $this->_data);
    }

    /**
     * ajax返回可转换的基金
     */
    public function getAllShare()
    {
        if (!$this->input->is_ajax_request()) exit('Forbidden!');
        $islogin = $this->User_Interact->ajaxPassportCheck();
        if ($islogin['flag'] != '10000') {
            returnJsonStr(['code' => 0, 'message' => '登录状态已失效，请重新登录', 'url' => TRADE_LOGIN_URL . '?rt=' . base64_encode($_SERVER['HTTP_REFERER'])]);
        }
        $fund_code = filter_slashes($this->input->post('code'));
        $offset = filter_slashes($this->input->post('offset'));
        $rows = filter_slashes($this->input->post('rows'));
        $data = $this->convert_mdl->getConvertibleFunds($fund_code, $rows, $offset);
        $res = $data['res'];
        $num = $data['arrkey'];
        if (!is_array($res)) {
            returnJsonStr(['code' => 201, 'msg' => '服务器出错，请重试']);
        }
        if (empty($res)) {
            returnJsonStr(['code' => 202, 'msg' => '没有更多数据']);
        }
        $html = '';
        foreach ($res as $item){
            $fund_name = mb_strlen($item['fund_name']) > 8 ? mb_substr($item['fund_name'], 0, 8) . '...' : $item['fund_name'];
            $day_class = $item['day_ratio'] > 0 ? 'tdRed' : 'tdGreen';
            $three_class = $item['three_month_ratio'] > 0 ? 'tdRed' : 'tdGreen';
            $six_class = $item['six_month_ratio'] > 0 ? 'tdRed' : 'tdGreen';
            $html .= '<tr>';
            $html .= '<td><span class="uRadio"><i><input type="radio" name="target_fund" value="' . $item['fund_code'] . '" min="' . $item['min_value'] . '" max="' . $item['max_value'] . '" onclick="getLimit(this)" autocomplete="off"></i></span></td>';
            $html .= '<td>' . $item['fund_code'] . '</td>';
            $html .= '<td><a href="' . WEB_URL . '/fund/' . $item['fund_code'] . '" target="_blank" class="blue" title="' . $item['fund_name'] . '">' . $fund_name . '</a></td>';
            $html .= '<td>' . $item['net_value'] . '</td>';
            $html .= '<td class="' . $day_class . '">' . $item['day_ratio'] . '</td>';
            $html .= '<td>' . date('Y-m-d', strtotime($item['hq_date'])) . '</td>';
            $html .= '<td class="' . $three_class . '">' . $item['three_month_ratio'] . '</td>';
            $html .= '<td class="' . $six_class . '">' . $item['six_month_ratio'] . '</td>';
            $html .= '</tr> ';
        }
        returnJsonStr(['code' => 200, 'data' => $html, 'num' => $num]);
    }

    public function getLimit()
    {
        $fund_code = filter_slashes($this->input->get('code', true));
        $res = $this->convert_mdl->tradeLimitQry($fund_code);
        t($res);
    }

    private function _formatConvert($data)
    {
        if (empty($data)) return [];
        $this->load->library('buyfunds_mem', 'memcache');
        $funds = $this->buyfunds_mem->get('funds');
        if (empty($funds)) {
            $this->load->model('_cron/cron_mdl');
            $funds = $this->cron_mdl->sortFunds()['funds'];
        }
        $return = [];
        foreach ($data as $key => $val) {
            if ($val['current_share'] > 0) {
                $return[$key]['fund_code'] = $val['fund_code'];
                $return[$key]['fund_name'] = $funds[$val['fund_code']]['fund_name'];
                $bank = $this->convert_mdl->getAccoInfo($val['trade_acco']);
                $return[$key]['bank_name'] = $this->convert_mdl->datadictionary['1601'][$bank['bank_no']];
                $return[$key]['bank_account'] = substr($bank['bank_account'], -4);
                $return[$key]['bank_no'] = $bank['bank_no'];
                $return[$key]['current_share'] = $val['current_share'];
                $return[$key]['enable_shares'] = $val['enable_shares'];
                $return[$key]['worth_value'] = $val['worth_value'];
                $return[$key]['net_value'] = $funds[$val['fund_code']]['net_value'];
                $return[$key]['nav_date'] = date('Y-m-d', strtotime($val['nav_date']));
            }
        }
        return $return;
    }
}