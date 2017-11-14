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

use EduTatarRuBot\Helpers\Date;
use EduTatarRuBot\Models\Client;
use EduTatarRuBot\Models\Homework;
use Longman\TelegramBot\Commands\SystemCommand;
use Longman\TelegramBot\Request;

/**
 * Callback query command
 *
 * This command handles all callback queries sent via inline keyboard buttons.
 *
 * @see InlinekeyboardCommand.php
 */
class CallbackqueryCommand extends SystemCommand
{
	/**
	 * @var string
	 */
	protected $name = 'callbackquery';

	/**
	 * @var string
	 */
	protected $description = 'Reply to callback query';

	/**
	 * @var string
	 */
	protected $version = '1.1.1';

	/**
	 * Command execute method
	 *
	 * @return \Longman\TelegramBot\Entities\ServerResponse
	 * @throws \Longman\TelegramBot\Exception\TelegramException
	 */
	public function execute()
	{
		$callback_query = $this->getCallbackQuery();
		$callback_query_id = $callback_query->getId();
		$callback_data = $callback_query->getData();

		$data = [
			'callback_query_id' => $callback_query_id,
			'text' => '',
			'show_alert' => false,
			'cache_time' => 5,
		];

		if ($callback_data == 'showTomorrowHomework') {
			$client = new Client();
			$client->loadByChatId($callback_query->getMessage()->getChat()->getId());
			if ($client->getValue('STATE') == 'ACTIVE') {
				$homework = new Homework();
				$homework->sendExistedForDay($client, Date::getNearestWorkDay());
			}
		}

		return Request::answerCallbackQuery($data);
	}
}
