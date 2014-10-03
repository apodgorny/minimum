<?

	class Response {
		
		/******************* PRIVATE *******************/
		
		private static $_aHeaders         = [];
		private static $_sCurrentFileName = '';
		private static $_bReadyToSend     = false;
		
		/******************* PUBLIC *******************/
		
		public static function setContentType($sType) {
			self::$_aHeaders['Content-Type'] = $sType;
		}
		
		public static function setHeader($sName, $sValue=null) {
			self::$_aHeaders[$sName] = $sValue;
		}
		
		public static function sendFileAbs($sAbsFilePath, $bEval=false) {
			if (file_exists($sAbsFilePath)) {
				return self::send(file_get_contents($sAbsFilePath), $bEval);
			} else {
				debug('File does not exist: '.$sAbsFilePath);
			}
			return false;
		}
		
		public static function sendFile($sFileName, $bEval=true) {
			global $MimeTypes;
			self::$_sCurrentFileName = $sFileName;
			$sExt  = strtolower(pathinfo($sFileName, PATHINFO_EXTENSION));
			$sType = MimeType::guess($sExt);
			if ($sType && !isset(self::$_aHeaders['Content-Type'])) {
				self::setContentType($sType);
				if (!MimeType::canEval($sExt)) {
					$bEval = false;
				}
			}
			$sFileName = M::path($sFileName);
			return self::sendFileAbs($sFileName, $bEval);
		}
		
		public static function sendHtmlFile($sFileName, $sScriptToInject=null, $sCssToInject=null, $bEval=true) {
			if (file_exists($sFileName)) {
				// self::setHeader('Cache-Control', 'no-cache');
				// self::setHeader('Pragma', 'no-cache');
				// self::setHeader('Expires', '-1');
				return self::sendHtml(file_get_contents($sFileName), $sScriptToInject, $sCssToInject, $bEval);
			} else {
				throw new Exception("Error: file $sFileName does not exist", 404);
			}
		}
		
		public static function sendTemplate($sFileName, $aContext=[], $sScriptToInject=null, $sCssToInject=null) {
			if (file_exists($sFileName)) {
				// self::setHeader('Cache-Control', 'no-cache');
				// self::setHeader('Pragma', 'no-cache');
				// self::setHeader('Expires', '-1');
				$sEvaledTemplate = Processor::evalFile($sFileName, $aContext, false);
				return self::sendHtml($sEvaledTemplate, $sScriptToInject, $sCssToInject, false);
			} else {
				throw new Exception("Error: file $sFileName does not exist", 404);
			}
		}
		
		public static function sendHtml($sHtml, $sScriptToInject=null, $sCssToInject=null, $bEval=true) {
			if ($sCssToInject) {
				$sHtml = preg_replace('/<\/head>/i', "\n<style>\n$sCssToInject\n</style>\n</head>", $sHtml);
			}
			if ($sScriptToInject) {
				$sHtml = preg_replace('/<\/head>/i', "\n<script>\n$sScriptToInject\n</script>\n</head>", $sHtml);
			}
			self::setContentType('text/html');
			self::send($sHtml, $bEval);
			return true;
		}
		
		public static function sendJson($mData) {
			$sResponse = json_encode($mData);
			self::setContentType('application/json');
			return self::send("{\"data\":$sResponse}", false);
		}
		
		public static function send($sContent, $bEval=true) {
			self::$_sCurrentFileName = '';
			if ($bEval) {
				print Processor::evalString($sContent);
			} else {
				print $sContent;
			}
			self::$_bReadyToSend = true;
		}
		
		public static function sendError($oException) {
			self::setHeader('HTTP/1.0 '.$oException->getCode().' '.$oException->getMessage());
			self::$_bReadyToSend = true;
			self::end();
		}
		
		public static function begin() {
			ob_start();
		}
		
		public static function end() {
			if (!isset(self::$_aHeaders['Content-Type'])) {
				self::$_aHeaders['Content-Type'] = 'text/html';
			}
			
			if (self::$_bReadyToSend) {
				foreach (self::$_aHeaders as $sName=>$sValue) {
					header("$sName" . ($sValue ? ": $sValue" : ''));
				}
				
				ob_flush();
				ob_clean();
				
				self::$_aHeaders = [];
			}
			die();
		}
		
		public static function redirectTo($sUrl) {
			self::$_bReadyToSend = true;
			if ($sUrl[0] == '/') {
				$sUrl = M::SITE_ROOT() . $sUrl;
			}
			debug('Redirecting to '.$sUrl);
			self::setHeader('HTTP/1.1 301 Moved Permanently'); 
			self::setHeader("Location: $sUrl");
			self::end();
		}
		
		public static function redirectToHttp() {
			self::redirectTo(M::HTTP_ROOT() . M::request_path());
		}

		public static function redirectToHttps() {
			self::redirectTo(M::HTTPS_ROOT() . M::request_path());
		}
	}

?>