<?php

	class Response {
		
		/******************* PRIVATE *******************/
		
		private static $_aHeaders         = [];
		private static $_sCurrentFileName = '';
		private static $_bReadyToSend     = false;
		private static $_sCharset         = 'utf-8';
		
		private static function _sendHeaders() {
			if (!isset(self::$_aHeaders['Content-Type'])) {
				self::$_aHeaders['Content-Type'] = 'text/html';
			}
			
			if (self::$_sCharset) {
				self::$_aHeaders['Content-Type'] .= '; charset=' . self::$_sCharset;
			}
			
			if (!isset(self::$_aHeaders['Cache-Control'])) {
				self::$_aHeaders['Cache-Control'] = 'max-age=31536000'; // One year
				self::$_aHeaders['Pragma']        = 'max-age=31536000';
				self::$_aHeaders['Expires']       = gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000);
				self::$_aHeaders['etag']          = M::BUILD_ID();
			}
			
			if (self::$_bReadyToSend) {
				foreach (self::$_aHeaders as $sName=>$sValue) {
					header("$sName" . ($sValue ? ": $sValue" : ''));
				}
			}
			self::$_aHeaders = [];
		}
		
		/******************* PUBLIC *******************/
		
		public static function setCharset($sCharset) {
			self::$_sCharset = $sCharset;
		}

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
				global $MimeTypes;
				self::$_sCurrentFileName = $sAbsFilePath;
				$sExt  = strtolower(pathinfo($sAbsFilePath, PATHINFO_EXTENSION));
				$sType = MimeType::guess($sExt);
				if ($sType && !isset(self::$_aHeaders['Content-Type'])) {
					self::setContentType($sType);
					if (!MimeType::canEval($sExt)) {
						$bEval = false;
					}
				}
				
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
			$sFileName = M::path($sFileName);
			// self::setHeader('Content-Length', filesize($sFileName));
			return self::sendFileAbs($sFileName, $bEval);
		}
		
		public static function sendHtmlFile($sFileName, $sScriptToInject=null, $sCssToInject=null, $bEval=true) {
			if (file_exists($sFileName)) {
				self::setHeader('Cache-Control', 'no-cache');
				self::setHeader('Pragma', 'no-cache');
				self::setHeader('Expires', '-1');
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
				$sEvaledTemplate  = Processor::evalFile($sFileName, $aContext, false);
				$sScriptToInject .= T::getJs();
				$sCssToInject    .= T::getCss();
				return self::sendHtml($sEvaledTemplate, $sScriptToInject, $sCssToInject, false);
			} else {
				throw new Exception("Error: file $sFileName does not exist", 404);
			}
		}
		
		public static function sendHtml($sHtml='', $sScriptToInject=null, $sCssToInject=null, $bEval=true) {
			if ($sHtml == '') {
				$sHtml = '<!DOCTYPE html><html><head></head><body></body></html>';
			}
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
		
		public static function sendJsonError($sType, $aData) {
			self::setHeader("HTTP/1.0 500");
			self::sendJson([
				'code'    => 500,
				'message' => '',
				'type'    => $sType,
				'errors'  => $aData
			]);
			self::$_bReadyToSend = true;
			self::end();
		}
		
		/**
		 *  sendError(sType, sMessage, nHttpCode)
		 *  sendError(sType, oException)
		 */
		public static function sendError($sType, $m, $nHttpCode=500) {
			if (!$sType) { $sType = 'Exception'; }
			if (!is_string($m) && !is_array($m)) {
				$nHttpCode = $m->getCode();
				$m         = $m->getMessage();
			}
			
			self::setHeader("HTTP/1.0 $nHttpCode $m");
			self::sendJson([
				'code'    => $nHttpCode,
				'message' => $m,
				'type'    => $sType,
				'errors'  => ''
			]);
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
			// self::setHeader('HTTP/1.1 301 Moved Permanently'); 
			self::setHeader("Location: $sUrl");
			self::end();
		}

		public static function switchDomain($sFromDomain, $sToDomain) {
			$bEndsWithFromDomain = strrpos($_SERVER['HTTP_HOST'], $sFromDomain) == strlen($_SERVER['HTTP_HOST']) - strlen($sFromDomain);
			$sSubdomain          = str_replace($sFromDomain, '', $_SERVER['HTTP_HOST']);

			if ($sFromDomain == '*' || $bEndsWithFromDomain) {
				$sUrl = str_replace($_SERVER['HTTP_HOST'], $sSubdomain.$sToDomain, self::getFullUrl());
				foreach ($_COOKIE as $sName=>$sValue) {
					if (isset(M::COOKIE_TTL()[$sName])) {
						setcookie($sName, $sValue, M::COOKIE_TTL()[$sName], '/');
					}
				}
				self::redirectTo($sUrl);
			}
		}
		
		public static function redirectToHttp() {
			self::redirectTo(M::HTTP_ROOT() . Request::path());
		}

		public static function redirectToHttps() {
			self::redirectTo(M::HTTPS_ROOT() . Request::path());
		}
	}

?>