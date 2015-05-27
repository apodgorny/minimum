__PHP_BEGIN__

	require_once 'settings.global.php';

	// Settings specific for project
	// Override settings.global.php and overridden by settings.host.php
	// Add settings specific for your project to $_ENV['PROJECT_SETTINGS'] in this file

	$_ENV['SETTINGS']['EMAIL_TEMPLATE_DIR']  = 'server/emails';
	$_ENV['SETTINGS']['POLL_INTERVAL']       = 15000;

__PHP_END__