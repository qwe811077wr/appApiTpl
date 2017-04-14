<?php
namespace Lib;
use Helper\Helper;
use Helper\Curl;

/**
 * leancloud 短信接口
 *
 * @author tianweimin
 */
class LeanCloud {
    
    private static $_instance = NULL;

    public $AppId = NULL;
    public $AppKey = NULL;

    public $url = 'https://api.leancloud.cn';
    public $requestSmsCode = '/1.1/requestSmsCode';
    public $verifySmsCode = '/1.1/verifySmsCode/%d';
    public $ContentType = 'application/json';

    /**
     * »ñÈ¡ÊµÀý
     * @return  $this
     */
    public static function getInstance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        $conf = Helper::getConf("leancloud");
        $this->AppId = $conf['AppId'];
        $this->AppKey = $conf['AppKey'];
    }

    public function sms($mobile, $template = 'vcode', $params = [], $smsType = 0)
    {
        $arg = array(
                'mobilePhoneNumber' => $mobile,
            );
        if($smsType == 1) {
            $arg['smsType'] = 'voice';
        }
        if (!empty($template)) {
            $arg['template'] = $template;
        }
        if (!empty($params)) {
            $arg = array_merge($arg, $params);
        }
        return $this->send($this->url . $this->requestSmsCode, $arg);
    }

    public function send($url, $arg)
    {
        $hr = array(
                'X-LC-Id:' . $this->AppId,
                'X-LC-Key:' . $this->AppKey,
                'Content-Type:' . $this->ContentType,
            );
        
        $res = Curl::HTTP($url, json_encode($arg), true, $hr);
        $phone = !empty($arg['mobilePhoneNumber'])?$arg['mobilePhoneNumber']:'';
        $template = !empty($arg['template'])?$arg['template']:'';
        if (empty($res)) {
            \Factory::log()->addError(sprintf("LeanCloud template %s send %s : CONNECT ERROR", $template, json_encode($arg)));
            return false;
        }
        \Factory::log()->addInfo(sprintf("LeanCloud template %s send (%s): %s", $template, json_encode($arg), $res));
        $data = json_decode($res, true);
        if (empty($data)) {
            unset($arg['mobilePhoneNumber']);unset($arg['template']);
            $arg = array(
                    'tpl_id' => $template,
                    'tpl_val' => $arg,
                );
            //发送成功，记录下来
            $smsLog = new \SmsLogModel();
            $smsLog->log($phone, 'captcha', json_encode($arg), Helper::ip());
        }
        return empty($data);
    }

    //校验验证码
    public function verifySmsCode($mobile, $code)
    {
        return $this->send($this->url . sprintf($this->verifySmsCode, $code) . '?mobilePhoneNumber=' . $mobile, []);
    }
}
