<?php
include_once( '../helper/no-direct-access.php' );
include_once 'database.php';
class DoorModel extends Database {
	
	/**
	 * Update a given door's state to "close".
	 * 
	 * @param $doorId (int)
	 * 
	 * @return success (bool)
	 */
	public static function close( $doorId ){
		self::closeAllDoorsOpenPastEllapsedTime();
		return self::changeDoorState( $doorId, "close" );
	}

	/**
	 * Update a given door's state to "open".
	 * 
	 * @param $doorId (int)
	 * 
	 * @return success (bool)
	 */
	public static function open( $doorId ){
		self::closeAllDoorsOpenPastEllapsedTime();
		return self::changeDoorState( $doorId, "open" );
	}
	
	/**
	 * Returns the state of a given door
	 * after closing all doors with elsapsed "timers".
	 * 
	 * @param $doorId (int)
	 * 
	 * @return state (string)
	 */
	public static function getState( $doorId ){
		self::closeAllDoorsOpenPastEllapsedTime();
		$db = self::connect();
		$stmt = $db->prepare("
			SELECT
				state
			FROM
				door
			WHERE
				id = ?
		");
		if( $stmt ){
			$stmt->bind_param( "i", $doorId );
			$stmt->execute();
			$stmt->bind_result( $state );
			$stmt->fetch();
			return $state ? $state : false;
		}
		else {
			return null;
		}
	}
	
	/**
	 * Closes all doors that were last changed 30 seconds ago
	 * 
	 * @param n/a
	 * 
	 * @return success (bool)
	 * 
	 */
	private static function closeAllDoorsOpenPastEllapsedTime(){
		$db = self::connect();
		$stmt = $db->prepare("
			UPDATE
				door
			SET
				state = 'closed'
			WHERE
				last_update = ?
		");
		if( $stmt ){
			$time = strtotime( "30 seconds ago" );
			$stmt->bind_param( "s", $time );
			$stmt->execute();
			return true;
		}
		else {
			return false;
		}
	}
	
	/**
	 * Updates a given door to the selected state
	 * 
	 * @param $doorId (int)
	 * @param $state (string)
	 * 
	 * @return success (bool)
	 * 
	 */
	private static function changeDoorState( $doorId, $state ){
		$db = self::connect();
		$stmt = $db->prepare("
			UPDATE
				door
			SET
				state = ?
			WHERE
				id = $doorId
		");
		if( $stmt ){
			$stmt->bind_param( "s", $state );
			$stmt->execute();
			return true;
		}
		else {
			return false;
		}
	}
}