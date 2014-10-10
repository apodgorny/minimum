<?php

	class Processor {
		
		public static function evalString($s, $aContextVariables=[], $bFlushBuffer=true) {
			if (class_exists('P')) { P::mark('EVAL_BEGIN'); }
			ob_start();
			extract($aContextVariables);
			if (eval('?>' . $s . '<?') === false) {
				$_ENV['EVAL_ERROR'] = true;
				$_ENV['EVALED_CODE'] = $s;
				file_put_contents(M::PROJECT_ROOT() . '/server/logs/processor.log', $s);
			}
			$sResult = ob_get_contents();
			ob_end_clean();
			if (class_exists('P')) { P::mark('EVAL_END'); }
			
			if ($bFlushBuffer) {
				print $sResult;
			} else {
				return $sResult;
			}
		}
		
		public static function evalFile($sFile, $aContextVariables=[], $bFlushBuffer=true) {
			if (file_exists($sFile)) {
				$_ENV['LAST_EVALED_FILE'] = $sFile;
				$s = self::evalString(file_get_contents($sFile), $aContextVariables, $bFlushBuffer);
				return $s;
			} else {
				throw new Exception("File $sFile does not exist");
			}
		} 
	}

?>