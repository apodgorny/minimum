<?
	
	class Request {
		
		/******************* PUBLIC *******************/
		
		public static function path() {
			return M::REQUEST_PATH();
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
				if (isset($_REQUEST[$sParamName])) {
					$sParam = trim($_REQUEST[$sParamName]);
					
					if ($sParam === '' && $bRequired) {
						throw new Exception("Parameter \"$sParamName\" is empty");
					}
					
					$mParam = json_decode($sParam, true);
					
					if (!$mParam && strlen($sParam) > 0) {
						$mParam = $sParam;
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
	}

?>