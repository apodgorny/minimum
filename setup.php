<?php

	class Setup {
		public static $PRODUCTION    = false;
		public static $DOCUMENT_ROOT = null;
		public static $PROJECT_ROOT  = null;
		public static $SITE_PATH     = null;
		public static $HOST          = 'localhost';
		public static $DATA_PATH     = null;
		
		public static $aDirectories = [
			'../classes',
			'../logic',
			'../logs',
			'../services',
			'../../client'
		];

		public static $aFiles = [
			'log'                   => ['path' => '../logs/log.log',                     'mode' => 0666],
			'htaccess'              => ['path' => '../../.htaccess',                     'mode' => 0644],
			'index'                 => ['path' => '../../index.php',                     'mode' => 0644],
			'gitignore1'            => ['path' => '../../.gitignore',                    'mode' => 0664],
			'settings.global'       => ['path' => '../settings.global.php',              'mode' => 0664],
			'settings.project'      => ['path' => '../settings.project.php',             'mode' => 0664],
			'settings.host'         => ['path' => '../settings.host.php',                'mode' => 0664],
			'start'                 => ['path' => '../start.php',                        'mode' => 0664],
			'gitignore2'            => ['path' => '../.gitignore',                       'mode' => 0664],
			'class.HttpService'     => ['path' => '../services/class.HttpService.php',   'mode' => 0664],
			'class.HttpsService'    => ['path' => '../services/class.HttpsService.php',  'mode' => 0664],
			'class.ErrorService'    => ['path' => '../services/class.ErrorService.php',  'mode' => 0664],
			'class.SampleService'   => ['path' => '../services/class.SampleService.php', 'mode' => 0664]
		];
		
		public static function acceptParameters() {
			global $argv;
			
			if (!isset($argv[1])) {
				die('Please supply path to document root as first parameter' . PHP_EOL);
			}
			if (!file_exists($argv[1]) || !is_dir($argv[1])) {
				die($argv[1] . ' is not a valid directory' . PHP_EOL);
			}
			
			if (!isset($argv[2])) {
				die('Please (1 or 0) as second parameter to specify if environment is production' . PHP_EOL);
			}
			
			if (!isset($argv[3])) {
				die('Please supply path to USER_DATA folder as third parameter' . PHP_EOL);
			}
			
			if (isset($argv[4])) {
				self::$HOST = $argv[4];
			}

			self::$PRODUCTION     = (bool)$argv[2];
			self::$DOCUMENT_ROOT  = realpath($argv[1]);
			self::$PROJECT_ROOT   = implode('/', array_slice(explode('/', __FILE__), 0, -3));
			self::$SITE_PATH      = str_replace(self::$DOCUMENT_ROOT, '', self::$PROJECT_ROOT);
			self::$DATA_PATH      = $argv[3];
			self::$aDirectories[] = self::$DATA_PATH;
		}
		
		public static function createDirectories() {
			print '----------------------------------'.PHP_EOL;
			print 'Creating directories: '.PHP_EOL;
			print '----------------------------------'.PHP_EOL;
			foreach (self::$aDirectories as $sDirectory) {
				print 'Creating ' . str_pad($sDirectory . ' ----', 40, '-');
				if (!file_exists($sDirectory)) {
					mkdir($sDirectory, 0777, true);
					print ' Done'.PHP_EOL;
				} else {
					print ' Exists'.PHP_EOL;
				}
			}
			chmod(self::$DATA_PATH, 0777);
		}
	
		public static function createFiles() {
			print '----------------------------------'.PHP_EOL;
			print 'Creating files: '.PHP_EOL;
			print '----------------------------------'.PHP_EOL;
			foreach (self::$aFiles as $sKey=>$aFile) {
				print 'Creating ' . str_pad($aFile['path'] . ' ----', 40, '-');
				$sContents = '';
				$sTemplate = 'setup/' . $sKey . '.php';
				if (file_exists($sTemplate)) {
					ob_start();
					eval('?>' . file_get_contents($sTemplate));
					$sContents = ob_get_contents();
					ob_end_clean();
			
					$sContents = str_replace('__PHP_BEGIN__',  '<?php', $sContents);
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
		}
	}
	
	print '----------------------------------'.PHP_EOL;
	print "Setting up Minimum workspace\n";
	print '----------------------------------'.PHP_EOL.PHP_EOL;
	
	Setup::acceptParameters();
	Setup::createDirectories();
	Setup::createFiles();
	
	print '----------------------------------'.PHP_EOL;
	print 'Done'.PHP_EOL;

?>