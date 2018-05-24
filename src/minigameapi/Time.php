<?php
namespace minigameapi;

class Time {
	private $sec = 0;
	public function __construct(float $sec = 0, float $min = 0, float $hour = 0) {
		$this->setTime($sec, $min, $hour);
	}
	public function setTime(float $sec = 0, float $min = 0, float $hour = 0) {
		//TODO support setTime as Tick 시간없어서 못합니다 해주시면 감사하겠습니다.
		$min += $hour * 60;
		$sec += $min * 60;
		$this->sec = $sec;
	}
	public function addTime(float $sec = 0, float $min = 0, float $hour = 0) {
		$min += $hour * 60;
		$sec += $min * 60;
		$this->sec += $sec;
	}
	public function reduceTime(float $sec = 0, float $min = 0, float $hour = 0) {
		$min += $hour * 60;
		$sec += $min * 60;
		$this->sec -= $sec;
	}
	public function asSec() : float {
		return $this->sec;
	}
	public function asMin() : float {
		return $this->asSec() / 60;
	}
	public function asHour() : float {
		return $this->asMin() / 60;
	}
	public function asTick() : float {
		return $this->asSec() * 20;
	}
}