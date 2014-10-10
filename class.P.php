<?php

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
			$nTotalTime = 0;
			debug('---------- Performance ----------');
			while (true) {
				if ($n < self::$_n-1) {
					$sKey1 = array_keys(self::$_a)[$n];
					$sKey2 = array_keys(self::$_a)[$n+1];
					$nTimeInterval = (self::$_a[$sKey2] - self::$_a[$sKey1]) * 1000;
					$nTotalTime += $nTimeInterval;
					$aIntervals["$sKey1 -> $sKey2"] = round($nTimeInterval, 4) . ' ms';
					debug(str_pad("$sKey1 -> $sKey2 ", 50, '-'), round($nTimeInterval, 4) . ' ms');
					$n ++;
				} else {
					break;
				}
			}
			
			debug(str_pad("Total ", 50, '-'), round($nTotalTime, 4) . ' ms');
		}
		
	}

?>