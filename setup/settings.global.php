__PHP_BEGIN__

	// Settings that settings.host.php and settings.project.php fall back to
	// Normally, you should not edit this file.

	$_ENV['SETTINGS'] = [
		'PRODUCTION'    => false,                  // Should be set in settings.host.php (development or production?)
		'HOST'          => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost',  // Should be set in settings.host.php
		'PROJECT_ROOT'  => '',                     // Should be set in settings.host.php (/www/mysite - location in file system)
		'SITE_PATH'     => '',                     // Should be set in settings.host.php (http://host/folder without http://host)
	
		'HTTP_PORT'     =>  80,
		'HTTPS_PORT'    =>  443,

		'HTTP_ROOT'     => '',               // Will be set automatically (http://mysite.com)
		'HTTPS_ROOT'    => '',               // Will be set automatically (https://mysite.com)
		'SITE_ROOT'     => '',               // Will be set automatically (either http_root or https_root, depending on request protocol)
//		'REQUEST_PATH'  => '',               // Will be set automatically
		'PROTOCOL'      => '',               // Will be set automatically

		'INCLUDE_DIRS'  => ['minimum', 'logic', 'services', 'classes'],
	];

__PHP_END__