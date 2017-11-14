<?php
include dirname(__DIR__) . '/src/Bootstrap.php';

$app = \EduTatarRuBot\Application::getInstance();
$app->run(new \EduTatarRuBot\Tasks\ProcessMessageQueueTask());