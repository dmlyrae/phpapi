<?php
namespace Src\TableGateways;


class RequestGateway {

	private $db = null;

	public function __construct($db)
	{
		$this->db = $db;
	}

	public function findAll()
	{
		$statement = "SELECT * FROM requests;";

		try {
			$statement = $this->db->query($statement);
			$result = $statement->fetchAll(\PDO::FETCH_ASSOC);
			return $result;
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}
	}
	public function findByUser($user)
	{
		$statement = " SELECT id, user, request time FROM requests WHERE user = ?; ";

		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array($user));
			$result = $statement->fetch(\PDO::FETCH_ASSOC);
			return $result;
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}
	}

	public function find($id)
	{
		$statement = ' SELECT id, "user", request, processed FROM requests WHERE id = ?; ';

		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array($id));
			$req = $statement->fetch(\PDO::FETCH_ASSOC);
			if ($req) {
				$statementUser = $this->db->prepare(" SELECT id, role, login, firstname, lastname, address, phone FROM users WHERE id = ?; ");
				$statementUser->execute(array($req['user']));
				$user = $statementUser->fetch();
				$req['user'] = [
					'id' => $user['id'],
					'firstname' => $user['firstname'],
					'lastname' => $user['lastname'],
					'phone' => $user['phone'],
					'address' => $user['address'],
				];
			}
			return $req;
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}    
	}

	public function insert(String $userId, Array $input)
	{
		$statement = ' INSERT INTO requests ("user", request) VALUES (:user, :request) RETURNING id; ';
		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array(
				'user' => (string) $userId,
				// 'request' => json_encode($input)
				'request' => json_encode([
					'menu' => $input['menu'],
					'common' => $input['common'],
				]),
			));
			return $statement->rowCount();
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}    
	}

	public function done(Array $ids)
	{
		$sqlIds = implode(',',str_split(str_repeat('?',count($ids))));

		try {
			$statement = $this->db->prepare(" UPDATE requests SET processed = true WHERE id in ($sqlIds); ");
			$statement->execute($ids);
			$statementSelect = $this->db->prepare(" SELECT * FROM requests WHERE id in ($sqlIds);");
			$statementSelect->execute($ids);
			$result = $statement->fetchAll(\PDO::FETCH_ASSOC);
			return $result;
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}    
	}

	public function delete($id)
	{
		$statement = " DELETE FROM requests WHERE id = :id; ";

		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array('id' => $id));
			return $statement->rowCount();
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}    
	}
}