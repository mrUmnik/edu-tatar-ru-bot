<?php
namespace EduTatarRuBot\Tasks;

use EduTatarRuBot\Application;

abstract class Task
{
	/**
	 * @var Application $app
	 */
	abstract public function run();
}
