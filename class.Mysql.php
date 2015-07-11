<?php

	if (!function_exists('debug')) {
		function debug() {
			$s = '';
			$a = func_get_args();
			foreach ($a as $m) {
				if (is_array($m) || is_object($m)) {
					$s .= ' ' . print_r($m, 1);
				} else if (is_bool($m)) {
					$s .= ' ' . ($m ? 'TRUE' : 'FALSE');
				} else if (is_null($m)) {
					$s .= ' NULL';
				} else {
					$s .= ' ' . $m;
				}
			}
			// print_r($s . PHP_EOL);
		}
	}
	
	class Mysql {
		
		private static $_oDb     = null;
		private static $_oResult = null;
		private static $_aFields = [];
		
		private static function _castValue($sValue, $nType) {
			switch ($nType) {
				case 1:
				case 2:
				case 3:
				case 8:
				case 9:
				case 16:
					return intval($sValue);
				case 4:
				case 5:
				case 246:
					return floatval($sValue);
				default:
					return stripslashes($sValue);
			}
		}
		
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
			debug($sHost, $sUser, $sPass, $sDb);
			self::$_oDb = new mysqli($sHost, $sUser, $sPass, $sDb);
			self::$_oDb->set_charset('utf8');
			
			if (self::$_oDb->connect_errno) {
				switch (self::$_oDb->connect_errno) {
					case 2002:
						throw new Exception('Mysql error: MYSQL SERVER IS DOWN');
					default:
						throw new Exception('Mysql error: (' . self::$_oDb->connect_errno . ') ' . self::$_oDb->connect_error);
				}
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
				switch (getType($aArgs[$n])) {
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
			if (self::$_oResult && self::$_oResult->data_seek(0)) {
				$aFields = self::$_oResult->fetch_fields();
				$nFields = count($aFields);
				while ($aRow = self::$_oResult->fetch_row()) {
					for ($n=0; $n<$nFields; $n++) {
						$aRow[$aFields[$n]->name] = self::_castValue($aRow[$n], $aFields[$n]->type);
						unset($aRow[$n]);
					}
					$aRows[] = $aRow;
				}
			}
			return $aRows;
		}
		
		public static function getRow($sQuery, $aArgs=[], $bDebug=false) {
			if ($sQuery) { self::query($sQuery, $aArgs, $bDebug); }
			$aReturnRow = [];
			if (self::$_oResult && self::$_oResult->data_seek(0)) {
				$aFields = self::$_oResult->fetch_fields();
				$nFields = count($aFields);
				if ($aRow = self::$_oResult->fetch_row()) {
					for ($n=0; $n<$nFields; $n++) {
						$aReturnRow[$aFields[$n]->name] = self::_castValue($aRow[$n], $aFields[$n]->type);
					}
					return $aReturnRow;
				}
			}
			return null;
		}
		
		public static function getValue($sQuery, $aArgs=[], $bDebug=false) {
			if ($sQuery) { self::query($sQuery, $aArgs, $bDebug); }
			if (self::$_oResult && self::$_oResult->data_seek(0)) {
				$aFields = self::$_oResult->fetch_fields();
				$nFields = count($aFields);
				if ($aRow = self::$_oResult->fetch_row()) {
					foreach ($aRow as $sKey=>$sValue) {
						return self::_castValue($aRow[0], $aFields[0]->type);
					}
				}
			}
			return null;
		}
		
		public static function getColumn($sQuery, $aArgs=[], $bDebug=false) {
			if ($sQuery) { self::query($sQuery, $aArgs, $bDebug); }
			$aColumn = [];
			if (self::$_oResult && self::$_oResult->data_seek(0)) {
				$aFields = self::$_oResult->fetch_fields();
				$nFields = count($aFields);
				while ($aRow = self::$_oResult->fetch_assoc()) {
					for ($n=0; $n<$nFields; $n++) {
						$aColumn[] = self::_castValue($aRow[$aFields[$n]->name], $aFields[$n]->type);
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