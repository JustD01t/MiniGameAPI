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
	private $runningTime;
	private $waitingRoom;
	private $waitingTime;
	private $teams = [];
	private $gameManager;
	private $waitingPlayers;
	private $started;
	private $plugin;
	public function __construct(Plugin $plugin, string $name, Time $runningTime = new Time(0,5),?Position $waitingRoom, Time $waitingTime = new Time(30)) {
		$this->plugin = $plugin;
		$this->name = $name;
		$this->runningTime = $runningTime;
		$this->waitingRoom = $waitingRoom;
		$this->waitingTime = $waitingTime;
	}
	public function addWaitingPlayer(Player $player) : bool{
		if($this->isStarted()) return false;
		$this->getGameManager()->removePlayer($player);
		$this->addWaitingPlayer($player);
		foreach($this->getTeams() as $team) {
			$minPlayers += $team->getMinPlayers();
			$maxPlayers += $team->getMaxPlayers();
		}
		return true;
	}
	public function broadcastMessage(string $message){
		foreach($this->getTeams() as $team) {
			$team->broadcastMessage($message);
		}
		return;
	}
	public function submitTeam(Team $team) {
		$team->setGame($this);
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
	public function onStart();
	public function assignPlayers(array $players) {
		foreach($this->getWaitingPlayers() as $player) {
			$team = new Team($player->getName(), 1,1);
			$team->addPlayer($player);
			$this->submitTeam($team);
		}
	}
	public function end(int $endCode) {
		switch($endCode) {
			//TODO	
				
		}
	}
	public function start() : bool{
		$this->assignPlayers($this->getPlayers());
		if(!isStartable()) {
			$this->end(self::END_STARTING_ERROR);
			return false;
		}
		foreach($this->getTeams() as $team) {
			$team->spawn();
		}
		$this->onStart();
	}
}
