<?php
namespace Easemobile;
use \Easemob\Easemob;
use \Helper\Helper;

class Service {

	static public $mapInstance = NULL;

	public $easemobServ = NULL;
	public $redcon = NULL;

	public $client_id = '';
	public $client_secret = '';
	public $app_key = '';
	public $org_name = '';
	public $app_name = '';
	
	public function __construct()
	{
		$conf = Helper::getConf('easemob');
		$this->client_id = $conf['ClientId'];
		$this->client_secret = $conf['ClientSecret'];
		$this->app_key = $conf['AppKey'];
		$this->easemobServ = new Easemob(['client_id' => $this->client_id, 'client_secret' => $this->ClientSecret]);
		$this->redcon = \Factory::redis();
	}

	static public function getInstance()
	{
		if (empty(self::$mapInstance)) {
			self::$mapInstance = new self;
		}
		return self::$mapInstance;
	}

	public static function __callStatic($name, $args)
	{
		$me = self::$mapInstance;
		if (method_exists($me->easemobServ, $name)) {
			return call_user_func_array([$me->easemobServ, $name], $args);
		}
		return false;
	}

}