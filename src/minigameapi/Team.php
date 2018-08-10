<?php
namespace minigameapi;

use minigameapi\event\MiniGamePlayerRemoveEvent;
use pocketmine\level\Position;
use pocketmine\Player;

class Team {
	private $minPlayers = 0;
	private $name;
	private $players = [];
	private $game;
	private $spawn;
	private $points = 0;
	public function __construct(Game $game, string $teamName, int $minPlayers = 0,Position $spawn = null) {
		$this->game = $game;
		$this->name = $teamName;
		$this->setMinPlayers($minPlayers);
		$this->setSpawn($spawn);
	}
	public function getPoints() : float {
	    return $this->points;
    }
    public function setPoints(float $points) : void {
	    $this->points = $points;
    }
    public function addPoints(float $points) : void {
	    $this->points += $points;
    }
    public function reducePoints(float $points) : void {
	    $this->points -= $points;
    }
	public function getName() : string {
	return $this->name;
  }
  public function getGame() : ?Game{
	return $this->game;
  }
	public function getMinPlayers() : int{
		return $this->minPlayers;
	}
	public function setMinPlayers(int $minPlayers) {
		$this->minPlayers = $minPlayers;
		return;
	}
	public function getSpawn() : ?Position{
		return $this->spawn;
	}
	public function setSpawn(?Position $spawn) {
		$this->spawn = $spawn;
	}
	public function addPlayer(Player $player) : bool{
		if (!$this->getGame()->isInGame($player)) return false;
		$this->getGame()->removePlayer($player);
		$this->players[] = $player;
		return true;
	}
	public function getPlayers() : array {
		return $this->players;
	}
	public function removePlayer(Player $player) : bool {
		foreach ($this->players as $key => $pl) {
			if($player->getName() == $pl->getName()) {
				$ev = new MiniGamePlayerRemoveEvent($this->getGame(),$player);
				$this->getGame()->getMiniGameApi()->getServer()->getPluginManager()->callEvent($ev);
				unset($this->players[$key]);
				$this->players = array_values($this->players);
				if(count($this->getPlayers()) == 0) $this->getGame()->removeTeam($this->getName());
				return true;
			}
		}
		return false;
	}
	public function broadcastMessage(string $message) {
		foreach($this->getPlayers() as $player) {
			//$player instanceof Player;
			$player->sendMessage($message);
		}
		return;
	}
	public function teleport(Position $position){
		foreach($this->getPlayers() as $player) {
			$player->teleport($position);
		}
	}
	public function spawn(){
		if(!is_null($this->getSpawn())) $this->teleport($this->getSpawn());
	}
	public function isInTeam(Player $player) : bool {
		foreach ($this->getPlayers() as $pl) {
			if($pl->getName() == $player->getName()) return true;
		}
		return false;
	}
}
