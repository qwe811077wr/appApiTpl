<?php

namespace Helper;
use Mailer\PHPMailer;

/**
 * 帮助函数类
 *
 * @author tianweimin
 */
class Helper {
    
    /**
     * 发送邮件统一接口
     * @param string $reciever_address
     * @param string $subject
     * @param string $body
     * @param array $cc_items
     * @return boolean
     */
    static public function sendMail($reciever_address, $subject, $body, $cc_items = array())
    {
            $cfg = \Params\Param::$_mailCfg;
            $paramInfo = [
                'reciever_address' => trim($reciever_address),
                'subject' => trim($subject),
                'body' => trim($body),
                'cc_items' => $cc_items,
            ];
            
            $mail = new PHPMailer();
            //$mail->SMTPDebug = 3;                               // Enable verbose debug output
            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = $cfg['smtp_host'];  // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = $cfg['username'];                 // SMTP username
            $mail->Password = $cfg['password'];                           // SMTP password
            $mail->SMTPSecure = $cfg['secure_type'];                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = $cfg['port'];                                    // TCP port to connect to
            $mail->CharSet = "UTF-8"; 

            $mail->setFrom($cfg['sender_address'], $cfg['sender_name']);
            $mail->addAddress($paramInfo['reciever_address']);     // Add a recipient
            
            foreach ($paramInfo['cc_items'] as $_v) {
                $mail->addCC($_v);
            }
             
            $mail->isHTML(true);                                  // Set email format to HTML

            $mail->Subject = $paramInfo['subject'];
            $mail->Body    = $paramInfo['body'];
            $mail->AltBody = strip_tags($paramInfo['body']);
            
            if ($mail->send()) {
                return true;
            } else {
                return false;
            }
    }
    
    /**
     * 随机生成字符
     * @param int $length
     * @return string
     */
    static public function stringGen($length = 8)
    {
        $str = '';
        for($i = 0; $i <= $length; $i++)
        {
            $str .= chr(rand(0, 127));
        }
        return $str;
    }
    
    /**
     * 统一获取参数配置
     * @param (string | array) $conf
     * @return (string | array)
     */
    static public function getConf($conf)
    {
        $resConf = [];
        $allConfig = \Yaf\Registry::get('config');
        if (is_array($conf)) {
            foreach ($conf as $value) {
                $resConf[$value] = $allConfig->$value->toArray();
            }
        } else {
            $resConf = $allConfig->toArray()[$conf];
        }
        return $resConf;
    }
    
    /**
     * 生成32位token字符串
     * @param int $size token`s length
     * @return string
     */
    static public function GenToken($size = 32)
    {
        $token = '';
        $str = '1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM@';
        for ($i = 0; $i <= $size; $i++)
        {
            $token .= $str[rand(0, strlen($str) - 1)];
        }
        return $token;
    }
    
    /**
     * 生成uuid
     **/
    static public function GenUuid() 
    {
        $buff = str_split(strtoupper(md5(uniqid('', true))), 4);
        return implode('-', [$buff[0], $buff[1], $buff[2], $buff[3], $buff[4], $buff[5], $buff[6], $buff[7]]);
    }
    
    /**
     * 获取客户端ip
     **/
   static public function ip() 
   {
       $ip = '';
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : "0.0.0.0";;
        }
        $arr = explode(',', $ip);
        return $arr[0];
   }

   /**
    * 校验手机号码
    */
    public static function getPhone($phone)
    {
        $phone = str_replace('-', '', $phone);
        if (!preg_match('/1\d{10}$/', $phone)) {
            return false;
        }
        return substr($phone, -11);
    }

}
