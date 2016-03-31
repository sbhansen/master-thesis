<?php
	include_once '../helper/direct-access-allowed.php';
	
	include_once '../model/provisionModel.php';
	
	switch( $_SERVER['REQUEST_METHOD'] ){
		case "GET":
		case "PUT":
		case "POST":
			// Add new doors
			$response = new stdClass();
			$response->success = ProvisionModel::provision();
			
			if( $response->success ){
				$response->message = "Doors Provisioned";
			}
			else {
				$response->message = "It's complicated.";
			}
			print json_encode( $response );
			break;
			
		case "DELETE":
			//Remove all doors
			$response = new stdClass();
			$response->success = ProvisionModel::reset();
			if( $response->success ){
				$response->message = "All doors deleted.";
			}
			else {
				$response->message = "Could not remove doors.";
			}
			print json_encode( $response );
			break;
	}