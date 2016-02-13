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

			// AP: Not sure why I ever added this, but it causes port 80 being added to https
			// Talk to me if you must add this back

			// if (!$sPort) {
			// 	if (preg_match("|^https://|", $sUrl)) {
			// 		$sPort = M::HTTPS_PORT();
			// 	} else {
			// 		$sPort = M::HTTP_PORT();
			// 	}
			// }
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