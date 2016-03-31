<?php
	include_once '../helper/direct-access-allowed.php';

	include_once '../model/doorModel.php';
	include_once '../model/logModel.php';
	
	switch( $_SERVER['REQUEST_METHOD'] ){
		case "GET":
			$response = new stdClass();
			$response->success = false;
			$response->message = "Could not read door";
			if( isset( $_GET["id"] ) ){
				$doorId = $_GET["id"];
				$state = DoorModel::getState( $doorId );
				if( $state ){
					$response->success = true;
					$response->message = $state;
				}
				else {
					$response->success = false;
					$response->message = "No state for door #$doorId";
				}
			}
			else {
				$response->message = "No door id given.";
			}
			LogModel::log( $response );
			print json_encode( $response );
			break;
		
		case "DELETE":
			$response = new stdClass();
			$response->success = false;
			$response->message = "Could not update door";
				
			$doorId = isset( $_GET["id"] ) ? $_GET["id"] : false;
			if( $doorId !== false ){
			
				$response->success = DoorModel::close( $doorId );
				if( $response->success ){
					$response->message = "Door #$doorId's state set to \"close\".";
				}
				else {
					$response->message = "Could set state \"close\" for #$doorId.";
				}
			}
			else {
				$response->message = "No door id given.";
			}
			LogModel::log( $response );
			print json_encode( $response );
			break;

		case "PUT":
			$response = new stdClass();
			$response->success = false;
			$response->message = "Could not update door";
				
			$doorId = isset( $_GET["id"] ) ? $_GET["id"] : false;
			if( $doorId !== false ){
			
				$response->success = DoorModel::open( $doorId );
				if( $response->success ){
					$response->message = "Door #$doorId's state set to \"open\".";
				}
				else {
					$response->message = "Could set state \"open\" for #$doorId.";
				}
			}
			else {
				$response->message = "No door id given.";
			}
			LogModel::log( $response );
			print json_encode( $response );
			break;
	}