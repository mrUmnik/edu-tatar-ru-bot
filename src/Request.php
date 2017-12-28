<?php

namespace EduTatarRuBot;

use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\TelegramLog;

class Request extends \Longman\TelegramBot\Request
{
	public static function sendMessage(array $data)
	{
		$result = parent::sendMessage($data);
		if ($result->isOk()) {
			TelegramLog::debug(print_r($data, true));
//            $messageData = $data;
//            $message = new Message($messageData, Application::getInstance()->getConfig('bot:name'));
//            DB::insertMessageRequest($message);
		}
		return $result;
	}
}