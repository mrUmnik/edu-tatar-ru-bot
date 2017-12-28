<?php
namespace EduTatarRuBot;


use EduTatarRuBot\Tasks\Task;
use Longman\TelegramBot\TelegramLog;

class Application
{
	protected $db;
	protected $config;
	protected $telegramBot;

	protected static $instance;

	protected function __construct(Config $config)
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
		TelegramLog::initErrorLog(ROOT_DIR . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . date('Y-m-d') . '_error.log');
		TelegramLog::initDebugLog(ROOT_DIR . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . date('Y-m-d') . '_debug.log');
		TelegramLog::initUpdateLog(ROOT_DIR . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . date('Y-m-d') . '_update.log');

	}

	public function getConfig($param)
	{
		return $this->config->get($param);
	}

    public function getTelegramBot()
    {
        return $this->telegramBot;
    }

    public function getDB()
    {
        return $this->db;
    }

	public function run(Task $task)
	{
		$task->run();
	}

	public static function getInstance()
	{
		if (null == self::$instance) {
			self::$instance = new self(new Config());
		}
		return self::$instance;
	}
}