<?php
namespace EduTatarRuBot\Models;

use EduTatarRuBot\Application;
use EduTatarRuBot\Exceptions\EduTatarRuBotException;

abstract class Model
{
	protected $data = null;

	public function load($id)
	{
		$data = Application::getInstance()->getDB()->selectOne("SELECT * FROM " . static::getTableName() . " WHERE ID=:ID", array('ID' => intval($id)));
		if (false === $data) {
			Throw new EduTatarRuBotException('Entity #' . $id . ' not found');
		}
		$this->loadFromArray($data);
	}

	protected static function getTableName()
	{

	}

	protected function loadFromArray($data)
	{
		$this->data = $data;
	}

	public function getValue($name)
	{
		if (!$this->isLoaded()) {
			Throw new EduTatarRuBotException('Entity is not loaded');
		}
		if (!array_key_exists($name, $this->data)) {
			Throw new EduTatarRuBotException('Field ' . $name . ' does not exists');
		}
		return $this->data[$name];
	}

	public function isLoaded()
	{
		return $this->data !== null;
	}

	public function setValue($name, $value)
	{
		if (!$this->isLoaded()) {
			Throw new EduTatarRuBotException('Entity is not loaded');
		}
		if (!array_key_exists($name, $this->data)) {
			Throw new EduTatarRuBotException('Field ' . $name . ' does not exists');
		}
		$statement = Application::getInstance()->getDB()->getConnection()->prepare("UPDATE " . static::getTableName() . " SET $name=:value WHERE ID=:id LIMIT 1");
		$statement->execute([':value' => $value, ':id' => $this->data['ID']]);
		$this->data[$name] = $value;
	}

	public function setValues(array $data)
	{
		if (!$this->isLoaded()) {
			Throw new EduTatarRuBotException('Entity is not loaded');
		}
		if (!is_array($data) || empty($data)) {
			Throw new EduTatarRuBotException('Empty data for update');
		}
		$updateFieldValues = [
			':id' => $this->data['ID']
		];
		$updateFieldExpressions = [];
		foreach ($data as $name => $value) {
			if (!array_key_exists($name, $this->data)) {
				Throw new EduTatarRuBotException('Field ' . $name . ' does not exists');
			}
			$updateFieldValues[':' . $name] = $value;
			$updateFieldExpressions[] = $name . '=:' . $name;

			$this->data[$name] = $value;
		}
		$statement = Application::getInstance()->getDB()->getConnection()->prepare("UPDATE " . static::getTableName() . " SET " . implode(', ', $updateFieldExpressions) . " WHERE ID=:id LIMIT 1");
		$statement->execute($updateFieldValues);
	}

	public function add($data)
	{
		$updateFieldValues = [];
		$updateFieldExpressions = [];
		foreach ($data as $name => $value) {
			$updateFieldValues[':' . $name] = $value;
			$updateFieldExpressions[] = $name . '=:' . $name;

			$this->data[$name] = $value;
		}
		$connection = Application::getInstance()->getDB()->getConnection();
		$statement = $connection->prepare("INSERT INTO " . static::getTableName() . " SET " . implode(', ', $updateFieldExpressions));
		$statement->execute($updateFieldValues);
		$data['ID'] = $connection->lastInsertId();
		$this->loadFromArray($data);

		return $data;
	}
}
