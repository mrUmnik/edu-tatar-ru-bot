<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\SystemCommands;

use EduTatarRuBot\Exceptions\EduTatarRuBotException;
use EduTatarRuBot\Helpers\Date;
use EduTatarRuBot\Models\Client;
use EduTatarRuBot\Models\Diary;
use EduTatarRuBot\Models\Homework;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class GradesCommand extends SystemCommand
{
	/**
	 * @var string
	 */
	protected $name = 'grades';

	/**
	 * @var string
	 */
	protected $description = 'Оценки';

	/**
	 * @var string
	 */
	protected $usage = '/grades';

	/**
	 * @var string
	 */
	protected $version = '1.0.0';

	/**
	 * Command execute method
	 *
	 * @return \Longman\TelegramBot\Entities\ServerResponse
	 * @throws \Longman\TelegramBot\Exception\TelegramException
	 */
	public function execute()
	{
		$message = $this->getMessage();
		$chatId = $message->getChat()->getId();

		$client = new Client();
		$client->loadByChatId($message->getChat()->getId());

		if ($client->checkActivity()) {
			$diary = Diary::getLast($client->getValue('ID'));
			if (false != $diary) {
				$marks = $diary->getMarks();
				if (empty($marks)) {
					$text = '🚽 ' . "Оценки отстуствуют\r\n";
				} else {
					$text = '🚰 ' . "*Оценки*\r\n";
					foreach ($marks as $lesson => $marksList) {
						$text .= "_" . $lesson . ":_\r\n`" . implode(', ', $marksList) . "`\r\n\r\n";
					}
				}
				$data = [
					'chat_id' => $chatId,
					'text' => $text,
					'parse_mode' => 'markdown',
				];

				return \EduTatarRuBot\Request::sendMessage($data);
			}
		}

		return Request::emptyResponse();
	}
}
