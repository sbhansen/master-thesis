<?php
class REST {
	
	private static $filterInput;
	private static $input;
	
	static function getData( $filterData = false, $decode_utf8 = false ){
		
		// Select if data needs to be filtered or not
		self::$filterInput = $filterData;
		
		// read incoming data
		self::$input = file_get_contents('php://input');
		
		if( ! self::$filterInput ){
			
			$response = array();
			
			foreach( explode( "&", self::$input ) as $variablePairs ){
				
				$variableItems = explode( "=", $variablePairs );

				if($decode_utf8) {
					$value = utf8_decode(urldecode( $variableItems[ 1 ] ));
				}
				else {
					$value = urldecode( $variableItems[ 1 ] );
				}
				$response[ $variableItems[ 0 ] ] = $value;
			}
			return $response;
			
		} else {		
			// grab multipart boundary from content type header
			preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'], $matches);
			$boundary = $matches[1];
		
			// split content by boundary and get rid of last -- element
			$a_blocks = preg_split("/-+$boundary/", self::$input);
			array_pop($a_blocks);
		
			// loop data blocks
			foreach ($a_blocks as $id => $block)
			{
				if (empty($block))
					continue;
		
				// you'll have to var_dump $block to understand this and maybe replace \n or \r with a visibile char
		
				// parse uploaded files
				if (strpos($block, 'application/octet-stream') !== FALSE)
				{
					// match "name", then everything after "stream" (optional) except for prepending newlines
					preg_match("/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s", $block, $matches);
				}
				// parse all other fields
				else
				{
					// match "name" and optional value in between newline sequences
					preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
				}
				$a_data[$matches[1]] = $matches[2];
			}
			return $a_data;
		}
	}
}