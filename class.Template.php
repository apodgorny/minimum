<?

	class Template {
		
		/**************** PRIVATE ****************/
		
		private static $_aJs  = [];
		private static $_aCss = [];
		
		private static function _findJsFile($sTemplate) {
			if ($aDirs = Minimum::TEMPLATE_JS()) {
				foreach ($aDirs as $sDir) {
					$sPath = Minimum::PROJECT_PATH() . '/' . $sDir . '/tpl.' . $sTemplate . '.js';
					if (file_exists($sPath)) {
						self::$_aJs[] = $sPath;
						return;
					}
				}
			}
		}
		
		private static function _findCssFile($sTemplate) {
			if ($aDirs = Minimum::TEMPLATE_CSS()) {
				foreach ($aDirs as $sDir) {
					$sPath = Minimum::PROJECT_PATH() . '/' . $sDir . '/tpl.' . $sTemplate . '.css';
					if (file_exists($sPath)) {
						self::$_aCss[] = $sPath;
						return;
					}
				}
			}
		}
		
		/**************** PUBLIC ****************/
		
		public static function get($sTemplate, $aParams) {
			if ($aDirs = Minimum::TEMPLATE_PHP()) {
				foreach ($aDirs as $sDir) {
					$sPath = Minimum::PROJECT_PATH() . '/' . $sDir . '/tpl.' . $sTemplate . '.php';
					if (file_exists($sPath)) {
						self::_findJsFile($sTemplate);
						self::_findCssFile($sTemplate);
						return Processor::evalString(file_get_contents($sPath), $aParams);
					}
				}
			}
			throw new Exception("Template \"$sTemplate\" is not found");
		}
		
		public static function getJs() {
			$sJs = '';
			foreach (self::$_aJs as $sFileName) {
				$sJs .= "/***** $sFileName *****/\n" . file_get_contents($sFileName) . "\n";
			}
			return $sJs;
		}
		
		public static function getCss() {
			$sCss = '';
			foreach (self::$_aCss as $sFileName) {
				$sCss .= "/***** $sFileName *****/\n" . file_get_contents($sFileName) . "\n";
			}
			return $sCss;
		}
	}

?>