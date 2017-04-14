<?php

use Params\EC;
use Helper\Helper;
use Helper\RSA;
use Cache\UUID;

class AppController extends Base\Controller\Api
{
    static protected function loadPublicApi()
    {
        return ['*'];
    }

    const OBSCURE_SIZE = 124;
    
    //app初始化
    public function initAction()
	{
        //根据http_user_agent和ip大概判断请求唯一性
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (empty($agent)) {
            return $this->err(EC::HTTP_USER_AGENT);
        }
        $ckey = md5(Helper::ip().$agent);
        if ($res = UUID::getInstance()->virifyRepeat($ckey) !== false) {
            return $this->output($res);
        }
        $uuid = Helper::GenUuid();
        $rsa = RSA::gen();
        $buff = RSA::pemToSTR($rsa['rsapukey']);
        $tunk = substr($buff, 0, self::OBSCURE_SIZE);
        $buff = substr($buff, self::OBSCURE_SIZE) . $tunk;
        
        $result = array(
            'uuid' => $uuid,
            'perm' => $buff,
            'time' => time()
        );
        UUID::getInstance()->save($uuid, $rsa, $ckey, $result);
        return $this->output($result);
	}
    
    //获取服务器时间
    public function timeAction() {
        return $this->output(array(
            'time' => time()
        ));
    }

}