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
	private $gameManager;
	private $waitingPlayers;
	private $started;
	public function __construct(string $name,?Time $runningTime = new Time(0,5),?Position $waitingRoom, ?Time $waitingTime = new Time(30)) {
		$this->name = $name;
		$this->runningTime = $runningTime;
		$this->waitingRoom = $waitingRoom;
		$this->waitingTime = $waitingTime;
	}
	public function addWaitingPlayer(Player $player) : bool{
		if($this->isStarted()) return false;
		$this->getGameManager()->removePlayer($player);
		$this->addWaitingPlayer($player);
		$this->start();
		return true;
	}
	public function broadcastMessage(string $message){
		foreach($this->getTeams() as $team) {
			$team->broadcastMessage($message);
		}
		return;
	}
	public function addTeam(Team $team) {
		$this->teams[] = $team
		return;
	}
	public function removeTeam(string $teamName) {
		foreach($this->getTeams() as $key => $team){
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
		foreach ($this->getPlayers() as $key => $pl) {
			//$pl instanceof Player;
			if($player->getName() == $pl->getName()) {
				unset($this->waitingPlayers[$key]);
			}
		}
		$this->waitingPlayers = array_values($this->waitingPlayers);
		foreach($this->getTeams() as $team) {
			$team->removePlayer($player);
		}
	}
		
	public function getTeams() : array{
		return $this->teams;
	}
	public function getPlayers() : array{
		if($this->isStarted()) return $this->waitingPlayers;
		$result = [];
		foreach($this->getTeams() as $team) {
			$result = array_merge($result,$team->getPlayers());
		}
		return $result;
	}
	public function getName() : string{
		return $this->name;
	}
	public function getGameManager() : GameManager{
		return $this->gameManager;
	}
	public function setGameManager(GameManager $gameManager){
		$this->gameManager = $gameManager();
	}
	public function isStarted() : bool {
		return $this->started;
	}
	public function start(); //TODO
}
