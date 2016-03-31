<?php
class Database {
	protected static $table = false;
	protected static $connection = false;
	protected static function connect(){
		if( ! self::$connection ){
			include_once( "../config.php" );
			
			self::$connection = new mysqli(
				DATABASE_HOST,
				DATABASE_USER,
				DATABASE_PASSWORD,
				DATABASE_NAME
			);
			$table = new stdClass();
			$table->log  = "request_log";
			$table->door = "door";
			self::$table = $table;
		}
		return self::$connection;
	}
}