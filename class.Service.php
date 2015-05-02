<?php
	
	class Service {
		
		/******************* PUBLIC *******************/
		
		public $routes = [];
		public $request;
		public $response;
		
		public static function create($sClassName, $sNamespace='') {
			$sClassName = $sNamespace . $sClassName;
			if (class_exists($sClassName)) {
				return new $sClassName();
			}
			throw new Exception("Class $sClassName does not exist");
		}

		public function catchAll() {
		}
		
		public function execute() {
			$sPath = Request::path();
			
			foreach ($this->routes as $sKey=>$sMethod) {
				if (preg_match('~' . $sKey . '~', $sPath)) {
					debug(get_class($this)." -> $sMethod() [$sPath => $sKey]");
					if ($sMethod[0] == '#') {
						$sMethod = substr($sMethod, 1);
						if (class_exists($sMethod)) {
							return Service::create($sMethod)->execute();
						} else {
							throw new Exception('Service not found: '.$sMethod, 404);
						}
					} else if (method_exists($this, $sMethod)) {
						$this->catchAll();
						$bReturn = $this->$sMethod();
						P::mark(get_class($this) . '::' . $sMethod);
						Response::end();
						return $bReturn;
					} else {
						throw new Exception('Method not found: '.get_class($this).'::'.$sMethod.'()', 404);
					}
				}
			}
			debug(get_class($this)." -> NO MATCH");
			return false;
		}
	}

?>