<?php
namespace Src\System;

class DatabaseConnector {

	private $dbConnection = null;

	public function __construct()
	{
		// $host = '127.0.0.1';
		// $port = '5432';
		// $db   = 'kitchen';
		// $user = 'dm';
		// $pass = '1012';
		$DB_HOST = $_ENV['DB_HOST'];
		$DB_PORT = $_ENV['DB_HOST'];
		$DB_DATABASE = $_ENV["DB_DATABASE"];
		$DB_USERNAME = $_ENV["DB_USERNAME"];
		$DB_PASSWORD = $_ENV["DB_PASSWORD"];

		$dsn = "pgsql:host=$DB_HOST;dbname=$DB_DATABASE;options='--client_encoding=UTF8'";
		$options = [
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_STRINGIFY_FETCHES => false,
			\PDO::ATTR_EMULATE_PREPARES => false,
		];

		try {
			$this->dbConnection = new \PDO($dsn,$DB_USERNAME,$DB_PASSWORD,$options);
		} catch (\PDOException $e) {
			exit($e->getMessage());
		}
	}

	public function getConnection()
	{
		return $this->dbConnection;
	}
}