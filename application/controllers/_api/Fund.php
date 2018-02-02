<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 基金数据接口
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * $author:wujg $addtime:2016-12-12
 ****************************************************************/
class Fund extends CI_Controller
{
	/* 传递到对应视图的数据 */
	private $cache;
	private $versonKeys;
	private $tid;
	private $keystr;
	private $vid;
	private $vKey;

    public function __construct() 
    {
        parent::__construct();
        
        $this->load->helper('api');
        
        $this->tid = $this->input->post('tid', true);
        $this->keystr = $this->input->post('keystr', true);
        $this->vid = (int)$this->input->post('vid', true);
        
        //$this->versonKeys = array('1' => 'uoJQS93&S*)$0dwseYpD#0*f)Eu9nms');
        //$this->vKey = isset($this->versonKeys[$this->vid]) ? $this->versonKeys[$this->vid] : 'nnnnnnn';
   
    }

    /**
     * 
     * 获取伯嘉基金信息
     * 
     */
    public function fList(){
          
        $type = (int)$this->input->post('type', true); //1 热销主题, 2稳定收益, 3伯嘉精选, 4热销基金
        $fType = (int)$this->input->post('ftype', true); //基金类型：1->债券型2->股票型3->货币型4->指数型5->混合型6->QDII
        $tid = $this->input->post('tid', true);
        $keystr = $this->input->post('keystr', true);
        
        $starNo = (int)$this->input->post('startno', true); //起始
        $qryCount = $this->input->post('qrycount', true) ?  (int)$this->input->post('qrycount', true) : 30; //数量
        
        if(!$type || !$tid || !$keystr){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数缺失', 'info'=>''));
            exit;
        }
        
        isRequestTimeout($tid);
        
        $vKey = md5('TfxjmSunla&UxRBnlD4#NNl1hOrHFh%O9Nxw' . $tid . $type . $fType);
        if($vKey != $keystr){
            echo json_encode(array('flag'=>'10003', 'msg'=>'校验错误', 'info'=>''));
            exit;
        }
        
        $ip = $this->input->ip_address();
        $arrIPs = array();
        if(!in_array($ip, $arrIPs)){
            //echo json_encode(array('flag'=>'10004', 'msg'=>'请求错误', 'info'=>''));
            //exit;
        }

        

        switch ($type) {
            case 1:
                $param['HotTopic'] = '1';
                $param['type'] = 'HotTopic';
                break;
            case 2:
                $param['StableIncome'] = '1';
                $param['type'] = 'StableIncome';
                break;
            case 3:
                $param['BjChoose'] = '1';
                $param['type'] = 'BjChoose';
                break;
            case 4:
                $param['HotFund'] = '1';
                $param['type'] = 'HotFund';
                break;
            default:
                $param['BjChoose'] = '1';
                $param['type'] = 'BjChoose';
        }
        if($fType){
            $param['FundType'] = $fType;
        }
        
        $this->load->model('_user/fund_model');
        $rFund  = array();
        $fund = $this->fund_model->gFund($param, $qryCount, $starNo);
        //pre($fund);
        if($fund){
            $rFund = $fund;
        }

        $rData = array('flag'=>'10000', 'msg'=>'ok', 'info'=>$rFund);
        echo json_encode($rData); exit;
        
    }
    
}

/* End of file stockquote.php */
/* Location: ./application/controllers/stockquote.php */