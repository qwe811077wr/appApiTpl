<?php
namespace Helper;

class Curl {
     /*
     * HTTP请求辅助函数
     *
     * 对CURL使用简单封装，实现POST与GET请求
     *
     * @param String  URL地址
     * @param Array   参数数组
     * @param Array   HTTP头
     * @param Boolean 是否使用POST方式请求
     * @param Array   服务返回的JSON数组
     */
    public static function HTTP($url, $param = array(), $isPOST = false, $header = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if (!empty($header))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        if ($isPOST)
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        }
        else if (!empty($param))
        {
            $xi   = parse_url($url);
            $url .= empty($xi['query']) ? '?' : '&';
            $url .= http_build_query($param);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        return curl_exec($ch);
    }
}