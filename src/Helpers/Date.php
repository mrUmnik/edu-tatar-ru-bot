<?php
namespace EduTatarRuBot\Helpers;


class Date
{
	/**
	 * Название месяца на русском по номеру месяца
	 * @param $monthNum
	 * @return string
	 */
	public static function getRussianMonthName($monthNum)
	{
		$months = [
			1 => 'Январь',
			2 => 'Февраль',
			3 => 'Март',
			4 => 'Апрель',
			5 => 'Май',
			6 => 'Июнь',
			7 => 'Июль',
			8 => 'Август',
			9 => 'Сентябрь',
			10 => 'Октябрь',
			11 => 'Ноябрь',
			12 => 'Декабрь',
		];
		return $months[$monthNum];
	}

	/**
	 * Ближайший рабочий день
	 * @return \DateTime
	 */
	public static function getNearestWorkDay()
	{
		$timestamp = time() + 60 * 60 * 24;
		if (date('N', $timestamp) == '6') {
			$timestamp += 60 * 60 * 24 * 2; // для субботы добавляется 2 дня
		}
		if (date('N', $timestamp) == '7') {
			$timestamp += 60 * 60 * 24; // для воскресенья добавляется 1 день
		}
		$result = new \DateTime();
		$result->setTimestamp($timestamp);
		return $result;
	}
}
