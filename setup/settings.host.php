__PHP_BEGIN__

	// Settings specific for each host
	// Should be ignored by git
	// Override settings.project.php and settings.global.php
	// Add settings specific for host to $_ENV['HOST_SETTINGS'] here.

	$_ENV['HOST_SETTINGS'] = [
		'PRODUCTION'   => <?= self::$PRODUCTION ? 'true' : 'false' ?>,                              // development or production?
		'HOST'         => '<?= self::$HOST ?>',
		'SITE_PATH'    => '<?= self::$SITE_PATH ?>',                                // folder above hostname (if any) in url where project is rooted
		'PROJECT_ROOT' => '<?= self::$PROJECT_ROOT ?>',  //
		'DATA_PATH'    => '<?= self::$DATA_PATH ?>',     // path to folder where we store hint html
		
		'DB_HOST'      => 'localhost',
		'DB_USER'      => '',
		'DB_PASSWORD'  => '',
		'DB_NAME'      => ''
	];

__PHP_END__