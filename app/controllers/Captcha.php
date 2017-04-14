<?php

use Params\EC;
use Params\LANG;
use Cache\Captcha;
use Lib\TaobaoSms;

class CaptchaController extends Base\Controller\Api
{
    static protected function loadPublicApi() 
    {
        return ['send'];
    }

    /**
     * 发送验证码
     */
    public function sendAction()
    {
    	$mobile = $this->jsonParam['mobile'];
    	if (!preg_match("/^1\d{10}$/", $mobile)) {
 			return $this->err(EC::PHONE_FOMATE_E);
    	}
    	$captcha = Captcha::getInstance();
    	//校验是否还可以发送验证码
    	if ($captcha->isDenied($mobile) !== true) {
    		return false;
    	}
    	//生成验证码，并保存至redis中
    	$vcode = $captcha->save($mobile);

    	//发送验证码
        $env = \YAF\Application::app()->environ();
        if (in_array($env, array('devaaa')) !== false) {
        	try {
        		$res = LeanCloud::getInstance()->sms($mobile);
        		if ($res !== true) {
	        		return $this->output([LANG::BLANK], EC::BLANK, $res);
	        	}
        	} catch (Exception $e) {
        		$msg = $e->getMessage();
        		return $this->output([LANG::BLANK], EC::BLANK, $msg);
        	}
        }
        return $this->output(array('time'=>time()));
    }
}