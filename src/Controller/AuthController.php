<?php
namespace Src\Controller;

use DateTimeImmutable;
use Firebase\JWT\Key;
use Src\TableGateways\UserGateway;
use Firebase\JWT\JWT;
use stdClass;

class AuthController {

	private $db;
	private $requestMethod;
	private $userId;
	private $token;
	private $userGateway;
	private $secretKey;
	private $tokenId;
	private $serverName;
	private $salt;


	public function __construct($db, $requestMethod, $userId)
	{
		$this->db = $db;
		$this->requestMethod = $requestMethod;
		$this->userId = $userId;
		$this->salt = $_ENV["JWT_SALT"];
		$this->secretKey = $_ENV["JWT_SECRET"];
		$this->tokenId    = base64_encode(random_bytes(16));
		$this->serverName =  "127.0.0.1";
		$this->userGateway = new UserGateway($db);
	}

	private function verifyPass ($pass, $hash) {
		$pepper = $this->salt;
		$pwd = $pass;
		$pwd_peppered = hash_hmac("sha256", $pwd, $pepper);
		$pwd_hashed = $hash;
		return password_verify($pwd_peppered, $pwd_hashed);
	}

	public function processRequest()
	{
		switch ($this->requestMethod) {
			case 'OPTIONS':
				$this->ok();
				break;
			case 'POST':
				$response = $this->auth();
				break;
			case 'GET':
				$response = $this->validateToken();
				break;
			default:
				$response = $this->notFoundResponse();
				break;
		}
		header($response['status_code_header']);
		if ($response['body']) {
			echo $response['body'];
		}
	}

	private function ok () {
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Headers: *");
		exit();
	}

	private function validateAuth($input)
	{
		if (! isset($input['login'])) {
			return false;
		}
		if (! isset($input['password'])) {
			return false;
		}
		return true;
	}

	private function createToken($user) {
		$userId = $user['id'];
		$issuedAt = new DateTimeImmutable();
		$expire = $issuedAt->modify('7 days')->getTimestamp();
		$data = [
			'iat'  => $issuedAt->getTimestamp(),  
			'jti'  => $this->tokenId,                  
			'iss'  => $this->serverName,               
			'nbf'  => $issuedAt->getTimestamp(),    // Not before
			'exp'  => $expire,      
			'data' => [             
				'userId' => $userId,  	
			]
		];
		$token = JWT::encode(
			$data,
			$this->secretKey, // The signing key
			'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
		);

		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		// $response['body'] = $token;
		$response['body'] = json_encode([
			'token' => $token,
			'error' => false,
			'user' => [
				'login' => $user['login'],
				'role' => $user['role'],
				'id' => $userId,
				'address' => $user['address'],
				'phone' => $user['phone'],
				'firstname' => $user['firstname'],
				'lastname' => $user['lastname']
			]
		]);
		return $response;
	}

	private function validateToken() {
		$user = $this->checkToken();
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = json_encode([
			'user' => [
				'login' => $user['login'],
				'role' => $user['role'],
				'id' => $user['id'],
				'address' => $user['address'],
				'phone' => $user['phone'],
				'firstname' => $user['firstname'],
				'lastname' => $user['lastname']
			],
			'error' => false,
		]);
		return $response;
	}
	public function checkToken() {

		if (! preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
			header('HTTP/1.0 400 Bad Request');
			echo 'Token not found in request';
			exit;
		}

		$jwt = $matches[1];
		if (! $jwt) {
			// No token was able to be extracted from the authorization header
			header('HTTP/1.0 400 Bad Request');
			exit;
		}

		$serverName = $this->serverName;
		$secretKey = $this->secretKey;
		$headers = new stdClass();
		$token = JWT::decode($jwt, new Key($secretKey, 'HS512'), $headers);
		$now = new DateTimeImmutable();

		if ($token->iss !== $serverName ||
			$token->nbf > $now->getTimestamp() ||
			$token->exp < $now->getTimestamp()) {
			header('HTTP/1.1 401 Unauthorized');
			exit;
		}

		$userId = $token->data->userId;
		$user = $this->userGateway->find($userId);

		return $user;
	}

	private function auth()
	{
		$input = (array) json_decode(file_get_contents('php://input'), TRUE);
		if (! $this->validateAuth($input)) {
			return $this->unprocessableEntityResponse();
		}

		$user = $this->userGateway->findByLogin($input["login"]);
		if (!$user) {
			return $this->notFoundResponse();
		}
		$verify = $this->verifyPass($input['password'], $user['password']);
		if ($verify) {
			return $this->createToken($user);
		} else {
			return $this->unprocessableEntityResponse();
		}
	}

	private function badAuthorization()
	{
		$response['status_code_header'] = 'HTTP/1.1 401 Unauthorized';
		$response['body'] = json_encode([
			'error' => 'Invalid input'
		]);
		return $response;
	}

	private function unprocessableEntityResponse()
	{
		$response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
		$response['body'] = json_encode([
			'error' => 'Invalid input'
		]);
		return $response;
	}

	private function notFoundResponse()
	{
		$response['status_code_header'] = 'HTTP/1.1 404 Not Found';
		$response['body'] = null;
		return $response;
	}
}