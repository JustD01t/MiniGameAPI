<?php
namespace minigameapi;

use pocketmine\level\Position;
use pocketmine\Player;

abstract class Game {
	const END_NORMAL = 0;
	const END_NO_PLAYERS = 1;
	const END_KILLED_GAME = 3;
	const END_STARTING_ERROR = 4;
	private $name;
	private $neededPlayers;
	private $runningTime;
	private $waitingRoom;
	private $waitingTime;
	private $teams = [];
	private $gameManager;
	private $waitingPlayers;
	private $started = false;
	private $plugin;
	private $remainingWaitTime;
	private $remainingRunTime;
	public function __construct(Plugin $plugin, string $name,int $neededPlayers = 1,Time $runningTime = new Time(0,5), Time $waitingTime = new Time(30), ?Position $waitingRoom,) {
		$this->plugin = $plugin;
		$this->name = $name;
		$this->neededPlayers = $neededPlayers;
		$this->runningTime = $runningTime;
		$this->waitingRoom = $waitingRoom;
		$this->waitingTime = $waitingTime;
	}
	public function getNeededPlayers() : int{
		return $this->neededPlayers;
	}
	public function getRemainingWaitTime() : ?Time {
		return isset($this->remainingWaitTime) ? $this->remainingWaitTime : null;
	}
	public function getWaitingTime() : Time {
		return $this->waitingTime;
	}
	public function getRemainingRunTime() : ?Time {
		return return isset($this->remainingRunTime) ? $this->remainingRunTime : null;
	}
	public function getRunTime() : Time {
		return $this->runningTime;
	}
	public function wait() {
		$this->remainingWaitTime = $this->getWaitingTime();
	}
	public function isWaiting() : bool {
		return is_null($this->getRemainingWaitTime()) ? false : true;
	}
	public function onWait();
	public function resetWaitingPlayers(){
		$this->waitingPlayers = [];
	}
	public function resetTeams() {
		$this->teams = [];
	}
	public function reset() {
		$this->resetWaitingPlayers();
		$this->resetTeams();
	}
	public function addWaitingPlayer(Player $player) : bool{
		if($this->isStarted()) return false;
		$this->getGameManager()->removePlayer($player);
		$this->waitingPlayers[] = $player;
		if(!is_null($this->getWaitingRoom())) $player->teleport($this->getWaitingRoom());
		return true;
	}
	public function getWaitingRoom() : ?Position {
		return $this->waitingRoom;
	}
			    
	public function broadcastMessage(string $message){
		foreach($this->getTeams() as $team) {
			$team->broadcastMessage($message);
		}
		return;
	}
	public function submitTeam(Team $team) {
		$this->removeTeam($team->getName());
		$team->setGame($this);
		$this->teams[] = $team;
		return;
	}
	public function removeTeam(string $teamName) {
		foreach($this->getTeams() as $key => $team){
			if($team->getName() == $teamName){
				unset($this->teams[$key]);
			}
		}
		$this->teams = array_values($this->teams);
		if(count($this->getTeams()) == 0 and $this->isStarted()) $this->end(self::END_NO_PLAYERS);
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
	public function getPlugin() : Plugin{
		return $this->plugin;
	}
	public function isStartable() : bool{
		foreach($this->getTeams() as $team) {
			if(count($team->getPlayers()) < $team->getMinPlayers()) return false;
		}
		return true;
	}
	public function onEnd();
	public function onStart();
	public function assignPlayers(array $players) {
		foreach($this->getWaitingPlayers() as $player) {
			$team = new Team($player->getName(), 1,1);
			$team->addPlayer($player);
			$this->submitTeam($team);
		
	}
	public function end(int $endCode) {
		switch($endCode) {
			case self::END_NORMAL:
			case self::END_NO_PLAYERS:
			case self::END_KILLED_GAME:
			case self::END_STARTING_ERROR:
				$this->onEnd();
				$this->reset();
				break;
		}
	}
	public function start() : bool{
		$this->assignPlayers($this->getPlayers());
		if(!$this->isStartable()) {
			$this->end(self::END_STARTING_ERROR);
			return false;
		}
		foreach($this->getTeams() as $team) {
			$team->spawn();
		}
		$this->onStart();
	}
}
