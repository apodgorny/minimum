__PHP_BEGIN__
	
	class ErrorService extends Service {
		public $routes = [
			'/.*/' => 'serveError'
		];
		
		public function serveError($oException) {
			$nCode = $oException->getCode() ? $oException->getCode() : '500';
			print 'HTTP/1.0 ' . $nCode . ': ' . $oException->getMessage();
			Response::sendError(get_class($oException), $oException, $nCode);
		}
	}
	
__PHP_END__