<?php

namespace Base\Model;

class Common 
{
	protected static $instance = array();

	public static function getInstance()
	{
		//调用的类名
		$className = get_called_class();
		//实例数组中是否有该调用的对象
		if (empty(self::$instance[$className])) {
			self::$instance[$className] = new static;
		}
		return self::$instance[$className];
	}

}