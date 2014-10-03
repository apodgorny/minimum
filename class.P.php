<?

	// Profiler
	
	class P {
		private static $_a = [];
		private static $_n = 0;
		
		public static function mark($sName) {
			if (!isset(self::$_a[$sName])) {
				self::$_a[$sName] = microtime(true);
				self::$_n ++;
			}
		}
		
		public static function report() {
			$n = 0;
			$aIntervals = [];
			while (true) {
				if ($n < self::$_n-1) {
					$sKey1 = array_keys(self::$_a)[$n];
					$sKey2 = array_keys(self::$_a)[$n+1];
					$aIntervals["$sKey1 -> $sKey2"] = round((self::$_a[$sKey2] - self::$_a[$sKey1]) * 1000, 3) . ' ms';
					$n ++;
				} else {
					break;
				}
			}
			
			debug($aIntervals);
		}
		
	}

?>