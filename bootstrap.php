<?php
	use Src\System\DatabaseConnector;
	use Dotenv\Dotenv;

	require 'vendor/autoload.php';

	$dotenv = Dotenv::createImmutable(__DIR__);
	$dotenv->load();

	$DB_HOST = $_ENV['DB_HOST'];
	$DB_PORT = $_ENV['DB_HOST'];
	$DB_DATABASE = $_ENV["DB_DATABASE"];
	$DB_USERNAME = $_ENV["DB_USERNAME"];
	$DB_PASSWORD = $_ENV["DB_PASSWORD"];

	$dbConnection = (new DatabaseConnector())->getConnection();
?>