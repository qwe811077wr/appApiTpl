<?php
	
	namespace Helper;
	
    use \Params\LANG;
    use \Params\EC;
    
	class RSA
	{
		const PADDING   = OPENSSL_PKCS1_PADDING;
		const CHUNKSIZE	= 64;
		const PACKAGE   = 256;
		
		/**
		 * 生成RSA对
		 *
		 */
		public static function gen()
		{
			$passphrase = Helper::stringGen(8);
			$rsaconf = Helper::getConf('RSA');
			$privkey = NULL;
			$certout = NULL;
			$csrout  = NULL;
			$pkeyGen = NULL;
			if (\YAF\Application::app()->environ() == 'dev')
			{
				$pkeyGen = openssl_pkey_new(array(
					'digest_alg'		=> $rsaconf['algorithm'],
					'private_key_bits'	=> $rsaconf['keybits'],
					'private_key_type'	=> OPENSSL_KEYTYPE_RSA,
					'encrypt_key'		=> true
				));
			}
			else
			{
				$pkeyGen = openssl_pkey_new();
			}
			if (empty($pkeyGen))
			{
				$message = openssl_error_string();
				\Factory::log()->error(sprintf("OpenSSL pkey_new ERROR: %s", $message));
				throw new \Exception\InputException(EC::OPENSSL_GEN);
			}
			openssl_pkey_export($pkeyGen, $privkey, $passphrase);
			$result = openssl_pkey_get_details($pkeyGen);
			$pukey	= $result['key'];
			$csr	= openssl_csr_new(array(
				"countryName"			=> $rsaconf['country'],
				"stateOrProvinceName"	=> $rsaconf['province'],
				"localityName"			=> $rsaconf['city'],
				"organizationName"		=> $rsaconf['organize']
			), $pkeyGen);
			$sscert	= openssl_csr_sign($csr, null, $pkeyGen, $rsaconf['expire']);
			// ** cert (.cert)
			openssl_x509_export($sscert, $certout);
			// ** csr (.csr)
			openssl_csr_export($csr, $csrout);
			return array(
				'privkey'    => $privkey,
				'csrkey'     => $csrout,
				'certkey'    => $certout,
				'rsapukey'   => $pukey,
				'passphrase' => base64_encode($passphrase)
			);
		}
		
		/**
		 * 由私钥生成获取公钥
		 *
		 */
		public static function getPublicKeyFromPriv($privkey, $passphrase)
		{
			$data = openssl_pkey_get_private(
				$privkey,
				base64_decode($passphrase)
			);
			$result = openssl_pkey_get_details($data);
			return $result['key'];
		}
		
		/**
		 * 公钥字符串格式转PEM格式
		 * 
		 */
		public static function strToPEM($buf)
		{
			$arr = str_split($buf, 64);
			array_unshift($arr, '-----BEGIN PUBLIC KEY-----');
			array_push($arr, '-----END PUBLIC KEY-----');
			return implode("\n", $arr);
		}
		
		/**
		 * 公钥PEM转字符串格式
		 *
		 */
		public static function pemToSTR($buf)
		{
			$result = '';
			$arr = explode("\n", $buf);
			foreach ($arr as $line)
			{
				$line = trim($line);
				if (empty($line) || $line[0] == '-')
				{
					continue;
				}
				$result .= $line;
			}
			return $result;
		}
		
		public static function getPrivate($privkey, $passphrase)
		{
			return new PrivateProvider($privkey, $passphrase);
		}
		public static function getPublic($publickey)
		{
			return new PublicProvider($publickey);
		}
	}
	
	/**
	 * 私钥加解密码
	 *
	 */
	class PrivateProvider
	{
		private $privkey    = NULL;
		private $passphrase = NULL;
		private $keyid      = NULL;
		
		public function __construct($key, $pwd)
		{
			$this->passphrase = base64_decode($pwd);
			$this->privkey = $key;
			$this->keyid   = openssl_pkey_get_private(
				$this->privkey,
				$this->passphrase
			);
		}
		
		/**
		 * 加密
		 *
		 */
		public function enc($buffer)
		{
			if (empty($buffer))
			{
				return LANG::BLANK;
			}
			$arrThunk = str_split($buffer, RSA::CHUNKSIZE);
			$result = NULL;
			foreach ($arrThunk as $trunk)
			{
				$temp = LANG::BLANK;
				if (openssl_private_encrypt($trunk, $temp, $this->keyid, RSA::PADDING))
				{
					$result	.= $temp;
				}
				else
				{
					return false;
				}
			}
			return base64_encode($result);
		}
		
		/**
		 * 解密
		 *
		 */
		public function dec($buffer)
		{
			if (empty($buffer))
			{
				return LANG::BLANK;
			}
			$string = base64_decode($buffer);
			if ($string === false || empty($string))
			{
				return LANG::BLANK;
			}
			$arrThrunk = str_split($string, RSA::PACKAGE);
			$result = NULL;
			foreach ($arrThrunk as $trunk)
			{
				$temp = LANG::BLANK;
				if (openssl_private_decrypt($trunk, $temp, $this->keyid, RSA::PADDING))
				{
					$result	.= $temp;
				}
				else
				{
					return false;
				}
			}
			return $result;
		}
	}
	
	/**
	 * 公钥加解密
	 *
	 */
	class PublicProvider
	{
		private $publickey  = NULL;
		private $passphrase = NULL;
		private $keyid      = NULL;
		
		public function __construct($key)
		{
			$this->publickey  = $key;
			$this->keyid = openssl_pkey_get_public($this->publickey);
		}
		
		/**
		 * 加密
		 *
		 */
		public function enc($buffer)
		{
			if (empty($buffer))
			{
				return LANG::BLANK;
			}
			$arrThunk = str_split($buffer, RSA::CHUNKSIZE);
			$result = NULL;
			foreach ($arrThunk as $thunk)
			{
				$temp = LANG::BLANK;
				if (openssl_public_encrypt($thunk, $temp, $this->keyid, RSA::PADDING))
				{
					$result	.= $temp;
				}
				else
				{
					return false;
				}
			}
			return base64_encode($result);
		}
		
		/**
		 * 解密
		 *
		 */
		public function dec($buffer)
		{
			if (empty($buffer))
			{
				return LANG::BLANK;
			}
			$string = base64_decode($buffer);
			if ($string === false || empty($string))
			{
				return LANG::BLANK;
			}
			$arrThrunk = str_split($string, RSA::PACKAGE);
			$result = NULL;
			foreach ($arrThrunk as $thunk)
			{
				$temp = LANG::BLANK;
				if (openssl_public_decrypt($thunk, $temp, $this->keyid, RSA::PADDING))
				{
					$result	.= $temp;
				}
				else
				{
					return false;
				}
			}
			return $result;
		}
	}


?>