<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ajaxcenter extends MY_Controller {

    private $datadictionary = array();
    private $bankCode = array();
    private $province = array();
    private $cache;
    
    
    public function __construct() {
        parent::__construct();
        $this->datadictionary = config_item('datadictionary');
        $this->bankCode = $this->datadictionary['1601']; //银行码
        $this->province = $this->datadictionary['769019']; //省份码
        //pre($this->bankCode);
        $this->load->model('_user/fund_model');
        $this->load->model('_user/User_Interact');
        
        $this->load->library('Buyfunds_mem',NULL,'buyfunds_mem');
        $this->cache = $this->buyfunds_mem;
        $this->_data['centerUrl'] = TRADE_WEB_URL . '/user/center';
        //pre($x);
    }
    
    
    /**
     * 
     * 通过银行获取省份
     * 
     */
    public function provinceByBank(){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        
        $bcode = filter_slashes(trim($this->input->post('bcode', true)));

        //参数是否完整
        if(!$bcode){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }
       
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 2);
        
        //获取省份
        $param = array('C_BANKNO'=>$bcode);
        $tProvince = $this->fund_model->getBankInfo($param, 'DISTINCT VC_PROVINCENAME');

        //pre($tProvince);
        if(!$tProvince){
            echo json_encode(array('flag'=>'10005', 'msg'=>'银行信息获取错误')); exit;
        }

        $rProvince = array();
        foreach($tProvince as $tk => $tv){
            if($pCode = array_search($tv['VC_PROVINCENAME'], $this->province)){
                $rProvince[$pCode] = $tv['VC_PROVINCENAME'];
            }

        }
        unset($tProvince);
        echo json_encode(array('flag'=>'10000', 'msg'=>'', 'info'=>$rProvince)); exit;

    }
    
    /**
     * 
     * 通过银行和省份获取市
     * 
     */
    public function cityByProvinceBank(){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        
        $bcode = filter_slashes(trim($this->input->post('bcode', true)));
        $provinceID = filter_slashes(trim($this->input->post('pid', true)));

        //参数是否完整
        if(!$bcode || !isset($this->province[$provinceID])){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }
        
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 2);
        
        //获取城市
        $param = array('C_BANKNO'=>$bcode, 'VC_PROVINCENAME'=>$this->province[$provinceID]);
        $tCity = $this->fund_model->getBankInfo($param, 'DISTINCT VC_CITYNAME');

        if(!$tCity){
            echo json_encode(array('flag'=>'10005', 'msg'=>'银行信息获取错误')); exit;
        }

        
        $rCity = array();
        foreach($tCity as $tk => $tv){
                $rCity[$tv['VC_CITYNAME']] = $tv['VC_CITYNAME'];

        }
        unset($tCity);
            
        echo json_encode(array('flag'=>'10000', 'msg'=>'', 'info'=>$rCity)); exit;

        
    }
	
    /**
     * 
     * 通过银行和省份获取市
     * 
     */
    public function bandByProvinceBankCity(){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        
        $bcode = filter_slashes(trim($this->input->post('bcode', true)));
        $provinceID = (int)$this->input->post('pid', true);
        $city = filter_slashes(trim($this->input->post('city', true)));

        //参数是否完整
        if(!$bcode || !isset($this->province[$provinceID])){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }
        
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 2);
        
        //获取银行
        $param = array('C_BANKNO'=>$bcode, 'VC_PROVINCENAME'=>$this->province[$provinceID], 'VC_CITYNAME'=>$city);
        //pre($param);
        $tBank = $this->fund_model->getBankInfo($param, 'DISTINCT VC_BANKNAME, VC_BRANCHBANK');

        if(!$tBank){
            echo json_encode(array('flag'=>'10005', 'msg'=>'银行信息获取错误')); exit;
        }

        
        $rBank = array();
        foreach($tBank as $tk => $tv){
                $rBank[$tv['VC_BRANCHBANK']] = $tv['VC_BANKNAME'];

        }
        unset($tCity);
            
        echo json_encode(array('flag'=>'10000', 'msg'=>'', 'info'=>$rBank)); exit;

    }
	
    /**
     *
     * 银行卡绑定页面
     *
     */
    public function regBindCard(){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        
        $bCode = filter_slashes(trim($this->input->post('bcode', true))); //银行编码
        $bProvince = (int)trim($this->input->post('bprovince', true)); //省份
        $bCity = filter_slashes(trim($this->input->post('bcity', true))); //县市
        $bBankCodeName = filter_slashes(trim($this->input->post('bbankcode', true))); //银行支行编号和支行名称 格式 ：银行支行编号|||支行名称
        $identnum = filter_slashes(trim($this->input->post('identnum', true))); //身份证
        $cName = filter_slashes(trim($this->input->post('cname', true))); //名字
        $mobile = filter_slashes(trim($this->input->post('cmobile', true))); //手机号
        $password = filter_slashes(trim($this->input->post('mypass', true))); //密码
        $bankNum = filter_slashes(trim($this->input->post('banknum', true))); //银行卡号

        if(!$bBankCodeName || $bBankCodeName =='undefined'){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误1，请重试')); exit;
        }
        
        $tmpBBankCodeName = explode('|||', $bBankCodeName);

        if(!$tmpBBankCodeName){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误2，请重试')); exit;
        }
        $bBankCode = $tmpBBankCodeName[0];
        $branchName = $tmpBBankCodeName[1];
        
        $tid = $this->input->post('tid', true);
        $kid = $this->input->post('kid', true);
        
        //参数是否完整
        if(!$bCode || !$bProvince || !$bCity || !$bBankCode || !$branchName|| !$identnum || !$cName || !$mobile || !$password || !$bankNum|| !$tid || !$kid){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }


        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }
        
        $userID = $this->_data['userID'] = (int)$this->User_Interact->getUserID();

        //页面过期, 半小时
        isRequestTimeout($tid, 60*30, '请求已失效，请刷新页面重试');

        $xVer = substr(md5(ENCODE_KEY . substr(md5($tid), -5) . 'regbindbank'), 8, 16);
         
        if($xVer != $kid){
            echo json_encode(array('flag'=>'10003', 'msg'=>'校验错误')); exit;
        }

        
        $mauRs = mobileAndUser($mobile, $userID, 60, 2);
        if($mauRs['flag'] == '10000'){
            echo json_encode(array('flag'=>'10008', 'msg'=>$mauRs['msg'])); exit;
        }
        
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        $this->_userAnti($userID, strtolower(__FUNCTION__), 10, 3);
        
        if(!preg_match('/^(\d{16}|\d{17}|\d{18}|\d{19})$/', $bankNum)){
            echo json_encode(array('flag'=>'10004', 'msg'=>'卡号格式错误，请确认后重试')); exit;
        }
         
        if(!check_str($mobile, 'mobile')){
            echo json_encode(array('flag'=>'10005', 'msg'=>'手机格式错误')); exit;
        }
        
        if(strlen($identnum) > 18 || strlen($identnum) < 5){
            echo json_encode(array('flag'=>'10006', 'msg'=>'请填写正确的身份证号码')); exit;
        }
        
        //密码判断
        $checkPwd =  tradeCheckpwd($password, $identnum);
        if($checkPwd['flag'] != '10000'){
            echo json_encode($checkPwd); exit;
        }
        
        if(!isset($this->province[$bProvince])){
            echo json_encode(array('flag'=>'10007', 'msg'=>'省份错误')); exit;
        }

        if(!isset($this->bankCode[$bCode])){
            echo json_encode(array('flag'=>'10008', 'msg'=>'银行代码错误')); exit;
        }
        
        //判断银行卡和身份证是否已存在
        $biParam = array(
            'BankCard' => $bankNum,
            'IdentityNumber' => $identnum
        );
        $xRs = $this->User_Model->isExistBankOrIndent($biParam);
        
        if($xRs['flag'] != '10000'){
            echo json_encode($xRs); exit;
        }
        
        $serialNo = 'bj'. date('YmdHis') . rand('00000', '99999');
        $param = array(
            'serial_no' => $serialNo,
            'capital_mode' => CAPITAL_MODE,
            'merchant_name' => MERCHANT_NAME,
            'id_kind_gb' => 0, //0 身份证
            'id_no' => $identnum, //身份证
            'real_name' => $cName, //真实姓名
            'mobile_tel' => $mobile,
            'order_date' => date('Ymd'),
            'order_time' => date('His'),
            'bank_account' => $bankNum,
            'bank_no' => $bCode  //银行代码
        );
        //pre($param);
        //请求短信签约接口，发送短信
        logs('|-param-|' . print_r($param, true), $logFile);
        $sRs = $this->User_Model->sign_sms($param);
        logs('|-sRs-|' . print_r($sRs, true), $logFile);
        //pre($sRs);
        if($sRs){
            if(isset($sRs['error_code'])){
                //echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_info'] . $sRs['error_code'])); exit;
                echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_info'])); exit;
            }
            
            if(isset($sRs['error']) && isset($sRs['error_description']) && $sRs['error_description']){
                //echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_info'] . $sRs['error_code'])); exit;
                echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_description'])); exit;
            }
            
            

            $ssid = $sRs['data'][0]['sms_serial_no'];
            $protocolNo = isset($sRs['data'][0]['protocol_no']) ? $sRs['data'][0]['protocol_no'] : '';
            $sKey = md5($ssid . ENCODE_KEY . $userID);
            $rData = array('skey'=>$sKey, 'ssid'=>$ssid, 'mobile'=>mobileReplace($mobile));
            
            $num =  substr($identnum, -2, 1);
            $oSex = 0; //0 男,1 女, 2非自然人
            if($num%2 == 0){
                $oSex = 1;
            }
            logs('|-bankNum-|' . $identnum . '|-sex-|' .  $oSex, $logFile);
            $sParam = array(
                'ssid' => $ssid,
                'serialNo' => $serialNo,
                'bBankCode' =>$bBankCode,
                'branchName' =>$branchName,
                'identnum' => $identnum,
                'mobile' => $mobile,
                'bankNum' => $bankNum,
                'password' => tradePswd($password),
                'province' => $this->province[$bProvince],
                'city' => $bCity,
                'bankName' => $this->bankCode[$bCode],
                'bankCode' => $bCode,
                'cName' => $cName,
                'protocolNo' => $protocolNo,
                'sex' => $oSex,
            );
            $this->cache->set($sKey, $sParam, 15*60); //缓存15分钟
            $tid2 = time();
            //设置发送短信的时间 用于函数mobileAndUser
            $t1  = '2time_sendsms_times_user_'.date('Ymd').'_'.$userID;
            $t2  = '2time_sendsms_times_mobile_'.date('Ymd').'_'.$mobile;
            $this->cache->set($t1, $tid2, 7*60);
            $this->cache->set($t2, $tid2, 7*60);
            echo json_encode(array('flag'=>'10000', 'msg'=>'成功', 'info'=>$rData)); exit;
            
        } else {
            echo json_encode(array('flag'=>'10009', 'msg'=>'短信签约失败')); exit;
        }
        
    }
    

    /**
     *
     * 激活短信签约
     *
     */
    public function activeBindCard(){
    
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
    
        $ssid = filter_slashes(trim($this->input->post('ssid', true))); //序列号
        $sCode = filter_slashes(trim($this->input->post('scode', true))); //验证码
        $sKey = filter_slashes(trim($this->input->post('skey', true))); //加密串
        $fst = (int)$this->input->post('fst',true); //0注册页绑定 1 银行页绑定  
       
        //参数是否完整
        if(!$ssid || !$sCode || !$sKey){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }
    
        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }
    
        $userID = $this->_data['userID'] = (int)$this->User_Interact->getUserID();
    
        $xVer = md5($ssid . ENCODE_KEY . $userID);
        if($xVer != $sKey){
            echo json_encode(array('flag'=>'10003', 'msg'=>'校验错误')); exit;
        }
    
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        $this->_userAnti($userID, strtolower(__FUNCTION__), 10, 3);
    
    
        //$ssid = '1680502';
        //$sCode = '365779';
        //$identnum = '350122198812076538';
        //$mobile = '13599951535';
        $mInfo = $this->cache->get($sKey);
        if(!$mInfo){
            echo json_encode(array('flag'=>'10004', 'msg'=>'信息获取失败')); exit;
        }
        //$bankNum = '6228480068816522772';
        //$bBankCode = '103391013537';
    
        //$serialNo = 'bj'. date('YmdHis') . rand('00000', '99999');
        $param = array(
            //'serial_no' => 'bj'. date('YmdHis') . rand('00000', '99999'), //短信流水序号
            'mobile_code' => $sCode, //手机验证码
            'sms_serial_no' => $ssid, //短信流水序号
            'original_serial_no' => $mInfo['serialNo'],
            //'original_serial_no' => '1680502',
            'capital_mode' => CAPITAL_MODE,
            'merchant_name' => MERCHANT_NAME,
            'id_kind_gb' => 0, //0 身份证
            'id_no' => $mInfo['identnum'], //身份证
            'mobile_tel' => $mInfo['mobile'],
            'order_date' => date('Ymd'),
            'order_time' => date('His'),
            'bank_account' => $mInfo['bankNum'],
            'bank_no' => $mInfo['bBankCode']  //银行支行代码
        );
        //pre($param);
        //请求短信签约接口 确认签约
        logs('|-param-|' . print_r($param, true), $logFile);
        $sRs = $this->User_Model->sign_sms($param);
        logs('|-sRs-|' . print_r($sRs, true), $logFile);
        //pre($sRs);
        if($sRs){
            if(isset($sRs['error_code'])){
                echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_info'])); exit;
            }
    
    
            if(isset($sRs['error']) && isset($sRs['error_description']) && $sRs['error_description']){
                //echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_info'] . $sRs['error_code'])); exit;
                echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_description'])); exit;
            }
            if($sRs['data'][0]['status'] ==1 ){
    
                $iParam = array(
                    'UserID' => $userID,
                    'Province' => $mInfo['province'],
                    'City' => $mInfo['city'],
                    'BankName' => $mInfo['bankName'],
                    'BankNo' => $mInfo['bankCode'],
                    'BranchBank' => $mInfo['bBankCode'],
                    'BranchName' => $mInfo['branchName'],
                    'BankCard' => $mInfo['bankNum'],
                    'Mobile' => $mInfo['mobile'],
                    'NickName' => $mInfo['cName'],
                    'IdentityNumber' => $mInfo['identnum'],
                    'Sex' => $mInfo['sex'],
                    'Password' => $mInfo['password'],
                    'EndValidDate' => '20990101', //默认全部写2099-01-01
                    'Status' => 1, //状态： 1审核通过 2 审核中 3 审核不通过
                    'protocolNo' => $mInfo['protocolNo'], //短信协议号
                    'DataTime' => date('Y-m-d H:i:s')
                );
    
                //pre($iParam);
                $iRs = $this->User_Model->regBindBank($iParam);
                unset($iParam['Password']);
                logs('|-iParam-|' . print_r($iParam, true), $logFile);
                logs('|-iRs-|' . print_r($iRs, true), $logFile);
                if($iRs['flag'] !='10000'){
                    echo json_encode(array('flag'=>'10014', 'msg'=>$iRs['msg'], 'info'=>'')); exit;
                }
                
                //$jUrl = TRADE_WEB_URL .'/user/center/banksuccess';
                if(!$fst){ //注册绑定 退出
                    //清除cookie
                    $this->User_Interact->delCnfolCookie();
                    $jUrl = TRADE_WEB_URL .'/user/register/regbindsuccess';
                } else {
                    $this->User_Interact->setNickNameCookie($mInfo['cName']);
                    $jUrl = TRADE_WEB_URL;
                }
                unset($iParam, $mInfo);
                //pre($iRs);
                echo json_encode(array('flag'=>'10000', 'msg'=>'成功', 'info'=>$jUrl)); exit;
            } else {
                echo json_encode(array('flag'=>'10012', 'msg'=>'短信签约失败2', 'info'=>'')); exit;
            }
    
    
        } else {
            echo json_encode(array('flag'=>'10009', 'msg'=>'短信签约失败')); exit;
        }
    }
    
    /**
     *
     * 银行卡再次绑定
     *
     */
    public function addBindCard(){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        
        $bCode = filter_slashes(trim($this->input->post('bcode', true))); //银行编码
        $bProvince = (int)trim($this->input->post('bprovince', true)); //省份
        $bCity = filter_slashes(trim($this->input->post('bcity', true))); //县市
        $bBankCodeName = filter_slashes(trim($this->input->post('bbankcode', true))); //银行支行编号和支行名称 格式 ：银行支行编号|||支行名称
        $mobile = filter_slashes(trim($this->input->post('cmobile', true))); //手机号
        $bankNum = filter_slashes(trim($this->input->post('banknum', true))); //银行卡号
        $password = filter_slashes(trim($this->input->post('mypass', true))); //密码
        
        if(!$bBankCodeName || $bBankCodeName =='undefined'){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误1，请重试')); exit;
        }
        $tmpBBankCodeName = explode('|||', $bBankCodeName);
        $bBankCode = $tmpBBankCodeName[0];
        $branchName = $tmpBBankCodeName[1];
        
        $tid = $this->input->post('tid', true);
        $kid = $this->input->post('kid', true);
        
        //参数是否完整
        if(!$bCode || !$bProvince || !$bCity || !$bBankCode || !$branchName || !$mobile || !$password || !$bankNum|| !$tid || !$kid){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }

        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }
        
        $userID = $this->_data['userID'] = (int)$this->User_Interact->getUserID();

        //页面过期, 半小时
        isRequestTimeout($tid, 60*30, '请求已失效，请刷新页面重试');

        $xVer = substr(md5(ENCODE_KEY . substr(md5($tid), -5) . 'addbank'), 8, 16);
         
        if($xVer != $kid){
            echo json_encode(array('flag'=>'10003', 'msg'=>'校验错误')); exit;
        }

        
        $mauRs = mobileAndUser($mobile, $userID, 60, 2);
        if($mauRs['flag'] == '10000'){
            echo json_encode(array('flag'=>'10008', 'msg'=>$mauRs['msg'])); exit;
        }
        
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        $this->_userAnti($userID, strtolower(__FUNCTION__), 10, 3);
        
        if(!preg_match('/^(\d{16}|\d{17}|\d{18}|\d{19})$/', $bankNum)){
            echo json_encode(array('flag'=>'10004', 'msg'=>'卡号格式错误，请确认后重试')); exit;
        }
         
        if(!check_str($mobile, 'mobile')){
            echo json_encode(array('flag'=>'10005', 'msg'=>'手机格式错误')); exit;
        }
        
        if(!isset($this->province[$bProvince])){
            echo json_encode(array('flag'=>'10007', 'msg'=>'省份错误')); exit;
        }

        if(!isset($this->bankCode[$bCode])){
            echo json_encode(array('flag'=>'10008', 'msg'=>'银行代码错误')); exit;
        }
        
        //判断银行卡是否已存在
        $biParam = array(
            'BankCard' => $bankNum
        );
        //pre($biParam);
        $xRs = $this->User_Model->isExistBankOrIndent($biParam);

        if($xRs['flag'] != '10000'){
            echo json_encode($xRs); exit;
        }
        
        $userBankCode = $this->User_Model->getUserBankCodeByUserID($userID);
        if($userBankCode){
            foreach ($userBankCode as $uk=>$uv){
                if($bCode == $uv['BankNo']){
                    echo json_encode(array('flag'=>'10012', 'msg'=>'不能绑定重复绑定相同银行')); exit;
                }
            }
        }
        //pre($userBankCode); exit;
        
        $userAuth = $this->User_Model->getUserAuthByUserID($userID);
        
        if(!isset($userAuth['Status']) || $userAuth['Status'] != '1'){
            echo json_encode(array('flag'=>'10006', 'msg'=>'请先通过实名认证')); exit;
            errorJump('请先通过实名认证', TRADE_ADD_BANK_URL);
        }
        
        //密码判断
        $checkPwd =  tradeCheckpwd($password, $userAuth['IdentityNumber']);
        if($checkPwd['flag'] != '10000'){
            echo json_encode($checkPwd); exit;
        }
        
        $serialNo = 'bj'. date('YmdHis') . rand('00000', '99999');
        $param = array(
            'serial_no' => $serialNo,
            'capital_mode' => CAPITAL_MODE,
            'merchant_name' => MERCHANT_NAME,
            'id_kind_gb' => 0, //0 身份证
            'id_no' => $userAuth['IdentityNumber'], //身份证
            'real_name' => $userAuth['TrueName'], //真实姓名
            'mobile_tel' => $mobile,
            'order_date' => date('Ymd'),
            'order_time' => date('His'),
            'bank_account' => $bankNum,
            'bank_no' => $bCode  //银行代码
        );
        //pre($param);
        //请求短信签约接口，发送短信
        logs('|-param-|' . print_r($param, true), $logFile);
        $sRs = $this->User_Model->sign_sms($param);
        logs('|-sRs-|' . print_r($sRs, true), $logFile);
        //pre($sRs);
        if($sRs){
            if(isset($sRs['error_code'])){
                //echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_info'] . $sRs['error_code'])); exit;
                echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_info'])); exit;
            }

            if(isset($sRs['error']) && isset($sRs['error_description']) && $sRs['error_description']){
                //echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_info'] . $sRs['error_code'])); exit;
                echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_description'])); exit;
            }
            
            $ssid = $sRs['data'][0]['sms_serial_no'];
            $protocolNo = isset($sRs['data'][0]['protocol_no']) ? $sRs['data'][0]['protocol_no'] : '';
            $sKey = md5($ssid . ENCODE_KEY  . 'a2a' . $userID);
            $rData = array('skey'=>$sKey, 'ssid'=>$ssid, 'mobile'=>mobileReplace($mobile));
            
            $num =  substr($userAuth['IdentityNumber'], -2, 1);
            $oSex = 0; //0 男,1 女, 2非自然人
            if($num%2 == 0){
                $oSex = 1;
            }
            
            $sParam = array(
                'ssid' => $ssid,
                'serialNo' => $serialNo,
                'bBankCode' =>$bBankCode,
                'branchName' =>$branchName,
                'identnum' => $userAuth['IdentityNumber'],
                'mobile' => $mobile,
                'bankNum' => $bankNum,
                'password' => tradePswd($password),
                'province' => $this->province[$bProvince],
                'city' => $bCity,
                'bankName' => $this->bankCode[$bCode],
                'bankCode' => $bCode,
                'cName' => $userAuth['TrueName'],
                'protocolNo' => $protocolNo,
                'sex' => $oSex,
            );
            $this->cache->set($sKey, $sParam, 15*60); //缓存15分钟
            $tid2 = time();
            //设置发送短信的时间 用于函数mobileAndUser
            $t1  = '2time_sendsms_times_user_'.date('Ymd').'_'.$userID;
            $t2  = '2time_sendsms_times_mobile_'.date('Ymd').'_'.$mobile;
            $this->cache->set($t1, $tid2, 7*60);
            $this->cache->set($t2, $tid2, 7*60);
            echo json_encode(array('flag'=>'10000', 'msg'=>'成功', 'info'=>$rData)); exit;
            
        } else {
            echo json_encode(array('flag'=>'10009', 'msg'=>'短信签约失败')); exit;
        }
        
    }
    
    /**
     *
     * 激活短信签约
     *
     */
    public function activeAddBindCard(){
    
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
    
        $ssid = filter_slashes(trim($this->input->post('ssid', true))); //序列号
        $sCode = filter_slashes(trim($this->input->post('scode', true))); //验证码
        $sKey = filter_slashes(trim($this->input->post('skey', true))); //加密串
         
        //参数是否完整
        if(!$ssid || !$sCode || !$sKey){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }
    
        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }
    
        $userID = $this->_data['userID'] = (int)$this->User_Interact->getUserID();
    
        $xVer = md5($ssid . ENCODE_KEY . 'a2a' . $userID);
        if($xVer != $sKey){
            echo json_encode(array('flag'=>'10003', 'msg'=>'校验错误')); exit;
        }
    
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        $this->_userAnti($userID, strtolower(__FUNCTION__), 10, 3);
    
    
        //$ssid = '1680502';
        //$sCode = '365779';
        //$identnum = '350122198812076538';
        //$mobile = '13599951535';
        $mInfo = $this->cache->get($sKey);
        if(!$mInfo){
            echo json_encode(array('flag'=>'10004', 'msg'=>'信息获取失败')); exit;
        }
        //$bankNum = '6228480068816522772';
        //$bBankCode = '103391013537';
    
        //$serialNo = 'bj'. date('YmdHis') . rand('00000', '99999');
        $param = array(
            //'serial_no' => 'bj'. date('YmdHis') . rand('00000', '99999'), //短信流水序号
            'mobile_code' => $sCode, //手机验证码
            'sms_serial_no' => $ssid, //短信流水序号
            'original_serial_no' => $mInfo['serialNo'],
            //'original_serial_no' => '1680502',
            'capital_mode' => CAPITAL_MODE,
            'merchant_name' => MERCHANT_NAME,
            'id_kind_gb' => 0, //0 身份证
            'id_no' => $mInfo['identnum'], //身份证
            'mobile_tel' => $mInfo['mobile'],
            'order_date' => date('Ymd'),
            'order_time' => date('His'),
            'bank_account' => $mInfo['bankNum'],
            'bank_no' => $mInfo['bBankCode']  //银行支行代码
        );
        //pre($param);
        //请求短信签约接口 确认签约
        logs('|-param-|' . print_r($param, true), $logFile);
        $sRs = $this->User_Model->sign_sms($param);
        logs('|-sRs-|' . print_r($sRs, true), $logFile);
        //pre($sRs);
        if($sRs){
            if(isset($sRs['error_code'])){
                echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_info'])); exit;
            }
    
            if(isset($sRs['error']) && isset($sRs['error_description']) && $sRs['error_description']){
                //echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_info'] . $sRs['error_code'])); exit;
                echo json_encode(array('flag'=>'10011', 'msg'=>$sRs['error_description'])); exit;
            }
    
            if($sRs['data'][0]['status'] ==1 ){
                $userInfo = $this->User_Model->getUserBaseByUserID($userID);
                $iParam = array(
                    'UserID' => $userID,
                    'HsUserID' => $userInfo['HsUserID'],
                    'HsAccount' => $userInfo['HsAccount'],
                    'Province' => $mInfo['province'],
                    'City' => $mInfo['city'],
                    'BankName' => $mInfo['bankName'],
                    'BankNo' => $mInfo['bankCode'],
                    'BranchBank' => $mInfo['bBankCode'],
                    'BranchName' => $mInfo['branchName'],
                    'BankCard' => $mInfo['bankNum'],
                    'Mobile' => $mInfo['mobile'],
                    'NickName' => $mInfo['cName'],
                    'IdentityNumber' => $mInfo['identnum'],
                    'Sex' => $mInfo['sex'],
                    'Password' => $mInfo['password'],
                    'EndValidDate' => '20990101', //默认全部写2099-01-01
                    'Status' => 1, //状态： 1审核通过 2 审核中 3 审核不通过
                    'protocolNo' => $mInfo['protocolNo'], //短信协议号
                    'DataTime' => date('Y-m-d H:i:s')
                );
    
                //pre($iParam);
                $iRs = $this->User_Model->addBindBank($iParam);
                unset($iParam['Password']);
                logs('|-iParam-|' . print_r($iParam, true), $logFile);
                logs('|-iRs-|' . print_r($iRs, true), $logFile);
                if($iRs['flag'] !='10000'){
                    echo json_encode(array('flag'=>'10014', 'msg'=>$iRs['msg'], 'info'=>'')); exit;
                }
    
                //pre($iRs);
                echo json_encode(array('flag'=>'10000', 'msg'=>'银行绑定成功', 'info'=>TRADE_BANK_LIST_URL)); exit;
            } else {
                echo json_encode(array('flag'=>'10012', 'msg'=>'短信签约失败2', 'info'=>'')); exit;
            }
    
    
        } else {
            echo json_encode(array('flag'=>'10009', 'msg'=>'短信签约失败')); exit;
        }
    }
    
    /**
     * 
     * 解绑功能
     * 
     */
    public function unbindBank(){
        
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        
        $bid = (int)$this->input->post('bid', true);
        $password = trim($this->input->post('pswd', true));
        
        if(!$bid){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数缺失')); exit;
        }
        if(!$password){
            echo json_encode(array('flag'=>'10001', 'msg'=>'交易密码必填')); exit;
        }
        
        //是否登录
        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }
        
        $userID = (int)$this->User_Interact->getUserID();
        
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        $this->_userAnti($userID, strtolower(__FUNCTION__), 10, 3);
        
        $userBank = $this->User_Model->getUserBankByUserIDBankID($userID, $bid);
        //pre($userBank);
        if(!$userBank){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数ID错误')); exit;
        }
        
        if($userBank['Master'] == 1){
            echo json_encode(array('flag'=>'10008', 'msg'=>'主卡不能直接解绑')); exit;
        }
        
        //$array('trade_acco'=>'1146');
        $param = array('trade_acco'=>$userBank['HsAccount']);
        logs('|-Param-|' . print_r($param, true), $logFile);
        
        //判断是否符合条件
        $uRs = $this->tradeapi_manage_mdl->unbindBankCard($param);
        logs('|-uRs-|' . print_r($uRs, true), $logFile);
        if($uRs['flag'] != '10000'){
            echo json_encode(array('flag'=>'10005', 'msg'=>$uRs['msg'])); exit;
        }
        
        //调解绑接口
        $unParam = array(
            'trust_way' => TRANSACTION_MODE,
            'trade_acco' => $userBank['HsAccount'], //交易帐号
            'password' => tradePswd($password),
        );
        $unRs = $this->User_Model->unbindBankCard($unParam, $userBank);
        
        if($unRs['flag'] == '10000'){
            echo json_encode(array('flag'=>'10000', 'msg'=>'解绑成功', 'info'=>TRADE_WEB_URL .'/user/center/unbanksuccess')); exit;
        }
        echo json_encode($unRs); exit;
    }

    
    public function riskAsk(){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        
        //是否登录
        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }
        
        $userID = (int)$this->User_Interact->getUserID();
        
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        $this->_userAnti($userID, strtolower(__FUNCTION__), 10, 3);
        
        $p = $this->input->post(NULL, true);
         
        if(!$p){
           echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        
        }
        $eContent = '';
        foreach($p as $pk => $pv){
            if(strpos(trim($pk), 'ques') !==FALSE){
                $eContent .= str_replace('ques', '', $pk) . ':' . $pv . '|';
            }
        }
        
        if(!$eContent){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试2')); exit;
        }
        
        $userInfo = $this->User_Model->getUserBaseByUserID($userID);
         
        if(!isset($userInfo['NickName']) || !$userInfo['NickName']){
            echo json_encode(array('flag'=>'10015', 'msg'=>'请先绑定银行卡', 'info'=>TRADE_ADD_BANK_URL)); exit;
        }
         
        $userAuth = $this->User_Model->getUserAuthByUserID($userID);
         
        if(!isset($userAuth['Status']) || $userAuth['Status'] != '1'){
            echo json_encode(array('flag'=>'10015', 'msg'=>'实名认证未通过', 'info'=>TRADE_ADD_BANK_URL)); exit;
        }
        
        //pre($eContent);
        $param = array(
            'UserID' => $userID,
            'trust_way' => TRANSACTION_MODE,
            'cust_type' => CUST_TYPE, //客户类别(0 机构；1个人）
            'full_name' => $userAuth['TrueName'], //账户全称
            'id_kind_gb' => 0, //证件类别
            'id_no' => $userAuth['IdentityNumber'], //证件类别
            'elig_content' => $eContent  //客户答题内容  格式如：问题编号1:选项编号1|问题编号2:选项编号2|…
        );
        logs('|-Param-|' . print_r($param, true), $logFile);
        $aRs = $this->User_Model->submitRiskAsk($param);
        logs('|-aRs-|' . print_r($aRs, true), $logFile);
        if($aRs['flag']!='10000'){
            echo json_encode(array('flag'=>'10006', 'msg'=>$aRs['msg'])); exit;
        }
        echo json_encode(array('flag'=>'10000', 'msg'=>'提交成功', 'info'=>TRADE_RISK_QUES_RESULT_URL)); exit;
    }
    
    /**
     * 
     * 预留信息
     * 
     */
    public function modReserved(){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        $reserved = filter_slashes(trim($this->input->post('rcontent', true))); //加密串;
        
        //参数是否完整
        if(!$reserved){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }
        
        if(mb_strlen($reserved,'UTF-8')>8){
            echo json_encode(array('flag'=>'10001', 'msg'=>'输入字符长度超过8')); exit;
        }
        
        //是否登录
        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }
        
        $userID = (int)$this->User_Interact->getUserID();
        
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        $this->_userAnti($userID, strtolower(__FUNCTION__), 10, 3);
        
        $param = array(
            'ReservedContent' => $reserved
        );
        logs('|-Param-|' . print_r($param, true), $logFile);
        $uRs = $this->User_Model->updateUserDetailByUserID($userID, $param);
        logs('|-uRs-|' . print_r($uRs, true), $logFile);
        if($uRs['flag'] != '10000'){
            echo json_encode(array('flag'=>'10006', 'msg'=>$uRs['msg'])); exit;
        }
        
        echo json_encode(array('flag'=>'10000', 'msg'=>'预留信息设置成功')); exit;
        
    }
    
    /**
     * 
     * 个人修改页
     * 
     */
    public function personal(){
        
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);

        //是否登录
        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }
        
        $password = $this->input->post('pswd', true);
        if(!$password){
            echo json_encode(array('flag'=>'10001', 'msg'=>'交易密码不为空！')); exit;
        }
        
        $userID = (int)$this->User_Interact->getUserID();
        
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        $this->_userAnti($userID, strtolower(__FUNCTION__), 10, 3);
        
        
        $userInfo = $this->User_Model->getUserBaseByUserID($userID);
        $userDetail = $this->User_Model->getUserDetailByUserID($userID);
        //$userDetail['SendMethod'] = $userDetail['SendMethod'] ? unserialize($userDetail['SendMethod']) : array();
        //$this->_data['userDetail'] = $userDetail;
        $userAuth = $this->User_Model->getUserAuthByUserID($userID);
        
        //用户详情修改
        $dParam = array();
        $xParam = array();
        //修改有效期
        $endValidDate = filter_slashes($this->input->post('endvaliddate',true));
        if($endValidDate != $userAuth['EndValidDate']){
            if(!preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})/', $endValidDate)) {
                echo json_encode(array('flag'=>'10001', 'msg'=>'到期时间格式错误')); exit;
            }
            $tEndValidDate = str_replace('-', '', $endValidDate);
            if($tEndValidDate < date('Ymd')){
                echo json_encode(array('flag'=>'10001', 'msg'=>'所填有效期不能小于当前日期！')); exit;
            }
            $aParam = array('EndValidDate'=>$endValidDate);
            logs('|-aParam-|' . print_r($aParam, true), $logFile);
            $uRs = $this->User_Model->updateUserAuthByUserID($userID, $aParam);
            logs('|-uRs-|' . print_r($uRs, true), $logFile);
            
            if($uRs['flag'] != '10000'){
                echo json_encode(array('flag'=>'10006', 'msg'=>$uRs['msg'])); exit;
            }
            $xParam['id_enddate'] = $tEndValidDate;
            $dParam['DateTime'] = NOW_TIME;
        } else {
            $xParam['id_enddate'] = str_replace('-', '', $endValidDate);
        }
        

        //固定电话
        $tel = filter_slashes($this->input->post('tel',true));
        if($tel != $userDetail['TelPhone']){
            $xParam['home_tel'] = $dParam['TelPhone'] = $tel;
        } else {
            $xParam['home_tel'] = '';
        }
        //通讯地址
        $address = filter_slashes($this->input->post('address',true));
        if($address != $userDetail['Address']){
            $xParam['address'] = $dParam['Address'] = $address;
        } else {
            $xParam['address'] = '';
        }
        //邮编
        $postal = filter_slashes($this->input->post('postal',true));
        if($postal != $userDetail['Postal']){
            $xParam['zipcode'] = $dParam['Postal'] = $postal;
        } else {
            $xParam['zipcode'] ='';
        }
        //邮箱
        $email = filter_slashes($this->input->post('email',true));
        if($email != $userDetail['Email']){
            if(!check_str($email, 'email')){
                echo json_encode(array('flag'=>'10001', 'msg'=>'邮箱格式不正确')); exit;
            }
            $xParam['e_mail'] = $dParam['Email'] = $email;
        } else {
            $xParam['e_mail'] = '';
        }
        //预留信息
        $rContent = filter_slashes($this->input->post('rcontent',true));
        if($rContent != $userDetail['ReservedContent']){
            if(mb_strlen($rContent,'UTF-8')>8){
                echo json_encode(array('flag'=>'10001', 'msg'=>'预留信息长度超过8')); exit;
            }
            $dParam['ReservedContent'] = $rContent;
        }
        
        //职业
        $job = (int)($this->input->post('job',true));
        if($job!='2018'){
            if($job != $userDetail['Job']){
                $xParam['ofund_prof_code'] = $dParam['Job'] = $job;
            }
        } else {
            $xParam['ofund_prof_code'] = '';
        }
        //账单寄送频率
        $sendRate = filter_slashes($this->input->post('sendrate',true));
        if($sendRate != 'sendRate'){
            if($sendRate != $userDetail['SendRate']){
                $xParam['statement_flag'] = $dParam['SendRate'] = $sendRate;
            }
        } else {
            $xParam['statement_flag'] = '';
        }
        //账单寄送方式
        $tsm = array();
        $sendMethod1 = filter_slashes($this->input->post('sendmethod1',true));
        //$sendMethod2 = filter_slashes($this->input->post('sendmethod2',true));
        $sendMethod2 = '';

        if($sendMethod1){
            if(!$email){
                echo json_encode(array('flag'=>'10001', 'msg'=>'电子邮件寄送需先填写电子邮箱')); exit;
            }
            $tsm[] = 1;
        }
        if($sendMethod2){
            if(!$address){
                echo json_encode(array('flag'=>'10001', 'msg'=>'邮件寄送需先填写通讯地址')); exit;
            }
            $tsm[] = 2;
        }
        if($tsm){
            sort($tsm);
        }
        $osm = $userDetail['SendMethod'] ? unserialize($userDetail['SendMethod']) : array();
        if($osm){
            sort($osm);
        }
        if($tsm != $osm){
            $dParam['SendMethod'] = serialize($tsm);
            $xParam['check_send_way'] = '1';
        } else {
            $dParam['SendMethod'] = '';
            $xParam['check_send_way'] = '';
        }
        
        //pre($password); exit;
        
        if($dParam){
            $xParam['trust_way'] = TRANSACTION_MODE;
            $xParam['password'] = tradePswd($password);
            $xParam['trade_acco'] = $userInfo['HsAccount'];
            $xParam['client_full_name'] = $userInfo['NickName'];
            $xParam['client_name'] = $userInfo['NickName'];
            $xParam['id_kind_gb'] = $userAuth['IdentType'];
            $xParam['id_no'] = $userAuth['IdentityNumber'];
            $xParam['trade_account_name'] = $userInfo['NickName']; //交易账号名称
            $dParam['UserID'] = $userID;
            $dParam['DateTime'] = NOW_TIME;
            $rs = $this->User_Model->modUserPersonal($dParam, $xParam);
            logs('|-dParam-|' . print_r($dParam, true), $logFile);
            echo json_encode($rs); exit;
            
        }
        echo json_encode(array('flag'=>'10000', 'msg'=>'用户个人信息修改成功')); exit;
        
    }
    /**
     * 
     * 设置主帐号
     * 
     */
    public function setMaster(){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        
        //是否登录
        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }
        
        $bid = (int)$this->input->post('bid', true);
        $password = $this->input->post('pswd', true);
        if(!$bid){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误，请重试')); exit;
        }
        
        $userID = (int)$this->User_Interact->getUserID();
        
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        $this->_userAnti($userID, strtolower(__FUNCTION__), 10, 3);
        
        $userBank = $this->User_Model->getUserBankByUserIDBankID($userID, $bid);
        //pre($userBank);
        if(!$userBank){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数ID错误')); exit;
        }
        
        if($userBank['Master'] == 1){
            echo json_encode(array('flag'=>'10005', 'msg'=>'此卡为主卡无须再修改')); exit;
        }
        
        //获取主帐号信息
        $userMasterBank = $this->User_Model->getUserBankMasterByUserID($userID);
        if(!$userMasterBank){
            echo json_encode(array('flag'=>'10006', 'msg'=>'主卡不存在')); exit;
        }
        
        
        $userInfo = $this->User_Model->getUserBaseByUserID($userID);
        //获取TA帐号
        $userAccount = $this->tradeapi_manage_mdl->fundBankAccountSearch('', $userInfo['HsAccount'], $userInfo['HsUserID']);
        $taAccount = $userAccount['ta_acco'];
        
        //pre($password);
        //pre(tradePswd($password));
        //exit;
        $sParam = array(
            'UserID' => $userID,
            'bid' => $bid,
            'trade_acco' => $userBank['HsAccount'],
            'ta_acco' =>$taAccount,
            'password' => tradePswd($password),
            'oldBid' =>$userMasterBank['BankID'],
            'origin_tradeacco'=>$userMasterBank['HsAccount'],
        );
        $xParam = $sParam;
        unset($xParam['password']);
        logs('|-xParam-|' . print_r($xParam, true), $logFile);
        $sRs = $this->User_Model->setMasterCard($sParam);
        logs('|-sRs-|' . print_r($sRs, true), $logFile);
        echo json_encode($sRs); exit;
        
    }

    /*
    * 验证登录密码修改
    * */
    public function ajaxLogin(){
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);

        //是否登录
        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }


        $navpwd = filter_slashes(trim($this->input->post('navpwd', true))); //原密码
        $newpwd = filter_slashes(trim($this->input->post('newpwd', true))); //新密码
        $surepwd = filter_slashes(trim($this->input->post('surepwd', true))); //确认密码

        //空值检测
        if(!$navpwd || !$newpwd || !$surepwd){
            echo json_encode(array('code'=>'10001', 'msg'=>'参数不全')); exit;
        }

        //检测确认密码是否与新密码一致
        if($newpwd!==$surepwd){
            echo json_encode(array('code'=>'10002', 'msg'=>'密码与确认密码不一致')); exit;
        }

        //检测cookie中是否存在所需的参数
        if(!isset($_COOKIE['trade']['userId'])){
            echo json_encode(array('code'=>'10003', 'msg'=>'cookie参数不全'.$_COOKIE['trade']['userId'])); exit;
        }
        //检测新密码是否符合规定
        $checkPwd=checkPassword($newpwd);
        if($checkPwd['flag'] != '10000'){
            echo json_encode($checkPwd); exit;
        }

        $UserID=$_COOKIE['trade']['userId'];
        if(!isset($UserID)){
            echo json_encode(array('code'=>'10007', 'msg'=>'数据库无数据！')); exit;
        }

        //修改旧密码
        $change=$this->User_Model->repeatPwd($newpwd,$navpwd,$UserID);
        logs('|-修改旧密码$change- repeatPwd -|' . $UserID.print_r($change,true), $logFile);
        //返回修改成功标识
        if($change['flag']!='10000'){
            echo json_encode(array('code'=>$change['flag'], 'msg'=>$change['msg'])); exit;
        }else{
            echo json_encode(array('code'=>'10000', 'msg'=>'修改密码成功，请重新登录！')); exit;
        }
    }
    /*
         * 获取交易密码短信
         * */
    public function getTradeMobileCode(){
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        //是否登录
        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }


        $mobile = filter_slashes(trim($this->input->post('mobile', true)));
        $userID = filter_slashes(trim($this->input->post('uid', true)));
        $i = (int)filter_slashes(trim($this->input->post('fd', true)));

        $tid = $this->input->post('tid', true);
        $stid = $this->input->post('stid', true);
        $mkid = $this->input->post('mkid', true);
        $type=3;//重置交易密码type

        logs('|-交易密码短信$Param-|' . '|-$mobile-|'.$mobile.'|-$userID-|'.$userID.'|-$i-|'.$i
            .'|-$tid-|'.$tid.'|-$stid-|'.$stid.'|-$mkid-|'.$mkid.'|-$type-|'.$type, $logFile);

        //参数是否完整
        if(!$mobile || !$userID || !$tid  || !$stid || !$mkid || !isset($i)){
            echo json_encode(array('flag'=>'10018', 'msg'=>'参数错误，请重试','info'=>array($mobile,$userID,$tid,$stid,$mkid))); exit;
        }

        //页面过期, 半小时
        isRequestTimeout($stid, 60*30, '请求已失效，请刷新页面重试');
        //获取信息进行
        $res=$this->User_Model->getUserBankByUserID($userID);
        logs('|-mcRs-|' . print_r($res, true), $logFile);

        if(!$res){
            echo json_encode(array('flag'=>'10019', 'msg'=>'读取参数错误19')); exit;
        }
        if($res[$i]['Mobile']!=$mobile){
            echo json_encode(array('flag'=>'10020', 'msg'=>'读取参数错误20')); exit;
        }

        //验证非法链接
        $m_name='modpswd_'.$userID.$tid.substr(md5(ENCODE_KEY . md5($userID) ), 8, 6);
        $hmkid=$this->cache->get($m_name);
        if($hmkid!=$mkid){
            echo json_encode(array('flag'=>'10021', 'msg'=>'非法链接不得进入!')); exit;
        }

        //短信是否在3分钟内有发送过
        $mauRs = mobileAndUser($mobile);
        if($mauRs['flag'] == '10000'){
            echo json_encode(array('flag'=>'10022', 'msg'=>$mauRs['msg'])); exit;
        }

        $ip = $this->input->ip_address();
        logs('|-ip-|' . $ip . '|-mobile-|' . $mobile . '|-type-|' . $type . '|-$userID-|' . $userID . '|||' .PHP_EOL, $logFile);
        //检查手机
        $cParam = array('Mobile'=>$mobile, 'BankNo'=>$res[$i]['BankNo'],'UserID'=>$userID);
        $cRs =  $this->User_Model->checkBankExist($cParam);
        logs('|-检查手机$cParam-|' . print_r($cParam, true). '|-cRs-|' . print_r($cRs, true) . PHP_EOL, $logFile);

        if ($cRs['flag'] == '10000') { //已注册
            $uParam = array('Type' => 2, 'Mobile' => $mobile, 'IP' => $ip,'UserID'=>$userID);
            //获取找回密码的手机口令
            $umRs = $this->User_Model->updateUserVerifyCode($uParam,array('BankNo'=>$res[$i]['BankNo']));
            logs('|-获取找回密码的手机口令$umRs-|' . print_r($umRs, true) . PHP_EOL, $logFile);

            if ($umRs['Code'] == '00') {

                $mobilekey = $umRs['Record']['CheckCode'];  //新的验证码
                $aParam = array(
                    'mobile' => $mobile, //手机号
                    'code' => $mobilekey, //验证码
                    'userID' => $userID, //用户ID
                    'IP' => $ip, //ip
                    'sType' => $type, //1 注册短信，2 找回密码 3 交易密码 4 修改手机，5 绑定手机 6，提现
                    'sign' => 'bj_tradepwd_check'
                );

                //发送短信
                $svcRs = $this->sendVerifyCode($aParam);
                logs('|-条件$aParam-|' . print_r($aParam, true) . '|-发送短信svcRs-|' . print_r($svcRs, true) . PHP_EOL, $logFile);
                $svcRs['flag']='10000';
                if ($svcRs['flag'] == '10000') {
                    //重塑mkid
                    $ctid=time();
                    echo json_encode(array('flag' => '10000', 'msg' => $svcRs['msg'], 'info' => array( 'ctid' => $ctid, 'mkid' => $mkid)));
                    exit;
                } else {
                    echo json_encode($svcRs);
                    exit;
                }
            } else {
                echo json_encode(array('flag' => '10024', 'msg' => $umRs['Msg']));
                exit;
            }
        } else {
            echo json_encode(array('flag' => '10023', 'msg' => '手机号未注册'));
            exit;
        }
    }
    //修改交易密码
    public function ajaxTrade(){
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
        //是否登录
        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }

        //防刷3秒， 10次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        //接收信息
        $mobile = filter_slashes(trim($this->input->post('mobile', true))); //手机号
        $bankcode = filter_slashes(trim($this->input->post('bankcode', true))); //银行代码
        $banknum = filter_slashes(trim($this->input->post('banknum', true))); //银行账号
        $idcard = filter_slashes(trim($this->input->post('idcard', true))); //身份证号
        $turename = filter_slashes(trim($this->input->post('turename', true))); //真实姓名
        $userID = filter_slashes(trim($this->input->post('uid', true))); //用户id

        $tid = $this->input->post('tid', true);//时间
        $mkid = $this->input->post('mkid', true);//页面验证随机码
        //先进行空值校验
        if(!$mobile || !$bankcode || !$banknum || !$idcard || !$turename || !$userID || !$tid || !$mkid){
            echo json_encode(array('flag'=>'10008', 'msg'=>'参数错误，请重试!')); exit;
        }
        logs('|-修改交易密码$Param-|' . '|-$mobile-|'.$mobile.'|-$bankcode-|'.$bankcode.'|-$banknum-|'.$banknum
            .'|-$idcard-|'.$idcard.'|-$turename-|'.$turename.'|-$userID-|'.$userID.'|-$tid-|'.$tid.'|-$mkid-|'.$mkid, $logFile);
        //验证超时
        //页面过期, 半小时
        isRequestTimeout($tid, 60*30, '请求已失效，请刷新页面重试');
        //验证非法链接
        $m_name='modpswd_'.$userID.$tid.substr(md5(ENCODE_KEY . md5($userID) ), 8, 6);
        $hmkid=$this->cache->get($m_name);
        if($hmkid!=$mkid){
            echo json_encode(array('flag'=>'10009', 'msg'=>'非法链接不得进入!')); exit;
        }

        //以银行卡号，身份证号，真实姓名，手机号进行验证身份
        //以userid在认证表查询并与真实姓名与身份证号进行对比
        $res=$this->User_Model->getUserAuthByUserID($userID);
        if(!$res){
            echo json_encode(array('flag'=>'10010', 'msg'=>'数据获取错误！')); exit;
        }
        logs('|-trParam-|' . print_r($res, true), $logFile);
        //用户状态校验
        if(isset($res['Status']) && $res['Status']!=1){
            echo json_encode(array('flag'=>'10011', 'msg'=>'您的资料尚未通过认证请耐心等候！'));exit;
        }
        //数据对比
        if(!isset($res['TrueName']) || $res['TrueName']!=$turename || !isset($res['IdentityNumber']) || $res['IdentityNumber']!=$idcard){
            echo json_encode(array('flag'=>'10012', 'msg'=>'您的资料与原始资料不符，请核实！'));exit;
        }
        //使用userid进行mobile搜索并对比
        $res2=$this->User_Model->getUserBankByUserID($userID);
        logs('|-修改交易密码$strParam-|' . print_r($res2, true), $logFile);
        if(!$res2){
            echo json_encode(array('flag'=>'10013', 'msg'=>'数据获取错误2！')); exit;
        }
        for($i=0;$i<count($res2);$i++){
            if($res2[$i]['BankNo']==$bankcode){
                break;
            }else if($i==count($res2)-1){
                echo json_encode(array('flag'=>'10014', 'msg'=>'银行卡数据获取错误3！')); exit;
            }
        }
        if(!isset($res2[$i]['Mobile']) || $res2[$i]['Mobile']!=$mobile ||!isset($res2[$i]['BankNo'])
            || $res2[$i]['BankNo']!=$bankcode || !isset($res2[$i]['BankCard']) || $res2[$i]['BankCard']!=$banknum
            || !isset($res2[$i]['Status']) || $res2[$i]['Status']!='1'){
            echo json_encode(array('flag'=>'10015', 'msg'=>'您的银行资料与原始资料不符，请核实！')); exit;
        }

        //更新页面连接
        $stid=time();
        $mkid= substr(md5(ENCODE_KEY . substr(md5($stid), -5) . 'mobile'), 8, 16);
        $this->cache->set($m_name, '', 1);
        $this->cache->set($m_name, $mkid, 30*60); //页面合法链接验证储存
        //验证通过之后跳转进入下一页面
        $url=$this->_data['centerUrl'].'/tradeMobile?mkid='.$mkid.
            '&stid='.$stid.'&uid='.$userID.'&mobile='.$mobile.'&tid='.$tid.'&fd='.$i;
        echo json_encode(array('flag'=>'10000', 'msg'=>$url)); exit;

    }

    /*
  * 交易密码修改短信验证
  * */
    public function ajaxTrademobile(){
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);

        //是否登录
        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }

        //防刷3秒十次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        //接收信息
        $mobile = filter_slashes(trim($this->input->post('mobile', true)));
        $userID = filter_slashes(trim($this->input->post('uid', true)));
        $dcode = filter_slashes(trim($this->input->post('dcode', true)));

        $tid = $this->input->post('tid', true);
        $stid = $this->input->post('stid', true);
        $ctid = $this->input->post('ctid', true);
        $mkid = $this->input->post('mkid', true);
        $ip=$this->input->ip_address();
        //空值校验
        if(!$mobile || !$userID || !$tid || !$stid || !$ctid || !$mkid){
            echo json_encode(array('flag'=>'10025', 'msg'=>'参数错误，请重试')); exit;
        }
        logs('|-$mobile-|' . $mobile .'|-$userID-|' . $userID.'|-$tid-|' . $tid.'|-$stid-|' .
            $stid.'|-$ctid-|' . $ctid.'|-$mkid-|' . $mkid .'|-$ip-|' . $ip, $logFile);

        //页面链接过期校验
        isRequestTimeout($stid, 60*30, '请求已失效，请刷新页面重试');
        //页面非法链接校验
        $m_name='modpswd_'.$userID.$tid.substr(md5(ENCODE_KEY . md5($userID) ), 8, 6);
        $hmkid=$this->cache->get($m_name);
        if(!$hmkid || $hmkid!=$mkid){
            echo json_encode(array('flag'=>'10026', 'msg'=>'非法链接不得进入!')); exit;
        }
        //手机验证码校验
        $m_key = SEND_SMS_SIGN . 'bj_tradepwd_check' . $userID  . $mobile;
        $vCode = $this->cache->get($m_key);
        if(!$vCode){
            echo json_encode(array('flag'=>'10027', 'msg'=>'短信验证码不存在或已过期')); exit;
        }
        if($dcode != $vCode){
            echo json_encode(array('flag'=>'10028', 'msg'=>'短信验证码错误')); exit;
        }
        //验证用户表中的短信验证码
        $cParam = array(
            'Type' => 2, //type=1修改手机号 type=2 找回密码
            'UserID' => $userID,
            'Mobile' => $mobile,
            'IP' => $ip,
            'CheckCode' => $dcode
        );
        $cRs = $this->User_Model->checkUserCode($cParam);
        logs('|-验证用户表中的短信验证码$cParam-|' . print_r($cParam, true).'|-结果$cRs-|' . print_r($cRs, true), $logFile);

        //校验成功赋值新的页面验证链接
        if($cRs['Code'] == '00'){
            $ttid=time();
            $mkid= substr(md5(ENCODE_KEY . substr(md5($ttid), -5) . 'mobile'), 8, 16);
            $this->cache->set($m_name, $mkid, 30*60); //页面合法链接验证储存
            $url=$this->_data['centerUrl'].'/tradeNew?mkid='.$mkid.
                '&ttid='.$ttid.'&uid='.$userID.'&mobile='.$mobile.'&tid='.$tid;
            //返回实用链接
            echo json_encode(array('flag'=>'10000', 'msg'=>$cRs['Msg'] , 'url' => $url)); exit;
        } else {
            echo json_encode(array('flag'=>'10030', 'msg'=>$cRs['Msg'] )) ; exit;
        }
    }

    /*
   * 交易密码修改
   * */
    public function ajaxTradenew(){

        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
        //是否登录
        $r = $this->User_Interact->ajaxPassportCheck();
        if($r['flag'] != '10000'){
            echo json_encode(array('flag'=>'11001', 'msg'=>$r['msg'])); exit;
        }

        //防刷3秒十次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 10, 3);
        //接收信息
        $userID = filter_slashes(trim($this->input->post('uid', true)));
        $pwd = filter_slashes(trim($this->input->post('pwd', true)));
        $acco= filter_slashes(trim($this->input->post('acco', true)));

        $tid = $this->input->post('tid', true);
        $ttid = $this->input->post('ttid', true);
        $mkid = $this->input->post('mkid', true);
        //空值校验
        if(!$userID || !$tid || !$ttid  || !$mkid || !$acco){
            echo json_encode(array('flag'=>'10033', 'msg'=>'页面参数错误，请重试')); exit;
        }
        logs('|交易密码修改-$userID-|' . $userID .'|-$acco-|' . $acco .'|-$tid-|' . $tid.'|-$ttid-|' . $ttid.'|-$mkid-|' .
            $mkid, $logFile);
        //页面链接过期校验
        isRequestTimeout($ttid, 60*30, '请求已失效，请刷新页面重试');
        //页面非法链接校验
        $m_name='modpswd_'.$userID.$tid.substr(md5(ENCODE_KEY . md5($userID) ), 8, 6);
        $hmkid=$this->cache->get($m_name);
        if(!$hmkid || $hmkid!=$mkid){
            echo json_encode(array('flag'=>'10034', 'msg'=>'非法链接不得进入!')); exit;
        }
        $userInfo = $this->User_Model->getUserBaseByUserID($userID);

        if(!$userInfo || $userInfo['HsAccount']!=$acco){
            echo json_encode(array('flag'=>'10035', 'msg'=>'输入资料与登录资料不符，操作不予通过!')); exit;
        }

        $res=$this->User_Model->getUserAuthByUserID($userID);
        if(!$res || !$res['IdentityNumber']){
            echo json_encode(array('flag'=>'10036', 'msg'=>'认证数据获取错误！')); exit;
        }
        logs('|-$res-|' . print_r($res,true), $logFile);

        //验证密码是否符合要求
        $checkPwd=tradeCheckpwd($pwd,$res['IdentityNumber']);

        if($checkPwd['flag'] != '10000'){
            echo json_encode($checkPwd); exit;
        }
        //调用伯嘉接口
        //清密
        $param2 = array(
            'trust_way' => TRANSACTION_MODE,
            'cust_type' => CUST_TYPE,
            'trade_acco' => $acco,
            'password' => md5('E1234567' . PASSPORT_AUTH_KEY),
        );
        $sign='tradepassword_clear_acct';
        $re = $this->User_Model->upSimpchange($param2,$sign);
        if(!$re ||!isset($re['data'][0]['success_type']) || $re['data'][0]['success_type']!=0){
            logs('|-清密 tradepassword_clear_acct $res-|' . print_r($param2,true).'|-$re-|' . print_r($re,true), $logFile);
            echo json_encode(array('flag'=>'10037', 'msg'=>'修改失败!','info'=>$re['data'][0]['error_info'])); exit;
        }
        logs('|-清密 tradepassword_clear_acct  $res-|' . print_r($param2,true).'|-$re-|' . print_r($re,true), $logFile);

        //修改密码
        $param=array(
            'trust_way'=>TRANSACTION_MODE,//'0-柜台委托 1-电话委托2-网上委托3-自助委托4-传真委托5-其他委托7-手机委托8-CRM委托'
            'trade_acco'=>$acco,
            'new_password'=>tradePswd($pwd)
        );
        $sign='tradepassword_mod_acct';
        $res=$this->User_Model->upSimpchange($param,$sign);

        //输入参数完成
        if(!$res || $res['data'][0]['success_type']!=0){
            logs('|-修改密码 tradepassword_mod_acct   $param-|'.print_r($param,true).print_r($res,true), $logFile);
            echo json_encode(array('flag'=>'10038', 'msg'=>'修改失败!')); exit;
        }
        logs('|-修改密码  tradepassword_mod_acct  $param-|'.print_r($param,true).print_r($res,true), $logFile);
        //修改keystr
        $this->User_Model->updateUserByUserID($userID,'',1);
        logs('|交易密码修改-$userID-|' . $userID .'|-$acco-|' . $acco .'|-$tid-|' . $tid.'|-$ttid-|' . $ttid.'|-$mkid-|' .
            $mkid, $logFile);

        $ttid=time();
        $mkid= substr(md5(ENCODE_KEY . substr(md5($ttid), -5) . 'mobile'), 8, 16);
        $this->cache->set($m_name, $mkid, 30*60); //页面合法链接验证储存
        $url=$this->_data['centerUrl'].'/tradeSuccess';
        echo json_encode(array('flag'=>'10000', 'msg'=>'修改成功!','url'=>$url)); exit;
    }
}
?>