<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
|
| 此文件放于system/libraries/下
|
| 使用方法:
| $this->load->library('Sendmail');
| $this->Sendmail->send();
  $sendomail = &load_class('Sendomail');
  $sendomail->send_mail($mailinfo);
|--------------------------------------------------------------------------
*/

class CI_Sendomail
{

    /**
     * @ send_mail 发送邮件
     *
     * @param array $mailinfo 邮件信息
     * $mailinfo=array(
     * 'mailto'=>目标邮箱地址
     * 'subject'=>主题
     * 'content'=>内容
     * );
     * @return mixed
     */
    function send_mail($mailinfo)
    {
//        $logFile = strtolower(__CLASS__) . '/' . strtolower(__FUNCTION__);
        $sendmail = &load_class('Sendomail');
        $email = str_replace('，', ',', $mailinfo['mailto']);//全角转半角
        $email = explode(',', $mailinfo['mailto']);
        $mailinfo['from'] = 'bjjj';
        $true = true;

        if (count($email) > 1) {
            foreach ($email as $k => $v) {
                if (!check_str($v, 'email')) {
                    unset($email[$k]);
                }
            }
            if (count($email) < 1)
                $true = false;
        } else {
            if (!check_str($mailinfo['mailto'], 'email')) {
                $true = false;
            }
        }

        if (!$true) {
            return $true;
        }
        $mailinfo['mailto'] = join(',', $email);
        $true = $sendmail->send($mailinfo);
//        logs(date('H:i:s') . '  ' . serialize($mailinfo) . '  ' . $true . PHP_EOL, $logFile);
        //@error_log(date('H:i:s').'  '.serialize($mailinfo).'  '.$true.PHP_EOL, 3, LOG_PATH.'/send_mail_'.date('Ymd').'.txt');
        return $true;
    }


    public function send($info, $action = 'post')
    {
        $url = 'http://mail.api.cnfol.net/bjindex.php';
        $ydUrl = 'http://mail.api.cnfol.net/index_yd.php';
        $key = 'da2f00b38ed9273b974f254b7ba27571';
        $emails = $info['mailto'];//用户邮件地址
        $subject = trim($info['subject']);//邮件标题
        $content = trim($info['content']);//邮件内容
        $charset = (isset($info['charset']) && $info['charset']) ? $info['charset'] : 'utf8';//编码
        $from = (isset($info['from']) && $info['from']) ? $info['from'] : 'passport';//来源系统
        $isyd = (isset($info['isyd']) && $info['isyd']) ? 1 : 0; //是否异地登录

        if ($subject <> '' && $content <> '') {
            $emails = str_replace('，', ',', $emails);//全角转半角
            $emails = explode(',', $emails);
            $emailto = '';
            foreach ($emails as $v) {
                if (!empty($v) && preg_match('/^[\w\-\.]+\@[\w\-\.]+[A-Za-z]{2,}$/', $v)) {
                    $emailto .= trim($v) . ',';
                } else {
                    @error_log(date('H:i:s') . PHP_EOL . __FILE__ . '//' . __LINE__ . PHP_EOL . '发送邮件，邮箱：' . $v . '格式不符合要求' . PHP_EOL, 3, LOG_PATH . '/sendmail' . date('Ymd') . '.log');
                }
            }
            $emailto = substr($emailto, 0, -1);

            if ($emailto <> '') {

                $info['key'] = $key;
                $info['content'] = $content;
                $info['mailto'] = $emailto;
                $info['subject'] = $subject;
                $info['Original'] = $from;
                $info['charset'] = $charset;
                $info['smtpID'] = rand(24, 26);

                $sendUrl = $isyd ? $ydUrl : $url;
                $result = curl_post($sendUrl, $info);

                @error_log(date('H:i:s') . '|sendUrl|' . $sendUrl . PHP_EOL . '|info|' . print_r($info, true) . PHP_EOL . '|rs|' . print_r($result, true) . PHP_EOL, 3, LOG_PATH . '/sendmail' . date('Ymd') . '.log');

//                pre($result);
                if ($result['code'] == 200 && $result['data'] == 'errid=0&msg=发送成功') {
                    return true;
                } else {
                    return false;
                }
            }
        } else {
            return false;
        }
    }


}// END Sendmail Class

/* End of file Sendmail.php */
/* Location: ./system/libraries/Sendmail.php */
