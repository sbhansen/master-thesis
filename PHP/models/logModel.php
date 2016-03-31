<?php
include_once 'database.php';
class LogModel extends Database {
	
	/**
	 * Log requests to API to database.
	 * 
	 * @param $data (mixed)
	 * 
	 * @return success (bool)
	 */
	public static function log( $data ){
		$data = json_encode( $data );
		$db = self::connect();
		$data = $db->real_escape_string( $data );
		$table = self::$table->log;
		$stmt = $db->prepare("
			INSERT INTO $table
				( request_data )
			VALUES
				( '$data' )
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