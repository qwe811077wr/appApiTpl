<?php

namespace Params;

class EC
{
	//全局通用类
	const SUCCESS = 0x0000;
	const UNKNOWN = 0x0001;
	const SYSTEM  = 0x0002;
	const PARARM  = 0x0003;
	const BLANK   = 0x0004;
    
    //验证相关
    const LACK_PARAM_TIME   = 0x0010;
    const API_TIME_OUT       = 0x0011;
    const VERIFY_SIGN        = 0x0012;
    const UUID_OR_UID        = 0x0013;
    const UUID_ILLEGAL       = 0x0014;
    const UID_ILLEGAL        = 0x0015;
    const DEC_PASSPHRASE     = 0x0016;
    const DEC_PRIVKEY        = 0x0017;
    const RSA_DECODE         = 0x0018;
    const JSON_DECODE        = 0x0019;
    const LACK_SIGN_DATA     = 0x001A;
    const HTTP_USER_AGENT     = 0x001B;
    const OPENSSL_GEN         = 0x001C;
    const PHONE_FOMATE_E      = 0x001D;
    const SMS_OFTEN           = 0x001E;
    const SMS_LIMIT_DAY       = 0x001F;

    const UPLOAD_OUT_NUMS_LIMIT = 0x0020;
    const UPLOAD_FAILED         = 0x0021;

    //用户相关
    const CAPTCHA_FORMATE_E     = 0x0100;
    const CAPTCHA_E     = 0x0101;
    const UESR_TOKEN_E     = 0x0102;
    const UESR_STAT_E     = 0x0103;
}