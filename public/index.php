<?php
	require "../bootstrap.php";
	use Src\Controller\AuthController;
	use Src\Controller\MenuController;
	use Src\Controller\RequestController;
	use Src\Controller\UserController;

	header("Access-Control-Max-Age: 3600");
	header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");

	$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$uri = explode( '/', $uri );

	if ($uri[1] === 'api' && isset($uri[2])) {
		header("Content-Type: application/json; charset=UTF-8");
		$requestMethod = $_SERVER["REQUEST_METHOD"];
		$id = null;
		if (isset($uri[3])) {
			$id = (string) $uri[3];
		}

		$auth = new AuthController($dbConnection, $requestMethod, $id);

		if ($requestMethod === "OPTIONS") {
			$response['status_code_header'] = 'HTTP/1.1 200 OK';
			header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
			header("Access-Control-Allow-Origin: *");
			header("Access-Control-Allow-Headers: *");
			exit();
		}

		if ($uri[2] === 'auth') {
			$controller = $auth;
			$controller->processRequest();
			exit();
		}

		$user = $auth->checkToken();

		if ($uri[2] === 'user') {
			$controller = new UserController($dbConnection, $requestMethod, $id);
			$controller->processRequest();
			exit();
		}

		if ($uri[2] === 'menu') {
			$controller = new MenuController($dbConnection, $requestMethod, $id);
			$controller->processRequest();
			exit();
		}

		if ($uri[2] === 'request') {
			$controller = new RequestController($dbConnection, $requestMethod, $id, $user);
			$controller->processRequest();
			exit();
		}
	}

	header("Content-Type: text/html; charset=utf-8");
	echo /*html*/'
		<!doctype html>
			<html lang="en">
			<head>
				<meta charset="UTF-8" />
				<link rel="icon" type="image/svg+xml" href="/vite.svg" />
				<meta name="viewport" content="width=device-width, initial-scale=1.0" />
				<title>Vite + React + TS</title>
				<script type="module" crossorigin src="/assets/index-c7e05d32.js"></script>
				<link rel="stylesheet" href="/assets/index-d526a0c5.css">
			</head>
			<body>
				<div id="root"></div>
				
			</body>
		</html>
	';
	exit();


	// header("HTTP/1.1 404 Not Found");

?>


