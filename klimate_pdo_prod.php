<?php
	class mysqlPDO extends PDO {
		public function __construct() {
			$drv = 'mysql';
			$hst = '128.39.19.159'; // eller 's120.hbv.no'
			$usr = 'usr_klima';
			$pwd = 'pw_klima';
			$sch = 'klima';
			$dsn = $drv . ':host=' . $hst . ';dbname=' . $sch;
			parent::__construct($dsn,$usr,$pwd);
		}
	}
?>