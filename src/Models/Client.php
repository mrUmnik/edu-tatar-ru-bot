<?php
namespace EduTatarRuBot\Models;

use EduTatarRuBot\Application;
use EduTatarRuBot\Exceptions\BlockedUserException;
use EduTatarRuBot\Exceptions\EduTatarRuBotException;
use EduTatarRuBot\Exceptions\WrongUsernameException;

class Client extends Model
{
    public static function getAllActive()
    {
        $db = Application::getInstance()->getDB();
        $activeClients = $db->selectMany(
            'SELECT * FROM ' . self::getTableName() . ' WHERE STATE="ACTIVE"'
        );
        $result = [];
        foreach ($activeClients as $arClient) {
            $item = new self();
            $item->loadFromArray($arClient);
            $result[] = $item;
        }
        return $result;
    }

    protected static function getTableName()
    {
        return 'client';
    }

    public function addClientProcess($chatId, $clientMessage = '')
    {
        $clientMessage = trim($clientMessage);
        $newClient = false;
        try {
            $this->loadByChatId($chatId);
        } catch (EduTatarRuBotException $e) {
            $newClient = true;
        }
        if ($newClient) {
            $this->add([
                'CHAT_ID' => $chatId,
                'STATE' => 'NEW',
                'NOTIFY_TIME_START' => 18 * 60,
                'NOTIFY_TIME_END' => 22 * 60,
                'CREATED_DATE' => date('Y-m-d H:i:s')
            ]);
        }

        $text = false;
        $currentState = $this->getValue('STATE');
        $type = 'AUTHORIZATION';
        switch ($currentState) {
            case "NEW":
                $text = 'Чтобы я мог сообщать об изменениях в электронном дневнике, отправьте мне логин от https://edu.tatar.ru/';
                $this->setValue('STATE', 'WAIT_FOR_LOGIN');
                break;
            case "WAIT_FOR_LOGIN":
                if (mb_strlen($clientMessage)) {
                    $this->setValue('LOGIN', $clientMessage);
                    $this->setValue('STATE', 'WAIT_FOR_PASSWORD');
                    $text = 'Отправьте пароль от электронного дневника для логина ' . $this->getValue('LOGIN');
                } else {
                    $text = "Это какой-то неправильный логин, попробуйте еще раз";
                }
                break;
            case "WAIT_FOR_PASSWORD":
                if (mb_strlen($clientMessage)) {
                    $this->setValue('PASSWORD', $clientMessage);
                    try {
                        $this->updateAnketaData();
                        $this->setValue('STATE', 'ACTIVE');
                        $text = "Поздравляю, все получилось!\r\n_Предыдущее сообщение с паролем в целях безопасности рекомендуется удалить_";
                        $type = 'WITH_KEYBOARD';
                    } catch (WrongUsernameException $e) {
                        $text = "Неправильный логин или пароль. Попробуйте еще раз.\r\nОтправьте мне логин от электронного дневника.";
                        $this->setValue('STATE', 'WAIT_FOR_LOGIN');
                    } catch (BlockedUserException $e) {
                        $text = 'Пользователь заблокирован. Обычно его отпускает минут через 20. Попробуйте еще раз позже.';
                        $this->setValue('STATE', 'NEW');
                    } catch (EduTatarRuBotException $e) {
                        $text = 'Какие-то проблемы с сетью. Придется подождать. Попробуйте еще раз позже.';
                        $this->setValue('STATE', 'NEW');
                    }
                } else {
                    $text = "Это какой-то неправильный пароль, попробуйте еще раз";
                }
                break;
            case "ACTIVE":
	            return false;
        }

        if ($text) {
            $this->sendMessage($text, $type);
        }
	    return true;
    }

    public function loadByChatId($chatId)
    {
        $data = Application::getInstance()->getDB()->selectOne("SELECT * FROM " . static::getTableName() . " WHERE CHAT_ID=:ID", array('ID' => intval($chatId)));
        if (false === $data) {
            Throw new EduTatarRuBotException('Client with #' . $chatId . ' not found');
        }
        $this->loadFromArray($data);
    }

    public function updateAnketaData()
    {
        $httpClient = new \EduTatarRuBot\Client($this->getValue('ID'), $this->getValue('LOGIN'), $this->getValue('PASSWORD'));
        $html = $httpClient->get('/user/anketa/index');
        preg_match('/<table class="tableEx">(.*?)<\/table>/s', $html, $table);
        if (strlen($table[0])) {
            $tableXml = simplexml_load_string($table[0]);
            $params = [];
            foreach ($tableXml->tr as $tr) {
                $params[trim((string)($tr->td[0]))] = trim(strip_tags((string)($tr->td[1]->asXML())));
            }
            print_r($params);
            $arUpdate = [];
            if (strlen($params['Имя:'])) {
                list($surname, $name, $pathronymic) = explode(' ', $params['Имя:']);
                $arUpdate['SURNAME'] = $surname;
                $arUpdate['NAME'] = $name;
                $arUpdate['PATHRONYMIC'] = $pathronymic;
            }
            if (strlen($params['Пол:'])) {
                $arUpdate['GENDER'] = $params['Пол:'] == 'женский' ? 'F' : 'M';
            }
            if (!empty($arUpdate)) {
                $this->setValues($arUpdate);
            }
        }
    }

    public function sendMessage($message, $type)
    {
        $messageQueue = new MessageQueue();
        $time = date('H') * 60 + date('i');
        $startTime = $this->getValue('NOTIFY_TIME_START');
        $endTime = $this->getValue('NOTIFY_TIME_END');
        if (
            in_array($type, array('CHANGED_HOMEWORK', 'HOMEWORK'))
            &&
            ($startTime > $time || $endTime < $time)
        ) {
            if ($startTime > $time) { // later today
                $ts = time();
            }
            if ($endTime < $time) { // tomorrow
                $ts = time() + 60 * 60 * 24;
            }
            $sendDate = mktime(floor($startTime / 60), $startTime % 60, 0, date('m', $ts), date('j', $ts), date('Y', $ts));
            $messageQueue->deleteOldQueue($this->getValue('ID'), 'CHANGED_HOMEWORK');
            $messageQueue->deleteOldQueue($this->getValue('ID'), 'HOMEWORK');
            $messageQueue->add(array(
                'CLIENT_ID' => $this->getValue('ID'),
                'TYPE' => $type,
                'MESSAGE' => $message,
                'TIME' => date('Y-m-d H:i:s', $sendDate)
            ));
        } else {
            $messageQueue->send($this->getValue('CHAT_ID'), $message, $type);
        }
    }

    public function checkActivity()
	{
        $currentState = $this->getValue('STATE');
        if ($currentState == "ACTIVE") {
            return true;
		}
        $this->sendMessage("Сначала необходимо авторизоваться. Используйте команду /start", 'AUTHORIZATION');
        return false;
	}

    public function showKeyboard($messageToClient = '')
    {
        if ($this->getValue('STATE') == 'ACTIVE') {
            $this->sendMessage($messageToClient, 'WITH_KEYBOARD');
        }
	}
}
