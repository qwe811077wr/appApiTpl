<?php

namespace Params;

/**
 * 常用参数
 *
 * @author tianweimin
 */
class Param {
    /**
     * 发送邮件的发件人配置
     */
    static $_mailCfg =  [
                'smtp_host' => 'smtp.exmail.qq.com',
                'username' => 'dev@ishaohuo.cn',
                'password' => 'Ishaohuo123',
                'sender_address' => 'dev@ishaohuo.cn',
                'sender_name' => '捎货运维中心',
                'secure_type' => 'TLS',
                'port' => 25
            ];
}
