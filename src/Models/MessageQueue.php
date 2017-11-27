<?php
namespace EduTatarRuBot\Models;

use EduTatarRuBot\Application;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

class MessageQueue extends Model
{
    public static function getAllActive()
    {
        $db = Application::getInstance()->getDB();
        $items = $db->selectMany(
            'SELECT ' . self::getTableName() . '.*, CHAT_ID FROM ' . self::getTableName() . ' LEFT JOIN client ON (client.ID=' . self::getTableName() . '.CLIENT_ID) WHERE REAL_SENT_TIME="0000-00-00 00:00:00" AND `TIME` < NOW() LIMIT 50'
        );
        $result = [];
        foreach ($items as $arItem) {
            $item = new self();
            $item->loadFromArray($arItem);
            $result[] = $item;
        }
        return $result;
    }

	protected static function getTableName()
    {
        return 'message_queue';
    }

    public function send($chatId, $message, $type)
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $message,
        ];
        switch ($type) {
            case "CHANGED_HOMEWORK":
                $inline_keyboard = new InlineKeyboard([
                    ['text' => 'Покажи полное расписание', 'callback_data' => 'showTomorrowHomework'],
                ]);
                $params['parse_mode'] = 'Markdown';
                $params['reply_markup'] = $inline_keyboard;
                break;
            case "WITH_KEYBOARD":
                $params['parse_mode'] = 'Markdown';
                $params['reply_markup'] = self::getKeyboard();
                break;
            case "EXISTED_HOMEWORK":
            case "HOMEWORK":
            case "MARK":
            case "AUTHORIZATION":
                $params['parse_mode'] = 'Markdown';
                break;
        }
        Request::initialize(Application::getInstance()->getTelegramBot());
        Request::sendMessage($params);

        if ($this->isLoaded()) {
            $this->setValue('REAL_SENT_TIME', date('Y-m-d H:i:s'));
        }
    }

    public static function getKeyboard()
    {
        $buttons = self::getKeyboardButtons();
        $result = new Keyboard(...$buttons);
        $result->setResizeKeyboard(true)
            ->setOneTimeKeyboard(false)
            ->setSelective(false);

        return $result;
    }

    public static function getKeyboardButtons()
    {
        return [
            ['tomorrow' => '📆' . ' Расписание на завтра'],
            ['aftertomorrow' => '🗓' . ' Расписание на послезавтра'],
            ['grades' => '📈' . ' Табель успеваемости']
        ];
    }

    public function deleteOldQueue($clientId, $type)
    {
        $statement = Application::getInstance()->getDB()->getConnection()->prepare("DELETE FROM " . static::getTableName() . " WHERE CLIENT_ID=:clientId AND TYPE=:type");
        $statement->execute([':type' => $type, ':clientId' => $clientId]);
    }
}
