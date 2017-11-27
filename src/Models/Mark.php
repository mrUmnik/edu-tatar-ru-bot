<?php
namespace EduTatarRuBot\Models;

use EduTatarRuBot\Application;

class Mark extends Model
{
	public function checkForChanges(Client $client, DiaryDay $diaryDay)
	{
		$db = Application::getInstance()->getDB();
		$date = $diaryDay->getDate();
		$oldMarksData = $db->selectMany("SELECT * FROM " . self::getTableName() . " WHERE CLIENT_ID=:clientId AND DATE=:date", array(
			":clientId" => $client->getValue('ID'),
			":date" => $date->format('Y-m-d'),
		));
		$newMarks = $diaryDay->getMarks();

		if (empty($newMarks)) {
			return;
		}
		$clientCreatedDate = new \DateTime($client->getValue('CREATED_DATE'));
		if ($clientCreatedDate->getTimestamp() - 60 * 60 * 24 > $date->getTimestamp()) {
			return;// mark was received before start notifications
		}
		$isToday = ($date->format('d.m.Y') == date('d.m.Y'));

		$text = '';
		$messageType = 'MARK';

		$oldMarks = [];
		foreach ($oldMarksData as $item) {
			$oldMarks[$item['LESSON']] = $item['MARK'];
		}
		foreach ($newMarks as $lesson => $mark) {
			if ($oldMarks[$lesson] != $mark) {
				if (mb_strtolower($mark) == 'н') {
					$text .= '🍻 ' . $client->getValue('NAME') . ' ' .
						($client->getValue('GENDER') == 'F' ? 'прогуляла' : 'прогулял') .
						" предмет *" . $lesson . "*" . ($isToday ? "" : $date->format('d.m.Y')) . "\r\n";
				} else {
					$icons = array(
						'1' => '🍄',
						'2' => '🍄',
						'3' => '🐒',
						'4' => '🐭',
						'5' => '🦊',
					);
					$icon = $icons[$mark] ? $icons[$mark] : '🦀';
					if (mb_strlen($oldMarks[$lesson])) {
						$text .= $icon . " Изменилась оценка по предмету " . $lesson . ($isToday ? "" : " за " . $date->format('d.m.Y')) . ". Было " . $oldMarks[$lesson] . ", стало " . $mark . "\r\n";
					} else {
						$text .= $icon . " Получена оценка *" . $mark . "* по предмету " . $lesson . ($isToday ? "" : " за " . $date->format('d.m.Y')) . "\r\n";
					}
				}
			}
		}
		foreach ($oldMarksData as $item) {
			if ($newMarks[$item['LESSON']] != $item['MARK']) { // mark was changed
				$this->loadFromArray($item);
				$this->setValue('MARK', $newMarks[$item['LESSON']]);
			}
			unset($newMarks[$item['LESSON']]);
		}
		foreach ($newMarks as $lesson => $mark) { // mark was added
			$this->add(array(
				'CLIENT_ID' => $client->getValue('ID'),
				'DATE' => $date->format('Y-m-d'),
				'LESSON' => $lesson,
				'MARK' => $mark
			));
		}

		if (mb_strlen($text)) {
			$client->sendMessage($text, $messageType);
		}
	}


	protected static function getTableName()
	{
		return 'mark';
	}
}
