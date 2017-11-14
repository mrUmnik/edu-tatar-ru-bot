<?php
namespace EduTatarRuBot\Tasks;
use EduTatarRuBot\Application;

class ProcessWebhookTask extends Task
{
	public function run()
	{
		Application::getInstance()->getTelegramBot()->handle();
	}
}