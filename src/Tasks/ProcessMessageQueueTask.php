<?php
namespace EduTatarRuBot\Tasks;

use EduTatarRuBot\Exceptions\WrongUsernameException;
use EduTatarRuBot\Helpers\Date;
use EduTatarRuBot\Models\Client;
use EduTatarRuBot\Models\Diary;
use EduTatarRuBot\Models\Homework;
use EduTatarRuBot\Models\MessageQueue;

class ProcessMessageQueueTask extends Task
{
	public function run()
	{
		$items = MessageQueue::getAllActive();
		$startTime = time();
		foreach ($items as $message) {
			/**
			 * @var $message MessageQueue
			 */
			$message->send($message->getValue('CHAT_ID'), $message->getValue('MESSAGE'), $message->getValue('TYPE'));
			$message->setValue('REAL_SENT_TIME', date('Y-m-d H:i:s'));
			if (time() - $startTime > 50) { // работает не больше 50 секунд
				break;
			}
		}

	}
}