<?php
namespace EduTatarRuBot\Tasks;

use EduTatarRuBot\Application;

abstract class Task
{
	/**
	 * @var Application $app
	 */
	protected $app;

	public function setApplication(Application $app)
	{
		$this->app = $app;
	}

	public function getApplication()
	{
		return $this->app;
	}

	abstract public function run();
}
