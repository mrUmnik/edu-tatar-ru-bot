<?php
include dirname(__DIR__) . '/src/Bootstrap.php';

$app = \EduTatarRuBot\Application::getInstance();
/*
$diary = new \EduTatarRuBot\Models\Diary();
$diary->load(6);
$client = new \EduTatarRuBot\Models\Client();
$client->load(1);

$diaryInfo = $diary->getDiaryDay(\EduTatarRuBot\Helpers\Date::getNearestWorkDay());
if (!empty($diaryInfo->getHomework())) {
    $homework = new \EduTatarRuBot\Models\Homework();
    $homework->checkForChanges($client, $diaryInfo);
}

$diaryInfo = $diary->getDiaryDay(new DateTime());
if (!empty($diaryInfo->getMarks())) {
    $mark = new \EduTatarRuBot\Models\Mark();
    $mark->checkForChanges($client, $diaryInfo);
}
*/

$client = new \EduTatarRuBot\Models\Client();
$allClients = $client::getAllActive();

foreach ($allClients as $client) {
	/**
	 * @var $client \EduTatarRuBot\Models\Client
	 */
	$client->showKeyboard('🍄🍄' . " *Ура товарищи!* " . '🍄🍄' . "\r\n" . '🎖' . " У бота появилась клавиатура, теперь можно быстро узнать расписание на ближайшие дни и посмотреть табель успеваемости.\r\n" . '🎖' . "Всем плясать.");
}
