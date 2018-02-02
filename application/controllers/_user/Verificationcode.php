<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class VerificationCode extends MY_Controller {
    
    private $cache;
    private $aType;
    
    public function __construct() {
        parent::__construct();
        $this->cache = $this->buyfunds_mem;
        $this->aType = array('1' => 'reg', '2'=>'findpswd'); //请求类型 1注册，2找回密码, 3验证码

    }

    public function index(){
        
        $func = $this->input->post('action', TRUE);
        if(TRUE === method_exists(__CLASS__, trim($func))){
            $this->$func();
        } else {
            echo '<!DOCTYPE html>
            <html>
            <head>
            	<title>403 Forbidden</title>
            </head>
            <body>
            <p>Directory access is forbidden.</p>
            </body>
            </html>';
            exit;
        }
        
    }
    
    
    /**
     *
     * 获取验证码
     *
     */
    public function ajaxGetCode(){
        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__) . '_' . date('Ymd') . '.log';

        $tid = $this->input->post('tid', true);
        $kid = $this->input->post('kid', true);
        $type = (int)$this->input->post('type', true); //1 忘记密码 2
        $ip = $this->input->ip_address();
        
    
        if(!isset($this->aType[$type])){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误')); exit;
        }
        $sType = $this->aType[$type];
         
        if(!$tid || !$kid || !$type){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数缺失')); exit;
        }
        //页面过期, 半小时
        isRequestTimeout($tid, 60*30, '请求超时');
        $ver = substr(md5(ENCODE_KEY . substr(md5($tid), -5) . 'code'), 8, 16);
        //pre($ver);
        if($ver != $kid){
            echo json_encode(array('flag'=>'10003', 'msg'=>'校验错误')); exit;
        }
        
        //防刷3秒， 30次
        $this->_ajaxRequestCheck(strtolower(__FUNCTION__), 50, 2);

        $tstr = time();
        $rand = rand(10000,99999);
        $vstr = md5(md5($kid) . $tid . md5($tstr . ENCODE_KEY) . $rand. $sType);

        $this->cache->set($vstr . $sType, makeRandChar(4, 'charnumber'), 6*60); //6分钟过期

        echo json_encode(array('flag'=>'10000', 'msg'=>'ok', 'info'=> array('tstr'=>$tstr, 'vstr'=>$vstr, 'x'=>$rand)));
        exit;
         
    }
    
    
    /**
     *
     * 获取验证码图片
     *
     */
    public function show(){
        $tid = $this->input->get('tid', true);
        $kid = $this->input->get('kid', true);
        $tstr = $this->input->get('tstr', true);
        $vstr = $this->input->get('vstr', true);
        $rand = $this->input->get('vstrx', true);
        $type = (int)$this->input->get('type', true); //1 忘记密码 2
        $ip = $this->input->ip_address();
        
        if(!$tid || !$kid || !$tstr || !$vstr || !$type){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数缺失')); exit;
        }
        
        //请求源头是否合法
        $refererUrl = $this->input->server('HTTP_REFERER', true);
        $domain = get_host_domain($refererUrl);
        if(!is_allowed_login_domain($domain) ){
            echo json_encode(array('flag'=>'10005', 'msg'=>'请求错误00，请重试')); exit;
        }

        if(!isset($this->aType[$type])){
            echo json_encode(array('flag'=>'10001', 'msg'=>'参数错误')); exit;
        }
        $sType = $this->aType[$type];

        //页面过期, 半小时
        isRequestTimeout($tid, 60*30, '请求超时2');
        $ver = substr(md5(ENCODE_KEY . substr(md5($tid), -5) . 'code'), 8, 16);
        //pre($ver);
        if($ver != $kid){
            echo json_encode(array('flag'=>'10003', 'msg'=>'校验错误') . '2'); exit;
        }
        
        
        //获取验证码请求过期, 6分钟
        isRequestTimeout($tstr, 60*6, '请求超时');
        $qVer = md5(md5($kid) . $tid . md5($tstr . ENCODE_KEY) . $rand . $sType);
        if($qVer != $vstr){
            echo json_encode(array('flag'=>'10003', 'msg'=>'校验错误')); exit;
        }
        

        //防刷
        $saRs = simpleAnti($ip, 'bjshowcode', 2, 50);
        
        if($saRs['flag'] != '10000'){
            echo json_encode($saRs); exit;
        }
        //获取验证码
        $code = $this->cache->get($vstr . $sType);

        //$code = makeRandChar(4, 'charnumber');
        
        //验证码图片的宽度
        $width  = 92;
        //验证码图片的高度
        $height = 34;
        //声明需要创建的图层的图片格式
        if (ob_get_contents()) ob_end_clean();
        @header("Content-Type:image/png");

        //error_reporting(E_ALL);
        //ini_set('display_errors','On');
        //pre('111');
        //创建一个图层
        $im = imagecreate($width, $height);
        //背景色
        $back = imagecolorallocate($im, 0xFF, 0xFF, 0xFF);
        //模糊点颜色
        $pix  = imagecolorallocate($im, 187, 230, 247);
        //字体色
        $font = imagecolorallocate($im, 41, 163, 238);
        //绘模糊作用的点
        mt_srand();
        for ($i = 0; $i < 1000; $i++) {
            imagesetpixel($im, mt_rand(0, $width), mt_rand(0, $height), $pix);
        }
        
        //pre($this->_getFont()); exit;
        //输出字符
        imagettftext($im, 16, rand(-5,20), 14, 30, $font, $this->_getFont(), $code);
        //输出矩形
        imagerectangle($im, 0, 0, $width -1, $height -1, $font);
        //输出图片
        imagepng($im);
    }
    
    private function _getFont(){
        $allFont = array(
            '0'=>'001.TTF', 
            '1'=>'002.ttf',
            '2'=>'003.ttc',
            '3'=>'004.TTF',
        );
        $max = count($allFont);
        return CACHE_PATH . '/font/' . $allFont[rand(0,$max-1)];
    }
}
?>