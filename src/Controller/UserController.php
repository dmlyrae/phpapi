<?php
namespace Src\Controller;

use Src\TableGateways\UserGateway;

class UserController {

	private $db;
	private $requestMethod;
	private $id;
	private $userGateway;
	private $salt;

	public function __construct($db, $requestMethod, $id)
	{
		$this->db = $db;
		$this->requestMethod = $requestMethod;
		$this->id = $id;
		$this->userGateway = new UserGateway($db);
		$this->salt = "__salt__";
	}

	public function processRequest()
	{
		switch ($this->requestMethod) {
			case 'GET':
				if ($this->id) {
					$response = $this->getUser($this->id);
				} else {
					$response = $this->getAllUsers();
				};
				break;
			case 'POST':
				$response = $this->createUserFromRequest();
				break;
			case 'PUT':
				$response = $this->updateUserFromRequest($this->id);
				break;
			case 'DELETE':
				$response = $this->deleteUser($this->id);
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

	private function getAllUsers()
	{
		$result = $this->userGateway->findAll();
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = json_encode($result);
		return $response;
	}

	private function getUser($id)
	{
		$result = $this->userGateway->find($id);
		if (! $result) {
			return $this->notFoundResponse();
		}
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = json_encode($result);
		return $response;
	}

	private function encryptPass ($pass) {
		$pepper = $this->salt; 
		$pwd = $pass;
		$pwd_peppered = hash_hmac("sha256", $pwd, $pepper);
		$pwd_hashed = password_hash($pwd_peppered, PASSWORD_ARGON2ID);
		return $pwd_hashed;
	}


	private function createUserFromRequest()
	{
		$input = (array) json_decode(file_get_contents('php://input'), TRUE);
		if (! $this->validateUser($input)) {
			return $this->unprocessableEntityResponse();
		}
		$input['password'] = $this->encryptPass(($input['password']));
		$newUser = $this->userGateway->insert($input);
		if ($newUser) {
			$response['status_code_header'] = 'HTTP/1.1 201 Created';
			$response['body'] = json_encode($newUser);
			return $response;
		} else {
			$this->unprocessableEntityResponse();
		}
	}

	private function updateUserFromRequest($id)
	{
		$result = $this->userGateway->find($id);
		if (! $result) {
			return $this->notFoundResponse();
		}
		$input = (array) json_decode(file_get_contents('php://input'), TRUE);
		if (! $this->validateUser($input)) {
			return $this->unprocessableEntityResponse();
		}
		$this->userGateway->update($id, $input);
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = null;
		return $response;
	}

	private function deleteUser($id)
	{
		$result = $this->userGateway->find($id);
		if (! $result) {
			return $this->notFoundResponse();
		}
		$this->userGateway->delete($id);
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = null;
		return $response;
	}

	private function validateUser($input)
	{
		if (! isset($input['login'])) {
			return false;
		}
		if (! isset($input['password'])) {
			return false;
		}
		return true;
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