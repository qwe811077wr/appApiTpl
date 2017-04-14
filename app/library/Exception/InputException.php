<?php
namespace Exception;

/**
* 需要抛出的异常类
*/
class InputException extends \Exception
{
	public function getECCode()
    {
        return $this->getMessage();
    }

    public function getECMsg()
    {
    	return $this->getCode();
    }
}
