<?php
namespace Src\TableGateways;

class UserGateway {

	private $db = null;

	public function __construct($db)
	{
		$this->db = $db;
	}

	public function findAll()
	{
		$statement = "SELECT * FROM users;";

		try {
			$statement = $this->db->query($statement);
			$result = $statement->fetchAll(\PDO::FETCH_ASSOC);
			return $result;
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}
	}
	public function findByLogin($name)
	{
		$statement = " SELECT * FROM users WHERE login = ?; ";

		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array($name));
			$result = $statement->fetch(\PDO::FETCH_ASSOC);
			return $result;
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}    
	}

	public function find($id)
	{
		$statement = " SELECT id, login, address, phone, role FROM users WHERE id = ?; ";

		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array($id));
			$result = $statement->fetch(\PDO::FETCH_ASSOC);
			return $result;
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}    
	}

	public function insert(Array $input)
	{
		$statement = "
			INSERT INTO users 
				(role, login, password, firstname, lastname, address, phone)
			VALUES
				(:role, :login, :password, :firstname, :lastname, :address, :phone);
		";

		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array(
				'firstname' => $input['firstname'],
				'lastname'  => $input['lastname'],
				'login' => $input['login'],
				'password'  => $input['password'],
				'role' => $input['role'],
				'phone'  => $input['phone'],
				'address'  => $input['address'],
			));
			return $statement->rowCount();
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}    
	}

	public function update($id, Array $input)
	{
		$statement = "
			UPDATE users
			SET 
				firstname = :firstname,
				lastname  = :lastname,
				firstparent_id = :firstparent_id,
				secondparent_id = :secondparent_id
			WHERE id = :id;
		";

		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array(
				'id' => (int) $id,
				'firstname' => $input['firstname'],
				'lastname'  => $input['lastname'],
				'firstparent_id' => $input['firstparent_id'] ?? null,
				'secondparent_id' => $input['secondparent_id'] ?? null,
			));
			return $statement->rowCount();
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}    
	}

	public function delete($id)
	{
		$statement = "
			DELETE FROM users
			WHERE id = :id;
		";

		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array('id' => $id));
			return $statement->rowCount();
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}    
	}
}