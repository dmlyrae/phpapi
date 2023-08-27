<?php
namespace Src\Controller;

use Src\TableGateways\RequestGateway;

class RequestController {

	private $db;
	private $requestMethod;
	private $requestId;
	private $requestGateway;
	private $user;

	public function __construct($db, $requestMethod, $requestId, $user)
	{
		$this->db = $db;
		$this->requestMethod = $requestMethod;
		$this->requestId = $requestId;
		$this->requestGateway = new RequestGateway($db);
		$this->user = $user;
	}

	public function processRequest()
	{
		switch ($this->requestMethod) {
			case 'GET':
				if ($this->requestId) {
					$response = $this->getRequest($this->requestId);
				} else {
					$response = $this->getAllRequests();
				};
				break;
			case 'POST':
				$response = $this->createRequestFromRequest();
				break;
			case 'PUT':
				$response = $this->completeRequests($this->requestId);
				break;
			case 'DELETE':
				$response = $this->deleteRequest($this->requestId);
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

	private function getAllRequests()
	{
		$result = $this->requestGateway->findAll();
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = json_encode($result);
		return $response;
	}

	private function getRequest($id)
	{
		$result = $this->requestGateway->find($id);
		if (! $result) {
			return $this->notFoundResponse();
		}
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = json_encode(Array($result));
		return $response;
	}

	private function createRequestFromRequest()
	{
		$input = (array) json_decode(file_get_contents('php://input'), TRUE);
		if (! $this->validateRequest($input)) {
			return $this->unprocessableEntityResponse();
		}
		$userId = $this->user['id'];
		$result = $this->requestGateway->insert($userId, $input);
		$response['status_code_header'] = 'HTTP/1.1 201 Created';
		$response['body'] = json_encode([
			"error" => "",
			"result" => $result,
		]);
		return $response;
	}

	private function completeRequests($id)
	{
		$ids = (array) json_decode(file_get_contents('php://input'), TRUE);
		$result = $this->requestGateway->done($ids);
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = json_encode($result);
		return $response;
	}

	private function deleteRequest($id)
	{
		$result = $this->requestGateway->find($id);
		if (! $result) {
			return $this->notFoundResponse();
		}
		$this->requestGateway->delete($id);
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = null;
		return $response;
	}

	private function validateRequest($input)
	{
		if (! isset($input['common'])) {
			return false;
		}
		if (! isset($input['menu'])) {
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