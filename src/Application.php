<?php
namespace EduTatarRuBot;


use EduTatarRuBot\Tasks\TaskInterface;

class Application
{
	public function __construct()
	{

	}

	public function run(TaskInterface $task)
	{
		$task->run();
	}
}