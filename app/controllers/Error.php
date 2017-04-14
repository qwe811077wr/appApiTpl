<?php

use \Params\EC;
use \Params\LANG;

class ErrorController extends Base\Controller\Api {
	
    static protected function loadPublicApi() 
    {
        return ['error'];
    }
	public function errorAction($exception)
    {
        $code = $exception->getCode();
		$msg = $exception->getMessage();
        //yaf的异常处理
        if ($code == Yaf\ERR\AUTOLOAD_FAILED) {
            echo '11111111';
            return true;
        }

  		//错误处理页面
		switch ($exception) {
            case ($exception instanceof Exception\InputException):
                //获取自定义错误码
                $ecCode = $exception->getECCode();
                $msg = $exception->getECMsg();
                if (empty($msg)) {
                    $this->err($ecCode);
                } else {
                    $this->output([LANG::BLANK], $ecCode, $msg);
                }
				break;
			
            case ($exception instanceof PDOException):
                //写错误日志
                \Factory::log()->error($msg);
                //发送错误邮件
                $reciever_address = '281665183@qq.com';
                $subject = '系统通知';
                $body = '错误信息：' . $msg;
                $cc_items = array();
                \Helper\Helper::sendMail($reciever_address, $subject, $body, $cc_items);
				break;
			
			default:
				# code...
                Factory::log()->error($msg);
				break;
		}
	}

}