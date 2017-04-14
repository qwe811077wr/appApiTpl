<?php

use Helper\Helper;
use Helper\RSA;

class SmsLogModel extends Base\Model\Db
{
    public function log($mobile, $op, $content, $ipadr)
    {
    	$res = \Factory::db()->insertInto('sms_log', array(
    		'phonenum' => $mobile, 
    		'op' => $op, 
    		'ipadr' => $ipadr, 
    		))->excute();
    	return $res;
    }
}