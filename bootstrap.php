<?php
	
	require_once 'server/settings.global.php';
	require_once 'server/settings.project.php';
	require_once 'server/settings.host.php';
	
	/********************************************************/
	
	foreach ($_ENV['PROJECT_SETTINGS'] as $sKey=>$sValue) {
		$_ENV['SETTINGS'][$sKey] = $sValue;
	}
	
	foreach ($_ENV['HOST_SETTINGS'] as $sKey=>$sValue) {
		$_ENV['SETTINGS'][$sKey] = $sValue;
	}
	
	/********************************************************/
	
	session_set_cookie_params(M::SESSION_TTL());
	session_name($_ENV['SETTINGS']['SESSION_NAME']);
	session_start();
	mb_internal_encoding('UTF-8');

	$_ENV['SETTINGS']['BUILD_ID'] = '';
	if (file_exists($_ENV['SETTINGS']['PROJECT_ROOT'].'/build')) {
		$_ENV['SETTINGS']['BUILD_ID'] = file_get_contents($_ENV['SETTINGS']['PROJECT_ROOT'].'/build');
	}
	
	$_ENV['LAST_EVALED_FILE'] = null;
	$_ENV['EVAL_ERROR']       = false;

	$_ENV['SETTINGS']['LOG_FILE'] = $_ENV['SETTINGS']['PROJECT_ROOT'] . '/' . $_ENV['SETTINGS']['LOG_FILE'];
	$_ENV['SETTINGS']['PROTOCOL'] = Request::isHttps() ? 'https' : 'http';

	$_ENV['SETTINGS']['HTTP_ROOT'] = 'http://' . $_ENV['SETTINGS']['HOST'] . 
		($_ENV['SETTINGS']['HTTP_PORT'] != 80 
			? ':' . $_ENV['SETTINGS']['HTTP_PORT'] 
			: ''
		) . $_ENV['SETTINGS']['SITE_PATH'];

	$_ENV['SETTINGS']['HTTPS_ROOT'] = 'https://' . $_ENV['SETTINGS']['HOST'] . 
		($_ENV['SETTINGS']['HTTPS_PORT'] != 443 
			? ':' . $_ENV['SETTINGS']['HTTPS_PORT'] 
			: ''
		) . $_ENV['SETTINGS']['SITE_PATH'];

	$_ENV['SETTINGS']['REQUEST_PATH'] = isset($_SERVER['REQUEST_URI'])
		? explode('?', str_replace_first($_ENV['SETTINGS']['SITE_PATH'], '', $_SERVER['REQUEST_URI']))[0]
		: '';
		
	$_ENV['SETTINGS']['SITE_ROOT'] = $_ENV['SETTINGS']['PROTOCOL'] == 'https' 
		? $_ENV['SETTINGS']['HTTPS_ROOT'] 
		: $_ENV['SETTINGS']['HTTP_ROOT'];

	/********************************************************/
	
	function str_replace_first($sNeedle, $sReplacement, $sHaystack) {
		if ($sNeedle && $sHaystack) {
			$nPosition = strpos($sHaystack, $sNeedle);
			if ($nPosition !== false) {
			    return substr_replace($sHaystack, $sReplacement, $nPosition, strlen($sNeedle));
			}
		}
		return $sHaystack;
	}
	
	function join_paths($sLeft, $sRight) {
		$aLeft  = explode(DIRECTORY_SEPARATOR, $sLeft);
		$aRight = explode(DIRECTORY_SEPARATOR, $sRight);
		
		if ($aRight[0] == '') { return $sRight; }
		
		$n = 0;
		while ($aLeft[count($aLeft)-1] == $aRight[$n]) {
			array_pop($aLeft);
		}
		
		return 
			implode(DIRECTORY_SEPARATOR, $aLeft) . 
			DIRECTORY_SEPARATOR . 
			implode(DIRECTORY_SEPARATOR, $aRight);
	}
	
	function debug() {
		if (isset($_ENV['CAN_DEBUG']) && !$_ENV['CAN_DEBUG']) { return; }
		if (!isset($_ENV['DEBUG'])) {
			$_ENV['DEBUG'] = '';
		}
		$a = func_get_args();
		foreach ($a as $m) {
			if (is_array($m) || is_object($m)) {
				$_ENV['DEBUG'] .= ' ' . print_r($m, 1);
			} else if (is_bool($m)) {
				$_ENV['DEBUG'] .= ' ' . ($m ? 'TRUE' : 'FALSE');
			} else if (is_null($m)) {
				$_ENV['DEBUG'] .= ' NULL';
			} else {
				$_ENV['DEBUG'] .= ' ' . $m;
			}
		}
		$_ENV['DEBUG'] .=  PHP_EOL;
	}
	
	/********************************************************/
	
	function __autoload($sClassName) {
		foreach ($_ENV['SETTINGS']['INCLUDE_DIRS'] as $sIncludeDir) {
			$sFileName = join_paths(getcwd(), "server/$sIncludeDir/class.$sClassName.php");
			if (file_exists($sFileName)) {
				require_once $sFileName;
				if (!class_exists($sClassName, false)) {
					throw new Exception("Class $sClassName could not be loaded, check syntax errors");
				}
				return;
			}
		}
		throw new Exception("Class $sClassName is not found");
	}
	
	function shutdownHandler() {
		P::mark('END');
		P::report();
		
		if ($aError = error_get_last()) {
			errorHandler($aError['type'], $aError['message'], $aError['file'], $aError['line']);
		}
		// Flush debug
		$oFile = fopen($_ENV['SETTINGS']['LOG_FILE'], 'a');
		fwrite($oFile, $_ENV['DEBUG']);
		fclose($oFile);
	}
	
	function errorHandler($nCode, $sMessage, $sFileName, $nLineNumber) {
		$_ENV['CAN_DEBUG'] = true;
		$aErrorNames = [
			E_ERROR             => 'E_ERROR',
			E_WARNING           => 'E_WARNING',
			E_PARSE             => 'E_PARSE',
			E_NOTICE            => 'E_NOTICE',
			E_CORE_ERROR        => 'E_CORE_ERROR',
			E_CORE_WARNING      => 'E_CORE_WARNING',
			E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
			E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
			E_USER_ERROR        => 'E_USER_ERROR',
			E_USER_WARNING      => 'E_USER_WARNING',
			E_USER_NOTICE       => 'E_USER_NOTICE',
			E_STRICT            => 'E_STRICT',
			E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
			E_DEPRECATED        => 'E_DEPRECATED',
			E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
			E_ALL               => 'E_ALL'
		];
		$sWrongCode = '';
		if ($_ENV['EVAL_ERROR'] && $_ENV['LAST_EVALED_FILE']) {
			$sFileName = $_ENV['LAST_EVALED_FILE'];
			$sWrongCode = substr(explode("\n", $_ENV['EVALED_CODE'])[$nLineNumber-1], 0, 100) . '...';
		}
		debug('ERROR ' . $aErrorNames[$nCode] . ": $sMessage in $sFileName, on line $nLineNumber: \n". $sWrongCode);
	}
	
	function exceptionHandler($oException) {
		
		$_ENV['CAN_DEBUG'] = true;
		$nCode = $oException->getCode();
		$nCode = $nCode ? $nCode : 500;
		
		$sMessage = $oException->getMessage();
		
		debug("EXCEPTION $nCode: $sMessage\n". $oException->getTraceAsString());
		
		$oErrorService = new ErrorService();
		$oErrorService->serveError($oException);
	}
	
	register_shutdown_function('shutdownHandler');
	set_error_handler('errorHandler');
	set_exception_handler('exceptionHandler');
	
	/********************************************************/
	
	if (!preg_match('/\.(js|css|gif|png|jpg|jpeg|ico|woff)$/', Request::path())) {
		$sIp   = 'NO IP';
		$sTime = date('m/d/Y h:i:s a', time());

		if (isset($_SERVER['REMOTE_ADDR'])) {
			$sIp = $_SERVER['REMOTE_ADDR'];
		}
		debug(str_pad('------ '.Request::path().' ---', 55, '-') . '-[ ' . $sIp . ' ]--[ ' . $sTime . ' ]--');
		$_ENV['CAN_DEBUG'] = true;
	} else {
		$_ENV['CAN_DEBUG'] = false;
	}
	
	/********************************************************/
	
	P::mark('START');
	
	require_once 'server/start.php';
	
?>