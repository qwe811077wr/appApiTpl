<?php

namespace Params;

class LANG
{
	static $_errors = array(
            //全局通用
		EC::BLANK 			=> '',
		EC::SUCCESS 		=> '成功',
		EC::UNKNOWN 		=> '未知错误',
		EC::SYSTEM 			=> '系统错误',
		EC::PARARM 			=> '参数错误',
        
            //验证相关
            EC::LACK_PARAM_TIME => '缺少参数时间戳',
            EC::API_TIME_OUT    => '接口调用超时',
            EC::VERIFY_SIGN    => '签名验证错误',
            EC::UUID_OR_UID    => 'uuid或uid必须有且只有一个',
            EC::UUID_ILLEGAL   => 'uuid不合法',
            EC::UID_ILLEGAL    => 'uid用户为空',
            EC::DEC_PASSPHRASE => '解密数据缺少参数passpahase',
            EC::DEC_PRIVKEY    => '解密数据缺少参数privkey',
            EC::RSA_DECODE     => '解密数据失败',
            EC::JSON_DECODE    => '获取JSON失败',
            EC::LACK_SIGN_DATA => '缺少解密参数sign或data',
            EC::HTTP_USER_AGENT => '未知的客户端请求',
            EC::OPENSSL_GEN => '秘钥生成失败',
            EC::PHONE_FOMATE_E => '手机号码格式错误',
            EC::SMS_OFTEN => '验证码发送过于频繁',
            EC::SMS_LIMIT_DAY => '验证码每天只能发送%d次',
            EC::UPLOAD_OUT_NUMS_LIMIT => '图片最多只能%d张',

            EC::CAPTCHA_FORMATE_E => '验证码格式错误',
            EC::CAPTCHA_E => '验证码错误',

            //用户相关
            EC::CAPTCHA_FORMATE_E => '验证码必须为数字',
            EC::CAPTCHA_E => '验证码错误',
            EC::UESR_TOKEN_E => '生成用户令牌错误',
            EC::UESR_STAT_E => '用户状态被禁止',
	);
    
    const BLANK = '';
}