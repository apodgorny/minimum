<?

	class Processor {
		
		public static function evalString($s, $aContextVariables=[], $bFlushBuffer=true) {
			ob_start();
			extract($aContextVariables);
			eval('?>' . $s . '<?');
			$sResult = ob_get_contents();
			ob_end_clean();
			
			if ($bFlushBuffer) {
				print $sResult;
			} else {
				return $sResult;
			}
		}
		
		public static function evalFile($sFile, $aContextVariables=[], $bFlushBuffer=true) {
			if (file_exists($sFile)) {
				return self::evalString(file_get_contents($sFile), $aContextVariables, $bFlushBuffer);
			} else {
				throw new Exception("File $sFile does not exist");
			}
		} 
	}

?>