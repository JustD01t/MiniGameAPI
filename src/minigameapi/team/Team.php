<?php
namespace minigameapi\team;

use pocketmine\Player;
use pocketmine\Server;

class Team {
	private $maxPlayers = '0';
	private $minPlayers = '0';
	private $teamName;
	private $players = [];
	public function getMaxPlayers() : int{
		return $this->maxPlayers;
	}
	public function setMaxPlayers(int $maxPlayers) : bool{
		if(count($this->players) > $maxPlayers) {
			
		}
	}
	public function __construct(string $teamName, Player $firstPlayer, int $maxPlayers = 0, int $minPlayers = 0) {
		$this->maxPlayers = $maxPlayers;
		$this->minPlayers = $minPlayers;
		$this->teamName = $teamName;
		$this->players[] = $firstPlayer;
	}
	public function addPlayer(Player $player) : bool{
		if(count($this->players) == $this->maxPlayers) {
			Server::getInstance()->getLogger()->error('adding new player on full team');
			return false;
		}
		$this->players[] = $player;
		//TODO remove exists players
		return true;
	}
	public function removeExistsPlayers(Player $player) {
		foreach ($this->players as $key => $pl) {
			$pl instanceof Player;
			if($player->getName() == $pl->getName()) {
				unset($this->players[$key]);
				break;
			}
		}
		$this->players = array_values($this->players);
	}
}