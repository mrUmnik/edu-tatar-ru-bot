<?php
include dirname(__DIR__) . '/src/Bootstrap.php';

$app = new \EduTatarRuBot\Application();

$app->run(new \EduTatarRuBot\Tasks\CheckHomework());