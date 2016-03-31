<?php
include_once( '../helper/no-direct-access.php' );

class Database {
	protected static $table = false;
	protected static $connection = false;

	/**
	 * Connects to the database and creates the table references needed.
	 * 
	 * @param n/a
	 * 
	 * @return connection (mySQLobject)
	 */
	protected static function connect(){
		if( ! self::$connection ){
			include_once( "../config.php" );
			
			// Connect to the database described in config
			self::$connection = new mysqli(
				DATABASE_HOST,
				DATABASE_USER,
				DATABASE_PASSWORD,
				DATABASE_NAME
			);

			// Create a reference to the tables used
			$table = new stdClass();
			$table->log  = "request_log";
			$table->door = "door";
			self::$table = $table;
		}
		return self::$connection;
	}
}