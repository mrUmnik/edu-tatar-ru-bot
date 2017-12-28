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
class TodayCommand extends SystemCommand
{
	/**
	 * @var string
	 */
	protected $name = 'today';

	/**
	 * @var string
	 */
	protected $description = 'Расписание на сегодня';

	/**
	 * @var string
	 */
	protected $usage = '/today';

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
				$diaryDay = $diary->getDiaryDay(new \DateTime());
				$lessons = $diaryDay->getLessons();
				$homework = $diaryDay->getHomework();

				if (empty($lessons)) {
					$text = '👀' . " *Расписание  на " . $diaryDay->getDate()->format('d.m.Y') . "* отстуствует\r\n";
				} else {
					$text = '👀' . " *Расписание  на " . $diaryDay->getDate()->format('d.m.Y') . "*\r\n";
					$index = 0;
					foreach ($lessons as $lesson) {
						$lessonHomework = $homework[$lesson];
						if (!mb_strlen($lessonHomework)) {
							$lessonHomework = '_не задано_';
						}
						$text .= ++$index . '. _' . $lesson . '_: ' . $lessonHomework . "\r\n";
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
