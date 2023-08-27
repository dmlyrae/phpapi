<?php
namespace Src\Controller;

use Src\TableGateways\MenuGateway;

class MenuController {

	private $db;
	private $requestMethod;
	private $id;
	private $menuGateway;
	private $salt;

	public function __construct($db, $requestMethod, $id)
	{
		$this->db = $db;
		$this->requestMethod = $requestMethod;
		$this->id = $id;
		$this->menuGateway = new MenuGateway($db);
	}

	public function processRequest()
	{
		switch ($this->requestMethod) {
			case 'GET':
				if ($this->id) {
					$response = $this->getMenu($this->id);
				} else {
					$response = $this->getLastMenu();
				};
				break;
			case 'POST':
				$response = $this->createMenuFromRequest();
				break;
			case 'PUT':
				$response = $this->updateMenuFromRequest($this->id);
				break;
			case 'DELETE':
				$response = $this->deleteMenu($this->id);
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

	private function getAllMenus()
	{
		$result = $this->menuGateway->findAll();
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = json_encode($result);
		return $response;
	}

	private function getLastMenu()
	{
		$result = $this->menuGateway->findLast();
		if (! $result) {
			return $this->notFoundResponse();
		}
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = json_encode($result);
		return $response;
	}

	private function createMenuFromRequest()
	{
		$input = (array) json_decode(file_get_contents('php://input'), TRUE);
		if (! $this->validateMenu($input)) {
			return $this->unprocessableEntityResponse();
		}
		$newMenu = $this->menuGateway->insert($input);
		if ($newMenu) {
			$response['status_code_header'] = 'HTTP/1.1 201 Created';
			$response['body'] = json_encode($newMenu);
			return $response;
		} else {
			$this->unprocessableEntityResponse();
		}
	}

	private function updateMenuFromRequest($id)
	{
		$result = $this->menuGateway->find($id);
		if (! $result) {
			return $this->notFoundResponse();
		}
		$input = (array) json_decode(file_get_contents('php://input'), TRUE);
		if (! $this->validateMenu($input)) {
			return $this->unprocessableEntityResponse();
		}
		$this->menuGateway->update($id, $input);
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = null;
		return $response;
	}

	private function deleteMenu($id)
	{
		$result = $this->menuGateway->find($id);
		if (! $result) {
			return $this->notFoundResponse();
		}
		$this->menuGateway->delete($id);
		$response['status_code_header'] = 'HTTP/1.1 200 OK';
		$response['body'] = null;
		return $response;
	}

	private function validateMenu($input)
	{
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