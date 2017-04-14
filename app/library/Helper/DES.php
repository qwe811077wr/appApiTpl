<?php
namespace Helper;

/**
* DES加解密
*/
class DES
{
	
	private $salt;

	function __construct($salt = '')
	{
		if (empty($salt)) {
			$this->salt = Helper::stringGen(16);
		} else {
			$this->salt = $salt;
		}
	}

	static function encodeData($data, $salt = '')
	{
		if ($salt) {
			
		} else {
			$salt = $this->salt;
		}
	}

	static function decodeData($ciphertext, $salt = '')
	{
		
	}
}