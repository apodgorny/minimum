<?php

	class Response {
		
		/******************* PRIVATE *******************/
		
		private static $_aHeaders         = [];
		private static $_sCurrentFileName = '';
		private static $_bReadyToSend     = false;
		
		private static function _sendHeaders() {
			if (!isset(self::$_aHeaders['Content-Type'])) {
				self::$_aHeaders['Content-Type'] = 'text/html';
			}
			
			if (self::$_bReadyToSend) {
				foreach (self::$_aHeaders as $sName=>$sValue) {
					header("$sName" . ($sValue ? ": $sValue" : ''));
				}
			}
			self::$_aHeaders = [];
		}
		
		/******************* PUBLIC *******************/

		public static function getFullUrl($bUseForwardedHost=false) {
		    $bHttps    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? true:false;
		    $sProtocol = strtolower($_SERVER['SERVER_PROTOCOL']);
		    $sProtocol = substr($sProtocol, 0, strpos($sProtocol, '/')) . (($bHttps) ? 's' : '');
		    $sPort     = $_SERVER['SERVER_PORT'];

		    $sPort = ((!$bHttps && $sPort=='80') || ($bHttps && $sPort=='443'))
		    	? ''
		    	: ':'.$sPort;

		    $sHost = ($bUseForwardedHost && isset($_SERVER['HTTP_X_FORWARDED_HOST'])
		    	? $_SERVER['HTTP_X_FORWARDED_HOST']
		    	: isset($_SERVER['HTTP_HOST'])
		    		? $_SERVER['HTTP_HOST']
		    		: null
		    );

		    $sHost = isset($sHost) ? $sHost : $_SERVER['SERVER_NAME'] . $sPort;
		    return $sProtocol . '://' . $sHost . $_SERVER['REQUEST_URI'];
		}

		public static function setContentType($sType) {
			self::$_aHeaders['Content-Type'] = $sType;
		}
		
		public static function setHeader($sName, $sValue=null) {
			self::$_aHeaders[$sName] = $sValue;
		}
		
		public static function sendFileAbs($sAbsFilePath, $bEval=false) {
			if (file_exists($sAbsFilePath)) {
				if ($bEval) {
					return self::send(file_get_contents($sAbsFilePath), $bEval);
				} else {
					self::$_bReadyToSend = true;
					self::_sendHeaders();
					readfile($sAbsFilePath);
					exit();
				}
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
			// self::setHeader('Content-Length', filesize($sFileName));
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
		
		public static function sendTemplate($sFileName, $aContext=[], $sScriptToInject='', $sCssToInject='') {
			if (file_exists($sFileName)) {
				self::setHeader('Cache-Control', 'no-cache');
				self::setHeader('Pragma', 'no-cache');
				self::setHeader('Expires', '-1');
				$sEvaledTemplate = Processor::evalFile($sFileName, $aContext, false);
				$sScriptToInject .= T::getJs();
				$sCssToInject    .= T::getCss();
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
		
		/**
		 *  sendError(sMessage, nHttpCode)
		 *  sendError(oException)
		 */
		public static function sendError($m, $nHttpCode=500) {
			if (!is_string($m)) {
				$nHttpCode = $m->getCode();
				$m         = $m->getMessage();
			}
			
			self::setHeader("HTTP/1.0 $nHttpCode $m");
			self::$_bReadyToSend = true;
			self::end();
		}
		
		public static function begin() {
			ob_start();
		}
		
		public static function end() {
			self::_sendHeaders();
			ob_flush();
			ob_clean();
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

		public static function switchDomain($sFromDomain, $sToDomain) {
			if ($sFromDomain == '*' || $sFromDomain == $_SERVER['HTTP_HOST']) {
				$sUrl = str_replace($_SERVER['HTTP_HOST'], $sToDomain, self::getFullUrl());
				self::redirectTo($sUrl);
			}
		}
		
		public static function redirectToHttp() {
			self::redirectTo(M::HTTP_ROOT() . M::request_path());
		}

		public static function redirectToHttps() {
			self::redirectTo(M::HTTPS_ROOT() . M::request_path());
		}
	}

?>