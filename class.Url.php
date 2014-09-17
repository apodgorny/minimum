<?

	class Url {
		private $_aUrl                        = [];
		private static $_aLastResponseHeaders = [];
		
		public static function create($sUrl) {
			return new Url($sUrl);
		}
		
		public function __construct($sUrl) {
			$this->_aUrl = parse_url($sUrl);
			
			if (isset($this->_aUrl['query'])) {
				parse_str($this->_aUrl['query'], $this->_aUrl['query']);
			} else {
				$this->_aUrl['query'] = [];
			}
		}
		
		public function toString() {
			return
				$this->getProtocol() . '://' . (
					isset($this->_aUrl['user'])
						? (isset($this->_aUrl['pass'])
							? $this->_aUrl['user'] . ':' . $this->_aUrl['pass'] . '@'
							: $this->_aUrl['user'] . ':@'
						) : ''
				). $this->getHost() . (
					isset($this->_aUrl['port'])
						? ':' . $this->_aUrl['port']
						: ''
				) . $this->getPath() . (
					isset($this->_aUrl['query'])
						? '?' . $this->getQuery()
						: ''
				) . (
					isset($this->_aUrl['fragment'])
						? '#' . $this->getHash()
						: ''
				);
		}
		
		public function getQueryParams() {
			return isset($this->_aUrl['query'])
				? $this->_aUrl['query']
				: [];
		}
		
		public function getQuery() {
			return isset($this->_aUrl['query'])
				? http_build_query($this->_aUrl['query'])
				: '';
		}
		
		public function getPort() {
			return isset($this->_aUrl['port'])
				? $this->_aUrl['port']
				: $this->getProtocol() == 'https'
					? '443'
					: '80';
		}
		
		public function getProtocol() {
			return isset($this->_aUrl['scheme'])
				? strtolower($this->_aUrl['scheme'])
				: 'http';
		}
		
		public function getHost() {
			return isset($this->_aUrl['host'])
				? $this->_aUrl['host']
				: 'localhost';
		}
		
		public function getUser() {
			return isset($this->_aUrl['user'])
				? $this->_aUrl['user']
				: null;
		}
		
		public function getPassword() {
			return isset($this->_aUrl['pass'])
				? $this->_aUrl['pass']
				: null;
		}
		
		public function getHash() {
			return isset($this->_aUrl['fragment'])
				? $this->_aUrl['fragment']
				: null;
		}
		
		public function getPath() {
			return isset($this->_aUrl['path'])
				? $this->_aUrl['path']
				: '';
		}
		
		public function params($aParams) {
			$this->_aUrl['query'] = array_merge($this->_aUrl['query'], $aParams);
			return $this;
		}
		
		public function request($sMethod, $aHeaders=null, $aBody=null, $aCurlOptions=null) {
			$sMethod	= strtoupper($sMethod);
			$oConnection = curl_init();
			$aOptions	= array(
				CURLOPT_URL            => $this->toString(),
				CURLOPT_HEADER         => false,
				CURLOPT_RETURNTRANSFER => true,
			);
			
			if ($sMethod == 'POST') { $aOptions[CURLOPT_POST]       = true;              }
			if ($aBody)             { $aOptions[CURLOPT_POSTFIELDS] = $aBody;            }
			if ($aHeaders)          { $aOptions[CURLOPT_HTTPHEADER] = $aHeaders;         }
			if ($aCurlOptions)      { $aOptions = array_merge($aOptions, $aCurlOptions); }
			
			curl_setopt_array($oConnection, $aOptions);
			
			curl_setopt($oConnection, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($oConnection, CURLOPT_VERBOSE, 1);
			curl_setopt($oConnection, CURLOPT_HEADER, 1);
			curl_setopt($oConnection, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0');
			curl_setopt($oConnection, CURLOPT_SSL_VERIFYPEER, false);
			
			$sResponse = curl_exec($oConnection);
			
			// Separate head from body
			$aResponseInfo = curl_getinfo($oConnection);
			$sHeader	  = substr($sResponse, 0, $aResponseInfo['header_size']);
			$sBody		= substr($sResponse, $aResponseInfo['header_size']);

			// debug("RETURNED $sMethod REQUEST");
			// debug('BODY:', $sBody);
			// debug('RESPONSE:', $sResponse);
			// debug('URL:', $this->toString());
			
			// Parse headers
			self::$_aLastResponseHeaders = [];
			$aHeaderLines = explode("\n", $sHeader);
			for ($n=1; $n<count($aHeaderLines); $n++) {
				$a = explode(':', $aHeaderLines[$n]);
				if (isset($a[1]) && trim($a[0])) {
					self::$_aLastResponseHeaders[trim($a[0])] = trim($a[1]);
				}
			}
			
			curl_close($oConnection);
			return $sBody;
		}
		
		public function get($aHeaders=null) {
			return $this->request('GET', $aHeaders);
		}
		
		public function getJson($aHeaders=null) {
			return json_decode($this->request('GET', $aHeaders), true);
		}
		
		public function post($aBody=null, $aHeaders=null) {
			return $this->request('POST', $aHeaders, $aBody);
		}
		
		public static function getResponseHeaders() {
			return self::$_aLastResponseHeaders;
		}
		
		public static function getResponseHeader($sHeaderName) {
			if (isset(self::$_aLastResponseHeaders[$sHeaderName])) {
				return self::$_aLastResponseHeaders[$sHeaderName];
			}
			return null;
		}
		
		public static function absolutize($sBaseUrl, $sRelativeUrl) {
			// If relative URL has a scheme, clean path and return.
			$r = split_url($sRelativeUrl);
			if ($r === FALSE) { return FALSE; }
			if (!empty($r['scheme'])) {
				if (!empty($r['path']) && $r['path'][0] == '/')
					$r['path'] = url_remove_dot_segments($r['path']);
				return join_url($r);
			}

			// Make sure the base URL is absolute.
			$b = split_url($sBaseUrl);
			if ($b === FALSE || empty($b['scheme']) || empty($b['host'])) { return FALSE; }
			$r['scheme'] = $b['scheme'];

			// If relative URL has an authority, clean path and return.
			if (isset($r['host'])) {
				if (!empty($r['path'])) {
					$r['path'] = url_remove_dot_segments($r['path']);
				}
				return join_url($r);
			}
			
			unset($r['port']);
			unset($r['user']);
			unset($r['pass']);

			// Copy base authority.
			$r['host'] = $b['host'];
			if (isset($b['port'])) $r['port'] = $b['port'];
			if (isset($b['user'])) $r['user'] = $b['user'];
			if (isset($b['pass'])) $r['pass'] = $b['pass'];

			// If relative URL has no path, use base path
			if (empty($r['path'])) {
				if (!empty($b['path']))
					$r['path'] = $b['path'];
				if (!isset($r['query']) && isset($b['query']))
					$r['query'] = $b['query'];
				return join_url($r);
			}

			// If relative URL path doesn't start with /, merge with base path
			if ($r['path'][0] != '/') {
				$base = mb_strrchr($b['path'], '/', TRUE, 'UTF-8');
				if ($base === FALSE) $base = '';
				$r['path'] = $base . '/' . $r['path'];
			}
			
			$r['path'] = url_remove_dot_segments($r['path']);
			return join_url($r);
		}
	}

?>