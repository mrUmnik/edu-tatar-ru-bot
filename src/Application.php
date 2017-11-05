<?php
namespace EduTatarRuBot;


use EduTatarRuBot\Tasks\Task;

class Application
{
	protected $db;
	protected $config;
	protected $telegramBot;

	public function __construct(Config $config)
	{
		$this->config = $config;
		$this->db = new Database(
			$this->getConfig('database:host'),
			$this->getConfig('database:name'),
			$this->getConfig('database:login'),
			$this->getConfig('database:password')
		);
		$this->telegramBot = new \Longman\TelegramBot\Telegram($this->getConfig('bot:token'), $this->getConfig('bot:name'));
		$this->telegramBot->enableExternalMySql($this->db->getConnection());

		$commands_paths = [
			ROOT_DIR . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Commands' . DIRECTORY_SEPARATOR
		];
		$this->telegramBot->addCommandsPaths($commands_paths);
		$this->telegramBot->enableLimiter();
		$this->telegramBot->enableBotan($this->getConfig('bot:botan_token'));
		$this->telegramBot->enableAdmin($this->getConfig('bot:admin_id'));
	}

	public function getConfig($param)
	{
		return $this->config->get($param);
	}

	public function getTelegramBot()
	{
		return $this->telegramBot;
	}
	public function run(Task $task)
	{
		$task->setApplication($this);
		$task->run();
	}
}