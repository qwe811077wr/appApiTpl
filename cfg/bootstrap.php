<?php

class bootstrap extends \Yaf\Bootstrap_Abstract
{

	private $_config;

	//初始化设置：禁用模板、开启错误捕获
	public function _initConfig()
	{
		Yaf\Dispatcher::getInstance()->disableView();
		Yaf\Dispatcher::getInstance()->catchException(true);
	}

	//注册本地类库
	public function _initRegistLocalLib()
	{
		$this->_config = Yaf\Application::app()->getConfig();
        Yaf\Registry::set("config", $this->_config);
		Yaf\Loader::getInstance($this->_config->application->localLibrary)
            ->registerLocalNamespace(array('Base', 'Exception', 'Params', 'Helper', 'Cache', 'Lib'));
	}
}