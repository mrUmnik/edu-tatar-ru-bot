<?php
namespace EduTatarRuBot;

class Database
{
	protected $connection;

	public function __construct($dbHost, $dbName, $dbLogin, $dbPassword)
	{
		$this->connection = new \PDO("mysql:host=$dbHost;dbname=$dbName", $dbLogin, $dbPassword, [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4']);
		$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	public function getConnection()
	{
		return $this->connection;
	}

	public function selectMany($expression, $values = array())
	{
		$statement = $this->connection->prepare($expression);
		$statement->setFetchMode(\PDO::FETCH_ASSOC);
		$statement->execute($values);
		$data = $statement->fetchAll();
		return $data;
	}
    public function selectOne($expression, $values = array())
    {
        $statement = $this->connection->prepare($expression);
        $statement->setFetchMode(\PDO::FETCH_ASSOC);
        $statement->execute($values);
        $data = $statement->fetch();
        return $data;
    }
}
