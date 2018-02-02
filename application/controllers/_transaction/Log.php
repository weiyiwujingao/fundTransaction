<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/****************************************************************
 * 用户操作记录
 *---------------------------------------------------------------
 * Copyright (c) 2004-2017 CNFOL Inc. (http://www.cnfol.com)
 *---------------------------------------------------------------
 * @author:jiangxuefeng  @addtime:2017/01/16
 ****************************************************************/
class Log extends MY_Controller
{
    protected $_data = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->model('_transaction/transaction_mdl');
        $this->_data['title'] = '操作日志_交易查询_伯嘉基金网';
        $this->_data['keywords'] = SEO_KEYWORDS;
        $this->_data['description'] = SEO_DESCRIPTION;
        $this->_data['nav'] = 'jycx';
        $this->_data['subnav'] = 'czrz';
        $this->_data['sidebar'] = 'jycx';
        $this->_data['headerCss'] = '?v=' . time();
        //用户相关
        $this->load->model('_user/User_Interact');
        $this->_data['userID'] = $this->User_Interact->getUserID();
        //判断权限
        $this->User_Interact->passportCheck(true);
        $this->_data['userInfo'] = $this->User_Model->getUserBaseByUserID($this->_data['userID']);
        $this->_data['nickName'] = $this->User_Interact->getNickName();
    }

    public function index()
    {
        $this->load->helper('html');
        $pagesize = filter_slashes($this->input->get('pagesize', true));
        $page = filter_slashes($this->input->get('page', true));
        $pagesize = $pagesize ? : 5;
        $page = $page ? : 1;
        $user_id = $this->_data['userInfo']['UserID'];
        $res = $this->transaction_mdl->getOperateLog($user_id, $pagesize, $pagesize * ($page - 1));
        $this->_data['total'] = $res['total'];
        $this->_data['data'] = $res['data'];
        $this->_data['page_html'] = pageajax($res['total'], $page, $pagesize);
        $this->load->view('_transaction/log.html', $this->_data);
    }
}