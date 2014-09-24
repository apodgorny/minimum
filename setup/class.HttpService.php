__PHP_BEGIN__

	class HttpService extends Service {
		public $routes = [
			'^/'        => 'serveMainPage',
			'^/sample/' => '#SampleService',
			'^/client'  => 'serveClientFile',
		];
		
		public function serveMainPage() {
			print 'Welcome to Minimum!';
		}
		
		public function serveClientFile() {
			return Response::sendFile(Request::path());
		}
	}

__PHP_END__