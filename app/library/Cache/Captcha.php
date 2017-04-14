<?php
namespace Cache;

use Helper\Helper;
use Params\EC;
use Exception\InputException;

/**
 * 登录注册验证码
 *
 * @author tianweimin
 */


class Captcha extends \Base\Model\Redis {
    
    //有效期
    const EXPIRE = 600;
    
    //前缀
    const PREFIX = 'CACHE:CAPTCHA:%s';
    
    //是否锁定
    const PREFIX_LOCK = 'CACHE:CAPTCHA:LOCK:%s';

    //当天登录次数
    const PREFIX_COUNTS = 'CACHE:CAPTCHA:COUNTS:%s:%s';//电话:日期
    
    //保存一个验证码
    public function save($phone)
    {
        $key = sprintf(self::PREFIX, $phone);
        $vcode = sprintf('%04d', rand(1000, 9999));
        $this->redis->setex($key, self::EXPIRE, $vcode);

        // 两次间隔时间
        $conf = Helper::getConf('captcha');
        $lkey = sprintf(self::PREFIX_LOCK, $phone);
        $this->redis->setex($lkey, $conf['interval'], time());

        // 每日发送次数
        $ckey = sprintf(self::PREFIX_COUNTS, $phone, date('Ymd'));
        $this->redis->incr($ckey);
        $this->redis->expire($ckey, 86400);
        return $vcode;
    }
    
    //校验验证码
    public function isVerified($phone, $vcode) {
        $key = sprintf(self::EXPIRE, $phone);
        if ($this->redis->get($key) != $vcode) {
            return false;
        }
        $this->redis->del($key);
        return true;
    }
    
    //检查发送验证码条件
    public function isDenied($phone) {
        $conf = Helper::getConf('captcha');
        $phone = Helper::getPhone($phone);
        if (!$phone) {
            throw new InputException(EC::PHONE_FOMATE_E);
        }
        $lkey = sprintf(self::PREFIX_LOCK, $phone);
        if ($this->redis->exists($lkey)) {
            throw new InputException(EC::SMS_OFTEN);
        }
        //验证每天发送总次数
        $ckey = sprintf(self::PREFIX_COUNTS, $phone, date("Ymd"));
        $nkey = $this->redis->get($ckey);
        if ($nkey >= $conf['limit']) {
            throw new InputException(EC::SMS_LIMIT_DAY, printf(LANG::$errors[EC::SMS_LIMIT_DAY]), $conf['limit']);
        }
        return true;
    }
    
}
