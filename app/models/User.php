<?php

use Helper\Helper;
use Helper\RSA;

class UserModel extends Base\Model\Db
{
    public $userInfo = [
        'uid' => 0,
        'token' => '',
        'user_type' => 0,   //用户类型
        'status' => 0,  //用户状态
        'public_key' => '', //公钥
        'is_new' => false,  //是否新用户
    ];
            
	function userInfo($uid)
	{
		$query = $this->db
				->from('user')
				->where('uid', $uid);
		return $query->fetch();
	}

    public function getInfoByMobile($mobile)
    {
        $query = $this->db
                    ->from('user a')
                    ->leftJoin('user_profile b on a.uid=b.uid')
                    ->select(NULL)
                    ->select('a.*,b.*')
                    ->where(array(
                        'a.mobile' => $mobile
                        ));
        return $query->fetch();
    }
    
    function tokenGen($phone, $newRSA = FALSE)
    {
        $newToken = Helper::GenToken();
        $expireDays = Helper::getconf('user')['token']['expire'];
        $tokenExpire = date('Y-m-d H:i:s', strtotime("+{$expireDays} day"));
        $userExisted = $this->db->from('user')->where('mobile', $phone)->fetch();
        //判断是否存在，不存在自动注册
        if (empty($userExisted)) {
            $rsa = RSA::gen();
            $newData = [
                'mobile' => $phone,
                'token' => $newToken, 
                'token_expired' => $tokenExpire,
                'rsa_privkey' => $rsa['privkey'],
                'rsa_passphrase' => $rsa['passphrase'],
                'user_type' => \Params\DEFINIE::USER_TYPE_COMMON,
                'status' => \Params\DEFINIE::USER_STAT_NOMAL,
            ];
            $this->userInfo['uid'] = $this->db->insertInto('user', $newData)->execute();
            $this->userInfo['token'] = $newToken;
            $this->userInfo['user_type'] = \Params\DEFINIE::USER_TYPE_COMMON;
            $this->userInfo['public_key'] = RSA::getPublicKeyFromPriv($rsa['privkey'], $rsa['passphrase']);
            $this->userInfo['is_new'] = true;
            $this->addProfile($this->userInfo['uid']);
        } else {
            $info = [
              'token' => $newToken,
              'token_expired' => $tokenExpire,
            ];
            if ($newRSA) {
                $rsa = RSA::gen();
                $this->userInfo['public_key'] = RSA::getPublicKeyFromPriv($rsa['privkey'], $rsa['passphrase']);
                $info['rsa_privkey'] = $rsa['privkey'];
                $info['rsa_passphrase'] = $rsa['passphrase'];
            }
            $this->db->update('user', $info)->where(['uid' => $userExisted['uid']])->execute();
            $this->userInfo['uid'] = $userExisted['uid'];
            $this->userInfo['token'] = $newToken;
            $this->userInfo['user_type'] = $userExisted['user_type'];
        }
        return true;
    }


    //注册用户时增加一条空资料
    public function addProfile($uid)
    {
        return $this->db->insertInto("user_profile", array('uid' => $uid))->execute();
    }
}