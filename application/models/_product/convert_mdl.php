<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 基金转换模型
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * @author:jiangxuefeng  @addtime:2016/12/29
 ****************************************************************/
class Convert_mdl extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->access_token = getAccessToken(__METHOD__);
        $this->datadictionary = config_item('datadictionary');
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
     * 可转换基金
     * @param $reg_inst_code
     * @param $fund_code
     * @param int $offset
     * @param int $rows
     * @return null
     */
    public function getConvertibleFunds($fund_code, $rows, $offset)
    {
        $res = [];
        $this->load->library('buyfunds_mem', 'memcache');
        $funds_raw = $this->buyfunds_mem->get('funds_raw');
        $ta_no2funds = $this->buyfunds_mem->get('ta_no2funds');
        if (empty($funds_raw) || empty($ta_no2funds)) {
            $this->load->model('_cron/cron_mdl');
            $cron = $this->cron_mdl->sortFunds();
            $funds_raw = $cron['funds_raw'];
            $ta_no2funds = $cron['ta_no2funds'];
        }
        if (!isset($funds_raw[$fund_code])) return [];
        $ta_no = $funds_raw[$fund_code]['ta_no'];
        $codes = $ta_no2funds[$ta_no];
        $count = $arrkey = 0;
        if ($codes && count($codes) > $offset && $codes = array_slice($codes, $offset, null, true)) {
            foreach ($codes as $key => $code){
                if ($code == $fund_code || $funds_raw[$fund_code]['share_type'] != $funds_raw[$code]['share_type'] || $funds_raw[$code]['fund_status'] != 0) continue;
                $limit = $this->tradeLimitQry($code);
                if ( $limit['error_code'] == 1000) continue;
                $count ++;
                $income = $this->incomeQry($code);
                $res[$code]['fund_code'] = $funds_raw[$code]['fund_code'];
                $res[$code]['hq_date'] = $funds_raw[$code]['hq_date'];
                $res[$code]['min_value'] = $limit['min_value'];
                $res[$code]['max_value'] = $limit['max_value'];
                if ($funds_raw[$code]['ofund_type'] != 2) {
                    $res[$code]['net_value'] = $funds_raw[$code]['net_value'];
                    $res[$code]['day_ratio'] = $income['rzzl'];
                    $res[$code]['three_month_ratio'] = $income['j3y'];
                    $res[$code]['six_month_ratio'] = $income['j6y'];
                } else {
                    $res[$code]['net_value'] = $income['7rnh'];
                    $res[$code]['day_ratio'] = $income['wfsy'];
                    $res[$code]['three_month_ratio'] = '--';
                    $res[$code]['six_month_ratio'] = '--';
                }
                $res[$code]['fund_name'] = $funds_raw[$code]['fund_name'];
                $arrkey = $key;
                if ($count == $rows) break;
            }
        }
        return ['res' => $res, 'arrkey' => $arrkey, 'min_share' => $funds_raw[$fund_code]['min_share']];
    }

    /**
     * 基金转入的份额限制
     * @param $fund_code
     * @return mixed
     */
    public function tradeLimitQry($fund_code)
    {
        $url = STI_CWSALE . 'trade_limit_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            'access_token' => $this->access_token,
            'request_num' => 1,
            'reqry_recordsum_flag' => 1,
            'qry_beginrownum' => 1,
            'fund_code' => $fund_code,
            'cust_type' => CUST_TYPE,
            'fund_busin_code' => '036',
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        return $data['data'][0];
    }

    /**
     * 基金转换
     * @param $param
     * @return string
     */
    public function convert($param)
    {
        $url = STI_CWSALE . 'convert_trade';
        $data = [
            'targetcomp_id'=>TARGETCOMP_ID,
            'sendercomp_id'=>SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            'trade_source' => SOURCE_TRADE
        ];
        $data = array_merge($data, $param);
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'],true);
        return $data['data'][0]/*['success_type'] == 0 ? '' : $data['data'][0]['error_info']*/;
    }

    /**
     * 根据交易账号获取银行卡信息
     * @param $trade_acco
     * @return array
     */
    public function getAccoInfo($trade_acco)
    {
        $url = STI_CWSALE . 'accobank_qry';
        $data = [
            'targetcomp_id'=>TARGETCOMP_ID,
            'sendercomp_id'=>SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            "request_num" => 1,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => 1,
            "trade_acco" => $trade_acco,
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if(!$data['data'][0]['rowcount']) return [];
        return $data['data'][0];
    }

    /**
     * 根据客户编号及银行代码获取银行卡信息
     * @param $client_id
     * @param $bank_no
     * @return array
     */
    public function getTradeAcco($client_id, $bank_no)
    {
        $url = STI_CWSALE . 'accobank_qry';
        $data = [
            'targetcomp_id'=>TARGETCOMP_ID,
            'sendercomp_id'=>SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            "request_num" => 1,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => 1,
            "client_id" => $client_id,
            "bank_no" => $bank_no,
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if(!$data['data'][0]['rowcount']) return [];
        return $data['data'][0];
    }

    /**
     * 查询下一个工作日
     * @param $day
     * @return null
     */
    public function nextDayQry($day)
    {
        $url = STI_CWSALE . 'next_day_qry';
        $data = [
            'targetcomp_id'=>TARGETCOMP_ID,
            'sendercomp_id'=>SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            "request_num" => 1,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => 1,
            "day" => $day,
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'],true);
        if(!$data['data'][0]['rowcount']) return null;
        return $data['data'][0]['next_day'];
    }

    public function incomeQry($fund_code)
    {
        $url = WEB_URL . '/financemarket/fund/baseinfo/' . $fund_code;
        $result = curl_post($url, []);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'. $url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'],true);
        return $data;
    }
}