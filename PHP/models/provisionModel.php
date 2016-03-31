<?php
include_once 'database.php';
class ProvisionModel extends Database {
	
	/**
	 * Drop table and reprovision.
	 * 
	 * @param n/a
	 * 
	 * @return success (bool)
	 */
	public static function reset(){
		$db = self::connect();
		$table = (array) self::$table;
		$table = implode( ", ", $table );
		$stmt = $db->prepare("
			DROP TABLE IF EXISTS $table
		");
		if( $stmt ){
			$stmt->execute();
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Truncate the door table effectly whiping all doors.
	 * 
	 * @param n/a
	 * 
	 * @return success (bool)
	 */
	private static function deleteAllDoors(){
		$db = self::connect();
		$table = self::$table->door;
		$stmt = $db->prepare("
			TRUNCATE TABLE
				$table
		");
		if( $stmt ){
			$stmt->execute();
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Creates a table to hold all doors.
	 * Adds "default" doors to the table.
	 */
	public static function provision(){
		$success = array();
		$success["doorTable"]       = self::createDoorTable();
		$success["defaultDoors"]    = self::createDefaultDoors();
		$success["requestLogTable"] = self::createRequestLogTable();
		return array_sum( $success );
	}
	
	
	/**
	 * Creates a table to hold all requests.
	 */
	private static function createRequestLogTable(){
		$db = self::connect();
		$table = self::$table->log;
		$stmt = $db->prepare("
			CREATE TABLE $table (
				id int PRIMARY KEY,
				request_data varchar(255),
				last_update timestamp
			)
		");
		if( $stmt ){
			$stmt->execute();
			return true;
		}
		else {
			return null;
		}
	}
	
	
	/**
	 * Creates a table to hold all doors.
	 */
	private static function createDoorTable(){
		$db = self::connect();
		$table = self::$table->door;
		$stmt = $db->prepare("
			CREATE TABLE $table (
				id int PRIMARY KEY,
				state varchar(12),
				last_update timestamp
			)
		");
		if( $stmt ){
			$stmt->execute();
			return true;
		}
		else {
			return null;
		}
	}
	
	/**
	 * Adds default doors to door table.
	 */
	public static function createDefaultDoors(){
		$db = self::connect();
		$table = self::$table->door;
		$stmt = $db->prepare("
			INSERT INTO $table
				( state )
			VALUES
				( 'close' )
		");
		if( $stmt ){
			$stmt->execute();
			return true;
		}
		else {
			return false;
		}
	}
}