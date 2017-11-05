<?php
namespace EduTatarRuBot\Tasks;
class ProcessWebhookTask extends Task
{
	public function run()
	{
		$this->getApplication()->getTelegramBot()->handle();
	}
}