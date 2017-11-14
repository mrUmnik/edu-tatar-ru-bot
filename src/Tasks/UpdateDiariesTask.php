<?php
namespace EduTatarRuBot\Tasks;

use EduTatarRuBot\Exceptions\WrongUsernameException;
use EduTatarRuBot\Helpers\Date;
use EduTatarRuBot\Models\Client;
use EduTatarRuBot\Models\Diary;
use EduTatarRuBot\Models\Homework;
use EduTatarRuBot\Models\Mark;

class UpdateDiariesTask extends Task
{
	public function run()
	{
		$activeClients = Client::getAllActive();
		foreach ($activeClients as $client) {
			$clientID = $client->getValue('ID');
			/**
			 * @var Client $client
			 */
			try {
				$httpClient = new \EduTatarRuBot\Client(
					$clientID,
					$client->getValue('LOGIN'),
					$client->getValue('PASSWORD')
				);
				$diaryXml = $httpClient->get('/user/diary.xml');
			} catch (WrongUsernameException $e) {
				$client->setValue('STATE', 'NEW');
				$client->sendMessage('Похоже вы изменили пароль от электронного дневника. Нужно заново указать данные для авторизации.', 'AUTHORIZATION');
				continue;
			}
			$diaryXmlHash = md5($diaryXml);
			if ($diary = Diary::getLast($clientID)) {
				if ($diary->getValue('CONTENT_HASH') == $diaryXmlHash) {
					$diary->setValue('UPDATED_TIME', date('Y-m-d H:i:s'));
				} else {
					if (simplexml_load_string($diaryXml)) {
						$newDiary = new Diary();
						$newDiary->add([
							'CLIENT_ID' => $clientID,
							'UPDATED_TIME' => date('Y-m-d H:i:s'),
							'CONTENT' => $diaryXml,
							'CONTENT_HASH' => $diaryXmlHash
						]);
						$diaryInfo = $newDiary->getDiaryDay(Date::getNearestWorkDay());
						if (!empty($diaryInfo->getHomework())) {
							$homework = new Homework();
							$homework->checkForChanges($client, $diaryInfo);
						}
						// today and previous week marks
						for ($day = 0; $day < 7; $day++) {
							$diaryInfo = $newDiary->getDiaryDay((new \DateTime())->setTimestamp(time() - 60 * 60 * 24 * $day));
							if (!empty($diaryInfo->getMarks())) {
								$mark = new Mark();
								$mark->checkForChanges($client, $diaryInfo);
							}
						}

						// check changes in homework and marks
						// compare $newDiary and $diary
					} else {
						// something wrong with xml...
					}
				}
			} else {
				$diary = new Diary();
				$diary->add([
					'CLIENT_ID' => $clientID,
					'UPDATED_TIME' => date('Y-m-d H:i:s'),
					'CONTENT' => $diaryXml,
					'CONTENT_HASH' => $diaryXmlHash
				]);
			}
		}
	}
}