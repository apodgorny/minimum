<?php

	class T {
		
		/**************** PRIVATE ****************/
		
		private static $_aJs  = [];
		private static $_aCss = [];
		
		private static function _findJsFile($sTemplate) {
			if ($aDirs = M::TEMPLATE_JS()) {
				foreach ($aDirs as $sDir) {
					$sPath = M::PROJECT_ROOT() . '/' . $sDir . '/tpl.' . $sTemplate . '.js';
					if (file_exists($sPath)) {
						self::$_aJs[$sTemplate] = $sPath;
						return;
					}
				}
			}
		}
		
		private static function _findCssFile($sTemplate) {
			if ($aDirs = M::TEMPLATE_CSS()) {
				foreach ($aDirs as $sDir) {
					$sPath = M::PROJECT_ROOT() . '/' . $sDir . '/tpl.' . $sTemplate . '.css';
					if (file_exists($sPath)) {
						self::$_aCss[$sTemplate] = $sPath;
						return;
					}
				}
			}
		}
		
		/**************** PUBLIC ****************/
		
		public static function __callStatic($sTemplate, $aArgs) {
			if ($aDirs = M::TEMPLATE_PHP()) {
				foreach ($aDirs as $sDir) {
					$sPath = M::PROJECT_ROOT() . '/' . $sDir . '/tpl.' . $sTemplate . '.php';
					if (file_exists($sPath)) {
						self::_findJsFile($sTemplate);
						self::_findCssFile($sTemplate);
						if (isset($aArgs[0])) {
							print Processor::evalString(file_get_contents($sPath), $aArgs[0]);
						}
						return;
					}
				}
			}
			throw new Exception("Template \"$sTemplate\" is not found");
		}
		
		public static function getJs() {
			$sJs = '';
			foreach (self::$_aJs as $sTemplate => $sFileName) {
				$sJs .= "/***** $sTemplate *****/\n" . file_get_contents($sFileName) . "\n";
			}
			return $sJs;
		}
		
		public static function getCss() {
			$sCss = '';
			foreach (self::$_aCss as $sTemplate => $sFileName) {
				$sCss .= "/***** $sTemplate *****/\n" . file_get_contents($sFileName) . "\n";
			}
			return $sCss;
		}
	}

?>