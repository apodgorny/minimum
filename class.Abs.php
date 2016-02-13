<?php
	
	if (!class_exists('Abs')) {
		require_once 'lib/simple_html_dom/simple_html_dom.php';
	}

	require_once 'lib/url_to_absolute/url_to_absolute.php';

	class Abs {
		public static function absolutizeUrl($sBaseUrl, $sUrl) {
			$sUrl = str_replace('&amp;', '&', $sUrl);
			return url_to_absolute($sBaseUrl, $sUrl);
		}
		
		public static function absolutizeHtml($sBaseUrl, $sHtml) {
			$oHtml = new simple_html_dom();
			$oHtml->load($sHtml);
			
			$aTags = $oHtml->find('a');
			foreach ($aTags as $oTag) {
				$oTag->href = self::absolutizeUrl($sBaseUrl, $oTag->href);
			}
			
			$aTags = $oHtml->find('img');
			foreach ($aTags as $oTag) {
				$oTag->src = self::absolutizeUrl($sBaseUrl, $oTag->src);
			}
			
			$aTags = $oHtml->find('script');
			foreach ($aTags as $oTag) {
				$oTag->src = self::absolutizeUrl($sBaseUrl, $oTag->src);
			}
			
			$aTags = $oHtml->find('link');
			foreach ($aTags as $oTag) {
				$oTag->href = self::absolutizeUrl($sBaseUrl, $oTag->href);
			}

			// Parse url() in inline css
			$aTags = $oHtml->find('style');
			foreach ($aTags as $oTag) {
				$oTag->innertext = preg_replace_callback(
					'|url\s*\(\s*[\'"]?([^\'"\)]+)[\'"]?\s*\)|',
					function($aMatches) use ($sBaseUrl) {
						return 'url("'. trim(self::absolutizeUrl($sBaseUrl, $aMatches[1])). '")';
					},
					$oTag->innertext
				);
			}
			
			return $oHtml . '';
		}
		
		/**
		 *  Absolutize to current url – use in templates
		 */
		public static function absolutize($sUrl, $sPort=null) {
			/* return if already absolute URL */
			if (parse_url($sUrl, PHP_URL_SCHEME) != '') { return $sUrl; }

			/* queries and anchors */
			if (!$sUrl || $sUrl[0]=='#' || $sUrl[0]=='?') { return M::SITE_ROOT().$sUrl; }

			/* parse base URL and convert to local variables:
			$scheme, $host, $path */
			extract(parse_url(M::SITE_ROOT()));

			/* remove non-directory element from path */
			// $path = preg_replace('#/[^/]*$#', '', $path);
			if (!isset($scheme)) { $scheme = ''; } else { $scheme = $scheme . ':'; }
			if (!isset($host))   { $host = ''; }
			if (!isset($path))   { $path = ''; }
			
			if ($sPort) { $host = $host . ':' . $sPort; }

			/* destroy path if relative url points to root */
			//if ($sUrl[0] == '/') { $path = ''; }

			/* dirty absolute URL */
			$sAbs = "$host$path/$sUrl";

			/* replace '//' or '/./' or '/foo/../' with '/' */
			$aRegex = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
			for($n=1; $n>0; $sAbs=preg_replace($aRegex, '/', $sAbs, -1, $n)) {}

			/* absolute URL is ready! */
			return $scheme.'//'.$sAbs;
		}
	}

?>