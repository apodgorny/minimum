<?php
	
	class Request {
		
		private static $_aHeaders = [];
		private static function _strReplaceFirst($sNeedle, $sReplacement, $sHaystack) {
			if ($sNeedle && $sHaystack) {
				$nPosition = strpos($sHaystack, $sNeedle);
				if ($nPosition !== false) {
				    return substr_replace($sHaystack, $sReplacement, $nPosition, strlen($sNeedle));
				}
			}
			return $sHaystack;
		}
		
		/******************* PUBLIC *******************/

		public static function path() {
			return isset($_SERVER['REQUEST_URI'])
				? explode('?', self::_strReplaceFirst($_ENV['SETTINGS']['SITE_PATH'], '', $_SERVER['REQUEST_URI']))[0]
				: '';
		}
		
		public static function getHeader($sHeader) {
			if (!self::$_aHeaders) {
				self::$_aHeaders = getallheaders();
			}
			return self::$_aHeaders[$sHeader];
		}
		
		public static function subdomain() {
			$a = explode('.', $_SERVER['HTTP_HOST']);
			array_pop($a);
			return implode('.', $a);
		}
		
		public static function param($sParamName, $bRequired=true) {
			if (is_int($sParamName)) {
				$aUrlParts = explode('/', trim(self::path(), ' /'));
				if (isset($aUrlParts[$sParamName])) {
					return $aUrlParts[$sParamName];
				} else {
					if ($bRequired) {
						throw new Exception("Numbered parameter \"$sParamName\" is missing in request");
					} else {
						return null;
					}
				}
			} else {
				if (isset($_REQUEST[$sParamName]) && $_REQUEST[$sParamName]) {
					$sParam = trim($_REQUEST[$sParamName]);
					$mParam = json_decode($sParam, true);
					
					// Hack for a double encode non–UTF8 parameters
					if (gettype($mParam) === 'string') {
						$mNewParam = json_decode($mParam, true);
						// Hack for a hack - will break if values "NULL" or deeply (>512 nested JSON
						// are double json encoded - rare corner case, see http://php.net/manual/en/function.json-decode.php
						if ($mNewParam !== null) {
							$mParam = $mNewParam;
						}
					}

					if ($mParam === null && strlen($sParam) > 0 && $sParam != '""') {
						$mParam = $sParam;
					}

					if ($bRequired && !$mParam) {
						throw new Exception("Parameter \"$sParamName\" is empty");
					}
				
					return $mParam;
				} else {
					if ($bRequired) {
						throw new Exception("Parameter \"$sParamName\" is missing in request");
					} else {
						return null;
					}
				}
			}

		}
		
		public static function url() {
			$bHttps    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true:false;
			$sProtocol = strtolower($_SERVER['SERVER_PROTOCOL']);
			$sProtocol = substr($sProtocol, 0, strpos($sProtocol, '/')) . (($bHttps) ? 's' : '');
			$nPort     = $_SERVER['SERVER_PORT'];
			$nPort     = ((!$bHttps && $nPort=='80') || ($bHttps && $nPort=='443')) ? '' : ':'.$nPort;
			$sHost     = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
			$sHost     = $sHost ? $sHost : $_SERVER['SERVER_NAME'] . $nPort;
			return $sProtocol . '://' . $sHost . $_SERVER['REQUEST_URI'];
		}
		
		public static function isHttps() {
			return 
				(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || 
				(isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
		}
	}

?>