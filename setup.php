<?

	if (!isset($argv[1])) {
		die('Please supply path to document root as first parameter' . PHP_EOL);
	}
	if (!file_exists($argv[1]) || !is_dir($argv[1])) {
		die($argv[1] . ' is not a valid directory' . PHP_EOL);
	}
	
	$sDocumentRoot = realpath($argv[1]);
	$sProjectRoot  = implode('/', array_slice(explode('/', __FILE__), 0, -3));
	$sSitePath     = str_replace($sDocumentRoot, '', $sProjectRoot);
	
	print '----------------------------------'.PHP_EOL;
	print "Setting up Minimum workspace\n";
	print '----------------------------------'.PHP_EOL.PHP_EOL;
	

	$aDirectories = [
		'../classes',
		'../logic',
		'../logs',
		'../services',
		'../../client'
	];

	$aFiles = [
		'log'              => ['path' => '../logs/log.log',                'mode' => 0666],
		'htaccess'         => ['path' => '../../.htaccess',                'mode' => 0644],
		'index'            => ['path' => '../../index.php',                'mode' => 0644],
		'gitignore1'       => ['path' => '../../.gitignore',               'mode' => 0664],
		'settings.global'  => ['path' => '../settings.global.php',         'mode' => 0664],
		'settings.project' => ['path' => '../settings.project.php',        'mode' => 0664],
		'settings.host'    => ['path' => '../settings.host.php',           'mode' => 0664],
		'start'            => ['path' => '../start.php',                   'mode' => 0664],
		'gitignore2'       => ['path' => '../.gitignore',               'mode' => 0664],
		
		'class.HttpService'     => ['path' => '../services/class.HttpService.php',   'mode' => 0664],
		'class.HttpsService'    => ['path' => '../services/class.HttpsService.php',  'mode' => 0664],
		'class.ErrorService'    => ['path' => '../services/class.ErrorService.php',  'mode' => 0664],
		'class.SampleService'   => ['path' => '../services/class.SampleService.php', 'mode' => 0664],
	];

	/************ CREATE DIRECTORIES ************/
	
	print '----------------------------------'.PHP_EOL;
	print 'Creating directories: '.PHP_EOL;
	print '----------------------------------'.PHP_EOL;
	foreach ($aDirectories as $sDirectory) {
		print 'Creating ' . str_pad($sDirectory . ' ----', 40, '-');
		if (!file_exists($sDirectory)) {
			mkdir($sDirectory, 0777, true);
			print ' Done'.PHP_EOL;
		} else {
			print ' Exists'.PHP_EOL;
		}
	}
	
	/************ CREATE FILES ************/
	
	print '----------------------------------'.PHP_EOL;
	print 'Creating files: '.PHP_EOL;
	print '----------------------------------'.PHP_EOL;
	foreach ($aFiles as $sKey=>$aFile) {
		print 'Creating ' . str_pad($aFile['path'] . ' ----', 40, '-');
		$sContents = '';
		$sTemplate = 'setup/' . $sKey . '.php';
		if (file_exists($sTemplate)) {
			ob_start();
			eval('?>' . file_get_contents($sTemplate) . '<?');
			$sContents = ob_get_contents();
			ob_end_clean();
			
			$sContents = str_replace('__PHP_BEGIN__',  '<?', $sContents);
			$sContents = str_replace('__PHP_END__', '?>', $sContents);
		}
		if (!file_exists($aFile['path'])) {
			file_put_contents($aFile['path'], $sContents);
			chmod($aFile['path'], $aFile['mode']);
			print ' Done'.PHP_EOL;
		} else {
			print ' Exists'.PHP_EOL;
		}
	}
	
	print '----------------------------------'.PHP_EOL;
	print 'Done'.PHP_EOL;

?>