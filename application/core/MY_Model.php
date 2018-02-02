<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class MY_Model extends CI_Model{
    //定义缓存
    protected $cache;
    protected $db;
    protected $mdb;


    public function __construct($group_name = ''){
        parent::__construct();
        //header('Content-Type: text/html; charset=utf-8');
        $this->cache = $this->buyfunds_mem;
        $this->db = $this->load->database('slave', TRUE);	//读从库

    }

    /**
     *
     * 获取分页数据及总条数
     * @param string @tablename 表名
     * @param mixed $where 条件
     * @param int $limit 每页条数
     * @param int $offset 当前页
     * @param string $order_by 排序字段
     *
     */
    protected function getPageData($tablename, $where, $limit, $offset, $choose='*', $order_by='') {
        if(empty($tablename)) {
            return FALSE;
        }
    
        if($where) {
            if(is_array($where)) {
                $this->db->where($where);
            } else {
                $this->db->where($where, NULL);
            }
        }
    
        $dbhandle = clone($this->db);
        $total = $this->db->count_all_results($tablename);
    
        if($choose){
            $this->db->select($choose);
        }
        
        if($limit) {
            $dbhandle->limit($limit);
        }
    
        if($offset) {
            $dbhandle->offset($offset);
        }
    
        if($order_by) {
            $dbhandle->order_by($order_by);
        }
    
        $data = $dbhandle->get($tablename)->result_array();
    
        return array('total' => $total, 'data' => $data);
    }
    
    
    /**
     * 从数据库中读取数据
     *
     * @param string $table 所要查询的数据库表
     * @param string $where 条件子句（不包含关键字 WHERE）
     * @param string $field 根据字段$field排序
     * @param string $choose 所选择的字段。默认为“*”（全选）；
     * @param string $sort 排列规则，值为”DESC“（降序）或”ASC“（升序）。默认为空。
     *
     * @return array $list 从数据库中所取得的值
     *
     */
    
    protected function getData($table, $where = '', $choose = '*', $orderBy='', $limit=''){
        if(empty($table)) {
            return FALSE;
        }
        $where = trim($where) ? " WHERE " . trim($where) : $where;
        $orderBy = $orderBy ? " ORDER BY "  . $orderBy : '';
        $limit = $limit ? ' LIMIT ' . $limit : '';
        $sql = 'SELECT ' . $choose. ' FROM ' . $table . $where . $orderBy . $limit;
        //pre($sql);
        $return = $this->db->query($sql);
		$list = $return->result_array();
        return $list;
    }
    
    /**
     * 从数据库中读取数据 getDataAR
     *
     * @param string $table 所要查询的数据库表
     * @param string $where 条件子句（不包含关键字 WHERE）
     * @param string $field 根据字段$field排序
     * @param string $choose 所选择的字段。默认为“*”（全选）；
     * @param string $sort 排列规则，值为”DESC“（降序）或”ASC“（升序）。默认为空。
     * @param bool $one 是否获取一位数组 默认false，获取二维
     *
     * @return array $list 从数据库中所取得的值
     *
     */
    protected function getDataAR($table, $where = '', $choose = '*', $orderBy='', $limit='', $one=FALSE){
        $data = array();
        if(empty($table)) {
            return $data;
        }
    
        if($where) {
            if(is_array($where)) {
                $this->db->where($where);
            } else {
                $this->db->where($where, NULL, false);
            }
        }
    
        if($choose){
            $this->db->select($choose);
        }
        if($limit) {
            $this->db->limit($limit);
        }
    
        if($orderBy) {
            $this->db->order_by($orderBy);
        }
    
        $data = $one ? $this->db->get($table)->row_array() : $this->db->get($table)->result_array();
    
        return $data;
    }
    
    /**
     * 
     * 更新表
     * 
     */
    protected function updateTb($table, $param, $where){
        $this->mdb = $this->load->database('master', TRUE);	//更新从库
        $this->mdb->update($table, $param, $where);
        //pre($this->mdb->last_query());
        
        $r = ($this->mdb->affected_rows() == 1) ? TRUE : FALSE;
        $this->mdb->close();
        return $r;
    }
    /**
     * 
     * 插入表
     * $returnID  是否返回插入ID
     * 
     */
    protected function insertTb($table, $param, $returnID = true){
        $this->mdb = $this->load->database('master', TRUE);	//写主库
        $this->mdb->insert($table, $param);
        //pre($this->mdb->last_query());
        $r = ($this->mdb->affected_rows() == 1) ? TRUE : FALSE;
        
        if($returnID){
            $r = (int)$this->mdb->insert_id();
        }
        $this->mdb->close();
        return $r;
    }
    
    /**
     * 
     * 获取插入的ID
     * 
     */
    protected function getInsertID(){
        $this->mdb = $this->load->database('master', TRUE);	//写主库
        $r = (int)$this->mdb->insert_id();
        $this->mdb->close();
        return $r;
    }
    
    /**
     * 
     * 操作记录插入
     * UserID 用户ID 必填
     * OperatType 操作类型 必填 见函数 $aType
     * OperatStatus 操作状态 必填 1成功 2失败
     * 
     * HsUserID 恒生用户编号 选填
     * OperatDetail 操作备注 选填
     * 
     *  
     */
    protected function operatLog($param){
        $table = 'tbPassportOperatLog';
        $aType = array('1' =>'客户开户', '2'=>'帐号修改');
        $tParma = $param;
        $tParma['OperatDesc'] = $aType[$param['OperatType']];
        $tParma['OperatTime'] = NOW_TIME;
        
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
        logs(print_r($tParma, true), $logFile);
        
        $this->insertTb($table, $tParma);
    }
    
    /**
     * 
     * 调伯嘉接口
     * @param unknown $param
     * @param unknown $sign
     * @param string $type
     * @return mixed|multitype:
     */
    protected function curlPostUFX($param, $sign, $access_token, $type = 'sale'){
        $logFile = strtolower(__CLASS__).'/'.strtolower(__FUNCTION__);
        $arrCommon = array(
                        'targetcomp_id' => TARGETCOMP_ID,
                        'sendercomp_id' => SENDERCOMP_ID,
                        'access_token'  => $access_token,              
                     );
        $aParam = array_merge($arrCommon, $param);
        $mUrl = $type == 'sale' ? STI_CWSALE : STI_CWPAY;
        $mUrl .= $sign;
        //$x = $mUrl . "\r\n<br/>";
        //$x .= '<form method="post" action="' . $mUrl . '">'. "\r\n<br/>";
        //foreach($aParam as $xk => $xv){
            //$x .= $xk .':<input type="text" value="' . $xv . '" name="' . $xk . '"/>'. "\r\n<br/>";
        //}
        //$x .='<input type="submit" value="ok">' . "\r\n";
        //$x .='</form>';
        //logs('|-x-|' . $x, $logFile);
        $tempParam = $aParam;
        if(isset($tempParam['password'])){
            unset($tempParam['password']);
        }
        logs('|-Url-|' . $mUrl . PHP_EOL . '|-aParam-|'  . print_r($tempParam, true), $logFile);
		unset($tempParam);
        $cRs = curl_post($mUrl, $aParam, 30);
        
        //pre($mUrl);
        //pre($aParam);
        //pre($cRs);
        
        //logs('|-string-|' . http_build_query($aParam), $logFile);
        logs('|-cRs-|' . print_r($cRs, true), $logFile);
        if(isset($cRs['code']) && $cRs['code'] == '200' && $cRs['data']){
            return json_decode($cRs['data'], true);
        } else if($cRs['data']) {
            return json_decode($cRs['data'], true);
        } else {
            return array();
        }
    }
}
?>