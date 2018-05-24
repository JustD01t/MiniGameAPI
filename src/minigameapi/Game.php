<?php
namespace minigameapi;

use pocketmine\level\Position;

abstract class Game {
	const NAME = 'abstract';
	private $title;
	private $runningTime;
	private $waitingRoom;
	private $waitingTime;
	private $teams = [];
	public function __construct(string $title = self::NAME,?Time $runningTime = new Time(0,5),?Position $waitingRoom, ?Time $waitingTime = new Time(30)) {
		$this->title = $title;
		$this->runningTime = $runningTime;
		$this->waitingRoom = $waitingRoom;
		$this->waitingTime = $waitingTime;
	}
	public function addTeam(Team $team) {
		$this->teams[] = $team
		return true;
	}
}