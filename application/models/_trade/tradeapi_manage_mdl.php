<?php if(!defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 沪深即时行情模型 v2.0
 *---------------------------------------------------------------
 * Copyright (c) 2004-2015 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2016-12-14
 ****************************************************************/
class Tradeapi_manage_mdl extends MY_Model 
{
    public $logfile = 'indexlog';
	
	public function __construct()
	{
        parent::__construct();
		$this->access_token = getAccessToken(__METHOD__);
		$this->datadictionary = config_item('datadictionary');	
		$this->businessdictionary = config_item('businessdictionary');	
    }

   /**
     * 最新基金行情查询  功能号 newhq_qry
	 *
     * @param mixed   $search   基金代码
     * @param integer $type     回复字段范围,参看文档
	 * @param integer $userid   用户ID
	 * @param string  $flag      需要返回的数据字段名称
     * @return array
     */
    public function fundType($search,$flag='') 
    {	
		$data = $fundcodemsg = array();
		//最新基金行情查询
		$url = STI_CWSALE.'newhq_qry';
		$arr = array(
					'targetcomp_id'=>TARGETCOMP_ID,
					'sendercomp_id'=>SENDERCOMP_ID,
					'access_token'=>$this->access_token,
					'request_num'=>'1',
					'reqry_recordsum_flag'=>'1',
					'qry_beginrownum'=>'0',
					'fund_code'=>$search,
				);
		$result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
		$data = json_decode($result['data'],true);
		//return $result;
		//判断是否存在该基金代码
		if(!isset($data['data']['0']['total_count']) || !$data['data']['0']['total_count']) return '';
		$data = $data['data']['0'];
		//返回指定基金行情信息
		if($flag){
			$flagdata='';
			foreach($data as $k=>$v){
				if($k==$flag)
					$flagdata = $v;
			}
			return $flagdata;
		}
			
		
		$fund_code = $data['fund_code'];
		$fundcodemsg[$fund_code]['name'] = $data['fund_full_name'];
		$fundcodemsg[$fund_code]['sname'] = $data['fund_name'];
		$fundcodemsg[$fund_code]['rank'] = $this->datadictionary['2029'][$data['ofund_risklevel']];
		$fundcodemsg[$fund_code]['code'] = $fund_code;
		$fundcodemsg[$fund_code]['type'] = $this->datadictionary['2022'][$data['ofund_type']];
		$fundcodemsg[$fund_code]['chargemode'] = $this->datadictionary['769023'][$data['share_type']];
		$fundcodemsg[$fund_code]['share_type'] = $data['share_type'];
		$fundcodemsg[$fund_code]['ofund_risklevel'] = $data['ofund_risklevel'];
		
		unset($data);
        return $fundcodemsg;
    }
	 /**
     * 所有最新基金行情  功能号 newhq_qry
	 *
     * @return array
     */
	public function getnewhq_qry()
	{
		//t($this->logfile);
		//最新基金行情查询
		$url = STI_CWSALE.'newhq_qry';
		$limit = 20;$page = 1;		
		$arr = array(
					'targetcomp_id'=>TARGETCOMP_ID,
					'sendercomp_id'=>SENDERCOMP_ID,
					'access_token'=>$this->access_token,
					'request_num'=>$limit,
					'reqry_recordsum_flag'=>'1',
					'qry_beginrownum'=>'0',
				);
		$result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
		$data = json_decode($result['data'],true);
		$list = $data['data'];
		if(!isset($data['data'][0]['total_count']) || !$data['data'][0]['total_count']) return '';
		$num = count($list);
		while($num<$data['data'][0]['total_count']){			 
			$arr['qry_beginrownum'] = $page*$limit;
			$result = curl_post($url,$arr);
			$data = json_decode($result['data'],true);
			$list = array_merge($list,$data['data']);
			$num = count($list); $page += 1;
		}	
		return $list;
	}
	/**
     * 最新基金分红查询  功能号 share_qry
	 *
     * @param string   $search 基金代码
     * @return string
     */
    public function fundShare($search,$client_id='') 
    {	
		$data = $fundcodemsg = '';
		// 份额查询 获取分红方式
		$url = STI_CWSALE.'share_qry';
		$arr = array(
					'targetcomp_id'=>TARGETCOMP_ID,
					'sendercomp_id'=>SENDERCOMP_ID,
					'access_token'=>$this->access_token,
					'request_num'=>'1',
					'reqry_recordsum_flag'=>'1',
					'qry_beginrownum'=>'0',
					'fund_code'=>$search,
					'client_id'=>$client_id
				);
		$result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
		$data = json_decode($result['data'],true);
		//return $data;
		//判断是否存在该基金代码
		if(!$data['data']['0']['total_count'] || $data['data']['0']['success_type']!='0') return '';
		$data = $data['data']['0'];
		//return $data;
		$fundcodemsg['share_type'] = $this->datadictionary['2010'][$data['auto_buy']];//分红方式
        $fundcodemsg['share_type_code'] = $data['auto_buy'];//分红方式
		$fundcodemsg['c_flag'] = $data['c_flag'];//是否拥有份额
		//returnJson($data);
		unset($data);
		return $fundcodemsg;
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
    public function shareQry($param = [], $rows = 1, $offset = 0, $flag = 1, $return='')
    {
       // if (empty($param) || !is_array($param)) return [];
        $url = STI_CWSALE . 'share_qry';
        $arr = [
            'targetcomp_id'=>TARGETCOMP_ID,
            'sendercomp_id'=>SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            "request_num" => $rows,
            "reqry_recordsum_flag" => $flag,
            "qry_beginrownum" => $offset,
        ];
		//t($data);
        $arr = array_merge($arr, $param);
        if (!isset($arr['client_id']) && !isset($arr['trade_acco'])) return [];
        $result = curl_post($url, $arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
		if($result['code']!='200') return array();
        $data = json_decode($result['data'],true);
        if(!isset($data['data']['0']['rowcount']) || !$data['data']['0']['rowcount'] ) return [];
        return $data['data'];
    }
    /**
     * 基金份额查询  功能号 share_qry
     *
     * @param string   $search 基金代码
     * @return string
     */
    public function fundShareQry($search='',$trade_acco='',$request_num=1,$client_id='',$qry_beginrownum='1') 
    {   
        $data = $fundcodemsg = '';
        // 份额查询
        $url = STI_CWSALE.'share_qry';
        $arrCommon = array(
                        'targetcomp_id'        => TARGETCOMP_ID,
                        'sendercomp_id'        => SENDERCOMP_ID,
                        'access_token'         => $this->access_token,
                        'reqry_recordsum_flag' => '1',
                        'qry_beginrownum'      => $qry_beginrownum,
                    
                    ); 
        $arrDynamic = array(
                         'fund_code'   => $search,//基金代码
                         'trade_acco'  => $trade_acco,//交易编号
                         'client_id'  => $client_id,//客户编号
                         'request_num' => $request_num,
                      );
        $arr = array_merge($arrCommon,$arrDynamic);

        $result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
        if($result['code']!=200)
            return '';
        $data = json_decode($result['data'],true);
        //判断是否存在该基金代码
        if(!$data['data']['0']['total_count']) return '';
		//t($data);
		$data = $this->fundAllotData($data['data']);
        return $data;
    }
	/**
     * 查询用户基金分红明细（$trade_acco必须传一个）
     * @param $trade_acco
     * @param int $offset
     * @param int $rows
     * @param int $flag
     * @return array
     */
    public function diviQry($param = [], $rows = 1, $offset = 0, $flag = 1, $return='')
    {
       // if (empty($param) || !is_array($param)) return [];
        $url = STI_CWSALE . 'divi_qry';
        $arr = [
            'targetcomp_id'=>TARGETCOMP_ID,
            'sendercomp_id'=>SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            "request_num" => $rows,
            "reqry_recordsum_flag" => $flag,
            "qry_beginrownum" => $offset,
        ];
		//t($data);
        $arr = array_merge($arr, $param);
        //if (!isset($param['trade_acco'])) return [];
        $result = curl_post($url, $arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
		if($result['code']!='200') return array();
        $data = json_decode($result['data'],true);
        if(!isset($data['data']['0']['rowcount']) || !$data['data']['0']['rowcount'] ) return [];
		$data = $this->diviqry_data($data['data']);
        return $data;
    }
	/**
     * 查询用户基金分红明细（$trade_acco必须传一个）
     * @param $trade_acco
     * @param int $offset
     * @param int $rows
     * @param int $flag
     * @return array
     */
    public function fundinfo_qry($param = [], $rows = 1, $offset = 0, $flag = 1, $return='')
    {
       // if (empty($param) || !is_array($param)) return [];
        $url = STI_CWSALE . 'fundinfo_qry';
        $arr = [
            'targetcomp_id'=>TARGETCOMP_ID,
            'sendercomp_id'=>SENDERCOMP_ID,
            "trust_way" => TRANSACTION_MODE,
            "access_token" => $this->access_token,
            "request_num" => $rows,
            "reqry_recordsum_flag" => $flag,
            "qry_beginrownum" => $offset,
        ];
		//t($data);
        $arr = array_merge($arr, $param);
        //if (!isset($param['trade_acco'])) return [];
        $result = curl_post($url, $arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
		if($result['code']!='200') return array();
        $data = json_decode($result['data'],true);
        if(!isset($data['data']['0']['rowcount']) || !$data['data']['0']['rowcount'] ) return [];
		$data = $this->diviqry_data($data['data']);
        return $data;
    }
	/**
     * 基金分红设置  功能号 share_qry
     *
     * @param array   $data 基金数据
     * @return array
     */
    public function diviqry_data($data) 
    { 
		if(!is_array($data) && !$data)
			return '';
		$result = array();
		foreach($data as $key=>$val)
		{
			$result[$key]['auto_buy'] = $this->datadictionary['2010'][$val['auto_buy']];//分红方式
			$result[$key]['auto_buys'] = $val['auto_buy'];//分红方式
		}
		return $result;
	}
	/**
     * 交易限制查询  功能号 trade_limit_qry
	 *
     * @param string   $search 基金代码
     * @return string
     */
    public function tradeRestrictions($search,$share_type,$client_id='',$fund_busin_code='') 
    {	
		$data = $fundcodemsg = '';
		// 份额查询 获取分红方式
		$url = STI_CWSALE.'trade_limit_qry';
		$arr = array(
					'targetcomp_id' => TARGETCOMP_ID,
					'sendercomp_id' => SENDERCOMP_ID,
					'access_token'  => $this->access_token,
					'request_num'   => '1',
					'qry_beginrownum'=>'0',
					'reqry_recordsum_flag'=>'1',
					'fund_code'      =>$search,
//					'share_type'=>$share_type,//分红方式
		           //'client_id'=>$client_id,//客户账号
			       //'trust_way'=>TRANSACTION_MODE,//委托方式 
			       //'capital_mode'=>CAPITAL_MODE,//资金方式 
					'cust_type'       =>CUST_TYPE,//客户类别
					'fund_busin_code' => $fund_busin_code
				);
		$result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
		$data = json_decode($result['data'],true);
		//returnJson($data);
		//判断是否存在该基金代码
		if(!$data['data']['0']['total_count']) return '';
		$data = $data['data']['0'];
		
		//$fundcodemsg = $this->datadictionary['2010'][$data['auto_buy']];
		
		//unset($data);
		return $data;
    }
	/**
     * 基金费率查询  功能号 rate_qry
	 *
     * @param string   $search 基金代码
     * @return string
     */
    public function fundShareSearch($search,$fund_busin_code='') 
    {	
		$data = $fundcodemsg = '';
		// 费率查询
		$url = STI_CWSALE.'rate_qry';
		$arr = array(
					'targetcomp_id'=>TARGETCOMP_ID,
					'sendercomp_id'=>SENDERCOMP_ID,
					'access_token'=>$this->access_token,
					'request_num'=>'1',
					'reqry_recordsum_flag'=>'1',
					'qry_beginrownum'=>'0',
					'fund_code'=>$search,
					'fund_busin_code'=>$fund_busin_code,
					'trust_way'=>TRANSACTION_MODE,//委托方式 
				);
		$result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
		$data = json_decode($result['data'],true);
		//判断是否存在该基金代码
		if(!$data['data']['0']['total_count'] || $data['data']['0']['success_type']!='0') return '';
		$data = $data['data']['0'];
		
		$fundcodefare['min_fare'] = $data['min_fare'];//最低费率
		$fundcodefare['max_fare'] = $data['max_fare'];//最高费率
		$fundcodefare['ratio'] = $data['ratio'];//费率比例
		
		unset($data);
		return $fundcodefare;
    }
	/**
     * 基金理财销售数据字典 查询
	 *
     * @param string   $search 要找的配置值
     * @param string   $key    数据范围键值
     * @return string
     */
    public function fundConfigSearch($search,$key) 
    {	
		return array_search($search,$this->datadictionary[$key]);
	}
	/**
     * 基金理财销售数据字典 查询
	 *
     * @param string   $search 要找的配置值
     * @param string   $key    数据范围键值
     * @return string
     */
    public function fundConfigValue($search,$key) 
    {	
		return $this->datadictionary[$key][$search];
	}
    /**
     * 基金申购  功能号 allot_trade
     *
     * @param string   $search 基金代码
     * @return string
     */
    public function fundAllotTrade($bank_no,$client_id,$fundcode='',$balance,$password) 
    {   
        $data  = $fundcodemsg = $param = '';
        $param = $this->fundBankAccountSearch($bank_no,'',$client_id);

        $share_type = $this->fundType($fundcode);
		if(!$share_type) return '';
        $share_type = $share_type[$fundcode]['share_type'];
       // echo $share_type;exit;
        if(!$param || !$share_type) return '';
        // 基金申购
        $url = STI_CWSALE.'allot_trade';
        $arrCommon = array(
                        'targetcomp_id' => TARGETCOMP_ID,
                        'sendercomp_id' => SENDERCOMP_ID,
                        'access_token'  => $this->access_token,
                        'money_type'    => CURRENCY,
                        'trust_way'     => TRANSACTION_MODE,//委托方式 
                        'capital_mode'  => CAPITAL_MODE,//资金方式CAPITAL_MODE
                        'trade_source'  => SOURCE_TRADE,//交易来源
						'detail_fund_way'=>'01',//资金明细方式
                     );
        $arrDynamic = array(
                        'fund_code'     => $fundcode,//基金代码
                        'bank_no'       => $param['bank_no'],//银行代码
                        'bank_account'  => $param['bank_account'],//银行账号
                        'share_type'    => $share_type,//份额分类$share_type['share_type_code']
                        'balance'       => $balance,//发生金额
                        'trade_acco'    => $param['trade_acco'], //交易账号
                        'password'      => $password, //交易密码
                      );
        $arr = array_merge($arrCommon,$arrDynamic);
        //var_dump($arr);//exit;
        $result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
		//t($result);
        //判断是否请求成功
        if($result['code']!='200')
			return '';
        $data = json_decode($result['data'],true);
        $data = $data['data']['0'];
		
        return $data;
    }

//$bank_no,$client_id,$data['fundcode'],$data['paymentAmount'],$data['password']
    /**
     * 基金认购、新基金购买
     * @param $bank_no
     * @param $client_id
     * @param $fundcode
     * @param $paymentAmount
     * @param $password
     * @return mixed|string
     */
    public function subscribe_trade($bank_no, $client_id, $fundcode, $paymentAmount,$password)
    {
        $data  = $fundcodemsg = $param = '';
        $param = $this->fundBankAccountSearch($bank_no,'',$client_id);

        $share_type = $this->fundType($fundcode);
        if(!$share_type) return '';
        $share_type = $share_type[$fundcode]['share_type'];
        // echo $share_type;exit;
        if(!$param || !$share_type) return '';
        // 基金申购
        $url = STI_CWSALE.'subscribe_trade';
        $arrCommon = array(
            'targetcomp_id' => TARGETCOMP_ID,
            'sendercomp_id' => SENDERCOMP_ID,
            'access_token'  => $this->access_token,
            'money_type'    => CURRENCY,
            'trust_way'     => TRANSACTION_MODE,//委托方式
            'capital_mode'  => CAPITAL_MODE,//资金方式CAPITAL_MODE
            'trade_source'  => SOURCE_TRADE,//交易来源
            'detail_fund_way'=>'01',//资金明细方式
        );
        $arrDynamic = array(
            'fund_code'     => $fundcode,//基金代码
            'share_type'    => $share_type,//份额分类$share_type['share_type_code']
            'balance'       => $paymentAmount,//发生金额
            'trade_acco'    => $param['trade_acco'], //交易账号
            'password'      => $password, //交易密码
        );
        $arr = array_merge($arrCommon,$arrDynamic);
        //var_dump($arr);//exit;
        $result = curl_post($url,$arr);
        logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
        //t($result);
        //判断是否请求成功
        if($result['code']!='200')
            return '';
        $data = json_decode($result['data'],true);
        $data = $data['data']['0'];

        return $data;
    }
	/**
     * 用户银行账号信息查询  功能号 accobank_qry
     *
     * @param string   $search 基金代码
     * @return string
     */
    public function fundBankAccountSearch($bank_no='',$trade_acco='',$client_id='',$request_num=1,$reqry_recordsum_flag=1,$qry_beginrownum=0) 
    {   
        $data = $fundcodemsg = '';
        // 
        $url = STI_CWSALE.'accobank_qry';
        $arrCommon = array(
                        'targetcomp_id' => TARGETCOMP_ID,
                        'sendercomp_id' => SENDERCOMP_ID,
                        'access_token'  => $this->access_token,
                        'trust_way'     => TRANSACTION_MODE,//委托方式 
                        //'capital_mode'  => CAPITAL_MODE,//资金方式
                        //'trade_source'  => SOURCE_TRADE,//交易来源
                     );
        $arrDynamic = array(
                        'request_num'          => $request_num,//请求行数
                        'reqry_recordsum_flag' => $reqry_recordsum_flag,//重新统计总记录数标志
                        'qry_beginrownum'      => $qry_beginrownum,//查询起始行号
                        'trade_acco'           => $trade_acco,//交易账号
                        'client_id'            => $client_id,//客户账号
                        'bank_no'              => $bank_no, //银行代码
                      );
        $arr = array_merge($arrCommon,$arrDynamic);
       // pre($arr);
        $result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
        $data = json_decode($result['data'],true);
        //var_dump($result);exit;
        //判断是否存在该基金代码
        if(!isset($data['data']['0']['success_type']) || $data['data']['0']['success_type']!='0') return '';
		if($request_num==1)
			$data = $data['data']['0'];
		else
			$data = $data['data'];

        return $data;
    }
	/**
     * 基金申购数据组装函数  功能号 allot_trade
     *
     * @param array   $data 需要组装的数组  
     * @return string
     */
    public function fundAllotData($data)
	{
		if(!is_array($data) && !$data)
			return '';
		$result = array();
		foreach($data as $key=>$val)
		{
			$result[$key]['fund_code'] = $val['fund_code'];// 基金代码
			$result[$key]['fund_name'] = $this->fundType($val['fund_code'],'fund_name');//基金名称
			$result[$key]['fund_bank'] = $this->datadictionary['1601'][$val['bank_no']];//银行名称
			$result[$key]['bank_acsort'] = substr($val['bank_account'],-4);//银行账号截取
			$result[$key]['share_type'] = $this->datadictionary['769023'][$val['share_type']];//收费方式
			$result[$key]['nav_value'] = $this->fundType($val['fund_code'],'nav');;//单位净值
			$result[$key]['nav_date'] = date('Y.m.d',strtotime($val['nav_date']));//净值日期
			$result[$key]['current_share'] = $val['current_share'];//总份额
			$result[$key]['enable_shares'] = $val['enable_shares'];//可用份额
			$result[$key]['worth_value'] = $val['worth_value'];//市值
			$result[$key]['lock_share'] = $val['lock_share'];//最低持有份额
						
		}
		return $result;
		
	}
	/**
     * 交易申请查询  功能号 trade_apply_qry
     *
     * @param string   $search 基金代码
     * @return string
     */
    public function tradeApplyQry($fundcode='',$trade_acco='',$client_id='',$request_num=1,$reqry_recordsum_flag=1,$qry_beginrownum=0) 
    {   
        $data = $fundcodemsg = '';
        // 
        $url = STI_CWSALE.'trade_apply_qry';
        $arrCommon = array(
                        'targetcomp_id' => TARGETCOMP_ID,
                        'sendercomp_id' => SENDERCOMP_ID,
                        'access_token'  => $this->access_token,
                        'trust_way'     => TRANSACTION_MODE,//委托方式 
                        'capital_mode'  => CAPITAL_MODE,//资金方式
                        'trade_source'  => SOURCE_TRADE,//交易来源
                     );
        $arrDynamic = array(
                        'request_num'          => $request_num,//请求行数
                        'reqry_recordsum_flag' => $reqry_recordsum_flag,//重新统计总记录数标志
                        'qry_beginrownum'      => $qry_beginrownum,//查询起始行号
                        'trade_acco'           => $trade_acco,//交易账号
                        'client_id'            => $client_id,//客户账号
                        'fund_code'            => $fundcode, //基金代码
                      );
        $arr = array_merge($arrCommon,$arrDynamic);
        //var_dump($arr);
        $result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
        $data = json_decode($result['data'],true);
        //var_dump($result);exit;
        //判断是否存在该基金代码
        if(!$data['data']['0']['total_count'] || $data['data']['0']['success_type']!='0') return '';
        $data = $data['data']['0'];
		//var_dump($data);exit;
        return $data;
    }
	/**
     * 基金列表撤单-交易申请查询  功能号 trade_apply_qry
     *
     * @param string   $search 基金代码
     * @return string
     */
    public function revokeTradeApplyQry($trade_acco='',$client_id='',$request_num=1,$reqry_recordsum_flag=1,$qry_beginrownum=1,$taconfirm_flag='9',$fundcode='')
    {   
        $data = $fundcodemsg = '';
        $url = STI_CWSALE.'trade_apply_qry';
        $arrCommon = array(
                        'targetcomp_id' => TARGETCOMP_ID,
                        'sendercomp_id' => SENDERCOMP_ID,
                        'access_token'  => $this->access_token,
                        'trust_way'     => TRANSACTION_MODE,//委托方式 
                       // 'capital_mode'  => CAPITAL_MODE,//资金方式
                        'trade_source'  => SOURCE_TRADE,//交易来源
                     );
        $arrDynamic = array(
                        'request_num'          => $request_num,//请求行数
                        'reqry_recordsum_flag' => $reqry_recordsum_flag,//重新统计总记录数标志
                        'qry_beginrownum'      => $qry_beginrownum,//查询起始行号
                        'trade_acco'           => $trade_acco,//交易账号
                        'client_id'            => $client_id,//客户账号
                        'fund_code'            => $fundcode, //基金代码
                        'taconfirm_flag'       => $taconfirm_flag, //确认标识 9为未确认
                        'begin_date'           => date('Ymd'), //起始日期
//                        'end_date'             => date('Ymd'), //到期日期
						//'hope_date'            => date('Ymd'), //预约日期
						
                      );
        $arr = array_merge($arrCommon,$arrDynamic);
        //t($arr);
        $result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
        $data = json_decode($result['data'],true);
       // t($data['data']);exit;
        //判断是否存在该基金代码
        if(!$data['data']['0']['total_count'] || $data['data']['0']['success_type']!='0') return '';
		$data = $this->fundRevokeData($data['data']);
		//t($data);
        return $data;
    }
	/**
     * 单个基金撤单-交易申请查询  功能号 trade_apply_qry
     *
     * @param string   $search 基金代码
     * @return string
     */
    public function revokeCodeTradeApplyQry($trade_acco='',$client_id='',$allot_no,$request_num=1,$reqry_recordsum_flag=1,$qry_beginrownum=0,$taconfirm_flag='9',$fundcode='') 
    {   
        $data = $fundcodemsg = '';
        $url = STI_CWSALE.'trade_apply_qry';
        $arrCommon = array(
                        'targetcomp_id' => TARGETCOMP_ID,
                        'sendercomp_id' => SENDERCOMP_ID,
                        'access_token'  => $this->access_token,
                        'trust_way'     => TRANSACTION_MODE,//委托方式 
                       // 'capital_mode'  => CAPITAL_MODE,//资金方式
                        'trade_source'  => SOURCE_TRADE,//交易来源
                     );
        $arrDynamic = array(
                        'request_num'          => $request_num,//请求行数
                        'reqry_recordsum_flag' => $reqry_recordsum_flag,//重新统计总记录数标志
                        'qry_beginrownum'      => $qry_beginrownum,//查询起始行号
                        'trade_acco'           => $trade_acco,//交易账号
                        'client_id'            => $client_id,//客户账号
                        'allot_no'             => $allot_no,//客户账号
                        'fund_code'            => $fundcode, //基金代码
                        'taconfirm_flag'       => '9', //确认标识 9为未确认
                        'begin_date'           => date('Ymd'), //起始日期
//                        'end_date'             => date('Ymd'), //到期日期
                       // 'hope_date'             => date('Ymd'), //预约日期
						
                      );
        $arr = array_merge($arrCommon,$arrDynamic);
       // t($arr);
        $result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
        $data = json_decode($result['data'],true);
//        t($data['data']);exit;
        //判断是否存在该基金代码
        if(!$data['data']['0']['total_count'] || $data['data']['0']['success_type']!='0') return '';
		$data = $this->fundRevokeData($data['data']);
//		t($data);
        return $data;
    }
	/**
     * 基金列表撤单-交易申请查询  功能号 trade_apply_qry
     *
     * @param string   $search 基金代码
     * @return string
     */
    public function trade_apply_qry($search=array(),$trade_acco='',$client_id='',$request_num=1,$reqry_recordsum_flag=1,$qry_beginrownum=1,$taconfirm_flag='') 
    {   
        $data = $fundcodemsg = '';
        $url = STI_CWSALE.'trade_apply_qry';
        $arrCommon = array(
                        'targetcomp_id' => TARGETCOMP_ID,
                        'sendercomp_id' => SENDERCOMP_ID,
                        'access_token'  => $this->access_token,
                        'trust_way'     => TRANSACTION_MODE,//委托方式 
                        'capital_mode'  => CAPITAL_MODE,//资金方式
                        //'trade_source'  => SOURCE_TRADE,//交易来源
                     );
        $arrDynamic = array(
                        'request_num'          => $request_num,//请求行数
                        'reqry_recordsum_flag' => $reqry_recordsum_flag,//重新统计总记录数标志
                        'qry_beginrownum'      => $qry_beginrownum,//查询起始行号
                        'trade_acco'           => $trade_acco,//交易账号
                        'client_id'            => $client_id,//客户账号
                        'taconfirm_flag'       => $taconfirm_flag, //确认标识 9为未确认
                       // 'begin_date'           => date('Ymd'), //起始日期
                       // 'end_date'             => date('Ymd'), //到期日期
						
                      );
        $arr = array_merge($arrCommon,$arrDynamic,$search);
       
        $result = curl_post($url,$arr);
		//$this->logfile = 'aaa';
		//file_put_contents('/var/tmp/trade.buyfunds.cn/aaa/20170222.log','');
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);
		
        $data = json_decode($result['data'],true);
        //判断是否存在该基金代码
        if(!$data['data']['0']['total_count'] || $data['data']['0']['success_type']!='0') return '';
		$data['list']  = $this->fundRevokeData($data['data']);
		$data['total'] = $data['data']['0']['total_count'];
        return $data;
    }
	/**
     * 交易确认查询
     * @param $client_id
     * @param $begin_date
     * @param $end_date
     * @return array
     */
    public function trade_confirm_qry($trade_acco='',$allot_no='',$flag='')
    {
        $url = STI_CWSALE . 'trade_confirm_qry';
		if(!$trade_acco || !$allot_no) return array();
        $arrCommon = [
            'targetcomp_id'   => TARGETCOMP_ID,
            'sendercomp_id'   => SENDERCOMP_ID,
            "trust_way"       => TRANSACTION_MODE,
            "access_token"    => $this->access_token,
            "request_num"     => 1,
            "qry_beginrownum" => 1,
            "trade_acco"      => $trade_acco,
            "allot_no"        => $allot_no,
			"reqry_recordsum_flag" => 1,
        ];
        $result = curl_post($url,$arrCommon);
		//$this->logfile = 'aqr/test';
		logs('参数：'.print_r($arrCommon,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
		$data = json_decode($result['data'],true);
		//判断是否存在该基金代码
		if(!isset($data['data']['0']['total_count']) || !$data['data']['0']['total_count']) return '';
		$data = $data['data']['0'];
		//返回指定基金行情信息
		if($flag){
			$flagdata='';
			foreach($data as $k=>$v){
				if($k==$flag)
					$flagdata = $v;
			}
			return $flagdata;
		}
		return $data;
    }
	/**
     * 基金撤单-交易申请查询数据组装函数  功能号 trade_apply_qry
     *
     * @param array   $data 需要组装的数组  
     * @return string
     */
    public function fundRevokeData($data)
	{
		if(!is_array($data) && !$data)
			return '';
		$result = array();
		//$busin_codearr = config_item('fund_busin_code');
		foreach($data as $key=>$val)
		{
			$result[$key]['time']       = date('Y-m-d H:i:s',strtotime($val['original_date'].' '.$val['order_date']));//具体时间
			$result[$key]['times']       = date('Y-m-d',strtotime($val['original_date'].' '.$val['order_date']));//具体时间
			$result[$key]['apply_time'] = $val['apply_time'];// 申请时间
			$result[$key]['apply_date'] = $val['apply_date'];//申请日期
			$result[$key]['fund_code']  = $val['fund_code'];// 基金代码
			$result[$key]['fund_name']  = $this->fundType($val['fund_code'],'fund_name');//基金名称
			$result[$key]['fund_bank']  = $this->datadictionary['1601'][$val['bank_no']];//银行名称			
			$result[$key]['balance']    = $val['balance'];//发生金额
			$result[$key]['shares']     = $val['shares'];//发生份额
			$result[$key]['allot_no']   = $val['allot_no'];//申请编号
			$result[$key]['trade_acco'] = $val['trade_acco'];//申请编号
			$result[$key]['taconfirm_flag'] = $this->datadictionary['C00006'][$val['taconfirm_flag']];//状态
			$result[$key]['taconfirm_flag_code'] = $val['taconfirm_flag'];//状态
			$result[$key]['fund_busin_code'] = $this->businessdictionary[$val['fund_busin_code']];//业务名称
			$result[$key]['fund_busin_codes'] = $val['fund_busin_code'];//业务标识
			$result[$key]['receivable_account'] = substr($val['receivable_account'],-4);//银行账号截取
			
			if($val['taconfirm_flag']=='1'){
				$result[$key]['trade_confirm_type'] = $this->trade_confirm_qry($val['trade_acco'],$val['allot_no'],'trade_confirm_type');//交易确认份额
				$result[$key]['trade_confirm_balance'] = $this->trade_confirm_qry($val['trade_acco'],$val['allot_no'],'trade_confirm_balance');//交易确认金额
			}else{
				$result[$key]['trade_confirm_type'] = '';//交易确认份额
				$result[$key]['trade_confirm_balance'] = '';//交易确认金额
			}
			
									
		}
		return $result;
		
	}
	/**
     *   持仓明细 功能号 share_qry
     *
     * @param array   $data 需要组装的数组  
     * @return string
     */
    public function positionData($data,$total='')
	{
		error_reporting(0);
		if(!is_array($data) && !$data)
			return '';
		$result = $total = array();
		$result['today_values'] = $result['today_income'] = '';
		//t($data);
		foreach($data as $key=>$val)
		{
			if($val['current_share']=='0.00') continue;
			$result['list'][$key]['fund_code']     = $val['fund_code'];// 基金代码
			$result['list'][$key]['fund_name']     = $this->fundType($val['fund_code'],'fund_name');//基金名称
			$result['list'][$key]['fund_bank']     = $this->datadictionary['1601'][$val['bank_no']];//银行名称
			$result['list'][$key]['ofund_type']    = $this->fundType($val['fund_code'],'ofund_type');//基金类型			
			$result['list'][$key]['ofund_type']    = $this->datadictionary['2022'][$result['list'][$key]['ofund_type']];;//基金类型			
			$result['list'][$key]['current_share'] = $val['current_share'];//拥有份额
			$result['list'][$key]['worth_value']   = sprintf("%.2f",$val['worth_value']);//市值
			$result['list'][$key]['accum_income']  = $val['accum_income'];//累计收益
			
			//没有份额删除
			if(!$val['worth_value'] || !$val['current_share']){
				unset($result['list'][$key]);
				continue;
			} 
			$result['list'][$key]['net_worth']     = sprintf("%.4f",$val['worth_value']/$val['current_share']);//最新净值
			$result['list'][$key]['loss_ratio']    = $val['accum_income']/$val['cost'];//盈亏比例
			if($result['list'][$key]['loss_ratio'])
			$result[$key]['loss_ratio']    = sprintf("%.2f",$result['list'][$key]['loss_ratio']).'%';//盈亏比例
		
			$result['today_values']  += $val['worth_value'];//总市值
			$result['today_income']  += $val['today_income'];//当日收益
										
		}
		$result['today_values'] = sprintf("%.2f",$result['today_values']);//总市值
		$result['today_income'] = sprintf("%.2f",$result['today_income']);//当日收益
		return $result;
		
	}
	/**
     *   收益详细 功能号 share_qry
     *
     * @param array   $data 需要组装的数组  
     * @return string
     */
    public function position_income($data,$total='')
	{
		if(!is_array($data) && !$data)
			return '';
		$result = $total = array();
		$result['today_values'] = $result['today_income'] = $result['accum_income'] = '';
		//t($data);
		foreach($data as $key=>$val)
		{		
			$result['today_values'] += $val['worth_value'];//总市值
			$result['today_income'] += $val['today_income'];//当天收益
			$result['accum_income'] += $val['accum_income'];//累计收益
										
		}
		$result['today_values'] = sprintf("%.2f",$result['today_values']);//总市值
		$result['today_income'] = sprintf("%.2f",$result['today_income']);//当天收益
		$result['accum_income'] = sprintf("%.2f",$result['accum_income']);//累计收益
		return $result;
		
	}
    /**
     * 赎回操作 卖出基金  功能号 redeem_trade
     *
     * @param string   $search 基金代码
     * @return string
     */
    public function fundRedeemTrade($bank_no,$client_id,$fundcode='',$shares,$password,$flag='0') 
    {   
        $data  = $fundcodemsg = $param = '';
		//pre($bank_no);pre($client_id);
        $param = $this->fundBankAccountSearch($bank_no,'',$client_id);
		//var_dump($param);exit;
        $share_type = $this->fundType($fundcode);
        $share_type = $share_type[$fundcode]['share_type'];
       // echo $share_type;exit;
        if(!$param || !$share_type) return '';
        // 基金赎回
        $url = STI_CWSALE.'redeem_trade';
        $arrCommon = array(
                        'targetcomp_id' => TARGETCOMP_ID,
                        'sendercomp_id' => SENDERCOMP_ID,
                        'access_token'  => $this->access_token,
                       // 'money_type'    => CURRENCY,//币种
                        'trust_way'     => TRANSACTION_MODE,//委托方式 
                        'capital_mode'  => CAPITAL_MODE,//资金方式CAPITAL_MODE
                        'trade_source'  => SOURCE_TRADE,//交易来源                    
                     );
        $arrDynamic = array(
                        'fund_code'     => $fundcode,//基金代码
                        'bank_no'       => $param['bank_no'],//银行代码
                        'bank_account'  => $param['bank_account'],//银行账号
                        'share_type'    => $share_type,//份额分类$share_type['share_type_code']
                        'shares'        => $shares,//发生份额
                        'trade_acco'    => $param['trade_acco'], //交易账号
                        'password'      => $password, //交易密码
						'fund_exceed_flag'=> $flag,//巨额赎回标志（0-放弃超额部分1-继续赎回）
                      );
        $arr = array_merge($arrCommon,$arrDynamic);
        $result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
        //判断是否请求成功
        if($result['code']!='200')
            return '';
        $data = json_decode($result['data'],true);
        $data = $data['data']['0'];     
        return $data;
    }
	/**
     * 基金撤单  功能号 undotradeapply_trade
     *
     * @param string   $search 基金代码
     * @return string
     */
    public function undotradeapplyTrade($trade_acco,$allot_no,$password) 
    {   
        $data = '';
        // 基金撤单
        $url = STI_CWSALE.'undotradeapply_trade';
        $arrCommon = array(
						'trust_way'     => TRANSACTION_MODE,//委托方式
                        'targetcomp_id' => TARGETCOMP_ID,
                        'sendercomp_id' => SENDERCOMP_ID,
                        'access_token'  => $this->access_token,
                        'capital_mode'  => CAPITAL_MODE,//资金方式CAPITAL_MODE
                        'trade_source'  => SOURCE_TRADE,//交易来源                    
                     );
        $arrDynamic = array(
                        'original_appno'=> $allot_no,//发生份额
                        'trade_acco'    => $trade_acco, //交易账号
                        'password'      => $password, //交易密码
						//'order_date'    => date('Ymd'), //下单日期
                       // 'apply_time'    => date('Ymd'), //申请时间
                      );
					  
        $arr = array_merge($arrCommon,$arrDynamic);
		
        $result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
        //判断是否请求成功
        if($result['code']!='200')
            return '';
        $data = json_decode($result['data'],true);
        $data = $data['data']['0'];     
        return $data;
    }
	/**
     * 基金分红设置  功能号 share_qry
     *
     * @param array   $data 基金数据
     * @return array
     */
    public function dividend_data($data) 
    { 
		//t($data);
		if(!is_array($data) && !$data)
			return '';
		$result = array();
		foreach($data as $key=>$val)
		{
			if($val['c_flag']!='1'||$val['current_share']=='0.00') continue;
			$result[$key]['fund_code']  = $val['fund_code'];// 基金代码
			$result[$key]['trade_acco'] = $val['trade_acco'];// 交易账号
			$result[$key]['fund_name']  = $this->fundType($val['fund_code'],'fund_name');//基金名称
			$result[$key]['share_type'] = $this->datadictionary['769023'][$val['share_type']];;//收费方式
			$result[$key]['auto_buy']   = $this->datadictionary['2010'][$val['auto_buy']];//分红方式
			$result[$key]['ofund_type'] = $this->fundType($val['fund_code'],'ofund_type');//基金类型
		}
		return $result;
	}
	/**
     * 设置分红方式  功能号 dividendmethod_trade
     *
     * @param array   $data 基金数据
     * @return array
     */
    public function dividendmethod_trade($fund_code,$trade_acco,$auto_buy,$password) 
    { 
		$share_type = $this->fundType($fund_code,'share_type');
		if(!$share_type) return '';
        // 基金撤单
        $url = STI_CWSALE.'dividendmethod_trade';
        $arrCommon = array(
                        'targetcomp_id' => TARGETCOMP_ID,
                        'sendercomp_id' => SENDERCOMP_ID,
                        'access_token'  => $this->access_token,
						'trust_way'     => TRANSACTION_MODE,//委托方式 
                        'capital_mode'  => CAPITAL_MODE,//资金方式
                        'trade_source'  => SOURCE_TRADE,//交易来源                   
                     );
        $arrDynamic = array(
                        'trade_acco'    => $trade_acco, //交易账号
                        'auto_buy'      => $auto_buy, //分红方式
                        'fund_code'     => $fund_code, //基金代码
                        'share_type'    => $share_type, //份额分红
                        'password'      => $password, //交易密码
                      );
        $arr = array_merge($arrCommon,$arrDynamic);
		//t($arr);
        $result = curl_post($url,$arr);
		logs('参数：'.print_r($arr,true).$url.PHP_EOL.'回参：'.print_r($result,true),$this->logfile);//记录接口执行信息
        //判断是否请求成功
        if($result['code']!='200')
            return '';
        $data = json_decode($result['data'],true);
		
		if(!isset($data['data']['0']['success_type']))
			return '';
		
        $data = $data['data']['0'];     
        return $data;
	}
	/**
     * 解绑银行卡判断  功能号 
     *
     * @param array   $data 基金数据
     * @return array
     */
    public function unbindBankCard($param=array()) 
    { 
        //$param = array('trade_acco'=>'1146');
		$data = $this->shareQry($param);
		if(!isset($param['trade_acco']) || !$param['trade_acco']){
			return array('flag'=>'10006','msg'=>'参数缺失！');
		}
		//份额查询
		if(isset($data['0']) && ($data['0']['current_share']>0 || $data['0']['total_count']>0))
			return array('flag'=>'10005','msg'=>'该账号拥有份额，不符合解绑条件！');
		//交易申请查询
		$applyqry = $this->trade_apply_qry(array('sort_direction'=>'1','begin_date'=> date('Ymd',time()-7*86400),'end_date'=> date('Ymd')),$param['trade_acco']);
		if(isset($applyqry['0']) && $applyqry['0']['taconfirm_flag']!=0)
			return array('flag'=>'10005','msg'=>'该账号有正在进行的交易，不符合解绑条件！');
		return array('flag'=>'10000','msg'=>'ok');
	}
	/**
     * 解绑银行卡判断  功能号 
     *
     * @param array   $data 基金数据
     * @return array
     */
    public function unbindBankCards($param=array()) 
    { 
		$data = $this->shareQry(array('trade_acco'=>'1146'));
		//份额查询
		if(isset($data['0']) && ($data['0']['current_share']>0 || $data['0']['total_count']>0))
			return array('flag'=>'10005','msg'=>'该账号拥有份额，不符合解绑条件！');
		//交易申请查询
		$applyqry = $this->trade_apply_qry(array('sort_direction'=>'1','begin_date'=> date('Ymd',time()-7*86400),'end_date'=> date('Ymd')),'1146');
		if(isset($applyqry['0']) && $applyqry['0']['taconfirm_flag']!=0)
			return array('flag'=>'10005','msg'=>'该账号有正在进行的交易，不符合解绑条件！');
		return array('flag'=>'10000','msg'=>'ok');
	}
	/**
     * 设置接口信息路径  
     *
     * @param string   $logfile 基金数据
     * 
     */
	public function set_log_file($logfile='')
	{
		if($logfile){
			$this->logfile = $logfile;
		}
	}
}

/* End of file quotes_manage_mdl.php */
/* Location: ./application/models/_stock/quotes_manage_mdl.php */