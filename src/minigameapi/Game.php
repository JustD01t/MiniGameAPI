<?php
namespace minigameapi;

use pocketmine\level\Position;
use pocketmine\Player;

abstract class Game {
	private $name;
	private $runningTime;
	private $waitingRoom;
	private $waitingTime;
	private $teams = [];
	public function __construct(string $name,?Time $runningTime = new Time(0,5),?Position $waitingRoom, ?Time $waitingTime = new Time(30)) {
		$this->name = $name;
		$this->runningTime = $runningTime;
		$this->waitingRoom = $waitingRoom;
		$this->waitingTime = $waitingTime;
	}
	public function broadcastMessage(string $message){
		foreach($this->teams as $team) {
			$team->broadcastMessage($message);
		}
		return;
	}
	public function addTeam(Team $team) {
		$this->teams[] = $team
		return;
	}
	public function removeTeam(string $teamName) {
		foreach($this->teams as $key => $team){
			if($team->getName() == $teamName){
				unset($this->teams[$key]);
			}
		}
		$this->teams = array_values($this->teams);
		return;
	}
	public function getTeam(string $teamName) : ?Team{
		foreach($this->getTeams() as $team) {
			if($teamName == $team->getName()) return $team;
		}
	}
	public function removePlayer(Player $player) {
		foreach($this->getTeams() as $team) {
			$team->removePlayer($player);
		}
	}
		
	public function getTeams() : array{
		return $this->teams;
	}
	public function getPlayers() : array{
		$result = [];
		foreach($this->getTeams() as $team) {
			foreach($team->getPlayers() as $player) {
				$result[] = $player;
			}
		}
		return $result;
	}
	public function getName() : string{
		return $this->name;
	}
}
