<?

	class Minimum {
		public static function realpath($sPath) {
			$sPrefix = ($sPath[0] === '/') ? '/' : '';
			$a     = explode('/', trim($sPath, '/'));
			$aOut  = [];
			
			foreach($a as $s){
				if ($s != '.' && !empty($s)) {
					if ($s == '..') { array_pop($aOut);      }
					else            { array_push($aOut, $s); }
				}
			}
			return $sPrefix . implode('/', $aOut);
		}
		
		public static function __callStatic($sMethod, $aParams=[]) {
			$sMethod = strtoupper($sMethod);
			if (isset($_ENV['SETTINGS'][$sMethod])) {
				return $_ENV['SETTINGS'][$sMethod];
			}
			return null;
		}
		
		public static function url($sUrl) {
			return Abs::absolutize($sUrl);
		}
		
		public static function path($sPath) {
			return self::realpath(implode(DIRECTORY_SEPARATOR, array_merge(
				explode(DIRECTORY_SEPARATOR, getCwd()),
				explode(DIRECTORY_SEPARATOR, $sPath)
			)));
		}
	}

?>