<?php
namespace EduTatarRuBot;


use EduTatarRuBot\Tasks\Task;

class Application
{
	protected $db;
	protected $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
		$this->db = new Database(
			$this->getConfig('database:host'),
			$this->getConfig('database:name'),
			$this->getConfig('database:login'),
			$this->getConfig('database:password')
		);
	}

	public function getConfig($param)
	{
		return $this->config->get($param);
	}

	public function run(Task $task)
	{
		$task->setApplication($this);
		$task->run();
	}
}