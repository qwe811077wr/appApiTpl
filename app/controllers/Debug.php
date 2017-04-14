<?php
/**
 * 调试
 *
 * @author tianweimin
 */

use Helper\RSA;

class DebugController extends \Base\Controller\Api {
    
    public $test_public_key = '';
    
    static protected function loadPublicApi()
    {
        return ['*'];
    }
    
    public function indexAction()
    {
        $uid = $this->jsonParam['uid'];
        $userInfo = Factory::db()->from('user')->where(array('uid' => $uid))->fetch();
        $publicKeyPem = RSA::getPublicKeyFromPriv($userInfo['rsa_privkey'], $userInfo['rsa_passphrase']);
        $buf = json_encode($this->jsonParam);
        //加密数据
        $data = RSA::getPublic($publicKeyPem)->enc($buf);
        $sign = strtolower(md5(strtolower(md5($data)) . $userInfo['token']));
        echo rawurlencode($data);
        echo '<hr />';
        echo $sign;
    }
    
    //加密
    public function genAction()
    {
        
    }
    //签名
    public function signAction()
    {
        
    }
    //解密
    public function decAction()
    {
        
    }
}
