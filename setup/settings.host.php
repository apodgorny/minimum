__PHP_BEGIN__

	require_once 'settings.project.php';

	// Settings specific for each host
	// Should be ignored by git
	// Override settings.project.php and settings.global.php
	// Add settings specific for host to $_ENV['HOST_SETTINGS'] here.

	$_ENV['SETTINGS']['PRODUCTION']   = <?= self::$PRODUCTION ? 'true' : 'false' ?>;              // development or production?
	$_ENV['SETTINGS']['HOST']         = '<?= self::$HOST ?>';
	$_ENV['SETTINGS']['SITE_PATH']    = '<?= self::$SITE_PATH ?>';                                // folder above hostname (if any) in url where project is rooted
	$_ENV['SETTINGS']['PROJECT_ROOT'] = '<?= self::$PROJECT_ROOT ?>';  //
	$_ENV['SETTINGS']['IMAGES_PATH']  = '<?= self::$IMAGES_PATH ?>';   // path to user images folder
		
	$_ENV['SETTINGS']['DB_HOST']      = 'localhost';
	$_ENV['SETTINGS']['DB_USER']      = '';
	$_ENV['SETTINGS']['DB_PASSWORD']  = '';
	$_ENV['SETTINGS']['DB_NAME']      = '';

__PHP_END__