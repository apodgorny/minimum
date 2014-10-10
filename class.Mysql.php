<?php
	
	class Mysql {
		
		private static $_oDb     = null;
		private static $_oResult = null;
		
		/******************* PUBLIC *******************/
		
		public static function escape($s) {
			return self::Db()->real_escape_string($s);
		}
		
		public static function Db($sDb=null, $sHost=null, $sUser=null, $sPass=null) {
			if (!self::$_oDb) {
				self::connect($sDb, $sHost, $sUser, $sPass);
			}
			return self::$_oDb;
		}

		public static function isConnected() {
		    return (bool)self::$_oDb;
	    }

		public static function connect($sDb=null, $sHost=null, $sUser=null, $sPass=null) {
			self::disconnect();
			
			$sHost = $sHost ? $sHost : M::DB_HOST();
			$sUser = $sUser ? $sUser : M::DB_USER();
			$sPass = $sPass ? $sPass : M::DB_PASSWORD();
			$sDb   = $sDb   ? $sDb   : M::DB_NAME();

			self::$_oDb = new mysqli($sHost, $sUser, $sPass, $sDb);
			
			if (self::$_oDb->connect_errno) {
				throw new Exception('Mysql error: (' . self::$_oDb->connect_errno . ') ' . self::$_oDb->connect_error);
			}
		}
		
		public static function disconnect() {
			if (self::$_oDb) {
				self::$_oDb->close();
				self::$_oDb = null;
			}
		}
		
		public static function query($sQuery, $aArgs=[], $bDebug=false) {
			$m = null;
			for ($n=count($aArgs)-1; $n>=0; $n--) {
				switch (gettype($aArgs[$n])) {
					case 'integer' :
					case 'double'  :
					case 'float'   :
					case 'boolean' :
						$m = $aArgs[$n];
						break;
					case 'string'  :
						$m = "'" . mysqli_real_escape_string(self::Db(), str_replace('$', '&#36;', addslashes($aArgs[$n]))) . "'";
						break;
					case 'NULL'    :
					default        :
						$m = "''";
						break;
				}
				$sQuery = str_replace('$'.($n+1), $m, $sQuery);
			}
			
			if ($bDebug) { debug($sQuery); }
			
			self::$_oResult = self::Db()->query($sQuery);
			if (!self::$_oResult) {
				throw new Exception(self::Db()->error);
			}
			return self::$_oResult;
		}
		
		public static function getRows($sQuery, $aArgs=[], $bDebug=false) {
			if ($sQuery) { self::query($sQuery, $aArgs, $bDebug); }
			$aRows = [];
			if (method_exists(self::$_oResult, 'data_seek') && self::$_oResult->data_seek(0)) {
				while (self::$_oResult && $aRow = self::$_oResult->fetch_assoc()) {
					foreach ($aRow as $sKey=>$sValue) {
						$aRow[$sKey] = stripslashes($sValue);
					}
					$aRows[] = $aRow;
				}
			}
			return $aRows;
		}
		
		public static function getRow($sQuery, $aArgs=[], $bDebug=false) {
			if ($sQuery) { self::query($sQuery, $aArgs, $bDebug); }
			if (self::$_oResult && self::$_oResult->data_seek(0)) {
				if ($aRow = self::$_oResult->fetch_assoc()) {
					foreach ($aRow as $sKey=>$sValue) {
						$aRow[$sKey] = stripslashes($sValue);
					}
					return $aRow;
				}
			}
			return null;
		}
		
		public static function getValue($sQuery, $aArgs=[], $bDebug=false) {
			if ($sQuery) { self::query($sQuery, $aArgs, $bDebug); }
			if (self::$_oResult && self::$_oResult->data_seek(0)) {
				if ($aRow = self::$_oResult->fetch_assoc()) {
					foreach ($aRow as $sKey=>$sValue) {
						return stripslashes($sValue);
					}
				}
			}
			return null;
		}
		
		public static function getColumn($sQuery, $aArgs=[], $bDebug=false) {
			if ($sQuery) { self::query($sQuery, $aArgs, $bDebug); }
			$aColumn = [];
			if (method_exists(self::$_oResult, 'data_seek') && self::$_oResult->data_seek(0)) {
				while (self::$_oResult && $aRow = self::$_oResult->fetch_assoc()) {
					foreach ($aRow as $sKey=>$sValue) {
						$aColumn[] = stripslashes($aRow[$sKey]);
					}
				}
			}
			return $aColumn;
		}
		
		public static function getInsertId() {
			$oInsertId = self::getRow('SELECT LAST_INSERT_ID() AS id');
			return $oInsertId['id'];
		}
	}

?>