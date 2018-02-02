<?php
/****************************************************************
 * 定时任务模型
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * @author:jiangxuefeng  @addtime:2017/02/06
 ****************************************************************/
class Cron_mdl extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->access_token = getAccessToken(__METHOD__);
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
        $letter2funds = $word2funds = $num2funds = $ta_no2funds = $funds = $funds_raw = [];
        $dictionary = config_item('datadictionary');
        $this->load->library('buyfunds_mem', 'memcache');
        $funds_allow_fix = $this->buyfunds_mem->get('funds_allow_fix');
        $funds_divi = $this->buyfunds_mem->get('funds_divi');
        if (empty($funds_allow_fix) || empty($funds_divi)) {
            $all_fix_divi = $this->allFixDivi();
            $funds_divi = $all_fix_divi['funds_divi'];
            $funds_allow_fix = $all_fix_divi['funds_allow_fix'];
        }
        $i = 1;
        while ($res = $this->_newhqQry(50, 50 * ($i - 1) + 1)) {
            foreach ($res as $item) {
                $letters = chi2Caps($item['fund_name']);
                $numbers = str_split($item['fund_code']);
                $letter2funds[$letters[0]][] = ['code' => $item['fund_code'], 'name' => $item['fund_name']];
                $ta_no2funds[$item['ta_no']][] = $item['fund_code'];
                foreach ($letters as $key => $letter) {
                    $word2funds[$key][$letter][] = $item['fund_code'];
                }
                foreach ($numbers as $key => $number) {
                    $num2funds[$key][$number][] = $item['fund_code'];
                }
                $limit = $this->_tradeLimitQry($item['fund_code'], $item['share_type']);
                $funds[$item['fund_code']] = [
                    'fund_code' => $item['fund_code'],
                    'fund_name' => $item['fund_name'],
                    'fund_full_name' => $item['fund_full_name'],
                    'share_type' => $dictionary['769023'][$item['share_type']],
                    'net_value' => $item['nav'],
                    'hq_date' => $item['hq_date'],
                    'fund_status' => $dictionary['2015'][$item['fund_status']],
                    'ofund_risklevel' => $dictionary['2029'][$item['ofund_risklevel']],
                    'ofund_type' => $dictionary['2022'][$item['ofund_type']],
                    'auto_buy' => isset($funds_divi[$item['fund_code']]) ? $dictionary['2010'][$funds_divi[$item['fund_code']]] : '--',
                    'ta_no' => $item['ta_no'],
                    'min_value' => empty($limit['min_value']) ? 0 : $limit['min_value'],
                    'second_min' => empty($limit['second_min']) ? 0 : $limit['second_min'],
                    'max_value' => empty($limit['max_value']) ? 999999999 : $limit['max_value'],
                    'allow_fix' => $funds_allow_fix[$item['fund_code']],
                    'min_share' => $item['en_minshare']
                ];
                $funds_raw[$item['fund_code']] = [
                    'fund_code' => $item['fund_code'],
                    'fund_name' => $item['fund_name'],
                    'fund_full_name' => $item['fund_full_name'],
                    'share_type' => $item['share_type'],
                    'net_value' => $item['nav'],
                    'hq_date' => $item['hq_date'],
                    'fund_status' => $item['fund_status'],
                    'ofund_risklevel' => $item['ofund_risklevel'],
                    'ofund_type' => $item['ofund_type'],
                    'auto_buy' => isset($funds_divi[$item['fund_code']]) ? $funds_divi[$item['fund_code']] : '',
                    'ta_no' => $item['ta_no'],
                    'min_value' => empty($limit['min_value']) ? 0 : $limit['min_value'],
                    'second_min' => empty($limit['second_min']) ? 0 : $limit['second_min'],
                    'max_value' => empty($limit['max_value']) ? 999999999 : $limit['max_value'],
                    'allow_fix' => $funds_allow_fix[$item['fund_code']],
                    'min_share' => $item['en_minshare']
                ];
            }
            $i++;
        }
        $this->buyfunds_mem->set('letter2funds', $letter2funds, 0);
        $this->buyfunds_mem->set('word2funds', $word2funds, 0);
        $this->buyfunds_mem->set('num2funds', $num2funds, 0);
        $this->buyfunds_mem->set('ta_no2funds', $ta_no2funds, 0);
        $this->buyfunds_mem->set('funds', $funds, 0);
        $this->buyfunds_mem->set('funds_raw', $funds_raw, 0);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('funds:'.count($funds).'; funds_raw:'.count($funds_raw).'; letter2funds:'.count($letter2funds).'; word2funds:'.count($word2funds).'; ta_no2funds:'.count($ta_no2funds).'; num2funds:'.count($num2funds).';',$logFile);
        return [
            'funds' => $funds,
            'letter2funds' => $letter2funds,
            'word2funds' => $word2funds,
            'num2funds' => $num2funds,
            'ta_no2funds' => $ta_no2funds,
            'funds_raw' => $funds_raw
        ];
    }

    /**
     * 获取所有基金分红方式及是否允许定投
     * @return array
     */
    public function allFixDivi()
    {
        $funds_allow_fix = $funds_divi = [];
        $this->load->library('buyfunds_mem', 'memcache');
        $i = 1;
        while ($res = $this->_fundinfoQry(50, 50 * ($i - 1) + 1)) {
            foreach ($res as $item) {
                $funds_allow_fix[$item['fund_code']] = $item['allow_fix'] === '0' ? 0 : 1;
                $funds_divi[$item['fund_code']] = $item['auto_buy'];
            }
            $i++;
        }
        $this->buyfunds_mem->set('funds_allow_fix', $funds_allow_fix, 0);
        $this->buyfunds_mem->set('funds_divi', $funds_divi, 0);
        return ['funds_allow_fix' => $funds_allow_fix, 'funds_divi' => $funds_divi];
    }

    /**
     * 最新行情查询
     * @param $rows
     * @param $offset
     * @return array
     */
    private function _newhqQry($rows, $offset)
    {
        $url = STI_CWSALE . 'newhq_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "access_token" => $this->access_token,
            "request_num" => $rows,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => $offset,
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if (!$data['data'][0]['rowcount']) return [];
        return $data['data'];
    }

    /**
     * 交易限制查询，得到首次最低、追加最低、最高
     * @param $fund_code
     * @param $share_type
     * @return string
     */
    private function _tradeLimitQry($fund_code, $share_type)
    {
        $url = STI_CWSALE . 'trade_limit_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            'access_token' => $this->access_token,
//            'capital_mode' => CAPITAL_MODE,
            'request_num' => 1,
            'reqry_recordsum_flag' => 1,
            'qry_beginrownum' => 1,
            'fund_code' => $fund_code,
//            'share_type' => $share_type,
            'cust_type' => CUST_TYPE,
            'fund_busin_code' => '039'
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        return $data['data'][0];
    }

    /**
     * 基金基本信息查询，得到是否支持定投
     * @param $fund_code
     * @return string
     */
    private function _fundinfoQry($rows, $offset)
    {
        $url = STI_CWSALE . 'fundinfo_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            'access_token' => $this->access_token,
            "request_num" => $rows,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => $offset,
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if (!$data['data'][0]['total_count']) return [];
        return $data['data'];
    }
}