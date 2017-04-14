<?php

use Params\EC;
use Params\DEFINIE;
use Helper\Helper;
use Lib\LeanCloud;

class UserController extends Base\Controller\Api
{
    static protected function loadPublicApi() 
    {
        return ['redisTest'];
    }

    /**
     * 登录和注册
     */
    public function login()
    {
    	$mobile = $this->jsonParam['mobile'];
    	$vcode = $this->jsonParam['vcode'];
    	$type = intval($this->jsonParam['type']);

    	$mobile = Helper::getPhone($mobile);
    	if (!$mobile) {
    		return $this->err(EC::PHONE_FOMATE_E);
    	}
    	if (!preg_match("/^\d{4}$/", $vcode)) {
    		return $this->err(EC::CAPTCHA_FORMATE_E);
    	}
    	if (LeanCloud::verifySmsCode($mobile, $vcode)) {
    		return $this->err(EC::CAPTCHA_E);
    	}
    	$user = UserModel::getInstance();
    	//获取用户令牌
    	$res = $user->tokenGen($mobile, !$this->isBindUser);
    	if (!$res) {
    		return $this->err(EC::UESR_TOKEN_E);
    	}
    	if ($user->userInfo['status'] == DEFINIE::USER_STAT_FORBIDDEN) {
    		return $this->err(EC::USERT_STAT_E);
    	}

    	$result = [
    		'uid' =>  $user->userInfo['uid'],
    		'token' =>  $user->userInfo['token'],
    		'user_type' =>  $user->userInfo['user_type'],
    		'status' =>  $user->userInfo['status'],
    		'new' =>  $user->userInfo['is_new']
    	];

    	if (!$this->isBindUser) {
    		$result['perm'] = $user->userInfo['publickey'];
    	}
    	//环信同步
    	
    }
}