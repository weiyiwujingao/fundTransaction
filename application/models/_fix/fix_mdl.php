<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/****************************************************************
 * 基金定投模型
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * @author:jiangxuefeng  @addtime:2016/12/29
 ****************************************************************/
class Fix_mdl extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->access_token = getAccessToken(__METHOD__);
        $this->datadictionary = config_item('datadictionary');
    }

    /**
     * 根据用户编号获取定投
     * @param $client_id
     * @param int $req_num
     * @return array
     */
    public function userFix($client_id)
    {
        $i = 1;
        $data = [];
        while ($res = $this->getFixByCid($client_id, ($i -1) * 50 + 1, 50)) {
            $data = array_merge($data, $res);
            $i ++;
        }
        return $data;
    }

    /**
     * 修改定投协议
     * @param $sch_protocol_id 协议号
     * @param $state 正常协议修改填A；终止协议填H；暂停协议填P；
     * @param $password 加密后的
     * @param array $param
     * @param string $fund_busin_code 申购变更090，赎回变更091
     */
    public function fixmodifyTrade($sch_protocol_id, $state, $password, $param = [], $fund_busin_code = "090")
    {
        $info = $this->getFixBySPid($sch_protocol_id);
        if (empty($info)) return '定投协议号不正确';
        $url = STI_CWSALE . 'fixmodify_trade';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "capital_mode" => CAPITAL_MODE,
            'trade_source' => SOURCE_TRADE,
            "access_token" => $this->access_token,
            "trade_acco" => "{$info['trade_acco']}",
            "password" => $password,
            "scheduled_protocol_id" => "{$sch_protocol_id}",
            "expiry_date" => "{$info['expiry_date']}",
            "trade_period" => "{$info['trade_period']}",
            "protocol_fix_day" => "{$info['protocol_fix_day']}",
            "fund_code" => "{$info['fund_code']}",
            "share_type" => "{$info['share_type']}",
            "balance" => "{$info['balance']}",
            "shares" => "{$info['shares']}",
            "fund_exceed_flag" => "{$info['fund_exceed_flag']}",
            "protocol_period_unit" => "{$info['protocol_period_unit']}",
            "fund_busin_code" => $fund_busin_code,
            "fix_state" => $state,
        ];
        $data = array_merge($data, $param);
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if ($data['data'][0]['success_type'] > 0) return ['code' => 0, 'msg' => $data['data'][0]['error_info'], 'allot_no' => $data['data'][0]['allot_no']];
        return ['code' => 200, 'msg' => $data['data'][0]['next_fixrequest_date'], 'allot_no' => $data['data'][0]['allot_no']];
    }

    /**
     * 根据用户编号获取被终止的定投记录
     * @param $client_id
     * @return array
     */
    public function userStopFix($client_id)
    {
        $data = [];
        $i = 1;
        while ($res = $this->getFixByCid($client_id, ($i - 1) * 50 + 1, 50)) {
            foreach ($res as $item) {
                if ($item['fix_state'] == 'H') {
                    $data[] = $item;
                }
            }
            $i++;
        }
        return $data;
    }

    /**
     * 定投申购
     * @param $param
     * @return array
     */
    public function fixallotTrade($param)
    {
        $url = STI_CWSALE . 'fixallot_trade';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            "money_type" => CURRENCY,
            "capital_mode" => CAPITAL_MODE,
            'detail_fund_way' => DETAIL_FUND_WAY,
            'trade_source' => SOURCE_TRADE
        ];
        $data = array_merge($data, $param);
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        return $data['data'][0]/*['success_type'] == 0 ? '' : $data['data'][0]['error_info']*/;
    }

    public function resetTradePwd()
    {
        $url = STI_CWSALE . 'tradepassword_clear_acct';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            'trade_acco' => '0001',
            'new_password' => tradePswd('bj123456')
        ];
        $result = curl_post($url, $data);
        $data = json_decode($result['data'], true);
//        t($result);
        return $data['data'][0]['success_type'] == 0 ? '' : $data['data'][0]['error_info'];
    }

    /**
     * 获取银行卡信息
     * @param $param
     * @param int $offset
     * @param int $rows
     * @param int $flag
     * @return array
     */
    public function getAccoInfo($param, $offset = 1, $rows = 50, $flag = 1)
    {
        $url = STI_CWSALE . 'accobank_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            "request_num" => $rows,
            "reqry_recordsum_flag" => $flag,
            "qry_beginrownum" => $offset,
        ];
        $data = array_merge($data, $param);
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if (!$data['data'][0]['rowcount']) return [];
        return $data['data'];
    }

    /**
     * 根据用户编号获取定投（原始数据）
     * @param $client_id
     * @param $offset
     * @param $rows
     * @param int $flag 是否重新统计记录行数
     * @return array
     */
    public function getFixByCid($client_id, $offset, $rows)
    {
        $url = STI_CWSALE . 'fix_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "access_token" => $this->access_token,
            "trust_way" => TRANSACTION_MODE,
            "request_num" => $rows,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => "$offset",
            "client_id" => $client_id,
            'sort_direction' => '1'
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if (!isset($data['data'][0]['total_count']) || !$data['data'][0]['total_count']) return [];
        return $data['data'];
    }

    /**
     * 根据协议号获取定投
     * @param $protocol_id
     * @return array
     */
    public function getFixBySPid($sch_protocol_id)
    {
        $url = STI_CWSALE . 'fix_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "access_token" => $this->access_token,
            "trust_way" => TRANSACTION_MODE,
            "request_num" => 1,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => 1,
            "scheduled_protocol_id" => $sch_protocol_id,
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if (!$data['data'][0]['total_count']) return [];
        return $data['data'][0];
    }

    /**
     * 根据定投协议号获取确认状态
     * @param $client_id
     * @param $scheduled_protocol_id
     * @return array
     */
    public function tradeConfirmQry($client_id, $scheduled_protocol_id)
    {
        $url = STI_CWSALE . 'trade_confirm_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "access_token" => $this->access_token,
            "trust_way" => TRANSACTION_MODE,
            "request_num" => 1,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => 1,
            "scheduled_protocol_id" => $scheduled_protocol_id,
            "client_id" => $client_id,
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        return $data['data'][0];
    }

    /**
     * 交易申请查询
     * @param $client_id
     * @return mixed
     */
    public function tradeApplyQry($client_id, $offset = 1, $rows = 1)
    {
        $url = STI_CWSALE . 'trade_apply_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "access_token" => $this->access_token,
            "trust_way" => TRANSACTION_MODE,
            "request_num" => $rows,
            "reqry_recordsum_flag" => 1,
            'protocol_traffic_flag' => 1,
            "qry_beginrownum" => $offset,
            "client_id" => $client_id,
            'sort_direction' => 1
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if (!isset($data['data'][0]['total_count']) || !$data['data'][0]['total_count']) return [];
        return $data['data'];
    }

    /**
     * 根据关键字（代码、首字母、汉字）检索基金
     * @param $key
     * @param $rows
     * @return array
     */
    public function searchFunds($key, $rows)
    {
        $res = [];
        $this->load->library('buyfunds_mem', 'memcache');
        $funds = $this->buyfunds_mem->get('funds');
        $num2funds = $this->buyfunds_mem->get('num2funds');
        $word2funds = $this->buyfunds_mem->get('word2funds');
        if (empty($funds) || empty($num2funds) || empty($word2funds)) {
            $this->load->model('_cron/cron_mdl');
            $cron = $this->cron_mdl->sortFunds();
            $funds = $cron['funds'];
            $num2funds = $cron['num2funds'];
            $word2funds = $cron['word2funds'];
        }
        $first = mb_substr($key, 0, 1);
        if (preg_match('/[0-9]/', $first)) {
            $arr = str_split($key);
            $tmp = isset($num2funds[0][$arr[0]]) ? $num2funds[0][$arr[0]] : [];
            foreach ($arr as $key => $value) {
                $t[$key] = isset($num2funds[$key][$value]) ? $num2funds[$key][$value] : [];
                $tmp = array_intersect($tmp, $t[$key]);
            }
        } elseif (preg_match('/[A-Za-z]/', $first)) {
            $arr = str_split(strtoupper($key));
            $tmp = isset($word2funds[0][$arr[0]]) ? $word2funds[0][$arr[0]] : [];
            foreach ($arr as $key => $value) {
                $t[$key] = isset($word2funds[$key][$value]) ? $word2funds[$key][$value] : [];
                $tmp = array_intersect($tmp, $t[$key]);
            }
        } else {
            $arr = str_split(chi2Caps($key));
            $tmp = isset($word2funds[0][$arr[0]]) ? $word2funds[0][$arr[0]] : [];
            foreach ($arr as $key => $value) {
                $t[$key] = isset($word2funds[$key][$value]) ? $word2funds[$key][$value] : [];
                $tmp = array_intersect($tmp, $t[$key]);
            }
        }
        if (empty($tmp)) return $res;
        $i = 1;
        foreach ($tmp as $item) {
            $res[$item] = $funds[$item];
            if ($i > $rows) break;
            $i++;
        }
        return $res;
    }

    /**
     * 根据字母检索基金
     * @param $key
     * @return mixed
     */
    public function searchCodesByLetter($key)
    {
        $res = [];
        $this->load->library('buyfunds_mem', 'memcache');
        $letter2funds = $this->buyfunds_mem->get('letter2funds');
        if (empty($letter2funds)) {
            $this->load->model('_cron/cron_mdl');
            $letter2funds = $this->cron_mdl->sortFunds()['letter2funds'];
        }
        if (isset($letter2funds[$key])) $res = $letter2funds[$key];
        return $res;
    }

    /**
     * 交易手续费查询
     * @param $fund_code
     * @param $balance
     * @param $trade_acco
     * @return array
     */
    public function calcFeeTrade($trade_acco, $fund_code, $balance, $client_id)
    {
        $hq = $this->newhqQry($fund_code);
        $url = STI_CWSALE . 'calc_fee_trade';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "access_token" => $this->access_token,
            "trust_way" => TRANSACTION_MODE,
            "cust_type" => CUST_TYPE,
            "capital_mode" => CAPITAL_MODE,
            "request_num" => 1,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => 1,
            "trade_acco" => "$trade_acco",
            "fund_code" => "$fund_code",
            "balance" => $balance,
            "client_id" => $client_id,
            "share_type" => $hq['share_type'],
            "fund_busin_code" => '022',
            "ta_no" => $hq['ta_no']
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        return $data['data'][0];
    }

    /**
     * 返回下一个工作日
     * @param $day
     */
    public function nextDayQry($day)
    {
        $url = STI_CWSALE . 'next_day_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "access_token" => $this->access_token,
            "request_num" => 1,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => 1,
            "day" => $day,
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        return $data['data'][0]['next_day'];
    }

    /**
     * 获取基金信息
     * @param $fund_code
     * @return array
     */
    public function newhqQry($fund_code)
    {
        $url = STI_CWSALE . 'newhq_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "access_token" => $this->access_token,
            "trust_way" => TRANSACTION_MODE,
            "request_num" => 1,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => 1,
            "fund_code" => "$fund_code"
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if (!isset($data['data'][0]['total_count']) || !$data['data'][0]['total_count']) return [];
        return $data['data'][0];
    }

    /**
     * 查询用户基金份额（$client_id, $trade_acco必须传一个）
     * @param $client_id
     * @param $trade_acco
     * @param int $offset
     * @param int $rows
     * @param int $flag
     * @return array
     */
    public function shareQry($param = [], $offset, $rows)
    {
        if (empty($param) || !is_array($param)) return [];
        $url = STI_CWSALE . 'share_qry';
        $data = [
            'targetcomp_id'=>TARGETCOMP_ID,
            'sendercomp_id'=>SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            "request_num" => $rows,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => $offset,
        ];
        $data = array_merge($data, $param);
        if (!isset($data['client_id']) && !isset($data['trade_acco'])) return [];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'],true);
        if(!$data['data'][0]['rowcount']) return [];
        return $data['data'];
    }

    /**
     * 获取最近10条定投终止记录
     * @return array
     */
    public function getStopFix($client_id)
    {
        $sql = "SELECT
              a.* 
            FROM
              tbPassportManagement a 
            LEFT JOIN tbPassportUser b ON a.UserID=b.UserID
            WHERE 
              a.Type=4 AND a.Modeway=4 AND a.Status=1 AND b.HsUserID={$client_id} AND a.Scheduled_protocol_id IS NOT NULL
            AND
              a.Scheduled_protocol_id NOT IN (
                SELECT a.Scheduled_protocol_id FROM tbPassportManagement a LEFT JOIN tbPassportUser b ON a.UserID=b.UserID WHERE a.Type=4 AND a.Modeway=6 AND a.Status=1 AND b.HsUserID={$client_id}
              )
            ORDER BY a.SubDate DESC LIMIT 10";
        return $this->db->query($sql)->result_array();
    }

}