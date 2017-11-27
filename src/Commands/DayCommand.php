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
class DayCommand extends SystemCommand
{
	/**
	 * @var string
	 */
	protected $name = 'day';

	/**
	 * @var string
	 */
	protected $description = 'Расписание на определенный день';

	/**
	 * @var string
	 */
	protected $usage = '/day DD.MM.YYYY';

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

		$date = trim($message->getText(true));

		$client = new Client();
		$client->loadByChatId($message->getChat()->getId());

		if ($client->checkActivity()) {
			$diary = Diary::getLast($client->getValue('ID'));
			if (false != $diary) {
				$ts = 0;
				if (preg_match('/[0-3][0-9]\.[0-1][0-9]\.[0-9]{4}/', $date)) {
					list($day, $month, $year) = explode('.', $date);
					$ts = mktime(0, 0, 0, $month, $day, $year);

				}
				if ($ts > 0) {
					$diaryDay = $diary->getDiaryDay((new \DateTime())->setTimestamp($ts));
					$lessons = $diaryDay->getLessons();
					$homework = $diaryDay->getHomework();

					if (empty($lessons)) {
						$text = " *Расписание  на " . $diaryDay->getDate()->format('d.m.Y') . "* отстуствует\r\n";
					} else {
						$text = " *Расписание  на " . $diaryDay->getDate()->format('d.m.Y') . "*\r\n";
						$index = 0;
						foreach ($lessons as $lesson) {
							$text .= ++$index . '. _' . $lesson . '_: ' . $homework[$lesson] . "\r\n";
						}
					}
				} else {
					$text = "Некорректная дата, правильно писать /day dd.mm.yyyy\r\nНапример /day " . Date::getNearestWorkDay()->format('d.m.Y');
				}
				$data = [
					'chat_id' => $chatId,
					'text' => $text,
					'parse_mode' => 'markdown',
				];

				return Request::sendMessage($data);
			}
		}

		return Request::emptyResponse();
	}
}
