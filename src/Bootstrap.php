<?php
namespace EduTatarRuBot;

define('ROOT_DIR', dirname(__DIR__));

$composer = require_once(ROOT_DIR . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
$composer->addPsr4('EduTatarRuBot\\', ROOT_DIR . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);