<?php
namespace minigameapi;

use pocketmine\Player;
use pocketmine\Server;

class Team {
	private $maxPlayers = -1;
	private $minPlayers = 0;
	private $teamName;
	private $players = [];
	private $game;
	private $spawn;
	public function __construct(string $teamName,Player $firstPlayer, int $maxPlayers = -1, int $minPlayers = 1,?Position $spawn) {
		$this->setMaxPlayers($maxPlayers);
		$this->setMinPlayers($minPlayers);
		$this->setTeamName($teamName);
		$this->addPlayer($firstPlayer);
	}
	public function getMaxPlayers() : int{
		return $this->maxPlayers;
	}
	public function setGame(Game $game) {
		foreach($this->getPlayers() as $player) {
			$game->getGameManager()->removePlayer($player);
		}
		$this->game = $game;
	}
	public function getGame() : ?Game{
		return $this->game;
	}
	public function setMaxPlayers(int $maxPlayers) : bool{
		if(count($this->players) > $maxPlayers) {
			Server::getInstance()->getLogger()->error('$maxPlayers has to be smaller than count of players in team');
			return FALSE;
		}
		$this->maxPlayers = $maxPlayers;
		return true;
	}
	public function getMinPlayers() : int{
		return $this->minPlayers;
	}
	public function setMinPlayers(int $minPlayers) {
		$this->minPlayers = $minPlayers;
		return;
	}
	public function getSpawn() : Postition{
		return $this->spawn;
	}
	public function setSpawn(Postition $spawn) {
		$this->spawn = $spawn;
	}
	public function addPlayer(Player $player) : bool{
		if(count($this->players) == $this->maxPlayers) {
			Server::getInstance()->getLogger()->error('adding new player on full team');
			return false;
		}
		if(is_null($this->getGame())){
			$this->removePlayer($player);
		} else {
			$this->getGame()->getGameManager()->removePlayer($player);
		}
		$this->players[] = $player;
		return true;
	}
	public function getPlayers() : array {
		return $this->players;
	}
	public function removePlayer(Player $player) {
		foreach ($this->players as $key => $pl) {
			$pl instanceof Player;
			if($player->getName() == $pl->getName()) {
				unset($this->players[$key]);
			}
		}
		$this->players = array_values($this->players);
		if(count($this->getPlayers()) == 0 and !is_null($this->getGame())) $this->getGame()->removeTeam($this->getName());
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
}
