<?php

	class M {
		public static function __callStatic($sMethod, $aParams=[]) {
			$sMethod = strtoupper($sMethod);
			if (isset($_ENV['SETTINGS'][$sMethod])) {
				return $_ENV['SETTINGS'][$sMethod];
			}
			return null;
		}
		
		public static function url($sUrl, $sPort=null) {
			return Abs::absolutize($sUrl, $sPort);
		}
		
		public static function path($sPath) {
			return implode(DIRECTORY_SEPARATOR, array_merge(
				explode(DIRECTORY_SEPARATOR, getCwd()),
				explode(DIRECTORY_SEPARATOR, $sPath)
			));
		}
	}

?>