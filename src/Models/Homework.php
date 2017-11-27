<?php
namespace EduTatarRuBot\Models;

use EduTatarRuBot\Application;

class Homework extends Model
{
    public function checkForChanges(Client $client, DiaryDay $diaryDay, $forceFullHomework = false)
    {
        $db = Application::getInstance()->getDB();
        $date = $diaryDay->getDate();
        $oldHomeworkData = $db->selectMany("SELECT * FROM " . self::getTableName() . " WHERE CLIENT_ID=:clientId AND DATE=:date", array(
            ":clientId" => $client->getValue('ID'),
            ":date" => $date->format('Y-m-d'),
        ));
        $newHomework = $diaryDay->getHomework();

        if (empty($newHomework)) {
            return;
        }
        $text = '';

        if (empty($oldHomeworkData) || $forceFullHomework) {
            $text = '🐔' . " *Домашнее задание на " . $date->format('d.m.Y') . "*\r\n";
            $index = 0;
            foreach ($newHomework as $lesson => $homework) {
                $text .= ++$index . '. _' . $lesson . '_: ' . $homework . "\r\n";
            }
        } else {
            $oldHomework = [];
            foreach ($oldHomeworkData as $item) {
                $oldHomework[$item['LESSON']] = $item['HOMEWORK'];
            }
            foreach ($newHomework as $lesson => $homework) {
                if ($oldHomework[$lesson] != $homework) {
                    $text .= '🦄 ' . (mb_strlen($oldHomework[$lesson]) ? 'Изменилось' : 'Появилось');
                    $text .= " домашнее задание на " . $date->format('d.m.Y') . " по предмету *" . $lesson . "*:\r\n" . $homework . "\r\n";
                    $text .= (mb_strlen($oldHomework[$lesson]) ? "(было: " . $oldHomework[$lesson] . ")" : "") . "\r\n";
                }
            }
        }
        foreach ($oldHomeworkData as $item) {
            if ($newHomework[$item['LESSON']] != $item['HOMEWORK']) { // homework was changed
                $this->loadFromArray($item);
                $this->setValue('HOMEWORK', $newHomework[$item['LESSON']]);
            }
            unset($newHomework[$item['LESSON']]);
        }
        foreach ($newHomework as $lesson => $homework) { // homework was added
            $this->add(array(
                'CLIENT_ID' => $client->getValue('ID'),
                'DATE' => $date->format('Y-m-d'),
                'LESSON' => $lesson,
                'HOMEWORK' => $homework
            ));
        }
        if (mb_strlen($text)) {
            $client->sendMessage($text, 'HOMEWORK');
        }
    }

    protected static function getTableName()
    {
        return 'homework';
    }

    public function sendExistedForDay(Client $client, \DateTime $date)
    {
        $homeworkData = Application::getInstance()->getDB()->selectMany("SELECT * FROM " . self::getTableName() . " WHERE CLIENT_ID=:clientId AND DATE=:date", array(
            ":clientId" => $client->getValue('ID'),
            ":date" => $date->format('Y-m-d'),
        ));
        $messageType = 'EXISTED_HOMEWORK';
        $text = '';
        if (!empty($homeworkData)) {
            $text = '🐧' . " *Домашнее задание на " . $date->format('d.m.Y') . "*\r\n";
            $index = 0;
            foreach ($homeworkData as $arHomework) {
                $text .= ++$index . '. _' . $arHomework['LESSON'] . '_: ' . $arHomework['HOMEWORK'] . "\r\n";
            }
        } else {
            $text = '🦃' . " *Домашнее задание на " . $date->format('d.m.Y') . "* отстуствует\r\n";
        }
        if (mb_strlen($text)) {
            $client->sendMessage($text, $messageType);
        }
    }
}
