<?php
namespace EduTatarRuBot;

class Database
{
	protected $connection;

	public function __construct($dbHost, $dbName, $dbLogin, $dbPassword)
	{
		$this->connection = new \PDO("mysql:host=$dbHost;dbname=$dbName", $dbLogin, $dbPassword);
		$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	public function getConnection()
	{
		return $this->connection;
	}
}
