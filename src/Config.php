<?php
namespace EduTatarRuBot;

use EduTatarRuBot\Exceptions\ConfigException;

class Config
{
	protected $config;

	public function __construct($filename = false)
	{
		if (false === $filename) {
			$filename = ROOT_DIR . DIRECTORY_SEPARATOR . 'config.json';
		}
		if (!file_exists($filename)) {
			throw new ConfigException('Config file ' . $filename . ' not found');
		}
		if (!is_readable($filename)) {
			throw new ConfigException('Config file ' . $filename . ' is not readable');
		}
		$json = file_get_contents($filename);

		$this->config = \json_decode($json, true);
		if (null === $this->config) {
			throw new ConfigException('Config file ' . $filename . ' has wrong format. Json required.');
		}
	}

	public function get($param)
	{
		if (!mb_strlen($param)) {
			throw new ConfigException('Empty param value');
		}
		$arParam = explode(':', $param);

		$item =& $this->config;
		$array =& $item;
		$key = $param[0];
		foreach ($arParam as $key) {
			if (!array_key_exists($key, $item)) {
				throw new ConfigException('Param ' . $param . ' is undefined');
			}
			$array =& $item;
			$item =& $item[$key];
		}
		return $array[$key];
	}
}