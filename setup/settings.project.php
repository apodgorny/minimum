__PHP_BEGIN__

	// Settings specific for project
	// Override settings.global.php and overridden by settings.host.php
	// Add settings specific for your project to $_ENV['PROJECT_SETTINGS'] in this file

	$_ENV['PROJECT_SETTINGS'] = [
		'EMAIL_TEMPLATE_DIR'  => 'server/emails',
		'POLL_INTERVAL'       => 15000,
	];

__PHP_END__