__PHP_BEGIN__

	$oHttpService  = Service::create('HttpService');
	$oHttpsService = Service::create('HttpsService');
	
	Response::begin();
	
	if (Request::isHttps()) {
		if (!$oHttpsService->execute()) {
			if (!$oHttpService->execute()) {
				throw new Exception('Page not found: ' . Request::path(), 404);
			}
		}
	} else {
		if (!$oHttpService->execute()) {
			throw new Exception('Page not found: ' . Request::path(), 404);
		}
	}

__PHP_END__