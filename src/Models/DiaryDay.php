<?php
namespace EduTatarRuBot\Models;

class DiaryDay
{
	protected $data = [];
	protected $lessons = [];
	protected $homework = [];
	protected $marks = [];
	protected $date;

	public function __construct(\DateTime $date)
	{
		$this->date = $date;
	}

	public function add($lesson, $homework, $mark)
	{
		if (!mb_strlen($lesson)) {
			return;
		}
		$this->data[] = [
			'NAME' => $lesson,
			'HOMEWORK' => $homework,
			'MARK' => $mark
		];
		if (mb_strlen($homework)) {
			$this->homework[$lesson] = (mb_strlen($this->homework[$lesson]) ? ', ' : '') . $homework;
		}
		if (mb_strlen($mark)) {
			$this->marks[$lesson] = (mb_strlen($this->marks[$lesson]) ? ', ' : '') . $mark;
		}
		$this->lessons[] = $lesson;
	}

	public function getMarks()
	{
		return $this->marks;
	}

	public function getHomework()
	{
		return $this->homework;
	}

	public function getLessons()
	{
		return $this->lessons;
	}

	public function getDate()
	{
		return $this->date;
	}

	public function isEmpty()
	{
		return !count($this->data);
	}
}
