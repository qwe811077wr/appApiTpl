<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use \Helper\Helper;

class Factory {
	
	protected static $dbInstances = NULL;

	protected static $logInstances = NULL;

	protected static $redisInstance = NULL;

    protected static $redisPrefix = 'jiangxiaotian:';

    public static function db($dbConfig = 'default')
	{
		if (!is_array($dbConfig)) 
		{
			$dbConfig = Helper::getConf('db')[$dbConfig]['args'];
		}

		$dbIndex = md5(serialize($dbConfig));
		if (!isset(self::$dbInstances[$dbIndex])) 
		{
			class_exists('FluentPDO', false) || Yaf\Loader::getInstance()->import(Yaf\Loader::getInstance()->getLibraryPath(true) . 'FluentPDO/FluentPDO.php');
            self::$dbInstances[$dbIndex] = new FluentPDO($dbConfig);
		}
		return self::$dbInstances[$dbIndex];
	}

	public static function log($section = 'default')
	{
		if (!isset(self::$logInstances[$section])) {
			$logConfig = Helper::getConf('log')[$section];
			$streamHandler = new StreamHandler(sprintf($logConfig['path'], date('Y'), date('m'), date('d')), Logger::DEBUG);
            $monlog = new Logger($section);
            $monlog->pushHandler($streamHandler);
            self::$logInstances[$section] = $monlog;
		}

		return self::$logInstances[$section];
	}
    
    public static function redis()
    {
        $redisConfigs = Helper::getConf('redis');
        $redisConfig = $redisConfigs[array_rand($redisConfigs)];
        if (!isset(self::$redisInstance)) {
            $connection = new \Redis();
            $connection->connect($redisConfig['host'], $redisConfig['port']);
            $connection->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_NONE);
            $connection->setOption(\Redis::OPT_PREFIX, self::$redisPrefix);
            self::$redisInstance = $connection;
        }
        return self::$redisInstance;
    }
    
}