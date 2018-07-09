<?php
namespace minigameapi;

class Time {
	private $tick = 0;
	public function __construct(int $tick = 0,float $sec = 0, float $min = 0, float $hour = 0) {
		$this->setTime($sec, $min, $hour);
	}
	public function setTime(int $tick = 0,float $sec = 0, float $min = 0, float $hour = 0) : Time{
		$min += $hour * 60;
		$sec += $min * 60;
		$tick += $sec * 20;
		$this->tick = $tick;
		return $this;
	}
	public function addTime(int $tick = 0,float $sec = 0, float $min = 0, float $hour = 0) : Time{
		$min += $hour * 60;
		$sec += $min * 60;
		$tick += $sec * 20;
		$this->tick += $tick;
		return $this;
	}
	public function reduceTime(int $tick = 0,float $sec = 0, float $min = 0, float $hour = 0) : Time{
		$min += $hour * 60;
		$sec += $min * 60;
		$tick += $sec * 20;
		$this->tick -= $tick;
		return $this;
	}
	public function asSec() : float {
		return $this->tick / 20;
	}
	public function asMin() : float {
		return $this->asSec() / 60;
	}
	public function asHour() : float {
		return $this->asMin() / 60;
	}
	public function asTick() : int {
		return intval($this->tick);
	}
	public function format() : array {
		return explode(':',gmdate('H:i:s',$this->asSec()));
		//array(0 => hour,1 => min, 2 => sec)
	}
}
