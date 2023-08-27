<?php
namespace Src\TableGateways;

class MenuGateway {

	private $db = null;

	public function __construct($db)
	{
		$this->db = $db;
	}

	public function findAll()
	{
		$statement = "SELECT * FROM current_menu;";

		try {
			$statement = $this->db->query($statement);
			$result = $statement->fetchAll(\PDO::FETCH_ASSOC);
			return $result;
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}
	}
	public function findLast()
	{
		$statement = " SELECT stamp, menu, id FROM current_menu ORDER BY stamp DESC LIMIT 1	; ";
		try {
			$statement = $this->db->prepare($statement);
			$statement->execute();
			$result = $statement->fetch(\PDO::FETCH_ASSOC);
			return $result;
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}    
	}

	public function find($id)
	{
		$statement = " SELECT * FROM current_menu WHERE id = ?; ";

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
		$statement = " INSERT INTO current_menu (:menu) VALUES (:menu); ";

		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array( 'menu' => $input['menu'],));
			return $statement->rowCount();
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}    
	}

	public function update($id, $input)
	{
		$statement = " UPDATE current_menu SET menu  = :menu WHERE id = :id; ";

		try {
			$statement = $this->db->prepare($statement);
			$statement->execute(array(
				'menu' => json_encode($input['menu']),
				'id' => $input['id'],
			));
			return $statement->rowCount();
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}    
	}

	public function delete($id)
	{
		$statement = "
			DELETE FROM current_menu
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