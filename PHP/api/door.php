<?php
	include_once 'REST.php';
	include_once '../models/doorModel.php';
	include_once '../models/logModel.php';
	
	switch( $_SERVER['REQUEST_METHOD'] ){
		case "GET":
			$response = new stdClass();
			$response->success = false;
			$response->message = "Could not read door";
			if( isset( $_GET["id"] ) ){
				$response->success = true;
				$response->message = DoorModel::getState( $_GET["id"] );
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