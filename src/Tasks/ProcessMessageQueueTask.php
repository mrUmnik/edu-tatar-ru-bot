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
            $type = $message->getValue('TYPE');
            if ($type == 'HOMEWORK' || $type == 'CHANGED_HOMEWORK') {
                $diary = Diary::getLast($message->getValue('CLIENT_ID'));
                $diaryInfo = $diary->getDiaryDay(Date::getNearestWorkDay());
                if (!empty($diaryInfo->getHomework())) {
                    $clientId = $message->getValue('CLIENT_ID');
                    $client = new Client();
                    $client->load($clientId);
                    $homework = new Homework();
                    $message->deleteOldQueue($clientId, 'CHANGED_HOMEWORK');
                    $message->deleteOldQueue($clientId, 'HOMEWORK');
                    $homework->checkForChanges($client, $diaryInfo, true);
                    break;
                }
            } else {
                $message->send($message->getValue('CHAT_ID'), $message->getValue('MESSAGE'), $type);
                $message->setValue('REAL_SENT_TIME', date('Y-m-d H:i:s'));
            }
            if (time() - $startTime > 50) { // работает не больше 50 секунд
                break;
            }
        }

    }
}