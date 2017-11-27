<?php
namespace EduTatarRuBot\Models;

use EduTatarRuBot\Application;
use EduTatarRuBot\Exceptions\EduTatarRuBotException;

class Diary extends Model
{
	public static function getLast($clientId)
	{
		$db = Application::getInstance()->getDB();
		$data = $db->selectOne(
			'SELECT * FROM ' . self::getTableName() . ' WHERE CLIENT_ID=:client_id ORDER BY ID DESC LIMIT 1',
			array(':client_id' => $clientId)
		);
		if (!$data) {
			return false;
		}
		$result = new self();
		$result->loadFromArray($data);
		return $result;
	}

	protected static function getTableName()
	{
		return 'diary';
	}

	public function getDiaryDay(\DateTime $date)
	{
		$result = new DiaryDay($date);
		if (!$this->isLoaded()) {
			throw new EduTatarRuBotException('Dairy is not loaded');
		}
		$diaryXml = simplexml_load_string($this->getValue('CONTENT'));
		if (!$diaryXml) {
			throw new EduTatarRuBotException('Diary content is not a valid xml');
		}
		$day = $date->format('j');
		$month = \EduTatarRuBot\Helpers\Date::getRussianMonthName($date->format('n'));
		foreach ($diaryXml->page as $monthXml) {
			if ($monthXml['month'] == $month) {
				foreach ($monthXml as $dayXml) {
					if ($dayXml['date'] == $day) {
						$index = 0;

						foreach ($dayXml->classes->class as $class) {
							$class = trim((string)$class);
							if (mb_strlen($class)) {
								$result->add(
									$class,
									trim((string)($dayXml->tasks->task[$index])),
									trim((string)($dayXml->marks->marks[$index]))
								);
							}
							$index++;
						}
					}
				}
			}
		}
		return $result;
	}


	public function getMarks()
	{
		$result = [];
		if (!$this->isLoaded()) {
			throw new EduTatarRuBotException('Dairy is not loaded');
		}
		$diaryXml = simplexml_load_string($this->getValue('CONTENT'));
		if (!$diaryXml) {
			throw new EduTatarRuBotException('Diary content is not a valid xml');
		}
		foreach ($diaryXml->page as $monthXml) {
			foreach ($monthXml as $dayXml) {
				$index = 0;
				foreach ($dayXml->classes->class as $class) {
					$class = trim((string)$class);
					$mark = $dayXml->marks->marks[$index];
					if (mb_strlen($class) && mb_strlen($mark)) {
						$result[$class][] = $mark;
					}
					$index++;
				}

			}
		}
		return $result;
	}
}
