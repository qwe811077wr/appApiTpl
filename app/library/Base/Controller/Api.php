<?php
/**
 * 所有接口父类
 */
namespace Base\Controller;

use Helper\RSA;
use Params\LANG;
use Params\EC;
use Helper\Helper;

class Api extends Common
{
    const ENCRYPT_SWITCH = 1;//加密总开关
    protected $encrypt = 1;//具体接口加密开关

    //参数校验状态
    const V_STAT_UUID = 0x01;
    const V_STAT_TIME = 0x02;
    const V_STAT_JSON = 0x04;
    const V_STAT_SIGN = 0x08;
    
    //参数校验过程
    protected $_verified = 0x00;

    //输入参数
    protected $jsonParam = [];
    protected $request = [];
    protected $timestamp = NULL;
    
    protected $rsa_privkey      = NULL;
    protected $rsa_passphrase   = NULL;
    //用户信息
    protected $user_id         = 0;
    protected $user_token      = NULL;
    protected $user_info       = [];

    protected $user_table = 'user';
    protected $user_prikey = 'uid';
    protected $isBindUser = false;


    public function init()
	{
		parent::init();
        $actionName = $this->getRequest()->getActionName();
        $openApis = array_map("strtolower", static::loadPublicApi());
        if (in_array($actionName, $openApis) || $openApis[0] == '*') {
            $this->encrypt = 0;
        }
        $this->paramInput();
	}

    /**
     * 加载数据
     */
    protected function paramInput()
    {
        $this->request = $this->getRequest()->getRequest();
        if (!self::ENCRYPT_SWITCH || !$this->encrypt) {
            foreach ($this->request as $key => $value) {
                \Factory::log()->addInfo("input: {$key} = {$value}");
                $this->jsonParam[$key] = $value;
            }
        } else {
            $uuid = intval(@$this->request['uuid']);
            $uid  = intval(@$this->request['uid']);
            $sign = @$this->request['sign'];
            $data = @$this->request['data'];
            if (!(empty($uuid)^empty($uid))) {
                \Factory::log()->addError('(uuid|uid) must have and only one');
               throw new \Exception\InputException(EC::UUID_OR_UID);
            }
            if (empty($sign) || empty($data)) {
                \Factory::log()->addError(Lang::$_errors[EC::LACK_SIGN_DATA]);
                throw new \Exception\InputException(EC::LACK_SIGN_DATA);
            }
            $this->loadRSA();
        }
    }
    
    //延迟绑定不需要加密的接口
    static protected function loadPublicApi()
    {
        return [];
    }

    /**
     * 根据UUID或者UID加载相关的秘钥信息
     */
    protected function loadRSA()
    {
        $uuid = intval(@$this->request['uuid']);
        $uid  = intval(@$this->request['uid']);
        //时间戳校验
        $this->checkTimestamp();
        if (!empty($uuid)) {
            $rsaInfo = \Cache\UUID::query($uuid);
            if (empty($rsaInfo)) {
                \Factory::log()->addError(LANG::$_errors[EC::UUID_ILLEGAL], $uuid);
                throw new \Exception\InputException(EC::UUID_ILLEGAL);
            }
            $this->rsa_privkey      = $rsaInfo['privkey'];
            $this->rsa_passphrase   = $rsaInfo['passphrase'];
        } elseif(!empty($uid)) {
            $userInfo = \Factory::db()->from($this->user_table)->where(array($this->user_prikey => $uid))->fetch();
            if (empty($userInfo)) {
                \Factory::log()->addError(LANG::$_errors[EC::UID_ILLEGAL], [$uuid]);
                throw new \Exception\InputException(EC::UID_ILLEGAL);
            }
            $this->rsa_privkey          = $userInfo['rsa_privkey'];
            $this->rsa_passphrase       = $userInfo['rsa_passphrase'];
            $this->user_id              = $userInfo['uid'];
            $this->user_token           = $userInfo['token'];
            $this->user_info            = $userInfo;
            $this->isBindUser           = true;
        } else {
            throw new \Exception\InputException(EC::UUID_OR_UID);
        }
        //绑定了用户的话需要验证签名
        if ($this->isBindUser) {
            $this->verifySign();
        }
        $this->decodeData();
    }

    /**
     * 验证签名
     * 签名规则
     * md5(md5(data) . token)
     */
    protected function verifySign()
    {
        $sign = $this->request['sign'];
        $data = $this->request['data'];
        $userToken = $this->user_token;
        if ($sign != strtolower(md5(strtolower(md5($data)) . $userToken))) {
            \Factory::log()->addError(Lang::$_errors[EC::VERIFY_SIGN], ['user token' => $userToken, 'sign' => strtolower(md5(strtolower(md5($data)) . $userToken))]);
            throw new \Exception\InputException(EC::VERIFY_SIGN);
        }
        //签名通过
        $this->_verified |= self::V_STAT_SIGN;
    }
    
    /**
     * 解密数据
     */
    protected function decodeData()
    {
        if (empty($this->request['data'])) {
            return;
        }
        if (empty($this->rsa_passphrase)) {
             \Factory::log()->addError(LANG::$_errors[EC::DEC_PASSPHRASE]);
            throw new \Exception\InputException(EC::DEC_PASSPHRASE);
        }
        if (empty($this->rsa_privkey)) {
             \Factory::log()->addError(LANG::$_errors[EC::DEC_PRIVKEY]);
            throw new \Exception\InputException(EC::DEC_PRIVKEY);
        }
        $queryStr = RSA::getPrivate($this->rsa_privkey, $this->rsa_passphrase)->dec(rawurldecode($this->request['data']));
        if (empty($queryStr)) {
            \Factory::log()->addError(LANG::$_errors[EC::RSA_DECODE]);
            throw new \Exception\InputException(EC::RSA_DECODE);
        }
        $this->jsonParam = json_decode($queryStr, true);
        foreach ($this->jsonParam as $key => $value) {
            $this->jsonParam[$key] = trim($value);
            \Factory::log()->addInfo(sprintf('input: %s = %s', $key, $value));
        }
        if ($this->jsonParam['time'] != $this->timestamp) {
            throw new \Exception\InputException(EC::JSON_DECODE);
        }
        $this->_verified |= self::V_STAT_JSON;
    }

    //校验时间戳
    protected function checkTimestamp()
    {
        if (!isset($this->request['time'])) {
            throw new \Exception\InputException(EC::LACK_PARAM_TIME);
        }
        $this->timestamp = $this->request['time'];
        $timeDifference = (int) Helper::getConf('api')['timeDifference'];
        if (abs($this->timestamp - time()) > $timeDifference) {
           throw new \Exception\InputException(EC::API_TIME_OUT);
        }
        $this->_verified |= self::V_STAT_TIME;
        return true;
    }

    /**
	 * 格式化输出数据
	 */
	protected function output($data, $code = 0, $msg = '')
	{
        if (Helper::getConf('logDebug')) {
            \Factory::log()->info('output:', $data);
        }
        if (self::ENCRYPT_SWITCH && $this->encrypt) {
            $data = RSA::getPrivate($this->rsa_privkey, $this->rsa_passphrase)->enc(json_encode($data));
        }
		return parent::output($data, $code, $msg);
	}

	/**
	 * 错误信息输出
	 */
	protected function err($code, $customMsg = NULL)
	{
		$msg = LANG::$_errors[EC::UNKNOWN];
		if (isset(LANG::$_errors[$code])) {
			$msg = LANG::$_errors[$code];
		}
		if (!empty($customMsg)) {
			$msg = $customMsg;
		}
		$this->output([LANG::$_errors[EC::BLANK]], $code, $msg);
	}
}