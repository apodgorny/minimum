__PHP_BEGIN__

	class SampleService extends Service {
		$routes = [
			'^/sample' => 'serveSamplePage'
		];
		
		public function serveSamplePage() {
			$sGreeting = param('greeting');
			print $sGreeting;
		}
	}

__PHP_END__