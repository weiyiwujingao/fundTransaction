<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/****************************************************************
 * 注释
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * @author:jiangxuefeng  @addtime:2017/01/16
 ****************************************************************/
class Transaction_mdl extends MY_Model
{
    const TBL_Email = 'tbPassportEmail';

    public function __construct()
    {
        parent::__construct();
        $this->access_token = getAccessToken(__METHOD__);
        $this->datadictionary = config_item('datadictionary');
    }

    /**
     * 给定时间段内的份额明细
     * @param $client_id
     * @param $begin_date
     * @param $end_date
     * @return array
     */
    public function shareQry($client_id, $rows, $offset)
    {
        $url = STI_CWSALE . 'share_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            "request_num" => $rows,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => $offset,
            "client_id" => $client_id,
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if (!$data['data'][0]['rowcount']) return [];
        return $data['data'];
    }

    /**
     * 根据基金代码获取基金信息
     * @param $fund_code
     * @return array
     */
    public function fundinfoQry($fund_code)
    {
        $url = STI_CWSALE . 'fundinfo_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            "request_num" => 1,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => 1,
            "fund_code" => "$fund_code",
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if (!$data['data'][0]['rowcount']) return [];
        return $data['data'][0];
    }

    /**
     * 根据交易账号查询银行账号
     * @param $trade_acco
     * @return array
     */
    public function accobankQry($trade_acco)
    {
        $url = STI_CWSALE . 'accobank_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
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
        if (!$data['data'][0]['rowcount']) return [];
        return $data['data'][0];
    }

    /**
     * 交易确认查询
     * @param $client_id
     * @param $begin_date
     * @param $end_date
     * @return array
     */
    public function tradeConfirmQry($client_id, $begin_date, $end_date, $rows, $offset)
    {
        $url = STI_CWSALE . 'trade_confirm_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            "request_num" => $rows,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => $offset,
            "client_id" => $client_id,
            "start_date" => $begin_date,
            "end_date" => $end_date
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if (!$data['data'][0]['rowcount']) return [];
        return $data['data'];
    }

    /**
     * 分红明细查询
     * @param $client_id
     * @param $begin_date
     * @param $end_date
     * @return array
     */
    public function diviQry($client_id, $begin_date, $end_date, $rows, $offset)
    {
        $url = STI_CWSALE . 'divi_qry';
        $data = [
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            "request_num" => $rows,
            "reqry_recordsum_flag" => 1,
            "qry_beginrownum" => $offset,
            "client_id" => $client_id,
            "confirmdate_begin" => $begin_date,
            "confirmdate_end" => $end_date
        ];
        $result = curl_post($url, $data);
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        logs('参数：'.print_r($data,true).$url.PHP_EOL.'回参：'.print_r($result,true),$logFile);
        $data = json_decode($result['data'], true);
        if (!$data['data'][0]['rowcount']) return [];
        return $data['data'];
    }

    /**
     * 获取账户操作记录
     * @param $client_id
     * @param $rows
     * @param $offset
     * @return array
     */
    public function getOperateLog($user_id, $rows, $offset)
    {
        return $this->getPageData('tbPassportOperatLog', "UserID={$user_id}", $rows, $offset, 'OperatDetail,OperatStatus,OperatTime', 'OperatTime DESC');
    }

    /**
     * 获取要求寄送账单的用户信息
     * @return mixed
     */
    public function getBillMailUserInfo($cids = [])
    {
        $owhere = '';
        $sendMethod = serialize([1]);
        if ($cids) $owhere = " AND a.HsUserID IN (" . implode(',', $cids) . ")";
        $sql = "SELECT a.UserID,a.HsUserID,a.NickName,b.Email,b.SendRate FROM tbPassportUser a INNER JOIN tbPassportUserDetail b ON a.UserID=b.UserID
            WHERE a.HsUserID <> 0 AND b.SendRate IN (2,3,4,5) AND b.SendMethod='{$sendMethod}' AND b.Email IS NOT NULL" . $owhere;
        $res = $this->db->query($sql)->result_array();
        $this->db->close();
        return $res;
    }

    /**
     * 插入发送邮件记录
     * @param $user_id
     * @param $accept_email
     * @param $subject
     * @param $status
     * @return bool|int
     */
    public function insertTbEmail($user_id, $send_email, $accept_email, $subject, $status)
    {
        return $this->insertTb(self::TBL_Email, ['UserID' => $user_id, 'SendEmail' => $send_email, 'AcceptEmail' => $accept_email, 'Title' => $subject, 'Status' => $status, 'SendTime' => date('Y-m-d H:i:s'), 'IP' => $this->input->ip_address()]);
    }
}