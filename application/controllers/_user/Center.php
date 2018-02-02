<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Center extends MY_Controller {

    private $user = array();
    private $bank = array(
	        '002'=>array('pic'=>'ICBC.jpg', 'name'=>'工商银行'), 
	        '005'=>array('pic'=>'CCB.jpg', 'name'=>'建设银行'), 
	        '010'=>array('pic'=>'SB.jpg', 'name'=>'浦发银行'),
	        '009'=>array('pic'=>'CEB.jpg', 'name'=>'光大银行'), 
	        '014'=>array('pic'=>'CMB.jpg', 'name'=>'民生银行'), 
	        '003'=>array('pic'=>'ACB.jpg', 'name'=>'农业银行'), 
	        '920'=>array('pic'=>'PB.jpg', 'name'=>'平安银行'), 
	        '004'=>array('pic'=>'CB.jpg', 'name'=>'中国银行'), 
	        '007'=>array('pic'=>'CBM.jpg', 'name'=>'招商银行'), 
	        '015'=>array('pic'=>'CIB.jpg', 'name'=>'中信银行'), 
	        '012'=>array('pic'=>'HB.jpg', 'name'=>'华夏银行'), 
	        '011'=>array('pic'=>'ICB.jpg', 'name'=>'兴业银行'), 
	        '006'=>array('pic'=>'BOC.jpg', 'name'=>'交通银行'), 
	        '016'=>array('pic'=>'GDB.jpg', 'name'=>'广东发展银行'), 
	    );
    
    public function __construct() {
        parent::__construct();

        $this->load->model('_user/User_Interact');
        
        $this->_data['headerCss'] = '';
        $this->_data['title'] = '我的账户_伯嘉基金网';
        $this->_data['keywords'] = SEO_KEYWORDS;
        $this->_data['description'] = SEO_DESCRIPTION;
        $this->_data['nav'] = 'wdzh';
        
        $this->_data['centerUrl'] = TRADE_WEB_URL . '/user/center';
        $this->_data['ajaxUrl'] = TRADE_WEB_URL . '/user/ajaxcenter';
        
        $this->_data['userID'] = (int)$this->User_Interact->getUserID();
        $this->_data['nickName'] = filter_slashes($this->User_Interact->getNickName());
        //判断权限
        $this->User_Interact->passportCheck();
		$this->load->library('Buyfunds_mem',NULL,'buyfunds_mem');
		$this->cache = $this->buyfunds_mem;
    }
    
    /**
     *
     * 首页
     *
     */
	public function index() {


	    $this->_data['subnav'] = 'wdzc';
	    $this->_data['sidebar'] = 'wdzc';
	    
	    $datadictionary = config_item('datadictionary');
	    $aRisk = $datadictionary['C00010']; //投资人风险承受能力
	    unset($datadictionary);
	    
	    $userID = $this->_data['userID'];

	    $userInfo = $this->User_Model->getUserBaseByUserID($userID);
		
	    //pre($userInfo);
        if(!$userInfo){
            exit('用户信息错误！');
        }
		
        /* 需要通过注册获取的数据  */
        $trade_acco = $userInfo['HsAccount'];//交易编号
        $client_id = $userInfo['HsUserID'];//用户编号
		
		if($trade_acco){
			//份额查询
			$applyqry = $this->tradeapi_manage_mdl->shareQry(array('client_id'=>$client_id),50);
			if($applyqry)
				$this->_data['data'] = $this->tradeapi_manage_mdl->position_income($applyqry,1);
		}
		
		
        $userDetail = $this->User_Model->getUserDetailByUserID($userID);
        
        //银行卡信息
        $this->_data['bankCount'] = $userBankCount = $this->User_Model->getUserBankCountByUserID($userID);
	    
        $isRisk = 0; //是否需要风险评估.0不需要，1需要  默认保守型
        if($userBankCount == 1 && ( $userDetail['RiskBear']=='' || $userDetail['PaperID']=='') ){
            $eContent = '7:1|8:1|10:5|11:1|12:3|13:5|14:4|15:5|16:5|'; //保守型答卷
            $userAuth = $this->User_Model->getUserAuthByUserID($userID);
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
            //logs('|-Param-|' . print_r($param, true), $logFile);
            $aRs = $this->User_Model->submitRiskAsk($param);
            //logs('|-aRs-|' . print_r($aRs, true), $logFile);
            //$this->User_Model->updateUserDetailByUserID($userID, array('RiskBear'=>1));
            $isRisk = 1;
        }

        
        //pre($userDetail);
        //pre($userInfo);
        
        $this->_data['tid'] = time();
        //验证手机号
        $this->_data['mkid'] = substr(md5(ENCODE_KEY . substr(md5($this->_data['tid']), -5) . 'mobile'), 8, 16);
        
	    //$this->_data['userHead'] = getUserHead($userID, 96);

	    $this->_data['isRisk'] = $isRisk;
	    $this->_data['aRisk'] = $aRisk;
	    $this->_data['userInfo'] = $userInfo;
	    $this->_data['userDetail'] = $userDetail;
	    $this->load->view('_user/center.html', $this->_data);
	}
	
	
	/**
	 * 
	 * 开户时绑定
	 * 
	 */
	public function regBindBank(){
	    
	    $userID = $this->_data['userID'];
	    //拥有的银行卡数
	    $this->_data['userBankCount'] = $userBankCount = $this->User_Model->getUserBankCountByUserID($userID);
	     
	    if($userBankCount>0){
	        cnfol_location(TRADE_BANK_LIST_URL);
	        exit;
	    }
	     
	    
	    //phpinfo();
	    //pre($_SERVER);
	    $this->_data['bank'] = $this->bank;

	    $tid = time();
	    $this->_data['tid'] = $tid;
	    //验证手机号
	    $this->_data['kid'] = substr(md5(ENCODE_KEY . substr(md5($tid), -5) . 'regbindbank'), 8, 16);
	    
	    $this->_data['title'] = '免费开户_伯嘉基金网';
	    $this->load->view('_user/reg_bind.html', $this->_data);
	}
	
	/**
	 * 
	 * 再次绑定银行卡
	 * 
	 */
	public function addBank(){

	    $this->_data['subnav'] = 'yhkgl';
	    $this->_data['sidebar'] = 'wdzc';
	    $this->_data['title'] = '新增银行卡_账户管理_伯嘉基金网';
	    
	    $userID = $this->_data['userID'];
	    //拥有的银行卡数
	    $this->_data['userBankCount'] = $userBankCount = $this->User_Model->getUserBankCountByUserID($userID);
	     
	    //pre($userBankCount); exit;

	    if($userBankCount<1){ //第一次绑定银行
	        
    	    $userID = $this->_data['userID'];
    	    //拥有的银行卡数
    	    $this->_data['userBankCount'] = $userBankCount = $this->User_Model->getUserBankCountByUserID($userID);
    	     
    	    if($userBankCount>0){
    	        cnfol_location(TRADE_BANK_LIST_URL);
    	        exit;
    	    }
    	     
    	    
    	    //phpinfo();
    	    //pre($_SERVER);
    	    $this->_data['bank'] = $this->bank;
    
    	    $tid = time();
    	    $this->_data['tid'] = $tid;
    	    //验证手机号
    	    $this->_data['kid'] = substr(md5(ENCODE_KEY . substr(md5($tid), -5) . 'regbindbank'), 8, 16);
    	    
    	    $this->load->view('_user/faddbank.html', $this->_data);
	    } else {
	        $this->_data['userAuth'] = $userAuth = $this->User_Model->getUserAuthByUserID($userID);
	        
	        if(!isset($userAuth['Status']) || $userAuth['Status'] != '1'){
	            errorJump('请先通过实名认证', TRADE_ADD_BANK_URL);
	        }
	         
	        //获取银行卡信息
	        $this->_data['userBank'] = $userBank = $this->User_Model->getUserBankByUserID($userID);
	        //phpinfo();
	        //pre($_SERVER);
	        $bank = $this->bank;
	         
	        if($userBank){
	            foreach($userBank as $uk=>$uv){
	                unset($bank[$uv['BankNo']]);
	            }
	        }
	        $this->_data['bank'] = $bank;
	        $tid = time();
	        $this->_data['tid'] = $tid;
	        //验证手机号
	        $this->_data['kid'] = substr(md5(ENCODE_KEY . substr(md5($tid), -5) . 'addbank'), 8, 16);

	        //$this->load->view('_user/addbank.html', $this->_data);
	        $this->load->view('_user/addbank.html', $this->_data);
	    }


	}
	
	/**
	 *
	 * 银行卡管理页面
	 *
	 */
	public function bankList(){
	    $userID = $this->_data['userID'];
	     
	    //拥有的银行卡数
	    $this->_data['userBankCount'] = $userBankCount = $this->User_Model->getUserBankCountByUserID($userID);
	     
	    if($userBankCount<1){
	        cnfol_location(TRADE_ADD_BANK_URL);
	        exit;
	    }
	     
	    $this->_data['subnav'] = 'yhkgl';
	    $this->_data['sidebar'] = 'wdzc';
	    $this->_data['title'] = '银行卡管理_账户管理_伯嘉基金网';
	
	    //获取银行卡信息
	    $this->_data['userBank'] = $userBank = $this->User_Model->getUserBankByUserID($userID);
	    //pre($userBank);
	    $this->load->view('_user/banklist.html', $this->_data);
	}
	
	/**
	 *
	 * 绑定银行成功
	 *
	 */
	public function bankSuccess(){
	
	    $this->_data['subnav'] = 'yhkgl';
	    $this->_data['sidebar'] = 'wdzc';
	    $this->_data['title'] = '新增银行卡_账户管理_伯嘉基金网';
	
	    $this->load->view('_user/banksuccess.html', $this->_data);
	}
	/**
	 *
	 * 解绑银行成功
	 *
	 */
	public function unBankSuccess(){
	
	    $this->_data['subnav'] = 'yhkgl';
	    $this->_data['sidebar'] = 'wdzc';
	    $this->_data['title'] = '解绑银行卡_账户管理_伯嘉基金网';
	
	    $this->load->view('_user/unbanksuccess.html', $this->_data);
	}
	
	/**
	 *
	 * 解绑页
	 *
	 */
	public function unBindBank($bid='99999999999'){
	    $userID = $this->_data['userID'];
	    //拥有的银行卡数
	    $this->_data['userBankCount'] = $userBankCount = $this->User_Model->getUserBankCountByUserID($userID);
	
	    if($userBankCount<1){
	        errorJump('只剩一张银行卡不能解绑',TRADE_BANK_LIST_URL);
	        exit;
	    }
	     
	    $bid = (int)$bid;
	
	    $this->_data['userBank'] = $userBank = $this->User_Model->getUserBankByUserIDBankID($userID, $bid);
	    if(!$userBank){
	        errorJump('参数错误',TRADE_BANK_LIST_URL);
	        exit;
	    }
	    
	    if($userBank['Master'] == 1){
	        errorJump('主卡不能解绑', TRADE_BANK_LIST_URL);
	        exit;
	    }
	    
	    $this->_data['userAuth'] = $userAuth = $this->User_Model->getUserAuthByUserID($userID);
	
	    if(!isset($userAuth['Status']) || $userAuth['Status'] != '1'){
	        $this->_data['error'] = 1;
	        $this->_data['errorMsg'] = '请先通过实名认证';
	        $this->_data['errorUrl'] = TRADE_ADD_BANK_URL;
	    }
	     
	    $this->_data['bankInfo'] = $this->bank[$userBank['BankNo']];
	     
	    $this->_data['subnav'] = 'yhkgl';
	    $this->_data['sidebar'] = 'wdzc';
	    $this->_data['title'] = '解绑银行卡_账户管理_伯嘉基金网';
	     
	    $this->load->view('_user/unbindbank.html', $this->_data);
	}

	/**
	 *
	 * 头像修改
	 *
	 */
	public function chgHeadImg(){
	
	    //判断是否已登录
	    $apc = $this->User_Interact->ajaxPassportCheck();
	
	    if($apc['flag'] != '10000'){
	        echo arrayErrorJump($apc);exit;
	    }
	     
	    $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
	     
	    $userID = $this->_data['userID'] = (int)$this->User_Interact->getUserID();
	
	    //请求源头是否合法
	    $refererUrl = $this->input->server('HTTP_REFERER', true);
	    $domain = get_host_domain($refererUrl);
	    if(!is_allowed_login_domain($domain) ){
	        //echo $this->jsonTranslate(array('flag'=>'10005', 'msg'=>'请求错误00，请重试')); exit;
	        echo arrayErrorJump(array('flag'=>'10005', 'msg'=>'请求错误00，请重试'));exit;
	        //echo $this->jsonTranslate(array('flag'=>'10005', 'msg'=>'请求错误00，请重试')); exit;
	    }
	
	    $ip = $this->input->ip_address();
	
	    logs('|-ip-|' . $ip, $logFile);
	
	    //防刷
	    $saRs = simpleAnti($ip, strtolower(__FUNCTION__), 15, 3);
	
	    if($saRs['flag'] != '10000'){
	        //echo $this->jsonTranslate($saRs); exit;
	        echo arrayErrorJump($saRs);exit;
	    }
	
	    if(!isset($_FILES) || !isset($_FILES['headFile'])){
	        $data = array('flag'=>'10001', 'msg'=>'请上传图片', 'info'=>'');
	        echo arrayErrorJump($data);exit;
	
	    }
	    $imgFile = $_FILES['headFile'];
	
	    $upError = array(
	        '0' => '正常',
	        '1' => '上传的文件超过最大值',
	        '2' => '上传文件的大小超过HTML最大值',
	        '3' => '文件只有部分被上传',
	        '4' => '没有文件被上传',
	        '6' => '找不到临时文件夹',
	        '7' => '文件写入失败'
	    );
	    if($imgFile['error'] != '0'){
	        $data = array('flag'=>'10002', 'msg'=>$upError[$imgFile['error']], 'info'=>'');
	        echo arrayErrorJump($data);exit;
	    }
	
	    $uRs = uploadImg($userID, $imgFile);
	    if($uRs['flag'] != '10000'){
	        echo arrayErrorJump($uRs);exit;
	        //errorJump($uRs['msg'], TRADE_WEB_URL);
	    }
	
	    $chRs = createHeadImg($userID, $uRs['info']['source'], $uRs['info']['aImgSize']);
	    echo arrayErrorJump($chRs);exit;
	    //errorJump($chRs['msg'], TRADE_WEB_URL);

	}

    
	/**
	 * 
	 * 风险测评
	 * 
	 */
	public function riskEvaluation(){
	    $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
	    $userID = $this->_data['userID'];
	    $userInfo = $this->User_Model->getUserBaseByUserID($userID);
	    
	    $this->_data['error'] = 0;
	    $this->_data['errorMsg'] = '';
	    $this->_data['errorUrl'] = '';
	    
	    if(!isset($userInfo['NickName']) || !$userInfo['NickName']){
	        $this->_data['error'] = 1;
	        $this->_data['errorMsg'] = '请先绑定银行卡';
	        $this->_data['errorUrl'] = TRADE_ADD_BANK_URL;
	    }
	    
	    $userAuth = $this->User_Model->getUserAuthByUserID($userID);
	    
	    if(!isset($userAuth['Status']) || $userAuth['Status'] != '1'){
	        $this->_data['error'] = 1;
	        $this->_data['errorMsg'] = '请先通过实名认证';
	        $this->_data['errorUrl'] = TRADE_ADD_BANK_URL;
	    }
        //pre($userInfo);

        $this->_data['title'] = '风险评测_账户管理_伯嘉基金网';
        $this->_data['subnav'] = 'fxcp';
        $this->_data['sidebar'] = 'wdzc';
        
        $riskQues = array();
        
        if($this->_data['error'] == 0){
            $param1 = array(
                'request_num' => 30, //查询条数
                'reqry_recordsum_flag' => 1,
                'qry_beginrownum' => 1,
                'answer_object' => 1 //回答对象(1-个人，0-机构)
            );
            
            $riskQuesTitle1 = $this->User_Model->riskQuesTitle($param1);
            
            $param2 = array(
                'request_num' => 30, //查询条数
                'reqry_recordsum_flag' => 0,
                'qry_beginrownum' => 10,
                'answer_object' => 1 //回答对象(1-个人，0-机构)
            );
            
            $riskQuesTitle2 = $this->User_Model->riskQuesTitle($param2);
            $riskQuesTitle = array_merge($riskQuesTitle1, $riskQuesTitle2);
            //pre($riskQuesTitle);
            if($riskQuesTitle){
                $i = 0;
                foreach ($riskQuesTitle as $rk => $rv){
                    $sParam = array(
                        'trust_way' => TRANSACTION_MODE, //交易委托方式  2代表的是网上委托 7 代表的是手机委托
                        'request_num' =>8, //查询条数
                        'reqry_recordsum_flag' => 1, //重新统计总记录数标志
                        'qry_beginrownum' =>1, //比如总记录是100，每50条分页；那么需要查询2次；第1次查询请求行数传50，重新统计总记录数传1，查询起始行号传1；第2次查询请求行数传50，重新统计总记录数传0，查询起始行号传51；
                        'question_no' => $rv['question_no']
                    );
                    $riskQuesOption = $this->User_Model->riskQuesOption($sParam);
                    //pre($riskQuesOption);
                    if($riskQuesOption){
                        $riskQues[$i]['question_no'] = $rv['question_no'];
                        $riskQues[$i]['question_title'] = $rv['question_content'];
                        $riskQues[$i]['question_option'] = $riskQuesOption;
                        $i++;
                    }
            
                }
            
            
            }
        }
        
	    //pre($riskQues);
	    $this->_data['riskQues'] = $riskQues;
	    $this->load->view('_user/riskevaluation.html', $this->_data);

	}
	
	
	/**
	 * 
	 * 风险评测结果
	 *
	 */
	public function riskEvaluationRs(){
	    
	    $this->_data['subnav'] = 'fxcp';
	    $this->_data['sidebar'] = 'wdzc';
	    $this->_data['title'] = '风险评测_账户管理_伯嘉基金网';
	    $userID = $this->_data['userID'];
	    
	    $userDetail = $this->User_Model->getUserDetailByUserID($userID);
	    //pre($userDetail);
	    $type = $userDetail['RiskBear'];
	    unset($userDetail);
	    if(!$type){
	        errorJump('请先进行风险评测', TRADE_RISK_QUES_URL);
	    }
	    $this->_data['type'] = $type;
	    $this->load->view('_user/riskevaluationrs.html', $this->_data);
	}
	

	/**
	 * 
	 * 风险评测结果
	 *
	 */
	public function Presonal(){
	    
	    $this->_data['subnav'] = 'grxxgl';
	    $this->_data['sidebar'] = 'wdzc';
	    $this->_data['title'] = '个人信息管理_账户管理_伯嘉基金网';
	    $userID = $this->_data['userID'];
	    
	    $this->_data['userAuth'] = $userAuth = $this->User_Model->getUserAuthByUserID($userID);
	    
	    if(!$userAuth){
	        errorJump('请先绑定银行卡', TRADE_ADD_BANK_URL);
	    }
	    
	    $this->_data['userInfo'] = $userInfo = $this->User_Model->getUserBaseByUserID($userID);
	    $this->_data['userDetail'] = $userDetail = $this->User_Model->getUserDetailByUserID($userID);
        
        if($userDetail['SendMethod'] && is_numeric($userDetail['SendMethod'])){
            $tempd = array();
            $tempd[] = $userDetail['SendMethod'];
            $userDetail['SendMethod'] = $tempd;
        } else {
            $userDetail['SendMethod'] = $userDetail['SendMethod'] ? (array)unserialize($userDetail['SendMethod']) : array();
        }
        
        //$userDetail['SendMethod'] = $userDetail['SendMethod'] ? unserialize($userDetail['SendMethod']) : array();
	    //pre($userDetail['SendMethod']);
	    $this->_data['userDetail'] = $userDetail;

	    $datadictionary = config_item('datadictionary');
        //pre($userAuth);
	    $this->_data['aIdType'] = $aIdType = $datadictionary['1041']; //证件类别
	    $this->_data['aSex'] = $aSex = $datadictionary['1049']; //性别
	    $this->_data['aJob'] = $aJob = $datadictionary['2018']; //职业
	    $this->_data['aSendRate'] = $aSendRate = $datadictionary['2303']; //账单寄送频率
	    unset($aSendRate['2303']);
	    $this->_data['aSendRate'] = $aSendRate;
	    $this->_data['aSendMethod'] = $aSendMethod = $datadictionary['250063']; //账单寄送方式
	    $this->_data['aCountry'] = $aCountry = $datadictionary['769008']; //国籍
	    $this->_data['aBear'] = $aBear = $datadictionary['C00010']; //投资人风险承受能力
	    //pre($aSendRate);
	    unset($datadictionary);
	    $this->load->view('_user/presonal.html', $this->_data);
	}
	/*
 * 进入登录密码修改页面
 * */
	public function loginPwd(){
		$this->_data['subnav'] = 'xgmm';
		$this->_data['sidebar'] = 'wdzc';
		$this->_data['title'] = '登录密码修改_账户管理_伯嘉基金网';
		$this->load->view('_user/modlogin_pwd.html', $this->_data);
	}

	/*
    * 进入交易密码修改页面首页
    * */
	public function tradePwd(){
        $this->_data['subnav'] = '';
        $this->_data['sidebar'] = '';
		$this->_data['title'] = '交易密码修改_账户管理_伯嘉基金网';
		$this->_data['bank'] =$this->bank;

		$userID = $this->_data['userID'];

		$userInfo = $this->User_Model->getUserBaseByUserID($userID);

		if(!$userInfo){
			exit('用户信息错误！');
		}

		$userDetail = $this->User_Model->getUserDetailByUserID($userID);

		$this->_data['tid'] = time();
		//验证号
		$this->_data['mkid'] = substr(md5(ENCODE_KEY . substr(md5($this->_data['tid']), -5) . 'mobile'), 8, 16);

		$this->_data['userInfo'] = $userInfo;
		$this->_data['userDetail'] = $userDetail;

		$m_name='modpswd_'.$userID.$this->_data['tid'].substr(md5(ENCODE_KEY . md5($userID) ), 8, 6);
		$this->cache->set($m_name, $this->_data['mkid'], 30*60); //页面合法链接验证储存
		$this->load->view('_user/modpswd_bank.html', $this->_data);
	}
	/*
         * 进入交易密码发送手机短信页
         * */
	public function tradeMobile(){
		$this->_data['subnav'] = '';
		$this->_data['sidebar'] = '';
		$this->_data['title'] = '交易密码修改_账户管理_伯嘉基金网';
		$userID = filter_slashes(trim($this->input->get('uid', true))); //用户id
		$mobile = filter_slashes(trim($this->input->get('mobile', true))); //用户id

        $fd = (int)$this->input->get('fd', true);
		$tid = $this->input->get('tid', true);//初始时间
		$stid = $this->input->get('stid', true);//最新时间
		$mkid = $this->input->get('mkid', true);//页面验证随机码

		//先进行空值校验
		if(!$mobile || !$userID || !$tid || !$stid || !$mkid || !isset($fd)){
			echo json_encode(array('flag'=>'10016', 'msg'=>'参数错误，请重试!')); exit;
		}
		//验证超时
		//页面过期, 半小时
		isRequestTimeout($stid, 60*30, '请求已失效，请刷新页面重试');
		//验证非法链接
		$m_name='modpswd_'.$userID.$tid.substr(md5(ENCODE_KEY . md5($userID) ), 8, 6);
		$hmkid=$this->cache->get($m_name);
		if($hmkid!=$mkid){
			echo json_encode(array('flag'=>'10017', 'msg'=>'非法链接不得进入!')); exit;
		}

		$this->_data['uid']=$userID;
		$this->_data['mobile']=$mobile;
		$this->_data['tid']=$tid;
		$this->_data['stid']=$stid;
		$this->_data['mkid']=$mkid;
		$this->_data['fd']=$fd;

		$userInfo = $this->User_Model->getUserBaseByUserID($userID);

		if(!$userInfo){
			exit('用户信息错误！');
		}
		$userDetail = $this->User_Model->getUserDetailByUserID($userID);
		$this->_data['userInfo'] = $userInfo;
		$this->_data['userDetail'] = $userDetail;
		$this->load->view('_user/modpswd_mobile.html', $this->_data);
	}

	/*
        * 进入交易密码修改页面
        * */
	public function tradeNew(){
		$this->_data['subnav'] = '';
		$this->_data['sidebar'] = '';
		$this->_data['title'] = '交易密码修改_账户管理_伯嘉基金网';
		$userID = filter_slashes(trim($this->input->get('uid', true))); //用户id

		$tid = $this->input->get('tid', true);//初始时间
		$ttid = $this->input->get('ttid', true);//最新时间
		$mkid = $this->input->get('mkid', true);//页面验证随机码

		//先进行空值校验
		if( !$userID || !$tid || !$ttid || !$mkid ){
			echo json_encode(array('flag'=>'10031', 'msg'=>'参数错误，请重试!')); exit;
		}

		//验证超时
		//页面过期, 半小时
		isRequestTimeout($ttid, 60*30, '请求已失效，请刷新页面重试');
		//验证非法链接
		$m_name='modpswd_'.$userID.$tid.substr(md5(ENCODE_KEY . md5($userID) ), 8, 6);
		$hmkid=$this->cache->get($m_name);
		if($hmkid!=$mkid){
			echo json_encode(array('flag'=>'10032', 'msg'=>'非法链接不得进入!')); exit;
		}

		$this->_data['uid']=$userID;
		$this->_data['tid']=$tid;
		$this->_data['ttid']=$ttid;
		$this->_data['mkid']=$mkid;

		$userInfo = $this->User_Model->getUserBaseByUserID($userID);

		if(!$userInfo){
			exit('用户信息错误！');
		}
		$userDetail = $this->User_Model->getUserDetailByUserID($userID);
		$this->_data['userInfo'] = $userInfo;
		$this->_data['userDetail'] = $userDetail;
		$this->load->view('_user/modpswd_new.html', $this->_data);
	}

	/*
       * 进入交易密码修改成功页面
       * */
	public function tradeSuccess(){
		$this->_data['subnav'] = '';
		$this->_data['sidebar'] = '';
		$this->_data['title'] = '交易密码修改_账户管理_伯嘉基金网';
		$userID = $this->_data['userID'];

		$userInfo = $this->User_Model->getUserBaseByUserID($userID);
		if(!$userInfo){
			exit('用户信息错误！');
		}
		$userDetail = $this->User_Model->getUserDetailByUserID($userID);

		$this->_data['userInfo'] = $userInfo;
		$this->_data['userDetail'] = $userDetail;

		$this->load->view('_user/modpswd_success.html', $this->_data);
	}
}
?>