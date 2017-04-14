<?php
/**
 * 控制器基础接口
 */
namespace Base\Controller;

abstract class Common extends \Yaf\Controller_Abstract
{
	public function init(){
        //请求记录
        $moduleName = '\\' . $this->getRequest()->getModuleName();
        $controllerName = '\\' . $this->getRequest()->getControllerName();
        $ActionName = '\\' . $this->getRequest()->getActionName();
        \Factory::log()->addInfo('request url:' . $moduleName . $controllerName . $ActionName);
	}

	/**
	 * 数据格式化
	 */
	protected function output($data, $code = 0, $msg = '')
	{
		$result = array(
				'code' => $code,
				'msg'  => $msg,
				'data' => $data
			);
		echo json_encode($result);
	}

	/**
	 * 错误信息
	 */
	protected function err($code){
		$msg = \LANG::$_errors[\EC::UNKNOWN];
		if (isset(\LANG::$_errors[$code])) {
			$msg = \LANG::$_errors[$code];
		}
		return json_encode(array('code' => $code, 'msg' => $msg));
	}
}