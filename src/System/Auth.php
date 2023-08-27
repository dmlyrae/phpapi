<?php
	function generateJWT():String{
		$signing_key = "changeme";
		$header = [ 
			"alg" => "HS512", 
			"typ" => "JWT" 
		];
		$header = base64_url_encode(json_encode($header));
		$payload =  [
			"exp" => 0,
		];
		$payload = base64_url_encode(json_encode($payload));
		$signature = base64_url_encode(hash_hmac('sha512', "$header.$payload", $signing_key, true));
		$jwt = "$header.$payload.$signature";
		return $jwt;    
	}

	function base64_url_encode($text):String {
		return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($text));
	}
	function base64_url_decode($text) {     
		return base64_decode(str_replace(['-', '_'], ['+', '/'], $text)); 
	}
?>